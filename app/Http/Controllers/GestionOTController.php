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
use App\Models\BarrenoProfundidad;
use App\Models\BarrenoProfundidad_pza;
use App\Models\Cavidades;
use App\Models\Cavidades_pza;
use App\Models\Cepillado;
use App\Models\Clase;
use App\Models\Copiado;
use App\Models\Copiado_pza;
use App\Models\Desbaste_pza;
use App\Models\DesbasteExterior;
use App\Models\EmbudoCM;
use App\Models\EmbudoCM_pza;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\OffSet;
use App\Models\OffSet_pza;
use App\Models\Orden_trabajo;
use App\Models\Palomas;
use App\Models\Palomas_pza;
use App\Models\PrimeraOpeSoldadura;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\Procesos;
use App\Models\PySOpeSoldadura;
use App\Models\PySOpeSoldadura_pza;
use App\Models\Pza_cepillado;
use App\Models\Rebajes;
use App\Models\Rebajes_pza;
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
                        $infoPzMala[$i][$contador] = array();
                        //Piezas buenas, malas y totales de cada proceso
                        $procesosClase = Procesos::where('id_clase', $clase->id)->first();
                        if ($procesosClase) {
                            $clases[$i][$contador] = $clase->nombre;
                            $pedidos[$i][$contador] = $clase->pedido;
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
                                                        if ($pzasMalasC != 2) {
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
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

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
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

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
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

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
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(PrimeraOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get()) / 2;
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
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

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
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

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
                                            $pzasBuenas = count(Soldadura_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(Soldadura_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = Soldadura_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Soldadura"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

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
                                            $pzasBuenas = count(SoldaduraPTA_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(SoldaduraPTA_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = SoldaduraPTA_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Soldadura PTA"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

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
                                            $pzasBuenas = count(Rectificado_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(Rectificado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Rectificado')->where('id_proceso', $proceso->id)->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = Rectificado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Rectificado')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Rectificado"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

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
                                            $pzasBuenas = count(Asentado_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(Asentado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Asentado')->where('id_proceso', $proceso->id)->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = Asentado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Asentado')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Asentado"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

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
                                            $pzasBuenas = count(revCalificado_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(revCalificado_pza::where('estado', 2)->where('error', 'Fundicion')->where('id_proceso', $proceso->id)->orWhere('error', 'Maquinado')->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = revCalificado_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Calificado"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

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
                                            $pzasBuenas = count(AcabadoBombilo_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(AcabadoBombilo_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = AcabadoBombilo_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    //$info[0] = $pzaMala[$p]->n_pieza;
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Acabado Bombillo"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

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
                                            $pzasBuenas = count(AcabadoMolde_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(AcabadoMolde_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = AcabadoMolde_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Acabado Molde"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

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
                                    case 'barreno_profundidad':
                                        $proceso = BarrenoProfundidad::where('id_ot', $otArray[$i])->where('id_proceso', "barrenoProfundidad_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = BarrenoProfundidad_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(BarrenoProfundidad_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(BarrenoProfundidad_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = BarrenoManiobra_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Barreno Profundidad"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(BarrenoProfundidad_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
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
                                            $pzasBuenas = count(Cavidades_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(Cavidades_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = Cavidades_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Cavidades"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

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
                                    case 'copiado':
                                        $proceso = Copiado::where('id_ot', $otArray[$i])->where('id_proceso', "copiado_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = Copiado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(Copiado_pza::where('estado', 2)->where('error_cavidades', 'Ninguno')->where('error_cilindrado', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(Copiado_pza::where('estado', 2)->where('error_cilindrado', 'Fundicion')->orWhere('error_cilindrado', 'Maquinado')->orWhere('error_cavidades', 'Maquinado')->orWhere('error_cilindrado', 'Fundicion')->where('id_proceso', $proceso->id)->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = Copiado_pza::where('estado', 2)->where('error_cilindrado', 'Fundicion')->orWhere('error_cilindrado', 'Maquinado')->orWhere('error_cavidades', 'Maquinado')->orWhere('error_cavidades', 'Fundicion')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Copiado"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(Copiado_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'offSet':
                                        $proceso = OffSet::where('id_ot', $otArray[$i])->where('id_proceso', "offSet_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = OffSet_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(OffSet_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(OffSet_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = OffSet_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Off Set"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(OffSet_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'palomas':
                                        $proceso = Palomas::where('id_ot', $otArray[$i])->where('id_proceso', "palomas_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = Palomas_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(Palomas_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(Palomas_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = Palomas_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Palomas"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(Palomas_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'rebajes':
                                        $proceso = Rebajes::where('id_ot', $otArray[$i])->where('id_proceso', "rebajes_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = Rebajes_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(Rebajes_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(Rebajes_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = Rebajes_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Rebajes"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(Rebajes_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'operacionEquipo':
                                        $juegosP = array();

                                        $proceso1 = PySOpeSoldadura::where('id_ot', $otArray[$i])->where('id_proceso', "1y2opeSoldadura_" . $clase->nombre . "_" . $otArray[$i] . '_1')->first();
                                        $proceso2 = PySOpeSoldadura::where('id_ot', $otArray[$i])->where('id_proceso', "1y2opeSoldadura_" . $clase->nombre . "_" . $otArray[$i] . '_2')->first();

                                        if ($proceso1 && $proceso2) {
                                            //Calcular las piezas totales
                                            $pzasTotales1 = PySOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso1->id)->get();
                                            $pzasTotales2 = PySOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso2->id)->get();

                                            $pzas1T = array();
                                            foreach ($pzasTotales1 as $pzaTotal1) {
                                                if (!in_array($pzaTotal1->n_juego, $juegosP)) {
                                                    array_push($juegosP, $pzaTotal1->n_juego);
                                                    $pzas = (PySOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso1->id)->where('n_juego', $pzaTotal1->n_juego)->get());
                                                    if (count($pzas) == 2) {
                                                        array_push($pzas1T, $pzaTotal1->n_juego);
                                                    }
                                                }
                                            }

                                            $pzas2T = array();
                                            unset($juegosP);
                                            $juegosP = array();
                                            foreach ($pzasTotales2 as $pzaTotal2) {
                                                if (!in_array($pzaTotal2->n_juego, $juegosP)) {
                                                    array_push($juegosP, $pzaTotal2->n_juego);
                                                    $pzas = (PySOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $proceso2->id)->where('n_juego', $pzaTotal2->n_juego)->get());
                                                    if (count($pzas) == 2) {
                                                        array_push($pzas2T, $pzaTotal2->n_juego);
                                                    }
                                                }
                                            }

                                            //Obtener piezas Totales en las dos operaciones
                                            $pzasTotales = 0;
                                            foreach ($pzas2T as $pza2T) {
                                                if (in_array($pza2T, $pzas1T)) {
                                                    $pzasTotales++;
                                                } else {
                                                    $pzasTotales += .5;
                                                }
                                            }
                                            //Piezas buenas en la primera operacion
                                            $juegosBuenos1 = array();
                                            if ($proceso1) { //Si existe el proceso
                                                unset($juegosP);
                                                $juegosP = array();
                                                $pzasBuenas1 = PySOpeSoldadura_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso1->id)->get();
                                                foreach ($pzasBuenas1 as $pzaBuena1) {
                                                    if (!in_array($pzaBuena1->n_juego, $juegosP)) {
                                                        array_push($juegosP, $pzaBuena1->n_juego);
                                                        $pzas = PySOpeSoldadura_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso1->id)->where('n_juego', $pzaBuena1->n_juego)->get();
                                                        if (count($pzas) == 2) {
                                                            array_push($juegosBuenos1, $pzaBuena1->n_juego);
                                                        }
                                                    }
                                                }

                                                //Piezas buenas en la segunda operacion
                                                $pzasBuenas2 = PySOpeSoldadura_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso2->id)->get();
                                                unset($juegosP);
                                                $juegosP = array();
                                                $juegosBuenos2 = array();

                                                foreach ($pzasBuenas2 as $pzaBuena2) {
                                                    if (!in_array($pzaBuena2->n_juego, $juegosP)) {
                                                        array_push($juegosP, $pzaBuena2->n_juego);
                                                        $pzas = PySOpeSoldadura_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso2->id)->where('n_juego', $pzaBuena2->n_juego)->get();
                                                        if (count($pzas) == 2) {
                                                            array_push($juegosBuenos2, $pzaBuena2->n_juego);
                                                        }
                                                    }
                                                }
                                                $pzasBuenas = 0;
                                                //Obtener piezas buenas en las dos operaciones
                                                foreach ($juegosBuenos2 as $juegoBueno2) {
                                                    if (in_array($juegoBueno2, $juegosBuenos1)) {
                                                        $pzasBuenas++;
                                                    }
                                                }


                                                //Piezas malas en la primera operacion
                                                $juegosMalos1 = array();
                                                $pzasMalas1 = PySOpeSoldadura_pza::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso1->id)->get();
                                                foreach ($pzasMalas1 as $pzaMala1) {
                                                    if (!in_array($pzaMala1->n_juego, $juegosMalos1)) {
                                                        array_push($juegosMalos1, $pzaMala1->n_juego);
                                                    }
                                                }

                                                //Piezas malas en la segunda operacion
                                                $pzasMalas2 = PySOpeSoldadura_pza::where('estado', 2)->where('correcto', 0)->where('id_proceso', $proceso2->id)->get();
                                                $juegosMalos2 = array();

                                                foreach ($pzasMalas2 as $pzaMala2) {
                                                    if (!in_array($pzaMala2->n_juego, $juegosMalos2)) {
                                                        array_push($juegosMalos2, $pzaMala2->n_juego);
                                                    }
                                                }
                                                $pzasMalas = 0;

                                                $juegosMalos = array();
                                                //Obtener piezas malas en las dos operaciones
                                                if (count($juegosMalos2) > count($juegosMalos1)) {
                                                    foreach ($juegosMalos2 as $juegoMalo2) {
                                                        if (!in_array($juegoMalo2, $juegosMalos1)) {
                                                            $pzasMalas++;
                                                            array_push($juegosMalos, $juegoMalo2);
                                                        }
                                                    }
                                                } else {
                                                    foreach ($juegosMalos1 as $juegoMalo1) {
                                                        if (!in_array($juegoMalo1, $juegosMalos2)) {
                                                            $pzasMalas++;
                                                            array_push($juegosMalos, $juegoMalo1);
                                                        }
                                                    }
                                                }

                                                foreach ($juegosMalos2 as $juegoMalo2) {
                                                    if (!in_array($juegoMalo2, $juegosMalos)) {
                                                        $pzasMalas++;
                                                    }
                                                }
                                                foreach ($juegosMalos1 as $juegoMalo1) {
                                                    if (!in_array($juegoMalo1, $juegosMalos)) {
                                                        $pzasMalas++;
                                                    }
                                                }

                                                if (count($pzasMalas1) > 0 || count($pzasMalas2) > 0) {
                                                    if (count($pzasMalas1) > 0) {
                                                        $this->savePzasMalas($pzasMalas1, $contador, $i, $infoPzMala);
                                                    }
                                                    if (count($pzasMalas2) > 0) {
                                                        $this->savePzasMalas($pzasMalas2, $contador, $i, $infoPzMala);
                                                    }
                                                }
                                            } else {
                                                $pzasBuenas = 0;
                                                $pzasMalas = 0;
                                                $pzasTotales = 0;
                                            }
                                        }else{
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    case 'embudoCM':
                                        $proceso = EmbudoCM::where('id_ot', $otArray[$i])->where('id_proceso', "embudoCM_" . $clase->nombre . "_" . $otArray[$i])->first();
                                        if (isset($proceso)) { //Si existe el proceso
                                            $pzas = EmbudoCM_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get();
                                            $pzasBuenas = count(EmbudoCM_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $proceso->id)->get());
                                            $pzasMalas = count(EmbudoCM_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get());

                                            if ($pzasMalas > 0) {
                                                $pzaMala = EmbudoCM_pza::where('estado', 2)->where('error', 'Fundicion')->orWhere('error', 'Maquinado')->where('id_proceso', $proceso->id)->get();
                                                $info = array();
                                                for ($p = 0; $p < count($pzaMala); $p++) {
                                                    $info[0] = '- - -';
                                                    $info[1] = $pzaMala[$p]->n_juego;
                                                    $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
                                                    $operador = User::where('matricula', $meta->id_usuario)->first();
                                                    $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
                                                    $info[3] = "Embudo"; //Nombre del proceso que se está realizando
                                                    $info[4] = "- - - ";
                                                    $info[5] = $pzaMala[$p]->error; //Error de la pieza

                                                    array_push($infoPzMala[$i][$contador], $info);
                                                }
                                            }
                                            $pzasTotales = count(EmbudoCM_pza::where('estado', 2)->where('id_proceso', $proceso->id)->get());
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
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
    public function savePzasMalas($pzaMala, $contador, $i, &$infoPzMala)
    {
        $info = array();
        for ($p = 0; $p < count($pzaMala); $p++) {
            $info[0] = '- - -';
            $info[1] = $pzaMala[$p]->n_juego;
            $meta = Metas::where('id', $pzaMala[$p]->id_meta)->first();
            $operador = User::where('matricula', $meta->id_usuario)->first();
            $info[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
            $info[3] = "1 y 2 Operacion equipo"; //Nombre del proceso que se está realizando
            //Obtener la operacion en la que se encuentra la pieza
            $procesoId = PySOpeSoldadura::find($pzaMala[$p]->id_proceso);
            $info[4] = $procesoId->operacion;
            $info[5] = $pzaMala[$p]->error; //Error de la pieza

            array_push($infoPzMala[$i][$contador], $info);
        }
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
