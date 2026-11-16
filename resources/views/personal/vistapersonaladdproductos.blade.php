@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
@stop

<style>
    #card-header-color {
        background-color: #673AB7 !important;
    }
    #preview-imagen {
        max-width: 300px;
        max-height: 200px;
        object-fit: cover;
        border-radius: 6px;
        display: none;
        margin-top: 10px;
        border: 2px solid #673AB7;
    }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Agregar Producto</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header" id="card-header-color">
                        <h3 class="card-title" style="color: white">
                            <i class="fas fa-box mr-2"></i>Nuevo Producto
                        </h3>
                    </div>
                    <div class="card-body">

                        <div class="form-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text"
                                   id="nombre"
                                   maxlength="300"
                                   autocomplete="off"
                                   class="form-control"
                                   placeholder="Nombre del producto">
                        </div>

                        <div class="form-group">
                            <label for="precio">Precio <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text"
                                       id="precio"
                                       autocomplete="off"
                                       class="form-control"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Imagen <span class="text-muted">(opcional)</span></label>
                            <p class="text-muted" style="font-size:13px; margin-bottom:8px">
                                Formatos: .jpg, .jpeg, .png
                            </p>
                            <input type="file"
                                   id="imagen"
                                   accept="image/jpeg, image/jpg, image/png"
                                   onchange="previsualizarImagen(this)">
                            <br>
                            <img id="preview-imagen" src="#" alt="Vista previa">
                        </div>

                    </div>
                    <div class="card-footer text-right">
                        <button type="button" class="btn btn-secondary mr-2" onclick="limpiarFormulario()">
                            <i class="fas fa-undo"></i> Limpiar
                        </button>
                        <button type="button" class="btn btn-primary" onclick="guardarProducto()">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@extends('backend.menus.footerjs')
@section('archivos-js')

    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

    <script>

        function previsualizarImagen(input) {
            var preview = document.getElementById('preview-imagen');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        function limpiarFormulario() {
            document.getElementById('nombre').value = '';
            document.getElementById('precio').value = '';
            document.getElementById('imagen').value = '';
            var preview = document.getElementById('preview-imagen');
            preview.style.display = 'none';
            preview.src = '#';
        }

        function guardarProducto() {

            var nombre  = document.getElementById('nombre').value.trim();
            var precio  = document.getElementById('precio').value.trim();
            var imagen  = document.getElementById('imagen');

            var reglaDecimal = /^[0-9]\d*(\.\d+)?$/;

            if (nombre === '') {
                toastr.error('El nombre es requerido');
                return;
            }

            if (nombre.length > 300) {
                toastr.error('Nombre máximo 300 caracteres');
                return;
            }

            if (precio === '') {
                toastr.error('El precio es requerido');
                return;
            }

            var precioFormateado = parseFloat(precio).toFixed(2);

            if (!precioFormateado.match(reglaDecimal)) {
                toastr.error('El precio debe ser un número válido');
                return;
            }

            if (parseFloat(precioFormateado) <= 0) {
                toastr.error('El precio debe ser mayor a 0');
                return;
            }

            if (parseFloat(precioFormateado) > 1000000) {
                toastr.error('Precio máximo: $1,000,000');
                return;
            }

            if (imagen.files && imagen.files[0]) {
                if (!imagen.files[0].type.match('image/jpeg|image/jpg|image/png')) {
                    toastr.error('Formato de imagen permitido: .png .jpg .jpeg');
                    return;
                }
            }

            openLoading();

            var formData = new FormData();
            formData.append('nombre',  nombre);
            formData.append('precio',  precioFormateado);
            if (imagen.files && imagen.files[0]) {
                formData.append('imagen', imagen.files[0]);
            }

            axios.post('/admin/personales/mitienda/nuevoproducto', formData)
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        toastr.success('Producto registrado correctamente');
                        limpiarFormulario();
                    } else {
                        toastr.error('Error al guardar el producto');
                    }
                })
                .catch(() => {
                    closeLoading();
                    toastr.error('Error al guardar el producto');
                });
        }

    </script>

@endsection
