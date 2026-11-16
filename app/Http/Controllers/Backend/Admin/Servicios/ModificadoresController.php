<?php

namespace App\Http\Controllers\Backend\Admin\Servicios;

use App\Http\Controllers\Controller;
use App\Models\Categorias;
use App\Models\ModificadorGrupo;
use App\Models\ModificadorOpcion;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Editor de modificadores (bloques de opciones) de un producto. Sin inventario.
 * Se guarda toda la estructura de una vez: se actualizan los bloques/opciones que traen id,
 * se crean los nuevos y se borran los que ya no vienen.
 */
class ModificadoresController extends Controller
{
    public function index($id)
    {
        $producto = Producto::findOrFail($id);
        $categoria = Categorias::where('id', $producto->id_categorias)->first();

        return view('backend.admin.productos.vistamodificadores', compact('producto', 'categoria'));
    }

    public function informacion($id)
    {
        $grupos = ModificadorGrupo::where('id_producto', $id)->orderBy('posicion')->orderBy('id')->get();
        $opciones = ModificadorOpcion::whereIn('id_grupo', $grupos->pluck('id'))
            ->orderBy('posicion')->orderBy('id')->get()->groupBy('id_grupo');

        $datos = $grupos->map(fn ($g) => [
            'id' => $g->id,
            'nombre' => $g->nombre,
            'requerido' => (bool) $g->requerido,
            'minimo' => (int) $g->minimo,
            'maximo' => (int) $g->maximo,
            'opciones' => $opciones->get($g->id, collect())->map(fn ($o) => [
                'id' => $o->id,
                'nombre' => $o->nombre,
                'precio' => number_format((float) $o->precio, 2, '.', ''),
                'maximo' => (int) $o->maximo,
                'obligatorio' => (bool) $o->obligatorio,
                'una_sola' => (bool) $o->una_sola,
                'activo' => (bool) $o->activo,
            ])->values(),
        ])->values();

        return ['success' => 1, 'grupos' => $datos];
    }

