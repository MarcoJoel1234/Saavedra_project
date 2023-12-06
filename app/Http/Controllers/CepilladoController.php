<?php

namespace App\Http\Controllers;

use App\Models\Cepillado;
use App\Models\Cepillado_cnominal;
use App\Models\Cepillado_tolerancia;
use App\Models\Clase;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\Pza_cepillado;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;

class CepilladoController extends Controller
{
    public function show()
    {
        $oTrabajo = array(); //Declara arreglo para guardar las ordenes de trabajo disponibles en Cepillado.
        $ot = Orden_trabajo::all(); //Obtención de todas las ordenes de trabajo.
        //Recorre todas las ordenes de trabajo.
        foreach($ot as $ot){
            $contador = 0;//Contador para verificar que existan clases que pasaran por Cepillado
            $clases = Clase::where('id_ot', $ot->id)->get();
            //Recorre todas las clases registradas en la orden de trabajo.
            foreach($clases as $clase){
                $proceso = Procesos::where('id_clase', $clase->id)->first(); //Obtención del proceso de la clase.
                if($proceso->cepillado){ //Si existen maquinas en cepillado de esa clase, se almacena en el arreglo que se pasara a la vista
                    $contador++;
                }
            }
            //Si hay clases que pasaran por Cepillado, se almacena la orden de trabajo en el arreglo.
            if($contador != 0){
                array_push($oTrabajo, $ot);
            }
        }
        //Se retorna a la vista de Cepillado con las ordenes de trabajo que tienen clases que pasaran por cepillado.
        return view('processes.cepillado', ['ot' => $oTrabajo]); //Retorno a vista de Cepillado.
    }

