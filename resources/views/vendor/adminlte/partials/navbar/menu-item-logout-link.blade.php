{{--
    Reemplaza el partial del paquete jeroennoten/laravel-adminlte que se usa cuando usermenu_enabled = false.
    En la barra superior solo se muestra el nombre del usuario; el formulario de cierre de sesión queda
    oculto aquí y lo envía la opción "Cerrar Sesión" del menú lateral.
--}}
<li class="nav-item">
    <span class="nav-link">{{ Auth::user()->nombre }}</span>

    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
        {{ csrf_field() }}
    </form>
</li>