    public function guardar(Request $request)
    {
        $producto = Producto::find($request->input('id_producto'));
        if (!$producto) {
            return ['success' => 0, 'mensaje' => 'Producto no encontrado'];
        }

        $grupos = $request->input('grupos', []);
        if (!is_array($grupos)) {
            return ['success' => 0, 'mensaje' => 'Datos inválidos'];
        }

        // ---- validación (la misma que hace el formulario, por si acaso) ----
        foreach ($grupos as $i => $g) {
            $n = $i + 1;
            $nombre = trim($g['nombre'] ?? '');
            $requerido = !empty($g['requerido']);
            $minimo = (int) ($g['minimo'] ?? 0);
            $maximo = (int) ($g['maximo'] ?? 0);
            $opciones = $g['opciones'] ?? [];

            if ($nombre === '' || mb_strlen($nombre) > 100) {
                return ['success' => 2, 'mensaje' => "Bloque {$n}: el nombre es requerido (máximo 100 caracteres)"];
            }
            if ($maximo < 1) {
                return ['success' => 2, 'mensaje' => "«{$nombre}»: el máximo debe ser al menos 1"];
            }
            if ($minimo < 0 || $minimo > $maximo) {
                return ['success' => 2, 'mensaje' => "«{$nombre}»: el mínimo no puede ser mayor que el máximo"];
            }
            if ($requerido && $minimo < 1) {
                return ['success' => 2, 'mensaje' => "«{$nombre}»: si es requerido, el mínimo debe ser al menos 1"];
            }
            if (!is_array($opciones) || count($opciones) === 0) {
                return ['success' => 2, 'mensaje' => "«{$nombre}»: agrega al menos una opción"];
            }

            $obligatorias = 0;
            $capacidad = 0;
            foreach ($opciones as $j => $o) {
                $nomOp = trim($o['nombre'] ?? '');
                $precio = $o['precio'] ?? 0;
                $maxOp = (int) ($o['maximo'] ?? 0);

                if ($nomOp === '' || mb_strlen($nomOp) > 100) {
                    return ['success' => 2, 'mensaje' => "«{$nombre}», opción " . ($j + 1) . ": el nombre es requerido"];
                }
                if (!is_numeric($precio) || $precio < 0 || $precio > 100000) {
                    return ['success' => 2, 'mensaje' => "«{$nomOp}»: el precio extra no es válido"];
                }
                if ($maxOp < 1) {
                    return ['success' => 2, 'mensaje' => "«{$nomOp}»: el máximo debe ser al menos 1"];
                }
                if (!empty($o['obligatorio']) && !empty($o['una_sola'])) {
                    return ['success' => 2, 'mensaje' => "«{$nomOp}»: no puede ser obligatoria y «una sola» a la vez"];
                }
                if (!empty($o['activo'])) {
                    $capacidad += $maxOp;
                    if (!empty($o['obligatorio'])) {
                        $obligatorias++;
                    }
                }
            }

            if ($obligatorias > $maximo) {
                return ['success' => 2, 'mensaje' => "«{$nombre}»: hay más opciones obligatorias que el máximo del bloque"];
            }
            if ($requerido && $capacidad < $minimo) {
                return ['success' => 2, 'mensaje' => "«{$nombre}»: con las opciones activas no se puede llegar al mínimo ({$minimo})"];
            }
        }

        DB::beginTransaction();
        try {
            $gruposGuardados = [];

            foreach (array_values($grupos) as $i => $g) {
                $datosGrupo = [
                    'id_producto' => $producto->id,
                    'nombre' => trim($g['nombre']),
                    'requerido' => !empty($g['requerido']) ? 1 : 0,
                    'minimo' => (int) $g['minimo'],
                    'maximo' => (int) $g['maximo'],
                    'posicion' => $i + 1,
                ];

                $grupo = !empty($g['id'])
                    ? ModificadorGrupo::where('id', $g['id'])->where('id_producto', $producto->id)->first()
                    : null;

                if ($grupo) {
                    $grupo->update($datosGrupo);
                } else {
                    $grupo = ModificadorGrupo::create($datosGrupo);
                }
                $gruposGuardados[] = $grupo->id;

                $opcionesGuardadas = [];
                foreach (array_values($g['opciones']) as $j => $o) {
                    $datosOpcion = [
                        'id_grupo' => $grupo->id,
                        'nombre' => trim($o['nombre']),
                        'precio' => round((float) $o['precio'], 2),
                        'maximo' => (int) $o['maximo'],
                        'obligatorio' => !empty($o['obligatorio']) ? 1 : 0,
                        'una_sola' => !empty($o['una_sola']) ? 1 : 0,
                        'activo' => !empty($o['activo']) ? 1 : 0,
                        'posicion' => $j + 1,
                    ];

                    $opcion = !empty($o['id'])
                        ? ModificadorOpcion::where('id', $o['id'])->where('id_grupo', $grupo->id)->first()
                        : null;

                    if ($opcion) {
                        $opcion->update($datosOpcion);
                    } else {
                        $opcion = ModificadorOpcion::create($datosOpcion);
                    }
                    $opcionesGuardadas[] = $opcion->id;
                }

                // opciones quitadas del bloque (se borran también de los carritos)
                ModificadorOpcion::where('id_grupo', $grupo->id)->whereNotIn('id', $opcionesGuardadas)->delete();
            }

            // bloques quitados
            ModificadorGrupo::where('id_producto', $producto->id)->whereNotIn('id', $gruposGuardados)->delete();

            DB::commit();
            return ['success' => 1];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Modificadores: error al guardar', ['producto' => $producto->id, 'error' => $e->getMessage()]);
            return ['success' => 0, 'mensaje' => 'Error al guardar'];
        }
    }
}
