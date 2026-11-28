<?php

namespace App\Http\Controllers\Backend\Api\Carrito;

use App\Http\Controllers\Controller;
use App\Models\InformacionAdmin;
use App\Services\EstadoLocal;
use App\Services\Modificadores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pedidos por WhatsApp sin cuenta de cliente.
 *
 * El carrito y la dirección viven en el teléfono. La app manda el carrito en cada petición
 * como JSON: [{"fila":1,"producto":5,"cantidad":2,"nota":"sin cebolla","opciones":"12:1,15:2"}]
 * ("fila" es el id local de la fila y se devuelve tal cual para que la app la identifique).
 *
 * Los precios SIEMPRE se calculan aquí con la base de datos; nada de lo que manda la app
 * sobre precios se usa. No se guarda nada en el servidor.
 */
class ApiPedidoWhatsappController extends Controller
{
    const MAX_FILAS = 50;
    const MAX_CANTIDAD = 99;

    /** Configuración del panel: número de WhatsApp y costo de envío. */
    private function config(): array
    {
        $info = InformacionAdmin::first();

        return [
            'whatsapp' => preg_replace('/\D/', '', (string) ($info->whatsapp_numero ?? '')),
            'envio_limite' => (float) ($info->envio_limite ?? 6),
            'envio_costo' => (float) ($info->envio_costo ?? 0),
        ];
    }

    /** Envío: se cobra si el subtotal es igual o menor al límite del panel. */
    private function costoEnvio(float $subtotal, array $config): float
    {
        if ($subtotal <= 0 || $config['envio_costo'] <= 0) {
            return 0;
        }
        return $subtotal <= $config['envio_limite'] ? $config['envio_costo'] : 0;
    }

    private function dinero(float $monto): string
    {
        return number_format($monto, 2, '.', ',');
    }

    /** Lee el JSON del carrito que manda la app y lo limpia. */
    private function leerCarrito(?string $json): array
    {
        $datos = json_decode((string) $json, true);
        if (!is_array($datos)) {
            return [];
        }

        $filas = [];
        foreach (array_slice($datos, 0, self::MAX_FILAS) as $d) {
            if (!is_array($d)) continue;

            $producto = (int) ($d['producto'] ?? 0);
            $cantidad = (int) ($d['cantidad'] ?? 0);
            if ($producto <= 0 || $cantidad <= 0) continue;

            $nota = trim((string) ($d['nota'] ?? ''));

            $filas[] = [
                'fila' => (int) ($d['fila'] ?? 0),
                'producto' => $producto,
                'cantidad' => min($cantidad, self::MAX_CANTIDAD),
                'nota' => $nota === '' ? null : mb_substr($nota, 0, 300),
                'opciones' => (string) ($d['opciones'] ?? ''),
            ];
        }

        return $filas;
    }

    /**
     * Calcula cada fila con los datos actuales del producto (precio + opciones elegidas)
     * y marca las que no se pueden pedir (producto desactivado o borrado, opciones que cambiaron).
     */
    private function calcular(array $filas): array
    {
        $ids = array_unique(array_column($filas, 'producto'));
        $productos = DB::table('producto')
            ->whereIn('id', $ids)
            ->select('id', 'nombre', 'precio', 'activo', 'imagen', 'utiliza_imagen')
            ->get()
            ->keyBy('id');

        $resultado = [];
        $subtotal = 0;
        $hayProblema = false;

        foreach ($filas as $f) {
            $p = $productos->get($f['producto']);

            $fila = (object) [
                'fila' => $f['fila'],
                'productoID' => $f['producto'],
                'nombre' => $p->nombre ?? 'Producto no disponible',
                'cantidad' => $f['cantidad'],
                'imagen' => $p->imagen ?? null,
                'utiliza_imagen' => (int) ($p->utiliza_imagen ?? 0),
                'activo' => (int) ($p->activo ?? 0),
                'nota' => $f['nota'],
                'opciones' => [],
                'precioUnidad' => 0.0,
                'precioBase' => (float) ($p->precio ?? 0),
                'titulo' => null,
                'mensaje' => null,
            ];

            if (!$p || (int) $p->activo === 0) {
                $fila->activo = 0;
                $fila->titulo = 'No disponible';
                $fila->mensaje = 'Quítalo para continuar';
                $hayProblema = true;
            } else {
                $validacion = Modificadores::validar($f['producto'], Modificadores::parsear($f['opciones']));
                $fila->opciones = Modificadores::texto($validacion['lineas']);
                $fila->precioUnidad = round((float) $p->precio + $validacion['extra'], 2);

                if (!$validacion['ok']) {
                    // cambiaron las opciones del producto: el cliente debe editarlo
                    $fila->titulo = 'Revisa las opciones';
                    $fila->mensaje = $validacion['mensaje'];
                    $hayProblema = true;
                }
            }

            $subtotal += $fila->cantidad * $fila->precioUnidad;
            $resultado[] = $fila;
        }

        return ['filas' => $resultado, 'subtotal' => round($subtotal, 2), 'hayProblema' => $hayProblema];
    }

