<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket orden #{{ $infoOrden->id }}</title>
    {{-- Ticket para impresora térmica de 80 mm (el tamaño de página lo pone el controlador) --}}
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #000;
            padding: 4mm 4mm 0 4mm; /* zona imprimible del rollo: ~72 mm */
            line-height: 1.25;
        }
        .centro { text-align: center; }
        .negocio { font-size: 14pt; font-weight: bold; letter-spacing: 0.5pt; }
        .orden { font-size: 18pt; font-weight: bold; margin-top: 2mm; }
        .fecha { font-size: 9pt; margin-top: 1mm; }
        .linea { border-top: 1px dashed #000; margin: 3mm 0; }
        .linea-doble { border-top: 2px solid #000; margin: 3mm 0 2mm; }
        .etiqueta { font-size: 7.5pt; font-weight: bold; text-transform: uppercase; }
        .dato { font-size: 10pt; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
        .cant { width: 9mm; font-size: 13pt; font-weight: bold; }
        .prod { font-size: 11pt; font-weight: bold; }
        .precio { width: 16mm; text-align: right; font-size: 10pt; font-weight: bold; }
        .item td { padding-top: 2mm; }

        .grupo { font-size: 8.5pt; padding-top: 0.6mm; }
        .grupo b { font-size: 7.5pt; text-transform: uppercase; }
        .nota-prod { font-size: 9pt; font-weight: bold; margin-top: 1mm; padding: 0.8mm 1.5mm; border: 1px solid #000; }

        .total td { font-size: 14pt; font-weight: bold; }
        .nota-orden { border: 2px solid #000; padding: 2mm; margin-top: 3mm; font-size: 10pt; font-weight: bold; }
        .pie { font-size: 8pt; margin-top: 3mm; }
    </style>
</head>
<body>

    <div class="centro">
        <div class="negocio">EL TUNCAZO</div>
        <div class="orden">ORDEN #{{ $infoOrden->id }}</div>
        <div class="fecha">{{ $fecha }}</div>
    </div>

    <div class="linea"></div>

    {{-- cliente --}}
    <div class="etiqueta">Cliente</div>
    <div class="dato">{{ $infoDireccion->nombre ?? '' }}</div>
    @if(!empty($infoDireccion->telefono))
        <div>Tel: {{ $infoDireccion->telefono }}</div>
    @endif

    <div class="linea"></div>

    {{-- productos --}}
    <table>
        @foreach($lista as $dato)
            <tr class="item">
                <td class="cant">{{ $dato->cantidad }}x</td>
                <td class="prod">{{ $dato->nomproducto }}</td>
                <td class="precio">${{ $dato->multiplicado }}</td>
            </tr>
            @if(count($dato->grupos) > 0 || !empty($dato->nota))
                <tr>
                    <td></td>
                    <td colspan="2">
                        @foreach($dato->grupos as $grupo)
                            <div class="grupo">
                                <b>{{ $grupo['grupo'] }}:</b>
                                {{ collect($grupo['opciones'])->map(fn ($op) =>
                                    $op['opcion']
                                    . ($op['cantidad'] > 1 ? ' x' . $op['cantidad'] : '')
                                    . ($op['precio'] > 0 ? ' (+$' . number_format($op['precio'], 2) . ')' : '')
                                )->implode(', ') }}
                            </div>
                        @endforeach
                        @if(!empty($dato->nota))
                            <div class="nota-prod">NOTA: {{ $dato->nota }}</div>
                        @endif
                    </td>
                </tr>
            @endif
            @if($dato->cantidad > 1)
                <tr>
                    <td></td>
                    <td colspan="2" style="font-size: 8pt;">{{ $dato->cantidad }} x ${{ $dato->precio }} c/u</td>
                </tr>
            @endif
        @endforeach
    </table>

    <div class="linea-doble"></div>

    <table>
        <tr>
            <td style="font-size: 9pt;">{{ $unidades }} {{ $unidades == 1 ? 'producto' : 'productos' }}</td>
        </tr>
        <tr class="total">
            <td>TOTAL</td>
            <td style="text-align: right;">${{ $suma }}</td>
        </tr>
    </table>

    @if(!empty($infoOrden->nota))
        <div class="nota-orden">NOTA DE LA ORDEN:<br>{{ $infoOrden->nota }}</div>
    @endif

    <div class="linea"></div>

    {{-- entrega --}}
    <div class="etiqueta">Dirección de entrega</div>
    <div class="dato">{{ $infoDireccion->direccion ?? '' }}</div>
    @if(!empty($infoDireccion->punto_referencia))
        <div style="margin-top: 1mm;"><b>Ref:</b> {{ $infoDireccion->punto_referencia }}</div>
    @endif

    <div class="linea"></div>

    <div class="centro pie">¡Gracias por su compra!</div>

    {{-- marca el final: el controlador mide hasta aquí para cortar el papel --}}
    <div id="fin-ticket" style="height: 1px;"></div>
</body>
</html>
