<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\OffSet;
use App\Models\OffSet_pza;
use App\Models\Orden_trabajo;
use App\Models\Palomas;
use App\Models\Palomas_cnominal;
use App\Models\Palomas_pza;
use App\Models\Palomas_tolerancia;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\Rebajes_pza;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PalomasController extends Controller
{
    public function show($error)
    {
        $ot = Orden_trabajo::all(); //Obtención de todas las ordenes de trabajo.
        if (count($ot) != 0) {
            $oTrabajo = array(); //Declara arreglo para guardar las ordenes de trabajo disponibles en Palomas
            //Recorre todas las ordenes de trabajo.
            foreach ($ot as $ot) {
                $contador = 0; //Contador para verificar que existan clases que pasaran por Palomas
                $clases = Clase::where('id_ot', $ot->id)->get();
                //Recorre todas las clases registradas en la orden de trabajo.
                foreach ($clases as $clase) {
                    $proceso = Procesos::where('id_clase', $clase->id)->first(); //Obtención del proceso de la clase.
                    if ($proceso) {
                        if ($proceso->palomas) { //Si existen maquinas en Palomas de esa clase, se almacena en el arreglo que se pasara a la vista
                            $contador++;
                        }
                    }
                }
                //Si hay clases que pasaran por Palomas, se almacena la orden de trabajo en el arreglo.
                if ($contador != 0) {
                    array_push($oTrabajo, $ot);
                }
            }
            //Si hay clases que pasaran por Primera operación soldadura, se almacena la orden de trabajo en el arreglo.
            if (count($oTrabajo) != 0) {
                if ($error == 1) {
                    return view('processes.palomas', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Desbaste exterior
                }
                return view('processes.palomas', ['ot' => $oTrabajo]); //Retorno a vista de Desbaste exterior
            }
            if ($error == 1) {
                return view('processes.palomas', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Desbaste exterior
            }
            //Se retorna a la vista de Primera operación soldadura con las ordenes de trabajo que tienen clases que pasaran por Desbaste exterior
            return view('processes.palomas', ['ot']); //Retorno a vista de Desbaste exterior
        }
        if ($error == 1) {
            return view('processes.palomas', ['error' => $error]); //Retorno a vista de Desbaste exterior
        }
        return view('processes.palomas');
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
        $id = "palomas_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Palomas
        $cNominal = Palomas_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = Palomas_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $proceso = Palomas::where('id_proceso', $id)->first(); //Busco el proceso de la OT.
        if (!$proceso) {
            //Llenado de la tabla Palomas
            $copiado = new Palomas(); //Creación de objeto para llenar tabla Palomas
            $copiado->id_proceso = $id; //Creación de id para tabla Palomas
            $copiado->id_ot = $ot->id; //Llenado de id_proceso para tabla Palomas
            $copiado->save(); //Guardado de datos en la tabla Palomas
        }

        $id_proceso = Palomas::where('id_proceso', $id)->first();
        $pzasPalomas = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
        $pzasOffSet = Pieza::where('proceso', 'Off Set')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('error', 'Ninguno')->get();
        $pzasRestantes = $this->piezasRestantes($pzasOffSet, $pzasPalomas, $clase);

        if (isset($request->n_pieza)) {  //Si se obtienen los datos de las piezas, se guardan en la tabla Palomas_cnominal.
            $id_pieza = $request->n_pieza . $id_proceso->id;
            $piezaExistente = Palomas_pza::where('id_pza', $id_pieza)->first();
            if ($piezaExistente) {
                $piezaExistente->anchoPaloma = $request->anchoPaloma;
                $piezaExistente->gruesoPaloma = $request->gruesoPaloma;
                $piezaExistente->profundidadPaloma = $request->profundidadPaloma;
                $piezaExistente->rebajeLlanta = $request->rebajeLlanta;
                $piezaExistente->observaciones = $request->observaciones;
                $piezaExistente->error = $request->error;
                $piezaExistente->estado = 2;
                $piezaExistente->save();

                //Actualiza el estado correcto de la pieza.
                if ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error == 0) {
                    $piezaExistente->error = 'Maquinado';
                } else if (($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 1 && $request->error == 'Fundicion')) {
                    $piezaExistente->error = $request->error;
                } else {
                    $piezaExistente->error = 'Ninguno';
                }

                $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Palomas')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if (!isset($pieza)) {
                    $pieza = new Pieza();
                }
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id;
                $pieza->n_pieza = $request->n_pieza;
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina;
                $pieza->proceso = "Palomas";
                $pieza->error = $piezaExistente->error;
                $pieza->save();

                //Actualizar resultado de la meta
                $pzasCorrectas = Palomas_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
                Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                    'resultado' => $pzasCorrectas->count(),
                ]);
                $meta = Metas::find($meta->id); //Busco la meta de la OT.

                $pzasPalomas = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
                $pzasOffSet = Pieza::where('proceso', 'Off Set')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('error', 'Ninguno')->get();
                $pzasRestantes = $this->piezasRestantes($pzasOffSet, $pzasPalomas, $clase);

                //  //Retornar la pieza siguiente
                $pzaUtilizar = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Offset
                    $pzasCreadas = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.palomas', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Copiado
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla Offset
                    $this->piezaUtilizar($ot->id, $clase);
                }
            }
        } else if (isset($request->n_juegoElegido)) {
            $juegoExiste = Palomas_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->first();
            if (!$juegoExiste) {
                $newPza = new Palomas_pza(); //Creación de objeto para llenar tabla Palomas
                $newPza->id_pza = $request->n_juegoElegido . $id_proceso->id; //Creación de id para tabla Palomas
                $newPza->id_meta = $meta->id; //Llenado de id_meta para tabla Palomas
                $newPza->id_proceso = $id_proceso->id; //Llenado de id_proceso para tabla Palomas
                $newPza->estado = 1; //Llenado de estado para tabla Palomas
                $newPza->n_juego = $request->n_juegoElegido; //Llenado de estado para tabla Palomas
                $newPza->save(); //Guardado de datos en la tabla Palomas
            }
        }
        $id_proceso = Palomas::where('id_proceso', $id)->first();
        if ($id_proceso !== "[]") {
            $pzasCreadas = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
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
            $pzasMeta = Palomas_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.

            if (isset($cNominal) && isset($tolerancia)) {
                $pzaUtilizar = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Palomas
                    $piezasVacias = Palomas_pza::where('error', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                    if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                        for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                            $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                            if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                                $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Palomas.
                                $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Palomas.
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
                    return view('processes.palomas', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Palomas
                } else {
                    return view('processes.palomas', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Palomas
                }
            } else {
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.palomas', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Palomas
            }
        }
    }
    public function compararDatosPieza($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->anchoPaloma > ($cNominal->anchoPaloma + $tolerancia->anchoPaloma) || $pieza->anchoPaloma < ($cNominal->anchoPaloma - $tolerancia->anchoPaloma) || $pieza->gruesoPaloma > ($cNominal->gruesoPaloma + $tolerancia->gruesoPaloma) || $pieza->gruesoPaloma < ($cNominal->gruesoPaloma - $tolerancia->gruesoPaloma) || $pieza->profundidadPaloma > ($cNominal->profundidadPaloma + $tolerancia->profundidadPaloma) || $pieza->profundidadPaloma < ($cNominal->profundidadPaloma - $tolerancia->profundidadPaloma) || $pieza->rebajeLlanta > ($cNominal->rebajeLlanta + $tolerancia->rebajeLlanta) || $pieza->rebajeLlanta < ($cNominal->rebajeLlanta - $tolerancia->rebajeLlanta)) {
            return 0; //Retorno de datos. //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        }
        return 1;
    }
    public function piezasRestantes($piezasProcesoA, $piezasProcesoB, $clase)
    {
        //Contar las piezas malas en el proceso de Rebajes
        $pzasMalasRebajes = Pieza::where('proceso', 'Rebajes')->where('id_clase', $clase->id)->where('error', '!=', 'Ninguno')->get();
        $pzasRestar = 0;
        foreach($pzasMalasRebajes as $pzaRebajes){
            $estado = 0;
            foreach($piezasProcesoB as $pzaB){
                if($pzaRebajes->n_pieza == $pzaB->n_juego){
                    $estado = 1;
                    break;
                }
            }
            if($estado == 0){
                $pzasRestar++;
            }
        }
        return (count($piezasProcesoA) - count($piezasProcesoB)) - $pzasRestar;
    }
    public function edit(Request $request)
    {
        $meta = Metas::find($request->metaData); //Busco la meta de la OT.
        $ot = Orden_trabajo::find($meta->id_ot); //Obtención de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $clase = Clase::find($meta->id_clase); //Busco la clase de la OT.
        $id = "palomas_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Palomas
        $id_proceso = Palomas::where('id_proceso', $id)->first();
        $cNominal = Palomas_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = Palomas_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $pzasCreadas = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.

        $pzasPalomas = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
        $pzasOffSet = Pieza::where('proceso', 'Off Set')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('error', 'Ninguno')->get();
        $pzasRestantes = $this->piezasRestantes($pzasOffSet, $pzasPalomas, $clase);

        $pzaUtilizar = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        if (isset($request->n_pieza)) { //Si se obtienen los datos de las piezas, se guardan en la tabla Palomas_cnominal.
            for ($i = 0; $i < count($request->n_pieza); $i++) {
                $id_pieza = $request->n_pieza[$i] . $id_proceso->id; //Creación de id para tabla Palomas_cnominal.
                $piezaExistente = Palomas_pza::where('id_pza', $id_pieza)->first();
                if ($piezaExistente) {
                    $piezaExistente->anchoPaloma = $request->anchoPaloma[$i];
                    $piezaExistente->gruesoPaloma = $request->gruesoPaloma[$i];
                    $piezaExistente->profundidadPaloma = $request->profundidadPaloma[$i];
                    $piezaExistente->rebajeLlanta = $request->rebajeLlanta[$i];
                    if (isset($request->observaciones[$i])) { //Si se obtienen los datos de las piezas, se guardan en la tabla Palomas_cnominal.
                        $piezaExistente->observaciones = $request->observaciones[$i];  //Llenado de observaciones para tabla Palomas_cnominal.
                    }
                    $piezaExistente->save(); //Guardado de datos en la tabla Pza_Palomas

                    //Acrualiza el estado correcto de la pieza.
                    if ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && ($request->error[$i] == "Ninguno" || $request->error[$i] == "Maquinado")) {
                        $piezaExistente->error = 'Maquinado';
                    } else if (($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error[$i] == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 1 && $request->error[$i] == 'Fundicion')) {
                        $piezaExistente->error = $request->error[$i];
                    } else {
                        $piezaExistente->error = 'Ninguno';
                    }
                    $piezaExistente->save();

                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Palomas')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if (!isset($pieza)) {
                        $pieza = new Pieza();
                    }
                    $pieza->id_clase = $clase->id;
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $piezaExistente->n_juego;
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Palomas";
                    $pieza->error = $piezaExistente->error;
                    $pieza->save();
                }
            }
            //Actualizar resultado de la meta
            $pzasMeta = Palomas_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.
            //Retornar la pieza siguiente
            $pzaUtilizar = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Palomas
                $piezasVacias = Palomas_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Palomas.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Palomas.
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
            $pzasCreadas = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = Palomas_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = Palomas_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Palomas
                return view('processes.palomas', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Palomas
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Palomas
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.palomas', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Palomas
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.palomas', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'pzasRestantes' => $pzasRestantes]); //Retorno la vista de Palomas
                    }
                }
            }
            $pzaUtilizar = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Palomas
                $piezasVacias = Palomas_pza::where('error', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Palomas.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Palomas.
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
            $pzasCreadas = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = Palomas_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = Palomas_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Palomas
                return view('processes.palomas', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Palomas
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Palomas
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.palomas', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Palomas
            }
        }
    }

    public function piezaUtilizar($ot, $clase) //Función para obtener la pieza a utilizar.
    {
        $pzasUtilizar = array();
        $juegosContados = array();
        //Obtener las piezas que esten terminadas y correctas en la tabla OffSet
        $pzasOffSet = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Off Set')->where('error', 'Ninguno')->get();
        //Piezas ocupadas en la tabla Palomas
        $id_proceso = Palomas::where('id_proceso', 'palomas_' . $clase->nombre . '_' . $clase->id_ot)->first();
        $pzasOcupadasP = Palomas_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->get();
        //Piezas terminadas en la tabla palomas
        $pzasTerminadasP = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Palomas')->get();

        //Verificar si pasara por el proceso Rebajes
        $procesos = Procesos::where('id_clase', $clase->id)->first();
        if ($procesos->rebajes != 0) {
            //Obtener piezas malas de rebajes
            $piezasR = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Rebajes')->where('error', '!=', 'Ninguno')->get();

            foreach ($pzasOffSet as $pzaOffSet) {
                $estado = 0;
                //Verificar que no se encuentre entre las piezas ocupadas en Palomas
                foreach ($pzasOcupadasP as $pzaPalomas) {
                    if ($pzaOffSet->n_pieza == $pzaPalomas->n_juego) {
                        $estado = 1;
                        break;
                    }
                }
                //Verificar que no se encuentre entre las piezas ya registradas en Palomas
                foreach ($pzasTerminadasP as $pzaPalomasT) {
                    if ($pzaOffSet->n_pieza == $pzaPalomasT->n_pieza) {
                        $estado = 1;
                        break;
                    }
                }
                //Verificar que no se encuentre entre las piezas malas de Rebajes
                foreach ($piezasR as $piezaR) {
                    if ($pzaOffSet->n_pieza == $piezaR->n_pieza) {
                        $estado = 1;
                        break;
                    }
                }
                if ($estado == 0) {
                    array_push($pzasUtilizar, $pzaOffSet->n_pieza);
                }
                array_push($juegosContados, $pzaOffSet->n_pieza);
            }
        }else{
            foreach ($pzasOffSet as $pzaOffSet) {
                $estado = 0;
                //Verificar que no se encuentre entre las piezas ocupadas en Palomas
                foreach ($pzasOcupadasP as $pzaPalomas) {
                    if ($pzaOffSet->n_pieza == $pzaPalomas->n_juego) {
                        $estado = 1;
                        break;
                    }
                }
                //Verificar que no se encuentre entre las piezas ya registradas en Palomas
                foreach ($pzasTerminadasP as $pzaPalomasT) {
                    if ($pzaOffSet->n_pieza == $pzaPalomasT->n_pieza) {
                        $estado = 1;
                        break;
                    }
                }
                if ($estado == 0) {
                    array_push($pzasUtilizar, $pzaOffSet->n_pieza);
                }
                array_push($juegosContados, $pzaOffSet->n_pieza);
            }
        }
        return $pzasUtilizar;
    }
}
