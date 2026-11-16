<?php

namespace App\Http\Controllers\Backend\Api\Cliente;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJobs;
use App\Mail\SendEmailCodigo;
use App\Models\Clientes;
use App\Models\InformacionAdmin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ApiClienteController extends Controller
{
    public function loginCliente(Request $request){

        $rules = array(
            'usuario' => 'required',
            'password' => 'required',
        );

        $validator = Validator::make($request->all(), $rules );

        if ( $validator->fails()){
            return ['success' => 0];
        }

        if($info = Clientes::where('usuario', $request->usuario)->first()){

            if (Hash::check($request->password, $info->password)) {

                // inicio sesion: cada inicio de sesión genera un token para ese teléfono
                $token = $info->createToken('app')->plainTextToken;

                return ['success' => 1, 'id' => strval($info->id), 'token' => $token];

            }else{
                // contraseña incorrecta
                return ['success' => 2];
            }

        } else {
            // usuario no encontrado
            return ['success' => 4];
        }
    }






    // cierra la sesión del teléfono: el token deja de servir
    public function logoutCliente(Request $request){

        $request->user()->currentAccessToken()->delete();

        return ['success' => 1];
    }


    public function actualizarPasswordCliente(Request $request){

        $rules = array(
            'id' => 'required',
            'password' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()){return ['success' => 0]; }

        if(Clientes::where('id', $request->id)->first()){

            Clientes::where('id', $request->id)->update(['password' => Hash::make($request->password)]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }


    // correo para recuperar la contraseña
    public function verCorreoCliente(Request $request){

        $cliente = $request->user();

        return ['success' => 1, 'correo' => $cliente->correo];
    }


    public function actualizarCorreoCliente(Request $request){

        $validator = Validator::make($request->all(), ['correo' => 'required|email|max:100']);

        if ($validator->fails()){ return ['success' => 0]; }

        $cliente = $request->user();
        $correo = mb_strtolower(trim($request->correo));

        if(Clientes::where('correo', $correo)->where('id', '!=', $cliente->id)->exists()){
            // otro cliente ya usa ese correo
            return ['success' => 2];
        }

        $cliente->correo = $correo;
        $cliente->save();

        return ['success' => 1, 'correo' => $correo];
    }

}
