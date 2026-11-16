<?php

namespace App\Http\Controllers\Backend\Api\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Clientes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ApiRegistroController extends Controller
{

    public function registroCliente(Request $request){

        $rules = array(
            'usuario' => 'required',
            'password' => 'required',
            'correo' => 'nullable|email|max:100', // opcional, sirve para recuperar la contraseña
        );

        // version

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()){return ['success' => 0]; }

        // verificar si existe el usuario
        if(Clientes::where('usuario', $request->usuario)->first()){
            return ['success' => 1, 'titulo' => 'Nota', 'mensaje' => 'Este usuario ya existe'];
        }

        $correo = $request->filled('correo') ? mb_strtolower(trim($request->correo)) : null;

        if($correo && Clientes::where('correo', $correo)->exists()){
            return ['success' => 1, 'titulo' => 'Nota', 'mensaje' => 'Este correo ya está registrado'];
        }

        $fecha = Carbon::now('America/El_Salvador');

        $usuario = new Clientes();
        $usuario->usuario = $request->usuario;
        $usuario->correo = $correo;
        $usuario->password = Hash::make($request->password);
        $usuario->fecha = $fecha;

        if($usuario->save()){
            $token = $usuario->createToken('app')->plainTextToken;

            return ['success'=> 2, 'id'=> strval($usuario->id), 'token' => $token, 'titulo' => 'Nota', 'mensaje' => 'Registro exitoso'];
        }else{
            return ['success' => 4,  'titulo' => 'Nota', 'mensaje' => 'Error'];
        }
    }
}
