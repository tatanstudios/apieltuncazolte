<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rutas de la app que requieren sesión (van después de auth:sanctum).
 *
 * La app manda el número de cliente en "id" o "clienteid". Aquí se reemplazan por el cliente
 * dueño del token, para que nadie pueda ver o modificar datos de otro cliente cambiando ese número.
 */
class ClienteDelToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $idCliente = (string) $request->user()->id;

        $request->merge([
            'id' => $idCliente,
            'clienteid' => $idCliente,
        ]);

        return $next($request);
    }
}
