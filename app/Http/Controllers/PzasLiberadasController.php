<?php

namespace App\Http\Controllers;

use App\Models\AcabadoBombilo;
use App\Models\AcabadoBombilo_pza;
use App\Models\AcabadoMolde;
use App\Models\AcabadoMolde_pza;
use App\Models\Asentado_pza;
use App\Models\BarrenoManiobra;
use App\Models\BarrenoManiobra_pza;
use App\Models\BarrenoProfundidad_pza;
use App\Models\Cavidades_pza;
use App\Models\Copiado_pza;
use App\Models\Desbaste_pza;
use App\Models\EmbudoCM_pza;
use App\Models\Metas;
use App\Models\OffSet_pza;
use App\Models\Orden_trabajo;
use App\Models\Palomas_pza;
use App\Models\Pieza;
use App\Models\PrimeraOpeSoldadura;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\PySOpeSoldadura;
use App\Models\PySOpeSoldadura_pza;
use App\Models\Pza_cepillado;
use App\Models\Rebajes_pza;
use App\Models\Rectificado_pza;
use App\Models\revCalificado_pza;
use App\Models\RevLaterales_pza;
use App\Models\SegundaOpeSoldadura;
use App\Models\SegundaOpeSoldadura_pza;
use App\Models\Soldadura_pza;
use App\Models\SoldaduraPTA_pza;
use ArchTech\Enums\Meta\Meta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PzasLiberadasController extends Controller
{
    protected $controladorPzas;
    public function __construct()
    {
        $this->controladorPzas = new PzasGeneralesController();
    }
    public function mostrarOTs()
    {
        if ($this->controladorPzas->retornarOTs() != 0) {
            $arregloOT = $this->controladorPzas->retornarOTs();
            return view('processesQuality.LiberarPiezas.OT', compact('arregloOT'));
        } else {
            return view('processesQuality.LiberarPiezas.OT');
        }
    }
    public function obtenerPiezasRequest(Request $request)
    {
        $datosPiezas = array(
            "ot" => $request->ot,
            "clase" => $request->clase,
            "operador" => $request->operador,
            "maquina" => $request->maquina,
            "proceso" => $request->proceso,
            "error" => $request->error,
            "fecha" => $request->fecha,
            "action" => $request->input("action"),
        );
        return $this->show($this->controladorPzas->search($datosPiezas, 'quality'));
    }
    public function show($array)
    {
        if ($array[0]) {
            return view('processesQuality.LiberarPiezas.pzasLiberar', ['piezas' => $array[1], 'otElegida' => $array[2], 'clase' => $array[3], 'operadores' => $array[4], 'maquina' => $array[5], 'array' => $array[6], 'proceso' => $array[7], 'error' => $array[8], 'infoPiezas' => $array[9]]);
        } else {
            //Eliminar el último elemento del array
            $contador = 0;
            foreach ($array[1] as $pza) {
                array_pop($pza);
                $array[1][$contador] = $pza;
                $contador++;
            }
            $pdf = Pdf::loadView('processesAdmin.ReportePiezas.pdf', ['piezas' => $array[1], 'otElegida' => $array[2], 'clase' => $array[3], 'operadores' => $array[4], 'maquina' => $array[5], 'array' => $array[6], 'proceso' => $array[7], 'error' => $array[8]]);
            return $pdf->download('Informe de piezas.pdf');
        }
    }
    public function liberar_rechazar($pieza, $proceso, $liberar, $buena, $request) //Función para liberar o rechazar piezas
    {
        if ($liberar == 'true') {
            $this->liberarPiezas($this->getPiezasLiberar($pieza, $proceso, $buena), $proceso, $buena);
        } else {
            $this->rechazarPieza($this->getPiezasLiberar($pieza, $proceso, $buena), $proceso);
        }
        //Datos de las piezas
        $request = explode(",", $request);
        $datosPiezas = array(
            "ot" => $request[0],
            "clase" => $request[1],
            "operador" => $request[2],
            "maquina" => $request[3],
            "proceso" => $request[4],
            "error" => $request[5],
            "fecha" => $request[6],
            "action" => null,
        );
        return $this->show($this->controladorPzas->search($datosPiezas, 'quality'));
    }

    public function getPiezasLiberar($juego, $proceso, $buena)
    {
        $juego = explode(",", $juego);
        switch ($proceso) {
            case "Cepillado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Pza_cepillado::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Pza_cepillado::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Desbaste Exterior":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Desbaste_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Desbaste_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Revision Laterales":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = RevLaterales_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = RevLaterales_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Primera Operacion Soldadura":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = PrimeraOpeSoldadura_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = PrimeraOpeSoldadura_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Barreno Maniobra":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = BarrenoManiobra_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = BarrenoManiobra_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Segunda Operacion Soldadura":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = SegundaOpeSoldadura_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = SegundaOpeSoldadura_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Soldadura":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Soldadura_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Soldadura_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Soldadura PTA":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = SoldaduraPTA_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = SoldaduraPTA_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Rectificado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Rectificado_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Rectificado_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Asentado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Asentado_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Asentado_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Revision Calificado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = revCalificado_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = revCalificado_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Acabado Bombillo":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = AcabadoBombilo_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = AcabadoBombilo_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Acabado Molde":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = AcabadoMolde_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = AcabadoMolde_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Cavidades":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Cavidades_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Cavidades_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Barreno Profundidad":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = BarrenoProfundidad_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = BarrenoProfundidad_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Copiado":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Copiado_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Copiado_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Off Set":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = OffSet_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = OffSet_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Palomas":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Palomas_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Palomas_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Rebajes":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = Rebajes_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = Rebajes_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Operacion Equipo_1":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = PySOpeSoldadura_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = PySOpeSoldadura_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Operacion Equipo_2":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = PySOpeSoldadura_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = PySOpeSoldadura_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
            case "Embudo CM":
                $pieza = array();
                foreach ($juego as $pza) {
                    $p = EmbudoCM_pza::where('id_pza', $pza)->first();
                    array_push($pieza, $p);
                }
                $piezas = EmbudoCM_pza::where('id_meta', $pieza[0]->id_meta)->get();
                break;
        }
        if ($buena == 'false') {
            $piezas = $pieza;
        }
        return $piezas;
    }
    public function liberarPiezas($piezas, $proceso, $buena)
    {
        //Identificar los juegos malos
        $meta = Metas::find($piezas[0]->id_meta);
        $juegosMalos = $this->juegosMalos($meta, $proceso);
        if ($buena == 'true') {
            foreach ($piezas as $pza) {
                //Actualizar el estado de liberacion de la pieza
                if ($pza->n_pieza) {
                    $numero = substr($pza->n_pieza, 0, -1);
                    $piezaH = Pieza::where('n_pieza', $numero . "H")->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->where('error', 'Ninguno')->where('liberacion', 0)->first();
                    $piezaM = Pieza::where('n_pieza', $numero . "M")->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->where('error', 'Ninguno')->where('liberacion', 0)->first();

                    if($piezaH && $piezaM){
                        if (!in_array($numero, $juegosMalos)) {
                            $piezaH->liberacion = 1;
                            $piezaH->fecha_liberacion = date('Y-m-d H:i:s');
                            $piezaH->user_liberacion = auth()->user()->matricula;
                            $piezaH->save();

                            $piezaM->liberacion = 1;
                            $piezaM->fecha_liberacion = date('Y-m-d H:i:s');
                            $piezaM->user_liberacion = auth()->user()->matricula;
                            $piezaM->save();
                        }    
                    }
                } else {
                    $pieza = Pieza::where('n_pieza', $pza->n_juego)->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->where('error', 'Ninguno')->where('liberacion', 0)->first();
                    if ($pieza) {
                        if (!in_array($this->controladorPzas->getPiezaNumber($pieza->n_pieza), $juegosMalos)) {
                            $pieza->liberacion = 1;
                            $pieza->fecha_liberacion = date('Y-m-d H:i:s');
                            $pieza->user_liberacion = auth()->user()->matricula;
                            $pieza->save();
                        }
                    }
                }
            }
        } else {
            $meta = Metas::find($piezas[0]->id_meta);
            //Actualizar el estado de liberacion de la pieza
            foreach ($piezas as $pza) {
                if ($pza->n_pieza) {
                    $n_pieza = $pza->n_pieza;
                } else {
                    $n_pieza = $pza->n_juego;
                }
                Pieza::where('n_pieza', $n_pieza)->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->update([
                    'liberacion' => 1,
                    'fecha_liberacion' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
    public function rechazarPieza($piezas, $proceso)
    {
        $meta = Metas::find($piezas[0]->id_meta);
        //Actualizar el estado de liberacion de la pieza
        foreach ($piezas as $pza) {
            if ($pza->n_pieza) {
                $n_pieza = $pza->n_pieza;
            } else {
                $n_pieza = $pza->n_juego;
            }
            Pieza::where('n_pieza', $n_pieza)->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->update([
                'liberacion' => 2,
                'fecha_liberacion' => date('Y-m-d H:i:s'),
            ]);
        }
    }
    public function juegosMalos($meta, $proceso)
    {
        $juegosMalos = array();
        $piezasMalas = Pieza::where('id_operador', $meta->id_usuario)->where('id_clase', $meta->id_clase)->where('proceso', $proceso)->where('error', '!=', 'Ninguno')->where('liberacion', 0)->get();
        foreach ($piezasMalas as $pieza) {
            //Obtener el numero de juego
            if ($pieza->n_pieza) {
                $nPieza = $pieza->n_pieza;
            } else {
                $nPieza = $pieza->n_juego;
            }
            if (!in_array($this->controladorPzas->getPiezaNumber($nPieza), $juegosMalos)) {
                array_push($juegosMalos, $this->controladorPzas->getPiezaNumber($nPieza));
            }
        }
        return $juegosMalos;
    }
    public function liberarPiezasMeta($meta, $piezasMeta, $piezaLiberar, $proceso){
        foreach($piezasMeta as $pieza){
            if($pieza->n_pieza){
                $numero = substr($pieza->n_pieza, 0, -1);
                $piezaLiberadaH = Pieza::where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $numero . "H")->where('error', 'Ninguno')->where('liberacion', 1)->first();
                $piezaLiberadaM = Pieza::where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $numero . "M")->where('error', 'Ninguno')->where('liberacion', 1)->first();

                if($piezaLiberadaH && $piezaLiberadaM){
                    $piezaLiberada = true;
                }else{
                    $piezaLiberada = false;
                }
            }else{
                $piezaLiberada = Pieza::where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $pieza->n_juego)->where('error', 'Ninguno')->first();
            }

            if($piezaLiberada){
                if(substr($piezaLiberar, -1) == "H" || substr($piezaLiberar, -1) == "M"){
                    $numero = substr($piezaLiberar, 0, -1);
                    $piezaLiberarH = Pieza::where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $numero . "H")->where('error', 'Ninguno')->first();
                    $piezaLiberarM = Pieza::where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $numero . "M")->where('error', 'Ninguno')->first();
                    
                    if($piezaLiberarH && $piezaLiberarM){
                        $piezaLiberarH->liberacion = 1;
                        $piezaLiberarH->fecha_liberacion = date('Y-m-d H:i:s');
                        $piezaLiberarH->user_liberacion = $piezaLiberadaH->user_liberacion;
                        $piezaLiberarH->save();

                        $piezaLiberarM->liberacion = 1;
                        $piezaLiberarM->fecha_liberacion = date('Y-m-d H:i:s');
                        $piezaLiberarM->user_liberacion = $piezaLiberadaH->user_liberacion;
                        $piezaLiberarM->save();
                        return;
                    }
                }else{
                    $piezaLiberar = Pieza::where('id_ot', $meta->id_ot)->where('id_clase', $meta->id_clase)->where('id_operador', $meta->id_usuario)->where('proceso', $proceso)->where('n_pieza', $piezaLiberar)->where('error', 'Ninguno')->first();
                    $piezaLiberar->liberacion = 1;
                    $piezaLiberar->fecha_liberacion = date('Y-m-d H:i:s');
                    $piezaLiberar->user_liberacion = $piezaLiberada->user_liberacion;
                    $piezaLiberar->save();
                    return;
                }
            }
        }
    }
}
