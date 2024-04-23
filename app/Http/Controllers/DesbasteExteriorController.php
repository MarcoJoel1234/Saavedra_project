<?php

namespace App\Http\Controllers;

use App\Models\Cepillado;
use App\Models\Clase;
use App\Models\Desbaste_cnominal;
use App\Models\Desbaste_pza;
use App\Models\Desbaste_tolerancia;
use App\Models\DesbasteExterior;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\Pza_cepillado;
use App\Models\RevLaterales;
use App\Models\RevLaterales_pza;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DesbasteExteriorController extends Controller
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
            $oTrabajo = array(); //Declara arreglo para guardar las ordenes de trabajo disponibles en Desbaste exterior.
            //Recorre todas las ordenes de trabajo.
            foreach ($ot as $ot) {
                $contador = 0; //Contador para verificar que existan clases que pasaran por Desbaste exterior
                $clases = Clase::where('id_ot', $ot->id)->get();
                //Recorre todas las clases registradas en la orden de trabajo.
                foreach ($clases as $clase) {
                    $proceso = Procesos::where('id_clase', $clase->id)->first(); //Obtención del proceso de la clase.
                    if ($proceso) {
                        if ($proceso->desbaste_exterior) { //Si existen maquinas en Desbaste exterior de esa clase, se almacena en el arreglo que se pasara a la vista
                            $contador++;
                        }
                    }
                }
                //Si hay clases que pasaran por Desbaste exterior, se almacena la orden de trabajo en el arreglo.
                if ($contador != 0) {
                    array_push($oTrabajo, $ot);
                }
            }
            if (count($oTrabajo) != 0) {
                if ($error == 1) {
                    return view('processes.desbaste', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Desbaste exterior
                }
                return view('processes.desbaste', ['ot' => $oTrabajo]); //Retorno a vista de Desbaste exterior
            }
            if ($error == 1) {
                return view('processes.desbaste', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Desbaste exterior
            }
            //Se retorna a la vista de Desbaste exterior con las ordenes de trabajo que tienen clases que pasaran por Desbaste exterior
            return view('processes.desbaste', ['ot' => $oTrabajo]); //Retorno a vista de Desbaste exterior
        }
        if ($error == 1) {
            return view('processes.desbaste', ['error' => $error]); //Retorno a vista de Desbaste exterior
        }
        return view('processes.desbaste');
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
        $id = "desbaste_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Desbaste exterior
        $cNominal = Desbaste_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = Desbaste_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $proceso = DesbasteExterior::where('id_proceso', $id)->first(); //Busco el proceso de la OT.
        if (!$proceso) {
            //Llenado de la tabla Desbaste exterior.
            $desbaste = new DesbasteExterior(); //Creación de objeto para llenar tabla Desbaste exterior.
            $desbaste->id_proceso = $id; //Creación de id para tabla Desbaste exterior.
            $desbaste->id_ot = $ot->id; //Llenado de id_proceso para tabla Desbaste exterior.
            $desbaste->save(); //Guardado de datos en la tabla Desbaste exterior.
        }
        $id_proceso = DesbasteExterior::where('id_proceso', $id)->first();
        $pzasDesbaste = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
        $id_procesoC = Cepillado::where('id_proceso', 'Cepillado_' . $clase->nombre . '_' . $clase->id_ot)->first();
        $pzasCepillado = Pza_cepillado::where('id_proceso', $id_procesoC->id)->where('estado', 2)->where('correcto', 1)->get();
        $pzasRestantes = $this->piezasRestantes($pzasCepillado, $pzasDesbaste, $clase);

        if (isset($request->n_pieza)) {  //Si se obtienen los datos de las piezas, se guardan en la tabla Desbaste exterior_cnominal.
            $id_pieza = $request->n_pieza . $id_proceso->id; //Creación de id para tabla Desbaste exterior_cnominal.
            $piezaExistente = Desbaste_pza::where('id_pza', $id_pieza)->first();
            if ($piezaExistente) {
                $piezaExistente->diametro_mordaza = $request->diametro_mordaza;
                $piezaExistente->diametro_ceja = $request->diametro_ceja;
                $piezaExistente->diametro_sufrideraExtra = $request->diametro_sufrideraExtra;
                $piezaExistente->simetria_ceja = $request->simetria_ceja;
                $piezaExistente->simetria_mordaza = $request->simetria_mordaza;
                $piezaExistente->altura_ceja = $request->altura_ceja;
                $piezaExistente->altura_sufridera = $request->altura_sufridera;
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

                $pieza = Pieza::where('n_pieza', $piezaExistente->n_pieza)->where('proceso', 'Desbaste Exterior')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if (!isset($pieza)) {
                    $pieza = new Pieza();
                }
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id;
                $pieza->n_pieza = $request->n_pieza;
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina;
                $pieza->proceso = "Desbaste Exterior";
                $pieza->error = $piezaExistente->error;
                $pieza->save();

                if ($pieza->error == 'Ninguno') {
                    //Obtener piezas de la meta
                    $piezasMeta = Desbaste_pza::where('id_meta', $meta->id)->get();
                    $this->controladorPzasLiberadas->liberarPiezasMeta($meta, $piezasMeta, $pieza->n_pieza, "Desbaste Exterior");
                }

                //Actualizar resultado de la meta
                $contadorPzas = 0;
                $juegosUsados = array();
                $pzasCorrectas = Desbaste_pza::where('id_meta', $meta->id)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas.
                foreach ($pzasCorrectas as $pzaCorrecta) {
                    $pzaCorrecta2 = Desbaste_pza::where('n_juego', $pzaCorrecta->n_juego)->where('id_meta', $meta->id)->get();
                    if (!in_array($pzaCorrecta->n_juego, $juegosUsados)) {
                        array_push($juegosUsados, $pzaCorrecta->n_juego);
                        $pzasMalas = 0;
                        foreach ($pzaCorrecta2 as $pza) {
                            if ($pza->correcto == 1) {
                                $contadorPzas += .5;
                            } else if ($pza->correcto === 0) {
                                $pzasMalas++;
                            }
                        }
                        if ($pzasMalas == 1) {
                            $contadorPzas -= .5;
                        }
                    }
                }
                $meta = Metas::find($meta->id); //Actualización de datos en tabla Metas.
                $meta->resultado = $contadorPzas;
                $meta->save(); //Guardado de datos en la tabla Metas.

                //Retornar la pieza siguiente
                $pzaUtilizar = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
                if ($id_proceso) {
                    $pzasDesbaste = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
                    $pzasCepillado = Pza_cepillado::where('id_proceso', $id_procesoC->id)->where('estado', 2)->where('correcto', 1)->get();
                    $pzasRestantes = $this->piezasRestantes($pzasCepillado, $pzasDesbaste, $clase);
                } else {
                    $pzasRestantes = 0;
                }
                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de desbaste.
                    $pzasCreadas = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.desbaste', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'pzasRestantes' => $pzasRestantes, 'piezaElegida' => $pzaUtilizar]); //Retorno a vista de Desbaste exterior.
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla desbaste exteriorts
                    $this->piezaUtilizar($ot->id, $clase);
                }
            }
        } else if (isset($request->n_juegoElegido)) {
            $juegoExiste = Desbaste_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->first();
            if (!$juegoExiste) {
                //Obtener el numero del juego para crear las piezas
                $numero = "";
                $juegoDividido = str_split($request->n_juegoElegido);
                for ($i = 0; $i < count($juegoDividido) - 1; $i++) {
                    $numero .= $juegoDividido[$i];
                }
                //For para crear las dos piezas del juego
                for ($i = 0; $i < 2; $i++) {
                    $newPza = new Desbaste_pza(); //Creación de objeto para llenar tabla Desbaste.
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
        }
        $id_proceso = DesbasteExterior::where('id_proceso', $id)->first();
        if ($id_proceso !== "[]") {
            $pzasCreadas = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
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
            $contadorPzas = 0;
            $juegosUsados = array();
            $pzasCorrectas = Desbaste_pza::where('id_meta', $meta->id)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas.
            foreach ($pzasCorrectas as $pzaCorrecta) {
                $pzaCorrecta2 = Desbaste_pza::where('n_juego', $pzaCorrecta->n_juego)->where('id_meta', $meta->id)->get();
                if (!in_array($pzaCorrecta->n_juego, $juegosUsados)) {
                    array_push($juegosUsados, $pzaCorrecta->n_juego);
                    $pzasMalas = 0;
                    foreach ($pzaCorrecta2 as $pza) {
                        if ($pza->correcto == 1) {
                            $contadorPzas += .5;
                        } else if ($pza->correcto === 0) {
                            $pzasMalas++;
                        }
                    }
                    if ($pzasMalas == 1) {
                        $contadorPzas -= .5;
                    }
                }
            }
            $meta = Metas::find($meta->id); //Actualización de datos en tabla Metas.
            $meta->resultado = $contadorPzas;
            $meta->save(); //Guardado de datos en la tabla Metas.

            if (isset($cNominal) && isset($tolerancia)) {
                $pzaUtilizar = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                    $piezasVacias = Desbaste_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
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
                        $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                    }
                }
                if (isset($pzasUtilizar)) { //Si no se encontro una pieza para utilizar, se crea una nueva pieza.
                    return view('processes.desbaste', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'pzasRestantes' => $pzasRestantes, 'piezasUtilizar' => $pzasUtilizar]); //Retorno a vista de Desbaste exterior.
                } else {
                    return view('processes.desbaste', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'pzasRestantes' => $pzasRestantes, 'piezaElegida' => $pzaUtilizar])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Desbaste exterior.
                }
            }
        }
        return view('processes.desbaste', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'clase' => $clase, 'pzasRestantes' => $pzasRestantes,])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Desbaste exterior.
    }
    public function compararDatosPieza($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->diametro_mordaza > ($cNominal->diametro_mordaza + $tolerancia->diametro_mordaza1) || $pieza->diametro_mordaza < ($cNominal->diametro_mordaza - $tolerancia->diametro_mordaza2) || $pieza->diametro_ceja > ($cNominal->diametro_ceja + $tolerancia->diametro_ceja1) || $pieza->diametro_ceja < ($cNominal->diametro_ceja - $tolerancia->diametro_ceja2) || $pieza->diametro_sufrideraExtra > ($cNominal->diametro_sufrideraExtra + $tolerancia->diametro_sufrideraExtra1) || $pieza->diametro_sufrideraExtra < ($cNominal->diametro_sufrideraExtra - $tolerancia->diametro_sufrideraExtra2) || $pieza->simetria_ceja > ($cNominal->simetria_ceja + $tolerancia->simetria_ceja1) || $pieza->simetria_ceja < ($cNominal->simetria_ceja - $tolerancia->simetria_ceja2) || $pieza->simetria_mordaza  > ($cNominal->simetria_mordaza  + $tolerancia->simetria_mordaza1) || $pieza->simetria_mordaza < ($cNominal->simetria_mordaza - $tolerancia->simetria_mordaza2) || $pieza->altura_ceja  > ($cNominal->altura_ceja  + $tolerancia->altura_ceja1) || $pieza->altura_ceja < ($cNominal->altura_ceja - $tolerancia->altura_ceja2) || $pieza->altura_sufridera > ($cNominal->altura_sufridera + $tolerancia->altura_sufridera1) || $pieza->altura_sufridera < ($cNominal->altura_sufridera - $tolerancia->altura_sufridera2)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
    public function piezasRestantes($piezasProcesoA, $piezasProcesoB, $clase)
    {
        $juegosRestantes = 0;
        $juegosContados = array();
        //Contar los juegos restantes de la piezas de cepillado
        foreach ($piezasProcesoA as $piezaA) {
            if (!in_array($piezaA->n_juego, $juegosContados)) {
                $juego = Pza_cepillado::where('n_juego', $piezaA->n_juego)->where('id_proceso', $piezaA->id_proceso)->get();
                $estado = 0;
                foreach ($juego as $pieza) {
                    if ($pieza->correcto == 1) {
                        $estado++;
                    }
                }
                if ($estado == 2) {
                    $juegosRestantes++;
                }
                array_push($juegosContados, $piezaA->n_juego);
            }
        }
        //Contar las piezas malas en el proceso de Revision laterales
        $juegosMalos = array();
        $contadorJM = 0;
        $id_proceso = RevLaterales::where('id_proceso', 'revLaterales_' . $clase->nombre . '_' . $clase->id_ot)->first();
        if ($id_proceso) {
            $piezasProcesoC = RevLaterales_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('correcto', 0)->get();
            foreach ($piezasProcesoC as $pzaMala) {
                if (!in_array($pzaMala->n_juego, $juegosMalos)) {
                    foreach ($piezasProcesoB as $pzaMala2) {
                        if ($pzaMala->n_juego == $pzaMala2->n_juego) {
                            $pzaMala2->n_juego;
                            $pzasRestar = 1;
                            break;
                        } else {
                            $pzasRestar = 0;
                        }
                    }
                    if ($pzasRestar != 1) {
                        $contadorJM++;
                    }
                    array_push($juegosMalos, $pzaMala->n_juego);
                }
            }
        }
        return $juegosRestantes = ($juegosRestantes - (count($piezasProcesoB) / 2)) - $contadorJM;
    }
    public function edit(Request $request)
    {
        $meta = Metas::find($request->metaData); //Busco la meta de la OT.
        $ot = Orden_trabajo::find($meta->id_ot); //Obtención de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $clase = Clase::find($meta->id_clase); //Busco la clase de la OT.
        $id = "desbaste_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Desbaste exterior.
        $id_proceso = DesbasteExterior::where('id_proceso', $id)->first();;
        $cNominal = Desbaste_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = Desbaste_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $pzasCreadas = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
        $pzaUtilizar = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        //Retornar la pieza siguiente
        $pzaUtilizar = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
        if ($id_proceso) {
            $pzasDesbaste = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
            $id_procesoC = Cepillado::where('id_proceso', 'Cepillado_' . $clase->nombre . '_' . $clase->id_ot)->first();
            $pzasCepillado = Pza_cepillado::where('id_proceso', $id_procesoC->id)->where('estado', 2)->where('correcto', 1)->get();
            $pzasRestantes = $this->piezasRestantes($pzasCepillado, $pzasDesbaste, $clase);
        } else {
            $pzasRestantes = 0;
        }
        if (isset($request->n_pieza)) { //Si se obtienen los datos de las piezas
            for ($i = 0; $i < count($request->n_pieza); $i++) {
                $id_pieza = $request->n_pieza[$i] . $id_proceso->id; //Creación de id para tabla Desbaste_cnominal.
                $piezaExistente = Desbaste_pza::where('id_pza', $id_pieza)->first();
                if ($piezaExistente) {
                    $piezaExistente->diametro_mordaza = $request->diametro_mordaza[$i];
                    $piezaExistente->diametro_ceja = $request->diametro_ceja[$i];
                    $piezaExistente->diametro_sufrideraExtra = $request->diametro_sufrideraExtra[$i];
                    $piezaExistente->simetria_ceja = $request->simetria_ceja[$i];
                    $piezaExistente->simetria_mordaza = $request->simetria_mordaza[$i];
                    $piezaExistente->altura_ceja = $request->altura_ceja[$i];
                    $piezaExistente->altura_sufridera = $request->altura_sufridera[$i];
                    if (isset($request->observaciones[$i])) { //Si se obtienen los datos de las piezas, se guardan en la tabla Desbaste_cnominal.
                        $piezaExistente->observaciones = $request->observaciones[$i];  //Llenado de observaciones para tabla Desbaste_cnominal.
                    }
                    $piezaExistente->save(); //Guardado de datos en la tabla Pza_Desbaste exterior.

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



                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_pieza)->where('proceso', 'Desbaste Exterior')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if (!isset($pieza)) {
                        $pieza = new Pieza(); //Creación del obejeto para llenar la tabla pieza.
                    }
                    $pieza->id_clase = $clase->id; //Lenado de id_clase para la tabla pieza.
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $piezaExistente->n_pieza;
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Desbaste Exterior";
                    $pieza->error = $piezaExistente->error;
                    $pieza->save();
                    if ($pieza->error == 'Ninguno') {
                        //Obtener piezas de la meta
                        $piezasMeta = Desbaste_pza::where('id_meta', $meta->id)->get();
                        $this->controladorPzasLiberadas->liberarPiezasMeta($meta, $piezasMeta, $pieza->n_pieza, "Desbaste Exterior");
                    }
                }
            }
            //Actualizar resultado de la meta
            $contadorPzas = 0;
            $juegosUsados = array();
            $pzasCorrectas = Desbaste_pza::where('id_meta', $meta->id)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas.
            foreach ($pzasCorrectas as $pzaCorrecta) {
                $pzaCorrecta2 = Desbaste_pza::where('n_juego', $pzaCorrecta->n_juego)->where('id_meta', $meta->id)->get();
                if (!in_array($pzaCorrecta->n_juego, $juegosUsados)) {
                    array_push($juegosUsados, $pzaCorrecta->n_juego);
                    $pzasMalas = 0;
                    foreach ($pzaCorrecta2 as $pza) {
                        if ($pza->correcto == 1) {
                            $contadorPzas += .5;
                        } else if ($pza->correcto === 0) {
                            $pzasMalas++;
                        }
                    }
                    if ($pzasMalas == 1) {
                        $contadorPzas -= .5;
                    }
                }
            }
            $meta = Metas::find($meta->id); //Actualización de datos en tabla Metas.
            $meta->resultado = $contadorPzas;
            $meta->save(); //Guardado de datos en la tabla Metas.
            //Retornar la pieza siguiente
            $pzaUtilizar = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            //Retornar la pieza siguiente
            $pzaUtilizar = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
            if ($id_proceso) {
                $pzasDesbaste = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
                $id_procesoC = Cepillado::where('id_proceso', 'Cepillado_' . $clase->nombre . '_' . $clase->id_ot)->first();
                $pzasCepillado = Pza_cepillado::where('id_proceso', $id_procesoC->id)->where('estado', 2)->where('correcto', 1)->get();
                $pzasRestantes = $this->piezasRestantes($pzasCepillado, $pzasDesbaste, $clase);
            } else {
                $pzasRestantes = 0;
            }
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $piezasVacias = Desbaste_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
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
                if (!$piezaEncontrada) { //Si no se encontro una pieza para utilizar, se crea una nueva pieza.
                    $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                }
            }
            $pzasCreadas = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = Desbaste_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = Desbaste_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                return view('processes.desbaste', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Desbaste exterior.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.desbaste', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Desbaste exterior.
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.desbaste', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'pzasRestantes' => $pzasRestantes]); //Retorno la vista de Desbaste exterior.
                    }
                }
            }
            $pzaUtilizar = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $piezasVacias = Desbaste_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
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
                if (!$piezaEncontrada) { //Si no se encontro una pieza para utilizar, se crea una nueva pieza.
                    $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                }
            }
            $pzasCreadas = Desbaste_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = Desbaste_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = Desbaste_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                return view('processes.desbaste', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Desbaste exterior.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.desbaste', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Desbaste exterior.
            }
        }
    }

    public function piezaUtilizar($ot, $clase) //Función para obtener la pieza a utilizar.
    {
        //Obtener las piezas que esten terminadas y correctas en la tabla Desbaste exterior para despues comparar cada una con su consecuente y asi armar los juegos
        $pzasUtilizar = array();
        $pzasGuardadas = array(); //Creación de array para guardar los números de pieza.
        $numero = ""; //Creación de variable para guardar el número de pieza.
        $pzasEncontradas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Cepillado')->where('error', 'Ninguno')->get(); //Obtención de todas las piezas creadas.


        $id_proceso = "desbaste_" . $clase->nombre . "_" . $ot; //Creación de id para la tabla Desbaste exterior
        $proceso = DesbasteExterior::where('id_proceso', $id_proceso)->first(); //Busco el proceso de la OT.

        $pzasOcupadas = Desbaste_pza::where('id_proceso', $proceso->id)->where('estado', 1)->get(); //Obtención de todas las piezas creadas.

        if ($proceso) {
            $pzasUsadas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Desbaste Exterior')->get(); //Obtención de todas las piezas creadas.
        }
        for ($i = 0; $i < count($pzasEncontradas); $i++) { //Recorro las piezas encontradas.
            if (array_search($pzasEncontradas[$i]->n_pieza, $pzasGuardadas) == false) {
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
                $n_pieza = $pzasEncontradas[$i]->n_pieza; //Obtención del número de pieza.
                $piezaDividida = str_split($n_pieza); //División del número de pieza.
                for ($j = 0; $j < count($piezaDividida) - 1; $j++) { //Recorro el número de pieza.
                    $numero .= $piezaDividida[$j]; //Obtención del número de pieza.
                }
                //Se hace la condicion para saber si el numero de la pieza se encuentra ya usada.
                if (array_search($numero, $numerosUsados) === false) {
                    if (mb_substr($n_pieza, -1, null, 'UTF-8') == "M") { //Si la pieza es macho, se busca la pieza hembra.
                        $pieza = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Cepillado')->where('n_pieza', $numero . "H")->where('error', 'Ninguno')->first(); //Busco la pieza hembra.
                    } else {
                        $pieza = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Cepillado')->where('n_pieza', $numero . "M")->where('error', 'Ninguno')->first(); //Busco la pieza macho.
                    }

                    $piezaRevision1 = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Revision Laterales')->where('n_pieza', $numero . "H")->first();
                    $piezaRevision2 = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Revision Laterales')->where('n_pieza', $numero . "M")->first();

                    if (isset($pieza)) {
                        if ((!isset($piezaRevision1) && !isset($piezaRevision2)) || (isset($piezaRevision1) && $piezaRevision1->error == "Ninguno" && isset($piezaRevision2) && $piezaRevision2->error == "Ninguno")) {
                            array_push($pzasUtilizar, $numero . "J"); //Guardo el número de pieza.
                            array_push($pzasGuardadas, $pzasEncontradas[$i]->n_pieza); //Guardo el número de pieza.
                            array_push($pzasGuardadas, $pieza->n_pieza); //Guardo el número de pieza.
                        }
                    }
                }
                $numero = "";
            }
        }
        return $pzasUtilizar;
    }
}
