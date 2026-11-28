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
            <h1>Configuración</h1>
        </div>

    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header" id="card-header-color">
                <h3 class="card-title" style="color: white">Información</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="tablaDatatable">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- modal editar -->
<div class="modal fade" id="modalEditar">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Editar Opciones</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formulario-editar">
                    <div class="card-body">
                        <input type="hidden" id="id-editar">

                        <div class="form-group" style="margin-left:0px">
                            <label>Cerrado de Aplicación</label><br>
                            <label class="switch" style="margin-top:10px">
                                <input type="checkbox" id="toggle-cerrado">
                                <div class="slider round">
                                    <span class="on">Activar</span>
                                    <span class="off">Desactivar</span>
                                </div>
                            </label>
                        </div>

                        <div class="form-group">
                            <label>Mensaje de Cerrado</label>
                            <input type="text" maxlength="300" class="form-control" id="mensaje">
                        </div>

                        <hr>

                        <div class="form-group">
                            <label>Envío de Pedidos desde la App</label>
                            <select class="form-control" id="modo-pedido">
                                <option value="1">Solo orden normal (llega al panel)</option>
                                <option value="2">Solo por WhatsApp</option>
                                <option value="3">Ambas opciones (el cliente elige)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Número de WhatsApp que recibe los pedidos</label>
                            <input type="text" maxlength="20" class="form-control" id="whatsapp" placeholder="50375825072">
                            <small class="text-muted">Código de país + número, solo dígitos. Ej: 50375825072</small>
                        </div>

                        <div class="form-group">
                            <label>Mínimo de Consumo ($)</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="minimo">
                            <small class="text-muted">Solo para versiones anteriores de la app (con cuenta de cliente).</small>
                        </div>

                        <div class="form-group">
                            <label>Cobrar envío si el pedido es de hasta ($)</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="envio-limite">
                            <small class="text-muted">Ej. 6.00: pedidos de $6.00 o menos pagan envío; mayores van sin envío.</small>
                        </div>

                        <div class="form-group">
                            <label>Costo de envío ($)</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="envio-costo">
                            <small class="text-muted">0 = envío gratis siempre.</small>
                        </div>

                        <hr>

                        <div class="form-group" style="margin-left:0px">
                            <label>Habilitar Slider (carrusel de imágenes que se muestra al inicio)</label><br>
                            <label class="switch" style="margin-top:10px">
                                <input type="checkbox" id="toggle-carrusel">
                                <div class="slider round">
                                    <span class="on">Si</span>
                                    <span class="off">No</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnGuardar" onclick="editar()">Guardar</button>
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

    <script type="text/javascript">
        $(document).ready(function(){
            var ruta = "{{ URL::to('/admin/configuracion/tablas') }}";
            $('#tablaDatatable').load(ruta);
        });
    </script>

    <script>

        function recargar(){
            var ruta = "{{ url('/admin/configuracion/tablas') }}";
            $('#tablaDatatable').load(ruta);
        }

        function verInformacion(id){

            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post('/admin/configuracion/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){

                        $('#modalEditar').modal('show');
                        $('#id-editar').val(id);
                        $('#mensaje').val(response.data.info.mensaje_cerrado);

                        if(response.data.info.cerrado === 0){
                            $("#toggle-cerrado").prop("checked", false);
                        }else{
                            $("#toggle-cerrado").prop("checked", true);
                        }

                        if(response.data.info.activo_slider === 0){
                            $("#toggle-carrusel").prop("checked", false);
                        }else{
                            $("#toggle-carrusel").prop("checked", true);
                        }

                        $('#modo-pedido').val(response.data.info.modo_pedido || 1);
                        $('#whatsapp').val(response.data.info.whatsapp_numero);
                        $('#minimo').val(response.data.info.minimo_consumo);
                        $('#envio-limite').val(response.data.info.envio_limite);
                        $('#envio-costo').val(response.data.info.envio_costo);

                    }else{
                        toastr.error('Error al Buscar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al Buscar');
                    closeLoading();
                });
        }

        function editar() {
            var id = document.getElementById('id-editar').value;
            var mensaje = document.getElementById('mensaje').value;
            var toggleCerrado = document.getElementById('toggle-cerrado').checked ? 1 : 0;
            var cbslider = document.getElementById('toggle-carrusel').checked;
            var toggleCarrusel = cbslider ? 1 : 0;

            if(mensaje === ''){
                toastr.error('Mensaje de Cerrado es Requerido');
                return;
            }

            if(mensaje.length > 300){
                toastr.error('Mensaje de Cerrado máximo 300 caracteres');
                return;
            }

            var modoPedido = document.getElementById('modo-pedido').value;
            var whatsapp = document.getElementById('whatsapp').value.replace(/\D/g, '');
            var minimo = document.getElementById('minimo').value;

            if(modoPedido !== '1' && whatsapp.length < 8){
                toastr.error('Número de WhatsApp es requerido (código de país + número)');
                return;
            }

            if(minimo === '' || isNaN(minimo) || parseFloat(minimo) < 0){
                toastr.error('Mínimo de consumo no válido');
                return;
            }

            var envioLimite = document.getElementById('envio-limite').value;
            var envioCosto = document.getElementById('envio-costo').value;

            if(envioLimite === '' || isNaN(envioLimite) || parseFloat(envioLimite) < 0){
                toastr.error('Monto para cobrar envío no válido');
                return;
            }

            if(envioCosto === '' || isNaN(envioCosto) || parseFloat(envioCosto) < 0){
                toastr.error('Costo de envío no válido');
                return;
            }

            openLoading();

            let formData = new FormData();
            formData.append('id', id);
            formData.append('mensaje', mensaje);
            formData.append('cbcerrado', toggleCerrado);
            formData.append('cbcarrusel', toggleCarrusel);
            formData.append('modo_pedido', modoPedido);
            formData.append('whatsapp', whatsapp);
            formData.append('minimo', minimo);
            formData.append('envio_limite', envioLimite);
            formData.append('envio_costo', envioCosto);

            axios.post('/admin/configuracion/editar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if (response.data.success === 1) {
                        $('#modalEditar').modal('hide');
                        toastr.success('Información Actualizada');
                        recargar();
                    } else if (response.data.success === 3) {
                        toastr.error('Número de WhatsApp no válido');
                    } else {
                        toastr.error('Error al Editar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al Editar');
                    closeLoading();
                });
        }

    </script>


@endsection
