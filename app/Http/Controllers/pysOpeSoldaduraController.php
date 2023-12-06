<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\PySOpeSoldadura;
use App\Models\PySOpeSoldadura_cnominal;
use App\Models\PySOpeSoldadura_pza;
use App\Models\PySOpeSoldadura_tolerancia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PySOpeSoldaduraController extends Controller
{
    public function show()
    {
        $ot = Orden_trabajo::all(); //Obtención de todas las ordenes de trabajo.
        return view('processes.pysOpeSoldadura', ['ot' => $ot]);
    }
    public function storeheaderTable(Request $request)
    {
        //Si se obtienen los datos de la OT y la meta, se guardan en variables de sesión.
        if (session('controller')) {
            $meta = Metas::find(session('meta')); //Busco la meta de la OT.
            $operacion = session('operacion');
        } else {
            $meta = Metas::find($request->metaData); //Busco la meta de la OT.
            $operacion = $request->operacion;
        }
        $ot = Orden_trabajo::where('id', $meta->id_ot)->first(); //Busco la OT que se quiere editar.
        $clase = Clase::find($meta->id_clase); //Busco la clase de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.

        $id = "1y2opeSoldadura_" . $clase->nombre . "_" . $ot->id . "_" . $operacion; //Creación de id para tabla Cepillado.

        $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = PySOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $id_proceso = PySOpeSoldadura::where('id_proceso', $id)->first();

        if (isset($request->n_pieza)) {  //Si se obtienen los datos de las piezas, se guardan en la tabla Cepillado_cnominal.
            $id_pieza = $request->n_pieza . $id_proceso->id; //Creación de id para tabla Cepillado_cnominal.
            $piezaExistente = PySOpeSoldadura_pza::where('id_pza', $id_pieza)->first();
            if ($piezaExistente) {
                $piezaExistente->altura = $request->altura;
                $piezaExistente->alturaCandado1 = $request->alturaCandado1;
                $piezaExistente->alturaCandado2 = $request->alturaCandado2;
                $piezaExistente->alturaAsientoObturador1 = $request->alturaAsientoObturador1;
                $piezaExistente->alturaAsientoObturador2 = $request->alturaAsientoObturador2;
                $piezaExistente->profundidadSoldadura1 = $request->profundidadSoldadura1;
                $piezaExistente->profundidadSoldadura2 = $request->profundidadSoldadura2;
                $piezaExistente->pushUp = $request->pushUp;
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

                $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Primera y Segunda Operacion Equipo')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if (!isset($pieza)) {
                    $pieza = new Pieza();
                }
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id; //Llenado de id_proceso para tabla 1ra y 2da opración c_nominal.
                $pieza->n_pieza = $request->n_pieza; //
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina; //Llenado de id_proceso para tabla 1ra y 2da opración c_nominal.
                $pieza->proceso = "Primera y Segunda Operacion Equipo";
                $pieza->error = $piezaExistente->error;
                $pieza->save();

                //Actualizar resultado de la meta
                $pzasCorrectas = PySOpeSoldadura_pza::where('id_meta', $meta->id)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas.
                Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                    'resultado' => $pzasCorrectas->count() / 2,
                ]);
                $meta = Metas::find($meta->id); //Busco la meta de la OT.

                //  //Retornar la pieza siguiente
                $pzaUtilizar = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Cepillado.
                    $pzasCreadas = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.primeraOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'juegos' => count($this->piezaUtilizar($ot->id, $clase, $id)), 'operacion' => $operacion]); //Retorno a vista de Cepillado.
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla desbaste
                    $this->piezaUtilizar($ot->id, $clase, $id);
                }
            }
        } else if (isset($request->n_juegoElegido)) {
            $juegoExiste = PySOpeSoldadura_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->first();
            if (!$juegoExiste) {
                $newPza = new PySOpeSoldadura_pza(); //Creación de objeto para llenar tabla Desbaste.
                $newPza->id_pza = $request->n_juegoElegido . $id_proceso->id;
                $newPza->n_juego = $request->n_juegoElegido; //Llenado de estado para tabla Desbaste.
                $newPza->id_meta = $meta->id; //Llenado de id_meta para tabla Desbaste.
                $newPza->id_proceso = $id_proceso->id; //Llenado de id_proceso para tabla Desbaste.
                $newPza->estado = 1; //Llenado de estado para tabla Desbaste.
                $newPza->save(); //Guardado de datos en la tabla Desbaste.
            }
        } else {
            if ($meta->id_proceso == null && isset($operacion) || !session('clase') && isset($operacion)) {
                $proceso = PySOpeSoldadura::where('id_proceso', $id)->first(); //Busco el proceso de la OT.
                if (!$proceso) {
                    //Llenado de la tabla PySOpeSoldaduraEquipo 
                    $pysSoldadura = new PySOpeSoldadura(); //Creación de objeto para llenar tabla de 1ra y 2da operación equipo
                    $pysSoldadura->id_proceso = $id; //Creación de id para tabla de 1ra y 2da operación equipo.
                    $pysSoldadura->id_clase = $clase->id; //Llenado de id_proceso para tabla de 1ra y 2da operación equipo.
                    $pysSoldadura->operacion = $operacion; //Llenado de id_proceso para tabla de 1ra y 2da operación equipo.
                    $pysSoldadura->id_ot = $ot->id; //Llenado de id_proceso para tabla de 1ra y 2da operación equipo.
                    $pysSoldadura->save(); //Guardado de datos en la tabla de 1ra y 2da operación equipo.
                    $meta->id_proceso = $pysSoldadura->id;
                } else {
                    $meta->id_proceso = $proceso->id;
                }
                $meta->save();
            }
        }
        $id_proceso = PySOpeSoldadura::where('id_proceso', $id)->first();
        if ($id_proceso !== "[]") {
            $pzasCreadas = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
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

            $pzasCorrectas = PySOpeSoldadura_pza::where('id_meta', $meta->id)->where('correcto', 1)->get();
            if (isset($pzasCorrectas)) { //Si existen piezas correctas, se actualiza el resultado de la meta.
                $meta->resultado = count($pzasCorrectas); //Actualización de datos en tabla Metas.
            } else {
                $meta->resultado = 0; //Actualización de los datos en la tabla metas.
            }
            $meta->save(); //Guardado de datos en la tabla Metas.
            //
            if (isset($cNominal) && isset($tolerancia)) {
                $pzaUtilizar = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                    $piezasVacias = PySOpeSoldadura_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
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
                        $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase, $id); //Llamado a función para obtener las piezas disponibles.
                    }
                }
                if (isset($pzasUtilizar)) { //Si no se encontro una pieza para utilizar, se crea una nueva pieza.
                    return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar), 'operacion' => $operacion]); //Retorno a vista de Cepillado.
                } else {
                    return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'juegos' => count($this->piezaUtilizar($ot->id, $clase, $id)), 'operacion' => $operacion])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
                }
            } else {
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase, $id); //Llamado a función para obtener las piezas disponibles.
                return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'juegos' => count($pzasUtilizar), 'operacion' => $operacion])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        }
    }
    public function compararDatosPieza($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->altura > ($cNominal->altura + $tolerancia->altura) || $pieza->altura < ($cNominal->altura - $tolerancia->altura) || $pieza->alturaCandado1 > ($cNominal->alturaCandado1 + $tolerancia->alturaCandado1) || $pieza->alturaCandado1 < ($cNominal->alturaCandado1 - $tolerancia->alturaCandado1) || $pieza->alturaCandado2 > ($cNominal->alturaCandado2 + $tolerancia->alturaCandado2) || $pieza->alturaCandado2 < ($cNominal->alturaCandado2 - $tolerancia->alturaCandado2) || $pieza->alturaAsientoObturador1 > ($cNominal->alturaAsientoObturador1 + $tolerancia->alturaAsientoObturador1) || $pieza->alturaAsientoObturador1 < ($cNominal->alturaAsientoObturador1 - $tolerancia->alturaAsientoObturador1) || $pieza->alturaAsientoObturador2 > ($cNominal->alturaAsientoObturador2 + $tolerancia->alturaAsientoObturador2) || $pieza->alturaAsientoObturador2 < ($cNominal->alturaAsientoObturador2 - $tolerancia->alturaAsientoObturador2) || $pieza->profundidadSoldadura1  > ($cNominal->profundidadSoldadura1  + $tolerancia->profundidadSoldadura1) || $pieza->profundidadSoldadura1 < ($cNominal->profundidadSoldadura1 - $tolerancia->profundidadSoldadura1) || $pieza->profundidadSoldadura2 > ($cNominal->profundidadSoldadura2  + $tolerancia->profundidadSoldadura2) || $pieza->profundidadSoldadura2 < ($cNominal->profundidadSoldadura2 - $tolerancia->profundidadSoldadura2) || $pieza->pushUp > ($cNominal->pushUp + $tolerancia->pushUp) || $pieza->pushUp < ($cNominal->pushUp - $tolerancia->pushUp)) {
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
        $id = "1y2opeSoldadura_" . $clase->nombre . "_" . $ot->id . "_" . $request->operacion; //Creación de id para tabla Cepillado.
        $id_proceso = PySOpeSoldadura::where('id_proceso', $id)->first();;
        $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = PySOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $pzasCreadas = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
        $pzaUtilizar = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        if (isset($request->n_pieza)) { 
            for ($i = 0; $i < count($request->n_pieza); $i++) {
                $id_pieza = $request->n_pieza[$i] . $id_proceso->id; //Creación de id para tabla Desbaste_cnominal.
                $piezaExistente = PySOpeSoldadura_pza::where('id_pza', $id_pieza)->first();
                if ($piezaExistente) {
                    $piezaExistente->altura = $request->altura[$i];
                    $piezaExistente->alturaCandado1 = $request->alturaCandado1[$i];
                    $piezaExistente->alturaCandado2 = $request->alturaCandado2[$i];
                    $piezaExistente->alturaAsientoObturador1 = $request->alturaAsientoObturador1[$i];
                    $piezaExistente->alturaAsientoObturador2 = $request->alturaAsientoObturador2[$i];
                    $piezaExistente->profundidadSoldadura1 = $request->profundidadSoldadura1[$i];
                    $piezaExistente->profundidadSoldadura2 = $request->profundidadSoldadura2[$i];
                    $piezaExistente->pushUp = $request->pushUp[$i];
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



                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Primera y Segunda Operacion Equipo')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if (!isset($pieza)) {
                        $pieza = new Pieza(); //Creación del obejeto para llenar la tabla pieza.
                    }
                    $pieza->id_clase = $clase->id; //Lenado de id_clase para la tabla pieza.
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $piezaExistente->n_juego;
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Primera y Segunda Operacion Equipo";
                    $pieza->error = $piezaExistente->error;
                    $pieza->save();
                }
            }
            $pzasCorrectas = PySOpeSoldadura_pza::where('id_meta', $meta->id)->where('correcto', 1)->get();
            if (isset($pzasCorrectas)) { //Si existen piezas correctas, se actualiza el resultado de la meta.
                $meta->resultado = count($pzasCorrectas); //Actualización de datos en tabla Metas.
            } else {
                $meta->resultado = 0; //Actualización de los datos en la tabla metas.
            }
            $meta->save(); //Guardado de datos en la tabla Metas.
            //Retornar la pieza siguiente
            $pzaUtilizar = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $piezasVacias = PySOpeSoldadura_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
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
                    $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase, $id); //Llamado a función para obtener las piezas disponibles.
                }
            }
            $pzasCreadas = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = PySOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar), 'operacion' => $request->operacion]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase, $id); //Llamado a función para obtener las piezas disponibles.
                return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'juegos' => count($pzasUtilizar), 'operacion' => $request->operacion])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.pysOpeSoldadura', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'juegos' => count($this->piezaUtilizar($ot->id, $clase, $id)), 'operacion' => $request->operacion]); //Retorno la vista de cepillado.
                    }
                }
            }
            $pzaUtilizar = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $piezasVacias = PySOpeSoldadura_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
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
                    $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase, $id); //Llamado a función para obtener las piezas disponibles.
                }
            }
            $pzasCreadas = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = PySOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar), 'operacion' => $request->operacion]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase, $id); //Llamado a función para obtener las piezas disponibles.
                return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'juegos' => count($pzasUtilizar), 'operacion' => $request->operacion])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        }
    }

    public function piezaUtilizar($ot, $clase, $id) //Función para obtener la pieza a utilizar.
    {
        //Obtener las piezas que esten terminadas y correctas en la tabla cepillado para despues comparar cada una con su consecuente y asi armar los juegos
        $pzasUtilizar = array();
        $pzasGuardadas = array(); //Creación de array para guardar los números de pieza.
        $numero = ""; //Creación de variable para guardar el número de pieza.
        $pzasEncontradasSegunOpe = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Segunda Operacion Soldadura')->get(); //Obtención de todas las piezas creadas.
        $pzasEncontradasProceso = PySOpeSoldadura_pza::where('id_proceso', $id)->first();
        if ($pzasEncontradasProceso->operacion == 1) {

        $proceso = PySOpeSoldadura::where('id_proceso', $id)->first(); //Busco el proceso de la OT.

        $pzasOcupadas = PySOpeSoldadura_pza::where('id_proceso', $proceso->id)->where('estado', 1)->get(); //Obtención de todas las piezas creadas.

        if ($proceso) {
            $pzasUsadas = PySOpeSoldadura_pza::where('id_proceso', $id)->where('estado', 2)->get();
            // $pzasUsadas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Primera y Segunda Operacion Equipo')->get(); //Obtención de todas las piezas creadas.
        }
        for ($i = 0; $i < count($pzasEncontradasSegunOpe); $i++) { //Recorro las piezas encontradas.
            if (array_search($pzasEncontradasSegunOpe[$i]->n_pieza, $pzasGuardadas) == false) {
                if ($pzasEncontradasSegunOpe[$i]->error == "Ninguno") {
                    $numerosUsados = array();
                    if (isset($pzasUsadas)) {
                        $numeroUsado = ""; //Reinicio la variable.
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
                    $n_pieza = $pzasEncontradasSegunOpe[$i]->n_pieza; //Obtención del número de pieza.
                    $piezaDividida = str_split($n_pieza); //División del número de pieza.
                    for ($j = 0; $j < count($piezaDividida) - 1; $j++) { //Recorro el número de pieza.
                        $numero .= $piezaDividida[$j]; //Obtención del número de pieza.
                    }
                    //Se hace la condicion para saber si el numero de la pieza se encuentra ya usada.
                    if (array_search($numero, $numerosUsados) === false) { //Si el número de pieza no se encuentra en el array de piezas usadas, se guarda.
                        if (mb_substr($n_pieza, -1, null, 'UTF-8') == "M") { //Si la pieza es macho, se busca la pieza hembra.
                            $pieza = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Segunda Operacion Soldadura')->where('n_pieza', $numero . "H")->where('error', 'Ninguno')->first(); //Busco la pieza hembra.
                        } else {
                            $pieza = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Segunda Operacion Soldadura')->where('n_pieza', $numero . "M")->where('error', 'Ninguno')->first(); //Busco la pieza macho.
                        }
                        if (isset($pieza)) {
                            array_push($pzasUtilizar, $numero . "J"); //Guardo el número de pieza.
                            array_push($pzasGuardadas, $pzasEncontradasSegunOpe[$i]->n_pieza); //Guardo el número de pieza.
                            array_push($pzasGuardadas, $pieza->n_pieza); //Guardo el número de pieza.
                        }
                    }
                    $numero = "";
                } else {
                    $numeroDiv = str_split($pzasEncontradasSegunOpe[$i]->n_pieza);
                    for ($l = 0; $l < count($numeroDiv) - 1; $l++) {
                        $numero .= $numeroDiv[$l];
                    }
                    array_push($pzasGuardadas, $numero . "H");
                    array_push($pzasGuardadas, $numero . "M");
                    $numero = "";
                }
            }
        }
        return $pzasUtilizar;
    }
}
