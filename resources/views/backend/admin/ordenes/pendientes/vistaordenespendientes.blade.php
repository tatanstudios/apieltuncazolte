@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/estiloToggle.css') }}" type="text/css" rel="stylesheet" />

@stop

<style>
    table{
        /*Ajustar tablas*/
        table-layout:fixed;
    }

    #card-header-color {
        background-color: #673AB7 !important;
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <h1>Ordenes Pendientes</h1>
        </div>

        <button type="button" onclick="recargar()" class="btn btn-success btn-sm">
            <i class="fas fa-pencil-alt"></i>
            Recargar
        </button>

        <div class="form-group" style="width: 25%">
            <label>Cronometro</label>
            <label id="contador"></label>
        </div>


    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header" id="card-header-color">
                <h3 class="card-title" style="color: white">Listado de Ordenes</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="tablaDatatable">
                            @include('backend.admin.ordenes.pendientes.tablaordenespendientes')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<div class="modal fade" id="modalCliente" style="z-index:1000000000">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cliente</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario-cliente">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="form-group">
                                    <label>Zona</label>
                                    <input type="text" readonly class="form-control" id="zona">
                                </div>

                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" readonly class="form-control" id="nombre">
                                </div>

                                <div class="form-group">
                                    <label>Dirección</label>
                                    <input type="text" readonly class="form-control" id="direccion">
                                </div>

                                <div class="form-group">
                                    <label>Punto de Referencia</label>
                                    <input type="text" readonly class="form-control" id="puntoref">
                                </div>


                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <input type="text" readonly class="form-control" id="telefono">
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalCancelar" style="z-index:1000000000">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cancelar Orden</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario-cancelar">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="form-group">
                                    <label>Explicación al Cliente</label>
                                    <input type="hidden" id="id-cancelar">
                                    <input type="text" maxlength="600" class="form-control" id="nombre-cancelar">
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="cancelarOrden()">Cancelar</button>
            </div>
        </div>
    </div>
</div>


