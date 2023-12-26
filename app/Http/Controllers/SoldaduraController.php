<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\Soldadura;
use App\Models\Soldadura_pza;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SoldaduraController extends Controller
{
    public function show()
    {
        $ot = Orden_trabajo::all(); //Obtención de todas las ordenes de trabajo.
        if (count($ot) != 0) {
            $oTrabajo = array(); //Declara arreglo para guardar las ordenes de trabajo disponibles en Soldadura
            //Recorre todas las ordenes de trabajo.
            foreach ($ot as $ot) {
                $contador = 0; //Contador para verificar que existan clases que pasaran por Soldadura
                $clases = Clase::where('id_ot', $ot->id)->get();
                //Recorre todas las clases registradas en la orden de trabajo.
                foreach ($clases as $clase) {
                    $proceso = Procesos::where('id_clase', $clase->id)->first(); //Obtención del proceso de la clase.
                    if ($proceso) {
                        if ($proceso->soldadura) { //Si existen maquinas en Soldadura de esa clase, se almacena en el arreglo que se pasara a la vista
                            $contador++;
                        }
                    }
                }
                //Si hay clases que pasaran por Barreno maniobra, se almacena la orden de trabajo en el arreglo.
                if ($contador != 0) {
                    array_push($oTrabajo, $ot);
                }
            }
            if (count($oTrabajo) != 0) {
                return view('processes.soldadura', ['ot' => $oTrabajo]); //Retorno a vista de Soldadura
            }
            //Se retorna a la vista de Cepillado con las ordenes de trabajo que tienen clases que pasaran por Soldadura
            return view('processes.soldadura', ['ot']); //Retorno a vista de Soldadura
        }
        return view('processes.soldadura');
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
        $id = "soldadura_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Soldadura
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $id_proceso = Soldadura::where('id_proceso', $id)->first();

        if (isset($request->n_pieza)) {  //Si se obtienen los datos de las piezas, se guardan en la tabla Soldadura_cnominal.
            $id_pieza = $request->n_pieza . $id_proceso->id; //Creación de id para tabla Soldadura_cnominal.
            $piezaExistente = Soldadura_pza::where('id_pza', $id_pieza)->first();
            if ($piezaExistente) {
                $piezaExistente->pesoxpieza = $request->pesoxpieza;
                $piezaExistente->temperatura_precalentado = $request->temperatura_precalentado;
                $piezaExistente->tiempo_aplicacion = $request->tiempo_aplicacion;
                $piezaExistente->tipo_soldadura = $request->tipo_soldadura;
                $piezaExistente->lote = $request->lote;
                $piezaExistente->error = $request->error;
                $piezaExistente->observaciones = $request->observaciones;
                $piezaExistente->estado = 2;
                $piezaExistente->save();

                $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Soldadura')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if (!$pieza) {
                    $pieza = new Pieza();
                }
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id;
                $pieza->n_pieza = $request->n_pieza;
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina;
                $pieza->proceso = "Soldadura";
                $pieza->error = $piezaExistente->error;
                $pieza->save();

                //Actualizar resultado de la meta
                $pzasMeta = Soldadura_pza::where('id_meta', $meta->id)->where('estado', 2)->where('error', "Ninguno")->get(); //Obtención de todas las piezas correctas.
                Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                    'resultado' => $pzasMeta->count(),
                ]);
                $meta = Metas::find($meta->id); //Busco la meta de la OT.

                //Retornar la pieza siguiente
                $pzaUtilizar = Soldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Soldadura
                    $pzasCreadas = Soldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.soldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))]); //Retorno a vista de Cepillado.
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla Soldadura
                    $this->piezaUtilizar($ot->id, $clase);
                }
            }
        } else if (isset($request->n_juegoElegido)) {
            $juegoExiste = Soldadura_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->first();
            if (!$juegoExiste) {
                $newPza = new Soldadura_pza(); //Creación de objeto para llenar tabla Soldadura
                $newPza->id_pza = $request->n_juegoElegido . $id_proceso->id; //Creación de id para tabla desbaste.
                $newPza->id_meta = $meta->id; //Llenado de id_meta para tabla Soldadura
                $newPza->id_proceso = $id_proceso->id; //Llenado de id_proceso para tabla Soldadura
                $newPza->estado = 1; //Llenado de estado para tabla Soldadura
                $newPza->n_juego = $request->n_juegoElegido; //Llenado de estado para tabla Soldadura
                $newPza->save(); //Guardado de datos en la tabla Soldadura
            }
        } else {
            $proceso = Soldadura::where('id_proceso', $id)->first(); //Busco el proceso de la OT.
            if (!$proceso) {
                //Llenado de la tabla Soldadura.
                $soldadura = new Soldadura(); //Creación de objeto para llenar tabla Soldadura
                $soldadura->id_proceso = $id; //Creación de id para tabla Soldadura.
                $soldadura->id_ot = $ot->id; //Llenado de id_proceso para tabla Soldadura.
                $soldadura->save(); //Guardado de datos en la tabla Soldadura.
            }
        }
        $id_proceso = Soldadura::where('id_proceso', $id)->first();
        if ($id_proceso !== "[]") {
            $pzasCreadas = Soldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.

            //Actualizar resultado de la meta
            $pzasMeta = Soldadura_pza::where('id_meta', $meta->id)->where('estado', 2)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.


            $pzaUtilizar = Soldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Soldadura
                $piezasVacias = Soldadura_pza::where('pesoxpieza', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Soldadura.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Soldadura.
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
                if (isset($pzasUtilizar)) { //Si no se encontro una pieza para utilizar, se crea una nueva pieza.
                    return view('processes.soldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de Cepillado.
                } else {
                    return view('processes.soldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
                }
            } else {
                return view('processes.soldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        }
    }
    public function edit(Request $request)
    {
        $meta = Metas::find($request->metaData); //Busco la meta de la OT.
        $ot = Orden_trabajo::find($meta->id_ot); //Obtención de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $clase = Clase::find($meta->id_clase); //Busco la clase de la OT.
        $id = "soldadura_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Soldadura
        $id_proceso = Soldadura::where('id_proceso', $id)->first();
        $pzasCreadas = Soldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
        $pzaUtilizar = Soldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        if (isset($request->n_pieza)) { //Si se obtienen los datos de las piezas, se guardan en la tabla Soldadura_cnominal.
            for ($i = 0; $i < count($request->n_pieza); $i++) {
                $id_pieza = $request->n_pieza[$i] . $id_proceso->id; //Creación de id para tabla Soldadura_cnominal.
                $piezaExistente = Soldadura_pza::where('id_pza', $id_pieza)->first();
                if ($piezaExistente) {
                    $piezaExistente->pesoxpieza = $request->pesoxpieza[$i];
                    $piezaExistente->temperatura_precalentado = $request->temperatura_precalentado[$i];
                    $piezaExistente->tiempo_aplicacion = $request->tiempo_aplicacion[$i];
                    $piezaExistente->tipo_soldadura = $request->tipo_soldadura[$i];
                    $piezaExistente->lote = $request->lote[$i];
                    $piezaExistente->error = $request->error[$i];
                    if (isset($request->observaciones[$i])) { //Si se obtienen los datos de las piezas, se guardan en la tabla Soldadura_cnominal.
                        $piezaExistente->observaciones = $request->observaciones[$i];  //Llenado de observaciones para tabla Soldadura_cnominal.
                    }
                    $piezaExistente->save();
                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Soldadura')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if (!isset($pieza)) {
                        $pieza = new Pieza(); //Creación del obejeto para llenar la tabla pieza.
                    }
                    $pieza->id_clase = $clase->id; //Lenado de id_clase para la tabla pieza.
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $piezaExistente->n_juego;
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Soldadura";
                    $pieza->error = $piezaExistente->error;
                    $pieza->save();
                }
            }
            //Actualizar resultado de la meta
            $pzasMeta = Soldadura_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.

            //Retornar la pieza siguiente
            $pzaUtilizar = Soldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Soldadura
                $piezasVacias = Soldadura_pza::where('pesoxpieza', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Soldadura.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Soldadura.
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
            $pzasCreadas = Soldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Soldadura
                return view('processes.soldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Soldadura
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.soldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.soldadura', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'nPiezas' => $pzasCreadas, 'juegos' => count($this->piezaUtilizar($ot->id, $clase))]); //Retorno la vista de cepillado.
                    }
                }
            }
            $pzaUtilizar = Soldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Soldadura
                $piezasVacias = Soldadura_pza::where('pesoxpieza', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Soldadura.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Soldadura.
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
            $pzasCreadas = Soldadura_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Soldadura
                return view('processes.soldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'juegos' => count($pzasUtilizar)]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Soldadura
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.soldadura', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'juegos' => count($pzasUtilizar)])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        }
    }

    public function piezaUtilizar($ot, $clase) //Función para obtener la pieza a utilizar.
    {
        $pzasUtilizar = array();
        $pzasGuardadas = array();
        $procesos = Procesos::where('id_clase', $clase->id)->first();

        //Obtener las piezas que esten terminadas y correctas en la tabla Soldadura para despues comparar cada una con su consecuente y asi armar los juegos
        $id_proceso = "soldadura_" . $clase->nombre . "_" . $ot;
        $proceso = Soldadura::where('id_proceso', $id_proceso)->first();
        $pzasOcupadas = Soldadura_pza::where('id_proceso', $proceso->id)->where('estado', 1)->get(); //Obtención de todas las piezas creadas.
        if ($proceso) {
            $pzasUsadas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Soldadura')->get(); //Obtención de todas las piezas creadas en Soldadura
        }

        if ($procesos->sOperacion != 0) {
            //Obtener las piezas solamente en el proceso de Segunda operacion
            $pzasEncontradas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Segunda Operacion Soldadura')->get();
            $this->piezasEncontradas($ot, $clase, $pzasEncontradas, $pzasUtilizar, $pzasGuardadas, 'Segunda Operacion Soldadura', $pzasUsadas, $pzasOcupadas);
        }
        return $pzasUtilizar;
    }
    public function piezasEncontradas($ot, $clase, $pzasEncontradas, &$pzasUtilizar, &$pzasGuardadas, $nameProceso, $pzasUsadas, $pzasOcupadas)
    {
        $numero = "";
        for ($i = 0; $i < count($pzasEncontradas); $i++) { //Recorro las piezas encontradas de Soldadura
            if (array_search($pzasEncontradas[$i]->n_pieza, $pzasGuardadas) == false) {
                if ($pzasEncontradas[$i]->error == "Ninguno") {
                    $numerosUsados = array();
                    if (count($pzasUsadas) > 0) {
                        $numeroUsado = "";
                        for ($x = 0; $x < count($pzasUsadas); $x++) {
                            $pzaDividida_Usada = str_split($pzasUsadas[$x]->n_pieza); //División del número de pieza usada.
                            for ($h = 0; $h < count($pzaDividida_Usada) - 1; $h++) { //Recorro el número de pieza usada.
                                $numeroUsado .= $pzaDividida_Usada[$h]; //Obtención del número de pieza usada.
                            }
                            array_push($numerosUsados, $numeroUsado); //Guardo el número de pieza usada.
                            $numeroUsado = ""; //Reinicio la variable.
                        } //Recorro las piezas ocupadas en Soldadura
                    }
                    if (count($pzasOcupadas) > 0) {
                        $numeroUsado = ""; //Reinicio la variable.
                        for ($x = 0; $x < count($pzasOcupadas); $x++) {
                            $n_piezaUsada = $pzasOcupadas[$x]->n_juego; //Obtención del número de pieza ocupada
                            $pzaDividida_Usada = str_split($n_piezaUsada);
                            for ($h = 0; $h < count($pzaDividida_Usada) - 1; $h++) {
                                $numeroUsado .= $pzaDividida_Usada[$h];
                            }
                            array_push($numerosUsados, $numeroUsado);
                            $numeroUsado = "";
                        }
                    }
                    $n_pieza = $pzasEncontradas[$i]->n_pieza; //Obtención del número de pieza.
                    $piezaDividida = str_split($n_pieza); //División del número de pieza.
                    for ($j = 0; $j < count($piezaDividida) - 1; $j++) { //Recorro el número de pieza.
                        $numero .= $piezaDividida[$j]; //Obtención del número de pieza.
                    }
                    //Se hace la condicion para saber si el numero de la pieza se encuentra ya usada.
                    if (array_search($numero, $numerosUsados) === false) {
                        if (mb_substr($n_pieza, -1, null, 'UTF-8') == "M") { //Si la pieza es macho, se busca la pieza hembra.
                            $pieza = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', $nameProceso)->where('n_pieza', $numero . "H")->where('error', 'Ninguno')->first(); //Busco la pieza hembra.
                        } else {
                            $pieza = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', $nameProceso)->where('n_pieza', $numero . "M")->where('error', 'Ninguno')->first(); //Busco la pieza macho.
                        }
                        if (isset($pieza)) {
                            array_push($pzasUtilizar, $numero . "J"); //Guardo el número de pieza.
                            array_push($pzasGuardadas, $pzasEncontradas[$i]->n_pieza); //Guardo el número de pieza.
                            array_push($pzasGuardadas, $pieza->n_pieza); //Guardo el número de pieza.
                        }
                    }
                    $numero = "";
                } else {
                    $numeroDiv = str_split($pzasEncontradas[$i]->n_pieza);
                    for ($l = 0; $l < count($numeroDiv) - 1; $l++) {
                        $numero .= $numeroDiv[$l];
                    }
                    array_push($pzasGuardadas, $numero . "H");
                    array_push($pzasGuardadas, $numero . "M");
                    $numero = "";
                }
            }
        }
    }
}
