{{-- Armazón del panel: barra superior, menú lateral (config/adminlte.php) y un iframe donde se cargan las pantallas --}}
@extends('adminlte::page')

@section('adminlte_css_pre')
    <link href="{{ asset('images/icono-sistema.png') }}" rel="icon">
@stop

@section('css')
    <style>
        .content-wrapper { background-color: #fff; }
        .content-wrapper > .content { padding: 0; }
        .content-wrapper > .content > .container-fluid { padding: 0; }
        #frameprincipal { display: block; width: 100%; border: 0; height: calc(100vh - 57px); }
    </style>
@stop

@section('content')
    <iframe id="frameprincipal" name="frameprincipal"></iframe>

    <script>
        // La pantalla abierta se guarda en la URL (/panel#/admin/...) para que al recargar se mantenga.
        // Solo se aceptan rutas internas del panel.
        function rutaGuardada() {
            var ruta = decodeURIComponent(window.location.hash.slice(1));
            return /^\/admin\/[^\/]/.test(ruta) && ruta.indexOf('//') === -1 ? ruta : null;
        }

        document.getElementById('frameprincipal').src = rutaGuardada() || "{{ route($ruta) }}";
    </script>
@stop

@section('js')
    <script>
        $(function () {
            var $frame = $('#frameprincipal');
            var $links = $('.nav-sidebar a.nav-link[target="frameprincipal"]');

            // marca en el menú la opción de la pantalla abierta (y abre su submenú si tiene)
            function marcar(ruta) {
                var $link = $links.filter(function () { return this.pathname === ruta; });
                if (!$link.length) return; // subpantallas (ej. productos de una orden) dejan marcada la anterior

                $('.nav-sidebar a.nav-link').removeClass('active');
                $link.addClass('active');

                var $padres = $link.parents('.nav-item.has-treeview');
                $padres.addClass('menu-open').children('a.nav-link').addClass('active');
                $padres.children('.nav-treeview').show();
            }

            function alCargar() {
                var loc;
                try {
                    loc = $frame[0].contentWindow.location;
                } catch (e) {
                    return;
                }
                if (loc.href === 'about:blank') return;

                // si la sesión venció, el iframe termina en el login: se muestra el login en toda la ventana
                if (loc.pathname === '/') {
                    window.location.href = '/';
                    return;
                }

                var ruta = loc.pathname + loc.search;
                history.replaceState(null, '', window.location.pathname + '#' + ruta);
                marcar(loc.pathname);
            }

            $frame.on('load', alCargar);

            // por si el iframe terminó de cargar antes de que este script se ejecutara
            try {
                if ($frame[0].contentDocument && $frame[0].contentDocument.readyState === 'complete') alCargar();
            } catch (e) {}

            $('#menu-cerrar-sesion > a').on('click', function (e) {
                e.preventDefault();
                document.getElementById('logout-form').submit();
            });

            $links.on('click', function () {
                marcar(this.pathname);
                // en celular se cierra el menú al elegir una opción
                if (window.innerWidth < 992) {
                    $('body').removeClass('sidebar-open').addClass('sidebar-collapse');
                }
            });
        });
    </script>
@stop
