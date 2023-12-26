<?php

namespace App\Http\Controllers;

use App\Models\Cavidades;
use App\Models\Cavidades_cnominal;
use App\Models\Cavidades_pza;
use App\Models\Cavidades_tolerancia;
use App\Models\Clase;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CavidadesController extends Controller
{
    public function show()
    {
        $ot = Orden_trabajo::all(); //Obtención de todas las ordenes de trabajo.
        if (count($ot) != 0) {
            $oTrabajo = array(); //Declara arreglo para guardar las ordenes de trabajo disponibles en Cavidades
            //Recorre todas las ordenes de trabajo.
            foreach ($ot as $ot) {
                $contador = 0; //Contador para verificar que existan clases que pasaran por Cavidades
                $clases = Clase::where('id_ot', $ot->id)->get();
                //Recorre todas las clases registradas en la orden de trabajo.
                foreach ($clases as $clase) {
                    $proceso = Procesos::where('id_clase', $clase->id)->first(); //Obtención del proceso de la clase.
                    if ($proceso) {
                        if ($proceso->cavidades) { //Si existen maquinas en cepillado de esa clase, se almacena en el arreglo que se pasara a la vista
                            $contador++;
                        }
                    }
                }
                //Si hay clases que pasaran por Cavidades, se almacena la orden de trabajo en el arreglo.
                if ($contador != 0) {
                    array_push($oTrabajo, $ot);
                }
            }
            if (count($oTrabajo) != 0) {
                return view('processes.cavidades', ['ot' => $oTrabajo]); //Retorno a vista de Cavidades
            }
            //Se retorna a la vista de Cepillado con las ordenes de trabajo que tienen clases que pasaran por Cavidades
            return view('processes.cavidades', ['ot']); //Retorno a vista de Cavidades
        }
        return view('processes.cavidades');
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
        $id = "cavidades_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Cavidades
        $cNominal = Cavidades_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = Cavidades_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $id_proceso = Cavidades::where('id_proceso', $id)->first();

        if (isset($request->n_pieza)) {  //Si se obtienen los datos de las piezas, se guardan en la tabla Cavidades_cnominal.
            $id_pieza = $request->n_pieza . $id_proceso->id; //Creación de id para tabla Cavidades_cnominal.
            $piezaExistente = Cavidades_pza::where('id_pza', $id_pieza)->first();
            if ($piezaExistente) {
                $piezaExistente->profundidad1 = $request->profundidad1;
                $piezaExistente->diametro1 = $request->diametro1;
                $piezaExistente->profundidad2 = $request->profundidad2;
                $piezaExistente->diametro2 = $request->diametro2;
                $piezaExistente->profundidad3 = $request->profundidad3;
                $piezaExistente->diametro3 = $request->diametro3;
                $piezaExistente->acetatoBM = $request->acetatoBM;
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

                $pieza = Pieza::where('n_pieza', $piezaExistente->n_pieza)->where('proceso', 'Cavidades')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if (!isset($pieza)) {
                    $pieza = new Pieza();
                }
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id;
                $pieza->n_pieza = $request->n_pieza;
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina;
                $pieza->proceso = "Cavidades";
                $pieza->error = $piezaExistente->error;
                $pieza->save();

                //Actualizar resultado de la meta
                $pzasCorrectas = Cavidades_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
                Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                    'resultado' => $pzasCorrectas->count(),
                ]);
                $meta = Metas::find($meta->id); //Busco la meta de la OT.
                //  //Retornar la pieza siguiente
                $pzaUtilizar = Cavidades_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Cavidades
                    $pzasCreadas = Cavidades_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.cavidades', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))]); //Retorno a vista de Cepillado.
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla Cavidades
                    $this->piezaUtilizar($ot->id, $clase);
                }
            }
        } else if (isset($request->n_juegoElegido)) {
            $juegoExiste = Cavidades_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->first();
            if (!$juegoExiste) {
                $newPza = new Cavidades_pza(); //Creación de objeto para llenar tabla Cavidades
                $newPza->id_pza = $request->n_juegoElegido . $id_proceso->id; //Creación de id para tabla Cavidades.
                $newPza->id_meta = $meta->id; //Llenado de id_meta para tabla Cavidades
                $newPza->id_proceso = $id_proceso->id; //Llenado de id_proceso para tabla Cavidades
                $newPza->estado = 1; //Llenado de estado para tabla Cavidades
                $newPza->n_juego = $request->n_juegoElegido; //Llenado de estado para tabla Cavidades
                $newPza->save(); //Guardado de datos en la tabla Cavidades
            }
        } else {
            $proceso = Cavidades::where('id_proceso', $id)->first(); //Busco el proceso de la OT.
            if (!$proceso) {
                //Llenado de la tabla Rectificado
                $soldadura = new Cavidades(); //Creación de objeto para llenar tabla Cavidades
                $soldadura->id_proceso = $id; //Creación de id para tabla Cavidades
                $soldadura->id_ot = $ot->id; //Llenado de id_proceso para tabla Cavidades
                $soldadura->save(); //Guardado de datos en la tabla Cavidades
            }
        }
        $id_proceso = Cavidades::where('id_proceso', $id)->first();
        if ($id_proceso !== "[]") {
            $pzasCreadas = Cavidades_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
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

            //Actualizar resultado de la meta
            $pzasMeta = Cavidades_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.

            if (isset($cNominal) && isset($tolerancia)) {
                $pzaUtilizar = Cavidades_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Cavidades.
                    $piezasVacias = Cavidades_pza::where('error', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                    if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                        for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                            $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                            if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                                $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Cavidades.
                                $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Cavidades.
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
                    return view('processes.cavidades', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de Cavidades
                } else {
                    return view('processes.cavidades', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cavidades
                }
            } else {
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.cavidades', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cavidades
            }
        }
    }
    public function compararDatosPieza($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->diametro_mordaza > ($cNominal->diametro_mordaza + $tolerancia->diametro_mordaza1) || $pieza->diametro_mordaza < ($cNominal->diametro_mordaza - $tolerancia->diametro_mordaza2) || $pieza->diametro_ceja > ($cNominal->diametro_ceja + $tolerancia->diametro_ceja1) || $pieza->diametro_ceja < ($cNominal->diametro_ceja - $tolerancia->diametro_ceja2) || $pieza->diametro_sufridera > ($cNominal->diametro_sufridera + $tolerancia->diametro_sufridera1) || $pieza->diametro_sufridera < ($cNominal->diametro_sufridera - $tolerancia->diametro_sufridera2) || $pieza->altura_mordaza > ($cNominal->altura_mordaza + $tolerancia->altura_mordaza1) || $pieza->altura_mordaza < ($cNominal->altura_mordaza - $tolerancia->altura_mordaza2) || $pieza->altura_ceja > ($cNominal->altura_ceja + $tolerancia->altura_ceja1) || $pieza->altura_ceja < ($cNominal->altura_ceja - $tolerancia->altura_ceja2) || $pieza->altura_sufridera > ($cNominal->altura_sufridera + $tolerancia->altura_sufridera1) || $pieza->altura_sufridera < ($cNominal->altura_sufridera - $tolerancia->altura_sufridera2) || $pieza->diametro_boca > ($cNominal->diametro_boca + $tolerancia->diametro_boca1) || $pieza->diametro_boca < ($cNominal->diametro_boca - $tolerancia->diametro_boca2) || $pieza->diametro_asiento_corona > ($cNominal->diametro_asiento_corona + $tolerancia->diametro_asiento_corona1) || $pieza->diametro_asiento_corona < ($cNominal->diametro_asiento_corona - $tolerancia->diametro_asiento_corona2) || $pieza->diametro_llanta > ($cNominal->diametro_llanta + $tolerancia->diametro_llanta1) || $pieza->diametro_llanta < ($cNominal->diametro_llanta - $tolerancia->diametro_llanta2) || $pieza->diametro_caja_corona > ($cNominal->diametro_caja_corona + $tolerancia->diametro_caja_corona1) || $pieza->diametro_caja_corona < ($cNominal->diametro_caja_corona - $tolerancia->diametro_caja_corona2) || $pieza->profundidad_corona > ($cNominal->profundidad_corona + $tolerancia->profundidad_corona1) || $pieza->profundidad_corona < ($cNominal->profundidad_corona - $tolerancia->profundidad_corona2) || $pieza->angulo_30 > ($cNominal->angulo_30 + $tolerancia->angulo_301) || $pieza->angulo_30 < ($cNominal->angulo_30 - $tolerancia->angulo_302) || $pieza->profundidad_caja_corona > ($cNominal->profundidad_caja_corona + $tolerancia->profundidad_caja_corona1) || $pieza->profundidad_caja_corona < ($cNominal->profundidad_caja_corona - $tolerancia->profundidad_caja_corona2) || $pieza->simetria > ($cNominal->simetria + $tolerancia->simetria1) || $pieza->simetria < ($cNominal->simetria - $tolerancia->simetria2)) {
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
        $id = "cavidades_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Cavidades
        $id_proceso = Cavidades::where('id_proceso', $id)->first();;
        $cNominal = Cavidades_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = Cavidades_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $pzasCreadas = Cavidades_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
        $pzaUtilizar = Cavidades_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        if (isset($request->n_pieza)) { //Si se obtienen los datos de las piezas, se guardan en la tabla Cavidades_cnominal.
            for ($i = 0; $i < count($request->n_pieza); $i++) {
                $id_pieza = $request->n_pieza[$i] . $id_proceso->id; //Creación de id para tabla Cavidades_cnominal.
                $piezaExistente = Cavidades_pza::where('id_pza', $id_pieza)->first();
                if ($piezaExistente) {
                    $piezaExistente->profundidad1 = $request->profundidad1[$i];
                    $piezaExistente->diametro1 = $request->diametro1[$i];
                    $piezaExistente->profundidad2 = $request->profundidad2[$i];
                    $piezaExistente->diametro2 = $request->diametro2[$i];
                    $piezaExistente->profundidad3 = $request->profundidad3[$i];
                    $piezaExistente->diametro3 = $request->diametro3[$i];
                    $piezaExistente->acetatoBM = $request->acetatoBM[$i];
                    $piezaExistente->save();
                    if (isset($request->observaciones[$i])) { //Si se obtienen los datos de las piezas, se guardan en la tabla Cavidades_cnominal.
                        $piezaExistente->observaciones = $request->observaciones[$i];  //Llenado de observaciones para tabla Cavidades_cnominal.
                    }
                    $piezaExistente->save(); //Guardado de datos en la tabla Pza_Cavidades

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

                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Cavidades')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if (!isset($pieza)) {
                        $pieza = new Pieza(); //Creación del obejeto para llenar la tabla pieza.
                    }
                    $pieza->id_clase = $clase->id; //Lenado de id_clase para la tabla pieza.
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $piezaExistente->n_juego;
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Cavidades";
                    $pieza->error = $piezaExistente->error;
                    $pieza->save();
                }
            }
            //Actualizar resultado de la meta
            $pzasMeta = Cavidades_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.
            //Retornar la pieza siguiente
            $pzaUtilizar = Cavidades_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Cavidades
                $piezasVacias = Cavidades_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Cavidades.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Cavidades.
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
            $pzasCreadas = Cavidades_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = Cavidades_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = Cavidades_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Cavidades.
                return view('processes.cavidades', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Cavidades.
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.cavidades', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.cavidades', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))]); //Retorno la vista de cepillado.
                    }
                }
            }
            $pzaUtilizar = Cavidades_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Cavidades
                $piezasVacias = Cavidades_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Cavidades.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Cavidades.
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
            $pzasCreadas = Cavidades_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = Cavidades_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = Cavidades_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                return view('processes.cavidades', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.cavidades', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        }
    }

    public function piezaUtilizar($ot, $clase) //Función para obtener la pieza a utilizar.
    {
        $pzasUtilizar = array();
        $pzasGuardadas = array();
        $procesos = Procesos::where('id_clase', $clase->id)->first();

        //Obtener las piezas que esten terminadas y correctas en la tabla Cavidades para despues comparar cada una con su consecuente y asi armar los juegos
        $id_proceso = "cavidades_" . $clase->nombre . "_" . $ot;
        $proceso = Cavidades::where('id_proceso', $id_proceso)->first();
        $pzasOcupadas = Cavidades_pza::where('id_proceso', $proceso->id)->where('estado', 1)->get(); //Obtención de todas las piezas creadas.
        if ($proceso) {
            $pzasUsadas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Cavidades')->get(); //Obtención de todas las piezas creadas en Cavidades
        }

        if ($procesos->acabadoBombillo != 0) {
            //Obtener las piezas solamente en el proceso de Cavidades
            $pzasEncontradas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Acabado Bombillo')->where('error', 'Ninguno')->get();
            $this->piezasEncontradas($pzasEncontradas, $pzasUtilizar, $pzasGuardadas, $pzasUsadas, $pzasOcupadas);
        }else if($procesos->acabadoMolde != 0){
            //Obtener las piezas solamente en el proceso de Cavidades
            $pzasEncontradas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Acabado Molde')->where('error', 'Ninguno')->get();
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
            } //Recorro las piezas ocupadas en Cavidades
        }
        if (count($pzasOcupadas) > 0) {
            for ($x = 0; $x < count($pzasOcupadas); $x++) {
                array_push($numerosUsados, $pzasOcupadas[$x]->n_juego); //Guardo el número de pieza usada.
            }
        }
        for ($i = 0; $i < count($pzasEncontradas); $i++) { //Recorro las piezas encontradas de Cavidades
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
