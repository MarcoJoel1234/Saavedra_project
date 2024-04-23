<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Copiado;
use App\Models\Copiado_cnominal;
use App\Models\Copiado_pza;
use App\Models\Copiado_tolerancia;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CopiadoController extends Controller
{
    protected $controladorPzasLiberadas;
    public function __construct()
    {
        $this->controladorPzasLiberadas = new PzasLiberadasController();
    }
    public function show($error)
    {
        $ot = Orden_trabajo::all(); //Obtención de todas las ordenes de trabajo.
        if (count($ot) != 0) {
            $oTrabajo = array(); //Declara arreglo para guardar las ordenes de trabajo disponibles en Copiado
            //Recorre todas las ordenes de trabajo.
            foreach ($ot as $ot) {
                $contador = 0; //Contador para verificar que existan clases que pasaran por Copiado
                $clases = Clase::where('id_ot', $ot->id)->get();
                //Recorre todas las clases registradas en la orden de trabajo.
                foreach ($clases as $clase) {
                    $proceso = Procesos::where('id_clase', $clase->id)->first(); //Obtención del proceso de la clase.
                    if ($proceso) {
                        if ($proceso->copiado) { //Si existen maquinas en Copiado de esa clase, se almacena en el arreglo que se pasara a la vista
                            $contador++;
                        }
                    }
                }
                //Si hay clases que pasaran por Copiado, se almacena la orden de trabajo en el arreglo.
                if ($contador != 0) {
                    array_push($oTrabajo, $ot);
                }
            }
            //Si hay clases que pasaran por Primera operación soldadura, se almacena la orden de trabajo en el arreglo.
            if (count($oTrabajo) != 0) {
                if ($error == 1) {
                    return view('processes.copiado', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Desbaste exterior
                }
                return view('processes.copiado', ['ot' => $oTrabajo]); //Retorno a vista de Desbaste exterior
            }
            if ($error == 1) {
                return view('processes.copiado', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Desbaste exterior
            }
            //Se retorna a la vista de Primera operación soldadura con las ordenes de trabajo que tienen clases que pasaran por Desbaste exterior
            return view('processes.copiado', ['ot']); //Retorno a vista de Desbaste exterior
        }
        if ($error == 1) {
            return view('processes.copiado', ['error' => $error]); //Retorno a vista de Desbaste exterior
        }
        return view('processes.copiado');
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
        $id = "copiado_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Copiado
        $cNominal = Copiado_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = Copiado_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $proceso = Copiado::where('id_proceso', $id)->first(); //Busco el proceso de la OT.
        if (!$proceso) {
            //Llenado de la tabla Copiado
            $copiado = new Copiado(); //Creación de objeto para llenar tabla Copiado
            $copiado->id_proceso = $id; //Creación de id para tabla Copiado
            $copiado->id_ot = $ot->id; //Llenado de id_proceso para tabla Copiado
            $copiado->save(); //Guardado de datos en la tabla Copiado
        }
        $id_proceso = Copiado::where('id_proceso', $id)->first();
        $pzasCopiado = Copiado_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
        $pzasCavidades = Pieza::where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('proceso', 'Cavidades')->where('error', 'Ninguno')->get();
        $pzasRestantes = $this->piezasRestantes($pzasCavidades, $pzasCopiado);
        if (isset($request->n_pieza)) {  //Si se obtienen los datos de las piezas, se guardan en la tabla Copiado_cnominal.
            $id_pieza = $request->n_pieza . $id_proceso->id; //Creación de id para tabla Copiado_cnominal.
            $piezaExistente = Copiado_pza::where('id_pza', $id_pieza)->first();
            if ($piezaExistente) {
                $piezaExistente->diametro1_cilindrado = $request->diametro1_cilindrado;
                $piezaExistente->profundidad1_cilindrado = $request->profundidad1_cilindrado;
                $piezaExistente->diametro2_cilindrado = $request->diametro2_cilindrado;
                $piezaExistente->profundidad2_cilindrado = $request->profundidad2_cilindrado;
                $piezaExistente->diametro_sufridera = $request->diametro_sufridera;
                $piezaExistente->diametro_ranura = $request->diametro_ranura;
                $piezaExistente->profundidad_ranura = $request->profundidad_ranura;
                $piezaExistente->profundidad_sufridera = $request->profundidad_sufridera;
                $piezaExistente->altura_total = $request->altura_total;
                $piezaExistente->diametro1_cavidades = $request->diametro1_cavidades;
                $piezaExistente->profundidad1_cavidades = $request->profundidad1_cavidades;
                $piezaExistente->diametro2_cavidades = $request->diametro2_cavidades;
                $piezaExistente->profundidad2_cavidades = $request->profundidad2_cavidades;
                $piezaExistente->diametro3 = $request->diametro3;
                $piezaExistente->profundidad3 = $request->profundidad3;
                $piezaExistente->diametro4 = $request->diametro4;
                $piezaExistente->profundidad4 = $request->profundidad4;
                $piezaExistente->volumen = $request->volumen;
                $piezaExistente->observaciones_cilindrado = $request->observaciones_cilindrado;
                $piezaExistente->observaciones_cavidades = $request->observaciones_cavidades;
                $piezaExistente->estado = 2;
                $piezaExistente->save();

                //Actualiza el estado correcto de la pieza en cilindrado.
                if ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia)[0] == 0 && $request->error_cilindrado == 0) {
                    $piezaExistente->error_cilindrado = 'Maquinado';
                } else if (($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia)[0] == 0 && $request->error_cilindrado == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia)[0] == 1 && $request->error_cilindrado == 'Fundicion')) {
                    $piezaExistente->error_cilindrado = $request->error_cilindrado;
                } else {
                    $piezaExistente->error_cilindrado = 'Ninguno';
                }

                if ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia)[1] == 0 && $request->error_cavidades == 0) {
                    $piezaExistente->error_cavidades = 'Maquinado';
                } else if (($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia)[1] == 0 && $request->error_cavidades == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia)[1] == 1 && $request->error_cavidades == 'Fundicion')) {
                    $piezaExistente->error_cavidades = $request->error_cavidades;
                } else {
                    $piezaExistente->error_cavidades = 'Ninguno';
                }
                $piezaExistente->save();

                $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Copiado')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if (!isset($pieza)) {
                    $pieza = new Pieza();
                }
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id;
                $pieza->n_pieza = $request->n_pieza;
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina;
                $pieza->proceso = "Copiado";
                if ($piezaExistente->error_cilindrado == "Ninguno" && $piezaExistente->error_cavidades == "Ninguno") {
                    $pieza->error = "Ninguno";
                } else {
                    if ($piezaExistente->error_cilindrado == "Ninguno") {
                        $pieza->error = $piezaExistente->error_cavidades;
                    } else {
                        $pieza->error = $piezaExistente->error_cilindrado;
                    }
                }
                $pieza->save();
                if ($pieza->error == 'Ninguno') {
                    //Obtener piezas de la meta
                    $piezasMeta = Copiado_pza::where('id_meta', $meta->id)->get();
                    $this->controladorPzasLiberadas->liberarPiezasMeta($meta, $piezasMeta, $pieza->n_pieza, "Copiado");
                }

                //Actualizar resultado de la meta
                $pzasCorrectas = Copiado_pza::where('id_meta', $meta->id)->where('error_cilindrado', 'Ninguno')->where('error_cavidades', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
                Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                    'resultado' => $pzasCorrectas->count(),
                ]);
                $meta = Metas::find($meta->id); //Busco la meta de la OT.
                //  //Retornar la pieza siguiente
                $pzasCopiado = Copiado_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
                $pzasCavidades = Pieza::where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('proceso', 'Cavidades')->where('error', 'Ninguno')->get();
                $pzasRestantes = $this->piezasRestantes($pzasCavidades, $pzasCopiado);
                $pzaUtilizar = Copiado_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Copiado
                    $pzasCreadas = Copiado_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.copiado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'nPiezasCavi' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Copiado
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla Copiado
                    $this->piezaUtilizar($ot->id, $clase);
                }
            }
        } else if (isset($request->n_juegoElegido)) {
            $juegoExiste = Copiado_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->first();
            if (!$juegoExiste) {
                $newPza = new Copiado_pza(); //Creación de objeto para llenar tabla Copiado
                $newPza->id_pza = $request->n_juegoElegido . $id_proceso->id; //Creación de id para tabla Copiado
                $newPza->id_meta = $meta->id; //Llenado de id_meta para tabla Copiado
                $newPza->id_proceso = $id_proceso->id; //Llenado de id_proceso para tabla Copiado
                $newPza->estado = 1; //Llenado de estado para tabla Copiado
                $newPza->n_juego = $request->n_juegoElegido; //Llenado de estado para tabla Copiado
                $newPza->save(); //Guardado de datos en la tabla Copiado
            }
        }
        $id_proceso = Copiado::where('id_proceso', $id)->first();
        if ($id_proceso !== "[]") {
            $pzasCreadas = Copiado_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            for ($i = 0; $i < count($pzasCreadas); $i++) { //Recorro las piezas creadas.
                //Acrualiza el estado correcto de la pieza.
                //Acrualiza el estado correcto de la pieza en cilindrado.
                if ($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia)[0] == 0 && $request->error_cilindrado == 0) {
                    $pzasCreadas[$i]->error_cilindrado = 'Maquinado';
                } else if (($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia)[0] == 0 && $request->error_cilindrado == 'Fundicion') || ($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia)[0] == 1 && $request->error_cilindrado == 'Fundicion')) {
                    $pzasCreadas[$i]->error_cilindrado = $request->error_cilindrado;
                } else {
                    $pzasCreadas[$i]->error_cilindrado = 'Ninguno';
                }

                if ($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia)[1] == 0 && $request->error_cavidades == 0) {
                    $pzasCreadas[$i]->error_cavidades = 'Maquinado';
                } else if (($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia)[1] == 0 && $request->error_cavidades == 'Fundicion') || ($this->compararDatosPieza($pzasCreadas[$i], $cNominal, $tolerancia)[1] == 1 && $request->error_cavidades == 'Fundicion')) {
                    $pzasCreadas[$i]->error_cavidades = $request->error_cavidades;
                } else {
                    $pzasCreadas[$i]->error_cavidades = 'Ninguno';
                }
                $pzasCreadas[$i]->save();
            }

            //Actualizar resultado de la meta
            $pzasMeta = Copiado_pza::where('id_meta', $meta->id)->where('error_cilindrado', 'Ninguno')->where('error_cavidades', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.

            if (isset($cNominal) && isset($tolerancia)) {
                $pzaUtilizar = Copiado_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Copiado
                    $piezasVacias = Copiado_pza::where('error_cilindrado', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                    if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                        for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                            $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                            if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                                $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Copiado.
                                $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Copiado.
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
                    return view('processes.copiado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'nPiezasCavi' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Copiado
                } else {
                    return view('processes.copiado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'nPiezasCavi' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Copiado
                }
            } else {
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.copiado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'nPiezasCavi' => $pzasCreadas, 'clase' => $clase, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Copiado
            }
        }
    }
    public function compararDatosPieza($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        $subprocesos = array();
        if ($pieza->diametro1_cilindrado > ($cNominal->diametro1_cilindrado + $tolerancia->diametro1_cilindrado) || $pieza->diametro1_cilindrado < ($cNominal->diametro1_cilindrado - $tolerancia->diametro1_cilindrado) || $pieza->profundidad1_cilindrado > ($cNominal->profundidad1_cilindrado + $tolerancia->profundidad1_cilindrado) || $pieza->profundidad1_cilindrado < ($cNominal->profundidad1_cilindrado - $tolerancia->profundidad1_cilindrado) || $pieza->diametro2_cilindrado > ($cNominal->diametro2_cilindrado + $tolerancia->diametro2_cilindrado) || $pieza->diametro2_cilindrado < ($cNominal->diametro2_cilindrado - $tolerancia->diametro2_cilindrado) || $pieza->profundidad2_cilindrado > ($cNominal->profundidad2_cilindrado + $tolerancia->profundidad2_cilindrado) || $pieza->profundidad2_cilindrado < ($cNominal->profundidad2_cilindrado - $tolerancia->profundidad2_cilindrado) || $pieza->diametro_sufridera > ($cNominal->diametro_sufridera + $tolerancia->diametro_sufridera) || $pieza->diametro_sufridera < ($cNominal->diametro_sufridera - $tolerancia->diametro_sufridera) || $pieza->diametro_ranura > ($cNominal->diametro_ranura + $tolerancia->diametro_ranura) || $pieza->diametro_ranura < ($cNominal->diametro_ranura - $tolerancia->diametro_ranura) || $pieza->profundidad_ranura > ($cNominal->profundidad_ranura + $tolerancia->profundidad_ranura) || $pieza->profundidad_ranura < ($cNominal->profundidad_ranura - $tolerancia->profundidad_ranura) || $pieza->profundidad_sufridera > ($cNominal->profundidad_sufridera + $tolerancia->profundidad_sufridera) || $pieza->profundidad_sufridera < ($cNominal->profundidad_sufridera - $tolerancia->profundidad_sufridera) || $pieza->altura_total > ($cNominal->altura_total + $tolerancia->altura_total) || $pieza->altura_total < ($cNominal->altura_total - $tolerancia->altura_total)) {
            $subprocesos[0] = 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            $subprocesos[0] = 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }

        if ($pieza->diametro1_cavidades > ($cNominal->diametro1_cavidades + $tolerancia->diametro1_cavidades) || $pieza->diametro1_cavidades < ($cNominal->diametro1_cavidades - $tolerancia->diametro1_cavidades) || $pieza->profundidad1_cavidades > ($cNominal->profundidad1_cavidades + $tolerancia->profundidad1_cavidades) || $pieza->profundidad1_cavidades < ($cNominal->profundidad1_cavidades - $tolerancia->profundidad1_cavidades) || $pieza->diametro2_cavidades > ($cNominal->diametro2_cavidades + $tolerancia->diametro2_cavidades) || $pieza->diametro2_cavidades < ($cNominal->diametro2_cavidades - $tolerancia->diametro2_cavidades) || $pieza->profundidad2_cavidades > ($cNominal->profundidad2_cavidades + $tolerancia->profundidad2_cavidades) || $pieza->profundidad2_cavidades < ($cNominal->profundidad2_cavidades - $tolerancia->profundidad2_cavidades) || $pieza->diametro3 > ($cNominal->diametro3 + $tolerancia->diametro3) || $pieza->diametro3 < ($cNominal->diametro3 - $tolerancia->diametro3) || $pieza->profundidad3 > ($cNominal->profundidad3 + $tolerancia->profundidad3) || $pieza->profundidad3 < ($cNominal->profundidad3 - $tolerancia->profundidad3) || $pieza->diametro4 > ($cNominal->diametro4 + $tolerancia->diametro4) || $pieza->diametro4 < ($cNominal->diametro4 - $tolerancia->diametro4) || $pieza->profundidad4 > ($cNominal->profundidad4 + $tolerancia->profundidad4) || $pieza->profundidad4 < ($cNominal->profundidad4 - $tolerancia->profundidad4) || $pieza->volumen > ($cNominal->volumen + $tolerancia->volumen) || $pieza->volumen < ($cNominal->volumen - $tolerancia->volumen)) {
            $subprocesos[1] = 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            $subprocesos[1] = 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
        return $subprocesos; //Retorno de datos.
    }
    public function piezasRestantes($pzasProcesoA, $pzasProcesoB)
    {
        $pzasRestantes = count($pzasProcesoA) - count($pzasProcesoB);
        return $pzasRestantes;
    }
    public function edit(Request $request)
    {
        $meta = Metas::find($request->metaData); //Busco la meta de la OT.
        $ot = Orden_trabajo::find($meta->id_ot); //Obtención de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $clase = Clase::find($meta->id_clase); //Busco la clase de la OT.
        $id = "copiado_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Copiado
        $id_proceso = Copiado::where('id_proceso', $id)->first();;
        $cNominal = Copiado_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = Copiado_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $pzasCreadas = Copiado_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
        $pzaUtilizar = Copiado_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        $pzasCopiado = Copiado_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
        $pzasCavidades = Pieza::where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('proceso', 'Cavidades')->where('error', 'Ninguno')->get();
        $pzasRestantes = $this->piezasRestantes($pzasCavidades, $pzasCopiado);
        if (isset($request->n_piezaCavi)) { //Si se obtienen los datos de las piezas, se guardan en la tabla Copiado_cnominal.
            for ($i = 0; $i < count($request->n_piezaCavi); $i++) {
                $id_pieza = $request->n_piezaCavi[$i] . $id_proceso->id; //Creación de id para tabla Copiado_cnominal.
                $piezaExistente = Copiado_pza::where('id_pza', $id_pieza)->first();
                if ($piezaExistente) {
                    $piezaExistente->diametro1_cilindrado = $request->diametro1_cilindrado[$i];
                    $piezaExistente->profundidad1_cilindrado = $request->profundidad1_cilindrado[$i];
                    $piezaExistente->diametro2_cilindrado = $request->diametro2_cilindrado[$i];
                    $piezaExistente->profundidad2_cilindrado = $request->profundidad2_cilindrado[$i];
                    $piezaExistente->diametro_sufridera = $request->diametro_sufridera[$i];
                    $piezaExistente->diametro_ranura = $request->diametro_ranura[$i];
                    $piezaExistente->profundidad_ranura = $request->profundidad_ranura[$i];
                    $piezaExistente->profundidad_sufridera = $request->profundidad_sufridera[$i];
                    $piezaExistente->altura_total = $request->altura_total[$i];
                    $piezaExistente->diametro1_cavidades = $request->diametro1_cavidades[$i];
                    $piezaExistente->profundidad1_cavidades = $request->profundidad1_cavidades[$i];
                    $piezaExistente->diametro2_cavidades = $request->diametro2_cavidades[$i];
                    $piezaExistente->profundidad2_cavidades = $request->profundidad2_cavidades[$i];
                    $piezaExistente->diametro3 = $request->diametro3[$i];
                    $piezaExistente->profundidad3 = $request->profundidad3[$i];
                    $piezaExistente->diametro4 = $request->diametro4[$i];
                    $piezaExistente->profundidad4 = $request->profundidad4[$i];
                    $piezaExistente->volumen = $request->volumen[$i];
                    if (isset($request->observaciones_cilindrado[$i])) { //Si se obtienen los datos de las piezas, se guardan en la tabla Copiado_cnominal.
                        $piezaExistente->observaciones_cilindrado = $request->observaciones_cilindrado[$i];  //Llenado de observaciones para tabla Copiado_cnominal.
                    }
                    if (isset($request->observaciones_cavidades[$i])) { //Si se obtienen los datos de las piezas, se guardan en la tabla Copiado_cnominal.
                        $piezaExistente->observaciones_cavidades = $request->observaciones_cavidades[$i];  //Llenado de observaciones para tabla Copiado_cnominal.
                    }
                    $piezaExistente->save(); //Guardado de datos en la tabla Pza_Copiado

                    //Actualiza el estado correcto de la pieza en cilindrado.
                    if ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia)[0] == 0 && $request->error_cilindrado[$i] == "Ninguno") {
                        $piezaExistente->error_cilindrado = 'Maquinado';
                    } else if (($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia)[0] == 0 && $request->error_cilindrado[$i] == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia)[0] == 1 && $request->error_cilindrado[$i] == 'Fundicion')) {
                        $piezaExistente->error_cilindrado = $request->error_cilindrado[$i];
                    } else {
                        $piezaExistente->error_cilindrado = 'Ninguno';
                    }

                    if ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia)[1] == 0 && $request->error_cavidades[$i] == "Ninguno") {
                        $piezaExistente->error_cavidades = 'Maquinado';
                    } else if (($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia)[1] == 0 && $request->error_cavidades[$i] == 'Fundicion') || ($this->compararDatosPieza($piezaExistente, $cNominal, $tolerancia)[1] == 1 && $request->error_cavidades[$i] == 'Fundicion')) {
                        $piezaExistente->error_cavidades = $request->error_cavidades[$i];
                    } else {
                        $piezaExistente->error_cavidades = 'Ninguno';
                    }
                    $piezaExistente->save();

                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Copiado')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if (!isset($pieza)) {
                        $pieza = new Pieza();
                    }
                    $pieza->id_clase = $clase->id;
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $piezaExistente->n_juego;
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Copiado";
                    if ($piezaExistente->error_cilindrado == "Ninguno" && $piezaExistente->error_cavidades == "Ninguno") {
                        $pieza->error = "Ninguno";
                    } else {
                        if ($piezaExistente->error_cilindrado == "Ninguno") {
                            $pieza->error = $piezaExistente->error_cavidades;
                        } else {
                            $pieza->error = $piezaExistente->error_cilindrado;
                        }
                    }
                    $pieza->save();
                    if ($pieza->error == 'Ninguno') {
                        //Obtener piezas de la meta
                        $piezasMeta = Copiado_pza::where('id_meta', $meta->id)->get();
                        $this->controladorPzasLiberadas->liberarPiezasMeta($meta, $piezasMeta, $pieza->n_pieza, "Copiado");
                    }
                }
            }
            //Actualizar resultado de la meta
            $pzasMeta = Copiado_pza::where('id_meta', $meta->id)->where('error_cilindrado', 'Ninguno')->where('error_cavidades', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.
            //Retornar la pieza siguiente
            $pzaUtilizar = Copiado_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Copiado
                $piezasVacias = Copiado_pza::where('error_cilindrado', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Copiado.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Copiado.
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
            $pzasCreadas = Copiado_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = Copiado_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = Copiado_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Copiado
                return view('processes.copiado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'nPiezasCavi' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Copiado
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Copiado
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.copiado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'nPiezasCavi' => $pzasCreadas,  'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Copiado
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.copiado', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'nPiezasCavi' => $pzasCreadas, 'pzasRestantes' => $pzasRestantes]); //Retorno la vista de Copiado
                    }
                }
            }
            $pzaUtilizar = Copiado_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Cavidades
                $piezasVacias = Copiado_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_Copiado.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_Copiado.
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
            $pzasCreadas = Copiado_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = Copiado_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = Copiado_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Copiado
                return view('processes.copiado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'nPiezasCavi' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Copiado
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Copiado
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.copiado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'nPiezasCavi' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Copiado
            }
        }
    }

    public function piezaUtilizar($ot, $clase) //Función para obtener la pieza a utilizar.
    {
        $pzasUtilizar = array();
        $pzasGuardadas = array();
        $procesos = Procesos::where('id_clase', $clase->id)->first();

        //Obtener las piezas que esten terminadas y correctas en la tabla Cavidades para despúes comparar cada una con su consecuente y asi armar los juegos
        $id_proceso = "copiado_" . $clase->nombre . "_" . $ot;
        $proceso = Copiado::where('id_proceso', $id_proceso)->first();
        $pzasOcupadas = Copiado_pza::where('id_proceso', $proceso->id)->where('estado', 1)->get(); //Obtención de todas las piezas creadas.
        if ($proceso) {
            $pzasUsadas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Copiado')->get(); //Obtención de todas las piezas creadas en Copiado
        }

        if ($procesos->cavidades != 0) {
            //Obtener las piezas solamente en el proceso de Copiado
            $pzasEncontradas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Cavidades')->where('error', 'Ninguno')->get();
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
            } //Recorro las piezas ocupadas en Copiado
        }
        if (count($pzasOcupadas) > 0) {
            for ($x = 0; $x < count($pzasOcupadas); $x++) {
                array_push($numerosUsados, $pzasOcupadas[$x]->n_juego); //Guardo el número de pieza usada.
            }
        }
        for ($i = 0; $i < count($pzasEncontradas); $i++) { //Recorro las piezas encontradas de Copiado
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
