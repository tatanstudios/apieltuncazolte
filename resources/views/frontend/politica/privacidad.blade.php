@php
    $app = config('app.name', 'El Tuncazo');
    $correo = config('mail.from.address');
    $actualizado = '27 de noviembre de 2026';
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
        y enviar pedidos de comida a domicilio por WhatsApp, trata la información de sus usuarios.
        La app <strong>no requiere crear una cuenta</strong> ni iniciar sesión.</p>

    <h2>2. Información que se usa</h2>
    <ul>
        <li><strong>Carrito:</strong> los productos, cantidades, opciones y notas que eliges. Se guardan <strong>solo en tu teléfono</strong>.</li>
        <li><strong>Direcciones de entrega:</strong> nombre de quien recibe, teléfono de contacto, dirección y punto de referencia.
            Se guardan <strong>solo en tu teléfono</strong>; puedes borrarlas desde la app cuando quieras.</li>
        <li><strong>Pedido por WhatsApp:</strong> al enviar tu pedido, la app abre WhatsApp con un mensaje que incluye tus productos,
            el total, tu dirección de entrega y tu nota. Tú decides si envías ese mensaje.</li>
    </ul>
    <p>Para mostrar los precios actualizados y armar el mensaje, la app envía tu carrito (y, al enviar el pedido, la dirección)
        a nuestro servidor. El servidor <strong>no guarda</strong> esa información: solo la usa para responder en ese momento.</p>
    <p>La app <strong>no</strong> accede a tu ubicación GPS, contactos, fotos, cámara ni micrófono, y no procesa pagos dentro de la app.</p>

    <h2>3. Para qué usamos tu información</h2>
    <ul>
        <li>Calcular el total de tu pedido y el costo de envío.</li>
        <li>Preparar y entregar tu pedido en la dirección que indicas.</li>
        <li>Contactarte por WhatsApp o teléfono sobre tu pedido.</li>
    </ul>
    <p>No vendemos ni alquilamos tu información personal, ni la usamos para publicidad.</p>

    <h2>4. Con quién la compartimos</h2>
    <ul>
        <li><strong>Personal del restaurante y repartidores</strong>, solo lo necesario para preparar y entregar tu pedido.</li>
        <li><strong>WhatsApp</strong> (Meta), cuando eliges enviar tu pedido por ese medio
            (<a href="https://www.whatsapp.com/legal/privacy-policy" target="_blank" rel="noopener">política de privacidad de WhatsApp</a>).</li>
        <li>Autoridades, únicamente cuando la ley lo exija.</li>
    </ul>

    <h2>5. Seguridad</h2>
    <p>La información viaja cifrada entre la app y nuestro servidor mediante HTTPS. Los datos guardados en tu teléfono
        permanecen en él y se borran si eliminas tus direcciones, vacías el carrito o desinstalas la app.</p>

    <h2>6. Conservación de los datos</h2>
    <p>No guardamos tus datos en nuestro servidor. Los mensajes de pedido que envías por WhatsApp quedan en la conversación
        con el restaurante, que los conserva solo el tiempo necesario para atender tu pedido.</p>

    <h2 id="eliminar-cuenta">7. Tus derechos y eliminación de datos</h2>
    <p>Como no hay cuentas, no hay una cuenta que eliminar. Puedes borrar en cualquier momento tus direcciones y tu carrito
        desde la app, o desinstalarla para borrar todo lo guardado en el teléfono. Si quieres que eliminemos los mensajes de pedido
        que enviaste por WhatsApp, escríbenos
        @if($correo) a <a href="mailto:{{ $correo }}?subject=Eliminar%20mis%20datos%20{{ rawurlencode($app) }}">{{ $correo }}</a> @endif
        indicando el número desde el que hiciste el pedido. Atenderemos la solicitud en un plazo máximo de 30 días.</p>

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
