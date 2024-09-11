<?php

namespace App\Http\Controllers;

use App\Models\AcabadoBombilo;
use App\Models\AcabadoBombilo_cnominal;
use App\Models\AcabadoBombilo_pza;
use App\Models\AcabadoBombilo_tolerancia;
use App\Models\AcabadoMolde;
use App\Models\AcabadoMolde_cnominal;
use App\Models\AcabadoMolde_pza;
use App\Models\AcabadoMolde_tolerancia;
use App\Models\Asentado;
use App\Models\Asentado_pza;
use App\Models\BarrenoManiobra;
use App\Models\BarrenoManiobra_cnominal;
use App\Models\BarrenoManiobra_pza;
use App\Models\BarrenoManiobra_tolerancia;
use App\Models\BarrenoProfundidad;
use App\Models\BarrenoProfundidad_cnominal;
use App\Models\BarrenoProfundidad_pza;
use App\Models\BarrenoProfundidad_tolerancia;
use App\Models\Cavidades;
use App\Models\Cavidades_cnominal;
use App\Models\Cavidades_pza;
use App\Models\Cavidades_tolerancia;
use App\Models\Cepillado;
use App\Models\Cepillado_cnominal;
use App\Models\Cepillado_tolerancia;
use App\Models\Clase;
use App\Models\Copiado;
use App\Models\Copiado_cnominal;
use App\Models\Copiado_pza;
use App\Models\Copiado_tolerancia;
use App\Models\Desbaste_cnominal;
use App\Models\Desbaste_pza;
use App\Models\Desbaste_tolerancia;
use App\Models\DesbasteExterior;
use App\Models\EmbudoCM;
use App\Models\EmbudoCM_cnominal;
use App\Models\EmbudoCM_pza;
use App\Models\EmbudoCM_tolerancias;
use App\Models\Metas;
use App\Models\OffSet;
use App\Models\OffSet_cnominal;
use App\Models\OffSet_pza;
use App\Models\OffSet_tolerancia;
use App\Models\Orden_trabajo;
use App\Models\Palomas;
use App\Models\Palomas_cnominal;
use App\Models\Palomas_pza;
use App\Models\Palomas_tolerancia;
use App\Models\Pieza;
use App\Models\PrimeraOpeSoldadura;
use App\Models\PrimeraOpeSoldadura_cnominal;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\PrimeraOpeSoldadura_tolerancia;
use App\Models\Procesos;
use App\Models\PySOpeSoldadura;
use App\Models\PySOpeSoldadura_cnominal;
use App\Models\PySOpeSoldadura_pza;
use App\Models\PySOpeSoldadura_tolerancia;
use App\Models\Pza_cepillado;
use App\Models\Rebajes;
use App\Models\Rebajes_cnominal;
use App\Models\Rebajes_pza;
use App\Models\Rebajes_tolerancia;
use App\Models\Rectificado;
use App\Models\Rectificado_pza;
use App\Models\revCalificado;
use App\Models\revCalificado_cnominal;
use App\Models\revCalificado_pza;
use App\Models\revCalificado_tolerancia;
use App\Models\RevLaterales;
use App\Models\RevLaterales_cnominal;
use App\Models\RevLaterales_pza;
use App\Models\RevLaterales_tolerancia;
use App\Models\SegundaOpeSoldadura;
use App\Models\SegundaOpeSoldadura_cnominal;
use App\Models\SegundaOpeSoldadura_pza;
use App\Models\SegundaOpeSoldadura_tolerancia;
use App\Models\Soldadura;
use App\Models\Soldadura_pza;
use App\Models\SoldaduraPTA;
use App\Models\SoldaduraPTA_pza;
use App\Models\User;
use ArchTech\Enums\Exceptions\UndefinedCaseError;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Exists;

