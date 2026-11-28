<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\Api\Cliente\ApiClienteController;
use App\Http\Controllers\Backend\Api\Cliente\ApiRegistroController;
use App\Http\Controllers\Backend\Api\Cliente\ApiRecuperacionController;
use App\Http\Controllers\Backend\Api\Servicios\ApiServiciosController;
use App\Http\Controllers\Backend\Api\Servicios\ApiZonasServiciosController;
use App\Http\Controllers\Backend\Api\Perfil\ApiPerfilController;
use App\Http\Controllers\Backend\Api\Productos\ApiProductosController;
use App\Http\Controllers\Backend\Api\Carrito\ApiCarritoController;
use App\Http\Controllers\Backend\Api\Carrito\ApiPedidoWhatsappController;
use App\Http\Controllers\Backend\Api\Ordenes\ApiOrdenesController;


use App\Http\Middleware\ClienteDelToken;


// ===== RUTAS PÚBLICAS (sin sesión) =====

// --- CLIENTES ---
Route::post('cliente/login', [ApiClienteController::class, 'loginCliente']);
Route::post('cliente/registro', [ApiRegistroController::class, 'registroCliente']);

// --- RECUPERAR CONTRASEÑA (código por correo) ---
// throttle: máximo 10 peticiones por minuto por IP
Route::middleware('throttle:10,1')->group(function () {
    Route::post('cliente/recuperar/enviar-codigo', [ApiRecuperacionController::class, 'enviarCodigo']);
    Route::post('cliente/recuperar/verificar-codigo', [ApiRecuperacionController::class, 'verificarCodigo']);
    Route::post('cliente/recuperar/cambiar-password', [ApiRecuperacionController::class, 'cambiarPassword']);
});

// --- MENÚ Y PRODUCTOS (se pueden ver sin iniciar sesión) ---
Route::post('cliente/lista/servicios-bloque', [ApiZonasServiciosController::class, 'listadoBloque']);
Route::post('cliente/servicios/listado/menu', [ApiServiciosController::class, 'listadoMenuVertical']);
Route::post('cliente/informacion/producto', [ApiProductosController::class, 'infoProductoIndividual']);

// --- PEDIDO POR WHATSAPP SIN CUENTA (el carrito y la dirección viven en el teléfono) ---
Route::middleware('throttle:60,1')->group(function () {
    Route::post('publico/carrito/ver', [ApiPedidoWhatsappController::class, 'verCarrito']);
    Route::post('publico/orden/ver', [ApiPedidoWhatsappController::class, 'verOrden']);
    Route::post('publico/orden/whatsapp', [ApiPedidoWhatsappController::class, 'enviar']);
});


// ===== RUTAS CON SESIÓN =====
// Requieren "Authorization: Bearer <token>" (lo entrega login/registro).
// ClienteDelToken reemplaza "id" y "clienteid" por el cliente dueño del token.
Route::middleware(['auth:sanctum', ClienteDelToken::class])->group(function () {

    Route::post('cliente/logout', [ApiClienteController::class, 'logoutCliente']);

    // --- PERFIL ---
    Route::post('cliente/actualizar/password', [ApiClienteController::class, 'actualizarPasswordCliente']);
    Route::post('cliente/listado/direcciones', [ApiPerfilController::class, 'listadoDeDirecciones']);
    Route::post('cliente/seleccionar/direccion', [ApiPerfilController::class, 'seleccionarDireccion']);
    Route::post('cliente/eliminar/direccion',  [ApiPerfilController::class, 'eliminarDireccion']);
    Route::post('cliente/nueva/direccion', [ApiPerfilController::class, 'nuevaDireccionCliente']);
    Route::post('cliente/perfil/cambiar-password', [ApiPerfilController::class, 'cambiarPasswordPerfil']);
    Route::post('cliente/perfil/ver-correo', [ApiClienteController::class, 'verCorreoCliente']);
    Route::post('cliente/perfil/actualizar-correo', [ApiClienteController::class, 'actualizarCorreoCliente']);

    // --- CARRITO DE COMPRAS ---
    Route::post('cliente/carrito/producto/agregar', [ApiProductosController::class, 'agregarProductoCarritoTemporal']);
    Route::post('cliente/carrito/ver/orden', [ApiCarritoController::class, 'verCarritoDeCompras']);
    Route::post('cliente/carrito/borrar/orden', [ApiCarritoController::class, 'borrarCarritoDeCompras']);
    Route::post('cliente/carrito/ver/producto', [ApiCarritoController::class, 'verProductoCarritoEditar']);
    Route::post('cliente/carrito/eliminar/producto', [ApiCarritoController::class, 'borrarProductoDelCarrito']);
    Route::post('cliente/carrito/cambiar/cantidad', [ApiCarritoController::class, 'editarCantidadProducto']);
    Route::post('cliente/carrito/ver/proceso-orden', [ApiCarritoController::class, 'verOrdenAProcesarCliente']);
    Route::post('cliente/proceso/orden/estado-1', [ApiCarritoController::class, 'procesarOrdenEstado1']);
    Route::post('cliente/proceso/orden/whatsapp', [ApiCarritoController::class, 'procesarOrdenWhatsapp']);

    // --- ORDENES ENVIADAS ---
    Route::post('cliente/ver/ordenes-activas',  [ApiOrdenesController::class, 'ordenesActivas']);
    Route::post('cliente/ver/estado-orden',  [ApiOrdenesController::class, 'estadoOrdenesActivas']);
    Route::post('cliente/listado/productos/ordenes',  [ApiOrdenesController::class, 'listadoProductosOrdenes']);
    Route::post('cliente/proceso/orden/cancelar',  [ApiOrdenesController::class, 'cancelarOrdenCliente']);
    Route::post('cliente/proceso/completar/orden',  [ApiOrdenesController::class, 'completarOrden']);
});
