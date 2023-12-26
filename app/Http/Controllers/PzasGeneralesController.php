<?php

namespace App\Http\Controllers;

use App\Models\AcabadoBombilo_pza;
use App\Models\AcabadoMolde_pza;
use App\Models\Asentado_pza;
use App\Models\BarrenoManiobra_pza;
use App\Models\Cavidades_pza;
use App\Models\Cepillado;
use App\Models\Clase;
use App\Models\Desbaste_pza;
use App\Models\Metas;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\Procesos;
use App\Models\Pza_cepillado;
use App\Models\Rectificado;
use App\Models\Rectificado_pza;
use App\Models\revCalificado_pza;
use App\Models\RevLaterales_pza;
use App\Models\SegundaOpeSoldadura_pza;
use App\Models\Soldadura_pza;
use App\Models\SoldaduraPTA_pza;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

use function Symfony\Component\String\b;
//Clase para el control de las piezas generales
class PzasGeneralesController extends Controller
{
    public function show()
    {
        $ot = Orden_trabajo::all();
        return view('processesAdmin.AdminPzas', compact('ot'));
    }
    public function search(Request $request)
    {
        $action = $request->input('action');

        $array = array();
        $otElegida = Orden_trabajo::find($request->ot);
        $clases = Clase::where('id_ot', $otElegida->id)->get();
        $operadores = $this->getOperadores($otElegida->id);
        $maquina = Pieza::where('id_ot', $otElegida->id)->distinct('maquina')->pluck('maquina');
        $proceso = ["Cepillado", "Desbaste Exterior", "Revisión Laterales", "Primera Operación Soldadura", "Barreno Maniobra", "Segunda Operación Soldadura", "Soldadura", "Soldadura PTA", "Rectificado", "Asentado", "Revision Calificado", "Acabado Bombillo", "Acabado Molde", "Cavidades"];

        $piezas = $this->buscarPiezas($otElegida, $request->clase, $request->operador, $request->maquina, $request->proceso, $array);
        $array = $piezas[1];
        $piezas = $piezas[0];
        if ($action != 'pdf' || $action == null) {
            return view('processesAdmin.AdminPzas', compact('piezas', 'otElegida', 'clases', 'operadores', 'maquina', 'array', 'proceso'));
        } else {
            $pdf = Pdf::loadView('processesAdmin.pdf', compact('piezas', 'otElegida', 'clases', 'operadores', 'maquina', 'array', 'proceso'));
            return $pdf->download('Reporte de piezas.pdf');
        }
    }
    public function buscarPiezas($ot,  $clase, $operador, $maquina, $proceso, $itemElegidos)
    {
        $array = array();
        if ($ot != null) {
            $array = Pieza::where('id_ot', $ot->id)->get();
            $array = $this->saveInArray($array);
            if (($clase != "todos" && isset($clase)) && $array != "[]") {
                $array = $this->buscarElemento($array, 1, $clase);
                $itemElegidos[0] = $clase;
            } else {
                $itemElegidos[0] = "Todos";
            }
            if (($operador != "todos" && isset($operador)) && $array != "[]") {
                $array = $this->buscarElemento($array, 2, $operador);
                $itemElegidos[1] = $operador;
            } else {
                $itemElegidos[1] = "Todos";
            }
            if (($maquina != "todos" && isset($maquina)) && $array != "[]") {
                $array = $this->buscarElemento($array, 3, $maquina);
                $itemElegidos[2] = $maquina;
            } else {
                $itemElegidos[2] = "Todos";
            }
            if (($proceso != "todos" && isset($proceso)) && $array != "[]") {
                $array = $this->buscarElemento($array, 4, $proceso);
                $itemElegidos[3] = $proceso;
            } else {
                $itemElegidos[3] = "Todos";
            }
        }
        return [$array, $itemElegidos];
    }
    public function buscarElemento($arrayP, $posicion, $elemento)
    {
        $array = array();
        for ($i = 0; $i < count($arrayP); $i++) {
            if (strpos($arrayP[$i][$posicion], $elemento) !== false) {
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
        $contador = 0;
        foreach ($arrayP as $item) {
            $array[$contador][0] = $item->n_pieza;
            $array[$contador][1] = $this->getNameClase($item->id_clase);
            $array[$contador][2] = $this->getNameOperador($item->id_operador);
            $array[$contador][3] = $item->maquina;
            $array[$contador][4] = $item->proceso;
            $array[$contador][5] = $item->error;
            $contador++;
        }
        return $array;
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


    //Funciones para el control de la vista de piezas por maquina
    public function showVistaMaquina()
    {
        $ot = Orden_trabajo::all();
        if (count($ot) > 0) {
            $arregloOT = array();
            $indiceOT = 0;
            foreach ($ot as $ot) {
                $clases = Clase::where('id_ot', $ot->id)->get();
                if ($clases->count() > 0) {
                    //Insertar la ot en el arreglo
                    $arregloOT[$indiceOT] = array();
                    $arregloOT[$indiceOT][0] = $ot->id;

                    $indiceClass = 0;
                    foreach ($clases as $clase) {
                        //Insertar la clase en el arreglo
                        $arregloOT[$indiceOT][1][$indiceClass] = array();
                        $arregloOT[$indiceOT][1][$indiceClass][0] = $clase->id;
                        $arregloOT[$indiceOT][1][$indiceClass][1] = $clase->nombre . " " . $clase->tamanio;
                        $indiceClass++;
                    }
                    $indiceOT++;
                }
            }
            return view('processesAdmin.Maquinas.maquinas', compact('arregloOT'));
        }
        return view('processesAdmin.Maquinas.maquinas');
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
                    case "barreno_profundidad":
                        $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        break;
                    case "copiado":
                        $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        break;
                    case "offSet":
                        $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        break;
                    case "palomas":
                        $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        break;
                    case "rebajes":
                        $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        break;
                    case "grabado":
                        $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        break;
                    case "operacioneEquipo":
                        $procesos[$indice][1][0] = array("---", "---", "---", "---");
                        break;
                    case "embudoCM":
                        $procesos[$indice][1][0] = array("---", "---", "---", "---");
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
}
