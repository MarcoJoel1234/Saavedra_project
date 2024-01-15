<?php

namespace App\Http\Controllers;

use App\Models\AcabadoBombilo;
use App\Models\AcabadoBombilo_pza;
use App\Models\AcabadoMolde;
use App\Models\AcabadoMolde_pza;
use App\Models\Asentado;
use App\Models\Asentado_pza;
use App\Models\BarrenoManiobra;
use App\Models\BarrenoManiobra_pza;
use App\Models\Cavidades;
use App\Models\Cavidades_pza;
use App\Models\Cepillado;
use App\Models\Clase;
use App\Models\Desbaste_pza;
use App\Models\DesbasteExterior;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\PrimeraOpeSoldadura;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\Procesos;
use App\Models\PySOpeSoldadura;
use App\Models\PySOpeSoldadura_pza;
use App\Models\Pza_cepillado;
use App\Models\Rectificado;
use App\Models\Rectificado_pza;
use App\Models\revCalificado;
use App\Models\revCalificado_pza;
use App\Models\RevLaterales;
use App\Models\RevLaterales_pza;
use App\Models\SegundaOpeSoldadura;
use App\Models\SegundaOpeSoldadura_pza;
use App\Models\Soldadura;
use App\Models\Soldadura_pza;
use App\Models\SoldaduraPTA;
use App\Models\SoldaduraPTA_pza;
use App\Models\User;
use ArchTech\Enums\Meta\Meta;
use Illuminate\Http\Request;

