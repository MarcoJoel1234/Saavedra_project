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
                            $procesosClases[$contador] = array();
                            foreach (array_keys($camposNoCero) as $nombreCampo) {
                                array_push($procesosClases[$contador], $nombreCampo);
                            }
                            array_splice($procesosClases[$contador], 0, 2);
                            for ($j = 0; $j < count($procesosClases[$contador]); $j++) {
                                switch ($procesosClases[$contador][$j]) {
                                    case 'cepillado':
                                        $proceso = Cepillado::where('id_ot', $otArray[$i])->where('id_proceso', "Cepillado_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) {
                                            $pzasBuenas1[$contador] = count(Pza_cepillado::where('estado', 2)->where('correcto', 1)->where('id_proceso', $proceso->id)->get()) / 2;
                                            $pzasMalas1[$contador] = count(Pza_cepillado::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get()) / 2;
                                            if ($pzasMalas1[$contador] > 0) {
                                                $pzaMala = Pza_cepillado::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Cepillado"; //Nombre del proceso
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales1[$contador] = count(Pza_cepillado::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2;
                                        } else {
                                            $pzasBuenas1[$contador] = 0;
                                            $pzasMalas1[$contador] = 0;
                                            $pzasTotales1[$contador] = 0;
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas1; //Piezas buenas
                                        $procesos[$i][$j][1] = $pzasMalas1; //Piezas malas
                                        $procesos[$i][$j][2] = $pzasTotales1; //Piezas totales
                                        break;
                                    case 'desbaste_exterior':
                                        $proceso = DesbasteExterior::where('id_ot', $otArray[$i])->where('id_proceso', "desbaste_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) {
                                            $pzas = Desbaste_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas2[$contador] = $this->calcularPbuenasYPmalas($pzas)[0]; //Piezas buenas 
                                            $pzasMalas2[$contador] = $this->calcularPbuenasYPmalas($pzas)[1]; //Piezas malas

                                            if ($pzasMalas2[$contador] > 0) {
                                                $pzaMala = Desbaste_pza::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza; //Número de pieza
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first(); //Meta de la pieza
                                                    $operador = User::where('matricula', $meta->id_usuario)->first(); //Operador que realizó la pieza
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Desbaste Exterior"; //Nombre del proceso
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info); //Guarda la información de la pieza mala
                                                }
                                            }
                                            $pzasTotales2[$contador] = count(Desbaste_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2; //Piezas totales
                                        } else {
                                            $pzasBuenas2[$contador] = 0;
                                            $pzasMalas2[$contador] = 0;
                                            $pzasTotales2[$contador] = 0;
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas2;
                                        $procesos[$i][$j][1] = $pzasMalas2;
                                        $procesos[$i][$j][2] = $pzasTotales2;
                                        break;
                                    case 'revision_laterales':
                                        $proceso = RevLaterales::where('id_ot', $otArray[$i])->where('id_proceso', "revLaterales_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) {
                                            $pzas = RevLaterales_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas3[$contador] = $this->calcularPbuenasYPmalas($pzas)[0];
                                            $pzasMalas3[$contador] = $this->calcularPbuenasYPmalas($pzas)[1];

                                            if ($pzasMalas3[$contador] > 0) {
                                                $pzaMala = RevLaterales_pza::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get();
                                                $info = array(); //Guarda la información de la pieza mala
                                                for ($p = 0; $p < count($pzaMala); $p++) { //Recorre las piezas malas
                                                    $info[0] = $pzaMala[$p]->n_pieza; //Número de pieza
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Revision Laterales"; //Nombre del proceso
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales3[$contador] = count(RevLaterales_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2;
                                        } else {
                                            $pzasBuenas3[$contador] = 0;
                                            $pzasMalas3[$contador] = 0;
                                            $pzasTotales3[$contador] = 0; //Piezas totales
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas3; //Piezas buenas
                                        $procesos[$i][$j][1] = $pzasMalas3;
                                        $procesos[$i][$j][2] = $pzasTotales3;
                                        break;
                                    case 'pOperacion':
                                        $proceso = PrimeraOpeSoldadura::where('id_ot', $otArray[$i])->where('id_proceso', "1opeSoldadura_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) {
                                            $pzas = PrimeraOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas4[$contador] = $this->calcularPbuenasYPmalas($pzas)[0];
                                            $pzasMalas4[$contador] = $this->calcularPbuenasYPmalas($pzas)[1];

                                            if ($pzasMalas4[$contador] > 0) {
                                                $pzaMala = PrimeraOpeSoldadura_pza::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Primera Operacion Soldadura"; //Nombre del proceso
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales4[$contador] = count(PrimeraOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2;
                                        } else {
                                            $pzasBuenas4[$contador] = 0;
                                            $pzasMalas4[$contador] = 0;
                                            $pzasTotales4[$contador] = 0;
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas4;
                                        $procesos[$i][$j][1] = $pzasMalas4;
                                        $procesos[$i][$j][2] = $pzasTotales4;
                                        break;
                                    case 'barreno_maniobra':
                                        $proceso = BarrenoManiobra::where('id_ot', $otArray[$i])->where('id_proceso', "barrenoManiobra_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = BarrenoManiobra_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas5[$contador] = $this->calcularPbuenasYPmalas($pzas)[0];
                                            $pzasMalas5[$contador] = $this->calcularPbuenasYPmalas($pzas)[1];

                                            if ($pzasMalas5[$contador] > 0) {
                                                $pzaMala = BarrenoManiobra_pza::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Barreno Maniobra"; //Nombre del proceso
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales5[$contador] = count(BarrenoManiobra_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2;
                                        } else {
                                            $pzasBuenas5[$contador] = 0;
                                            $pzasMalas5[$contador] = 0;
                                            $pzasTotales5[$contador] = 0;
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas5;
                                        $procesos[$i][$j][1] = $pzasMalas5;
                                        $procesos[$i][$j][2] = $pzasTotales5;
                                        break;
                                    case 'sOperacion':
                                        $proceso = SegundaOpeSoldadura::where('id_ot', $otArray[$i])->where('id_proceso', "2opeSoldadura_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = SegundaOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas6[$contador] = $this->calcularPbuenasYPmalas($pzas)[0];
                                            $pzasMalas6[$contador] = $this->calcularPbuenasYPmalas($pzas)[1];

                                            if ($pzasMalas6[$contador] > 0) {
                                                $pzaMala = SegundaOpeSoldadura_pza::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_pieza;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Segunda Operacion Soldadura"; //Nombre del proceso
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales6[$contador] = count(SegundaOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2;
                                        } else {
                                            $pzasBuenas6[$contador] = 0;
                                            $pzasMalas6[$contador] = 0;
                                            $pzasTotales6[$contador] = 0;
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas6;
                                        $procesos[$i][$j][1] = $pzasMalas6;
                                        $procesos[$i][$j][2] = $pzasTotales6;
                                        break;
                                    case 'soldadura':
                                        $proceso = Soldadura::where('id_ot', $otArray[$i])->where('id_proceso', "soldadura_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = Soldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas7[$contador] = count(Soldadura_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas7[$contador] = count(Soldadura_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get());

                                            if ($pzasMalas7[$contador] > 0) {
                                                $pzaMala = Soldadura_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Soldadura"; //Nombre del proceso que se está realizando
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales7[$contador] = count(Soldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas7[$contador] = 0;
                                            $pzasMalas7[$contador] = 0;
                                            $pzasTotales7[$contador] = 0;
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas7;
                                        $procesos[$i][$j][1] = $pzasMalas7;
                                        $procesos[$i][$j][2] = $pzasTotales7;
                                        break;
                                    case 'soldaduraPTA':
                                        $proceso = SoldaduraPTA::where('id_ot', $otArray[$i])->where('id_proceso', "soldaduraPTA_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = SoldaduraPTA_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas8[$contador] = count(SoldaduraPTA_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas8[$contador] = count(SoldaduraPTA_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get());

                                            if ($pzasMalas8[$contador] > 0) {
                                                $pzaMala = SoldaduraPTA_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Soldadura PTA"; //Nombre del proceso que se está realizando
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales8[$contador] = count(SoldaduraPTA_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas8[$contador] = 0;
                                            $pzasMalas8[$contador] = 0;
                                            $pzasTotales8[$contador] = 0;
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas8;
                                        $procesos[$i][$j][1] = $pzasMalas8;
                                        $procesos[$i][$j][2] = $pzasTotales8;
                                        break;
                                    case 'rectificado':
                                        $proceso = Rectificado::where('id_ot', $otArray[$i])->where('id_proceso', "rectificado_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = Rectificado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas9[$contador] = count(Rectificado_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas9[$contador] = count(Rectificado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Rectificado')->get());

                                            if ($pzasMalas9[$contador] > 0) {
                                                $pzaMala = Rectificado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Rectificado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Rectificado"; //Nombre del proceso que se está realizando
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales9[$contador] = count(Rectificado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas9[$contador] = 0;
                                            $pzasMalas9[$contador] = 0;
                                            $pzasTotales9[$contador] = 0;
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas9;
                                        $procesos[$i][$j][1] = $pzasMalas9;
                                        $procesos[$i][$j][2] = $pzasTotales9;
                                        break;
                                    case 'asentado':
                                        $proceso = Asentado::where('id_ot', $otArray[$i])->where('id_proceso', "asentado_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = Asentado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas10[$contador] = count(Asentado_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas10[$contador] = count(Asentado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Asentado')->get());

                                            if ($pzasMalas10[$contador] > 0) {
                                                $pzaMala = Asentado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Asentado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Asentado"; //Nombre del proceso que se está realizando
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales10[$contador] = count(Asentado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas10[$contador] = 0;
                                            $pzasMalas10[$contador] = 0;
                                            $pzasTotales10[$contador] = 0;
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas10;
                                        $procesos[$i][$j][1] = $pzasMalas10;
                                        $procesos[$i][$j][2] = $pzasTotales10;
                                        break;
                                    case 'calificado':
                                        $proceso = revCalificado::where('id_ot', $otArray[$i])->where('id_proceso', "revCalificado_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = revCalificado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas11[$contador] = count(revCalificado_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas11[$contador] = count(revCalificado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get());

                                            if ($pzasMalas11[$contador] > 0) {
                                                $pzaMala = revCalificado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Calificado"; //Nombre del proceso que se está realizando
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales11[$contador] = count(revCalificado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas11[$contador] = 0;
                                            $pzasMalas11[$contador] = 0;
                                            $pzasTotales11[$contador] = 0;
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas11;
                                        $procesos[$i][$j][1] = $pzasMalas11;
                                        $procesos[$i][$j][2] = $pzasTotales11;
                                        break;
                                    case 'acabadoBombillo':
                                        $proceso = AcabadoBombilo::where('id_ot', $otArray[$i])->where('id_proceso', "acabadoBombillo_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = AcabadoBombilo_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                                $pzasBuenas12[$contador] = count(AcabadoBombilo_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas12[$contador] = count(AcabadoBombilo_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get());

                                            if ($pzasMalas12[$contador] > 0) {
                                                $pzaMala = AcabadoBombilo_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Acabado Bombillo"; //Nombre del proceso que se está realizando
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales12[$contador] = count(AcabadoBombilo_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas12[$contador] = 0;
                                            $pzasMalas12[$contador] = 0;
                                            $pzasTotales12[$contador] = 0;
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas12;
                                        $procesos[$i][$j][1] = $pzasMalas12;
                                        $procesos[$i][$j][2] = $pzasTotales12;
                                        break;
                                    case 'acabadoMolde':
                                        $proceso = AcabadoMolde::where('id_ot', $otArray[$i])->where('id_proceso', "acabadoMolde_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = AcabadoMolde_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas13[$contador] = count(AcabadoMolde_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas13[$contador] = count(AcabadoMolde_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get());

                                            if ($pzasMalas13[$contador] > 0) {
                                                $pzaMala = AcabadoMolde_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Acabado Molde"; //Nombre del proceso que se está realizando
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales13[$contador] = count(AcabadoMolde_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas13[$contador] = 0;
                                            $pzasMalas13[$contador] = 0;
                                            $pzasTotales13[$contador] = 0;
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas13;
                                        $procesos[$i][$j][1] = $pzasMalas13;
                                        $procesos[$i][$j][2] = $pzasTotales13;
                                        break;
                                    case 'cavidades':
                                        $proceso = Cavidades::where('id_ot', $otArray[$i])->where('id_proceso', "cavidades_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = Cavidades_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas14[$contador] = count(Cavidades_pza::where('estado', 2)->where('error', 'Ninguno')->get());
                                            $pzasMalas14[$contador] = count(Cavidades_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get());

                                            if ($pzasMalas14[$contador] > 0) {
                                                $pzaMala = Cavidades_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[1] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[2] = "Cavidades"; //Nombre del proceso que se está realizando
                                                    $info[3] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales14[$contador] = count(Cavidades_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas14[$contador] = 0;
                                            $pzasMalas14[$contador] = 0;
                                            $pzasTotales14[$contador] = 0;
                                        }
                                        $procesos[$i][$j][0] = $pzasBuenas14;
                                        $procesos[$i][$j][1] = $pzasMalas14;
                                        $procesos[$i][$j][2] = $pzasTotales14;
                                        break;
                                    case 'barreno_profundidad':
                                        $pzasBuenas15[$contador] = 0;
                                        $pzasMalas15[$contador] = 0;
                                        $pzasTotales15[$contador] = 0;

                                        $procesos[$i][$j][0] = $pzasBuenas15;
                                        $procesos[$i][$j][1] = $pzasMalas15;
                                        $procesos[$i][$j][2] = $pzasTotales15;
                                        break;
                                    case 'copiado':
                                        $pzasBuenas16[$contador] = 0;
                                        $pzasMalas16[$contador] = 0;
                                        $pzasTotales16[$contador] = 0;

                                        $procesos[$i][$j][0] = $pzasBuenas16;
                                        $procesos[$i][$j][1] = $pzasMalas16;
                                        $procesos[$i][$j][2] = $pzasTotales16;
                                        break;
                                    case 'offSet':
                                        $pzasBuenas17[$contador] = 0;
                                        $pzasMalas17[$contador] = 0;
                                        $pzasTotales17[$contador] = 0;

                                        $procesos[$i][$j][0] = $pzasBuenas17;
                                        $procesos[$i][$j][1] = $pzasMalas17;
                                        $procesos[$i][$j][2] = $pzasTotales17;
                                        break;
                                    case 'palomas':
                                        $pzasBuenas18[$contador] = 0;
                                        $pzasMalas18[$contador] = 0;
                                        $pzasTotales18[$contador] = 0;

                                        $procesos[$i][$j][0] = $pzasBuenas18;
                                        $procesos[$i][$j][1] = $pzasMalas18;
                                        $procesos[$i][$j][2] = $pzasTotales18;
                                        break;
                                    case 'rebajes':
                                        $pzasBuenas19[$contador] = 0;
                                        $pzasMalas19[$contador] = 0;
                                        $pzasTotales19[$contador] = 0;

                                        $procesos[$i][$j][0] = $pzasBuenas19;
                                        $procesos[$i][$j][1] = $pzasMalas19;
                                        $procesos[$i][$j][2] = $pzasTotales19;
                                        break;
                                    case 'grabado':
                                        $pzasBuenas20[$contador] = 0;
                                        $pzasMalas20[$contador] = 0;
                                        $pzasTotales20[$contador] = 0;

                                        $procesos[$i][$j][0] = $pzasBuenas20;
                                        $procesos[$i][$j][1] = $pzasMalas20;
                                        $procesos[$i][$j][2] = $pzasTotales20;
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


                                        $pzasBuenas21[$contador] = 0;
                                        $pzasMalas21[$contador] = 0;
                                        $pzasTotales21[$contador] = 0;

                                        $procesos[$i][$j][0] = $pzasBuenas21;
                                        $procesos[$i][$j][1] = $pzasMalas21;
                                        $procesos[$i][$j][2] = $pzasTotales21;
                                        break;
                                    case 'embudoCM':
                                        $pzasBuenas22[$contador] = 0;
                                        $pzasMalas22[$contador] = 0;
                                        $pzasTotales22[$contador] = 0;

                                        $procesos[$i][$j][0] = $pzasBuenas22;
                                        $procesos[$i][$j][1] = $pzasMalas22;
                                        $procesos[$i][$j][2] = $pzasTotales22;
                                        break;
                                    default:
                                        echo $nombreCampo;
                                        break;
                                }
                                $procesosClases[$contador][$j] = $this->nombreProceso($procesosClases[$contador][$j]);
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
            case "operacioneEquipo":
                return "Operacion Equipo";
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