    /** Montos que muestra la app: subtotal, envío y total. */
    private function montos(float $subtotal, array $config): array
    {
        $envio = $this->costoEnvio($subtotal, $config);

        return [
            'subtotal' => $this->dinero($subtotal),
            'envio' => $this->dinero($envio),
            'total' => $this->dinero($subtotal + $envio),
            // texto para explicar el envío, ej. "Envío gratis en pedidos mayores a $6.00"
            'envio_texto' => $config['envio_costo'] > 0
                ? 'Envío gratis en pedidos mayores a $' . $this->dinero($config['envio_limite'])
                : null,
        ];
    }

    /**
     * Carrito: productos con precio actual, subtotal, envío y total.
     * success 1 = con productos, 2 = vacío.
     */
    public function verCarrito(Request $request)
    {
        $filas = $this->leerCarrito($request->carrito);
        if (count($filas) === 0) {
            return ['success' => 2];
        }

        $calculo = $this->calcular($filas);
        $config = $this->config();

        $productos = array_map(function ($f) {
            $totalFila = $f->cantidad * $f->precioUnidad;
            return [
                'carritoid' => $f->fila,
                'productoID' => $f->productoID,
                'nombre' => $f->nombre,
                'cantidad' => $f->cantidad,
                'imagen' => $f->imagen,
                'utiliza_imagen' => $f->utiliza_imagen,
                'activo' => $f->activo,
                'nota' => $f->nota,
                'opciones' => $f->opciones,
                'titulo' => $f->titulo,
                'mensaje' => $f->mensaje,
                'preciobase' => $this->dinero($f->precioBase),
                'preciounitario' => $this->dinero($f->precioUnidad),
                'precioformat' => $this->dinero($totalFila),
                'precio' => number_format($totalFila, 2, '.', ''),
            ];
        }, $calculo['filas']);

        return array_merge([
            'success' => 1,
            'estadoProductoGlobal' => $calculo['hayProblema'] ? 1 : 0,
            'producto' => $productos,
        ], $this->montos($calculo['subtotal'], $config));
    }

    /**
     * Resumen antes de enviar: productos, montos y estado del local.
     * success 2 = listo para mostrar, 3 = carrito vacío.
     */
    public function verOrden(Request $request)
    {
        $filas = $this->leerCarrito($request->carrito);
        if (count($filas) === 0) {
            return ['success' => 3];
        }

        $calculo = $this->calcular($filas);
        $config = $this->config();
        $estado = EstadoLocal::ahora();

        $resumen = array_map(fn ($f) => [
            'cantidad' => $f->cantidad,
            'nombre' => $f->nombre,
            'opciones' => $f->opciones,
            'nota' => $f->nota,
            'total' => $this->dinero($f->cantidad * $f->precioUnidad),
        ], $calculo['filas']);

        return array_merge([
            'success' => 2,
            'productos' => $resumen,
            // 1 = todos los productos se pueden pedir
            'minimo' => $calculo['hayProblema'] ? 0 : 1,
            'mensaje' => $calculo['hayProblema'] ? 'Revisa tu carrito: hay productos que no se pueden pedir.' : null,
            'abierto' => $estado['abierto'] ? 1 : 0,
            'estado_texto' => $estado['texto'],
            'estado_detalle' => $estado['motivo'] ?? 'Podrás enviar tu orden cuando el local abra.',
            'modo_pedido' => 2,
            'whatsapp' => $config['whatsapp'],
        ], $this->montos($calculo['subtotal'], $config));
    }

