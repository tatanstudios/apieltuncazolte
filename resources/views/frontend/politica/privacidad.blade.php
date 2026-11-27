@php
    $app = config('app.name', 'El Tuncazo');
    $correo = config('mail.from.address');
    $actualizado = '26 de noviembre de 2026';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Política de Privacidad · {{ $app }}</title>
    <meta name="robots" content="index, follow">
    <style>
        :root { --primario: #5B2DB0; --texto: #1F1B24; --suave: #5f5a66; --fondo: #F6F3FB; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
               color: var(--texto); background: var(--fondo); line-height: 1.6; }
        header { background: var(--primario); color: #fff; padding: 28px 20px; }
        header h1 { margin: 0 auto; max-width: 820px; font-size: 1.6rem; }
        header p { margin: 6px auto 0; max-width: 820px; opacity: .85; font-size: .95rem; }
        main { max-width: 820px; margin: 24px auto; padding: 28px 24px; background: #fff;
               border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        h2 { color: var(--primario); font-size: 1.15rem; margin: 28px 0 8px; }
        h2:first-child { margin-top: 0; }
        p, li { color: var(--suave); }
        ul { padding-left: 22px; }
        a { color: var(--primario); }
        footer { text-align: center; color: var(--suave); font-size: .85rem; padding: 8px 20px 32px; }
    </style>
</head>
<body>
<header>
    <h1>Política de Privacidad de {{ $app }}</h1>
    <p>Última actualización: {{ $actualizado }}</p>
</header>

<main>
    <h2>1. Quiénes somos</h2>
    <p>Esta política explica cómo la aplicación móvil <strong>{{ $app }}</strong> (“la app”), usada para ver el menú
        y hacer pedidos de comida a domicilio, recopila, usa y protege la información de sus usuarios.
        Al registrarte y usar la app aceptas lo descrito aquí.</p>

    <h2>2. Información que recopilamos</h2>
    <ul>
        <li><strong>Datos de cuenta:</strong> número de teléfono (usado como usuario) y contraseña, que se guarda cifrada.</li>
        <li><strong>Correo electrónico</strong> (opcional): solo si lo agregas, para recuperar tu contraseña.</li>
        <li><strong>Direcciones de entrega:</strong> nombre, dirección, punto de referencia y teléfono de contacto que escribes para recibir tus pedidos.</li>
        <li><strong>Pedidos:</strong> productos, cantidades, notas, totales y fecha de cada orden.</li>
        <li><strong>Identificador de notificaciones:</strong> un identificador del dispositivo generado por OneSignal para enviarte avisos sobre el estado de tus pedidos.</li>
    </ul>
    <p>La app <strong>no</strong> accede a tu ubicación GPS, contactos, fotos, cámara ni micrófono, y no procesa pagos con tarjeta dentro de la app.</p>

    <h2>3. Para qué usamos tu información</h2>
    <ul>
        <li>Crear y administrar tu cuenta e iniciar sesión.</li>
        <li>Recibir, preparar y entregar tus pedidos en la dirección que elijas.</li>
        <li>Contactarte sobre un pedido y enviarte notificaciones de su estado.</li>
        <li>Recuperar el acceso a tu cuenta mediante un código enviado a tu correo.</li>
    </ul>
    <p>No vendemos ni alquilamos tu información personal, ni la usamos para publicidad.</p>

    <h2>4. Con quién la compartimos</h2>
    <ul>
        <li><strong>Personal del restaurante y repartidores</strong>, solo lo necesario para preparar y entregar tu pedido.</li>
        <li><strong>OneSignal</strong>, proveedor del servicio de notificaciones push
            (<a href="https://onesignal.com/privacy_policy" target="_blank" rel="noopener">política de privacidad de OneSignal</a>).</li>
        <li>Autoridades, únicamente cuando la ley lo exija.</li>
    </ul>

    <h2>5. Seguridad</h2>
    <p>La información viaja cifrada entre la app y nuestro servidor mediante HTTPS, y las contraseñas se almacenan
        con cifrado unidireccional (hash). Aun así, ningún sistema es 100 % seguro, por lo que te pedimos mantener tu contraseña en privado.</p>

    <h2>6. Conservación de los datos</h2>
    <p>Conservamos tus datos mientras tu cuenta esté activa. Puedes borrar tus direcciones desde la app en cualquier momento.
        Si eliminas tu cuenta, borraremos tus datos personales, salvo los registros de pedidos que debamos conservar por obligaciones legales o contables.</p>

    <h2 id="eliminar-cuenta">7. Tus derechos y eliminación de la cuenta</h2>
    <p>Puedes consultar, corregir o actualizar tu correo y contraseña desde la sección de perfil de la app.
        Para solicitar la <strong>eliminación de tu cuenta y de tus datos</strong>, escríbenos
        @if($correo) a <a href="mailto:{{ $correo }}?subject=Eliminar%20mi%20cuenta%20{{ rawurlencode($app) }}">{{ $correo }}</a> @endif
        desde el correo registrado o indicando el número de teléfono de tu cuenta. Atenderemos la solicitud en un plazo máximo de 30 días.</p>

    <h2>8. Menores de edad</h2>
    <p>La app no está dirigida a menores de 13 años y no recopilamos conscientemente información de ellos.
        Si crees que un menor nos dio sus datos, contáctanos para eliminarlos.</p>

    <h2>9. Cambios a esta política</h2>
    <p>Podemos actualizar esta política. Publicaremos la nueva versión en esta misma página con su fecha de actualización.</p>

    <h2>10. Contacto</h2>
    <p>Si tienes preguntas sobre esta política o sobre tus datos, escríbenos
        @if($correo) a <a href="mailto:{{ $correo }}">{{ $correo }}</a>@endif.</p>
</main>

<footer>© {{ date('Y') }} {{ $app }}</footer>
</body>
</html>