class GestionOTController extends Controller
{
    public function show()
    {
        $otDispo = array();
        $ot = Orden_trabajo::all();
        for ($i = 0; $i < count($ot); $i++) {
            $claseDispo = Clase::where('id_ot', $ot[$i]->id)->where('finalizada', 0)->get();
            if (isset($claseDispo) > 0) {
                foreach ($claseDispo as $class) {
                    $process = Procesos::where('id_clase', $class->id)->first();
                    if ($process) {
                        if (!in_array($ot[$i]->id, $otDispo)) {
                            array_push($otDispo, $ot[$i]->id);
                        }
                    }
                }
            }
        }
        if (isset($otDispo)) {
            if (count($otDispo) > 0) {
                $molduras = array();
                $otArray = array();
                $clases = array();
                $pedidos = array();
                $procesos = array();
                $infoPzMala = array();
                $procesosClases = array();
                for ($i = 0; $i < count($otDispo); $i++) {
                    $otUtilizar = Orden_trabajo::find($otDispo[$i]);
                    //Ordenes de trabajo
                    array_push($otArray, $otUtilizar->id);
                    //Nombre de las molduras 
                    $moldura = Moldura::find($otUtilizar->id_moldura);
                    array_push($molduras, $moldura->nombre);

                    //Nombre de las clases y cantidad de pedidos en la orden de trabajo
                    $clase = Clase::where('id_ot', $otUtilizar->id)->where('finalizada', 0)->get();
                    $contador = 0;
                    foreach ($clase as $clase) {
                        $clases[$i][$contador] = $clase->nombre;
                        $pedidos[$i][$contador] = $clase->pedido;
                        $infoPzMala[$i][$contador] = array();
                        //Piezas buenas, malas y totales de cada proceso
                        $procesosClase = Procesos::where('id_clase', $clase->id)->first();
                        if ($procesosClase) {
                            $procesosClase = $procesosClase->toArray();
                            $camposNoCero = array_filter($procesosClase, function ($valor) {
                                return $valor != 0;
                            });
                            $procesosClases[$i][$contador] = array();
                            foreach (array_keys($camposNoCero) as $nombreCampo) {
                                array_push($procesosClases[$i][$contador], $nombreCampo);
                            }
                            array_splice($procesosClases[$i][$contador], 0, 2);
                            for ($j = 0; $j < count($procesosClases[$i][$contador]); $j++) {
                                switch ($procesosClases[$i][$contador][$j]) {
                                    case 'cepillado':
                                        $proceso = Cepillado::where('id_ot', $otArray[$i])->where('id_proceso', "Cepillado_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) {
                                            // $pzasBuenas = count(Pza_cepillado::where('estado', 2)->where('correcto', 1)->where('id_proceso', $proceso->id)->get()) / 2;
                                            // if(is_double($pzasBuenas)){
                                            //     $pzasBuenas = intval($pzasBuenas);
                                            // }
                                            // $pzasMalas = count(Pza_cepillado::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get()) / 2;
                                            // if(is_double($pzasMalas)){
                                            //     $pzasMalas += .5;
                                            // }
                                            //Actualizar resultado de la meta
                                            $contadorPzas = 0;
                                            $pzasMalas = 0;
                                            $juegosUsados = array();
                                            $pzasCorrectas = Pza_cepillado::where('estado', 2)->where('id_proceso', $proceso->id)->get(); //Obtención de todas las piezas correctas.
                                            foreach ($pzasCorrectas as $pzaCorrecta) {
                                                $pzaCorrecta2 = Pza_cepillado::where('n_juego', $pzaCorrecta->n_juego)->where('id_proceso', $proceso->id)->get();
                                                if (!in_array($pzaCorrecta->n_juego, $juegosUsados)) {
                                                    array_push($juegosUsados, $pzaCorrecta->n_juego);
                                                    $pzasMalasC = 0;
                                                    foreach ($pzaCorrecta2 as $pza) {
                                                        if ($pza->correcto == 1) {
                                                            $contadorPzas += .5;
                                                        } else if ($pza->correcto === 0) {
                                                            $pzasMalasC++;
                                                        }
                                                    }
                                                    if ($pzasMalasC > 0) {
                                                        if($pzasMalasC != 2){
                                                            $contadorPzas -= .5;
                                                        }
                                                        $pzasMalas++;
                                                    }
                                                }
                                            }
                                            $pzasBuenas = $contadorPzas;

                                            if ($pzasMalas > 0) {
                                                $pzaMala = Pza_cepillado::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Cepillado"; //Nombre del proceso
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(Pza_cepillado::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2;
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas; //Piezas buenas
                                        $procesos[$i][$contador][$j][1] = $pzasMalas; //Piezas malas
                                        $procesos[$i][$contador][$j][2] = $pzasTotales; //Piezas totales
                                        break;
                                    case 'desbaste_exterior':
                                        $proceso = DesbasteExterior::where('id_ot', $otArray[$i])->where('id_proceso', "desbaste_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) {
                                            $pzas = Desbaste_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = $this->calcularPbuenasYPmalas($pzas)[0]; //Piezas buenas 
                                            $pzasMalas = $this->calcularPbuenasYPmalas($pzas)[1]; //Piezas malas

                                            if ($pzasMalas > 0) {
                                                $pzaMala = Desbaste_pza::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza; //Número de pieza
                                                    $info[1] = $pzaMala[$p]->n_juego; //Número de pieza
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first(); //Meta de la pieza
                                                    $operador = User::where('matricula', $meta->id_usuario)->first(); //Operador que realizó la pieza
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Desbaste Exterior"; //Nombre del proceso
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info); //Guarda la información de la pieza mala
                                                }
                                            }
                                            $pzasTotales = count(Desbaste_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2; //Piezas totales
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'revision_laterales':
                                        $proceso = RevLaterales::where('id_ot', $otArray[$i])->where('id_proceso', "revLaterales_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) {
                                            $pzas = RevLaterales_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = $this->calcularPbuenasYPmalas($pzas)[0];
                                            $pzasMalas = $this->calcularPbuenasYPmalas($pzas)[1];

                                            if ($pzasMalas > 0) {
                                                $pzaMala = RevLaterales_pza::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get();
                                                $info = array(); //Guarda la información de la pieza mala
                                                for ($p = 0; $p < count($pzaMala); $p++) { //Recorre las piezas malas
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Revision Laterales"; //Nombre del proceso
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(RevLaterales_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2;
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0; //Piezas totales
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas; //Piezas buenas
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'pOperacion':
                                        $proceso = PrimeraOpeSoldadura::where('id_ot', $otArray[$i])->where('id_proceso', "1opeSoldadura_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) {
                                            $pzas = PrimeraOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = $this->calcularPbuenasYPmalas($pzas)[0];
                                            $pzasMalas = $this->calcularPbuenasYPmalas($pzas)[1];

                                            if ($pzasMalas > 0) {
                                                $pzaMala = PrimeraOpeSoldadura_pza::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Primera Operacióon Soldadura"; //Nombre del proceso
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales[$contador] = count(PrimeraOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2;
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'barreno_maniobra':
                                        $proceso = BarrenoManiobra::where('id_ot', $otArray[$i])->where('id_proceso', "barrenoManiobra_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = BarrenoManiobra_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = $this->calcularPbuenasYPmalas($pzas)[0];
                                            $pzasMalas = $this->calcularPbuenasYPmalas($pzas)[1];

                                            if ($pzasMalas > 0) {
                                                $pzaMala = BarrenoManiobra_pza::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Barreno Maniobra"; //Nombre del proceso
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(BarrenoManiobra_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2;
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'sOperacion':
                                        $proceso = SegundaOpeSoldadura::where('id_ot', $otArray[$i])->where('id_proceso', "2opeSoldadura_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = SegundaOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = $this->calcularPbuenasYPmalas($pzas)[0];
                                            $pzasMalas = $this->calcularPbuenasYPmalas($pzas)[1];

                                            if ($pzasMalas > 0) {
                                                $pzaMala = SegundaOpeSoldadura_pza::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Segunda Operacion Soldadura"; //Nombre del proceso
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(SegundaOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2;
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'soldadura':
                                        $proceso = Soldadura::where('id_ot', $otArray[$i])->where('id_proceso', "soldadura_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = Soldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(Soldadura_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas = count(Soldadura_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = Soldadura_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Soldadura"; //Nombre del proceso que se está realizando
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(Soldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'soldaduraPTA':
                                        $proceso = SoldaduraPTA::where('id_ot', $otArray[$i])->where('id_proceso', "soldaduraPTA_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = SoldaduraPTA_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(SoldaduraPTA_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas = count(SoldaduraPTA_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = SoldaduraPTA_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Soldadura PTA"; //Nombre del proceso que se está realizando
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(SoldaduraPTA_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'rectificado':
                                        $proceso = Rectificado::where('id_ot', $otArray[$i])->where('id_proceso', "rectificado_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = Rectificado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(Rectificado_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas = count(Rectificado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Rectificado')->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = Rectificado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Rectificado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Rectificado"; //Nombre del proceso que se está realizando
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(Rectificado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'asentado':
                                        $proceso = Asentado::where('id_ot', $otArray[$i])->where('id_proceso', "asentado_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = Asentado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(Asentado_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas = count(Asentado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Asentado')->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = Asentado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Asentado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Asentado"; //Nombre del proceso que se está realizando
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(Asentado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'calificado':
                                        $proceso = revCalificado::where('id_ot', $otArray[$i])->where('id_proceso', "revCalificado_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = revCalificado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(revCalificado_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas = count(revCalificado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = revCalificado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Calificado"; //Nombre del proceso que se está realizando
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(revCalificado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'acabadoBombillo':
                                        $proceso = AcabadoBombilo::where('id_ot', $otArray[$i])->where('id_proceso', "acabadoBombillo_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = AcabadoBombilo_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(AcabadoBombilo_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas = count(AcabadoBombilo_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = AcabadoBombilo_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Acabado Bombillo"; //Nombre del proceso que se está realizando
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(AcabadoBombilo_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'acabadoMolde':
                                        $proceso = AcabadoMolde::where('id_ot', $otArray[$i])->where('id_proceso', "acabadoMolde_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = AcabadoMolde_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(AcabadoMolde_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas = count(AcabadoMolde_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = AcabadoMolde_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Acabado Molde"; //Nombre del proceso que se está realizando
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(AcabadoMolde_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'cavidades':
                                        $proceso = Cavidades::where('id_ot', $otArray[$i])->where('id_proceso', "cavidades_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = Cavidades_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(Cavidades_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas = count(Cavidades_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = Cavidades_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Cavidades"; //Nombre del proceso que se está realizando
                                                    $info[4] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(Cavidades_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'barreno_profundidad':
                                        $pzasBuenas = 0;
                                        $pzasMalas = 0;
                                        $pzasTotales = 0;

                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'copiado':
                                        $pzasBuenas = 0;
                                        $pzasMalas = 0;
                                        $pzasTotales = 0;

                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'offSet':
                                        $pzasBuenas = 0;
                                        $pzasMalas = 0;
                                        $pzasTotales = 0;

                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'palomas':
                                        $pzasBuenas = 0;
                                        $pzasMalas = 0;
                                        $pzasTotales = 0;

                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'rebajes':
                                        $pzasBuenas = 0;
                                        $pzasMalas = 0;
                                        $pzasTotales = 0;

                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'grabado':
                                        $pzasBuenas = 0;
                                        $pzasMalas = 0;
                                        $pzasTotales = 0;

                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'operacionEquipo':
                                        // $proceso = PySOpeSoldadura::where('id_ot', $otArray[$i])->where('id_clase', $clase->id)->get();
                                        // if (isset($proceso)) {
                                        //     for ($l = 0; $l < count($proceso); $l++) {

                                        //         $condiciones = [
                                        //             ['estado', '=', 2],
                                        //             ['id_proceso', '=', $proceso[$l]->id],
                                        //             // Puedes agregar más condiciones aquí según sea necesario
                                        //         ];

                                        //         // Construir la consulta con las condiciones dinámicas
                                        //         $pzas = PySOpeSoldadura_pza::where(function ($query) use ($condiciones) {
                                        //             foreach ($condiciones as $condicion) {
                                        //                 $query->where($condicion[0], $condicion[1], $condicion[2]);
                                        //             }
                                        //         })->get();
                                        //     }
                                        //     echo $pzas;



                                        // echo $pzasBuenas6[$contador] = $this->calcularPbuenasYPmalas($pzas)[0];
                                        // echo $pzasMalas6[$contador] = $this->calcularPbuenasYPmalas($pzas)[1];
                                        //     if ($pzasMalas6[$contador] > 0) {
                                        //         for ($l = 0; $l < count($proceso); $l++) {
                                        //             $condiciones = [
                                        //                 ['estado', '=', 2],
                                        //                 ['correcto', '=', 0],
                                        //                 ['id_proceso', '=', $proceso[$l]->id],
                                        //                 // Puedes agregar más condiciones aquí según sea necesario
                                        //             ];

                                        //             // Construir la consulta con las condiciones dinámicas
                                        //             $pzaMala = PySOpeSoldadura_pza::where(function ($query) use ($condiciones) {
                                        //                 foreach ($condiciones as $condicion) {
                                        //                     $query->where($condicion[0], $condicion[1], $condicion[2]);
                                        //                 }
                                        //             })->get();
                                        //         }
                                        //         $info = array();
                                        //         for ($p = 0; $p < count($pzaMala); $p++) {
                                        //             $info[0] = $pzaMala[$p]->n_juego;
                                        //             $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                        //             $operador = User::where('matricula', $meta->id_usuario)->first();
                                        //             $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                        //             $info[2] = "Primera y Segunda Operacion Equipo"; //Nombre del proceso
                                        //             $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                        //             array_push($infoPzMala[$i][$contador], $info);
                                        //         }
                                        //     }
                                        //     for ($l = 0; $l < count($proceso); $l++) {
                                        //         $condiciones = [
                                        //             ['estado', '=', 2],
                                        //             ['id_proceso', '=', $proceso[$l]->id],
                                        //             // Agregar más condiciones aquí según sea necesario
                                        //         ];

                                        //         // Construir la consulta con las condiciones dinámicas
                                        //         $pzasT = PySOpeSoldadura_pza::where(function ($query) use ($condiciones) {
                                        //             foreach ($condiciones as $condicion) {
                                        //                 $query->where($condicion[0], $condicion[1], $condicion[2]);
                                        //             }
                                        //         })->get();
                                        //     }
                                        //     $pzasTotales6[$contador] = count($pzasT);
                                        // } else {
                                        //     $pzasBuenas6[$contador] = 0;
                                        //     $pzasMalas6[$contador] = 0;
                                        //     $pzasTotales6[$contador] = 0;
                                        // }
                                        // $procesos[$i][$j][0] = $pzasBuenas6;
                                        // $procesos[$i][$j][1] = $pzasMalas6;
                                        // $procesos[$i][$j][2] = $pzasTotales6;


                                        $pzasBuenas = 0;
                                        $pzasMalas = 0;
                                        $pzasTotales = 0;

                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'embudoCM':
                                        $pzasBuenas = 0;
                                        $pzasMalas = 0;
                                        $pzasTotales = 0;

                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                }
                                $procesosClases[$i][$contador][$j] = $this->nombreProceso($procesosClases[$i][$contador][$j]);
                            }
                            $contador++;
                        }
                    }
                }
                return view('processesAdmin.viewPiezas', ['ot' => $ot, 'otArray' => $otArray, 'molduras' => $molduras, 'clases' => $clases, 'pedidos' => $pedidos, 'procesos' => $procesos, 'infoPzMala' => $infoPzMala, 'procesosClase' => $procesosClases]);
            }
        }
        return view('processesAdmin.viewPiezas');
    }
    public function nombreProceso($proceso)
    {
        switch ($proceso) {
            case "cepillado":
                return "Cepillado";
            case "desbaste_exterior":
                return "Desbaste Exterior";
            case "revision_laterales":
                return "Revision Laterales";
            case "pOperacion":
                return "Primera Operacion Soldadura";
            case "barreno_maniobra":
                return "Barreno maniobra";
            case "sOperacion":
                return "Segunda Operacion Soldadura";
            case "soldadura":
                return "Soldadura";
            case "soldaduraPTA":
                return "Soldadura PTA";
            case "rectificado":
                return "Rectificado";
            case "asentado":
                return "Asentado";
            case "calificado":
                return "Calificado";
            case "acabadoBombillo":
                return "Acabado Bombillo";
            case "acabadoMolde":
                return "Acabado Molde";
            case "cavidades":
                return "Cavidades";
            case "barreno_profundidad":
                return "Barreno Profundidad";
            case "copiado":
                return "Copiado";
            case "offSet":
                return "Off Set";
            case "palomas":
                return "Palomas";
            case "rebajes":
                return "Rebajes";
            case "grabado":
                return "Grabado";
            case "operacionEquipo":
                return "Operación Equipo";
            case "embudoCM":
                return "Embudo CM";
        }
    }
    function terminarPedido(Request $request)
    {
        echo $request->ot; //Imprime el id de la o
        echo $request->clase;
        $clase = Clase::where('id_ot', $request->ot)->where('nombre', $request->clase)->first();
        $clase->finalizada = 1;
        $clase->save(); //Guarda la clase como finalizada
        return redirect()->route('vistaPiezas'); //Redirecciona a la vista de piezas 
    }
    public function calcularPbuenasYPmalas($pzasCorrectas) //Calcula las piezas buenas y malas
    {
        $buenas = 0;
        $malas = 0;
        $juegosUtilizados = array(); //Guarda los juegos que ya se utilizaron 
        $malosUtilizados = array();
        for ($x = 0; $x < count($pzasCorrectas); $x++) {
            for ($y = 0; $y < count($pzasCorrectas); $y++) {
                if ($pzasCorrectas[$x]->n_juego === $pzasCorrectas[$y]->n_juego && $x != $y) {
                    if ($pzasCorrectas[$x]->correcto == 1 && $pzasCorrectas[$y]->correcto == 1) {
                        if (array_search($pzasCorrectas[$x]->n_juego, $juegosUtilizados) === false) {
                            array_push($juegosUtilizados, $pzasCorrectas[$x]->n_juego);
                            $buenas++;
                        }
                    } else {
                        if (array_search($pzasCorrectas[$x]->n_juego, $malosUtilizados) === false) {
                            array_push($malosUtilizados, $pzasCorrectas[$x]->n_juego);
                            $malas++;
                        }
                    }
                }
            }
        }
        return [$buenas, $malas];
    }
}