    /**
     * Arma el mensaje de WhatsApp con el pedido.
     * success 1/2 = local cerrado, 4 = no se puede enviar (mensaje), 5 = listo (url wa.me).
     */
    public function enviar(Request $request)
    {
        $config = $this->config();
        if (strlen($config['whatsapp']) < 8) {
            return ['success' => 4, 'titulo' => 'WhatsApp', 'mensaje' => 'Por el momento no se reciben pedidos.'];
        }

        $nombre = trim((string) $request->nombre);
        $telefono = preg_replace('/\D/', '', (string) $request->telefono);
        $direccion = trim((string) $request->direccion);
        $referencia = trim((string) $request->referencia);
        $nota = trim((string) $request->nota);

        if ($nombre === '' || $direccion === '' || strlen($telefono) < 8) {
            return ['success' => 4, 'titulo' => 'Dirección', 'mensaje' => 'Se necesita una dirección de entrega'];
        }

        // local cerrado (panel, día cerrado o fuera de horario)
        $estado = EstadoLocal::ahora();
        if (!$estado['abierto']) {
            $mensaje = $estado['texto'];
            if (!empty($estado['motivo'])) {
                $mensaje .= "\n" . $estado['motivo'];
            }
            return [
                'success' => !empty($estado['motivo']) ? 2 : 1,
                'titulo' => 'Local cerrado',
                'mensaje' => $mensaje,
            ];
        }

        $filas = $this->leerCarrito($request->carrito);
        if (count($filas) === 0) {
            return ['success' => 4, 'titulo' => 'Carrito', 'mensaje' => 'No hay productos'];
        }

        $calculo = $this->calcular($filas);
        foreach ($calculo['filas'] as $f) {
            if ($f->activo == 0) {
                return ['success' => 4, 'titulo' => 'Carrito', 'mensaje' => "«{$f->nombre}» no está disponible, quítalo del carrito"];
            }
            if ($f->titulo !== null) {
                return ['success' => 4, 'titulo' => 'Opciones', 'mensaje' => "Revisa las opciones de «{$f->nombre}»: {$f->mensaje}"];
            }
        }

        $subtotal = $calculo['subtotal'];
        $envio = $this->costoEnvio($subtotal, $config);

        // ── texto del pedido ──
        $t = "*Productos:*\n";
        foreach ($calculo['filas'] as $f) {
            $t .= "• {$f->cantidad}x {$f->nombre} - \$" . $this->dinero($f->cantidad * $f->precioUnidad) . "\n";
            foreach ((array) $f->opciones as $opcion) {
                $t .= "   - {$opcion}\n";
            }
            if (!empty($f->nota)) {
                $t .= "   Nota: {$f->nota}\n";
            }
        }

        $t .= "\nSubtotal: $" . $this->dinero($subtotal) . "\n";
        $t .= "Envío: " . ($envio > 0 ? '$' . $this->dinero($envio) : 'Gratis') . "\n";
        $t .= "*Total: $" . $this->dinero($subtotal + $envio) . "*\n\n";

        $t .= "*Entregar a:*\n";
        $t .= mb_substr($nombre, 0, 100) . "\n";
        $t .= "Tel: {$telefono}\n";
        $t .= mb_substr($direccion, 0, 400) . "\n";
        if ($referencia !== '') {
            $t .= "Referencia: " . mb_substr($referencia, 0, 400) . "\n";
        }

        if ($nota !== '') {
            $t .= "\n*Nota:* " . mb_substr($nota, 0, 300) . "\n";
        }

        return [
            'success' => 5,
            'numero' => $config['whatsapp'],
            'texto' => $t,
            'url' => 'https://wa.me/' . $config['whatsapp'] . '?text=' . rawurlencode($t),
        ];
    }
}
