<!DOCTYPE html>
<html lang="es">
<head>
    <title>El Tuncazo - Resultados</title>
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
    html, body {
        height: 100%;
    }
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
    .image-size-small {
        width: 140px;
        margin: 0 auto;
    }
    .image-size-small img {
        width: 140px;
        margin-bottom: -70px;
    }
    .resultado-card {
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
    }
    .resultado-correcto {
        background: #e8f5e9;
        border-left: 4px solid #4CAF50;
    }
    .resultado-incorrecto {
        background: #ffebee;
        border-left: 4px solid #f44336;
    }
    .producto-info {
        font-size: 13px;
        color: #555;
        margin-bottom: 3px;
    }
    .producto-nombre {
        font-size: 15px;
        font-weight: 700;
        color: #333;
        margin-bottom: 8px;
    }
    .precio-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 14px;
        margin-right: 5px;
    }
    .precio-correcto {
        background: #4CAF50;
        color: white;
    }
    .precio-incorrecto {
        background: #f44336;
        color: white;
    }
    .precio-real {
        background: #2196F3;
        color: white;
    }
    .resumen-box {
        background: #f3e5f5;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        text-align: center;
    }
    .resumen-numero {
        font-size: 36px;
        font-weight: 700;
    }
    .resumen-label {
        font-size: 13px;
        color: #555;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .icono-resultado {
        float: right;
        font-size: 22px;
    }
</style>

<body>

<div class="demo-container">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 col-12 mx-auto">

                <div class="p-5 bg-white rounded shadow-lg">
                    <h3 class="mb-2 text-center pt-5">RESULTADOS</h3>
                    <p class="text-center lead">Verificación de precios</p>

                    {{-- RESUMEN --}}
                    <div class="row" style="margin-top:20px">
                        <div class="col-4">
                            <div class="resumen-box">
                                <div class="resumen-numero">{{ $total }}</div>
                                <div class="resumen-label">Total</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="resumen-box" style="background: #e8f5e9;">
                                <div class="resumen-numero" style="color: #4CAF50;">{{ $correctas }}</div>
                                <div class="resumen-label">Correctas</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="resumen-box" style="background: #ffebee;">
                                <div class="resumen-numero" style="color: #f44336;">{{ $incorrectas }}</div>
                                <div class="resumen-label">Incorrectas</div>
                            </div>
                        </div>
                    </div>

                    {{-- NOTA --}}
                    @php
                        $porcentaje = $total > 0 ? round(($correctas / $total) * 100) : 0;
                    @endphp

                    <div class="text-center" style="margin-bottom: 20px;">
                        @if($porcentaje == 100)
                            <h4 style="color: #4CAF50;"><i class="fas fa-trophy"></i> ¡PERFECTO! 100%</h4>
                        @elseif($porcentaje >= 70)
                            <h4 style="color: #FF9800;"><i class="fas fa-thumbs-up"></i> ¡Buen trabajo! {{ $porcentaje }}%</h4>
                        @else
                            <h4 style="color: #f44336;"><i class="fas fa-redo"></i> Sigue practicando {{ $porcentaje }}%</h4>
                        @endif
                    </div>

                    {{-- DETALLE --}}
                    @foreach ($resultados as $index => $resultado)
                        <div class="resultado-card {{ $resultado['es_correcta'] ? 'resultado-correcto' : 'resultado-incorrecto' }}">

                            @if($resultado['es_correcta'])
                                <span class="icono-resultado" style="color: #4CAF50;"><i class="fas fa-check-circle"></i></span>
                            @else
                                <span class="icono-resultado" style="color: #f44336;"><i class="fas fa-times-circle"></i></span>
                            @endif

                            <div class="producto-info"><strong>Código:</strong> {{ $resultado['alternativo'] }}</div>
                            <div class="producto-nombre">{{ $resultado['nombre'] }}</div>
                            <div class="producto-info"><strong>Marca:</strong> {{ $resultado['marca'] }} | <strong>Unidad:</strong> {{ $resultado['unidad'] }}</div>

                            <div style="margin-top: 10px;">
                                <span class="precio-badge {{ $resultado['es_correcta'] ? 'precio-correcto' : 'precio-incorrecto' }}">
                                    Tu precio: ${{ $resultado['precio_ingresado'] }}
                                </span>

                                @if(!$resultado['es_correcta'])
                                    <span class="precio-badge precio-real">
                                        Real: {{ $resultado['precio_real'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <br>
                    <div class="form-group text-center">
                        <a href="/admin/personales/mitienda/index"
                           class="btn btn-lg w-100 shadow-lg" style="background: #673AB7; color: white">
                            <i class="fas fa-redo"></i> VOLVER A INTENTAR
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/sweetalert2.all.min.js') }}" type="text/javascript"></script>

</body>
</html>
