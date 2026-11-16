<?php

namespace App\Http\Controllers\Backend\Api\Cliente;

use App\Http\Controllers\Controller;
use App\Mail\SendEmailCodigo;
use App\Models\Clientes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * Recuperación de contraseña por correo (sin sesión):
 *  1. enviarCodigo     -> manda un código de 6 dígitos al correo registrado
 *  2. verificarCodigo  -> confirma que el código es correcto (para pasar a la siguiente pantalla)
 *  3. cambiarPassword  -> cambia la contraseña, cierra las sesiones viejas y entrega un token nuevo
 */
class ApiRecuperacionController extends Controller
{
    private const MINUTOS_VALIDEZ = 15;
    private const SEGUNDOS_ENTRE_ENVIOS = 60;
    private const MAX_INTENTOS = 5;

    public function enviarCodigo(Request $request)
    {
        $validator = Validator::make($request->all(), ['correo' => 'required|email|max:100']);
        if ($validator->fails()) {
            return ['success' => 0];
        }

        $cliente = Clientes::where('correo', $this->normalizar($request->correo))->first();
        if (!$cliente) {
            return ['success' => 2, 'mensaje' => 'No hay ninguna cuenta registrada con ese correo'];
        }

        $ahora = Carbon::now();

        // evita mandar muchos correos seguidos
        if ($cliente->codigo_expira) {
            $enviadoEn = Carbon::parse($cliente->codigo_expira)->subMinutes(self::MINUTOS_VALIDEZ);
            $espera = self::SEGUNDOS_ENTRE_ENVIOS - $enviadoEn->diffInSeconds($ahora, false);
            if ($espera > 0) {
                return ['success' => 3, 'mensaje' => 'Espera ' . (int) ceil($espera) . ' segundos para pedir otro código'];
            }
        }

        $codigo = (string) random_int(100000, 999999);

        $cliente->codigo_recuperacion = Hash::make($codigo);
        $cliente->codigo_expira = $ahora->copy()->addMinutes(self::MINUTOS_VALIDEZ);
        $cliente->codigo_intentos = 0;
        $cliente->save();

        try {
            Mail::to($cliente->correo)->send(new SendEmailCodigo($codigo));
        } catch (\Throwable $e) {
            Log::error('Recuperación: no se pudo enviar el correo', ['cliente' => $cliente->id, 'error' => $e->getMessage()]);
            // se libera para que pueda volver a intentarlo de inmediato
            $cliente->codigo_recuperacion = null;
            $cliente->codigo_expira = null;
            $cliente->save();
            return ['success' => 4, 'mensaje' => 'No se pudo enviar el correo, intenta de nuevo'];
        }

        return ['success' => 1, 'mensaje' => 'Código enviado'];
    }

    public function verificarCodigo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email|max:100',
            'codigo' => 'required|digits:6',
        ]);
        if ($validator->fails()) {
            return ['success' => 2, 'mensaje' => 'Código incorrecto'];
        }

        $resultado = $this->validarCodigo($request->correo, $request->codigo);
        if (is_array($resultado)) {
            return $resultado;
        }

        return ['success' => 1];
    }

    public function cambiarPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email|max:100',
            'codigo' => 'required|digits:6',
            'password' => 'required|min:4|max:16',
        ]);
        if ($validator->fails()) {
            return ['success' => 0];
        }

        $cliente = $this->validarCodigo($request->correo, $request->codigo);
        if (is_array($cliente)) {
            return $cliente;
        }

        $cliente->password = Hash::make($request->password);
        $cliente->codigo_recuperacion = null;
        $cliente->codigo_expira = null;
        $cliente->codigo_intentos = 0;
        $cliente->save();

        // se cierran las sesiones abiertas en otros teléfonos y se inicia sesión en este
        $cliente->tokens()->delete();
        $token = $cliente->createToken('app')->plainTextToken;

        return ['success' => 1, 'id' => strval($cliente->id), 'token' => $token, 'mensaje' => 'Contraseña actualizada'];
    }

    /**
     * Devuelve el cliente si el código es válido; si no, un arreglo con la respuesta de error.
     *  success 2 = código incorrecto, 3 = vencido o demasiados intentos (pedir uno nuevo)
     */
    private function validarCodigo(string $correo, string $codigo)
    {
        $cliente = Clientes::where('correo', $this->normalizar($correo))->first();

        if (!$cliente || !$cliente->codigo_recuperacion || !$cliente->codigo_expira) {
            return ['success' => 3, 'mensaje' => 'El código venció, solicita uno nuevo'];
        }

        if (Carbon::now()->greaterThan(Carbon::parse($cliente->codigo_expira))
            || $cliente->codigo_intentos >= self::MAX_INTENTOS) {
            return ['success' => 3, 'mensaje' => 'El código venció, solicita uno nuevo'];
        }

        if (!Hash::check($codigo, $cliente->codigo_recuperacion)) {
            $cliente->increment('codigo_intentos');
            return ['success' => 2, 'mensaje' => 'Código incorrecto'];
        }

        return $cliente;
    }

    private function normalizar(string $correo): string
    {
        return mb_strtolower(trim($correo));
    }
}
