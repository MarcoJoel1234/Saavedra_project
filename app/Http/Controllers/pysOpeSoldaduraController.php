<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\PySOpeSoldadura;
use App\Models\PySOpeSoldadura_cnominal;
use App\Models\PySOpeSoldadura_pza;
use App\Models\PySOpeSoldadura_tolerancia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PySOpeSoldaduraController extends Controller
{
    public function show($error)
    {
        $ot = Orden_trabajo::all(); //Obtención de todas las ordenes de trabajo.
        if (count($ot) != 0) {
            $oTrabajo = array(); //Declara arreglo para guardar las ordenes de trabajo disponibles en pysOperacion
            //Recorre todas las ordenes de trabajo.
            foreach ($ot as $ot) {
                $contador = 0; //Contador para verificar que existan clases que pasaran por pysOperacion
                $clases = Clase::where('id_ot', $ot->id)->get();
                //Recorre todas las clases registradas en la orden de trabajo.
                foreach ($clases as $clase) {
                    $proceso = Procesos::where('id_clase', $clase->id)->first(); //Obtención del proceso de la clase.
                    if ($proceso) {
                        if ($proceso->operacionEquipo) { //Si existen maquinas en Operacion equipo de esa clase, se almacena en el arreglo que se pasara a la vista
                            $contador++;
                        }
                    }
                }
                //Si hay clases que pasaran por pysOperacion, se almacena la orden de trabajo en el arreglo.
                if ($contador != 0) {
                    array_push($oTrabajo, $ot);
                }
            }
            //Si hay clases que pasaran por pysOperacion, se almacena la orden de trabajo en el arreglo.
            if (count($oTrabajo) != 0) {
                if ($error == 1) {
                    return view('processes.pysOpeSoldadura', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Operacion equipo
                }
                return view('processes.pysOpeSoldadura', ['ot' => $oTrabajo]);
            }
            if ($error == 1) {
                return view('processes.pysOpeSoldadura', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Operacion equipo
            }
            //Se retorna a la vista de Operacion equipo con las ordenes de trabajo que tienen clases que pasaran por Operacion equipo
            return view('processes.pysOpeSoldadura', ['ot']);
        }
        if ($error == 1) {
            return view('processes.pysOpeSoldadura', ['error' => $error]);
        }
        return view('processes.pysOpeSoldadura');
    }
    public function storeheaderTable(Request $request)
    {
        //Si se obtienen los datos de la OT y la meta, se guardan en variables de sesión.
        if (session('controller')) {
            $meta = Metas::find(session('meta'));
            $operacion = session('operacion');
        } else {
            $meta = Metas::find($request->metaData);
            $operacion = $request->operacion;
        }
        $ot = Orden_trabajo::where('id', $meta->id_ot)->first(); //Busco la OT que se quiere editar.
        $clase = Clase::find($meta->id_clase);
        $moldura = Moldura::find($ot->id_moldura);

        $id = "1y2opeSoldadura_" . $clase->nombre . "_" . $ot->id . "_" . $operacion; //Creación de id para tabla 1y2opeSoldadura.
        $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id)->first();
        $tolerancia = PySOpeSoldadura_tolerancia::where('id_proceso', $id)->first();
        if ($meta->id_proceso == null && isset($operacion) || !session('clase') && isset($operacion)) {
            $proceso = PySOpeSoldadura::where('id_proceso', $id)->first();
            if (!$proceso) {
                //Llenado de la tabla PySOpeSoldaduraEquipo 
                $pysSoldadura = new PySOpeSoldadura(); //Creación de objeto para llenar tabla de 1ra y 2da operación equipo
                $pysSoldadura->id_proceso = $id; 
                $pysSoldadura->id_clase = $clase->id; 
                $pysSoldadura->operacion = $operacion; 
                $pysSoldadura->id_ot = $ot->id; 
                $pysSoldadura->save();
                $meta->id_proceso = $pysSoldadura->id;
            } else {
                $meta->id_proceso = $proceso->id;
            }
            $meta->save();
        }
        $id_proceso = PySOpeSoldadura::where('id_proceso', $id)->first();
        $pzasRestantes = count(PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get()) / 2;
        $pzasRestantes = $clase->piezas - $pzasRestantes;

        
        if (isset($request->n_pieza)) { //Si existe informacion de alguna pieza registrada
            //Se verifica que la pieza exista para llenar los campos que sean null
            $id_pieza = $request->n_pieza . $id_proceso->id;
            $piezaExistente = PySOpeSoldadura_pza::where('id_pza', $id_pieza)->first();
            //Llenado de campos de la pieza existente
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

                //Actualizacion de error y estado de la pieza
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

                
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                //Se verifica si la pieza ya existe en la tabla pieza
                $pieza = Pieza::where('n_pieza', $request->n_pieza)->where('proceso', 'Operacion Equipo_' . $operacion)->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                if (!isset($pieza)) {//Si no existe se crea un nuevo objeto
                    $pieza = new Pieza();
                }//Si existe unicamente se actualiza
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id;
                $pieza->n_pieza = $request->n_pieza; //
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina;
                $pieza->proceso = "Operacion Equipo_" . $operacion;
                $pieza->error = $piezaExistente->error;
                $pieza->save();

                //Actualizar resultado de la meta
                $contadorPzas = 0;
                $juegosUsados = array();
                $pzasCorrectas = PySOpeSoldadura_pza::where('id_meta', $meta->id)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas del proceso.
                foreach ($pzasCorrectas as $pzaCorrecta) {
                    $pzaCorrecta2 = PySOpeSoldadura_pza::where('n_juego', $pzaCorrecta->n_juego)->where('id_meta', $meta->id)->get();
                    
                    if (!in_array($pzaCorrecta->n_juego, $juegosUsados)) {
                        array_push($juegosUsados, $pzaCorrecta->n_juego);
                        $pzasMalas = 0;
                        foreach ($pzaCorrecta2 as $pza) {
                            if ($pza->correcto == 1) {
                                $contadorPzas += .5;
                            } else if ($pza->correcto !== null) {
                                $pzasMalas++;
                            }
                        }
                        if ($pzasMalas > 0 && $pzasMalas < 2) {
                            $contadorPzas -= .5;
                        }
                    }
                }
                $meta = Metas::find($meta->id); //Actualización de datos en tabla Metas.
                $meta->resultado = $contadorPzas;
                $meta->save(); //Guardado de datos en la tabla Metas.
                $pzasRestantes = count(PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get()) / 2;
                $pzasRestantes = $clase->piezas - $pzasRestantes;

                //Retornar la pieza siguiente
                $pzaUtilizar = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Operacion equipo.
                    $pzasCreadas = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes, 'operacion' => $operacion]); //Retorno a vista de Operacion equipo.
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla operacion equipo
                    $this->piezaUtilizar($clase, $id);
                }
            }
        } else if (isset($request->n_juegoElegido)) {
            $juegoExiste = PySOpeSoldadura_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->first();
            if (!$juegoExiste) {
                //Separar string de juego elegido, el numero de juego y la letra
                $n_pieza = str_split($request->n_juegoElegido);
                $numero = "";
                foreach ($n_pieza as $n) {
                    if (is_numeric($n)) {
                        $numero .= $n;
                    } else {
                        continue;
                    }
                }
                for ($i = 1; $i <= 2; $i++) {
                    $newPza = new PySOpeSoldadura_pza();
                    if ($i == 1) {
                        $newPza->n_pieza = $numero . 'H';
                    } else {
                        $newPza->n_pieza = $numero . 'M';
                    }
                    $newPza->id_pza = $newPza->n_pieza . $id_proceso->id;
                    $newPza->n_juego = $request->n_juegoElegido;
                    $newPza->id_meta = $meta->id;
                    $newPza->id_proceso = $id_proceso->id;
                    $newPza->estado = 1;
                    $newPza->save();
                }
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

            //Actualizar el resultado de la meta
            $contadorPzas = 0;
            $juegosUsados = array();
            $pzasCorrectas = PySOpeSoldadura_pza::where('id_meta', $meta->id)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas.
            foreach ($pzasCorrectas as $pzaCorrecta) {
                $pzaCorrecta2 = PySOpeSoldadura_pza::where('n_juego', $pzaCorrecta->n_juego)->where('id_meta', $meta->id)->get();
                if (!in_array($pzaCorrecta->n_juego, $juegosUsados)) {
                    array_push($juegosUsados, $pzaCorrecta->n_juego);
                    $pzasMalas = 0;
                    foreach ($pzaCorrecta2 as $pza) {
                        if ($pza->correcto == 1) {
                            $contadorPzas += .5;
                        } else if ($pza->correcto !== null) {
                            $pzasMalas++;
                        }
                    }
                    if ($pzasMalas > 0 && $pzasMalas < 2) {
                        $contadorPzas -= .5;
                    }
                }
            }
            $meta = Metas::find($meta->id); 
            $meta->resultado = $contadorPzas;
            $meta->save();
            //
            if (isset($cNominal) && isset($tolerancia)) {
                $pzaUtilizar = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                    $piezasVacias = PySOpeSoldadura_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                    if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                        for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                            $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                            if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                                $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Desbaste exterior.
                                $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Desbaste exterior.
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
                        $pzasUtilizar = $this->piezaUtilizar($clase, $id); //Llamado a función para obtener las piezas disponibles.
                    }
                }
                if (isset($pzasUtilizar)) { //Si no se encontro una pieza para utilizar, se crea una nueva pieza.
                    return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes, 'operacion' => $operacion]); //Retorno a vista de Operacion equipo.
                } else {
                    return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes, 'operacion' => $operacion])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Operacion equipo.
                }
            } else {
                $pzasUtilizar = $this->piezaUtilizar($clase, $id); //Llamado a función para obtener las piezas disponibles.
                return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'pzasRestantes' => $pzasRestantes, 'operacion' => $operacion])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Operacion equipo.
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
        $meta = Metas::find($request->metaData);
        $ot = Orden_trabajo::find($meta->id_ot); 
        $moldura = Moldura::find($ot->id_moldura);
        $clase = Clase::find($meta->id_clase);
        $id = "1y2opeSoldadura_" . $clase->nombre . "_" . $ot->id . "_" . $request->operacion; 
        $id_proceso = PySOpeSoldadura::where('id_proceso', $id)->first();;
        $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id)->first(); 
        $tolerancia = PySOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); 
        $pzasCreadas = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
        $pzasRestantes = count(PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get()) / 2;
        $pzasRestantes = $clase->piezas - $pzasRestantes;
        $pzaUtilizar = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        if (isset($request->n_pieza)) {
            for ($i = 0; $i < count($request->n_pieza); $i++) {
                $id_pieza = $request->n_pieza[$i] . $id_proceso->id;
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



                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_pieza)->where('proceso', 'Operacion Equipo_' . $request->operacion)->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if (!isset($pieza)) {
                        $pieza = new Pieza(); //Creación del obejeto para llenar la tabla pieza.
                    }
                    $pieza->id_clase = $clase->id; //Lenado de id_clase para la tabla pieza.
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $piezaExistente->n_pieza;
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Operacion Equipo_" . $request->operacion;
                    $pieza->error = $piezaExistente->error;
                    $pieza->save();
                }
            }
            //Actualizar resultado de la meta
            $contadorPzas = 0;
            $juegosUsados = array();
            $pzasCorrectas = PySOpeSoldadura_pza::where('id_meta', $meta->id)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas.
            foreach ($pzasCorrectas as $pzaCorrecta) {
                $pzaCorrecta2 = PySOpeSoldadura_pza::where('n_juego', $pzaCorrecta->n_juego)->where('id_meta', $meta->id)->get();
                if (!in_array($pzaCorrecta->n_juego, $juegosUsados)) {
                    array_push($juegosUsados, $pzaCorrecta->n_juego);
                    $pzasMalas = 0;
                    foreach ($pzaCorrecta2 as $pza) {
                        if ($pza->correcto == 1) {
                            $contadorPzas += .5;
                        } else if ($pza->correcto !== null) {
                            $pzasMalas++;
                        }
                    }
                    if ($pzasMalas > 0 && $pzasMalas < 2) {
                        $contadorPzas -= .5;
                    }
                }
            }
            $meta = Metas::find($meta->id); //Actualización de datos en tabla Metas.
            $meta->resultado = $contadorPzas;
            $meta->save(); //Guardado de datos en la tabla Metas.
            //Retornar la pieza siguiente
            $pzaUtilizar = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $piezasVacias = PySOpeSoldadura_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Desbaste exterior.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Desbaste exterior.
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
                    $pzasUtilizar = $this->piezaUtilizar($clase, $id); //Llamado a función para obtener las piezas disponibles.
                }
            }
            $pzasCreadas = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = PySOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes, 'operacion' => $request->operacion]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $pzasUtilizar = $this->piezaUtilizar($clase, $id); //Llamado a función para obtener las piezas disponibles.
                return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes, 'operacion' => $request->operacion])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.pysOpeSoldadura', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'pzasRestantes' => $pzasRestantes, 'operacion' => $request->operacion]); //Retorno la vista de cepillado.
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
                    $pzasUtilizar = $this->piezaUtilizar($clase, $id); //Llamado a función para obtener las piezas disponibles.
                }
            }
            $pzasCreadas = PySOpeSoldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = PySOpeSoldadura_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes, 'operacion' => $request->operacion]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $pzasUtilizar = $this->piezaUtilizar($clase, $id); //Llamado a función para obtener las piezas disponibles.
                return view('processes.pysOpeSoldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes, 'operacion' => $request->operacion])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        }
    }

    public function piezaUtilizar($clase, $id) //Función para obtener la pieza a utilizar.
    {
        //Obtener el numero de juego de los juegos ya utilizados o ya registrados
        $pzasUtilizadas = array();
        $operacionEquipo = PySOpeSoldadura::where('id_proceso', $id)->first();
        $pzas1y2Ope = PySOpeSoldadura_pza::where('id_proceso', $operacionEquipo->id)->get();
        foreach ($pzas1y2Ope as $pza) {
            array_push($pzasUtilizadas, $pza->n_juego);
        }


        //Obtener el numero de piezas que se crearan
        $pzasUtilizar = array();
        for ($i = 0; $i < $clase->piezas; $i++) {
            if (!in_array($i + 1 . 'J', $pzasUtilizadas)) {
                array_push($pzasUtilizar, $i + 1 . 'J');
            }
        }
        return $pzasUtilizar;
    }
}