    public function storeheaderTable(Request $request) 
    {
        //Si se obtienen los datos de la OT y la meta, se guardan en variables de sesión.
        if (session('controller')) { //
            $meta = Metas::find(session('meta')); //Busco la meta de la OT.
        } else {
            $meta = Metas::find($request->metaData); //Busco la meta de la OT.
        }
        $ot = Orden_trabajo::where('id', $meta->id_ot)->first(); //Busco la OT que se quiere editar.
        $clase = Clase::find($meta->id_clase); //Busco la clase de la OT.
        $id = "Cepillado_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Cepillado.
        $cNominal = Cepillado_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = Cepillado_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $id_proceso = Cepillado::where('id_proceso', $id)->first();
        if (isset($id_proceso)) {
            $pzasCreadas = Pza_cepillado::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas
            $nPiezasCreadas = Pza_cepillado::where('id_proceso', $id_proceso->id)->get();
            if ((($nPiezasCreadas->count() / 2) < $clase->piezas && $nPiezasCreadas->count() > 0) || $nPiezasCreadas === 0) { //Si el número de piezas creadas es menor al número de piezas de la clase, se crearan las piezas.
                $this->createPiezas($id_proceso->id, $clase->piezas, ($nPiezasCreadas->count() / 2) + 1); //Creación de piezas en la tabla Pza_cepillado.
            }
        } else {
            $pzasCreadas = 0;
            $nPiezasCreadas = 0;
        }
        if (isset($request->n_pieza)) {  //Si se obtienen los datos de las piezas, se guardan en la tabla Cepillado_cnominal.
            $id_pieza = $request->n_pieza . $id_proceso->id; //Creación de id para tabla Cepillado_cnominal.
            $piezaExistente = Pza_cepillado::where('id_pza', $id_pieza)->first();
            if ($piezaExistente) {
                $piezaExistente->radiof_mordaza = $request->radiof_mordaza;
                $piezaExistente->radiof_mayor = $request->radiof_mayor;
                $piezaExistente->radiof_sufridera = $request->radiof_sufridera;
                $piezaExistente->profuFinal_CFC = $request->profuFinal_CFC;
                $piezaExistente->profuFinal_mitadMB = $request->profuFinal_mitadMB;
                $piezaExistente->profuFinal_PCO = $request->profuFinal_PCO;
                $piezaExistente->acetato_MB = $request->acetato_MB;
                $piezaExistente->ensamble = $request->ensamble;
                $piezaExistente->distancia_barrenoAli = $request->distancia_barrenoAli;
                $piezaExistente->profu_barrenoAli = $request->profu_barrenoAli;
                $piezaExistente->altura_vena = $request->altura_vena;
                $piezaExistente->ancho_vena = $request->ancho_vena;
                $piezaExistente->pin1 = $request->pin1;
                $piezaExistente->pin2 = $request->pin2;
                $piezaExistente->observaciones = $request->observaciones;
                $piezaExistente->estado = 2;
                $piezaExistente->save();

                if (!isset($piezaExistente->correcto)) {
                    //Restar el número de piezas en la orden de trabajo
                    $clase->piezas -= .5; //Resto el número de piezas de la clase.
                    $clase->save(); //Guardo los datos en la tabla Clase.
                }
                if($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error == 0) {
                    $piezaExistente->error = 'Maquinado';
                    $piezaExistente->correcto = 0;
                }else if(($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 1 && $request->error == 'Fundicion')){
                    $piezaExistente->error = $request->error;
                    $piezaExistente->correcto = 0;
                }else{
                    $piezaExistente->error = 'Ninguno';
                    $piezaExistente->correcto = 1;
                }
                $piezaExistente->save();
                
                $pieza = Pieza::where('n_pieza', $piezaExistente->n_pieza)->where('proceso', "Cepillado")->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if(!isset($pieza)){
                    $pieza = new Pieza();
                }
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id;
                $pieza->n_pieza = $request->n_pieza;
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina;
                $pieza->proceso = "Cepillado";
                $pieza->error = $piezaExistente->error;
                $pieza->save();

                //Actualizar resultado de la meta
                $pzasCorrectas = Pza_cepillado::where('id_meta', $meta->id)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas.
                Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                    'resultado' => $pzasCorrectas->count() / 2,
                ]);
                $meta = Metas::find($meta->id); //Busco la meta de la OT.

                //  //Retornar la pieza siguiente
                $pzaUtilizar = Pza_cepillado::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Cepillado.
                    $pzasCreadas = Pza_cepillado::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.cepillado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaUtilizar' => $pzaUtilizar]); //Retorno a vista de Cepillado.
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla cepillado
                    $this->piezaUtilizar($id_proceso->id, $meta);
                }
            }
        } else {
            $proceso = Cepillado::where('id_proceso', $id)->first(); //Busco el proceso de la OT.
            if (!$proceso) {
                //Llenado de la tabla Cepillado.
                $cepillado = new Cepillado(); //Creación de objeto para llenar tabla Cepillado.
                $cepillado->id_proceso = $id; //Creación de id para tabla Cepillado.
                $cepillado->id_ot = $ot->id; //Llenado de id_proceso para tabla Cepillado.
                $cepillado->save(); //Guardado de datos en la tabla Cepillado.

                if ($nPiezasCreadas == 0) { //Si no existen piexas creadas en la tabla Pza_cepillado.
                    $this->createPiezas($cepillado->id, $clase->piezas, 1); //Creación de piezas en la tablaPza_cepillado.
                }
                if (isset($cNominal) && isset($tolerancia)) {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla cepillado
                    $this->piezaUtilizar($cepillado->id, $meta);
                }
            }
        }
        $id_proceso = Cepillado::where('id_proceso', $id)->first();
        if ($id_proceso !== "[]") {
            $pzasCreadas = Pza_cepillado::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            for ($i = 0; $i < count($pzasCreadas); $i++) { //Recorro las piezas creadas.
                    //Acrualiza el estado correcto de la pieza.
                    if($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia) == 0 && ($pzasCreadas[$i]->error == 'Maquinado' || $pzasCreadas[$i]->error == 'Ninguno')) {
                        $pzasCreadas[$i]->error = 'Maquinado';  //Llenado de observaciones para tabla Cepillado_cnominal.
                        $pzasCreadas[$i]->correcto = 0; //Llenado de observaciones para tabla Cepillado_cnominal.
                    }else if(($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia) == 0 && $pzasCreadas[$i]->error == 'Fundicion') || ($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia) == 1 && $pzasCreadas[$i]->error == 'Fundicion')){
                        $pzasCreadas[$i]->error = 'Fundicion';
                        $pzasCreadas[$i]->correcto = 0;
                    }else{
                        $pzasCreadas[$i]->error = 'Ninguno'; 
                        $pzasCreadas[$i]->correcto = 1;
                    }
                    $pzasCreadas[$i]->save();
            }
            $pzasCorrectas = Pza_cepillado::where('id_meta', $meta->id)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas.
            $meta->resultado = $pzasCorrectas->count() / 2; //Actualización de datos en tabla Metas.
            $meta->save(); //Guardar las piezas correctas en la tabla Metas.
            if (isset($cNominal) && isset($tolerancia)) {
                $pzaUtilizar = Pza_cepillado::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Cepillado.
                    $piezasVacias = Pza_cepillado::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get(); //Obtención de todas las piezas creadas.
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
                        $this->piezaUtilizar($id_proceso->id, $meta); //Llamado a función para obtener la pieza a utilizar.
                        $pzaUtilizar = Pza_cepillado::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                    }
                }
            }
            if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Cepillado.
                return view('processes.cepillado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaUtilizar' => $pzaUtilizar]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Cepillado.
                return view('processes.cepillado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        }
    }

    public function compararDatosPieza($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->radiof_mordaza > ($cNominal->radiof_mordaza + $tolerancia->radiof_mordaza1) || $pieza->radiof_mordaza < ($cNominal->radiof_mordaza - $tolerancia->radiof_mordaza2) || $pieza->radiof_mayor > ($cNominal->radiof_mayor + $tolerancia->radiof_mayor1) || $pieza->radiof_mayor < ($cNominal->radiof_mayor - $tolerancia->radiof_mayor2) || $pieza->radiof_sufridera > ($cNominal->radiof_sufridera + $tolerancia->radiof_sufridera1) || $pieza->radiof_sufridera < ($cNominal->radiof_sufridera - $tolerancia->radiof_sufridera2) || $pieza->profuFinal_CFC > ($cNominal->profuFinal_CFC + $tolerancia->profuFinal_CFC1) || $pieza->profuFinal_CFC < ($cNominal->profuFinal_CFC - $tolerancia->profuFinal_CFC2) || $pieza->profuFinal_mitadMB  > ($cNominal->profuFinal_mitadMB  + $tolerancia->profuFinal_mitadMB1) || $pieza->profuFinal_mitadMB < ($cNominal->profuFinal_mitadMB - $tolerancia->profuFinal_mitadMB2) || $pieza->profuFinal_PCO  > ($cNominal->profuFinal_PCO  + $tolerancia->profuFinal_PCO1) || $pieza->profuFinal_PCO < ($cNominal->profuFinal_PCO - $tolerancia->profuFinal_PCO2) || $pieza->acetato_MB == "Mal" || $pieza->ensamble > ($cNominal->ensamble + $tolerancia->ensamble1) || $pieza->ensamble < ($cNominal->ensamble - $tolerancia->ensamble2) || $pieza->distancia_barrenoAli != $cNominal->distancia_barrenoAli || $pieza->profu_barrenoAli != $cNominal->profu_barrenoAli || $pieza->altura_vena != $cNominal->altura_vena || $pieza->ancho_vena != $cNominal->ancho_vena || $pieza->pin1 > ($cNominal->pin1 + $tolerancia->pin1) || $pieza->pin1 < ($cNominal->pin1 - $tolerancia->pin1) || $pieza->pin2 > ($cNominal->pin2 + $tolerancia->pin2) || $pieza->pin2 < ($cNominal->pin2 - $tolerancia->pin2)) {
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
        $id = "cepillado_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Cepillado.
        $id_proceso = Cepillado::where('id_proceso', $id)->first();;
        $cNominal = Cepillado_cnominal::where('id_proceso', "Cepillado_" . $clase->nombre . "_" . $ot->id)->first(); //Busco la meta de la OT.
        $tolerancia = Cepillado_tolerancia::where('id_proceso', "Cepillado_" . $clase->nombre . "_" . $ot->id)->first(); //Busco la meta de la OT.
        $pzasCreadas = Pza_cepillado::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
        $pzaUtilizar = Pza_cepillado::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        if (isset($request->n_pieza)) {
            for ($i = 0; $i < count($request->n_pieza); $i++) {
                $id_pieza = $request->n_pieza[$i] . $id_proceso->id; //Creación de id para tabla Cepillado_cnominal.
                $piezaExistente = Pza_cepillado::where('id_pza', $id_pieza)->first();
                if ($piezaExistente) {
                    $piezaExistente->radiof_mordaza = $request->radiof_mordaza[$i];
                    $piezaExistente->radiof_mayor = $request->radiof_mayor[$i];
                    $piezaExistente->radiof_sufridera = $request->radiof_sufridera[$i];
                    $piezaExistente->profuFinal_CFC = $request->profuFinal_CFC[$i];
                    $piezaExistente->profuFinal_mitadMB = $request->profuFinal_mitadMB[$i];
                    $piezaExistente->profuFinal_PCO = $request->profuFinal_PCO[$i];
                    $piezaExistente->acetato_MB = $request->acetato_MB[$i];
                    $piezaExistente->ensamble = $request->ensamble[$i];
                    $piezaExistente->distancia_barrenoAli = $request->distancia_barrenoAli[$i];
                    $piezaExistente->profu_barrenoAli = $request->profu_barrenoAli[$i];
                    $piezaExistente->altura_vena = $request->altura_vena[$i];
                    $piezaExistente->ancho_vena = $request->ancho_vena[$i];
                    $piezaExistente->pin1 = $request->pin1[$i];
                    $piezaExistente->pin2 = $request->pin2[$i];
                    if (isset($request->observaciones[$i])) { //Si se obtienen los datos de las piezas, se guardan en la tabla Cepillado_cnominal.
                        $piezaExistente->observaciones = $request->observaciones[$i];  //Llenado de observaciones para tabla Cepillado_cnominal.
                    }
                    $piezaExistente->save(); //Guardado de datos en la tabla Pza_cepillado.

                    //Acrualiza el estado correcto de la pieza.
                    if($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && ($request->error[$i] == "Ninguno" || $request->error[$i] == "Maquinado")) {
                        $piezaExistente->error = 'Maquinado';
                        $piezaExistente->correcto = 0;
                    }else if(($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 0 && $request->error[$i] == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia) == 1 && $request->error[$i] == 'Fundicion')){
                        $piezaExistente->error = $request->error[$i];
                        $piezaExistente->correcto = 0;
                    }else{
                        $piezaExistente->error = 'Ninguno';
                        $piezaExistente->correcto = 1;
                    }
                    $piezaExistente->save();

                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_pieza)->where('proceso', "Cepillado")->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if(!isset($pieza)){
                        $pieza = new Pieza();
                    }
                    $pieza->id_clase = $clase->id;
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $request->n_pieza[$i];
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Cepillado";
                    $pieza->error = $piezaExistente->error;
                    $pieza->save();
                }
            }
            $pzasCorrectas = Pza_cepillado::where('id_meta', $meta->id)->where('correcto', 1)->get();
            if (isset($pzasCorrectas)) { //Si existen piezas correctas, se actualiza el resultado de la meta.
                $meta->resultado = $pzasCorrectas->count() / 2; //Actualización de datos en tabla Metas.
            } else {
                $meta->resultado = 0; //Actualización de los datos en la tabla metas.
            }
            $meta->save(); //Guardado de datos en la tabla Metas.
            //Retornar la pieza siguiente
            $pzaUtilizar = Pza_cepillado::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            $pzasCreadas = Pza_cepillado::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = Cepillado_cnominal::where('id_proceso', "Cepillado_" . $clase->nombre . "_" . $ot->id)->first(); //Busco la meta de la OT.
            $tolerancia = Cepillado_tolerancia::where('id_proceso', "Cepillado_" . $clase->nombre . "_" . $ot->id)->first(); //Busco la meta de la OT.
            if (isset($pzaUtilizar)) {
                return view('processes.cepillado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaUtilizar' => $pzaUtilizar]); //Retorno a vista de Cepillado.
            } else {
                return view('processes.cepillado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase])->with('succes', 'Las piezas se han editado correctamente'); //Retorno a vista de Cepillado.
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.cepillado', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas]); //Retorno la vista de cepillado.
                    }
                }
            }
            return view('processes.cepillado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaUtilizar' => $pzaUtilizar]); //Retorno a vista de Cepillado.
        }
    }

    public function piezaUtilizar($id, $meta) //Función para obtener la pieza a utilizar.
    {
        //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla cepillado
        $pzasLibres[0] = Pza_cepillado::where('id_proceso', $id)->where('estado', 0)->first();
        if (isset($pzasLibres[0])) {
            $numPieza = str_split($pzasLibres[0]->n_pieza); //Obtención del número de piezas.
            if (count($numPieza) == 2) {
                if ($numPieza[1] == "M") { //Si la pieza es de mujer.
                    $pzasLibres[1] = Pza_cepillado::where('id_proceso', $id)->where('estado', 0)->where('n_pieza', $numPieza[0] . "H")->first();
                } else {
                    $pzasLibres[1] = Pza_cepillado::where('id_proceso', $id)->where('estado', 0)->where('n_pieza', $numPieza[0] . "M")->first();
                }
            } else if (count($numPieza) == 3) { //Si la pieza es macho.
                if ($numPieza[2] == "M") {
                    $pzasLibres[1] = Pza_cepillado::where('id_proceso', $id)->where('estado', 0)->where('n_pieza', $numPieza[0] . $numPieza[1] . "H")->first();
                } else {
                    $pzasLibres[1] = Pza_cepillado::where('id_proceso', $id)->where('estado', 0)->where('n_pieza', $numPieza[0] . $numPieza[1] . "M")->first();
                }
            } else if (count($numPieza) == 4) { //Si la pieza es macho.
                if ($numPieza[3] == "M") {
                    $pzasLibres[1] = Pza_cepillado::where('id_proceso', $id)->where('estado', 0)->where('n_pieza', $numPieza[0] . $numPieza[1] . $numPieza[2] . "H")->first();
                } else {
                    $pzasLibres[1] = Pza_cepillado::where('id_proceso', $id)->where('estado', 0)->where('n_pieza', $numPieza[0] . $numPieza[1] . $numPieza[2] . "M")->first();
                }
            }
            for ($i = 0; $i < 2; $i++) { //Recorro las pieza.
                Pza_cepillado::where('id_pza', $pzasLibres[$i]->id_pza)->update([
                    'estado' => 1, //Llenado de estado para tabla Pza_cepillado.
                    'id_meta' => $meta->id, //Llenado de la id_meta para pieza a utilizar.
                ]);
            }
        }
    }

    public function createPiezas($id, $numPiezas, $inicio)
    { //Función para crear las piezas en la tabla Pza_cepillado.
        if (!is_int($numPiezas)) { //Si el número de piezas es decimal.
            $numPiezas += .5; //Se suma .5 para que se creen las piezas de hombre y mujer.
        }
        for ($i = $inicio; $i <= $numPiezas; $i++) { //Recorro las piezas creadas.
            for ($j = 0; $j < 2; $j++) { //Recorro las piezas creadas.
                $pieza = new Pza_cepillado(); //Creación de objeto para llenar tabla Cepillado_cnominal.
                $pieza->id_proceso = $id; //Llenado de id_proceso para tabla Cepillado_cnominal.
                if ($j == 0) { //Si la pieza es de hombre.
                    $pieza->n_pieza = $i . 'H'; //Llenado de id_proceso para tabla Cepillado_cnominal.
                } else {
                    $pieza->n_pieza = $i . 'M'; //Llenado de id_proceso para tabla Cepillado_cnominal.
                }
                $pieza->id_pza = $pieza->n_pieza . $pieza->id_proceso; //Creación de id para tabla Cepillado_cnominal.
                $pieza->save(); //Guardado de datos en tabla Cepillado_cnominal.
            }
        }
    }
}
