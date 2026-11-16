{{-- Productos de una orden, pensado para leerlo rápido en cocina: cantidad grande, opciones por bloque y notas resaltadas --}}
<style>
    .po-lista { display: flex; flex-direction: column; gap: 12px; }
    .po-item { display: flex; gap: 14px; border: 1px solid #e6e3ee; border-radius: 12px; padding: 14px 16px; background: #fff; }
    .po-cant { flex: 0 0 auto; min-width: 54px; height: 54px; border-radius: 12px; background: #673AB7; color: #fff;
        font-size: 1.45rem; font-weight: 700; display: flex; align-items: center; justify-content: center; padding: 0 8px; }
    .po-cuerpo { flex: 1 1 auto; min-width: 0; }
    .po-nombre { font-size: 1.15rem; font-weight: 700; color: #1f1b24; margin: 0 0 6px; line-height: 1.25; }
    .po-grupo { display: flex; flex-wrap: wrap; align-items: baseline; gap: 6px; margin-bottom: 6px; }
    .po-grupo-nombre { font-size: .8rem; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; color: #7a7585; min-width: 150px; }
    .po-opcion { display: inline-flex; align-items: center; gap: 4px; background: #f1edf8; color: #3d2a66; border-radius: 999px;
        padding: 3px 10px; font-size: .92rem; font-weight: 600; }
    .po-opcion .po-x { background: #673AB7; color: #fff; border-radius: 999px; padding: 0 6px; font-size: .78rem; }
    .po-opcion .po-mas { color: #2e7d32; font-weight: 600; font-size: .82rem; }
    .po-nota { margin-top: 8px; background: #fff8e1; border-left: 4px solid #ffb300; border-radius: 6px; padding: 6px 10px;
        font-size: .95rem; color: #5d4300; }
    .po-precios { flex: 0 0 150px; text-align: right; border-left: 1px dashed #e6e3ee; padding-left: 14px; }
    .po-precios .po-fila { font-size: .85rem; color: #7a7585; }
    .po-precios .po-unit { font-size: .9rem; color: #444; margin-top: 2px; }
    .po-precios .po-total { font-size: 1.25rem; font-weight: 700; color: #673AB7; margin-top: 6px; }
    .po-cab { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 10px; align-items: center; margin-bottom: 14px; }
    .po-nota-orden { background: #fff8e1; border: 1px solid #ffe082; border-radius: 10px; padding: 10px 14px; margin-bottom: 14px; color: #5d4300; }
    .po-resumen { display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #ece9f3;
        margin-top: 16px; padding-top: 14px; }
    .po-resumen .po-gran-total { font-size: 1.6rem; font-weight: 700; color: #1f1b24; }
    @media (max-width: 700px) {
        .po-item { flex-wrap: wrap; }
        .po-precios { flex: 1 1 100%; border-left: 0; border-top: 1px dashed #e6e3ee; padding: 8px 0 0; text-align: left; }
        .po-grupo-nombre { min-width: 0; width: 100%; }
    }
</style>

<div class="po-cab">
    <div style="color:#555">
        <b>{{ count($lista) }}</b> {{ count($lista) === 1 ? 'producto' : 'productos' }} ·
        <b>{{ $unidades }}</b> {{ $unidades === 1 ? 'unidad' : 'unidades' }}
    </div>
    @if($orden)
        <button type="button" class="btn btn-primary btn-sm" onclick="window.open('{{ url('/admin/ordenes/ticket/'.$orden->id) }}', '_blank')">
            <i class="fas fa-print"></i>&nbsp; Imprimir ticket
        </button>
    @endif
</div>

@if($orden && !empty($orden->nota))
    <div class="po-nota-orden"><i class="fas fa-sticky-note"></i>&nbsp; <b>Nota de la orden:</b> {{ $orden->nota }}</div>
@endif

<div class="po-lista">
    @foreach($lista as $dato)
        <div class="po-item">
            <div class="po-cant">{{ $dato->cantidad }}x</div>

            <div class="po-cuerpo">
                <p class="po-nombre">{{ $dato->nombre }}</p>

                @foreach($dato->grupos as $grupo)
                    <div class="po-grupo">
                        <span class="po-grupo-nombre">{{ $grupo['grupo'] }}</span>
                        @foreach($grupo['opciones'] as $op)
                            <span class="po-opcion">
                                {{ $op['opcion'] }}
                                @if($op['cantidad'] > 1)<span class="po-x">x{{ $op['cantidad'] }}</span>@endif
                                @if($op['precio'] > 0)<span class="po-mas">+${{ number_format($op['precio'], 2) }}</span>@endif
                            </span>
                        @endforeach
                    </div>
                @endforeach

                @if(!empty($dato->nota))
                    <div class="po-nota"><i class="fas fa-comment-dots"></i>&nbsp; {{ $dato->nota }}</div>
                @endif
            </div>

            <div class="po-precios">
                @if($dato->extra)
                    <div class="po-fila">Base ${{ $dato->precio_base }}</div>
                    <div class="po-fila">Opciones +${{ $dato->extra }}</div>
                @endif
                <div class="po-unit">${{ $dato->precio }} c/u</div>
                <div class="po-total">${{ $dato->total }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="po-resumen">
    <span style="font-size:1.1rem;color:#555">Total de la orden</span>
    <span class="po-gran-total">${{ $suma }}</span>
</div>
