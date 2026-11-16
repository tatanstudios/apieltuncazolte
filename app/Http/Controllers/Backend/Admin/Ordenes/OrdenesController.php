<?php

namespace App\Http\Controllers\Backend\Admin\Ordenes;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotiClienteJobs;
use App\Models\Clientes;
use App\Models\Ordenes;
use App\Models\OrdenesDescripcion;
use App\Services\Modificadores;
use App\Services\Notificaciones;
use App\Models\OrdenesDirecciones;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Ladumor\OneSignal\OneSignal;
use Mpdf\Mpdf;
class OrdenesController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index(){
        return view('backend.admin.ordenes.todas.vistaordenes');
    }

    // no canceladas
    public function tablaOrdenesTodas(){

        $ordenes = Ordenes::where('estado_cancelada', 0)
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($ordenes as $mm){

            // la dirección se busca por el id de la orden (no por su propio id)
            $clienteDireccion = OrdenesDirecciones::where('id_ordenes', $mm->id)->first();
            $mm->cliente = $clienteDireccion->nombre ?? '';
            $mm->telefono = $clienteDireccion->telefono ?? '';
            $mm->direccion = $clienteDireccion->direccion ?? '';
            $mm->referencia = $clienteDireccion->punto_referencia ?? '';

            $mm->fecha_orden = date("h:i A d-m-Y", strtotime($mm->fecha_orden));
            $mm->precio_consumido = number_format((float)$mm->precio_consumido, 2, '.', ',');
        }

        return view('backend.admin.ordenes.todas.tablaordenes', compact('ordenes'));
    }


    public function indexOrdenesCanceladas(){

        return view('backend.admin.ordenes.canceladas.vistaordenescanceladas');
    }


    public function tablaOrdenesTodasCanceladas(){
        $ordenes = Ordenes::where('estado_cancelada', 1)
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($ordenes as $mm){

            // la dirección se busca por el id de la orden (no por su propio id)
            $clienteDireccion = OrdenesDirecciones::where('id_ordenes', $mm->id)->first();
            $mm->cliente = $clienteDireccion->nombre ?? '';
            $mm->telefono = $clienteDireccion->telefono ?? '';
            $mm->direccion = $clienteDireccion->direccion ?? '';
            $mm->referencia = $clienteDireccion->punto_referencia ?? '';

            $mm->fecha_orden = date("h:i A d-m-Y", strtotime($mm->fecha_orden));
            $mm->precio_consumido = number_format((float)$mm->precio_consumido, 2, '.', ',');
        }

        return view('backend.admin.ordenes.canceladas.tablaordenescanceladas', compact('ordenes'));
    }




    public function indexProductosOrdenes($id){
        return view('backend.admin.ordenes.productos.vistaproductoorden', compact('id'));
    }

    public function tablaOrdenesProducto($id){

        $orden = Ordenes::where('id', $id)->first();
        $lista = OrdenesDescripcion::where('id_ordenes', $id)->orderBy('id')->get();
        $opciones = Modificadores::gruposDeOrden($lista->pluck('id')->all());

        $suma = 0;
        $unidades = 0;

        foreach ($lista as $ll){

            $info = Producto::where('id', $ll->id_producto)->first();
            $ll->nombre = $info->nombre ?? 'Producto eliminado';

            // el precio guardado ya incluye las opciones: se separa el precio base para mostrarlo
            $ll->grupos = $opciones[$ll->id]['grupos'] ?? [];
            $extra = $opciones[$ll->id]['extra'] ?? 0;
            $ll->precio_base = number_format(max(0, (float) $ll->precio - $extra), 2, '.', ',');
            $ll->extra = $extra > 0 ? number_format($extra, 2, '.', ',') : null;

            $total = $ll->cantidad * $ll->precio;
            $suma += $total;
            $unidades += $ll->cantidad;

            $ll->total = number_format((float)$total, 2, '.', ',');
            $ll->precio = number_format((float)$ll->precio, 2, '.', ',');
        }

        $suma = number_format($suma, 2, '.', ',');

        return view('backend.admin.ordenes.productos.tablaproductoorden', compact('lista', 'orden', 'suma', 'unidades'));
    }


    //**** ORDENES PENDIENTES *******

    public function indexOrdenesPendientes(){

        //$dataFecha = Carbon::now('America/El_Salvador');
        //$fecha = date("d-m-Y", strtotime($dataFecha));

        return view('backend.admin.ordenes.pendientes.vistaordenespendientes');
    }

    // datos JSON para la tabla (DataTables ajax), se recarga cada 30 segundos
    public function tablaOrdenesPendientes(){

        $ordenes = Ordenes::from('ordenes AS o')
            ->leftJoin('ordenes_direcciones AS d', 'd.id_ordenes', '=', 'o.id')
            ->where('o.estado_iniciada', 0)
            ->where('o.estado_cancelada', 0)
            ->orderBy('o.id', 'DESC')
            ->select('o.id', 'o.fecha_orden', 'o.precio_consumido', 'o.nota',
                'd.nombre AS cliente', 'd.direccion', 'd.punto_referencia AS referencia', 'd.telefono')
            ->get();

        $data = $ordenes->map(function ($mm) {
            return [
                'id' => $mm->id,
                'fecha_orden' => $mm->fecha_orden,
                'fecha_texto' => date("h:i A d-m-Y", strtotime($mm->fecha_orden)),
                'precio_consumido' => number_format((float)$mm->precio_consumido, 2, '.', ','),
                'nota' => $mm->nota,
                'cliente' => $mm->cliente,
                'direccion' => $mm->direccion,
                'referencia' => $mm->referencia,
                'telefono' => $mm->telefono,
            ];
        });

        return ['data' => $data];
    }




    public function imprimirTicketV2($id){

        $infoOrden = Ordenes::where('id', $id)->first();
        $infoDireccion = OrdenesDirecciones::where('id_ordenes', $id)->first();

        $fecha = date("d-m-Y h:i A", strtotime($infoOrden->fecha_orden));


        $lista = OrdenesDescripcion::where('id_ordenes', $id)->orderBy('id')->get();
        $opciones = Modificadores::gruposDeOrden($lista->pluck('id')->all());

        $suma = 0;
        $unidades = 0;

        foreach ($lista as $ll){

            $info = Producto::where('id', $ll->id_producto)->first();

            $ll->nomproducto = $info->nombre ?? 'Producto';
            $ll->grupos = $opciones[$ll->id]['grupos'] ?? [];

            $multiplicado = $ll->cantidad * $ll->precio;
            $suma = $suma + $multiplicado;
            $unidades += $ll->cantidad;

            $ll->multiplicado = number_format((float)$multiplicado, 2, '.', ',');
            $ll->precio = number_format((float)$ll->precio, 2, '.', ',');
        }

        $suma = number_format((float)$suma, 2, '.', ',');

        $datos = compact('infoOrden', 'lista', 'fecha', 'suma', 'unidades', 'infoDireccion');

        // Rollo térmico de 80 mm: el ancho es fijo y el alto se ajusta a lo que mide la orden.
        // 1.ª pasada: se mide dónde termina el ticket (div#fin-ticket). 2.ª pasada: PDF con ese alto.
        $ancho = 80 / 25.4 * 72; // 80 mm en puntos
        $alto = 0;

        $medicion = PDF::loadView('backend.admin.ticket.vistaticketv2', $datos)->setPaper([0, 0, $ancho, 4000]);
        $medicion->getDomPDF()->setCallbacks([[
            'event' => 'end_frame',
            'f' => function ($frame) use (&$alto) {
                $nodo = $frame->get_node();
                if ($nodo instanceof \DOMElement && $nodo->getAttribute('id') === 'fin-ticket') {
                    $caja = $frame->get_border_box();
                    $alto = $caja['y'] + $caja['h'];
                }
            },
        ]]);
        $medicion->output();

        $alto = $alto > 0 ? min($alto + 20, 4000) : 1200; // 20 pt de margen al final para el corte

        $pdf = PDF::loadView('backend.admin.ticket.vistaticketv2', $datos)->setPaper([0, 0, $ancho, $alto]);

        return $pdf->stream('ticket-orden-' . $id . '.pdf');
    }




    public function iniciarOrden(Request $request){

        $rules = array(
            'id' => 'required', // id de la orden
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()){return ['success' => 0]; }

        $fecha = Carbon::now('America/El_Salvador');

        // solo se inicia si sigue pendiente (evita choque si el cliente cancela al mismo tiempo)
        $filas = Ordenes::where('id', $request->id)
            ->where('estado_iniciada', 0)
            ->where('estado_cancelada', 0)
            ->update([
                'estado_iniciada' => 1,
                'fecha_iniciada' => $fecha,
            ]);

        if($filas > 0){
            // avisar al cliente que el local inició su orden (es el único estado después de enviarla)
            $orden = Ordenes::where('id', $request->id)->first();
            Notificaciones::cliente(
                (int) $orden->id_clientes,
                "Orden #{$orden->id} iniciada",
                'El local inició tu orden.',
                ['tipo' => 'orden', 'idorden' => $orden->id]
            );

            return ['success' => 2]; // orden iniciada
        }

        $infoOrdenes = Ordenes::where('id', $request->id)->first();

        if($infoOrdenes == null){
            return ['success' => 0]; // no existe
        }

        if($infoOrdenes->estado_cancelada == 1){
            return ['success' => 1]; // el cliente la cancelo
        }

        return ['success' => 3]; // ya estaba iniciada
    }


    function cancelarOrden(Request $request){

        $rules = array(
            'id' => 'required', // id de la orden
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()){return ['success' => 0]; }

        $fecha = Carbon::now('America/El_Salvador');

        // la vista "Iniciadas Hoy" tambien cancela ordenes iniciadas, por eso solo se valida que no este cancelada
        $filas = Ordenes::where('id', $request->id)
            ->where('estado_cancelada', 0)
            ->update([
                'estado_cancelada' => 1,
                'fecha_cancelada' => $fecha,
                'mensaje_cancelada' => $request->nombre,
                'cancelada_por' => 2 // por propietario
            ]);

        if($filas > 0){
            // avisar al cliente, con el motivo si el local lo escribió
            $orden = Ordenes::where('id', $request->id)->first();
            $motivo = trim((string) $request->nombre);
            Notificaciones::cliente(
                (int) $orden->id_clientes,
                "Orden #{$orden->id} cancelada",
                $motivo !== '' ? "Motivo: {$motivo}" : 'El local canceló tu orden.',
                ['tipo' => 'orden', 'idorden' => $orden->id]
            );

            return ['success' => 1];
        }

        if(Ordenes::where('id', $request->id)->exists()){
            return ['success' => 2]; // ya estaba cancelada
        }

        return ['success' => 0];
    }









    //******* ORDENES INICIADAS HOY ***************
    public function indexOrdenHoy(){

        $dataFecha = Carbon::now('America/El_Salvador');
        $fecha = date("d-m-Y", strtotime($dataFecha));

        return view('backend.admin.ordenes.hoy.vistaordeneshoy', compact('fecha'));
    }

    public function tablaOrdenesHoy(){
        $fecha = Carbon::now('America/El_Salvador');
        $ordenes = Ordenes::whereDate('fecha_orden', $fecha)
            ->where('estado_iniciada', 1)
            ->where('estado_cancelada', 0)
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($ordenes as $mm){

            // la dirección se busca por el id de la orden (no por su propio id)
            $clienteDireccion = OrdenesDirecciones::where('id_ordenes', $mm->id)->first();
            $mm->cliente = $clienteDireccion->nombre ?? '';
            $mm->telefono = $clienteDireccion->telefono ?? '';
            $mm->direccion = $clienteDireccion->direccion ?? '';
            $mm->referencia = $clienteDireccion->punto_referencia ?? '';

            $mm->fecha_orden = date("h:i A d-m-Y", strtotime($mm->fecha_orden));
            $mm->precio_consumido = number_format((float)$mm->precio_consumido, 2, '.', ',');
        }

        return view('backend.admin.ordenes.hoy.tablaordeneshoy', compact('ordenes'));
    }


}
