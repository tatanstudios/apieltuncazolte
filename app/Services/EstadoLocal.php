<?php

namespace App\Services;

use App\Models\Horario;
use App\Models\InformacionAdmin;
use Carbon\Carbon;

/**
 * ¿El local está abierto ahora? Mismas reglas que al enviar una orden (ApiCarritoController::procesarOrdenEstado1):
 *  - cerrado desde el panel (informacion_admin.cerrado)
 *  - día marcado como cerrado (horario.cerrado)
 *  - fuera del horario del día (horario.hora1 - horario.hora2)
 *
 * En la tabla horario: 1 = domingo, 2 = lunes ... 7 = sábado.
 */
class EstadoLocal
{
    private const DIAS = [1 => 'domingo', 2 => 'lunes', 3 => 'martes', 4 => 'miércoles', 5 => 'jueves', 6 => 'viernes', 7 => 'sábado'];

    /**
     * @return array{abierto: bool, texto: string, detalle: ?string, motivo: ?string}
     *   texto:   frase corta ("Cerrado · abrimos mañana a las 3:00 PM")
     *   detalle: línea extra para el menú
     *   motivo:  mensaje escrito en el panel al cerrar la app (si aplica)
     */
    public static function ahora(): array
    {
        $ahora = Carbon::now('America/El_Salvador');
        $info = InformacionAdmin::where('id', 1)->first();

        if ($info && $info->cerrado == 1) {
            return [
                'abierto' => false,
                'texto' => 'Cerrado por el momento',
                'detalle' => $info->mensaje_cerrado ?: null,
                'motivo' => $info->mensaje_cerrado ?: null,
            ];
        }

        $horarios = Horario::all()->keyBy('dia');
        $hoy = $horarios->get($ahora->dayOfWeek + 1);
        $hora = $ahora->format('H:i:s');

        if ($hoy && $hoy->cerrado == 0) {
            if ($hora >= $hoy->hora1 && $hora <= $hoy->hora2) {
                return [
                    'abierto' => true,
                    'texto' => 'Abierto · hasta las ' . self::formato($hoy->hora2),
                    'detalle' => null,
                    'motivo' => null,
                ];
            }
            if ($hora < $hoy->hora1) {
                return [
                    'abierto' => false,
                    'texto' => 'Cerrado · abrimos hoy a las ' . self::formato($hoy->hora1),
                    'detalle' => 'Puedes ver el menú y armar tu orden.',
                    'motivo' => null,
                ];
            }
        }

        // próximo día que abre (hasta una semana)
        for ($i = 1; $i <= 7; $i++) {
            $fecha = $ahora->copy()->addDays($i);
            $dia = $horarios->get($fecha->dayOfWeek + 1);
            if ($dia && $dia->cerrado == 0) {
                $cuando = $i === 1 ? 'mañana' : 'el ' . self::DIAS[$fecha->dayOfWeek + 1];
                return [
                    'abierto' => false,
                    'texto' => "Cerrado · abrimos {$cuando} a las " . self::formato($dia->hora1),
                    'detalle' => 'Puedes ver el menú y armar tu orden.',
                    'motivo' => null,
                ];
            }
        }

        return ['abierto' => false, 'texto' => 'Cerrado por el momento', 'detalle' => null, 'motivo' => null];
    }

    private static function formato(string $hora): string
    {
        return date('g:i A', strtotime($hora));
    }
}
