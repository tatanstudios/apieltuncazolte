<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\Login\LoginController;
use App\Http\Controllers\Backend\Admin\Control\ControlController;
use App\Http\Controllers\Backend\Admin\Control\RolesController;
use App\Http\Controllers\Backend\Admin\Control\PermisosController;
use App\Http\Controllers\Backend\Admin\Perfil\PerfilController;
use App\Http\Controllers\Backend\Admin\Servicios\CategoriasController;
use App\Http\Controllers\Backend\Admin\Servicios\ModificadoresController;
use App\Http\Controllers\Backend\Admin\Clientes\ClientesController;
use App\Http\Controllers\Backend\Admin\Eventos\EventosController;
use App\Http\Controllers\Backend\Admin\Horario\HorarioController;
use App\Http\Controllers\Backend\Admin\Ordenes\OrdenesController;

// POLÍTICA DE PRIVACIDAD (pública, enlazada desde Google Play)
Route::view('/politica-privacidad', 'frontend.politica.privacidad')->name('politica.privacidad');

// INICIO
Route::get('/', [LoginController::class,'index'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

// --- CONTROL WEB ---
Route::get('/panel', [ControlController::class,'indexRedireccionamiento'])->name('admin.panel');

Route::get('/admin/roles/index', [RolesController::class,'index'])->name('admin.roles.index');
Route::get('/admin/roles/tabla', [RolesController::class,'tablaRoles']);
Route::get('/admin/roles/lista/permisos/{id}', [RolesController::class,'vistaPermisos']);
Route::get('/admin/roles/permisos/tabla/{id}', [RolesController::class,'tablaRolesPermisos']);
Route::post('/admin/roles/permiso/borrar', [RolesController::class, 'borrarPermiso']);
Route::post('/admin/roles/permiso/agregar', [RolesController::class, 'agregarPermiso']);
Route::get('/admin/roles/permisos/lista', [RolesController::class,'listaTodosPermisos']);
Route::get('/admin/roles/permisos-todos/tabla', [RolesController::class,'tablaTodosPermisos']);
Route::post('/admin/roles/borrar-global', [RolesController::class, 'borrarRolGlobal']);

// --- PERMISOS ---
Route::get('/admin/permisos/index', [PermisosController::class,'index'])->name('admin.permisos.index');
Route::get('/admin/permisos/tabla', [PermisosController::class,'tablaUsuarios']);
Route::post('/admin/permisos/nuevo-usuario', [PermisosController::class, 'nuevoUsuario']);
Route::post('/admin/permisos/info-usuario', [PermisosController::class, 'infoUsuario']);
Route::post('/admin/permisos/editar-usuario', [PermisosController::class, 'editarUsuario']);
Route::post('/admin/permisos/nuevo-rol', [PermisosController::class, 'nuevoRol']);
Route::post('/admin/permisos/extra-nuevo', [PermisosController::class, 'nuevoPermisoExtra']);
Route::post('/admin/permisos/extra-borrar', [PermisosController::class, 'borrarPermisoGlobal']);

// --- PERFIL ---
Route::get('/admin/editar-perfil/index', [PerfilController::class,'indexEditarPerfil'])->name('admin.perfil');
Route::post('/admin/editar-perfil/actualizar', [PerfilController::class, 'editarUsuario']);

// --- BLOQUE DE SERVICIOS ---
Route::get('/admin/bloques', [CategoriasController::class,'indexBloque'])->name('index.bloques');
Route::get('/admin/bloques/tablas/', [CategoriasController::class,'tablaBloque']);
Route::post('/admin/bloques/nuevo', [CategoriasController::class,'nuevoBloque']);
Route::post('/admin/bloques/informacion', [CategoriasController::class,'informacionBloque']);
Route::post('/admin/bloques/editar', [CategoriasController::class,'editarBloque']);
Route::post('/admin/bloques/ordenar', [CategoriasController::class,'ordenarBloque']);

// --- CATEGORIAS ---
Route::get('/admin/categorias/{id}', [CategoriasController::class,'indexCategorias']);
Route::get('/admin/categorias/tablas/{id}', [CategoriasController::class,'tablaCategorias']);
Route::post('/admin/categorias/nuevo', [CategoriasController::class,'nuevaCategorias']);
Route::post('/admin/categorias/informacion', [CategoriasController::class,'informacionCategorias']);
Route::post('/admin/categorias/editar', [CategoriasController::class,'editarCategorias']);
Route::post('/admin/categorias/ordenar', [CategoriasController::class,'ordenarCategorias']);

// --- PRODUCTOS ---
Route::get('/admin/productos/{id}', [CategoriasController::class,'indexProductos']);
Route::get('/admin/productos/tablas/{id}', [CategoriasController::class,'tablaProductos']);
Route::post('/admin/productos/nuevo', [CategoriasController::class,'nuevoProducto']);
Route::post('/admin/productos/informacion', [CategoriasController::class,'informacionProductos']);
Route::post('/admin/productos/editar', [CategoriasController::class,'editarProductos']);
Route::post('/admin/productos/ordenar', [CategoriasController::class,'ordenarProductos']);

// --- MODIFICADORES DE PRODUCTO ---
Route::get('/admin/productos/modificadores/{id}', [ModificadoresController::class,'index']);
Route::get('/admin/productos/modificadores/informacion/{id}', [ModificadoresController::class,'informacion']);
Route::post('/admin/productos/modificadores/guardar', [ModificadoresController::class,'guardar']);

// iniciar la orden y se envia notificacion al cliente
Route::post('/admin/ordenes/iniciar', [OrdenesController::class,'iniciarOrden']);

// PRODUCTOS DE LA ORDEN
Route::get('/admin/productos/ordenes/{id}', [OrdenesController::class,'indexProductosOrdenes']);
Route::get('/admin/productos/ordenes/tabla/{id}', [OrdenesController::class,'tablaOrdenesProducto']);


// --- ORDENES TODAS---
Route::get('/admin/ordenes/todas', [OrdenesController::class,'index'])->name('index.ordenes.todas');
Route::get('/admin/ordenes/todas/tablas', [OrdenesController::class,'tablaOrdenesTodas']);


// --- ORDENES CANCELADAS---
Route::get('/admin/ordenes-canceladas/todas', [OrdenesController::class,'indexOrdenesCanceladas'])->name('index.ordenes.canceladas');
Route::get('/admin/ordenes-canceladas/todas/tablas', [OrdenesController::class,'tablaOrdenesTodasCanceladas']);

Route::post('/admin/ordenes/cancelar', [OrdenesController::class,'cancelarOrden']);


// ORDENES PENDIENTES

Route::get('/admin/ordenes-pendientes/lista', [OrdenesController::class,'indexOrdenesPendientes'])->name('index.ordenes.pendientes');
Route::get('/admin/ordenes-pendientes/tabla/lista', [OrdenesController::class,'tablaOrdenesPendientes']);

Route::get('/admin/ordenes/ticket/{id}', [OrdenesController::class,'imprimirTicketV2']);


// Notificacion al cliente
// cancelar la orden

// ORDENES INICIADAS HOY
Route::get('/admin/ordenes-hoy/lista', [OrdenesController::class,'indexOrdenHoy'])->name('index.ordenes.hoy');
Route::get('/admin/ordenes-hoy/tabla/lista', [OrdenesController::class,'tablaOrdenesHoy']);


// --- HORARIO ---
Route::get('/admin/horario', [HorarioController::class,'indexHorario'])->name('index.horario');
Route::get('/admin/horario/tablas', [HorarioController::class,'tablaHorario']);
Route::post('/admin/horario/informacion', [HorarioController::class,'informacionHorario']);
Route::post('/admin/horario/editar', [HorarioController::class,'editarHorario']);


// --- CLIENTES ---
Route::get('/admin/cliente/lista-clientes-hoy', [ClientesController::class, 'indexRegistradosHoy'])->name('index.clientes.registrados.hoy');
Route::get('/admin/cliente/tablas/cliente-hoy', [ClientesController::class, 'tablaRegistradosHoy']);

Route::get('/admin/cliente/listado', [ClientesController::class, 'indexListaClientes'])->name('index.clientes.listado');
Route::get('/admin/cliente/tabla/listado', [ClientesController::class, 'tablaindexListaClientes']);
Route::post('/admin/cliente/informacion', [ClientesController::class, 'informacionCliente']);
Route::post('/admin/cliente/actualizar/informacion', [ClientesController::class, 'actualizarCliente']);

Route::get('/admin/cliente/lista/direcciones/{id}', [ClientesController::class, 'indexListaDirecciones']);
Route::get('/admin/cliente/lista/tabla-direcciones/{id}', [ClientesController::class, 'tablaIndexListaDirecciones']);

// --- CONFIGURACION ---
Route::get('/admin/configuracion/index', [HorarioController::class, 'indexConfiguracion'])->name('index.configuracion');
Route::get('/admin/configuracion/tablas', [HorarioController::class, 'tablaConfiguracion']);
Route::post('/admin/configuracion/informacion', [HorarioController::class,'informacionConfiguracion']);
Route::post('/admin/configuracion/editar', [HorarioController::class,'editarConfiguracion']);

// CERRAR APP
Route::get('/admin/cerrado/app/index', [HorarioController::class, 'indexCerradoApp'])->name('index.cerrar.app');
Route::get('/admin/cerrado/app/tabla', [HorarioController::class, 'tablaCerradoApp']);
Route::post('/admin/cerrado/app/informacion', [HorarioController::class, 'infoCerradoApp']);

Route::post('/admin/cerrado/app/actualizar', [HorarioController::class, 'actualizarCerradoApp']);

