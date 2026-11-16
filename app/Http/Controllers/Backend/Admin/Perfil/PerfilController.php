<?php

namespace App\Http\Controllers\Backend\Admin\Perfil;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Controllers\Controller;
use App\Jobs\SendNotiClienteJobs;
use App\Models\Usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function indexEditarPerfil(){
        $usuario = auth()->user();
        return view('backend.admin.perfil.vistaperfil', compact('usuario'));
    }

    public function editarUsuario(Request $request){

        $regla = array(
            'password' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){return ['success' => 0];}

        $usuario = auth()->user();

        Usuarios::where('id', $usuario->id)
            ->update(['password' => bcrypt($request->password)]);

        return ['success' => 1];
    }


}