@extends('backend.menus.footerjs')
@section('archivos-js')

    <script src="{{ asset('js/jquery.dataTables.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.js') }}" type="text/javascript"></script>

    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

    <script>

        var SEGUNDOS_RECARGA = 30;
        var segundos = SEGUNDOS_RECARGA;
        var cargando = false;
        var sesionExpirada = false;
        var ultimoIdVisto = null; // mayor # de orden ya mostrado, para detectar ordenes nuevas
        var tabla = null;
        var audioCtx = null;

        $(document).ready(function(){

            // los errores se manejan en la funcion ajax, no con alert()
            $.fn.dataTable.ext.errMode = 'none';

            tabla = $('#tabla').DataTable({
                "ajax": function (data, callback) {
                    cargarDatos(callback);
                },
                "columns": [
                    { "data": "id" },
                    { "data": null, "render": function (d, type, row) {
                            // se ordena por la fecha real, se muestra la fecha formateada
                            return (type === 'sort' || type === 'type') ? row.fecha_orden : row.fecha_texto;
                        }},
                    { "data": "precio_consumido" },
                    { "data": "nota", "defaultContent": "", "render": $.fn.dataTable.render.text() },
                    { "data": "cliente", "defaultContent": "", "render": $.fn.dataTable.render.text() },
                    { "data": "direccion", "defaultContent": "", "render": $.fn.dataTable.render.text() },
                    { "data": "referencia", "defaultContent": "", "render": $.fn.dataTable.render.text() },
                    { "data": "telefono", "defaultContent": "", "render": $.fn.dataTable.render.text() },
                    { "data": "id", "orderable": false, "searchable": false, "render": function (id) {
                            return '<button type="button" class="btn btn-primary btn-xs" onclick="imprimirTicket(' + id + ')">' +
                                '<i class="fas fa-print" title="Ticket"></i>&nbsp; Ticket</button>';
                        }},
                    { "data": "id", "orderable": false, "searchable": false, "render": function (id) {
                            return '<button type="button" class="btn btn-success btn-xs" onclick="informacionProducto(' + id + ')">' +
                                '<i class="fas fa-shopping-cart" title="Productos"></i>&nbsp; Productos</button><br><br>' +
                                '<button type="button" class="btn btn-warning btn-xs" onclick="informacionIniciar(' + id + ')">' +
                                '<i class="fas fa-cookie" title="Iniciar"></i>&nbsp; Iniciar</button><br><br>' +
                                '<button type="button" class="btn btn-danger btn-xs" onclick="informacionCancelar(' + id + ')">' +
                                '<i class="fas fa-info" title="Cancelar"></i>&nbsp; Cancelar</button>';
                        }}
                ],
                "order": [[0, "desc"]],
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, 100, 150, -1], [10, 25, 50, 100, 150, "Todo"]],
                "language": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningún dato disponible en esta tabla",
                    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix": "",
                    "sSearch": "Buscar:",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                }
            });

            // el navegador solo permite sonido despues de que el usuario interactua con la pagina
            $(document).one('click keydown', prepararSonido);

            mostrarContador();
            setInterval(tickContador, 1000);
        });

        function cargarDatos(callback){
            cargando = true;

            $.ajax({
                url: "{{ url('/admin/ordenes-pendientes/tabla/lista') }}",
                dataType: 'json',
                cache: false
            })
                .done(function (respuesta) {
                    revisarOrdenesNuevas(respuesta.data);
                    callback(respuesta);
                })
                .fail(function (xhr) {
                    if(xhr.status === 401 || xhr.status === 419){
                        sesionVencida();
                    }else{
                        toastr.error('No se pudo actualizar el listado, se reintentará');
                    }
                    // en el primer intento se deja la tabla vacia; en recargas se conservan los datos
                    if(ultimoIdVisto === null){
                        callback({ data: [] });
                    }
                })
                .always(function () {
                    cargando = false;
                    segundos = SEGUNDOS_RECARGA;
                    mostrarContador();
                });
        }

        // recarga sin perder busqueda, pagina ni cantidad de registros
        function recargar(){
            if(cargando || sesionExpirada || tabla === null) return;
            segundos = SEGUNDOS_RECARGA;
            mostrarContador();
            tabla.ajax.reload(null, false);
        }

        function tickContador(){
            if(cargando || sesionExpirada) return;
            segundos--;
            if(segundos <= 0){
                recargar();
            }else{
                mostrarContador();
            }
        }

        function mostrarContador(){
            var texto = sesionExpirada ? 'detenido' : (cargando ? 'actualizando...' :
                '0:' + (segundos < 10 ? '0' : '') + segundos);
            document.getElementById('contador').innerHTML = texto;
        }

        function revisarOrdenesNuevas(lista){
            var maximo = 0;
            var nuevas = 0;

            $.each(lista, function (i, orden) {
                var id = parseInt(orden.id);
                if(id > maximo) maximo = id;
                if(ultimoIdVisto !== null && id > ultimoIdVisto) nuevas++;
            });

            if(nuevas > 0){
                toastr.info(nuevas === 1 ? 'Llegó 1 orden nueva' : 'Llegaron ' + nuevas + ' órdenes nuevas');
                sonarAviso();
            }

            if(ultimoIdVisto === null || maximo > ultimoIdVisto){
                ultimoIdVisto = maximo;
            }
        }

        function sesionVencida(){
            sesionExpirada = true;
            mostrarContador();

            Swal.fire({
                title: 'Sesión expirada',
                text: 'Inicia sesión de nuevo para seguir recibiendo órdenes',
                icon: 'warning',
                allowOutsideClick: false,
                confirmButtonText: 'Iniciar sesión'
            }).then(function () {
                window.location.href = "{{ route('login') }}";
            });
        }

        function prepararSonido(){
            try {
                var Ctx = window.AudioContext || window.webkitAudioContext;
                if(Ctx && audioCtx === null) audioCtx = new Ctx();
                if(audioCtx && audioCtx.state === 'suspended') audioCtx.resume();
            } catch (e) { audioCtx = null; }
        }

        function sonarAviso(){
            if(audioCtx === null) return;
            try {
                [0, 0.25].forEach(function (inicio) {
                    var osc = audioCtx.createOscillator();
                    var gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.value = 880;
                    gain.gain.value = 0.2;
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(audioCtx.currentTime + inicio);
                    osc.stop(audioCtx.currentTime + inicio + 0.18);
                });
            } catch (e) {}
        }


        function informacion(id){
            openLoading();
            document.getElementById("formulario-cliente").reset();

            axios.post('/admin/ordenes/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        $('#zona').val(response.data.zona);
                        $.each(response.data.cliente, function( key, val ){
                            $('#nombre').val(val.nombre);
                            $('#direccion').val(val.direccion);
                            $('#telefono').val(val.telefono)
                            $('#puntoref').val(val.punto_referencia)
                        });

                        $('#modalCliente').modal('show');
                    }else{
                        toastr.error('Información no encontrada');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    errorPeticion(error, 'Información no encontrada');
                });
        }

        function informacionProducto(id){
            window.location.href="{{ url('/admin/productos/ordenes') }}/"+id;
        }


        // iniciar la orden
        function informacionIniciar(id){

            Swal.fire({
                title: 'Iniciar Orden?',
                text: "",
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    iniciarOrden(id);
                }
            })
        }

        function iniciarOrden(id){

            openLoading();
            document.getElementById("formulario-cliente").reset();

            axios.post('/admin/ordenes/iniciar',{
                'id': id
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        recargar();

                        // si el cliente cancelo la orden

                        Swal.fire({
                            title: 'ORDEN CANCELADA',
                            text: "El Cliente Cancelo la Orden",
                            icon: 'error',
                            showCancelButton: false,
                            confirmButtonColor: '#ff0000',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Aceptar'
                        })

                    }
                    else  if(response.data.success === 2){

                        recargar();

                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: 'Orden Iniciada',
                            showConfirmButton: false,
                            timer: 1200
                        })

                    }
                    else if(response.data.success === 3){
                        recargar();
                        toastr.warning('La orden ya estaba iniciada');
                    }
                    else{
                        recargar();
                        toastr.error('Orden no encontrada');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    errorPeticion(error, 'Reintentar de nuevo');
                });
        }


        function imprimirTicket(id){

            window.open("{{ URL::to('admin/ordenes/ticket') }}/" + id);

        }

        function informacionCancelar(id){

            document.getElementById("formulario-cancelar").reset();
            $('#id-cancelar').val(id);
            $('#modalCancelar').modal('show');
        }


        function cancelarOrden(){

            var nombre = document.getElementById('nombre-cancelar').value;
            var id = document.getElementById('id-cancelar').value;


            if(nombre.length > 600){
                toastr.error('Nota máximo 600 caracteres');
                return;
            }

            openLoading();

            var formData = new FormData();
            formData.append('id', id);
            formData.append('nombre', nombre);

            axios.post('/admin/ordenes/cancelar', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        $('#modalCancelar').modal('hide');
                        toastr.success('Cancelada correctamente');
                        recargar();
                    }
                    else if (response.data.success === 2) {
                        $('#modalCancelar').modal('hide');
                        toastr.warning('La orden ya estaba cancelada');
                        recargar();
                    }
                    else {
                        toastr.error('Error al cancelar');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    errorPeticion(error, 'Error al cancelar');
                });
        }

        function errorPeticion(error, mensaje){
            var status = error.response ? error.response.status : 0;
            if(status === 401 || status === 419){
                sesionVencida();
            }else{
                toastr.error(mensaje);
            }
        }



    </script>


@endsection
