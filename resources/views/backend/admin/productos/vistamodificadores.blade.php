@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
@stop

<style>
    #card-header-color { background-color: #673AB7 !important; }

    .mod-titulo { font-weight: 700; font-size: 1.35rem; margin: 0; }
    .mod-bloque { border: 1px solid #e3e1ea; border-radius: 14px; margin-bottom: 18px; background: #fff; }
    .mod-bloque-cab { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 14px 16px;
        background: #f8f7fb; border-bottom: 1px solid #ecebf1; border-radius: 14px 14px 0 0; }
    .mod-num { font-weight: 700; color: #555; min-width: 22px; }
    .mod-bloque-cab .nombre-bloque { flex: 1 1 200px; max-width: 280px; font-weight: 600; }
    .mod-segmento { display: inline-flex; border: 1px solid #d6d3df; border-radius: 10px; padding: 3px; background: #fff; }
    .mod-segmento button { border: 0; background: transparent; padding: 5px 12px; border-radius: 8px; font-size: .9rem; color: #444; }
    .mod-segmento button.sel-opcional { background: #6c757d; color: #fff; }
    .mod-segmento button.sel-requerido { background: #e8112d; color: #fff; }
    .mod-flecha { border: 0; background: transparent; color: #555; padding: 4px 8px; }
    .mod-flecha:disabled { color: #ccc; }
    .mod-quitar { color: #e8112d; border: 0; background: transparent; margin-left: auto; }
    .mod-cuerpo { padding: 16px; }
    .mod-minmax { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
    .mod-minmax label { margin: 0; font-size: .9rem; color: #555; }
    .mod-minmax input { width: 80px; }
    .mod-resumen { font-size: .82rem; color: #6b7c3a; margin: 10px 0 14px; }
    .mod-opcion { border: 1px solid #e6e4ec; border-radius: 12px; padding: 12px; margin-bottom: 10px; }
    .mod-opcion.inactiva { opacity: .55; }
    .mod-fila { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .mod-fila .nombre-opcion { flex: 1 1 180px; min-width: 0; width: auto; }
    .mod-precio { display: flex; align-items: center; border: 1px solid #ced4da; border-radius: .25rem;
        padding-left: 8px; flex: 0 0 130px; height: calc(2.25rem + 2px); background: #fff; }
    .mod-precio span { color: #888; font-size: .9rem; }
    .mod-precio input { border: 0; width: 100%; padding: 6px 8px; outline: none; background: transparent; }
    .mod-fila .form-control.mod-maxop { flex: 0 0 72px; width: 72px; }
    .mod-minmax .form-control { flex: 0 0 80px; width: 80px; }
    .mod-etiqueta { font-size: .9rem; color: #555; white-space: nowrap; }
    .mod-x { border: 0; background: transparent; color: #999; font-size: 1.3rem; line-height: 1; padding: 0 6px; }
    .mod-checks { display: flex; flex-wrap: wrap; gap: 18px; margin-top: 10px; font-size: .92rem; }
    .mod-checks label { margin: 0; font-weight: 400; display: inline-flex; align-items: center; gap: 6px; }
    .mod-ayuda { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 20px; width: 20px; height: 20px;
        border: 1px solid #bbb; border-radius: 50%; font-size: .72rem; color: #666; cursor: pointer; font-weight: 700;
        background: #fff; padding: 0; line-height: 1; }
    .mod-ayuda:hover, .mod-ayuda.abierta { border-color: #673AB7; color: #673AB7; }
    #mod-burbuja { position: absolute; z-index: 2000; max-width: 260px; background: #2d2640; color: #fff;
        font-size: .85rem; line-height: 1.35; padding: 8px 12px; border-radius: 8px; box-shadow: 0 4px 14px rgba(0,0,0,.18);
        display: none; }
    .mod-agregar { color: #3b5bdb; border: 0; background: transparent; padding: 4px 0; font-weight: 500; }
    .mod-vacio { text-align: center; color: #888; padding: 30px 10px; border: 2px dashed #e3e1ea; border-radius: 14px; }
    .mod-como { background: #f6f4fb; border-radius: 12px; padding: 14px 18px; font-size: .9rem; display: none; }
    .mod-como li { margin-bottom: 4px; }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <a class="btn btn-info btn-sm" href="{{ url('/admin/productos/'.$producto->id_categorias) }}">Atrás</a>
            <h1 style="margin-left: 15px">Modificadores</h1>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header" id="card-header-color">
                <h3 class="card-title" style="color: white">
                    {{ $categoria->nombre ?? '' }} · {{ $producto->nombre }} · Precio base ${{ number_format((float)$producto->precio, 2) }}
                </h3>
            </div>
            <div class="card-body">

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <p class="mod-titulo">Modificadores de {{ $producto->nombre }}</p>
                    <button type="button" class="btn btn-outline-secondary btn-sm" style="border-radius: 20px" onclick="$('#como').slideToggle(150)">
                        <span class="mod-ayuda" style="width:18px;height:18px;border-color:currentColor;color:inherit;background:transparent">?</span> ¿Cómo funciona?
                    </button>
                </div>

                <div class="mod-como mb-3" id="como">
                    <ul class="mb-0 pl-3">
                        <li><b>Bloque:</b> un grupo de opciones que el cliente ve al agregar el producto (ej. «Preparación de huevo»).</li>
                        <li><b>Opcional / Requerido:</b> si es requerido, el cliente no puede agregar el producto sin elegir.</li>
                        <li><b>Mínimo / Máximo:</b> cuántas opciones (unidades) puede elegir en el bloque. Con máximo 1, tocar otra opción la cambia.</li>
                        <li><b>+$:</b> precio extra que se suma por cada unidad elegida. Déjalo en 0 si no cuesta más.</li>
                        <li><b>Máx. de la opción:</b> cuántas veces puede elegir la misma opción (ej. «Tocino» hasta 2).</li>
                        <li><b>Obligatorio:</b> la opción siempre va incluida y no se puede quitar.</li>
                        <li><b>Una sola:</b> si el cliente la elige, no puede combinarla con otras del mismo bloque (ej. «Sin salsa»).</li>
                        <li><b>Activa:</b> desmárcala para ocultar la opción sin borrarla.</li>
                        <li>Los cambios aplican de inmediato. Los carritos con opciones que ya no existen piden al cliente revisarlas.</li>
                    </ul>
                </div>

                <div id="bloques"></div>

                <div class="d-flex flex-wrap justify-content-between align-items-center mt-2" style="gap: 10px">
                    <button type="button" class="btn btn-outline-primary" onclick="agregarBloque()">+ Agregar bloque</button>
                    <button type="button" class="btn btn-primary" style="background:#673AB7;border-color:#673AB7;min-width:160px" onclick="guardar()">Guardar cambios</button>
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
        const ID_PRODUCTO = {{ $producto->id }};
        let grupos = [];
        let hayCambios = false;

        const AYUDA = {
            tipo: 'Requerido: el cliente debe elegir para poder agregar el producto.',
            minmax: 'Cuántas opciones (unidades) puede elegir el cliente en este bloque.',
            precio: 'Precio extra por cada unidad elegida. 0 = sin costo.',
            maxop: 'Cuántas veces se puede elegir esta misma opción.',
            obligatorio: 'Siempre va incluida y el cliente no la puede quitar.',
            unasola: 'Si se elige, no se puede combinar con otras opciones del bloque.',
            activa: 'Desmarca para ocultarla sin borrarla.'
        };

        function esc(t) {
            return String(t ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
        function ayuda(clave) {
            return `<button type="button" class="mod-ayuda" data-ayuda="${clave}" aria-label="Ayuda">?</button>`;
        }

        // burbuja de ayuda: se abre al hacer clic en "?" y se cierra al hacer clic fuera
        const burbuja = document.createElement('div');
        burbuja.id = 'mod-burbuja';
        document.body.appendChild(burbuja);
        let ayudaAbierta = null;

        function cerrarAyuda() {
            burbuja.style.display = 'none';
            if (ayudaAbierta) ayudaAbierta.classList.remove('abierta');
            ayudaAbierta = null;
        }

        document.addEventListener('click', (e) => {
            const boton = e.target.closest('.mod-ayuda[data-ayuda]');
            if (!boton) { cerrarAyuda(); return; }
            e.preventDefault();
            e.stopPropagation(); // dentro de un <label> no debe marcar la casilla

            if (ayudaAbierta === boton) { cerrarAyuda(); return; }
            cerrarAyuda();

            burbuja.textContent = AYUDA[boton.dataset.ayuda] || '';
            burbuja.style.display = 'block';
            const r = boton.getBoundingClientRect();
            const ancho = burbuja.offsetWidth;
            let izquierda = r.left + window.scrollX + r.width / 2 - ancho / 2;
            izquierda = Math.max(8, Math.min(izquierda, window.scrollX + document.documentElement.clientWidth - ancho - 8));
            burbuja.style.left = izquierda + 'px';
            burbuja.style.top = (r.bottom + window.scrollY + 8) + 'px';
            boton.classList.add('abierta');
            ayudaAbierta = boton;
        }, true);

        window.addEventListener('resize', cerrarAyuda);
        window.addEventListener('scroll', cerrarAyuda, true);

        function opcionNueva() {
            return { id: null, nombre: '', precio: '0.00', maximo: 1, obligatorio: false, una_sola: false, activo: true };
        }
        function bloqueNuevo() {
            return { id: null, nombre: '', requerido: false, minimo: 0, maximo: 1, opciones: [opcionNueva()] };
        }

        function resumen(g) {
            const min = g.requerido ? Math.max(1, +g.minimo || 0) : (+g.minimo || 0);
            const max = Math.max(1, +g.maximo || 1);
            let regla;
            if (min === max) regla = `elige ${max}`;
            else if (min <= 0) regla = `elige hasta ${max}`;
            else regla = `elige de ${min} a ${max}`;
            const extra = max === 1 ? ' (tocar otra la cambia)' : '';
            return `Al vender: «${esc(g.nombre || 'Sin nombre')}» · ${g.requerido ? 'Requerido' : 'Opcional'} · ${regla}${extra}`;
        }

        function render() {
            const cont = document.getElementById('bloques');
            if (grupos.length === 0) {
                cont.innerHTML = `<div class="mod-vacio">Este producto no tiene modificadores.<br>
                    Agrega un bloque para que el cliente elija opciones (ej. tipo de huevo, salsas, extras).</div>`;
                return;
            }

            cont.innerHTML = grupos.map((g, i) => `
                <div class="mod-bloque">
                    <div class="mod-bloque-cab">
                        <span class="mod-num">${i + 1}.</span>
                        <input type="text" class="form-control nombre-bloque" maxlength="100" placeholder="Nombre del bloque"
                               value="${esc(g.nombre)}" oninput="cambiarBloque(${i}, 'nombre', this.value)">
                        <div class="mod-segmento">
                            <button type="button" class="${g.requerido ? '' : 'sel-opcional'}" onclick="tipoBloque(${i}, false)">Opcional</button>
                            <button type="button" class="${g.requerido ? 'sel-requerido' : ''}" onclick="tipoBloque(${i}, true)">Requerido</button>
                        </div>
                        ${ayuda('tipo')}
                        <button type="button" class="mod-flecha" title="Subir" ${i === 0 ? 'disabled' : ''} onclick="moverBloque(${i}, -1)">&uarr;</button>
                        <button type="button" class="mod-flecha" title="Bajar" ${i === grupos.length - 1 ? 'disabled' : ''} onclick="moverBloque(${i}, 1)">&darr;</button>
                        <button type="button" class="mod-quitar" onclick="quitarBloque(${i})">Quitar bloque</button>
                    </div>
                    <div class="mod-cuerpo">
                        <div class="mod-minmax">
                            <label>Mínimo</label>
                            <input type="number" min="0" class="form-control" value="${g.minimo}" oninput="cambiarBloque(${i}, 'minimo', this.value)">
                            <label>Máximo</label>
                            <input type="number" min="1" class="form-control" value="${g.maximo}" oninput="cambiarBloque(${i}, 'maximo', this.value)">
                            ${ayuda('minmax')}
                        </div>
                        <div class="mod-resumen" id="resumen-${i}">${resumen(g)}</div>

                        ${g.opciones.map((o, j) => `
                            <div class="mod-opcion ${o.activo ? '' : 'inactiva'}">
                                <div class="mod-fila">
                                    <input type="text" class="form-control nombre-opcion" maxlength="100" placeholder="Nombre de la opción"
                                           value="${esc(o.nombre)}" oninput="cambiarOpcion(${i}, ${j}, 'nombre', this.value)">
                                    <div class="mod-precio">
                                        <span>+$</span>
                                        <input type="number" min="0" step="0.01" placeholder="0.00" value="${esc(o.precio)}"
                                               oninput="cambiarOpcion(${i}, ${j}, 'precio', this.value)">
                                    </div>
                                    ${ayuda('precio')}
                                    <span class="mod-etiqueta">Máx.</span>
                                    <input type="number" min="1" class="form-control mod-maxop" value="${o.maximo}"
                                           oninput="cambiarOpcion(${i}, ${j}, 'maximo', this.value)">
                                    ${ayuda('maxop')}
                                    <button type="button" class="mod-x" title="Quitar opción" onclick="quitarOpcion(${i}, ${j})">&times;</button>
                                </div>
                                <div class="mod-checks">
                                    <label><input type="checkbox" ${o.obligatorio ? 'checked' : ''} onchange="marcarOpcion(${i}, ${j}, 'obligatorio', this.checked)"> Obligatorio ${ayuda('obligatorio')}</label>
                                    <label><input type="checkbox" ${o.una_sola ? 'checked' : ''} onchange="marcarOpcion(${i}, ${j}, 'una_sola', this.checked)"> Una sola ${ayuda('unasola')}</label>
                                    <label><input type="checkbox" ${o.activo ? 'checked' : ''} onchange="marcarOpcion(${i}, ${j}, 'activo', this.checked)"> Activa ${ayuda('activa')}</label>
                                </div>
                            </div>
                        `).join('')}

                        <button type="button" class="mod-agregar" onclick="agregarOpcion(${i})">+ Agregar opción</button>
                    </div>
                </div>
            `).join('');
        }

        // --- cambios que no redibujan (para no perder el foco al escribir) ---
        function cambiarBloque(i, campo, valor) {
            grupos[i][campo] = (campo === 'nombre') ? valor : valor;
            hayCambios = true;
            document.getElementById('resumen-' + i).innerHTML = resumen(grupos[i]);
        }
        function cambiarOpcion(i, j, campo, valor) {
            grupos[i].opciones[j][campo] = valor;
            hayCambios = true;
        }

        // --- cambios estructurales (redibujan) ---
        function tipoBloque(i, requerido) {
            const g = grupos[i];
            g.requerido = requerido;
            if (requerido && (+g.minimo || 0) < 1) g.minimo = 1;
            if (!requerido) g.minimo = 0;
            hayCambios = true;
            render();
        }
        function marcarOpcion(i, j, campo, valor) {
            const o = grupos[i].opciones[j];
            o[campo] = valor;
            // obligatorio y "una sola" no se pueden combinar
            if (valor && campo === 'obligatorio') o.una_sola = false;
            if (valor && campo === 'una_sola') o.obligatorio = false;
            hayCambios = true;
            render();
        }
        function agregarBloque() {
            grupos.push(bloqueNuevo());
            hayCambios = true;
            render();
        }
        function quitarBloque(i) {
            Swal.fire({
                title: '¿Quitar bloque?',
                text: `Se quitará «${grupos[i].nombre || 'Sin nombre'}» y sus opciones al guardar.`,
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#e8112d', cancelButtonText: 'Cancelar', confirmButtonText: 'Quitar'
            }).then((r) => {
                if (r.isConfirmed) { grupos.splice(i, 1); hayCambios = true; render(); }
            });
        }
        function moverBloque(i, dir) {
            const k = i + dir;
            if (k < 0 || k >= grupos.length) return;
            [grupos[i], grupos[k]] = [grupos[k], grupos[i]];
            hayCambios = true;
            render();
        }
        function agregarOpcion(i) {
            grupos[i].opciones.push(opcionNueva());
            hayCambios = true;
            render();
        }
        function quitarOpcion(i, j) {
            grupos[i].opciones.splice(j, 1);
            hayCambios = true;
            render();
        }

        // --- validación (igual a la del servidor) ---
        function validar() {
            for (let i = 0; i < grupos.length; i++) {
                const g = grupos[i];
                const nombre = (g.nombre || '').trim();
                const min = parseInt(g.minimo, 10);
                const max = parseInt(g.maximo, 10);

                if (!nombre) return `Bloque ${i + 1}: escribe el nombre`;
                if (isNaN(max) || max < 1) return `«${nombre}»: el máximo debe ser al menos 1`;
                if (isNaN(min) || min < 0 || min > max) return `«${nombre}»: el mínimo no puede ser mayor que el máximo`;
                if (g.requerido && min < 1) return `«${nombre}»: si es requerido, el mínimo debe ser al menos 1`;
                if (g.opciones.length === 0) return `«${nombre}»: agrega al menos una opción`;

                let obligatorias = 0, capacidad = 0;
                for (let j = 0; j < g.opciones.length; j++) {
                    const o = g.opciones[j];
                    const nomOp = (o.nombre || '').trim();
                    const precio = o.precio === '' ? 0 : Number(o.precio);
                    const maxOp = parseInt(o.maximo, 10);
                    if (!nomOp) return `«${nombre}», opción ${j + 1}: escribe el nombre`;
                    if (isNaN(precio) || precio < 0) return `«${nomOp}»: el precio extra no es válido`;
                    if (isNaN(maxOp) || maxOp < 1) return `«${nomOp}»: el máximo debe ser al menos 1`;
                    if (o.activo) {
                        capacidad += maxOp;
                        if (o.obligatorio) obligatorias++;
                    }
                }
                if (obligatorias > max) return `«${nombre}»: hay más opciones obligatorias que el máximo del bloque`;
                if (g.requerido && capacidad < min) return `«${nombre}»: con las opciones activas no se puede llegar al mínimo (${min})`;
            }
            return null;
        }

        function guardar() {
            const error = validar();
            if (error) { toastr.error(error); return; }

            const datos = grupos.map((g) => ({
                id: g.id,
                nombre: g.nombre.trim(),
                requerido: g.requerido ? 1 : 0,
                minimo: parseInt(g.minimo, 10),
                maximo: parseInt(g.maximo, 10),
                opciones: g.opciones.map((o) => ({
                    id: o.id,
                    nombre: o.nombre.trim(),
                    precio: o.precio === '' ? 0 : Number(o.precio).toFixed(2),
                    maximo: parseInt(o.maximo, 10),
                    obligatorio: o.obligatorio ? 1 : 0,
                    una_sola: o.una_sola ? 1 : 0,
                    activo: o.activo ? 1 : 0,
                }))
            }));

            openLoading();
            axios.post('/admin/productos/modificadores/guardar', { id_producto: ID_PRODUCTO, grupos: datos })
                .then((r) => {
                    closeLoading();
                    if (r.data.success === 1) {
                        toastr.success('Modificadores guardados');
                        hayCambios = false;
                        cargar();
                    } else {
                        toastr.error(r.data.mensaje || 'Error al guardar');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error al guardar'); });
        }

        function cargar() {
            axios.get('/admin/productos/modificadores/informacion/' + ID_PRODUCTO)
                .then((r) => {
                    if (r.data.success === 1) {
                        grupos = r.data.grupos;
                        render();
                    } else {
                        toastr.error('Error al cargar');
                    }
                })
                .catch(() => toastr.error('Error al cargar'));
        }

        window.addEventListener('beforeunload', (e) => {
            if (hayCambios) { e.preventDefault(); e.returnValue = ''; }
        });

        $(document).ready(cargar);
    </script>

@endsection
