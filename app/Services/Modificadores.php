<?php

namespace App\Services;

use App\Models\CarritoExtraOpcion;
use App\Models\ModificadorGrupo;
use App\Models\ModificadorOpcion;

/**
 * Reglas de los modificadores de producto (sin inventario).
 *
 * Bloque (grupo):
 *  - requerido: el cliente debe elegir al menos max(1, mínimo) unidades.
 *  - opcional:  puede no elegir nada; si elige, debe cumplir el mínimo.
 *  - máximo:    unidades máximas en el bloque (con máximo 1, tocar otra opción la cambia).
 * Opción:
 *  - precio:      se suma al precio del producto por cada unidad elegida.
 *  - máximo:      veces que se puede elegir la misma opción.
 *  - obligatorio: siempre va incluida (el cliente no la puede quitar).
 *  - una_sola:    si se elige, no se puede combinar con otras opciones del bloque.
 *  - activo:      solo las activas se muestran y se pueden elegir.
 *
 * La app manda la selección como texto "idOpcion:cantidad,idOpcion:cantidad".
 */
class Modificadores
{
    /** Bloques con sus opciones activas, listos para la app. Bloques sin opciones activas no se envían. */
    public static function grupos(int $idProducto): array
    {
        $grupos = ModificadorGrupo::where('id_producto', $idProducto)->orderBy('posicion')->orderBy('id')->get();
        if ($grupos->isEmpty()) {
            return [];
        }

        $opciones = ModificadorOpcion::whereIn('id_grupo', $grupos->pluck('id'))
            ->where('activo', 1)
            ->orderBy('posicion')->orderBy('id')
            ->get()
            ->groupBy('id_grupo');

        $resultado = [];
        foreach ($grupos as $g) {
            $lista = $opciones->get($g->id, collect());
            if ($lista->isEmpty()) {
                continue;
            }

            $resultado[] = [
                'id' => $g->id,
                'nombre' => $g->nombre,
                'requerido' => (int) $g->requerido,
                'minimo' => self::minimoEfectivo($g),
                'maximo' => (int) $g->maximo,
                'opciones' => $lista->map(fn ($o) => [
                    'id' => $o->id,
                    'nombre' => $o->nombre,
                    'precio' => number_format((float) $o->precio, 2, '.', ''),
                    'maximo' => (int) $o->maximo,
                    'obligatorio' => (int) $o->obligatorio,
                    'una_sola' => (int) $o->una_sola,
                ])->values()->all(),
            ];
        }

        return $resultado;
    }

    /** "12:1,15:2" -> [12 => 1, 15 => 2]. Ignora basura y repetidos (suma). */
    public static function parsear(?string $texto): array
    {
        $seleccion = [];
        if ($texto === null || trim($texto) === '') {
            return $seleccion;
        }

        foreach (explode(',', $texto) as $par) {
            $partes = explode(':', trim($par));
            $id = (int) ($partes[0] ?? 0);
            $cantidad = isset($partes[1]) ? (int) $partes[1] : 1;
            if ($id > 0 && $cantidad > 0) {
                $seleccion[$id] = ($seleccion[$id] ?? 0) + $cantidad;
            }
        }

        return $seleccion;
    }

    /** Selección guardada de una fila del carrito: [idOpcion => cantidad]. */
    public static function seleccionDeFila(int $idCarritoExtra): array
    {
        return CarritoExtraOpcion::where('id_carrito_extra', $idCarritoExtra)
            ->pluck('cantidad', 'id_opcion')
            ->map(fn ($c) => (int) $c)
            ->all();
    }

    /**
     * Valida la selección contra las reglas actuales del producto.
     *
     * @return array{ok: bool, mensaje: ?string, extra: float, lineas: array}
     *   lineas = [['id_opcion', 'grupo', 'opcion', 'precio', 'cantidad'], ...] en el orden del producto
     */
    public static function validar(int $idProducto, array $seleccion): array
    {
        $grupos = ModificadorGrupo::where('id_producto', $idProducto)->orderBy('posicion')->orderBy('id')->get();
        $opciones = $grupos->isEmpty() ? collect() : ModificadorOpcion::whereIn('id_grupo', $grupos->pluck('id'))
            ->orderBy('posicion')->orderBy('id')
            ->get();

        $porId = $opciones->keyBy('id');
        $error = fn (string $m) => ['ok' => false, 'mensaje' => $m, 'extra' => 0.0, 'lineas' => []];

        // cada opción elegida debe existir, estar activa, ser de este producto y no pasar su máximo
        foreach ($seleccion as $idOpcion => $cantidad) {
            $op = $porId->get($idOpcion);
            if (!$op || !$op->activo) {
                return $error('Una de las opciones elegidas ya no está disponible, revisa las opciones');
            }
            if ($cantidad > max(1, (int) $op->maximo)) {
                return $error("«{$op->nombre}» se puede elegir máximo {$op->maximo}");
            }
        }

        $extra = 0.0;
        $lineas = [];

        foreach ($grupos as $g) {
            $activas = $opciones->where('id_grupo', $g->id)->where('activo', 1);
            if ($activas->isEmpty()) {
                continue; // bloque sin opciones activas: no se pide
            }

            $elegidas = $activas->filter(fn ($o) => isset($seleccion[$o->id]));
            $total = $elegidas->sum(fn ($o) => $seleccion[$o->id]);

            foreach ($activas->where('obligatorio', 1) as $o) {
                if (!isset($seleccion[$o->id])) {
                    return $error("«{$o->nombre}» es obligatorio en «{$g->nombre}»");
                }
            }

            if ($elegidas->count() > 1 && $elegidas->contains(fn ($o) => $o->una_sola)) {
                $sola = $elegidas->first(fn ($o) => $o->una_sola);
                return $error("«{$sola->nombre}» no se puede combinar con otras opciones de «{$g->nombre}»");
            }

            $minimo = self::minimoEfectivo($g);
            $maximo = max(1, (int) $g->maximo);

            if ($total > $maximo) {
                return $error("«{$g->nombre}»: máximo {$maximo}");
            }
            if (($g->requerido || $total > 0) && $total < $minimo) {
                return $error("«{$g->nombre}»: " . self::textoRegla($minimo, $maximo));
            }

            foreach ($elegidas as $o) {
                $cantidad = $seleccion[$o->id];
                $extra += $cantidad * (float) $o->precio;
                $lineas[] = [
                    'id_opcion' => $o->id,
                    'grupo' => $g->nombre,
                    'opcion' => $o->nombre,
                    'precio' => (float) $o->precio,
                    'cantidad' => $cantidad,
                ];
            }
        }

        return ['ok' => true, 'mensaje' => null, 'extra' => round($extra, 2), 'lineas' => $lineas];
    }

