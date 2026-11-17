<?php

namespace App\Http\Controllers\Backend\Api\Carrito;

use App\Http\Controllers\Controller;
use App\Models\CarritoExtra;
use App\Models\CarritoTemporal;
use App\Models\Clientes;
use App\Models\DireccionCliente;
use App\Models\Horario;
use App\Models\InformacionAdmin;
use App\Models\OrdenesDescripcion;
use App\Models\OrdenesDirecciones;
use App\Models\OrdenesDescripcionOpcion;
use App\Services\Modificadores;
use App\Services\EstadoLocal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ApiCarritoController extends Controller
{
    /**
     * Filas del carrito con el precio por unidad ya calculado (producto + opciones elegidas)
     * y la validación de las opciones contra las reglas actuales del producto.
     */
    private function filasCarrito(int $idCarrito)
    {
        $filas = DB::table('producto AS p')
            ->join('carrito_extra AS c', 'c.id_producto', '=', 'p.id')
            ->select('p.id AS productoID', 'p.nombre', 'c.cantidad',
                'p.imagen', 'p.precio', 'p.activo',
                'c.id AS carritoid', 'p.utiliza_imagen', 'c.nota_producto AS nota')
            ->where('c.id_carrito_temporal', $idCarrito)
            ->orderBy('c.id')
            ->get();

        foreach ($filas as $f) {
            $validacion = Modificadores::validar($f->productoID, Modificadores::seleccionDeFila($f->carritoid));

            $f->modificadoresOk = $validacion['ok'];
            $f->modificadoresMensaje = $validacion['mensaje'];
            $f->lineas = $validacion['lineas'];
            $f->precioUnidad = round((float) $f->precio + $validacion['extra'], 2);
            $f->opciones = Modificadores::texto($validacion['lineas']);
        }

        return $filas;
    }

    public function verCarritoDecompras(Request $request){

        $reglaDatos = array(
            'clienteid' => 'required',
        );

        $validarDatos = Validator::make($request->all(), $reglaDatos);

        if($validarDatos->fails()){return ['success' => 0]; }

        if(Clientes::where('id', $request->clienteid)->first()){

            try {

                $hayDireccionRegistrada = 1;
                if(!DireccionCliente::where('id_cliente', $request->clienteid)->first()){
                    $hayDireccionRegistrada = 0;
                }


                $estadoProductoGlobal = 0; // saver si producto esta activo

                // preguntar si usuario ya tiene un carrito de compras
                if($cart = CarritoTemporal::where('id_clientes', $request->clienteid)->first()){
                    $producto = $this->filasCarrito($cart->id);

                    $subTotal = 0;

                    // verificar cada producto
                    foreach ($producto as $pro) {

                        // saver si al menos un producto no esta activo o disponible
                        if($pro->activo == 0){
                            $estadoProductoGlobal = 1; // producto no disponible global
                            $pro->titulo = 'No disponible';
                            $pro->mensaje = 'Quítalo para continuar';
                        } else if (!$pro->modificadoresOk) {
                            // cambiaron las opciones del producto: el cliente debe editarlo
                            $estadoProductoGlobal = 1;
                            $pro->titulo = 'Revisa las opciones';
                            $pro->mensaje = $pro->modificadoresMensaje;
                        }

                        // precio unitario ya incluye las opciones elegidas
                        $total = $pro->cantidad * $pro->precioUnidad;
                        $subTotal += $total;

                        $pro->preciobase = number_format((float) $pro->precio, 2, '.', ','); // sin opciones
                        $pro->preciounitario = number_format($pro->precioUnidad, 2, '.', ',');
                        $pro->precioformat = number_format($total, 2, '.', ','); // total de la fila
                        $pro->precio = number_format($total, 2, '.', ''); // se mantiene por compatibilidad

                        unset($pro->precioUnidad, $pro->modificadoresOk, $pro->modificadoresMensaje, $pro->lineas);
                    }

                    return [
                        'success' => 1,
                        'subtotal' => number_format((float)$subTotal, 2, '.', ','), // subtotal
                        'estadoProductoGlobal' => $estadoProductoGlobal, // saver si producto esta activo
                        'producto' => $producto, //todos los productos
                        'hayDireccionRegistrada' => $hayDireccionRegistrada
                    ];
                }else{
                    return [
                        'success' => 2  // no tiene carrito de compras
                    ];
                }
            }catch(\Error $e){
                return [
                    'success' => 3, // error
                ];
            }
        }
        else{
            return ['success' => 4]; // usuario no encontrado
        }
    }

    public function borrarCarritoDeCompras(Request $request){

        $reglaDatos = array(
            'clienteid' => 'required',
        );

        $validarDatos = Validator::make($request->all(), $reglaDatos );

        if($validarDatos->fails()){return ['success' => 0]; }

        if($carrito = CarritoTemporal::where('id_clientes', $request->clienteid)->first()){
            CarritoExtra::where('id_carrito_temporal', $carrito->id)->delete();
            CarritoTemporal::where('id_clientes', $request->clienteid)->delete();

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }

    public function borrarProductoDelCarrito(Request $request){

        $reglaDatos = array(
            'clienteid' => 'required',
            'carritoid' => 'required'
        );

        $validarDatos = Validator::make($request->all(), $reglaDatos);

        if($validarDatos->fails()){return ['success' => 0]; }

        // verificar si tenemos carrito
        if($ctm = CarritoTemporal::where('id_clientes', $request->clienteid)->first()){

            // encontrar el producto a borrar
            if(CarritoExtra::where('id', $request->carritoid)->where('id_carrito_temporal', $ctm->id)->first()){
                CarritoExtra::where('id', $request->carritoid)->where('id_carrito_temporal', $ctm->id)->delete();

                // saver si tenemos mas productos aun
                $dato = CarritoExtra::where('id_carrito_temporal', $ctm->id)->get();

                if(count($dato) == 0){
                    CarritoTemporal::where('id', $ctm->id)->delete();
                    return ['success' => 1]; // carrito de compras borrado
                }

                return ['success' => 2]; // producto eliminado
            }else{
                // producto a borrar no encontrado
                return ['success' => 3];
            }
        }else{
            // carrito de compras borrado
            return ['success' => 1 ];
        }
    }


    public function editarCantidadProducto(Request $request){

        $reglaDatos = array(
            'clienteid' => 'required',
            'cantidad' => 'required',
            'carritoid' => 'required',
        );

        $validarDatos = Validator::make($request->all(), $reglaDatos);

        if($validarDatos->fails()){return ['success' => 0]; }

        // buscar carrito de compras a quien pertenece el producto
        // verificar si existe el carrito
        if($ctm = CarritoTemporal::where('id_clientes', $request->clienteid)->first()){
            // verificar si existe el carrito extra id que manda el usuario (y que sea de su carrito)
            if($fila = CarritoExtra::where('id', $request->carritoid)->where('id_carrito_temporal', $ctm->id)->first()){

                // si la app manda opciones, se validan y reemplazan (versiones viejas no las mandan)
                $lineas = null;
                if ($request->has('opciones')) {
                    $modificadores = Modificadores::validar((int) $fila->id_producto, Modificadores::parsear($request->opciones));
                    if (!$modificadores['ok']) {
                        return ['success' => 3, 'titulo' => 'Opciones', 'mensaje' => $modificadores['mensaje']];
                    }
                    $lineas = $modificadores['lineas'];
                }

                DB::transaction(function () use ($fila, $request, $lineas) {
                    $fila->update(['cantidad' => $request->cantidad, 'nota_producto' => $request->nota]);
                    if ($lineas !== null) {
                        Modificadores::guardarEnFila($fila->id, $lineas);
                    }
                });

                return [
                    'success' => 1 // cantidad actualizada
                ];

            }else{
                // producto no encontrado
                return ['success' => 2];
            }
        }else{
            return ['success' => 2];
        }
    }

    public function verProductoCarritoEditar(Request $request){

        $reglaDatos = array(
            'clienteid' => 'required',
            'carritoid' => 'required' //es id del producto
        );

        $validarDatos = Validator::make($request->all(), $reglaDatos);

        if($validarDatos->fails()){return ['success' => 0]; }

        if($ctm = CarritoTemporal::where('id_clientes', $request->clienteid)->first()){

            if(CarritoExtra::where('id', $request->carritoid)->where('id_carrito_temporal', $ctm->id)->first()){

                // informacion del producto + cantidad elegida
                $producto = DB::table('producto AS p')
                    ->join('carrito_extra AS c', 'c.id_producto', '=', 'p.id')
                    ->select('p.id AS productoID', 'p.nombre', 'p.descripcion', 'c.cantidad', 'c.nota_producto',
                        'p.imagen', 'p.precio', 'p.utiliza_nota', 'p.nota', 'p.utiliza_imagen')
                    ->where('c.id', $request->carritoid)
                    ->first();

                // opciones del producto y lo que el cliente ya había elegido
                $seleccion = [];
                foreach (Modificadores::seleccionDeFila((int) $request->carritoid) as $idOpcion => $cantidad) {
                    $seleccion[] = ['id_opcion' => $idOpcion, 'cantidad' => $cantidad];
                }

                return [
                    'success' => 1,
                    'producto' => $producto,
                    'modificadores' => Modificadores::grupos((int) $producto->productoID),
                    'seleccion' => $seleccion,
                ];

            }else{
                // producto no encontrado
                return ['success' => 2];
            }
        }else{
            // no tiene carrito
            return ['success' => 3];
        }
    }


    public function verOrdenAProcesarCliente(Request $request){

        $reglaDatos = array(
            'clienteid' => 'required'
        );

        $validarDatos = Validator::make($request->all(), $reglaDatos);

        if($validarDatos->fails()){return ['success' => 0]; }

        // verificar que cliente tenga direccion
        if(!DireccionCliente::where('id_cliente', $request->clienteid)->first()){
            // sin direccion
            return ['success' => 1];
        }

        try {
            // preguntar si usuario ya tiene un carrito de compras
            if($cart = CarritoTemporal::where('id_clientes', $request->clienteid)->first()){

                $infoDireccion = DireccionCliente::where('id_cliente', $request->clienteid)
                    ->where('seleccionado', 1)
                    ->first();

                // listado de productos del carrito (precio con opciones) para el resumen
                $subtotal = 0;
                $resumen = [];
                foreach($this->filasCarrito($cart->id) as $p){
                    $totalFila = $p->cantidad * $p->precioUnidad;
                    $subtotal += $totalFila;
                    $resumen[] = [
                        'cantidad' => (int) $p->cantidad,
                        'nombre' => $p->nombre,
                        'opciones' => $p->opciones,
                        'nota' => $p->nota,
                        'total' => number_format($totalFila, 2, '.', ','),
                    ];
                }

                $minimoConsumo = 6; // $6.00 DOLARES ES EL MINIMO DE CONSUMO
                $minimo = 0; // aqui no puede ordenar

                $msjMinimoConsumo = "El mínimo de consumo es: $".number_format((float)$minimoConsumo, 2, '.', ',');;

                $estado = EstadoLocal::ahora();

                if($subtotal >= $minimoConsumo){
                    // si puede ordenar
                    $minimo = 1;
                }

                $total = "$" . number_format((float)$subtotal, 2, '.', '');

                return [
                    'success' => 2,
                    'total' => $total,
                    'cliente' => $infoDireccion->nombre,
                    'direccion' => $infoDireccion->direccion,
                    'telefono' => $infoDireccion->telefono,
                    'referencia' => $infoDireccion->punto_referencia,
                    'minimo' => $minimo,
                    'mensaje' => $msjMinimoConsumo,
                    'minimoconsumo' => number_format((float) $minimoConsumo, 2, '.', ','),
                    // estado del local: si está cerrado la app desactiva "Confirmar orden"
                    'abierto' => $estado['abierto'] ? 1 : 0,
                    'estado_texto' => $estado['texto'],
                    'estado_detalle' => $estado['motivo'] ?? 'Podrás enviar tu orden cuando el local abra.',
                    'faltante' => number_format(max(0, $minimoConsumo - $subtotal), 2, '.', ','),
                    'productos' => $resumen,
                ];

            }else{
                // no tiene carrito de compras
                return ['success' => 3];
            }
        }catch(\Error $e){
            return ['success' => 4, 'err' => $e];
        }
    }


    public function procesarOrdenEstado1(Request $request){

        // validaciones para los datos
        $reglaDatos = array(
            'clienteid' => 'required',
        );

        $validarDatos = Validator::make($request->all(), $reglaDatos);

        if($validarDatos->fails()){return ['success' => 0]; }


        DB::beginTransaction();

        try {

            // DIRECCION DEL CLIENTE
            $infoDireccion = DireccionCliente::where('id_cliente', $request->clienteid)
                ->where('seleccionado', 1)
                ->first();

            //**** VALIDACIONES

            // local cerrado (panel, día cerrado o fuera de horario): mismo cálculo que el aviso del menú
            $estado = EstadoLocal::ahora();
            if (!$estado['abierto']) {
                $mensaje = $estado['texto'];
                if (!empty($estado['motivo'])) {
                    $mensaje .= "\n" . $estado['motivo'];
                }
                // 1 = cerrado por horario, 2 = cerrado desde el panel (la app muestra titulo y mensaje)
                return [
                    'success' => !empty($estado['motivo']) ? 2 : 1,
                    'titulo' => 'Local cerrado',
                    'mensaje' => $mensaje,
                ];
            }

            // VERIFICAR SI TENGO CARRITO DE COMPRAS
            if($cart = CarritoTemporal::where('id_clientes', $request->clienteid)->first()){

                // listado de productos del carrito (precio con opciones)
                $producto = $this->filasCarrito($cart->id);

                $total = 0;

                foreach($producto as $p){
                    if ($p->activo == 0) {
                        return ['success' => 4, 'mensaje' => "«{$p->nombre}» no está disponible, quítalo del carrito"];
                    }
                    if (!$p->modificadoresOk) {
                        return ['success' => 4, 'mensaje' => "Revisa las opciones de «{$p->nombre}»: {$p->modificadoresMensaje}"];
                    }
                    $total += $p->cantidad * $p->precioUnidad;
                }

                $minimoConsumo = 6; // $6.00 DOLARES ES EL MINIMO DE CONSUMO
                $minimo = 0; // aqui no puede ordenar

                $msjMinimoConsumo = "El mínimo de consumo es: $".number_format((float)$minimoConsumo, 2, '.', ',');;

                if ($total < $minimoConsumo) {
                    // no puede ordenar
                    return ['success' => 3, 'mensaje' => $msjMinimoConsumo];
                }

                $fechahoy = Carbon::now('America/El_Salvador');

                $idOrden = DB::table('ordenes')->insertGetId(
                    [ 'id_clientes' => $request->clienteid,
                        'nota' => $request->nota,

                        'precio_consumido' => $total,
                        'fecha_orden' => $fechahoy,

                        'estado_iniciada' => 0, // el propietario inicia la orden
                        'fecha_iniciada' => null,

                        'estado_cancelada' => 0, // el motorista inicia el envio
                        'fecha_cancelada' => null,
                        'mensaje_cancelada' => null,
                        'cancelada_por' => 0,
                        'visible' => 1,
                    ]
                );

                // guadar todos los productos de esa orden
                // el precio guardado es por unidad e incluye las opciones; las opciones se copian
                // para que la orden no cambie si luego se editan los modificadores del producto
                foreach($producto as $p){

                    $idDescripcion = OrdenesDescripcion::insertGetId([
                        'id_ordenes' => $idOrden,
                        'id_producto' => $p->productoID,
                        'cantidad' => $p->cantidad,
                        'precio' => $p->precioUnidad,
                        'nota' => $p->nota,
                    ]);

                    foreach ($p->lineas as $l) {
                        OrdenesDescripcionOpcion::create([
                            'id_ordenes_descripcion' => $idDescripcion,
                            'id_opcion' => $l['id_opcion'],
                            'grupo' => $l['grupo'],
                            'opcion' => $l['opcion'],
                            'precio' => $l['precio'],
                            'cantidad' => $l['cantidad'],
                        ]);
                    }
                }


                $nuevaDir = new OrdenesDirecciones();
                $nuevaDir->id_ordenes = $idOrden;
                $nuevaDir->nombre = $infoDireccion->nombre;
                $nuevaDir->telefono = $infoDireccion->telefono;
                $nuevaDir->direccion = $infoDireccion->direccion;
                $nuevaDir->punto_referencia = $infoDireccion->punto_referencia;
                $nuevaDir->version = $request->version;
                $nuevaDir->save();

                // BORRAR CARRITO TEMPORAL DEL USUARIO
                CarritoExtra::where('id_carrito_temporal', $cart->id)->delete();
                CarritoTemporal::where('id_clientes', $request->clienteid)->delete();

                DB::commit();
                return ['success' => 5];
            }else{
                // no tiene carrito de compras
                return ['success' => 4, 'mensaje' => 'No hay productos'];
            }

        } catch(\Throwable $e){

            DB::rollback();
            return [
                'success' => 101,
                'error' => $e->getMessage()
            ];
        }
    }

}
