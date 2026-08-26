<!DOCTYPE html>
<html lang="es">
<head>
    <title>El Tuncazo - Practicar Precios</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link href="{{ asset('images/icono-sistema.png') }}" rel="icon">
    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
    <link href="{{ asset('css/toastr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet">
</head>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
    html, body { height: 100%; }
    body {
        font-family: 'Roboto', sans-serif;
        background-image: linear-gradient(to top, #0f2027 0%, #203a43 50%, #2c5364 100%);
    }
    .demo-container {
        min-height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 30px 0;
    }
    .btn-lg {
        padding: 12px 26px;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    ::placeholder { font-size: 14px; letter-spacing: 0.5px; }
    .producto-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid #673AB7;
    }
    .producto-nombre {
        font-size: 15px;
        font-weight: 700;
        color: #333;
        margin-bottom: 8px;
    }
    .numero-producto {
        background: #673AB7;
        color: white;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        float: right;
    }
    .producto-imagen {
        width: 100%;
        max-height: 180px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 12px;
    }
</style>

<body>
<div class="demo-container">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 col-12 mx-auto">
                <div class="p-5 bg-white rounded shadow-lg">
                    <h3 class="mb-2 text-center pt-5">INGRESA LOS PRECIOS</h3>
                    <p class="text-center lead">{{ count($productos) }} productos seleccionados</p>

                    <form action="/admin/personales/mitienda/verificar" method="POST" id="formVerificar">
                        @csrf

                        @foreach ($productos as $index => $producto)
                            <div class="producto-card">
                                <div class="numero-producto">{{ $index + 1 }}</div>

                                {{-- Imagen si existe --}}
                                @if($producto->imagen)
                                    <img style="width: 150px; height: 150px; object-fit:cover; border-radius:8px; display:block; margin:0 auto 12px;" src="{{ asset('storage/imagenes2/' . $producto->imagen) }}"
                                         alt="{{ $producto->nombre }}"
                                         class="producto-imagen">
                                @endif

                                <div class="producto-nombre">{{ $producto->nombre }}</div>

                                <input type="hidden" name="producto_id[]" value="{{ $producto->id }}">

                                <div class="input-group" style="margin-top: 10px">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    </div>
                                    <input type="number" step="0.01" name="precio[]" class="form-control"
                                           placeholder="¿Cuál es el precio?" autocomplete="off" required>
                                </div>
                            </div>
                        @endforeach

                        <br>
                        <div class="form-group text-center">
                            <input type="button" value="VERIFICAR PRECIOS" onclick="verificar()"
                                   class="btn btn-lg w-100 shadow-lg" style="background: #673AB7; color: white">
                        </div>

                        <div class="form-group text-center">
                            <a href="/admin/personales/mitienda/index"
                               class="btn btn-lg w-100 shadow-lg" style="background: #e0e0e0; color: #333">
                                VOLVER
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/sweetalert2.all.min.js') }}" type="text/javascript"></script>

<script>
    document.querySelectorAll('input[name="precio[]"]').forEach(function(input) {
        input.addEventListener('blur', function() {
            var valor = this.value.trim();
            if (valor !== '' && !isNaN(valor)) {
                this.value = parseFloat(valor).toFixed(2);
            }
        });
    });

    function verificar() {
        var precios = document.querySelectorAll('input[name="precio[]"]');
        var todosLlenos = true;

        precios.forEach(function(input) {
            if (input.value.trim() === '') todosLlenos = false;
        });

        if (!todosLlenos) { toastr.error("Todos los precios son requeridos"); return; }

        precios.forEach(function(input) {
            var valor = input.value.trim();
            if (valor !== '' && !isNaN(valor)) {
                input.value = parseFloat(valor).toFixed(2);
            }
        });

        document.getElementById('formVerificar').submit();
    }
</script>
</body>
</html>
