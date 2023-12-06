<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\PrimeraOpeSoldadura;
use App\Models\PrimeraOpeSoldadura_cnominal;
use App\Models\PrimeraOpeSoldadura_pza;
use App\Models\PrimeraOpeSoldadura_tolerancia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PrimeraOpeSoldaduraController extends Controller
{
    public function show()
    {
        $ot = Orden_trabajo::all(); //Obtención de todas las ordenes de trabajo.
        return view('processes.primeraOpeSoldadura', ['ot' => $ot]);
    }
    public function storeheaderTable(Request $request)
    {
        //Si se obtienen los datos de la OT y la meta, se guardan en variables de sesión.
        if (session('controller')) {
            $meta = Metas::find(session('meta')); //Busco la meta de la OT.
        } else {
            $meta = Metas::find($request->metaData); //Busco la meta de la OT.
        }
        $ot = Orden_trabajo::where('id', $meta->id_ot)->first(); //Busco la OT que se quiere editar.
        $clase = Clase::find($meta->id_clase); //Busco la clase de la OT.
        $id = "1opeSoldadura_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Cepillado.
        $cNominal = PrimeraOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = PrimeraOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $id_proceso = PrimeraOpeSoldadura::where('id_proceso', $id)->first();

        if (isset($request->n_pieza)) {  //Si se obtienen los datos de las piezas, se guardan en la tabla Cepillado_cnominal.
            $id_pieza = $request->n_pieza . $id_proceso->id; //Creación de id para tabla Cepillado_cnominal.
            $piezaExistente = PrimeraOpeSoldadura_pza::where('id_pza', $id_pieza)->first();
            if ($piezaExistente) {
                $piezaExistente->diametro1 = $request->diametro1;
                $piezaExistente->profundidad1 = $request->profundidad1;
                $piezaExistente->diametro2 = $request->diametro2;
                $piezaExistente->profundidad2 = $request->profundidad2;
                $piezaExistente->diametro3 = $request->diametro3;
                $piezaExistente->profundidad3 = $request->profundidad3;
                $piezaExistente->diametroSoldadura = $request->diametroSoldadura;
                $piezaExistente->profundidadSoldadura = $request->profundidadSoldadura;
                $piezaExistente->diametroBarreno = $request->diametroBarreno;
                $piezaExistente->simetriaLinea_partida = $request->simetriaLinea_partida;
                $piezaExistente->pernoAlineacion = $request->pernoAlineacion;
                $piezaExistente->Simetria90G = $request->Simetria90G;
                $piezaExistente->observaciones = $request->observaciones;
                $piezaExistente->estado = 2;
                $piezaExistente->save();

                if ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error == 0) {
                    $piezaExistente->error = 'Maquinado';
                    $piezaExistente->correcto = 0;
                } else if (($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 1 && $request->error == 'Fundicion')) {
                    $piezaExistente->error = $request->error;
                    $piezaExistente->correcto = 0;
                } else {
                    $piezaExistente->error = 'Ninguno';
                    $piezaExistente->correcto = 1;
                }
                $piezaExistente->save();

                $pieza = Pieza::where('n_pieza', $piezaExistente->n_pieza)->where('proceso', 'Primera Operacion Soldadura')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if (!isset($pieza)) {
                    $pieza = new Pieza();
                }
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id;
                $pieza->n_pieza = $request->n_pieza;
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina;
                $pieza->proceso = "Primera Operacion Soldadura";
                $pieza->error = $piezaExistente->error;
                $pieza->save();

                //Actualizar resultado de la meta
                $pzasCorrectas = PrimeraOpeSoldadura_pza::where('id_meta', $meta->id)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas.
                Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                    'resultado' => $pzasCorrectas->count() / 2,
                ]);
                $meta = Metas::find($meta->id); //Busco la meta de la OT.

                //  //Retornar la pieza siguiente
                $pzaUtilizar = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Cepillado.
                    $pzasCreadas = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))]); //Retorno a vista de Cepillado.
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla desbaste
                    $this->piezaUtilizar($ot->id, $clase);
                }
            }
        } else if (isset($request->n_juegoElegido)) {
            $juegoExiste = PrimeraOpeSoldadura_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->first();
            if (!$juegoExiste) {
                //Obtener el numero del juego para crear las piezas
                $numero = "";
                $juegoDividido = str_split($request->n_juegoElegido);
                for ($i = 0; $i < count($juegoDividido) - 1; $i++) {
                    $numero .= $juegoDividido[$i];
                }
                //For para crear las dos piezas del juego
                for ($i = 0; $i < 2; $i++) {
                    $newPza = new PrimeraOpeSoldadura_pza(); //Creación de objeto para llenar tabla Desbaste.
                    if ($i == 0) {
                        $newPza->id_pza = $numero . "M" . $id_proceso->id; //Creación de id para tabla desbaste.
                        $newPza->n_pieza = $numero . "M";
                    } else {
                        $newPza->id_pza = $numero . "H" . $id_proceso->id; //Creación de id para tabla desbaste.
                        $newPza->n_pieza = $numero . "H";
                    }
                    $newPza->id_meta = $meta->id; //Llenado de id_meta para tabla Desbaste.
                    $newPza->id_proceso = $id_proceso->id; //Llenado de id_proceso para tabla Desbaste.
                    $newPza->estado = 1; //Llenado de estado para tabla Desbaste.
                    $newPza->n_juego = $request->n_juegoElegido; //Llenado de estado para tabla Desbaste.
                    $newPza->save(); //Guardado de datos en la tabla Desbaste.
                }
            }
        } else {
            $proceso = PrimeraOpeSoldadura::where('id_proceso', $id)->first(); //Busco el proceso de la OT.
            if (!$proceso) {
                //Llenado de la tabla Cepillado.
                $desbaste = new PrimeraOpeSoldadura(); //Creación de objeto para llenar tabla Cepillado.
                $desbaste->id_proceso = $id; //Creación de id para tabla Cepillado.
                $desbaste->id_ot = $ot->id; //Llenado de id_proceso para tabla Cepillado.
                $desbaste->save(); //Guardado de datos en la tabla Cepillado.
            }
        }
        $id_proceso = PrimeraOpeSoldadura::where('id_proceso', $id)->first();
        if ($id_proceso !== "[]") {
            $pzasCreadas = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            for ($i = 0; $i < count($pzasCreadas); $i++) { //Recorro las piezas creadas.
                //Acrualiza el estado correcto de la pieza.
                if ($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia) == 0 && ($pzasCreadas[$i]->error == 'Maquinado' || $pzasCreadas[$i]->error == 'Ninguno')) {
                    $pzasCreadas[$i]->error = 'Maquinado';
                    $pzasCreadas[$i]->correcto = 0;
                } else if (($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia) == 0 && $pzasCreadas[$i]->error == 'Fundicion') || ($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia) == 1 && $pzasCreadas[$i]->error == 'Fundicion')) {
                    $pzasCreadas[$i]->error = 'Fundicion';
                    $pzasCreadas[$i]->correcto = 0;
                } else {
                    $pzasCreadas[$i]->error = 'Ninguno';
                    $pzasCreadas[$i]->correcto = 1; //pieza correcto de la pieza.
                }
                $pzasCreadas[$i]->save();
            }

            $pzasCorrectas = PrimeraOpeSoldadura_pza::where('id_meta', $meta->id)->where('correcto', 1)->get();
            if (isset($pzasCorrectas)) { //Si existen piezas correctas, se actualiza el resultado de la meta.
                $correctas = 0;
                $juegosUtilizados = array();
                for ($x = 0; $x < count($pzasCorrectas); $x++) {
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
                $meta->resultado = $correctas; //Actualización de datos en tabla Metas.
            } else {
                $meta->resultado = 0; //Actualización de los datos en la tabla metas.
            }
            $meta->save(); //Guardado de datos en la tabla Metas.

            if (isset($cNominal) && isset($tolerancia)) {
                $pzaUtilizar = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                    $piezasVacias = PrimeraOpeSoldadura_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                    if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                        for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                            $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                            if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                                $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_cepillado.
                                $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_cepillado.
                                $pzaUtilizar = $piezasVacias[$i]; //Obtención de la pieza a utilizar.
                                $piezaEncontrada = true; //Se encontro una pieza para utilizar.
                                break; //Se rompe el ciclo.
                            } else {
                                $piezaEncontrada = false; //No se encontro una pieza para utilizar.
                            }
                        }
                    } else {
                        $piezaEncontrada = false; //No se encontro una pieza para utilizar.
                    }

                    if (!$piezaEncontrada) {
                        $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                    }
                }
                if (isset($pzasUtilizar)) { //Si no se encontro una pieza para utilizar, se crea una nueva pieza.
                    return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de Cepillado.
                } else {
                    return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
                }
            } else {
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        }
    }
    public function compararDatosPieza($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->diametro1 > ($cNominal->diametro1 + $tolerancia->diametro1) || $pieza->diametro1 < ($cNominal->diametro1 - $tolerancia->diametro1) || $pieza->profundidad1 > ($cNominal->profundidad1 + $tolerancia->profundidad1) || $pieza->profundidad1 < ($cNominal->profundidad1 - $tolerancia->profundidad1) || $pieza->diametro2 > ($cNominal->diametro2 + $tolerancia->diametro2) || $pieza->diametro2 < ($cNominal->diametro2 - $tolerancia->diametro2) || $pieza->profundidad2 > ($cNominal->profundidad2 + $tolerancia->profundidad2) || $pieza->profundidad2 < ($cNominal->profundidad2 - $tolerancia->profundidad2) || $pieza->diametro3 > ($cNominal->diametro3 + $tolerancia->diametro3) || $pieza->diametro3 < ($cNominal->diametro3 - $tolerancia->diametro3) || $pieza->profundidad3  > ($cNominal->profundidad3  + $tolerancia->profundidad3) || $pieza->profundidad3 < ($cNominal->profundidad3 - $tolerancia->profundidad3) || $pieza->diametroSoldadura > ($cNominal->diametroSoldadura  + $tolerancia->diametroSoldadura) || $pieza->diametroSoldadura < ($cNominal->diametroSoldadura - $tolerancia->diametroSoldadura) || $pieza->profundidadSoldadura > ($cNominal->profundidadSoldadura + $tolerancia->profundidadSoldadura) || $pieza->profundidadSoldadura < ($cNominal->profundidadSoldadura - $tolerancia->profundidadSoldadura) || $pieza->diametroBarreno < ($cNominal->diametroBarreno - $tolerancia->diametroBarreno1) || $pieza->diametroBarreno > ($cNominal->diametroBarreno + $tolerancia->diametroBarreno2) || $pieza->simetriaLinea_partida < ($cNominal->simetriaLinea_partida - $tolerancia->simetriaLinea_partida1) || $pieza->simetriaLinea_partida > ($cNominal->simetriaLinea_partida + $tolerancia->simetriaLinea_partida2) || $pieza->pernoAlineacion > ($cNominal->pernoAlineacion + $tolerancia->pernoAlineacion) || $pieza->pernoAlineacion < ($cNominal->pernoAlineacion - $tolerancia->pernoAlineacion) || $pieza->Simetria90G > ($cNominal->Simetria90G + $tolerancia->Simetria90G) || $pieza->Simetria90G < ($cNominal->Simetria90G - $tolerancia->Simetria90G)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
    public function edit(Request $request) 
    {
        $meta = Metas::find($request->metaData); //Busco la meta de la OT.
        $ot = Orden_trabajo::find($meta->id_ot); //Obtención de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $clase = Clase::find($meta->id_clase); //Busco la clase de la OT.
        $id = "1opeSoldadura_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Cepillado.
        $id_proceso = PrimeraOpeSoldadura::where('id_proceso', $id)->first();;
        $cNominal = PrimeraOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = PrimeraOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $pzasCreadas = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
        $pzaUtilizar = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        if (isset($request->n_pieza)) { //Si se obtienen los datos de las piezas, se gua
            for ($i = 0; $i < count($request->n_pieza); $i++) {
                $id_pieza = $request->n_pieza[$i] . $id_proceso->id; //Creación de id para tabla Desbaste_cnominal.
                $piezaExistente = PrimeraOpeSoldadura_pza::where('id_pza', $id_pieza)->first();
                if ($piezaExistente) {
                    $piezaExistente->diametro1 = $request->diametro1[$i];
                    $piezaExistente->profundidad1 = $request->profundidad1[$i];
                    $piezaExistente->diametro2 = $request->diametro2[$i];
                    $piezaExistente->profundidad2 = $request->profundidad2[$i];
                    $piezaExistente->diametro3 = $request->diametro3[$i];
                    $piezaExistente->profundidad3 = $request->profundidad3[$i];
                    $piezaExistente->diametroSoldadura = $request->diametroSoldadura[$i];
                    $piezaExistente->profundidadSoldadura = $request->profundidadSoldadura[$i];
                    $piezaExistente->diametroBarreno = $request->diametroBarreno[$i];
                    $piezaExistente->simetriaLinea_partida = $request->simetriaLinea_partida[$i];
                    $piezaExistente->pernoAlineacion = $request->pernoAlineacion[$i];
                    $piezaExistente->Simetria90G = $request->Simetria90G[$i];
                    if (isset($request->observaciones[$i])) { //Si se obtienen los datos de las piezas, se guardan en la tabla Desbaste_cnominal.
                        $piezaExistente->observaciones = $request->observaciones[$i];  //Llenado de observaciones para tabla Desbaste_cnominal.
                    }
                    $piezaExistente->save(); //Guardado de datos en la tabla Pza_cepillado.

                    //Acrualiza el estado correcto de la pieza.
                    if ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && ($request->error[$i] == "Ninguno" || $request->error[$i] == "Maquinado")) {
                        $piezaExistente->error = 'Maquinado';
                        $piezaExistente->correcto = 0;
                    } else if (($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error[$i] == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 1 && $request->error[$i] == 'Fundicion')) {
                        $piezaExistente->error = $request->error[$i];
                        $piezaExistente->correcto = 0;
                    } else {
                        $piezaExistente->error = 'Ninguno';
                        $piezaExistente->correcto = 1;
                    }
                    $piezaExistente->save();



                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_pieza)->where('proceso', 'Primera Operacion Soldadura')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if (!isset($pieza)) {
                        $pieza = new Pieza(); //Creación del obejeto para llenar la tabla pieza.
                    }
                    $pieza->id_clase = $clase->id; //Lenado de id_clase para la tabla pieza.
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $piezaExistente->n_pieza;
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Primera Operacion Soldadura";
                    $pieza->error = $piezaExistente->error;
                    $pieza->save();
                }
            }
            $pzasCorrectas = PrimeraOpeSoldadura_pza::where('id_meta', $meta->id)->where('correcto', 1)->get();
            if (isset($pzasCorrectas)) { //Si existen piezas correctas, se actualiza el resultado de la meta.
                $correctas = 0;
                $juegosUtilizados = array();
                for ($x = 0; $x < count($pzasCorrectas); $x++) {
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
                $meta->resultado = $correctas; //Actualización de datos en tabla Metas.
            } else {
                $meta->resultado = 0; //Actualización de los datos en la tabla metas.
            }
            $meta->save(); //Guardado de datos en la tabla Metas.
            //Retornar la pieza siguiente
            $pzaUtilizar = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $piezasVacias = PrimeraOpeSoldadura_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_cepillado.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_cepillado.
                            $pzaUtilizar = $piezasVacias[$i]; //Obtención de la pieza a utilizar.
                            $piezaEncontrada = true; //Se encontro una pieza para utilizar.
                            break; //Se rompe el ciclo.
                        } else {
                            $piezaEncontrada = false; //No se encontro una pieza para utilizar.
                        }
                    }
                } else {
                    $piezaEncontrada = false; //No se encontro una pieza para utilizar.
                }
                if (!$piezaEncontrada) { //Si no se encontro una pieza para utilizar, se crea una nueva pieza.
                    $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                }
            }
            $pzasCreadas = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = PrimeraOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = PrimeraOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.primeraOpeSoldadura', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))]); //Retorno la vista de cepillado.
                    }
                }
            }
            $pzaUtilizar = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $piezasVacias = PrimeraOpeSoldadura_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_cepillado.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_cepillado.
                            $pzaUtilizar = $piezasVacias[$i]; //Obtención de la pieza a utilizar.
                            $piezaEncontrada = true; //Se encontro una pieza para utilizar.
                            break; //Se rompe el ciclo.
                        } else {
                            $piezaEncontrada = false; //No se encontro una pieza para utilizar.
                        }
                    }
                } else {
                    $piezaEncontrada = false; //No se encontro una pieza para utilizar.
                }
                if (!$piezaEncontrada) { //Si no se encontro una pieza para utilizar, se crea una nueva pieza.
                    $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                }
            }
            $pzasCreadas = PrimeraOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = PrimeraOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = PrimeraOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        }
    }

    public function piezaUtilizar($ot, $clase) //Función para obtener la pieza a utilizar.
    {
        //Obtener las piezas que esten terminadas y correctas en la tabla cepillado para despues comparar cada una con su consecuente y asi armar los juegos
        $pzasUtilizar = array();
        $pzasGuardadas = array(); //Creación de array para guardar los números de pieza.
        $numero = ""; //Creación de variable para guardar el número de pieza.
        $pzasEncontradasDesbaste = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Desbaste Exterior')->get(); //Obtención de todas las piezas creadas.
        $pzasEncontradasRevision = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Revision Laterales')->get(); //Obtención de todas las piezas creadas.


        $id_proceso = "1opeSoldadura_" . $clase->nombre . "_" . $ot; //Creación de id para la tabla cepillado
        $proceso = PrimeraOpeSoldadura::where('id_proceso', $id_proceso)->first(); //Busco el proceso de la OT.

        $pzasOcupadas = PrimeraOpeSoldadura_pza::where('id_proceso', $proceso->id)->where('estado', 1)->get(); //Obtención de todas las piezas creadas.

        if ($proceso) {
            $pzasUsadas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Primera Operacion Soldadura')->get(); //Obtención de todas las piezas creadas.
        }
        for ($i = 0; $i < count($pzasEncontradasDesbaste); $i++) { //Recorro las piezas encontradas.
            if (array_search($pzasEncontradasDesbaste[$i]->n_pieza, $pzasGuardadas) == false) {
                if ($pzasEncontradasDesbaste[$i]->error == "Ninguno") {
                    $numerosUsados = array();
                    if (isset($pzasUsadas)) {
                        $numeroUsado = "";
                        for ($x = 0; $x < count($pzasUsadas); $x++) {
                            $pzaDividida_Usada = str_split($pzasUsadas[$x]->n_pieza); //División del número de pieza usada.
                            for ($h = 0; $h < count($pzaDividida_Usada) - 1; $h++) { //Recorro el número de pieza usada.
                                $numeroUsado .= $pzaDividida_Usada[$h]; //Obtención del número de pieza usada.
                            }
                            array_push($numerosUsados, $numeroUsado); //Guardo el número de ppieza usada.
                            $numeroUsado = ""; //Reinicio la variable.
                        }
                    }
                    if (isset($pzasOcupadas)) {
                        $numeroUsado = ""; //Reinicio la variable.
                        for ($x = 0; $x < count($pzasOcupadas); $x++) {
                            $n_piezaUsada = $pzasOcupadas[$x]->n_pieza; //Obtención del número de pieza ocupada
                            $pzaDividida_Usada = str_split($n_piezaUsada);
                            for ($h = 0; $h < count($pzaDividida_Usada) - 1; $h++) {
                                $numeroUsado .= $pzaDividida_Usada[$h];
                            }
                            array_push($numerosUsados, $numeroUsado);
                            $numeroUsado = "";
                        }
                    }
                    $n_pieza = $pzasEncontradasDesbaste[$i]->n_pieza; //Obtención del número de pieza.
                    $piezaDividida = str_split($n_pieza); //División del número de pieza.
                    for ($j = 0; $j < count($piezaDividida) - 1; $j++) { //Recorro el número de pieza.
                        $numero .= $piezaDividida[$j]; //Obtención del número de pieza.
                    }
                    //Se hace la condicion para saber si el numero de la pieza se encuentra ya usada.
                    if (array_search($numero, $numerosUsados) === false) {
                        if (mb_substr($n_pieza, -1, null, 'UTF-8') == "M") { //Si la pieza es macho, se busca la pieza hembra.
                            $pieza = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Desbaste Exterior')->where('n_pieza', $numero . "H")->where('error', 'Ninguno')->first(); //Busco la pieza hembra.
                        } else {
                            $pieza = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Desbaste Exterior')->where('n_pieza', $numero . "M")->where('error', 'Ninguno')->first(); //Busco la pieza macho.
                        }
                        if (isset($pieza)) {
                            array_push($pzasUtilizar, $numero . "J"); //Guardo el número de pieza.
                            array_push($pzasGuardadas, $pzasEncontradasDesbaste[$i]->n_pieza); //Guardo el número de pieza.
                            array_push($pzasGuardadas, $pieza->n_pieza); //Guardo el número de pieza.
                        }
                    }
                    $numero = "";
                } else {
                    $numeroDiv = str_split($pzasEncontradasDesbaste[$i]->n_pieza);
                    for ($l = 0; $l < count($numeroDiv) - 1; $l++) {
                        $numero .= $numeroDiv[$l];
                    }
                    array_push($pzasGuardadas, $numero . "H");
                    array_push($pzasGuardadas, $numero . "M");
                    $numero = "";
                }
            }
        }
        for ($i = 0; $i < count($pzasEncontradasRevision); $i++) { //Recorro las piezas encontradas.
            if (array_search($pzasEncontradasRevision[$i]->n_pieza, $pzasGuardadas) == false) {
                if ($pzasEncontradasRevision[$i]->error == "Ninguno") {
                    if (isset($pzasUsadas)) {
                        $numeroUsado = "";
                        for ($x = 0; $x < count($pzasUsadas); $x++) {
                            $pzaDividida_Usada = str_split($pzasUsadas[$x]->n_pieza); //División del número de pieza usada.
                            for ($h = 0; $h < count($pzaDividida_Usada) - 1; $h++) { //Recorro el número de pieza usada.
                                $numeroUsado .= $pzaDividida_Usada[$h]; //Obtención del número de pieza usada.
                            }
                            array_push($numerosUsados, $numeroUsado); //Guardo el número de ppieza usada.
                            $numeroUsado = ""; //Reinicio la variable.
                        }
                    }
                    if (isset($pzasOcupadas)) {
                        $numeroUsado = ""; //Reinicio la variable.
                        for ($x = 0; $x < count($pzasOcupadas); $x++) {
                            $n_piezaUsada = $pzasOcupadas[$x]->n_pieza; //Obtención del número de pieza ocupada
                            $pzaDividida_Usada = str_split($n_piezaUsada);
                            for ($h = 0; $h < count($pzaDividida_Usada) - 1; $h++) {
                                $numeroUsado .= $pzaDividida_Usada[$h];
                            }
                            array_push($numerosUsados, $numeroUsado);
                            $numeroUsado = "";
                        }
                    }
                    $n_pieza = $pzasEncontradasRevision[$i]->n_pieza; //Obtención del número de pieza.
                    $piezaDividida = str_split($n_pieza); //División del número de pieza.
                    for ($j = 0; $j < count($piezaDividida) - 1; $j++) { //Recorro el número de pieza.
                        $numero .= $piezaDividida[$j]; //Obtención del número de pieza.
                    }
                    //Se hace la condicion para saber si el numero de la pieza se encuentra ya usada.
                    if (array_search($numero, $numerosUsados) === false) {
                        if (mb_substr($n_pieza, -1, null, 'UTF-8') == "M") { //Si la pieza es macho, se busca la pieza hembra.
                            $pieza = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Revision Laterales')->where('n_pieza', $numero . "H")->where('error', 'Ninguno')->first(); //Busco la pieza hembra.
                        } else {
                            $pieza = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Revision Laterales')->where('n_pieza', $numero . "M")->where('error', 'Ninguno')->first(); //Busco la pieza macho.
                        }
                        if (isset($pieza)) {
                            array_push($pzasUtilizar, $numero . "J"); //Guardo el número de pieza.
                            array_push($pzasGuardadas, $pzasEncontradasRevision[$i]->n_pieza); //Guardo el número de pieza.
                            array_push($pzasGuardadas, $pieza->n_pieza); //Guardo el número de pieza.
                        }
                    }
                    $numero = "";
                }
            } else {
                $numeroDivi = str_split($pzasEncontradasRevision[$i]->n_pieza);
                $numeroComprobar = "";
                for ($p = 0; $p < count($numeroDivi) - 1; $p++) {
                    $numeroComprobar .= $numeroDivi[$p];
                }
                if ($pzasEncontradasRevision[$i]->n_pieza == $numeroComprobar . "H") {
                    $piezaOpuesta = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Revision Laterales')->where('n_pieza', $numeroComprobar . "M")->first(); //Busco la pieza hembra.
                } else {
                    $piezaOpuesta = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Revision Laterales')->where('n_pieza', $numeroComprobar . "H")->first(); //Busco la pieza hembra.
                }
                if (isset($piezaOpuesta) && $piezaOpuesta->error != "Ninguno") {
                    $piezaEliminar = array_search($numeroComprobar . "J", $pzasUtilizar);
                    if (isset($pzasUtilizar[$piezaEliminar])) {
                        unset($pzasUtilizar[$piezaEliminar]);
                        array_push($pzasGuardadas, $numeroComprobar . "H");
                        array_push($pzasGuardadas, $numeroComprobar . "M");
                    }
                }
            }
        }
        return $pzasUtilizar;
    }
}
