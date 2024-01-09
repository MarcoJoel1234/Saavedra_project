<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\OffSet;
use App\Models\OffSet_cnominal;
use App\Models\OffSet_pza;
use App\Models\OffSet_tolerancia;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OffSetController extends Controller
{
    public function show()
    {
        $ot = Orden_trabajo::all(); //Obtención de todas las ordenes de trabajo.
        if (count($ot) != 0) {
            $oTrabajo = array(); //Declara arreglo para guardar las ordenes de trabajo disponibles en Offset.
            //Recorre todas las ordenes de trabajo.
            foreach ($ot as $ot) {
                $contador = 0; //Contador para verificar que existan clases que pasaran por Offset
                $clases = Clase::where('id_ot', $ot->id)->get();
                //Recorre todas las clases registradas en la orden de trabajo.
                foreach ($clases as $clase) {
                    $proceso = Procesos::where('id_clase', $clase->id)->first(); //Obtención del proceso de la clase.
                    if ($proceso) {
                        if ($proceso->offSet) { //Si existen maquinas en Copiado de esa clase, se almacena en el arreglo que se pasara a la vista
                            $contador++;
                        }
                    }
                }
                //Si hay clases que pasaran por Offset, se almacena la orden de trabajo en el arreglo.
                if ($contador != 0) {
                    array_push($oTrabajo, $ot);
                }
            }
            if (count($oTrabajo) != 0) {
                return view('processes.offSet', ['ot' => $oTrabajo]); //Retorno a vista de Offset
            }
            //Se retorna a la vista de Offset con las ordenes de trabajo que tienen clases que pasaran por Offset
            return view('processes.offSet', ['ot']); //Retorno a vista de Offset
        }
        return view('processes.offSet');
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
        $id = "offSet_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla OffSet
        $cNominal = OffSet_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = OffSet_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $id_proceso = OffSet::where('id_proceso', $id)->first();

        if (isset($request->n_pieza)) {  //Si se obtienen los datos de las piezas, se guardan en la tabla OffSet_cnominal.
            $id_pieza = $request->n_pieza . $id_proceso->id;
            $piezaExistente = OffSet_pza::where('id_pza', $id_pieza)->first();
            if ($piezaExistente) {
                $piezaExistente->anchoRanura = $request->anchoRanura;
                $piezaExistente->profuTaconHembra = $request->profuTaconHembra;
                $piezaExistente->profuTaconMacho = $request->profuTaconMacho;
                $piezaExistente->simetriaHembra = $request->simetriaHembra;
                $piezaExistente->simetriaMacho = $request->simetriaMacho;
                $piezaExistente->anchoTacon = $request->anchoTacon;
                $piezaExistente->barrenoLateralHembra = $request->barrenoLateralHembra;
                $piezaExistente->barrenoLateralMacho = $request->barrenoLateralMacho;
                $piezaExistente->alturaTaconInicial = $request->alturaTaconInicial;
                $piezaExistente->alturaTaconIntermedia = $request->alturaTaconIntermedia;
                $piezaExistente->observaciones = $request->observaciones;
                $piezaExistente->error = $request->error;
                $piezaExistente->estado = 2;
                $piezaExistente->save();

                //Actualiza el estado correcto de la pieza en cilindrado.
                if ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error == 0) {
                    $piezaExistente->error = 'Maquinado';
                } else if (($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 1 && $request->error == 'Fundicion')) {
                    $piezaExistente->error = $request->error;
                } else {
                    $piezaExistente->error = 'Ninguno';
                }

                $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Off Set')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if (!isset($pieza)) {
                    $pieza = new Pieza();
                }
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id;
                $pieza->n_pieza = $request->n_pieza;
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina;
                $pieza->proceso = "Off Set";
                $pieza->error = $piezaExistente->error;
                $pieza->save();

                //Actualizar resultado de la meta
                $pzasCorrectas = OffSet_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
                Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                    'resultado' => $pzasCorrectas->count(),
                ]);
                $meta = Metas::find($meta->id); //Busco la meta de la OT.
                //  //Retornar la pieza siguiente
                $pzaUtilizar = OffSet_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Offset
                    $pzasCreadas = OffSet_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.offSet', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))]); //Retorno a vista de Copiado
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla Offset
                    $this->piezaUtilizar($ot->id, $clase);
                }
            }
        } else if (isset($request->n_juegoElegido)) {
            $juegoExiste = OffSet_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->first();
            if (!$juegoExiste) {
                $newPza = new OffSet_pza(); //Creación de objeto para llenar tabla Offset
                $newPza->id_pza = $request->n_juegoElegido . $id_proceso->id; //Creación de id para tabla Offset
                $newPza->id_meta = $meta->id; //Llenado de id_meta para tabla Offset
                $newPza->id_proceso = $id_proceso->id; //Llenado de id_proceso para tabla Offset
                $newPza->estado = 1; //Llenado de estado para tabla Offset
                $newPza->n_juego = $request->n_juegoElegido; //Llenado de estado para tabla Offset
                $newPza->save(); //Guardado de datos en la tabla Offset
            }
        } else {
            $proceso = OffSet::where('id_proceso', $id)->first(); //Busco el proceso de la OT.
            if (!$proceso) {
                //Llenado de la tabla Offset
                $copiado = new OffSet(); //Creación de objeto para llenar tabla Offset
                $copiado->id_proceso = $id; //Creación de id para tabla Offset
                $copiado->id_ot = $ot->id; //Llenado de id_proceso para tabla Offset
                $copiado->save(); //Guardado de datos en la tabla Offset
            }
        }
        $id_proceso = OffSet::where('id_proceso', $id)->first();
        if ($id_proceso !== "[]") {
            $pzasCreadas = OffSet_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            for ($i = 0; $i < count($pzasCreadas); $i++) { //Recorro las piezas creadas.
                //Actualiza el estado correcto de la pieza.
                if ($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia) == 0 && $request->error == 0) {
                    $pzasCreadas[$i]->error = 'Maquinado';
                } else if (($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia) == 0 && $request->error == 'Fundicion') || ($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia) == 1 && $request->error == 'Fundicion')) {
                    $pzasCreadas[$i]->error = $request->error;
                } else {
                    $pzasCreadas[$i]->error = 'Ninguno';
                }
                $pzasCreadas[$i]->save();
            }

            //Actualizar resultado de la meta
            $pzasMeta = OffSet_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.

            if (isset($cNominal) && isset($tolerancia)) {
                $pzaUtilizar = OffSet_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Offset
                    $piezasVacias = OffSet_pza::where('error', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                    if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                        for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                            $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                            if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                                $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_COffset.
                                $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Offset.
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
                    return view('processes.offSet', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de OffSet
                } else {
                    return view('processes.offSet', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de OffSet
                }
            } else {
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.offSet', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de OffSet
            }
        }
    }
    public function compararDatosPieza($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->anchoRanura > ($cNominal->anchoRanura + $tolerancia->anchoRanura) || $pieza->anchoRanura < ($cNominal->anchoRanura - $tolerancia->anchoRanura) || $pieza->profuTaconHembra > ($cNominal->profuTaconHembra + $tolerancia->profuTaconHembra) || $pieza->profuTaconHembra < ($cNominal->profuTaconHembra - $tolerancia->profuTaconHembra) || $pieza->profuTaconMacho > ($cNominal->profuTaconMacho + $tolerancia->profuTaconMacho) || $pieza->profuTaconMacho < ($cNominal->profuTaconMacho - $tolerancia->profuTaconMacho) || $pieza->simetriaHembra > ($cNominal->simetriaHembra + $tolerancia->simetriaHembra) || $pieza->simetriaHembra < ($cNominal->simetriaHembra - $tolerancia->simetriaHembra) || $pieza->simetriaMacho > ($cNominal->simetriaMacho + $tolerancia->simetriaMacho) || $pieza->simetriaMacho < ($cNominal->simetriaMacho - $tolerancia->simetriaMacho) || $pieza->anchoTacon > ($cNominal->anchoTacon + $tolerancia->anchoTacon) || $pieza->anchoTacon < ($cNominal->anchoTacon - $tolerancia->anchoTacon) || $pieza->barrenoLateralHembra > ($cNominal->barrenoLateralHembra + $tolerancia->barrenoLateralHembra) || $pieza->barrenoLateralHembra < ($cNominal->barrenoLateralHembra - $tolerancia->barrenoLateralHembra) || $pieza->barrenoLateralMacho > ($cNominal->barrenoLateralMacho + $tolerancia->barrenoLateralMacho) || $pieza->barrenoLateralMacho < ($cNominal->barrenoLateralMacho - $tolerancia->barrenoLateralMacho) || $pieza->alturaTaconInicial > ($cNominal->alturaTaconInicial + $tolerancia->alturaTaconInicial) || $pieza->alturaTaconInicial < ($cNominal->alturaTaconInicial - $tolerancia->alturaTaconInicial) || $pieza->alturaTaconIntermedia > ($cNominal->alturaTaconIntermedia + $tolerancia->alturaTaconIntermedia) || $pieza->alturaTaconIntermedia < ($cNominal->alturaTaconIntermedia - $tolerancia->alturaTaconIntermedia)) {
            return 0; //Retorno de datos. //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        }
            return 1;
    }
    public function edit(Request $request)
    {
        $meta = Metas::find($request->metaData); //Busco la meta de la OT.
        $ot = Orden_trabajo::find($meta->id_ot); //Obtención de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $clase = Clase::find($meta->id_clase); //Busco la clase de la OT.
        $id = "offSet_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla OffSet
        $id_proceso = OffSet::where('id_proceso', $id)->first();
        $cNominal = OffSet_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = OffSet_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $pzasCreadas = OffSet_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
        $pzaUtilizar = OffSet_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        if (isset($request->n_pieza)) { //Si se obtienen los datos de las piezas, se guardan en la tabla OffSet_cnominal.
            for ($i = 0; $i < count($request->n_pieza); $i++) {
                $id_pieza = $request->n_pieza[$i] . $id_proceso->id; //Creación de id para tabla OffSet_cnominal.
                $piezaExistente = OffSet_pza::where('id_pza', $id_pieza)->first();
                if ($piezaExistente) {
                    $piezaExistente->anchoRanura = $request->anchoRanura[$i];
                    $piezaExistente->profuTaconHembra = $request->profuTaconHembra[$i];
                    $piezaExistente->profuTaconMacho = $request->profuTaconMacho[$i];
                    $piezaExistente->simetriaHembra = $request->simetriaHembra[$i];
                    $piezaExistente->simetriaMacho = $request->simetriaMacho[$i];
                    $piezaExistente->anchoTacon = $request->anchoTacon[$i];
                    $piezaExistente->barrenoLateralHembra = $request->barrenoLateralHembra[$i];
                    $piezaExistente->barrenoLateralMacho = $request->barrenoLateralMacho[$i];
                    $piezaExistente->alturaTaconInicial = $request->alturaTaconInicial[$i];
                    $piezaExistente->alturaTaconIntermedia = $request->alturaTaconIntermedia[$i];
                    if (isset($request->observaciones[$i])) { //Si se obtienen los datos de las piezas, se guardan en la tabla OffSet_cnominal.
                        $piezaExistente->observaciones = $request->observaciones[$i];  //Llenado de observaciones para tabla OffSet_cnominal.
                    }
                    $piezaExistente->save(); //Guardado de datos en la tabla Pza_OffSet

                    //Acrualiza el estado correcto de la pieza.
                    if ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && ($request->error[$i] == "Ninguno" || $request->error[$i] == "Maquinado")) {
                        $piezaExistente->error = 'Maquinado';
                    } else if (($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error[$i] == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 1 && $request->error[$i] == 'Fundicion')) {
                        $piezaExistente->error = $request->error[$i];
                    } else {
                        $piezaExistente->error = 'Ninguno';
                    }
                    $piezaExistente->save();

                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Off Set')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if (!isset($pieza)) {
                        $pieza = new Pieza();
                    }
                    $pieza->id_clase = $clase->id;
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $piezaExistente->n_juego;
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Off Set";
                    $pieza->error = $piezaExistente->error;
                    $pieza->save();
                }
            }
            //Actualizar resultado de la meta
            $pzasMeta = OffSet_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.
            //Retornar la pieza siguiente
            $pzaUtilizar = OffSet_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de OffSet
                $piezasVacias = OffSet_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_OffSet.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_OffSet.
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
            $pzasCreadas = OffSet_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = OffSet_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = OffSet_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de OffSet
                return view('processes.offSet', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de OffSet
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de OffSet
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.offSet', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Copiado
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.offSet', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))]); //Retorno la vista de OffSet
                    }
                }
            }
            $pzaUtilizar = OffSet_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de OffSet
                $piezasVacias = OffSet_pza::where('error', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_OffSet.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_OffSet.
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
            $pzasCreadas = OffSet_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = OffSet_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = OffSet_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de OffSet
                return view('processes.offSet', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de OffSet
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de OffSet
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.offSet', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de OffSet
            }
        }
    }

    public function piezaUtilizar($ot, $clase) //Función para obtener la pieza a utilizar.
    {
        $pzasUtilizar = array();
        $pzasGuardadas = array();
        $procesos = Procesos::where('id_clase', $clase->id)->first();

        //Obtener las piezas que esten terminadas y correctas en la tabla Cavidades para despúes comparar cada una con su consecuente y asi armar los juegos
        $id_proceso = "offSet_" . $clase->nombre . "_" . $ot;
        $proceso = OffSet::where('id_proceso', $id_proceso)->first();
        $pzasOcupadas = OffSet_pza::where('id_proceso', $proceso->id)->where('estado', 1)->get(); //Obtención de todas las piezas creadas.
        if ($proceso) {
            $pzasUsadas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Off Set')->get(); //Obtención de todas las piezas creadas en OffSet
        }

        if ($procesos->copiado != 0) {
            //Obtener las piezas solamente en el proceso de OffSet
            $pzasEncontradas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Copiado')->where('error', 'Ninguno')->get();
            $this->piezasEncontradas($pzasEncontradas, $pzasUtilizar, $pzasGuardadas, $pzasUsadas, $pzasOcupadas);
        }
        return $pzasUtilizar;
    }
    public function piezasEncontradas($pzasEncontradas, &$pzasUtilizar, &$pzasGuardadas, $pzasUsadas, $pzasOcupadas)
    {
        $numerosUsados = array();
        if (count($pzasUsadas) > 0) {
            for ($x = 0; $x < count($pzasUsadas); $x++) {
                array_push($numerosUsados, $pzasUsadas[$x]->n_pieza); //Guardo el número de pieza usada.
            } //Recorro las piezas ocupadas en OffSet
        }
        if (count($pzasOcupadas) > 0) {
            for ($x = 0; $x < count($pzasOcupadas); $x++) {
                array_push($numerosUsados, $pzasOcupadas[$x]->n_juego); //Guardo el número de pieza usada.
            }
        }
        for ($i = 0; $i < count($pzasEncontradas); $i++) { //Recorro las piezas encontradas de OffSet
            if (array_search($pzasEncontradas[$i]->n_pieza, $pzasGuardadas) == false) {
                // if ($pzasEncontradas[$i]->error == "Ninguno") {
                //Se hace la condicion para saber si el numero de la pieza se encuentra ya usada.
                if (array_search($pzasEncontradas[$i]->n_pieza, $numerosUsados) === false) {
                    array_push($pzasUtilizar, $pzasEncontradas[$i]->n_pieza); //Guardo el número de pieza.
                    array_push($pzasGuardadas, $pzasEncontradas[$i]->n_pieza); //Guardo el número de pieza.
                }
            }
        }
    }
}
