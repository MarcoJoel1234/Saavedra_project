<?php

namespace App\Http\Controllers;

use App\Models\BarrenoProfundidad;
use App\Models\BarrenoProfundidad_cnominal;
use App\Models\BarrenoProfundidad_pza;
use App\Models\BarrenoProfundidad_tolerancia;
use App\Models\Clase;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\PySOpeSoldadura;
use App\Models\PySOpeSoldadura_pza;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BarrenoProfundidadController extends Controller
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
            $oTrabajo = array(); //Declara arreglo para guardar las ordenes de trabajo disponibles en Barreno de profundidad
            //Recorre todas las ordenes de trabajo.
            foreach ($ot as $ot) {
                $contador = 0; //Contador para verificar que existan clases que pasaran por Barreno de profundidad
                $clases = Clase::where('id_ot', $ot->id)->get();
                //Recorre todas las clases registradas en la orden de trabajo.
                foreach ($clases as $clase) {
                    $proceso = Procesos::where('id_clase', $clase->id)->first(); //Obtención del proceso de la clase.
                    if ($proceso) {
                        if ($proceso->barreno_profundidad) { //Si existen maquinas en cepillado de esa clase, se almacena en el arreglo que se pasara a la vista
                            $contador++;
                        }
                    }
                }
                //Si hay clases que pasaran por Barreno de profundidad, se almacena la orden de trabajo en el arreglo.
                if ($contador != 0) {
                    array_push($oTrabajo, $ot);
                }
            }
            //Si hay clases que pasaran por Primera operación soldadura, se almacena la orden de trabajo en el arreglo.
            if (count($oTrabajo) != 0) {
                if ($error == 1) {
                    return view('processes.barrenoProfundidad', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Desbaste exterior
                }
                return view('processes.barrenoProfundidad', ['ot' => $oTrabajo]); //Retorno a vista de Desbaste exterior
            }
            if ($error == 1) {
                return view('processes.barrenoProfundidad', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Desbaste exterior
            }
            //Se retorna a la vista de Primera operación soldadura con las ordenes de trabajo que tienen clases que pasaran por Desbaste exterior
            return view('processes.barrenoProfundidad', ['ot']); //Retorno a vista de Desbaste exterior
        }
        if ($error == 1) {
            return view('processes.barrenoProfundidad', ['error' => $error]); //Retorno a vista de Desbaste exterior
        }
        return view('processes.barrenoProfundidad');
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
        $id = "barrenoProfundidad_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Barreno de profundidad
        $cNominal = BarrenoProfundidad_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = BarrenoProfundidad_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $proceso = BarrenoProfundidad::where('id_proceso', $id)->first(); //Busco el proceso de la OT.
        if (!$proceso) {
            //Llenado de la tabla Barreno de profundidad
            $soldadura = new BarrenoProfundidad(); //Creación de objeto para llenar tabla Barreno de profundidad
            $soldadura->id_proceso = $id; //Creación de id para tabla Barreno de profundidad
            $soldadura->id_ot = $ot->id; //Llenado de id_proceso para tabla Barreno de profundidad
            $soldadura->save(); //Guardado de datos en la tabla Barreno de profundidad
        }
        $id_proceso = BarrenoProfundidad::where('id_proceso', $id)->first();
        $pzasBarrenoP = BarrenoProfundidad_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
        $procesos = Procesos::where('id_clase', $clase->id)->first();
        if ($procesos->acabadoBombillo != 0) {
            //Obtener las piezas solamente en el proceso de Acabado Bombillo
            $pzasEncontradas = Pieza::where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('proceso', 'Acabado Bombillo')->where('error', 'Ninguno')->get();
            $pzasRestantes = $this->piezasRestantes($clase, $pzasEncontradas, $pzasBarrenoP, null, 'acabadoBombillo');
        } else if ($procesos->acabadoMolde != 0) {
            //Obtener las piezas solamente en el proceso de Acabado Molde
            $pzasEncontradas = Pieza::where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('proceso', 'Acabado Molde')->where('error', 'Ninguno')->get();
            $pzasRestantes = $this->piezasRestantes($clase, $pzasEncontradas, $pzasBarrenoP, null, 'acabadoMolde');
        }else if($procesos->operacionEquipo != 0){
            $id_opeEquipo1 = PySOpeSoldadura::where('id_proceso', '1y2opeSoldadura_' . $clase->nombre . '_' . $clase->id_ot . '_1')->first();
            $id_opeEquipo2 = PySOpeSoldadura::where('id_proceso', '1y2opeSoldadura_' . $clase->nombre . '_' . $clase->id_ot . '_2')->first();
            $pzasOpeEquipo1 = PySOpeSoldadura_pza::where('id_proceso', $id_opeEquipo1->id)->where('estado', 2)->get();
            $pzasRestantes = $this->piezasRestantes($clase, $pzasBarrenoP, $pzasOpeEquipo1, $id_opeEquipo2->id, 'Operacion equipo');
        }

        if (isset($request->n_pieza)) {  //Si se obtienen los datos de las piezas, se guardan en la tabla barrenoProfundidad_cnominal.
            $id_pieza = $request->n_pieza . $id_proceso->id; //Creación de id para tabla barrenoProfundidad_cnominal.
            $piezaExistente = BarrenoProfundidad_pza::where('id_pza', $id_pieza)->first();
            if ($piezaExistente) {
                $piezaExistente->broca1 = $request->broca1;
                $piezaExistente->tiempo1 = $request->tiempo1;
                $piezaExistente->broca2 = $request->broca2;
                $piezaExistente->tiempo2 = $request->tiempo2;
                $piezaExistente->broca3 = $request->broca3;
                $piezaExistente->tiempo3 = $request->tiempo3;
                $piezaExistente->entrada = $request->entrada;
                $piezaExistente->salida = $request->salida;
                $piezaExistente->diametro_arrastre1 = $request->diametro_arrastre1;
                $piezaExistente->diametro_arrastre2 = $request->diametro_arrastre2;
                $piezaExistente->diametro_arrastre3 = $request->diametro_arrastre3;
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

                $pieza = Pieza::where('n_pieza', $piezaExistente->n_pieza)->where('proceso', 'Barreno Profundidad')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if (!isset($pieza)) {
                    $pieza = new Pieza();
                }
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id;
                $pieza->n_pieza = $request->n_pieza;
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina;
                $pieza->proceso = "Barreno Profundidad";
                $pieza->error = $piezaExistente->error;
                $pieza->save();
                if ($pieza->error == 'Ninguno') {
                    //Obtener piezas de la meta
                    $piezasMeta = BarrenoProfundidad_pza::where('id_meta', $meta->id)->get();
                    $this->controladorPzasLiberadas->liberarPiezasMeta($meta, $piezasMeta, $pieza->n_pieza, "Barreno Profundidad");
                }

                //Actualizar resultado de la meta
                $pzasCorrectas = BarrenoProfundidad_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
                Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                    'resultado' => $pzasCorrectas->count(),
                ]);
                $meta = Metas::find($meta->id); //Busco la meta de la OT.
                //  //Retornar la pieza siguiente
                $pzaUtilizar = BarrenoProfundidad_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();

                $pzasBarrenoP = BarrenoProfundidad_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
                if ($procesos->acabadoBombillo != 0) {
                    //Obtener las piezas solamente en el proceso de Acabado Bombillo
                    $pzasEncontradas = Pieza::where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('proceso', 'Acabado Bombillo')->where('error', 'Ninguno')->get();
                    $pzasRestantes = $this->piezasRestantes($clase, $pzasEncontradas, $pzasBarrenoP, null, 'acabadoBombillo');
                } else if ($procesos->acabadoMolde != 0) {
                    //Obtener las piezas solamente en el proceso de Acabado Molde
                    $pzasEncontradas = Pieza::where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('proceso', 'Acabado Molde')->where('error', 'Ninguno')->get();
                    $pzasRestantes = $this->piezasRestantes($clase, $pzasEncontradas, $pzasBarrenoP, null, 'acabadoMolde');
                }else if($procesos->operacionEquipo != 0){
                    $id_opeEquipo1 = PySOpeSoldadura::where('id_proceso', '1y2opeSoldadura_' . $clase->nombre . '_' . $clase->id_ot . '_1')->first();
                    $id_opeEquipo2 = PySOpeSoldadura::where('id_proceso', '1y2opeSoldadura_' . $clase->nombre . '_' . $clase->id_ot . '_2')->first();
                    $pzasOpeEquipo1 = PySOpeSoldadura_pza::where('id_proceso', $id_opeEquipo1->id)->where('estado', 2)->get();
                    $pzasRestantes = $this->piezasRestantes($clase, $pzasBarrenoP, $pzasOpeEquipo1, $id_opeEquipo2->id, 'Operacion equipo');
                }

                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Barreno de profundidad.
                    $pzasCreadas = BarrenoProfundidad_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.barrenoProfundidad', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Barreno de profundidad
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupar en la tabla barreno de profundidad
                    $this->piezaUtilizar($ot->id, $clase);
                }
            }
        } else if (isset($request->n_juegoElegido)) {
            $juegoExiste = BarrenoProfundidad_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->first();
            if (!$juegoExiste) {
                $newPza = new BarrenoProfundidad_pza(); //Creación de objeto para llenar tabla Barreno de profundidad
                $newPza->id_pza = $request->n_juegoElegido . $id_proceso->id; //Creación de id para tabla Barreno de profundidad
                $newPza->id_meta = $meta->id; //Llenado de id_meta para tabla Barreno de profundidad
                $newPza->id_proceso = $id_proceso->id; //Llenado de id_proceso para tabla Barreno de profundidad
                $newPza->estado = 1; //Llenado de estado para tabla Barreno de profundidad
                $newPza->n_juego = $request->n_juegoElegido; //Llenado de estado para tabla Barreno de profundidad
                $newPza->save(); //Guardado de datos en la tabla Barreno de profundidad
            }
        }
        $id_proceso = BarrenoProfundidad::where('id_proceso', $id)->first();
        if ($id_proceso !== "[]") {
            $pzasCreadas = BarrenoProfundidad_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            for ($i = 0; $i < count($pzasCreadas); $i++) { //Recorro las piezas creadas.
                //Actualiza el estado correcto de la pieza.
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
            $pzasMeta = BarrenoProfundidad_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.

            if (isset($cNominal) && isset($tolerancia)) {
                $pzaUtilizar = BarrenoProfundidad_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Barreno de profundidad
                    $piezasVacias = BarrenoProfundidad_pza::where('error', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                    if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                        for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                            $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                            if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                                $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_barrenoProfundidad.
                                $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_barrenoProfundidad.
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
                    return view('processes.barrenoProfundidad', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Cavidades
                } else {
                    return view('processes.barrenoProfundidad', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cavidades
                }
            } else {
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.barrenoProfundidad', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cavidades
            }
        }
    }
    public function compararDatosPieza($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->broca1 > ($cNominal->broca1 + $tolerancia->broca1) || $pieza->broca1 < ($cNominal->broca1 - $tolerancia->broca1) || $pieza->tiempo1 > ($cNominal->tiempo1 + $tolerancia->tiempo1) || $pieza->tiempo1 < ($cNominal->tiempo1 - $tolerancia->tiempo1) || $pieza->broca2 > ($cNominal->broca2 + $tolerancia->broca2) || $pieza->broca2 < ($cNominal->broca2 - $tolerancia->broca2) || $pieza->tiempo2 > ($cNominal->tiempo2 + $tolerancia->tiempo2) || $pieza->tiempo2 < ($cNominal->tiempo2 - $tolerancia->tiempo2) || $pieza->broca3 > ($cNominal->broca3 + $tolerancia->broca3) || $pieza->broca3 < ($cNominal->broca3 - $tolerancia->broca3) || $pieza->tiempo3 > ($cNominal->tiempo3 + $tolerancia->tiempo3) || $pieza->tiempo3 < ($cNominal->tiempo3 - $tolerancia->tiempo3) || $pieza->entrada > ($cNominal->entradaSalida + $tolerancia->entrada) || $pieza->entrada < ($cNominal->entradaSalida - $tolerancia->salida) || $pieza->salida > ($cNominal->entradaSalida + $tolerancia->entrada) || $pieza->salida < ($cNominal->entradaSalida - $tolerancia->salida) || $pieza->diametro_arrastre1 > ($cNominal->diametro_arrastre1 + $tolerancia->diametro_arrastre1) || $pieza->diametro_arrastre1 < ($cNominal->diametro_arrastre1 - $tolerancia->diametro_arrastre1) || $pieza->diametro_arrastre2 > ($cNominal->diametro_arrastre2 + $tolerancia->diametro_arrastre2) || $pieza->diametro_arrastre2 < ($cNominal->diametro_arrastre2 - $tolerancia->diametro_arrastre2) || $pieza->diametro_arrastre3 > ($cNominal->diametro_arrastre3 + $tolerancia->diametro_arrastre3) || $pieza->diametro_arrastre3 < ($cNominal->diametro_arrastre3 - $tolerancia->diametro_arrastre3)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
    }
    public function piezasRestantes($clase, $pzasProcesoA, $pzasProcesoB, $id_procesoC, $procesoName)
    {
        $pzasProcesos = 0;
        $pzasRestantes = 0;
        $pzasContadas = array();
        $proceso = Procesos::where('id_clase', $clase->id)->first(); //Obtención del proceso de la clase.
        if ($proceso) {
            if ($procesoName != 'Operacion equipo') {
                $pzasRestantes = count($pzasProcesoA) - count($pzasProcesoB);
            } else {
                foreach ($pzasProcesoB as $pzaB) {
                    if (!in_array($pzaB->n_juego, $pzasContadas)) {
                        $pzasB = PySOpeSoldadura_pza::where('n_juego', $pzaB->n_juego)->where('correcto', 1)->where('id_proceso', $pzaB->id_proceso)->get();
                        $pzasC = PySOpeSoldadura_pza::where('n_juego', $pzaB->n_juego)->where('correcto', 1)->where('id_proceso', $id_procesoC)->get();
                        if (count($pzasB) == 2 && count($pzasC) == 2) {
                            $pzasProcesos++;
                        }
                        array_push($pzasContadas, $pzaB->n_juego);
                    }
                }
                $pzasRestantes = $pzasProcesos - count($pzasProcesoA);
            }
        }
        return $pzasRestantes;
    }
    public function edit(Request $request)
    {
        $meta = Metas::find($request->metaData); //Busco la meta de la OT.
        $ot = Orden_trabajo::find($meta->id_ot); //Obtención de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $clase = Clase::find($meta->id_clase); //Busco la clase de la OT.
        $id = "barrenoProfundidad_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Barreno de profundidad
        $id_proceso = BarrenoProfundidad::where('id_proceso', $id)->first();;
        $cNominal = BarrenoProfundidad_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = BarrenoProfundidad_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $pzasCreadas = BarrenoProfundidad_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
        $pzaUtilizar = BarrenoProfundidad_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        $pzasBarrenoP = BarrenoProfundidad_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
        $procesos = Procesos::where('id_clase', $clase->id)->first();
        if ($procesos->acabadoBombillo != 0) {
            //Obtener las piezas solamente en el proceso de Acabado Bombillo
            $pzasEncontradas = Pieza::where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('proceso', 'Acabado Bombillo')->where('error', 'Ninguno')->get();
            $pzasRestantes = $this->piezasRestantes($clase, $pzasEncontradas, $pzasBarrenoP, null, 'acabadoBombillo');
        } else if ($procesos->acabadoMolde != 0) {
            //Obtener las piezas solamente en el proceso de Acabado Molde
            $pzasEncontradas = Pieza::where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('proceso', 'Acabado Molde')->where('error', 'Ninguno')->get();
            $pzasRestantes = $this->piezasRestantes($clase, $pzasEncontradas, $pzasBarrenoP, null, 'acabadoMolde');
        }else if($procesos->operacionEquipo != 0){
            $id_opeEquipo1 = PySOpeSoldadura::where('id_proceso', '1y2opeSoldadura_' . $clase->nombre . '_' . $clase->id_ot . '_1')->first();
            $id_opeEquipo2 = PySOpeSoldadura::where('id_proceso', '1y2opeSoldadura_' . $clase->nombre . '_' . $clase->id_ot . '_2')->first();
            $pzasOpeEquipo1 = PySOpeSoldadura_pza::where('id_proceso', $id_opeEquipo1->id)->where('estado', 2)->get();
            $pzasRestantes = $this->piezasRestantes($clase, $pzasBarrenoP, $pzasOpeEquipo1, $id_opeEquipo2->id, 'Operacion equipo');
        }
        if (isset($request->n_pieza)) { //Si se obtienen los datos de las piezas, se guardan en la tabla barrenoProfundidad_cnominal.
            for ($i = 0; $i < count($request->n_pieza); $i++) {
                $id_pieza = $request->n_pieza[$i] . $id_proceso->id; //Creación de id para tabla barrenoProfundidad_cnominal.
                $piezaExistente = BarrenoProfundidad_pza::where('id_pza', $id_pieza)->first();
                if ($piezaExistente) {
                    $piezaExistente->broca1 = $request->broca1[$i];
                    $piezaExistente->tiempo1 = $request->tiempo1[$i];
                    $piezaExistente->broca2 = $request->broca2[$i];
                    $piezaExistente->tiempo2 = $request->tiempo2[$i];
                    $piezaExistente->broca3 = $request->broca3[$i];
                    $piezaExistente->tiempo3 = $request->tiempo3[$i];
                    $piezaExistente->entrada = $request->entrada[$i];
                    $piezaExistente->salida = $request->salida[$i];
                    $piezaExistente->diametro_arrastre1 = $request->diametro_arrastre1[$i];
                    $piezaExistente->diametro_arrastre2 = $request->diametro_arrastre2[$i];
                    $piezaExistente->diametro_arrastre3 = $request->diametro_arrastre3[$i];
                    $piezaExistente->save();
                    if (isset($request->observaciones[$i])) { //Si se obtienen los datos de las piezas, se guardan en la tabla barrenoProfundidad_cnominal.
                        $piezaExistente->observaciones = $request->observaciones[$i];  //Llenado de observaciones para tabla barrenoProfundidad_cnominal.
                    }
                    $piezaExistente->save(); //Guardado de datos en la tabla Pza_barrenoProfundidad

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

                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Barreno Profundidad')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if (!isset($pieza)) {
                        $pieza = new Pieza(); //Creación del obejeto para llenar la tabla pieza.
                    }
                    $pieza->id_clase = $clase->id; //Lenado de id_clase para la tabla pieza.
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $piezaExistente->n_juego;
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Barreno Profundidad";
                    $pieza->error = $piezaExistente->error;
                    $pieza->save();
                    if ($pieza->error == 'Ninguno') {
                        //Obtener piezas de la meta
                        $piezasMeta = BarrenoProfundidad_pza::where('id_meta', $meta->id)->get();
                        $this->controladorPzasLiberadas->liberarPiezasMeta($meta, $piezasMeta, $pieza->n_pieza, "Barreno Profundidad");
                    }
                }
            }
            //Actualizar resultado de la meta
            $pzasMeta = BarrenoProfundidad_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.
            //Retornar la pieza siguiente
            $pzaUtilizar = BarrenoProfundidad_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Barreno de profundidad
                $piezasVacias = BarrenoProfundidad_pza::where('error', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_barrenoProfundidad.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_barrenoProfundidad.
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
            $pzasCreadas = BarrenoProfundidad_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = BarrenoProfundidad_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = BarrenoProfundidad_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Barreno de profundidad.
                return view('processes.barrenoProfundidad', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Barreno de profundidad.
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.barrenoProfundidad', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.barrenoProfundidad', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'pzasRestantes' => $pzasRestantes]); //Retorno la vista de cepillado.
                    }
                }
            }
            $pzaUtilizar = BarrenoProfundidad_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Barreno de profundidad
                $piezasVacias = BarrenoProfundidad_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                    for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                        $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                        if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                            $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_barrenoProfundidad.
                            $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_barrenoProfundidad.
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
            $pzasCreadas = BarrenoProfundidad_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = BarrenoProfundidad_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = BarrenoProfundidad_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Barreno de profundidad.
                return view('processes.barrenoProfundidad', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Barreno de profundidad.
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.barrenoProfundidad', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Barrereno de profundidad.
            }
        }
    }

    public function piezaUtilizar($ot, $clase) //Función para obtener la pieza a utilizar.
    {
        $pzasUtilizar = array();
        $pzasGuardadas = array();
        $procesos = Procesos::where('id_clase', $clase->id)->first();

        //Obtener las piezas que esten terminadas y correctas en la tabla Barreno de profundidad para despues comparar cada una con su consecuente y asi armar los juegos
        $id_proceso = "barrenoProfundidad_" . $clase->nombre . "_" . $ot;
        $proceso = BarrenoProfundidad::where('id_proceso', $id_proceso)->first();
        $pzasOcupadas = BarrenoProfundidad_pza::where('id_proceso', $proceso->id)->where('estado', 1)->get(); //Obtención de todas las piezas creadas.
        if ($proceso) {
            $pzasUsadas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Barreno Profundidad')->get(); //Obtención de todas las piezas creadas en Barreno de profundidad.
        }

        if ($procesos->acabadoBombillo != 0) {
            //Obtener las piezas solamente en el proceso de Acabado Bombillo
            $pzasEncontradas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Acabado Bombillo')->where('error', 'Ninguno')->get();
            $this->piezasEncontradas($pzasEncontradas, $pzasUtilizar, $pzasGuardadas, $pzasUsadas, $pzasOcupadas);
        } else if ($procesos->acabadoMolde != 0) {
            //Obtener las piezas solamente en el proceso de Acabado Molde
            $pzasEncontradas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Acabado Molde')->where('error', 'Ninguno')->get();
            $this->piezasEncontradas($pzasEncontradas, $pzasUtilizar, $pzasGuardadas, $pzasUsadas, $pzasOcupadas);
        }else if($procesos->operacionEquipo != 0){
            $pzasUtilizarO1 = array();
            $pzasGuardadasO1 = array();

            $pzasUtilizarO2 = array();
            $pzasGuardadasO2 = array();
            $juegosUtilizados = array();
            $procesos = Procesos::where('id_clase', $clase->id)->first();

            //Obtener las piezas solamente en el proceso de Segunda operacion
            $pzasEncontradasOp1 = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Operacion Equipo_1')->get();
            $pzasEncontradasOp2 = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Operacion Equipo_2')->get();
            $this->piezasEncontradas1($ot, $clase, $pzasEncontradasOp1, $pzasUtilizarO1, $pzasGuardadasO1, 'Operacion Equipo_1', $pzasUsadas, $pzasOcupadas);
            $this->piezasEncontradas1($ot, $clase, $pzasEncontradasOp2, $pzasUtilizarO2, $pzasGuardadasO2, 'Operacion Equipo_2', $pzasUsadas, $pzasOcupadas);
            foreach($pzasUtilizarO1 as $pzaOp1){
                if(!in_array($pzaOp1, $juegosUtilizados)){
                    array_push($juegosUtilizados, $pzaOp1);
                    if(in_array($pzaOp1, $pzasUtilizarO2)){
                        array_push($pzasUtilizar, $pzaOp1);
                    }
                }
            }
        }
        return $pzasUtilizar;
    }
    public function piezasEncontradas($pzasEncontradas, &$pzasUtilizar, &$pzasGuardadas, $pzasUsadas, $pzasOcupadas)
    {
        $numerosUsados = array();
        if (count($pzasUsadas) > 0) {
            for ($x = 0; $x < count($pzasUsadas); $x++) {
                array_push($numerosUsados, $pzasUsadas[$x]->n_pieza); //Guardo el número de pieza usada.
            } //Recorro las piezas ocupadas en Barreno de profundidad.
        }
        if (count($pzasOcupadas) > 0) {
            for ($x = 0; $x < count($pzasOcupadas); $x++) {
                array_push($numerosUsados, $pzasOcupadas[$x]->n_juego); //Guardo el número de pieza usada.
            }
        }
        for ($i = 0; $i < count($pzasEncontradas); $i++) { //Recorro las piezas encontradas de Barreno de profundidad.
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
    public function piezasEncontradas1($ot, $clase, $pzasEncontradas, &$pzasUtilizar, &$pzasGuardadas, $nameProceso, $pzasUsadas, $pzasOcupadas)
    {
        $numero = "";
        for ($i = 0; $i < count($pzasEncontradas); $i++) { //Recorro las piezas encontradas de SoldaduraPTA
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