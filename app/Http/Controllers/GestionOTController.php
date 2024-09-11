<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Fecha_proceso;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\PySOpeSoldadura;
use App\Models\PySOpeSoldadura_pza;
use App\Models\User;
use DateTime;
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
                            $clases[$i][$contador][0] = $clase->nombre;
                            $clases[$i][$contador][1] = $clase->fecha_inicio . " " . $clase->hora_inicio;
                            $clases[$i][$contador][1] = $this->obtenerStringFecha($clase->fecha_inicio, $clase->hora_inicio);
                            if ($clase->fecha_termino == null) {
                                $clases[$i][$contador][2] = "-";
                            } else {
                                $clases[$i][$contador][2] = $this->obtenerStringFecha($clase->fecha_termino, $clase->hora_termino);
                            }
                            $pedidos[$i][$contador] = $clase->pedido;
                            $procesosClase = $procesosClase->toArray();
                            $camposNoCero = array_filter($procesosClase, function ($valor) {
                                return $valor != 0;
                            });
                            $procesosClases[$i][$contador][0] = array();
                            foreach (array_keys($camposNoCero) as $nombreCampo) {
                                array_push($procesosClases[$i][$contador][0], $nombreCampo);
                            }
                            array_splice($procesosClases[$i][$contador][0], 0, 2);
                            for ($j = 0; $j < count($procesosClases[$i][$contador][0]); $j++) {
                                //Prueba de datos para agregar fecha y hora
                                $fechaTermino = Fecha_proceso::where('clase', $clase->id)->where('proceso', $procesosClases[$i][$contador][0][$j])->first();
                                if ($fechaTermino) {
                                    $fechaTForma = new DateTime($fechaTermino->fecha_fin);
                                    $fechaTForma = $fechaTForma->format('d-m-Y');

                                    $horaTForma = new DateTime($fechaTermino->fecha_fin);
                                    $horaTForma = $horaTForma->format('H:i:s');
                                    $procesosClases[$i][$contador][1][$j] = $this->obtenerStringFecha($fechaTForma, $horaTForma);
                                }else{
                                    $procesosClases[$i][$contador][1][$j] = "No terminado";
                                }

                                switch ($procesosClases[$i][$contador][0][$j]) {
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
                                        } else {
                                            $pzasBuenas = 0;
                                            $pzasMalas = 0;
                                            $pzasTotales = 0;
                                        }
                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;

                                        //Verificar que existan piezas en las dos operaciones
                                        break;
                                    case 'grabado':
                                        $pzasBuenas = 0;
                                        $pzasMalas = 0;
                                        $pzasTotales = 0;

                                        $procesos[$i][$contador][$j][0] = $pzasBuenas;
                                        $procesos[$i][$contador][$j][1] = $pzasMalas;
                                        $procesos[$i][$contador][$j][2] = $pzasTotales;
                                        break;
                                    default:
                                        $proceso = $this->nombreProceso($procesosClases[$i][$contador][0][$j]);
                                        $array = $this->obtenerPiezasBM($otArray[$i], $clase->id, $proceso, $infoPzMala[$i][$contador]);
                                        $procesos[$i][$contador][$j][0] = $array[0]; //Piezas buenas
                                        $procesos[$i][$contador][$j][1] = $array[1]; //Piezas malas
                                        $procesos[$i][$contador][$j][2] = $array[2]; //Piezas totales
                                        break;
                                }
                                $procesosClases[$i][$contador][0][$j] = $this->nombreProceso($procesosClases[$i][$contador][0][$j]);
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
    public function obtenerStringFecha($fecha, $hora)
    {
        $fechaFormat = new DateTime($fecha);
        $fechaFormat = $fechaFormat->format('d-m-Y');

        //Establecer la fecha en español
        $nombreDia = new DateTime($fecha);
        $nombreDia = $nombreDia->format('l');

        switch ($nombreDia) {
            case "Monday":
                $nombreDia = "Lunes";
                break;
            case "Tuesday":
                $nombreDia = "Martes";
                break;
            case "Wednesday":
                $nombreDia = "Miercoles";
                break;
            case "Thursday":
                $nombreDia = "Jueves";
                break;
            case "Friday":
                $nombreDia = "Viernes";
                break;
            case "Saturday":
                $nombreDia = "Sabado";
                break;
            case "Sunday":
                $nombreDia = "Domingo";
                break;
        }

        $horaFormateada = new DateTime($hora);
        $horaFormateada = $horaFormateada->format('H:i:s A');

        return $nombreDia . " " . $fechaFormat . " " . $horaFormateada;
    }
    public function savePzasMalas($pzaMala, $contador, $i, &$infoPzMala)
    {
        $info = array();
        for ($p = 0; $p < count($pzaMala); $p++) {
            $info[0] = $pzaMala[$p]->n_pieza;
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
                return "Revision Calificado";
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
    function obtenerPiezasBM($ot, $clase, $proceso, &$infoPzMala)
    {
        $juegosRegistrados = array();
        $pBuenas = array();
        $pMalas = array();
        $pzasTotales = 0;
        $pProceso = Pieza::where("proceso", $proceso)->where('id_ot', $ot)->where('id_clase', $clase)->get();

        if (count($pProceso) > 0) {
            //Recorrer cada una de las piezas
            foreach ($pProceso as $pza) {
                //Verificar si es juego o pieza
                if (substr($pza->n_pieza, -1, 1) != "J") {
                    $pares = true;
                    preg_match('/^\d+/', $pza->n_pieza, $n_juego); //Obtener el numero de juego de la pieza
                    //Comprobar si el juego ya fue registrado
                    if (!in_array($n_juego[0], $juegosRegistrados)) {
                        array_push($juegosRegistrados, $n_juego[0]); //Almacenar el juego en el array

                        //Obtener las piezas del juego
                        $pHembra = Pieza::where("n_pieza", $n_juego[0] . "H")->where('id_ot', $ot)->where('id_clase', $clase)->where('proceso', $proceso)->first();
                        $pMacho = Pieza::where("n_pieza", $n_juego[0] . "M")->where('id_ot', $ot)->where('id_clase', $clase)->where('proceso', $proceso)->first();

                        //Verificar si ambas piezas existen
                        if ($pHembra && $pMacho) {

                            //Verificar si el juego esta rechazado o liberado
                            if ($pHembra->liberacion == 0) {
                                //Verificar si las pieza son correctas o no
                                if ($pHembra->error == "Ninguno" && $pMacho->error == "Ninguno") {
                                    array_push($pBuenas, $pHembra, $pMacho);
                                } else {
                                    //Guardar el juego completo como malo
                                    array_push($pMalas, $pHembra, $pMacho);

                                    if ($pHembra->error != "Ninguno") {
                                        array_push($infoPzMala, $this->getDatosPMalas($pHembra));
                                    }
                                    if ($pMacho->error != "Ninguno") {
                                        array_push($infoPzMala, $this->getDatosPMalas($pMacho));
                                    }
                                }
                            } else if ($pHembra->liberacion == 1) {
                                array_push($pBuenas, $pHembra, $pMacho);
                            } else {
                                array_push($pMalas, $pHembra, $pMacho);

                                if ($pHembra->error != "Ninguno") {
                                    array_push($infoPzMala, $this->getDatosPMalas($pHembra));
                                } else {
                                    array_push($infoPzMala, $this->getDatosPMalas($pHembra, "Rechazada"));
                                }
                                if ($pMacho->error != "Ninguno") {
                                    array_push($infoPzMala, $this->getDatosPMalas($pMacho));
                                } else {
                                    array_push($infoPzMala, $this->getDatosPMalas($pMacho, "Rechazada"));
                                }
                            }
                        } else {
                            if ($pHembra) {
                                $piezaIncompleta = $pHembra;
                            } else {
                                $piezaIncompleta = $pMacho;
                            }

                            if ($piezaIncompleta->liberacion == 2) {
                                array_push($pMalas, $piezaIncompleta, $piezaIncompleta);
                                array_push($infoPzMala, $this->getDatosPMalas($piezaIncompleta, "Rechazada"));
                            }
                        }
                    }
                } else {
                    $pares = false;
                    $pzasTotales = count($pProceso);
                    //Verificar si el juego esta rechazado o liberado
                    if ($pza->liberacion == 0) {
                        //Verificar si las pieza son correctas o no
                        if ($pza->error == "Ninguno") {
                            array_push($pBuenas, $pza);
                        } else {
                            //Guardar el juego completo como malo
                            array_push($pMalas, $pza);
                            array_push($infoPzMala, $this->getDatosPMalas($pza));
                        }
                    } else if ($pza->liberacion == 1) {
                        array_push($pBuenas, $pza);
                    } else {
                        array_push($pMalas, $pza);
                        if ($pza->error != "Ninguno") {
                            array_push($infoPzMala, $this->getDatosPMalas($pza));
                        } else {
                            array_push($infoPzMala, $this->getDatosPMalas($pza, "Rechazada"));
                        }
                    }
                }
            }
            if (isset($pares)) {
                if ($pares) {
                    $pzasTotales = count($pProceso) / 2;
                    $pzasBuenas = count($pBuenas) / 2;
                    $pzasMalas = count($pMalas) / 2;
                } else {
                    $pzasTotales = count($pProceso);
                    $pzasBuenas = count($pBuenas);
                    $pzasMalas = count($pMalas);
                }
            }
        } else {
            $pzasTotales = 0;
            $pzasBuenas = 0;
            $pzasMalas = 0;
        }

        return [$pzasBuenas, $pzasMalas, $pzasTotales];
    }
    function getDatosPMalas($pieza, $rechazada = null)
    {
        $array = array();
        $operador = User::where('matricula', $pieza->id_operador)->first();
        $array[0] = $pieza->n_pieza;
        //Obtener el numero de juego
        preg_match('/^\d+/', $pieza->n_pieza, $n_juego);
        $array[1] = $n_juego[0] . "J";
        $array[2] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
        $array[3] = $pieza->proceso;
        $array[4] = "- - - ";
        //Si la pieza no tiene ningun error pero esta rechazada
        if ($rechazada != null) {
            $array[5] = "Rechazada";
        } else {
            $array[5] = $pieza->error;
        }
        return $array;
    }
}
