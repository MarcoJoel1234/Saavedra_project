<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\Rebajes;
use App\Models\Rebajes_cnominal;
use App\Models\Rebajes_pza;
use App\Models\Rebajes_tolerancia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RebajesController extends Controller
{
    public function show()
    {
        $ot = Orden_trabajo::all(); //Obtención de todas las ordenes de trabajo.
        if (count($ot) != 0) {
            $oTrabajo = array(); //Declara arreglo para guardar las ordenes de trabajo disponibles en Rebajes
            //Recorre todas las ordenes de trabajo.
            foreach ($ot as $ot) {
                $contador = 0; //Contador para verificar que existan clases que pasaran por Rebajes
                $clases = Clase::where('id_ot', $ot->id)->get();
                //Recorre todas las clases registradas en la orden de trabajo.
                foreach ($clases as $clase) {
                    $proceso = Procesos::where('id_clase', $clase->id)->first(); //Obtención del proceso de la clase.
                    if ($proceso) {
                        if ($proceso->rebajes) { //Si existen maquinas en Rebajes de esa clase, se almacena en el arreglo que se pasara a la vista
                            $contador++;
                        }
                    }
                }
                //Si hay clases que pasaran por Rebajes, se almacena la orden de trabajo en el arreglo.
                if ($contador != 0) {
                    array_push($oTrabajo, $ot);
                }
            }
            if (count($oTrabajo) != 0) {
                return view('processes.rebajes', ['ot' => $oTrabajo]); //Retorno a vista de Rebajes
            }
            //Se retorna a la vista de Rebajes con las ordenes de trabajo que tienen clases que pasaran por Rebajes
            return view('processes.rebajes', ['ot']); //Retorno a vista de Rebajes
        }
        return view('processes.rebajes');
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
        $id = "rebajes_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Rebajes
        $cNominal = Rebajes_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = Rebajes_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $id_proceso = Rebajes::where('id_proceso', $id)->first();

        if (isset($request->n_pieza)) {  //Si se obtienen los datos de las piezas, se guardan en la tabla Rebajes_cnominal.
            $id_pieza = $request->n_pieza . $id_proceso->id;
            $piezaExistente = Rebajes_pza::where('id_pza', $id_pieza)->first();
            if ($piezaExistente) {
                $piezaExistente->rebaje1 = $request->rebaje1;
                $piezaExistente->rebaje2 = $request->rebaje2;
                $piezaExistente->rebaje3 = $request->rebaje3;
                $piezaExistente->profundidad_bordonio = $request->profundidad_bordonio;
                $piezaExistente->vena1 = $request->vena1;
                $piezaExistente->vena2 = $request->vena2;
                $piezaExistente->simetria = $request->simetria;
                $piezaExistente->observaciones = $request->observaciones;
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

                $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Rebajes')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if (!isset($pieza)) {
                    $pieza = new Pieza();
                }
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id;
                $pieza->n_pieza = $request->n_pieza;
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina;
                $pieza->proceso = "Rebajes";
                $pieza->error = $piezaExistente->error;
                $pieza->save();

                //Actualizar resultado de la meta
                $pzasCorrectas = Rebajes_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
                Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                    'resultado' => $pzasCorrectas->count(),
                ]);
                $meta = Metas::find($meta->id); //Busco la meta de la OT.
                //  //Retornar la pieza siguiente
                $pzaUtilizar = Rebajes_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Rebajes
                    $pzasCreadas = Rebajes_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.rebajes', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))]); //Retorno a vista de Rebajes
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla Rebajes
                    $this->piezaUtilizar($ot->id, $clase);
                }
            }
        } else if (isset($request->n_juegoElegido)) {
            $juegoExiste = Rebajes_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->first();
            if (!$juegoExiste) {
                $newPza = new Rebajes_pza(); //Creación de objeto para llenar tabla Rebajes
                $newPza->id_pza = $request->n_juegoElegido . $id_proceso->id; //Creación de id para tabla Rebajes
                $newPza->id_meta = $meta->id; //Llenado de id_meta para tabla Rebajes
                $newPza->id_proceso = $id_proceso->id; //Llenado de id_proceso para tabla Rebajes
                $newPza->estado = 1; //Llenado de estado para tabla Rebajes
                $newPza->n_juego = $request->n_juegoElegido; //Llenado de estado para tabla Rebajes
                $newPza->save(); //Guardado de datos en la tabla Rebajes
            }
        } else {
            $proceso = Rebajes::where('id_proceso', $id)->first(); //Busco el proceso de la OT.
            if (!$proceso) {
                //Llenado de la tabla Rebajes
                $copiado = new Rebajes(); //Creación de objeto para llenar tabla Rebajes
                $copiado->id_proceso = $id; //Creación de id para tabla Rebajes
                $copiado->id_ot = $ot->id; //Llenado de id_proceso para tabla Rebajes
                $copiado->save(); //Guardado de datos en la tabla Rebajes
            }
        }
        $id_proceso = Rebajes::where('id_proceso', $id)->first();
        if ($id_proceso !== "[]") {
            $pzasCreadas = Rebajes_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
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
            $pzasMeta = Rebajes_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.

            if (isset($cNominal) && isset($tolerancia)) {
                $pzaUtilizar = Rebajes_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Rebajes
                    $piezasVacias = Rebajes_pza::where('error', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                    if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                        for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                            $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                            if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                                $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Rebajes.
                                $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Rebajes.
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
                    return view('processes.rebajes', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de Rebajes
                } else {
                    return view('processes.rebajes', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Rebajes
                }
            } else {
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.rebajes', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Rebajes
            }
        }
    }
    public function compararDatosPieza($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->rebaje1 > ($cNominal->rebaje1 + $tolerancia->rebaje1) || $pieza->rebaje1 < ($cNominal->rebaje1 - $tolerancia->rebaje1) || $pieza->rebaje2 > ($cNominal->rebaje2 + $tolerancia->rebaje2) || $pieza->rebaje2 < ($cNominal->rebaje2 - $tolerancia->rebaje2) || $pieza->rebaje3 > ($cNominal->rebaje3 + $tolerancia->rebaje3) || $pieza->rebaje3 < ($cNominal->rebaje3 - $tolerancia->rebaje3) || $pieza->profundidad_bordonio > ($cNominal->profundidad_bordonio + $tolerancia->profundidad_bordonio) || $pieza->profundidad_bordonio < ($cNominal->profundidad_bordonio - $tolerancia->profundidad_bordonio) || $pieza->vena1 > ($cNominal->vena1 + $tolerancia->vena1) || $pieza->vena1 < ($cNominal->vena1 - $tolerancia->vena1) || $pieza->vena2 > ($cNominal->vena2 + $tolerancia->vena2) || $pieza->vena2 < ($cNominal->vena2 - $tolerancia->vena2) || $pieza->simetria > ($cNominal->simetria + $tolerancia->simetria) || $pieza->simetria < ($cNominal->simetria - $tolerancia->simetria)) {
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
        $id = "rebajes_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Rebajes
        $id_proceso = Rebajes::where('id_proceso', $id)->first();
        $cNominal = Rebajes_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = Rebajes_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $pzasCreadas = Rebajes_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
        $pzaUtilizar = Rebajes_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        if (isset($request->n_pieza)) { //Si se obtienen los datos de las piezas, se guardan en la tabla Rebajes_cnominal.
            for ($i = 0; $i < count($request->n_pieza); $i++) {
                $id_pieza = $request->n_pieza[$i] . $id_proceso->id; //Creación de id para tabla Rebajes_cnominal.
                $piezaExistente = Rebajes_pza::where('id_pza', $id_pieza)->first();
                if ($piezaExistente) {
                    $piezaExistente->rebaje1 = $request->rebaje1[$i];
                    $piezaExistente->rebaje2 = $request->rebaje2[$i];
                    $piezaExistente->rebaje3 = $request->rebaje3[$i];
                    $piezaExistente->profundidad_bordonio = $request->profundidad_bordonio[$i];
                    $piezaExistente->vena1 = $request->vena1[$i];
                    $piezaExistente->vena2 = $request->vena2[$i];
                    $piezaExistente->simetria = $request->simetria[$i];
                    if (isset($request->observaciones[$i])) { //Si se obtienen los datos de las piezas, se guardan en la tabla Rebajes_cnominal.
                        $piezaExistente->observaciones = $request->observaciones[$i];  //Llenado de observaciones para tabla Rebajes_cnominal.
                    }
                    $piezaExistente->save(); //Guardado de datos en la tabla Pza_Rebajes
                    
                    //Acrualiza el estado correcto de la pieza.
                    if ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && ($request->error[$i] == "Ninguno" || $request->error[$i] == "Maquinado")) {
                        $piezaExistente->error = 'Maquinado';
                    } else if (($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error[$i] == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 1 && $request->error[$i] == 'Fundicion')) {
                        $piezaExistente->error = $request->error[$i];
                    } else {
                        $piezaExistente->error = 'Ninguno';
                    }
                    $piezaExistente->save();

                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Rebajes')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if (!isset($pieza)) {
                        $pieza = new Pieza();
                    }
                    $pieza->id_clase = $clase->id;
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $piezaExistente->n_juego;
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Rebajes";
                    $pieza->error = $piezaExistente->error;
                    $pieza->save();
                }
            }
            //Actualizar resultado de la meta
            $pzasMeta = Rebajes_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.
            //Retornar la pieza siguiente
            $pzaUtilizar = Rebajes_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Rebajes
                $piezasVacias = Rebajes_pza::where('error', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Rebajes.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Rebajes.
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
            $pzasCreadas = Rebajes_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = Rebajes_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = Rebajes_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Rebajes
                return view('processes.rebajes', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de Palomas
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Rebajes
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.rebajes', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Palomas
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.rebajes', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))]); //Retorno la vista de Palomas
                    }
                }
            }
            $pzaUtilizar = Rebajes_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Rebajes
                $piezasVacias = Rebajes_pza::where('error', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Rebajes.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Rebajes.
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
            $pzasCreadas = Rebajes_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = Rebajes_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = Rebajes_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Rebajes
                return view('processes.rebajes', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de Rebajes
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Rebajes
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.rebajes', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Rebajes
            }
        }
    }

    public function piezaUtilizar($ot, $clase) //Función para obtener la pieza a utilizar.
    {
        $pzasUtilizar = array();
        $pzasGuardadas = array();
        $procesos = Procesos::where('id_clase', $clase->id)->first();

        $id_proceso = "rebajes_" . $clase->nombre . "_" . $ot;
        $proceso = Rebajes::where('id_proceso', $id_proceso)->first();
        $pzasOcupadas = Rebajes_pza::where('id_proceso', $proceso->id)->where('estado', 1)->get(); //Obtención de todas las piezas creadas.
        if ($proceso) {
            $pzasUsadas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Rebajes')->get(); //Obtención de todas las piezas creadas en Palomas
        }

        if ($procesos->palomas != 0) {
            //Obtener las piezas solamente en el proceso de Rebajes
            $pzasEncontradas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Palomas')->where('error', 'Ninguno')->get();
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
            } //Recorro las piezas ocupadas en Rebajes
        }
        if (count($pzasOcupadas) > 0) {
            for ($x = 0; $x < count($pzasOcupadas); $x++) {
                array_push($numerosUsados, $pzasOcupadas[$x]->n_juego); //Guardo el número de pieza usada.
            }
        }
        for ($i = 0; $i < count($pzasEncontradas); $i++) { //Recorro las piezas encontradas de Rebajes
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
