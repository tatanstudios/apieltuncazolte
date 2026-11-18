<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Notificaciones push con OneSignal (API REST).
 *
 * La app vincula cada teléfono con el id del cliente (OneSignal.login = external_id),
 * así que aquí se envía al cliente por su id: no se guardan tokens en la base de datos.
 *
 * .env:
 *   ONE_SIGNAL_APP_ID=...      (App ID, el mismo que usa la app)
 *   ONE_SIGNAL_AUTHORIZE=...   (REST API Key: OneSignal > Settings > Keys & IDs)
 */
class Notificaciones
{
    /**
     * Envía la notificación después de responder la petición, para no hacer esperar al panel.
     * Si falla (sin internet, llave incorrecta), solo se registra en el log.
     */
    public static function cliente(int $idCliente, string $titulo, string $mensaje, array $datos = []): void
    {
        dispatch(function () use ($idCliente, $titulo, $mensaje, $datos) {
            self::enviar([(string) $idCliente], $titulo, $mensaje, $datos);
        })->afterResponse();
    }

    /** Envío inmediato. Devuelve true si OneSignal aceptó la notificación. */
    public static function enviar(array $idsClientes, string $titulo, string $mensaje, array $datos = []): bool
    {
        $appId = config('one-signal.app_id');
        $llave = config('one-signal.authorize');

        if (empty($appId) || empty($llave) || empty($idsClientes)) {
            Log::warning('OneSignal: falta ONE_SIGNAL_APP_ID u ONE_SIGNAL_AUTHORIZE en el .env');
            return false;
        }

        // La app registra el External ID como "cliente_{id}" porque OneSignal rechaza valores como "1".
        // También se incluye el id sin prefijo para teléfonos con la app 3.0.0 (versionCode 13), que lo registraba así.
        $aliases = [];
        foreach ($idsClientes as $id) {
            $aliases[] = 'cliente_' . $id;
            $aliases[] = (string) $id;
        }

        // las llaves nuevas (os_v2_...) usan "Key"; las antiguas usan "Basic"
        $esquema = str_starts_with($llave, 'os_v2_') ? 'Key' : 'Basic';

        try {
            $respuesta = Http::timeout(10)
                ->withHeaders(['Authorization' => "{$esquema} {$llave}"])
                ->post('https://api.onesignal.com/notifications?c=push', [
                    'app_id' => $appId,
                    'target_channel' => 'push',
                    'include_aliases' => ['external_id' => $aliases],
                    'headings' => ['es' => $titulo, 'en' => $titulo],
                    'contents' => ['es' => $mensaje, 'en' => $mensaje],
                    'data' => (object) $datos,
                ]);

            // con id la notificación salió; "errors" puede listar solo los alias sin teléfono (ej. el formato viejo)
            if (!$respuesta->successful() || empty($respuesta->json('id'))) {
                Log::warning('OneSignal: notificación rechazada', [
                    'status' => $respuesta->status(),
                    'respuesta' => $respuesta->json(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('OneSignal: error al enviar', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
