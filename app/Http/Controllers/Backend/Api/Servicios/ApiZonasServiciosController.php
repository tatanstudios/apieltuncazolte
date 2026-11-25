<?php

namespace App\Http\Controllers\Backend\Api\Servicios;

use App\Http\Controllers\Controller;
use App\Models\BloqueServicios;
use App\Models\BloqueSlider;
use App\Models\Clientes;
use App\Models\DireccionCliente;
use App\Models\InformacionAdmin;
use App\Services\EstadoLocal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ApiZonasServiciosController extends Controller
{
    // obtener listado de servicios
    public function listadoBloque(Request $request){

        $rules = array(
            'id' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){return ['success' => 0]; }

        // retornar bloques de servicios
        $servicios = BloqueServicios::where('activo', 1)
            ->orderBy('posicion', 'ASC')
            ->get();

        // la ruta es pública: si viene el token se sabe quién es y cuántos productos tiene en el carrito
        $cantidadCarrito = 0;
        if ($cliente = auth('sanctum')->user()) {
            $cantidadCarrito = (int) DB::table('carrito_temporal AS t')
                ->join('carrito_extra AS e', 'e.id_carrito_temporal', '=', 't.id')
                ->where('t.id_clientes', $cliente->id)
                ->sum('e.cantidad');
        }

        $estado = EstadoLocal::ahora();

        // 1 = solo orden normal, 2 = solo WhatsApp, 3 = ambas (con 2 la app oculta "Órdenes")
        $modoPedido = (int) (InformacionAdmin::value('modo_pedido') ?? 1);
        if (!in_array($modoPedido, [1, 2, 3], true)) {
            $modoPedido = 1;
        }

        return [
            'success' => 1,
            'servicios' => $servicios,
            'abierto' => $estado['abierto'] ? 1 : 0,
            'estado_texto' => $estado['texto'],
            'estado_detalle' => $estado['detalle'],
            'cantidad_carrito' => $cantidadCarrito,
            'modo_pedido' => $modoPedido,
        ];
    }



}
