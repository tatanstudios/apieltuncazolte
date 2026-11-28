<?php

namespace App\Http\Controllers\Backend\Admin\Horario;

use App\Http\Controllers\Controller;
use App\Models\Contenedor;
use App\Models\Horario;
use App\Models\InformacionAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class HorarioController extends Controller
{

    public function indexHorario(){
        return view('backend.admin.zonas.horario.vistahorario');
    }

    // tabla
    public function tablaHorario(){
        $horario = Horario::orderBy('dia', 'ASC')->get();

        foreach ($horario as $hh){
            // en la base: 1 = domingo, 2 = lunes ... 7 = sábado. En el panel se muestra de lunes a domingo.
            $hh->orden_semana = $hh->dia == 1 ? 8 : $hh->dia;
            $hh->hora1 = date("h:i A", strtotime($hh->hora1));
            $hh->hora2 = date("h:i A", strtotime($hh->hora2));
        }

        $horario = $horario->sortBy('orden_semana')->values();

        return view('backend.admin.zonas.horario.tablahorario', compact('horario'));
    }

    public function informacionHorario(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){return ['success' => 0]; }

        if($horario = Horario::where('id', $request->id)->first()){

            return ['success' => 1, 'horario' => $horario];
        }else{
            return ['success' => 2];
        }
    }


    public function editarHorario(Request $request){
        $rules = array(
            'id' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()){ return ['success' => 0];}

        if(Horario::where('id', $request->id)->first()){

            Horario::where('id', $request->id)->update([
                'hora1' => $request->hora1,
                'hora2' => $request->hora2,
                'cerrado' => $request->cbcerrado
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }

    public function indexConfiguracion(){
        return view('backend.admin.informacion.vistainformacion');
    }

    // tabla
    public function tablaConfiguracion(){
        $info = InformacionAdmin::get();

        return view('backend.admin.informacion.tablainformacion', compact('info'));
    }

    public function informacionConfiguracion(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){return ['success' => 0]; }

        if($info = InformacionAdmin::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $info];
        }else{
            return ['success' => 2];
        }
    }


    public function editarConfiguracion(Request $request){
        $rules = array(
            'id' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()){ return ['success' => 0];}

        $modo = (int) $request->modo_pedido;
        if (!in_array($modo, [1, 2, 3], true)) { return ['success' => 0]; }

        // WhatsApp: solo dígitos (código de país + número)
        $whatsapp = preg_replace('/\D/', '', (string) $request->whatsapp);
        if ($modo !== 1 && strlen($whatsapp) < 8) { return ['success' => 3]; }

        $minimo = (float) $request->minimo;
        if ($minimo < 0) { return ['success' => 0]; }

        // costo de envío (pedidos por WhatsApp sin cuenta)
        $envioLimite = (float) $request->envio_limite;
        $envioCosto = (float) $request->envio_costo;
        if ($envioLimite < 0 || $envioCosto < 0) { return ['success' => 0]; }

        if(InformacionAdmin::where('id', $request->id)->first()){

            InformacionAdmin::where('id', $request->id)->update([
                'mensaje_cerrado' => $request->mensaje,
                'cerrado' => $request->cbcerrado,
                'activo_slider' => $request->cbcarrusel,
                'modo_pedido' => $modo,
                'whatsapp_numero' => $whatsapp !== '' ? $whatsapp : '50375825072',
                'minimo_consumo' => $minimo,
                'envio_limite' => $envioLimite,
                'envio_costo' => $envioCosto,
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }





    // ******** TIPO DE SERVICIO /****************










    public function indexCerradoApp()
    {
        return view('backend.cerradoapp.vistacerrado');
    }

    public function tablaCerradoApp()
    {
        $arrayInfo = InformacionAdmin::all();
        return view('backend.cerradoapp.tablacerrado', compact('arrayInfo'));
    }

    public function infoCerradoApp(Request $request)
    {
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){return ['success' => 0]; }

        if($info = InformacionAdmin::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $info];
        }else{
            return ['success' => 2];
        }
    }

    public function actualizarCerradoApp(Request $request)
    {
        $rules = array(
            'id' => 'required',
            'estado' => 'required',
            'nombre' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()){return ['success' => 0]; }

        if(InformacionAdmin::where('id', $request->id)->first()){

            InformacionAdmin::where('id', $request->id)->update([
                'cerrado' => $request->estado,
                'mensaje_cerrado' => $request->nombre,
            ]);
            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }




}