    /** Reemplaza las opciones guardadas de una fila del carrito. */
    public static function guardarEnFila(int $idCarritoExtra, array $lineas): void
    {
        CarritoExtraOpcion::where('id_carrito_extra', $idCarritoExtra)->delete();
        foreach ($lineas as $l) {
            CarritoExtraOpcion::create([
                'id_carrito_extra' => $idCarritoExtra,
                'id_opcion' => $l['id_opcion'],
                'cantidad' => $l['cantidad'],
            ]);
        }
    }

    /**
     * Texto para mostrar: una línea por bloque.
     * ej. ["Preparación de Huevo: Revuelto", "Extras: Tocino x2 (+$1.00), Queso"]
     */
    public static function texto(array $lineas): array
    {
        $porGrupo = [];
        foreach ($lineas as $l) {
            $t = $l['opcion'];
            if ($l['cantidad'] > 1) {
                $t .= ' x' . $l['cantidad'];
            }
            $precio = $l['precio'] * $l['cantidad'];
            if ($precio > 0) {
                $t .= ' (+$' . number_format($precio, 2, '.', ',') . ')';
            }
            $porGrupo[$l['grupo']][] = $t;
        }

        $resultado = [];
        foreach ($porGrupo as $grupo => $opciones) {
            $resultado[] = $grupo . ': ' . implode(', ', $opciones);
        }
        return $resultado;
    }

    /** Texto de las opciones guardadas en las filas de una orden: [idOrdenesDescripcion => [lineas de texto]]. */
    public static function textoDeOrden(array $idsDescripcion): array
    {
        if (empty($idsDescripcion)) {
            return [];
        }

        return \App\Models\OrdenesDescripcionOpcion::whereIn('id_ordenes_descripcion', $idsDescripcion)
            ->orderBy('id')
            ->get()
            ->groupBy('id_ordenes_descripcion')
            ->map(fn ($filas) => self::texto($filas->map(fn ($f) => [
                'grupo' => $f->grupo,
                'opcion' => $f->opcion,
                'precio' => (float) $f->precio,
                'cantidad' => (int) $f->cantidad,
            ])->all()))
            ->all();
    }

    /**
     * Opciones guardadas en las filas de una orden, agrupadas por bloque (para el panel):
     * [idOrdenesDescripcion => ['grupos' => [['grupo' => ..., 'opciones' => [[opcion, cantidad, precio], ...]]], 'extra' => float]]
     */
    public static function gruposDeOrden(array $idsDescripcion): array
    {
        if (empty($idsDescripcion)) {
            return [];
        }

        $resultado = [];
        $filas = \App\Models\OrdenesDescripcionOpcion::whereIn('id_ordenes_descripcion', $idsDescripcion)
            ->orderBy('id')
            ->get()
            ->groupBy('id_ordenes_descripcion');

        foreach ($filas as $idDescripcion => $opciones) {
            $grupos = [];
            $extra = 0.0;
            foreach ($opciones as $o) {
                $grupos[$o->grupo][] = [
                    'opcion' => $o->opcion,
                    'cantidad' => (int) $o->cantidad,
                    'precio' => (float) $o->precio * (int) $o->cantidad,
                ];
                $extra += (float) $o->precio * (int) $o->cantidad;
            }

            $resultado[$idDescripcion] = [
                'grupos' => collect($grupos)->map(fn ($ops, $nombre) => ['grupo' => $nombre, 'opciones' => $ops])->values()->all(),
                'extra' => round($extra, 2),
            ];
        }

        return $resultado;
    }

    public static function minimoEfectivo($grupo): int
    {
        $minimo = (int) $grupo->minimo;
        return $grupo->requerido ? max(1, $minimo) : $minimo;
    }

    public static function textoRegla(int $minimo, int $maximo): string
    {
        if ($minimo === $maximo) {
            return "elige {$minimo}";
        }
        if ($minimo <= 0) {
            return "elige hasta {$maximo}";
        }
        return "elige de {$minimo} a {$maximo}";
    }
}
