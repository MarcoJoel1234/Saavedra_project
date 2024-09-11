<?php

namespace App\Http\Controllers;

use App\Models\Orden_trabajo;
use App\Models\tiempoproduccion;
use Illuminate\Http\Request;

class tiemposProduccionController extends Controller
{
    protected $controladorPzas;
    public function __construct()
    {
        $this->controladorPzas = new PzasLiberadasController();
    }
    public function show($clase = false)
    {
        //Obtener el perfil del usuario
        $layout = $this->controladorPzas->obtenerLayout();
        
        $tiempos = [];
        $tiemposProduccion = tiempoproduccion::whereIn('clase', ['Bombillo', 'Molde'])->get();
        if ($tiemposProduccion->count() > 0) {
            foreach ($tiemposProduccion as $tiempo) {
                $tiempos[$tiempo->clase][$tiempo->proceso][$tiempo->tamanio] = $tiempos[$tiempo->clase][$tiempo->proceso][$tiempo->tamanio] ?? [];
                foreach ($tiempo->toArray() as $columna => $valor) {
                    if ($columna == 'clase' || $columna == 'proceso' || $columna == 'tamanio' || $columna == 'created_at' || $columna == 'updated_at') {
                        continue;
                    }
                    $tiempos[$tiempo->clase][$tiempo->proceso][$tiempo->tamanio][$columna] = $valor;
                }
            }
        } else {
            $tiempos = null;
        }
        if ($clase) {
            return view('processesAdmin.tiemposProduccion', compact('tiempos', 'clase', 'layout'));
        }
        return view('processesAdmin.tiemposProduccion', compact('tiempos', 'layout'));
    }
    public function store(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            if ($key == '_token' || $key == "clase") {
                continue;
            }
            $tamanios = ['Chico', 'Mediano', 'Grande'];
            foreach ($value as $k => $v) {
                echo $key;
                $tiempo = tiempoproduccion::where('clase', $request->input('clase'))->where('tamanio', $tamanios[$k])->where('proceso', $key)->first();
                if ($tiempo) {
                    if($v == null){
                        $v = 0;
                    }else{
                        $tiempo->tiempo = $v;
                    }
                    $tiempo->save();
                } else {
                    $tiempo = new tiempoproduccion();
                    $tiempo->clase = $request->input('clase');
                    $tiempo->tamanio = $tamanios[$k];
                    $tiempo->proceso = $key;
                    if($v == null){
                        $v = 0;
                    }else{
                        $tiempo->tiempo = $v;
                    }
                    $tiempo->save();
                }
            }
        }
        $clase = $request->input('clase');


        //Actualizar todas las Clases
        return redirect()->route("mostrarTiempos", compact('clase'));
    }
    public function actualizarClases(){
        // $clasesFP = 
    }
}
