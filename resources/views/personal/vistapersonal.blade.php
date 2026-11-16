<!DOCTYPE html>
<html lang="es">
<head>
    <title>El Tuncazo - Aprender Precios</title>
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
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .btn-lg {
        padding: 12px 26px;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    ::placeholder { font-size: 14px; letter-spacing: 0.5px; }
    .form-control-lg { font-size: 16px; padding: 25px 20px; }
</style>

<body>
<div class="demo-container">
    <div class="container">
        <div class="row" style="margin-top: 60px">
            <div class="col-lg-6 col-12 mx-auto">
                <div class="p-5 bg-white rounded shadow-lg">
                    <h3 class="mb-2 text-center pt-5">APRENDER PRECIOS</h3>
                    <p class="text-center lead">Cafe, Helados Y Pupusería El Tuncazo</p>

                    <p class="text-center text-muted" style="margin-top:15px">
                        Total de productos registrados: <strong>{{ $totalProductos }}</strong>
                    </p>

                    <form action="/admin/personales/mitienda/productos" method="POST" id="formCantidad">
                        @csrf

                        {{-- SELECT BLOQUE --}}
                        <div class="input-group form-group" style="margin-top: 25px">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                            </div>
                            <select name="bloque" id="bloque" class="form-control" onchange="actualizarTotal()">
                                <option value="todos">Todos los bloques</option>
                                <option value="1">Bloque 1</option>
                                <option value="2">Bloque 2</option>
                                <option value="3">Bloque 3</option>
                            </select>
                        </div>

                        {{-- INPUT CANTIDAD --}}
                        <div class="input-group form-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                            </div>
                            <input type="number" name="cantidad" id="cantidad" class="form-control"
                                   placeholder="¿Cuántos productos quieres practicar?"
                                   min="1" max="{{ $totalProductos }}"
                                   autocomplete="off" required>
                        </div>

                        <small class="text-muted d-block text-center mb-3" id="textoDisponibles">
                            Disponibles en selección: <strong id="numDisponibles">{{ $totalProductos }}</strong>
                        </small>

                        <div class="form-group text-center">
                            <input type="button" value="COMENZAR" onclick="iniciar()" id="btnIniciar"
                                   class="btn btn-lg w-100 shadow-lg" style="background: #673AB7; color: white">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/sweetalert2.all.min.js') }}" type="text/javascript"></script>

<script>
    // Totales por bloque desde el servidor
    var totalesPorBloque = {
        'todos': {{ $totalProductos }},
        '1':     {{ $totalBloque1 }},
        '2':     {{ $totalBloque2 }},
        '3':     {{ $totalBloque3 }},
    };

    var inputCantidad = document.getElementById("cantidad");
    inputCantidad.addEventListener("keyup", function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            iniciar();
        }
    });

    function actualizarTotal() {
        var bloque = document.getElementById('bloque').value;
        var disponibles = totalesPorBloque[bloque] || 0;

        document.getElementById('numDisponibles').textContent = disponibles;
        document.getElementById('cantidad').max = disponibles;
        document.getElementById('cantidad').value = '';
    }

    function iniciar() {
        var bloque     = document.getElementById('bloque').value;
        var cantidad   = document.getElementById('cantidad').value;
        var disponibles = totalesPorBloque[bloque] || 0;

        if (cantidad === '')              { toastr.error("Ingrese una cantidad"); return; }
        if (parseInt(cantidad) <= 0)      { toastr.error("La cantidad debe ser mayor a 0"); return; }
        if (parseInt(cantidad) > disponibles) {
            toastr.error("Solo hay " + disponibles + " productos en esta selección");
            return;
        }

        document.getElementById('formCantidad').submit();
    }
</script>
</body>
</html>