use function Symfony\Component\String\b;
//Clase para el control de las piezas generales
class PzasGeneralesController extends Controller
{
    public function showVistaPiezas()
    {
        if ($this->retornarOTs() != 0) {
            $arregloOT = $this->retornarOTs();
            return view('processesAdmin.ReportePiezas.OT', compact('arregloOT'));
        } else {
            return view('processesAdmin.ReportePiezas.OT');
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
        return $this->search($datosPiezas, 'admin');
    }
    public function search($datosPiezas, $perfil = null)
    {
        $array = array();
        $infoPiezas = array();
        $otElegida = Orden_trabajo::find($datosPiezas["ot"]); 
        $clase = Clase::find($datosPiezas["clase"]);
        $operadores = $this->getOperadores($otElegida->id);
        $maquina = Pieza::where('id_ot', $otElegida->id)->distinct('maquina')->pluck('maquina');
        $proceso = $this->procesosClase($clase);
        $piezas = $this->buscarPiezas($otElegida, $clase, $datosPiezas["operador"], $datosPiezas["maquina"], $datosPiezas["proceso"], $datosPiezas["error"], $datosPiezas["fecha"], $array);
        $error = ['Ninguno', 'Maquinado', 'Fundicion'];
        $this->saveInfoPzas($infoPiezas, $piezas, $clase->nombre);

        if ($datosPiezas["action"] != 'pdf' || $datosPiezas["action"] == null) {
            if ($perfil == 'quality') {
                return [true, $piezas, $otElegida, $clase, $operadores, $maquina, $array, $proceso, $error, $infoPiezas];
            }
            return view('processesAdmin.ReportePiezas.AdminPzas', compact('piezas', 'otElegida', 'clase', 'operadores', 'maquina', 'array', 'proceso', 'error', 'infoPiezas'));
        } else {
            if ($perfil == 'quality') {
                return [false, $piezas, $otElegida, $clase, $operadores, $maquina, $array, $proceso, $error];
            }
            $pdf = Pdf::loadView('processesAdmin.ReportePiezas.pdf', compact('piezas', 'otElegida', 'clase', 'operadores', 'maquina', 'array', 'proceso', 'error', 'perfil'));
            return $pdf->download('Reporte de piezas.pdf');
        }
    }
    public function buscarPiezas($ot, $clase, $operador, $maquina, $proceso, $error, $fecha, &$itemElegidos)
    {
        //Busca las piezas que coincidan con los parametros de búsqueda
        $array = array();
        if ($ot != null) {
            $array = Pieza::where('id_ot', $ot->id)->where('id_clase', $clase->id)->get();
            $array = $this->saveInArray($array);
            if (($operador != "todos" && isset($operador)) && $array != "[]") {
                $array = $this->buscarElemento($array, 2, $operador);
                $itemElegidos[0] = $operador;
            } else {
                $itemElegidos[0] = "Todos";
            }
            if (($maquina != "todos" && isset($maquina)) && $array != "[]") {
                $array = $this->buscarElemento($array, 3, $maquina);
                $itemElegidos[1] = $maquina;
            } else {
                $itemElegidos[1] = "Todos";
            }
            if (($proceso != "todos" && isset($proceso)) && $array != "[]") {
                $array = $this->buscarElemento($array, 4, $proceso);
                $itemElegidos[2] = $proceso;
            } else {
                $itemElegidos[2] = "Todos";
            }
            if (($error != "todos" && isset($error)) && $array != "[]") {
                $array = $this->buscarElemento($array, 5, $error);
                $itemElegidos[3] = $error;
            } else {
                $itemElegidos[3] = "Todos";
            }
            if (($fecha != "todos" && isset($fecha)) && $array != "[]") {
                $array = $this->buscarElemento($array, 6, $fecha);
                $itemElegidos[4] = $fecha;
            } else {
                $itemElegidos[4] = "Todos";
            }
        }
        return $array;
    }
    //Obtener los procesos por los que pasa una clase
    public function procesosClase($clase)
    {
        $procesos = array();
        $procesosClase = Procesos::where('id_clase', $clase->id)->first();
        $procesosClase = $procesosClase->toArray();
        $campos = array_filter($procesosClase, function ($valor) {
            return $valor != 0;
        });
        $procesos = array();
        foreach (array_keys($campos) as $nombreCampo) {
            array_push($procesos, $this->nombreProceso($nombreCampo));
        }
        array_splice($procesos, 0, 2);
        return $procesos;
    }
    public function buscarElemento($arrayP, $posicion, $elemento)
    {
        //Busca un elemento en un arreglo de arreglos y regresa un arreglo con los arreglos que contienen el elemento
        $array = array();
        for ($i = 0; $i < count($arrayP); $i++) {
            $elementoArray = $arrayP[$i][$posicion];
            if (is_numeric($arrayP[$i][$posicion]) && $posicion == 5) {
                $elementoArray = $arrayP[$i][$posicion + 1];
            }
            if (strpos($elementoArray, $elemento) !== false) {
                if ($elemento == "Soldadura") {
                    if ($arrayP[$i][$posicion] === $elemento) {
                        array_push($array, $arrayP[$i]);
                    }
                } else {
                    array_push($array, $arrayP[$i]);
                }
            }
        }
        return $array;
    }
    public function saveInArray($arrayP)
    {
        $array = array();
        $juegosGuardados = array();
        $contador = 0;
        $mitad = false;
        foreach ($arrayP as $item) {
            $band = false;
            //Identificar si la pieza es mitad o juego
            if (substr($item->n_pieza, -1) != "J") { //Si la pieza es mitad
                $mitad = true;
                //Si la pieza es mitad, buscar si ya se guardo el juego
                $numJuego = $this->getPiezaNumber($item->n_pieza);
                if (!in_array($numJuego . "J" . "_" . $item->proceso, $juegosGuardados)) {
                    $band = true;
                    //Guardar el numero de juego
                    $array[$contador][1] = $numJuego . "J";
                    array_push($juegosGuardados, $array[$contador][1] . "_" . $item->proceso);

                    //Buscar las mitades del juego para los demás datos
                    $pzas[0] = Pieza::where('id_clase', $item->id_clase)->where('proceso', $item->proceso)->where('n_pieza', $numJuego . 'H')->first();
                    $pzas[1] = Pieza::where('id_clase', $item->id_clase)->where('proceso', $item->proceso)->where('n_pieza', $numJuego . 'M')->first();

                    if (!$pzas[0] || !$pzas[1]) { //Si no existe la mitad M
                        if (!$pzas[0]) {
                            //Guardar operador
                            $array[$contador][2] = $this->getNameOperador($pzas[1]->id_operador);

                            //Identificar error
                            $error = "";
                            if ($pzas[1]->error != "Ninguno") {
                                $error = $pzas[1]->error . " / Incompleto";
                            } else {
                                $error = "Incompleto";
                            }
                        } else {
                            //Guardar operador
                            $array[$contador][2] = $this->getNameOperador($pzas[0]->id_operador);

                            //Identificar error
                            $error = "";
                            if ($pzas[0]->error != "Ninguno") {
                                $error = $pzas[0]->error . " / Incompleto";
                            } else {
                                $error = "Incompleto";
                            }
                        }

                        //Guardar el error
                        if ($item->proceso == "Operacion Equipo_1" || $item->proceso == "Operacion Equipo_2") {
                            $array[$contador][6] = $error;
                        } else {
                            $array[$contador][5] = $error;
                        }
                    } else {
                        //Guardar operadores u operador
                        if ($pzas[0]->id_operador == $pzas[1]->id_operador) {
                            $array[$contador][2] = $this->getNameOperador($pzas[0]->id_operador);
                        } else {
                            $array[$contador][2] = $this->getNameOperador($pzas[0]->id_operador) . " / " . $this->getNameOperador($pzas[1]->id_operador);
                        }

                        //Guardar el error o errores
                        if ($pzas[0]->error == $pzas[1]->error) {
                            if ($item->proceso == "Operacion Equipo_1" || $item->proceso == "Operacion Equipo_2") {
                                $array[$contador][6] = $pzas[0]->error;
                            } else {
                                $array[$contador][5] = $pzas[0]->error;
                            }
                        } else {
                            if ($item->proceso == "Operacion Equipo_1" || $item->proceso == "Operacion Equipo_2") {
                                $array[$contador][6] = $pzas[0]->error . " / " . $pzas[1]->error;
                            } else {
                                $array[$contador][5] = $pzas[0]->error . " / " . $pzas[1]->error;
                            }
                        }
                    }
                }
            } else { //Si la pieza es juego
                $band = true;
                $mitad = false;
                $array[$contador][1] = $item->n_pieza;
                $array[$contador][2] = $this->getNameOperador($item->id_operador);
                if ($item->proceso == "Operacion Equipo_1" || $item->proceso == "Operacion Equipo_2") {
                    $array[$contador][6] = $item->error;
                } else {
                    $array[$contador][5] = $item->error;
                }
            }

            //Almacenar los demas datos
            $user_liberacion = User::where('matricula', $item->user_liberacion)->first();
            if ($band) {
                $array[$contador][0] = $item->id_ot;
                $array[$contador][3] = $item->maquina;
                if ($item->proceso == "Operacion Equipo_1" || $item->proceso == "Operacion Equipo_2") {
                    $array[$contador][4] = substr($item->proceso, 0, -2);
                    $array[$contador][5] = substr($item->proceso, -1);
                    $array[$contador][7] = $item->created_at;
                    if ($item->fecha_liberacion != null) {
                        $array[$contador][8] = $item->fecha_liberacion;
                    } else {
                        $array[$contador][8] = "No liberado";
                    }
                    if ($user_liberacion) {
                        $array[$contador][9] = $user_liberacion->nombre . " " . $user_liberacion->a_paterno . " " . $user_liberacion->a_materno;
                    } else {
                        $array[$contador][9] = null;
                    }
                } else {
                    $array[$contador][4] = $item->proceso;
                    $array[$contador][6] = $item->created_at;
                    if ($item->fecha_liberacion != null) {
                        $array[$contador][7] = $item->fecha_liberacion;
                    } else {
                        $array[$contador][7] = "No liberado";
                    }
                    if ($user_liberacion) {
                        $array[$contador][8] = $user_liberacion->nombre . " " . $user_liberacion->a_paterno . " " . $user_liberacion->a_materno;
                    } else {
                        $array[$contador][8] = null;
                    }
                }
                //Almacenar valor de liberacion
                array_push($array[$contador], $item->liberacion);
                //Almacenar valor si la pieza es juego o no
                if ($mitad == true) {
                    array_push($array[$contador], "mitad");
                } else {
                    array_push($array[$contador], "juego");
                }

                $contador++;
            }
        }
        return $array;
    }
    public function saveInfoPzas(&$infoPiezas, $piezas, $clase)
    {
        $contador = 0;
        foreach ($piezas as $pieza) {
            switch ($pieza[4]) {
                case 'Cepillado':
                    $id_proceso = 'Cepillado' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = Cepillado::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Cepillado';
                    break;
                case 'Desbaste Exterior':
                    $id_proceso = 'desbaste' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = DesbasteExterior::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Desbaste Exterior';
                    break;
                case 'Revision Laterales':
                    $id_proceso = 'revLaterales' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = RevLaterales::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Revision Laterales';
                    break;
                case 'Primera Operacion Soldadura':
                    $id_proceso = '1opeSoldadura' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = PrimeraOpeSoldadura::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Primera Operacion Soldadura';
                    break;
                case 'Barreno Maniobra':
                    $id_proceso = 'barrenoManiobra' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = BarrenoManiobra::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Barreno Maniobra';
                    break;
                case 'Segunda Operacion Soldadura':
                    $id_proceso = '2opeSoldadura' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = SegundaOpeSoldadura::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Segunda Operacion Soldadura';
                    break;
                case 'Soldadura':
                    $id_proceso = 'soldadura' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = Soldadura::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Soldadura';
                    break;
                case 'Soldadura PTA':
                    $id_proceso = 'soldaduraPTA' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = SoldaduraPTA::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Soldadura PTA';
                    break;
                case 'Rectificado':
                    $id_proceso = 'rectificado' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = Rectificado::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Rectificado';
                    break;
                case 'Asentado':
                    $id_proceso = 'asentado' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = Asentado::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Asentado';
                    break;
                case 'Revision Calificado':
                    $id_proceso = 'revCalificado' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = revCalificado::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Revision Calificado';
                    break;
                case 'Acabado Bombillo':
                    $id_proceso = 'acabadoBombillo' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = AcabadoBombilo::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Acabado Bombillo';
                    break;
                case 'Acabado Molde':
                    $id_proceso = 'acabadoMolde' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = AcabadoMolde::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Acabado Molde';
                    break;
                case 'Barreno Profundidad':
                    $id_proceso = 'barrenoProfundidad' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = BarrenoProfundidad::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Barreno Profundidad';
                    break;
                case 'Cavidades':
                    $id_proceso = 'cavidades' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = Cavidades::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Cavidades';
                    break;
                case 'Copiado':
                    $id_proceso = 'copiado' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = Copiado::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Copiado';
                    break;
                case 'Off Set':
                    $id_proceso = 'offSet' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = OffSet::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Off Set';
                    break;
                case 'Palomas':
                    $id_proceso = 'palomas' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = Palomas::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Palomas';
                    break;
                case 'Rebajes':
                    $id_proceso = 'rebajes' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = Rebajes::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Rebajes';
                    break;
                case 'Operacion Equipo':
                    $id_proceso = '1y2OpeSoldadura' . "_" . $clase . "_" . $pieza[0] . "_" . $pieza[5];
                    $id_proceso = PySOpeSoldadura::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Operacion Equipo_' . $pieza[5];
                    break;
                case 'Embudo CM':
                    $id_proceso = 'embudoCM' . "_" . $clase . "_" . $pieza[0];
                    $id_proceso = EmbudoCM::where('id_proceso', $id_proceso)->first();
                    $infoPiezas[$contador][1] = 'Embudo CM';
                    break;
            }
            if (end($pieza) == "mitad") {
                //Guardar el numero de pieza
                $numero = $this->getPiezaNumber($pieza[1]);
                $infoPiezas[$contador][0][0] = $numero . "H" . $id_proceso->id;
                $infoPiezas[$contador][0][1] = $numero . "M" . $id_proceso->id;
            } else {
                $infoPiezas[$contador][0][0] = $pieza[1] . $id_proceso->id;
            }

            //Guardar el error
            if (count($pieza) > 11) {
                $infoPiezas[$contador][2] = $pieza[6];
            } else {
                $infoPiezas[$contador][2] = $pieza[5];
            }
            $contador++;
        }
    }

    public function showPieza($pieza, $proceso, $perfil)
    {
        switch ($proceso) {
            case 'Cepillado':
                $piezaInfo = array();
                $pieza = explode(",", $pieza);
                foreach ($pieza as $pza) {
                    array_push($piezaInfo, Pza_cepillado::where('id_pza', $pza)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_proceso = Cepillado::find($piezaInfo[0]->id_proceso);
                $cNominal = Cepillado_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = Cepillado_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Cepillado';
                break;
            case 'Desbaste Exterior':
                //Obtener informacion de la pieza elegida
                $piezaInfo = array();
                $pieza = explode(",", $pieza);
                foreach ($pieza as $pza) {
                    array_push($piezaInfo, Desbaste_pza::where('id_pza', $pza)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_proceso = DesbasteExterior::find($piezaInfo[0]->id_proceso);
                $cNominal = Desbaste_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = Desbaste_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Desbaste Exterior';
                break;
            case 'Revision Laterales':
                //Obtener informacion de la pieza elegida
                $piezaInfo = array();
                $pieza = explode(",", $pieza);
                foreach ($pieza as $pza) {
                    array_push($piezaInfo, RevLaterales_pza::where('id_pza', $pza)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_proceso = RevLaterales::find($piezaInfo[0]->id_proceso);
                $cNominal = RevLaterales_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = RevLaterales_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Revision Laterales';
                break;
            case 'Primera Operacion Soldadura':
                //Obtener informacion de la pieza elegida
                $piezaInfo = array();
                $pieza = explode(",", $pieza);
                foreach ($pieza as $pza) {
                    array_push($piezaInfo, PrimeraOpeSoldadura_pza::where('id_pza', $pza)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_proceso = PrimeraOpeSoldadura::find($piezaInfo[0]->id_proceso);
                $cNominal = PrimeraOpeSoldadura_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = PrimeraOpeSoldadura_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Primera Operacion';
                break;
            case 'Barreno Maniobra':
                //Obtener informacion de la pieza elegida
                $piezaInfo = array();
                $pieza = explode(",", $pieza);
                foreach ($pieza as $pza) {
                    array_push($piezaInfo, BarrenoManiobra_pza::where('id_pza', $pza)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_proceso = BarrenoManiobra::find($piezaInfo[0]->id_proceso);
                $cNominal = BarrenoManiobra_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = BarrenoManiobra_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Barreno Maniobra';
                break;
            case 'Segunda Operacion Soldadura':
                //Obtener informacion de la pieza elegida
                $piezaInfo = array();
                $pieza = explode(",", $pieza);
                foreach ($pieza as $pza) {
                    array_push($piezaInfo, SegundaOpeSoldadura_pza::where('id_pza', $pza)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_proceso = SegundaOpeSoldadura::find($piezaInfo[0]->id_proceso);
                $cNominal = SegundaOpeSoldadura_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = SegundaOpeSoldadura_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Segunda Operacion';
                break;
            case 'Soldadura':
                //Obtener informacion de la pieza elegida
                $piezaInfo = Soldadura_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = Soldadura::find($piezaInfo->id_proceso);
                $cNominal = 0;
                $tolerancia = 0;
                $proceso = 'Soldadura';
                break;
            case 'Soldadura PTA':
                //Obtener informacion de la pieza elegida
                $piezaInfo = SoldaduraPTA_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = SoldaduraPTA::find($piezaInfo->id_proceso);
                $cNominal = 0;
                $tolerancia = 0;
                $proceso = 'Soldadura PTA';
                break;
            case 'Rectificado':
                //Obtener informacion de la pieza elegida
                $piezaInfo = Rectificado_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = Rectificado::find($piezaInfo->id_proceso);
                $cNominal = 0;
                $tolerancia = 0;
                $proceso = 'Rectificado';
                break;
            case 'Asentado':
                //Obtener informacion de la pieza elegida
                $piezaInfo = Asentado_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = Asentado::find($piezaInfo->id_proceso);
                $cNominal = 0;
                $tolerancia = 0;
                $proceso = 'Asentado';
                break;

            case 'Calificado':
                //Obtener informacion de la pieza elegida
                $piezaInfo = revCalificado_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = revCalificado::find($piezaInfo->id_proceso);
                $cNominal = revCalificado_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = revCalificado_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Calificado';
                break;
            case 'Acabado Bombillo':
                //Obtener informacion de la pieza elegida
                $piezaInfo = AcabadoBombilo_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = AcabadoBombilo::find($piezaInfo->id_proceso);
                $cNominal = AcabadoBombilo_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = AcabadoBombilo_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Acabado Bombillo';
                break;
            case 'Acabado Molde':
                //Obtener informacion de la pieza elegida
                $piezaInfo = AcabadoMolde_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = AcabadoMolde::find($piezaInfo->id_proceso);
                $cNominal = AcabadoMolde_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = AcabadoMolde_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Acabado Molde';
                break;
            case 'Barreno Profundidad':
                //Obtener informacion de la pieza elegida
                $piezaInfo = BarrenoProfundidad_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = BarrenoProfundidad::find($piezaInfo->id_proceso);
                $cNominal = BarrenoProfundidad_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = BarrenoProfundidad_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Barreno Profundidad';
                break;
            case 'Cavidades':
                //Obtener informacion de la pieza elegida
                $piezaInfo = Cavidades_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = Cavidades::find($piezaInfo->id_proceso);
                $cNominal = Cavidades_cnominal::where('id_proceso', $id_proceso->id_proceso)->first();
                $tolerancia = Cavidades_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first();
                $proceso = 'Cavidades';
                break;
            case 'Copiado':
                //Obtener informacion de la pieza elegida
                $piezaInfo = Copiado_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = Copiado::find($piezaInfo->id_proceso);
                $cNominal = Copiado_cnominal::where('id_proceso', $id_proceso->id_proceso)->first();
                $tolerancia = Copiado_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first();
                $proceso = 'Copiado';
                break;
            case 'Off Set':
                //Obtener informacion de la pieza elegida
                $piezaInfo = OffSet_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = OffSet::find($piezaInfo->id_proceso);
                $cNominal = OffSet_cnominal::where('id_proceso', $id_proceso->id_proceso)->first();
                $tolerancia = OffSet_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first();
                $proceso = 'Off Set';
                break;
            case 'Palomas':
                //Obtener informacion de la pieza elegida
                $piezaInfo = Palomas_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = Palomas::find($piezaInfo->id_proceso);
                $cNominal = Palomas_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = Palomas_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Palomas';
                break;
            case 'Rebajes':
                //Obtener informacion de la pieza elegida
                $piezaInfo = Rebajes_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = Rebajes::find($piezaInfo->id_proceso);
                $cNominal = Rebajes_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = Rebajes_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Rebajes';
                break;
            case 'Operacion Equipo_1':
                //Obtener informacion del juego elegido
                $piezaInfo = array();
                $pieza = explode(",", $pieza);
                foreach ($pieza as $pza) {
                    array_push($piezaInfo, PySOpeSoldadura_pza::where('id_pza', $pza)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_proceso = PySOpeSoldadura::find($piezaInfo[0]->id_proceso);
                $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = PySOpeSoldadura_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Operacion Equipo';
                break;
            case 'Operacion Equipo_2':
                //Obtener informacion del juego elegido
                $piezaInfo = array();
                $pieza = explode(",", $pieza);
                foreach ($pieza as $pza) {
                    array_push($piezaInfo, PySOpeSoldadura_pza::where('id_pza', $pza)->first());
                }
                //Obtener Cotas nominales y tolerancias
                $id_proceso = PySOpeSoldadura::find($piezaInfo[0]->id_proceso);
                $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = PySOpeSoldadura_tolerancia::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Operacion Equipo';
                break;
            case 'Embudo CM':
                //Obtener informacion de la pieza elegida
                $piezaInfo = EmbudoCM_pza::where('id_pza', $pieza)->first();
                //Obtener Cotas nominales y tolerancias
                $id_proceso = EmbudoCM::find($piezaInfo->id_proceso);
                $cNominal = EmbudoCM_cnominal::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $tolerancia = EmbudoCM_tolerancias::where('id_proceso', $id_proceso->id_proceso)->first()->toArray();
                $proceso = 'Embudo CM';
                break;
        }
        // Obtener meta para obtener la ot y la clase
        if (is_array($piezaInfo)) { //Si el juego es mitad
            $meta = Metas::find($piezaInfo[0]->id_meta);
        } else { //Si no es mitad
            $meta = Metas::find($piezaInfo->id_meta);
        }
        $ot = $meta->id_ot;
        $clase = Clase::find($meta->id_clase);
        if ($clase->nombre == "Obturador") {
            $clase = $clase->nombre . " - seccion: " . $clase->seccion;
        } else {
            $clase = $clase->nombre . " " . $clase->tamanio;
        }
        if ($proceso != 'Asentado' && $proceso != 'Cavidades' && $proceso != 'Copiado' && $proceso != 'Off Set') {
            $piezasInfo = array();
            //Si el juego es mitad
            if (is_array($piezaInfo)) {
                $contador = 0;
                foreach ($piezaInfo as $pza) {
                    $piezasInfo[$contador] = $pza->toArray();
                    $contador++;
                }
            } else { //Si no es mitad
                $piezasInfo[0] = $piezaInfo->toArray();
            }
        } else {
            $piezasInfo = $piezaInfo;
        }
        $perfil = $perfil;

        //Obtener el nombre del operador
        $operadores = array();
        if (is_array($piezasInfo)) {
            $contador = 0;
            foreach ($piezasInfo as $pza) {
                //Obtener la meta para obtener el id del operador
                $meta = Metas::find($pza['id_meta']);

                //Obtener el nombre del operador
                if (!in_array($this->getNameOperador($meta->id_usuario), $operadores)) {
                    //Guardar el nombre del operador
                    $operadores[$contador] = array();
                    if (!array_key_exists("n_pieza", $pza)) {
                        array_push($operadores[$contador], $pza["n_juego"]);
                    } else {
                        array_push($operadores[$contador], $pza["n_pieza"]);
                    }
                    array_push($operadores[$contador], $this->getNameOperador($meta->id_usuario));
                    $contador++;
                }
            }
        } else {
            $meta = Metas::find($piezaInfo->id_meta);
            $operadores[0] = array();
            array_push($operadores[0], $piezaInfo->n_juego);
            array_push($operadores[0], $this->getNameOperador($meta->id_usuario));
        }
        return view('processesAdmin.ReportePiezas.piezaElegida', compact('proceso', 'piezasInfo', 'cNominal', 'tolerancia', 'ot', 'clase', 'perfil', 'operadores'));
    }

    public function getOperadores($ot)
    {
        $operadores = Pieza::where('id_ot', $ot)->distinct('id_operador')->pluck('id_operador');
        for ($i = 0; $i < count($operadores); $i++) {
            $operadores[$i] = User::where('matricula', $operadores[$i])->first();
        }
        return $operadores;
    }
    public function getNameOperador($matricula)
    {
        $operador = User::where('matricula', $matricula)->first();
        return $operador->nombre . " " . $operador->a_paterno . " " . $operador->a_materno;
    }
    public function getNameClase($id)
    {
        $clase = Clase::find($id);
        return $clase->nombre . " " . $clase->tamanio;
    }
    public function getPiezaNumber($pieza)
    {
        switch (strlen($pieza)) {
            case 2:
                return substr($pieza, 0, 1);
            case 3:
                return substr($pieza, 0, 2);
            case 4:
                return substr($pieza, 0, 3);
        }
    }

    //Funciones para el control de la vista de piezas por maquina

    public function retornarOTyClases($ot, $indiceOT, &$arrayOT, $clases)
    {
        //Insertar la ot en el arreglo
        $arrayOT[$indiceOT] = array();
        $arrayOT[$indiceOT][0] = $ot->id;

        $indiceClass = 0;
        foreach ($clases as $clase) {
            //Insertar la clase en el arreglo
            $arrayOT[$indiceOT][1][$indiceClass] = array();
            $arrayOT[$indiceOT][1][$indiceClass][0] = $clase->id;
            $arrayOT[$indiceOT][1][$indiceClass][1] = $clase->nombre . " " . $clase->tamanio;
            $indiceClass++;
        }
    }
    public function retornarOTs()
    {
        $ot = Orden_trabajo::all();
        $indiceOT = 0;
        $arrayOT = array();
        $band = false;
        if (count($ot) > 0) {
            foreach ($ot as $ot) {
                $clases = Clase::where('id_ot', $ot->id)->get();
                if (count($clases) > 0) {
                    $this->retornarOTyClases($ot, $indiceOT, $arrayOT, $clases);
                    $band = true;
                    $indiceOT++;
                }
            }
        }

        if ($band) {
            return $arrayOT;
        } else {
            return 0;
        }
    }

    public function showVistaMaquina()
    {
        if ($this->retornarOTs() != 0) {
            $arregloOT = $this->retornarOTs();
            return view('processesAdmin.Maquinas.maquinas', compact('arregloOT'));
        } else {
            return view('processesAdmin.Maquinas.maquinas');
        }
    }
    public function showMachinesProcess(Request $request)
    {
        $ot = Orden_trabajo::find($request->ot);
        $clase = Clase::find($request->clase);
        $procesos = array();

        $proceso = Procesos::where('id_clase', $clase->id)->first();
        $proceso = $proceso->toArray();
        $camposNoCero = array_filter($proceso, function ($valor) {
            return $valor != 0;
        });
        $contador = 0;
        $indice = 0;

        foreach (array_keys($camposNoCero) as $nombreCampo) {
            if ($contador != 0 || $contador != 1) {
                $procesos[$indice] = array();
                $procesos[$indice][0] = $this->nombreProceso($nombreCampo);
                switch ($nombreCampo) {
                    case "cepillado":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Pza_cepillado::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "desbaste_exterior":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Desbaste_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "revision_laterales":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = RevLaterales_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "pOperacion":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = PrimeraOpeSoldadura_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "barreno_maniobra":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = BarrenoManiobra_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "sOperacion":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = SegundaOpeSoldadura_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "soldadura":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Soldadura_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "soldaduraPTA":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = SoldaduraPTA_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "rectificado":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Rectificado_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "asentado":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Asentado_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "calificado":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = revCalificado_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "acabadoBombillo":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = AcabadoBombilo_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "acabadoMolde":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = AcabadoMolde_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case 'barreno_profundidad':
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = BarrenoProfundidad_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "cavidades":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Cavidades_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "copiado":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Copiado_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "offSet":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = OffSet_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "palomas":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Palomas_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "rebajes":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = Rebajes_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "grabado":
                        $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        break;
                    case "operacionEquipo":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = PySOpeSoldadura_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            //Obtener usuario
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            //Obtener operacion
                                            $operacion = PySOpeSoldadura::find($pieza->id_proceso);
                                            $operacion = $operacion->operacion;
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "---", $meta->maquina, $operacion);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_pieza, $user->nombre, "Terminada", $meta->maquina, $operacion);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    case "embudoCM":
                        $metas = Metas::where('id_clase', $clase->id)->get();
                        if (count($metas) > 0) {
                            $pzasNoCero = 0;
                            foreach ($metas as $meta) {
                                $piezas = EmbudoCM_pza::where('id_meta', $meta->id)->get();
                                if (count($piezas) > 0) {
                                    foreach ($piezas as $pieza) {
                                        if ($pieza->estado != 0) {
                                            $user = User::where('matricula', $meta->id_usuario)->first();
                                            if ($pieza->estado == 1) {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "---", $meta->maquina);
                                            } else {
                                                $procesos[$indice][1][$pzasNoCero] = array($pieza->n_juego, $user->nombre, "Terminada", $meta->maquina);
                                            }
                                            $pzasNoCero++;
                                        }
                                    }
                                }
                            }
                            if ($pzasNoCero == 0) {
                                $procesos[$indice][1][$pzasNoCero] = array("---", "---", "---", "---");
                            }
                        } else {
                            $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        }
                        break;
                    default:
                        break;
                }
                $indice++;
            }

            $contador++;
        }
        array_splice($procesos, 0, 2);
        return view('processesAdmin.Maquinas.vistaProcesos', compact('procesos', 'ot', 'clase'));
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
                return "Barreno Maniobra";
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
                return "Operacion Equipo";
            case "embudoCM":
                return "Embudo CM";
        }
    }
}
