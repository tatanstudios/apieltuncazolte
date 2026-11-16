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

        // las llaves nuevas (os_v2_...) usan "Key"; las antiguas usan "Basic"
        $esquema = str_starts_with($llave, 'os_v2_') ? 'Key' : 'Basic';

        try {
            $respuesta = Http::timeout(10)
                ->withHeaders(['Authorization' => "{$esquema} {$llave}"])
                ->post('https://api.onesignal.com/notifications?c=push', [
                    'app_id' => $appId,
                    'target_channel' => 'push',
                    'include_aliases' => ['external_id' => array_values($idsClientes)],
                    'headings' => ['es' => $titulo, 'en' => $titulo],
                    'contents' => ['es' => $mensaje, 'en' => $mensaje],
                    'data' => (object) $datos,
                ]);

            if (!$respuesta->successful() || $respuesta->json('errors')) {
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
