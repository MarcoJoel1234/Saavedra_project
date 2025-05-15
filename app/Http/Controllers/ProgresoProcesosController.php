<?php

namespace App\Http\Controllers;

use App\Models\Cepillado;
use App\Models\Clase;
use App\Models\Desbaste_pza;
use App\Models\DesbasteExterior;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\PrimeraOpeSoldadura;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\Pza_cepillado;
use App\Models\RevLaterales;
use App\Models\RevLaterales_pza;
use App\Models\SegundaOpeSoldadura;
use App\Models\SegundaOpeSoldadura_pza;
use Illuminate\Http\Request;

class ProgresoProcesosController extends Controller
{
    public function show()
    {
        $ot = $this->almacenarDatos();
        if ($ot != 0) {
            return view('processesAdmin.verProcesos', ['ot' => $ot]);
        }
        return view('processesAdmin.verProcesos');
    }
    public function almacenarDatos()
    {
        $clases = Clase::all();
        if (count($clases) > 0) {
            //Array para guardar los datos obtenidos
            $ot = array();
            //Guardar los datos por cada orden de trabajo y clase
            $contador = 0;
            foreach ($clases as $clases) {
                for ($i = 0; $i < 26; $i++) {
                    $ordenT = Orden_trabajo::where('id', $clases->id_ot)->first();
                    switch ($i) {
                        case 0:
                            $ot[$contador][$i] = $ordenT->id;
                            break;
                        case 1:
                            //Buscar la moldura y guardarla
                            $moldura = Moldura::find($ordenT->id_moldura);
                            $ot[$contador][$i] = $moldura->nombre;
                            break;
                        case 2:
                            $ot[$contador][$i] = $clases->nombre . " " . $clases->tamanio;
                            break;
                        case 3:
                            $proceso = 'Cepillado_' . $clases->nombre . "_" . $ordenT->id;
                            $cepillado = Cepillado::where('id_proceso', $proceso)->first();

                            if ($cepillado != null) {
                                $ot[$contador][$i] = count(Pza_cepillado::where('estado', 2)->where('correcto', 1)->where('id_proceso', $cepillado->id)->get()) / 2;
                            } else {
                                $ot[$contador][$i] = 0;
                            }
                            break;
                        case 4:
                            $proceso = 'desbaste_' . $clases->nombre . "_" . $ordenT->id;
                            $desbaste = DesbasteExterior::where('id_proceso', $proceso)->first();

                            if ($desbaste != null) {
                                $pzasCorrectas = Desbaste_pza::where('estado', 2)->where('correcto', 1)->where('id_proceso', $desbaste->id)->get();
                                if (isset($pzasCorrectas)) { //Si existen piezas correctas, se actualiza el resultado de la meta.
                                    $correctas = 0;
                                    $juegosUtilizados = array(); //Array para guardar los juegos que ya se han utilizado.
                                    for ($x = 0; $x < count($pzasCorrectas); $x++) { //Recorrer todas las piezas correctas.
                                        for ($y = 0; $y < count($pzasCorrectas); $y++) {
                                            if ($pzasCorrectas[$x]->n_juego === $pzasCorrectas[$y]->n_juego && $x != $y) {
                                                if ($pzasCorrectas[$x]->correcto == 1 && $pzasCorrectas[$y]->correcto == 1) {
                                                    if (array_search($pzasCorrectas[$x]->n_juego, $juegosUtilizados) === false) {
                                                        array_push($juegosUtilizados, $pzasCorrectas[$x]->n_juego);
                                                        $correctas++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $ot[$contador][$i] = $correctas; //Actualización de datos en tabla Metas.
                                } else {
                                    $ot[$contador][$i] = 0; //Actualización de los datos en la tabla metas.
                                }
                            } else {
                                $ot[$contador][$i] = 0;
                            }
                            break;
                        case 5:
                            $proceso = 'revLaterales_' . $clases->nombre . "_" . $ordenT->id;
                            $revLaterales = RevLaterales::where('id_proceso', $proceso)->first();

                            if ($revLaterales != null) {
                                $pzasCorrectas = RevLaterales_pza::where('estado', 2)->where('correcto', 1)->where('id_proceso', $revLaterales->id)->get();
                                if (isset($pzasCorrectas)) { //Si existen piezas correctas, se actualiza el resultado de la meta.
                                    $correctas = 0;
                                    $juegosUtilizados = array(); //Array para guardar los juegos que ya se han utilizado.
                                    for ($x = 0; $x < count($pzasCorrectas); $x++) { //Recorrer todas las piezas correctas.
                                        for ($y = 0; $y < count($pzasCorrectas); $y++) {
                                            if ($pzasCorrectas[$x]->n_juego === $pzasCorrectas[$y]->n_juego && $x != $y) {
                                                if ($pzasCorrectas[$x]->correcto == 1 && $pzasCorrectas[$y]->correcto == 1) {
                                                    if (array_search($pzasCorrectas[$x]->n_juego, $juegosUtilizados) === false) {
                                                        array_push($juegosUtilizados, $pzasCorrectas[$x]->n_juego);
                                                        $correctas++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $ot[$contador][$i] = $correctas; //Actualización de datos en tabla Metas.
                                } else {
                                    $ot[$contador][$i] = 0; //Actualización de los datos en la tabla metas.
                                }
                            } else {
                                $ot[$contador][$i] = 0;
                            }
                            break;
                        case 6:
                            $proceso = '1opeSoldadura_' . $clases->nombre . "_" . $ordenT->id;
                            $primeraOpeSoldadura = PrimeraOpeSoldadura::where('id_proceso', $proceso)->first();

                            if ($primeraOpeSoldadura != null) {
                                $pzasCorrectas = PrimeraOpeSoldadura_pza::where('estado', 2)->where('correcto', 1)->where('id_proceso', $primeraOpeSoldadura->id)->get();
                                if (isset($pzasCorrectas)) { //Si existen piezas correctas, se actualiza el resultado de la meta.
                                    $correctas = 0;
                                    $juegosUtilizados = array(); //Array para guardar los juegos que ya se han utilizado.
                                    for ($x = 0; $x < count($pzasCorrectas); $x++) { //Recorrer todas las piezas correctas.
                                        for ($y = 0; $y < count($pzasCorrectas); $y++) {
                                            if ($pzasCorrectas[$x]->n_juego === $pzasCorrectas[$y]->n_juego && $x != $y) {
                                                if ($pzasCorrectas[$x]->correcto == 1 && $pzasCorrectas[$y]->correcto == 1) {
                                                    if (array_search($pzasCorrectas[$x]->n_juego, $juegosUtilizados) === false) {
                                                        array_push($juegosUtilizados, $pzasCorrectas[$x]->n_juego);
                                                        $correctas++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $ot[$contador][$i] = $correctas; //Actualización de datos en tabla Metas.
                                } else {
                                    $ot[$contador][$i] = 0; //Actualización de los datos en la tabla metas.
                                }
                            } else {
                                $ot[$contador][$i] = 0;
                            }
                            break;
                        case 7:
                            $proceso = '2opeSoldadura_' . $clases->nombre . "_" . $ordenT->id;
                            $segundaOpeSoldadura = SegundaOpeSoldadura::where('id_proceso', $proceso)->first();

                            if ($segundaOpeSoldadura != null) {
                                $pzasCorrectas = SegundaOpeSoldadura_pza::where('estado', 2)->where('correcto', 1)->where('id_proceso', $segundaOpeSoldadura->id)->get();
                                if (isset($pzasCorrectas)) { //Si existen piezas correctas, se actualiza el resultado de la meta.
                                    $correctas = 0;
                                    $juegosUtilizados = array(); //Array para guardar los juegos que ya se han utilizado.
                                    for ($x = 0; $x < count($pzasCorrectas); $x++) { //Recorrer todas las piezas correctas.
                                        for ($y = 0; $y < count($pzasCorrectas); $y++) {
                                            if ($pzasCorrectas[$x]->n_juego === $pzasCorrectas[$y]->n_juego && $x != $y) {
                                                if ($pzasCorrectas[$x]->correcto == 1 && $pzasCorrectas[$y]->correcto == 1) {
                                                    if (array_search($pzasCorrectas[$x]->n_juego, $juegosUtilizados) === false) {
                                                        array_push($juegosUtilizados, $pzasCorrectas[$x]->n_juego);
                                                        $correctas++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $ot[$contador][$i] = $correctas; //Actualización de datos en tabla Metas.
                                } else {
                                    $ot[$contador][$i] = 0; //Actualización de los datos en la tabla metas.
                                }
                            } else {
                                $ot[$contador][$i] = 0;
                            }
                            break;
                        case 8:
                            $ot[$contador][$i] = 0;
                            break;
                        case 9:
                            $ot[$contador][$i] = 0;
                            break;
                        case 10:
                            $ot[$contador][$i] = 0;
                            break;
                        case 11:
                            $ot[$contador][$i] = 0;
                            break;
                        case 12:
                            $ot[$contador][$i] = 0;
                            break;
                        case 13:
                            $ot[$contador][$i] = 0;
                            break;
                        case 14:
                            $ot[$contador][$i] = 0;
                            break;
                        case 15:
                            $ot[$contador][$i] = 0;
                            break;
                        case 16:
                            $ot[$contador][$i] = 0;
                            break;
                        case 17:
                            $ot[$contador][$i] = 0;
                            break;
                        case 18:
                            $ot[$contador][$i] = 0;
                            break;
                        case 19:
                            $ot[$contador][$i] = 0;
                            break;
                        case 20:
                            $ot[$contador][$i] = 0;
                            break;
                        case 21:
                            $ot[$contador][$i] = 0;
                            break;
                        case 22:
                            $ot[$contador][$i] = 0;
                            break;
                        case 23:
                            $ot[$contador][$i] = 0;
                            break;
                        case 24:
                            $ot[$contador][$i] = 0;
                            break;
                        case 25:
                            $ot[$contador][$i] = $clases->pedido;
                            break;
                    }
                }
                $contador++;
            }
            return $ot;
        }
        return 0;
    }
}
