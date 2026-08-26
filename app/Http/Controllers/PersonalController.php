<?php

namespace App\Http\Controllers;

use App\Models\Contenedor;
use App\Models\PersonalProductos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PersonalController extends Controller
{

    public function vistaPersonalAgregarProductos()
    {
        return view('personal.vistapersonaladdproductos');
    }

    public function nuevoProductoPersonal(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:300',
            'precio' => 'required|numeric|min:0.01|max:1000000',
        ]);

        $nombreFoto = null;

        if ($request->hasFile('imagen')) {
            $nombreFoto = Str::random(15) . '_' . microtime(true) . '.' .
                strtolower($request->imagen->getClientOriginalExtension());

            $subido = Storage::disk('imagenes2')->put($nombreFoto, file_get_contents($request->imagen));

            if (!$subido) {
                return ['success' => 2];
            }
        }

        PersonalProductos::create([
            'nombre' => $request->nombre,
            'precio' => $request->precio,
            'imagen' => $nombreFoto,
        ]);

        return ['success' => 1];
    }


    public function vistaPersonal()
    {
        $totalProductos = PersonalProductos::count();
        return view('personal.vistapersonal', compact('totalProductos'));
    }

    public function mostrarProductos(Request $request)
    {
        $cantidad = (int)$request->input('cantidad');

        if ($cantidad <= 0) {
            return redirect('/admin/personales/mitienda/index');
        }

        $productos = PersonalProductos::select('id', 'nombre', 'precio', 'imagen')
            ->inRandomOrder()
            ->limit($cantidad)
            ->get();

        return view('personal.vistaproductos', compact('productos'));
    }

    public function verificarPrecios(Request $request)
    {
        $productosId = $request->input('producto_id');
        $precios     = $request->input('precio');

        $resultados  = [];
        $correctas   = 0;
        $incorrectas = 0;

        foreach ($productosId as $index => $id) {
            $producto       = PersonalProductos::find($id);
            $precioIngresado = trim($precios[$index]);
            $precioReal      = trim($producto->precio);

            $esCorrecta = (floatval($precioIngresado) == floatval($precioReal));

            $esCorrecta ? $correctas++ : $incorrectas++;

            $resultados[] = [
                'nombre'           => $producto->nombre,
                'imagen'           => $producto->imagen,   // null si no tiene
                'precio_ingresado' => number_format((float)$precioIngresado, 2),
                'precio_real'      => number_format((float)$precioReal, 2),
                'es_correcta'      => $esCorrecta,
            ];
        }

        $total = count($resultados);

        return view('personal.vistaresultado', compact('resultados', 'correctas', 'incorrectas', 'total'));
    }




}
