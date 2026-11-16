<script src="{{ asset('vendor/jquery/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>
<script>
    // AdminLTE 3.2: dentro del iframe "frameprincipal" lee estas opciones del navegador y, si no existen,
    // lanza "Cannot read properties of null (reading 'autoIframeMode')". Se crean vacías antes de cargarlo.
    try {
        if (window.frameElement && !localStorage.getItem('AdminLTE:IFrame:Options')) {
            localStorage.setItem('AdminLTE:IFrame:Options', JSON.stringify({ autoIframeMode: false, autoDarkMode: false }));
        }
    } catch (e) {}
</script>
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}" type="text/javascript"></script>


@yield('archivos-js')

</body>
</html>
