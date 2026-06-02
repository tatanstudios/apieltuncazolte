<?php

namespace App\Http\Controllers;

use App\Models\Contenedor;
use Illuminate\Http\Request;

class PersonalController extends Controller
{

    public function vistaPersonal()
    {
        // contar total de productos disponibles
        $totalProductos = Contenedor::count();
        return view('personal.vistapersonal', compact('totalProductos'));
    }


    public function mostrarProductos(Request $request)
    {
        $cantidad = (int)$request->input('cantidad');

        if ($cantidad <= 0) {
            return redirect('/admin/personales/mitienda/index');
        }

        $productos = Contenedor::select('id', 'alternativo', 'nombre', 'marca', 'unidad', 'precio')
            ->inRandomOrder()
            ->limit($cantidad)
            ->get();

        return view('personal.vistaproductos', compact('productos'));
    }

    public function verificarPrecios(Request $request)
    {
        $productosId = $request->input('producto_id');
        $precios = $request->input('precio');

        $resultados = [];
        $correctas = 0;
        $incorrectas = 0;

        foreach ($productosId as $index => $id) {
            $producto = Contenedor::where('id', $id)->first();
            $precioIngresado = trim($precios[$index]);
            $precioReal = trim($producto->precio);

            // Comparar como números para evitar problemas de formato
            $esCorrecta = (floatval($precioIngresado) == floatval($precioReal));

            if ($esCorrecta) {
                $correctas++;
            } else {
                $incorrectas++;
            }

            $resultados[] = [
                'alternativo' => $producto->alternativo,
                'nombre' => $producto->nombre,
                'marca' => $producto->marca,
                'unidad' => $producto->unidad,
                'precio_ingresado' => $precioIngresado,
                'precio_real' => $precioReal,
                'es_correcta' => $esCorrecta,
            ];
        }

        $total = count($resultados);

        return view('personal.vistaresultado', compact('resultados', 'correctas', 'incorrectas', 'total'));
    }



}
