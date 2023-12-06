<?php

namespace App\Http\Controllers;

use App\Models\Cepillado;
use App\Models\Clase;
use App\Models\Desbaste_pza;
use App\Models\DesbasteExterior;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\PrimeraOpeSoldadura;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\PySOpeSoldadura;
use App\Models\PySOpeSoldadura_pza;
use App\Models\Pza_cepillado;
use App\Models\RevLaterales;
use App\Models\RevLaterales_pza;
use App\Models\SegundaOpeSoldadura;
use App\Models\SegundaOpeSoldadura_pza;
use App\Models\User;
use ArchTech\Enums\Meta\Meta;
use Illuminate\Http\Request;

class GestionOTController extends Controller
{
    public function show()
    {
        $ordenesT = 0;
        $ot = Orden_trabajo::all();
        for ($i = 0; $i < count($ot); $i++) {
            $claseDispo = Clase::where('id_ot', $ot[$i]->id)->where('finalizada', 0)->first();
            if (isset($claseDispo) > 0) {
                $otDispo[$ordenesT] = $claseDispo->id_ot;
                $ordenesT++;
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
                        //Piezas buenas, malas y totales
                        for ($j = 0; $j < 22; $j++) {
                            switch ($j) {
                                case 0:
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
                                case 1:
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
                                case 2:
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
                                case 3:
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
                                case 4:
                                    $proceso = SegundaOpeSoldadura::where('id_ot', $otArray[$i])->where('id_proceso', "2opeSoldadura_" . $clase->nombre . "_" . $otArray[$i])->first();
                                    if (isset($proceso)) { //Si existe el proceso
                                        $pzas = SegundaOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                        $pzasBuenas5[$contador] = $this->calcularPbuenasYPmalas($pzas)[0];
                                        $pzasMalas5[$contador] = $this->calcularPbuenasYPmalas($pzas)[1];

                                        if ($pzasMalas5[$contador] > 0) {
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
                                        $pzasTotales5[$contador] = count(SegundaOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2;
                                    } else {
                                        $pzasBuenas5[$contador] = 0;
                                        $pzasMalas5[$contador] = 0;
                                        $pzasTotales5[$contador] = 0;
                                    }
                                    $procesos[$i][$j][0] = $pzasBuenas5;
                                    $procesos[$i][$j][1] = $pzasMalas5;
                                    $procesos[$i][$j][2] = $pzasTotales5;
                                    break;
                                case 5:
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
                                    //             // Puedes agregar más condiciones aquí según sea necesario
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


                                    $pzasBuenas6[$contador] = 0;
                                    $pzasMalas6[$contador] = 0;
                                    $pzasTotales6[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas6;
                                    $procesos[$i][$j][1] = $pzasMalas6;
                                    $procesos[$i][$j][2] = $pzasTotales6;
                                    break;
                                case 6:
                                    $pzasBuenas7[$contador] = 0;
                                    $pzasMalas7[$contador] = 0;
                                    $pzasTotales7[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas7;
                                    $procesos[$i][$j][1] = $pzasMalas7;
                                    $procesos[$i][$j][2] = $pzasTotales7;
                                    break;
                                case 7:
                                    $pzasBuenas8[$contador] = 0;
                                    $pzasMalas8[$contador] = 0;
                                    $pzasTotales8[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas8;
                                    $procesos[$i][$j][1] = $pzasMalas8;
                                    $procesos[$i][$j][2] = $pzasTotales8;
                                    break;
                                case 8:
                                    $pzasBuenas9[$contador] = 0;
                                    $pzasMalas9[$contador] = 0;
                                    $pzasTotales9[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas9;
                                    $procesos[$i][$j][1] = $pzasMalas9;
                                    $procesos[$i][$j][2] = $pzasTotales9;
                                    break;
                                case 9:
                                    $pzasBuenas10[$contador] = 0;
                                    $pzasMalas10[$contador] = 0;
                                    $pzasTotales10[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas10;
                                    $procesos[$i][$j][1] = $pzasMalas10;
                                    $procesos[$i][$j][2] = $pzasTotales10;
                                    break;
                                case 10:
                                    $pzasBuenas11[$contador] = 0;
                                    $pzasMalas11[$contador] = 0;
                                    $pzasTotales11[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas11;
                                    $procesos[$i][$j][1] = $pzasMalas11;
                                    $procesos[$i][$j][2] = $pzasTotales11;
                                    break;
                                case 11:
                                    $pzasBuenas12[$contador] = 0;
                                    $pzasMalas12[$contador] = 0;
                                    $pzasTotales12[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas12;
                                    $procesos[$i][$j][1] = $pzasMalas12;
                                    $procesos[$i][$j][2] = $pzasTotales12;
                                    break;
                                case 12:
                                    $pzasBuenas13[$contador] = 0;
                                    $pzasMalas13[$contador] = 0;
                                    $pzasTotales13[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas13;
                                    $procesos[$i][$j][1] = $pzasMalas13;
                                    $procesos[$i][$j][2] = $pzasTotales13;
                                    break;
                                case 13:
                                    $pzasBuenas14[$contador] = 0;
                                    $pzasMalas14[$contador] = 0;
                                    $pzasTotales14[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas14;
                                    $procesos[$i][$j][1] = $pzasMalas14;
                                    $procesos[$i][$j][2] = $pzasTotales14;
                                    break;
                                case 14:
                                    $pzasBuenas15[$contador] = 0;
                                    $pzasMalas15[$contador] = 0;
                                    $pzasTotales15[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas15;
                                    $procesos[$i][$j][1] = $pzasMalas15;
                                    $procesos[$i][$j][2] = $pzasTotales15;
                                    break;
                                case 15:
                                    $pzasBuenas16[$contador] = 0;
                                    $pzasMalas16[$contador] = 0;
                                    $pzasTotales16[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas16;
                                    $procesos[$i][$j][1] = $pzasMalas16;
                                    $procesos[$i][$j][2] = $pzasTotales16;
                                    break;
                                case 16:
                                    $pzasBuenas17[$contador] = 0;
                                    $pzasMalas17[$contador] = 0;
                                    $pzasTotales17[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas17;
                                    $procesos[$i][$j][1] = $pzasMalas17;
                                    $procesos[$i][$j][2] = $pzasTotales17;
                                    break;
                                case 17:
                                    $pzasBuenas18[$contador] = 0;
                                    $pzasMalas18[$contador] = 0;
                                    $pzasTotales18[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas18;
                                    $procesos[$i][$j][1] = $pzasMalas18;
                                    $procesos[$i][$j][2] = $pzasTotales18;
                                    break;
                                case 18:
                                    $pzasBuenas19[$contador] = 0;
                                    $pzasMalas19[$contador] = 0;
                                    $pzasTotales19[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas19;
                                    $procesos[$i][$j][1] = $pzasMalas19;
                                    $procesos[$i][$j][2] = $pzasTotales19;
                                    break;
                                case 19:
                                    $pzasBuenas20[$contador] = 0;
                                    $pzasMalas20[$contador] = 0;
                                    $pzasTotales20[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas20;
                                    $procesos[$i][$j][1] = $pzasMalas20;
                                    $procesos[$i][$j][2] = $pzasTotales20;
                                    break;
                                case 20:
                                    $pzasBuenas21[$contador] = 0;
                                    $pzasMalas21[$contador] = 0;
                                    $pzasTotales21[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas21;
                                    $procesos[$i][$j][1] = $pzasMalas21;
                                    $procesos[$i][$j][2] = $pzasTotales21;
                                    break;
                                case 21:
                                    $pzasBuenas22[$contador] = 0;
                                    $pzasMalas22[$contador] = 0;
                                    $pzasTotales22[$contador] = 0;

                                    $procesos[$i][$j][0] = $pzasBuenas22;
                                    $procesos[$i][$j][1] = $pzasMalas22;
                                    $procesos[$i][$j][2] = $pzasTotales22;
                                    break;
                            }
                        }
                        $contador++;
                    }
                }
                return view('processesAdmin.viewPiezas', ['ot' => $ot, 'otArray' => $otArray, 'molduras' => $molduras, 'clases' => $clases, 'pedidos' => $pedidos, 'procesos' => $procesos, 'infoPzMala' => $infoPzMala]);
            }
        }
        return view('processesAdmin.viewPiezas');
        
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
