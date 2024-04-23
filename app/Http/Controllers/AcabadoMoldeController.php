<?php

namespace App\Http\Controllers;

use App\Models\AcabadoMolde;
use App\Models\AcabadoMolde_cnominal;
use App\Models\AcabadoMolde_pza;
use App\Models\AcabadoMolde_tolerancia;
use App\Models\Clase;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AcabadoMoldeController extends Controller
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
            $oTrabajo = array(); //Declara arreglo para guardar las ordenes de trabajo disponibles en Acabado Molde.
            //Recorre todas las ordenes de trabajo.
            foreach ($ot as $ot) {
                $contador = 0; //Contador para verificar que existan clases que pasaran por Acabado Molde
                $clases = Clase::where('id_ot', $ot->id)->get();
                //Recorre todas las clases registradas en la orden de trabajo.
                foreach ($clases as $clase) {
                    $proceso = Procesos::where('id_clase', $clase->id)->first(); //Obtención del proceso de la clase.
                    if ($proceso) {
                        if ($proceso->acabadoMolde) { //Si existen maquinas en cepillado de esa clase, se almacena en el arreglo que se pasara a la vista
                            $contador++;
                        }
                    }
                }
                //Si hay clases que pasaran por Acabado Molde, se almacena la orden de trabajo en el arreglo.
                if ($contador != 0) {
                    array_push($oTrabajo, $ot);
                }
            }
            //Si hay clases que pasaran por Primera operación soldadura, se almacena la orden de trabajo en el arreglo.
            if (count($oTrabajo) != 0) {
                if ($error == 1) {
                    return view('processes.revAcabadosMolde', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Desbaste exterior
                }
                return view('processes.revAcabadosMolde', ['ot' => $oTrabajo]); //Retorno a vista de Desbaste exterior
            }
            if ($error == 1) {
                return view('processes.revAcabadosMolde', ['ot' => $oTrabajo, 'error' => $error]); //Retorno a vista de Desbaste exterior
            }
            //Se retorna a la vista de Primera operación soldadura con las ordenes de trabajo que tienen clases que pasaran por Desbaste exterior
            return view('processes.revAcabadosMolde', ['ot']); //Retorno a vista de Desbaste exterior
        }
        if ($error == 1) {
            return view('processes.revAcabadosMolde', ['error' => $error]); //Retorno a vista de Desbaste exterior
        }
        return view('processes.revAcabadosMolde');
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
        $id = "acabadoMolde_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Acabado Molde.
        $cNominal = AcabadoMolde_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = AcabadoMolde_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
        $proceso = AcabadoMolde::where('id_proceso', $id)->first(); //Busco el proceso de la OT.
        if (!$proceso) {
            //Llenado de la tabla Acabado Molde
            $soldadura = new AcabadoMolde(); //Creación de objeto para llenar tabla Acabado Molde
            $soldadura->id_proceso = $id; //Creación de id para tabla Acabado Molde
            $soldadura->id_ot = $ot->id; //Llenado de id_proceso para tabla Acabado Molde
            $soldadura->save(); //Guardado de datos en la tabla Acabado Molde
        }
        $id_proceso = AcabadoMolde::where('id_proceso', $id)->first();
        $pzasAcabadoM = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
        $pzasCalificado = Pieza::where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('proceso', 'Revision Calificado')->where('error', 'Ninguno')->get();
        $pzasRestantes = $this->piezasRestantes($pzasCalificado, $pzasAcabadoM);

        if (isset($request->n_pieza)) {  //Si se obtienen los datos de las piezas, se guardan en la tabla Acabado_Molde_cnominal.
            $id_pieza = $request->n_pieza . $id_proceso->id; //Creación de id para tabla Acabado_Molde_cnominal.
            $piezaExistente = AcabadoMolde_pza::where('id_pza', $id_pieza)->first();
            if ($piezaExistente) {
                $piezaExistente->diametro_mordaza = $request->diametro_mordaza;
                $piezaExistente->diametro_ceja = $request->diametro_ceja;
                $piezaExistente->diametro_sufridera = $request->diametro_sufridera;
                $piezaExistente->altura_mordaza = $request->altura_mordaza;
                $piezaExistente->altura_ceja = $request->altura_ceja;
                $piezaExistente->altura_sufridera = $request->altura_sufridera;
                $piezaExistente->gauge_ceja = $request->gauge_ceja;
                $piezaExistente->altura_total = $request->altura_total;
                $piezaExistente->diametro_conexion_fondo = $request->diametro_conexion_fondo;
                $piezaExistente->diametro_llanta = $request->diametro_llanta;
                $piezaExistente->diametro_caja_fondo = $request->diametro_caja_fondo;
                $piezaExistente->altura_conexion_fondo = $request->altura_conexion_fondo;
                $piezaExistente->profundidad_llanta = $request->profundidad_llanta;
                $piezaExistente->profundidad_caja_fondo = $request->profundidad_caja_fondo;
                $piezaExistente->simetria = $request->simetria;
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

                $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Acabado Molde')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if (!isset($pieza)) {
                    $pieza = new Pieza();
                }
                $pieza->id_clase = $clase->id;
                $pieza->id_ot = $ot->id;
                $pieza->n_pieza = $request->n_pieza;
                $pieza->id_operador = $meta->id_usuario;
                $pieza->maquina = $meta->maquina;
                $pieza->proceso = "Acabado Molde";
                $pieza->error = $piezaExistente->error;
                $pieza->save();
                if ($pieza->error == 'Ninguno') {
                    //Obtener piezas de la meta
                    $piezasMeta = AcabadoMolde_pza::where('id_meta', $meta->id)->get();
                    $this->controladorPzasLiberadas->liberarPiezasMeta($meta, $piezasMeta, $pieza->n_pieza, "Acabado Molde");
                }

                //Actualizar resultado de la meta
                $pzasCorrectas = AcabadoMolde_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
                Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                    'resultado' => $pzasCorrectas->count(),
                ]);
                $meta = Metas::find($meta->id); //Busco la meta de la OT.

                $pzasAcabadoM = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
                $pzasCalificado = Pieza::where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('proceso', 'Revision Calificado')->where('error', 'Ninguno')->get();
                $pzasRestantes = $this->piezasRestantes($pzasCalificado, $pzasAcabadoM);
                //  //Retornar la pieza siguiente
                $pzaUtilizar = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first();
                if (isset($pzaUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Acabado Molde
                    $pzasCreadas = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get();
                    return view('processes.revAcabadosMolde', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Cepillado.
                } else {
                    //Actualizar solo dos registros de las piezas que se van a ocupada de Acabado Molde
                    $this->piezaUtilizar($ot->id, $clase);
                }
            }
        } else if (isset($request->n_juegoElegido)) {
            $juegoExiste = AcabadoMolde_pza::where('n_juego', $request->n_juegoElegido)->where('id_proceso', $id_proceso->id)->first();
            if (!$juegoExiste) {
                $newPza = new AcabadoMolde_pza(); //Creación de objeto para llenar tabla Acabado Molde
                $newPza->id_pza = $request->n_juegoElegido . $id_proceso->id; //Creación de id para tabla Acabado Molde
                $newPza->id_meta = $meta->id; //Llenado de id_meta para tabla Acabado Molde
                $newPza->id_proceso = $id_proceso->id; //Llenado de id_proceso para tabla Acabado Molde
                $newPza->estado = 1; //Llenado de estado para tabla Acabado Molde
                $newPza->n_juego = $request->n_juegoElegido; //Llenado de estado para tabla Acabado Molde
                $newPza->save(); //Guardado de datos en la tabla Acabado Molde
            }
        }
        $id_proceso = AcabadoMolde::where('id_proceso', $id)->first();
        if ($id_proceso !== "[]") {
            $pzasCreadas = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
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
            $pzasMeta = AcabadoMolde_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.

            if (isset($cNominal) && isset($tolerancia)) {
                $pzaUtilizar = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
                if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Acabado Molde
                    $piezasVacias = AcabadoMolde_pza::where('error', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
                    if (isset($piezasVacias) && $piezasVacias->count() > 0) { //Si existen piezas vacias, se busca una pieza para utilizar.
                        for ($i = 0; $i < count($piezasVacias); $i++) { //Recorro las piezas creadas.
                            $metaAnterior = Metas::where('id', $piezasVacias[$i]->id_meta)->first(); //Obtención de la meta anterior.
                            if ($metaAnterior->maquina == $meta->maquina) { //Si la meta anterior es igual a la meta actual, se utiliza la pieza.
                                $piezasVacias[$i]->id_meta = $meta->id; //Llenado de id_meta para tabla Pza_AcabadoMolde.
                                $piezasVacias[$i]->save(); //Guardado de datos en tabla Pza_AcabadoMolde.
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
                    return view('processes.revAcabadosMolde', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Cepillado.
                } else {
                    return view('processes.revAcabadosMolde', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
                }
            } else {
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.revAcabadosMolde', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        }
    }
    public function compararDatosPieza($pieza, $cNominal, $tolerancia) //Función para comparar los datos de la pieza con los datos nominales y de tolerancia.
    {
        if ($pieza->diametro_mordaza > ($cNominal->diametro_mordaza + $tolerancia->diametro_mordaza1) || $pieza->diametro_mordaza < ($cNominal->diametro_mordaza - $tolerancia->diametro_mordaza2) || $pieza->diametro_ceja > ($cNominal->diametro_ceja + $tolerancia->diametro_ceja1) || $pieza->diametro_ceja < ($cNominal->diametro_ceja - $tolerancia->diametro_ceja2) || $pieza->diametro_sufridera > ($cNominal->diametro_sufridera + $tolerancia->diametro_sufridera1) || $pieza->diametro_sufridera < ($cNominal->diametro_sufridera - $tolerancia->diametro_sufridera2) || $pieza->altura_mordaza > ($cNominal->altura_mordaza + $tolerancia->altura_mordaza1) || $pieza->altura_mordaza < ($cNominal->altura_mordaza - $tolerancia->altura_mordaza2) || $pieza->altura_ceja > ($cNominal->altura_ceja + $tolerancia->altura_ceja1) || $pieza->altura_ceja < ($cNominal->altura_ceja - $tolerancia->altura_ceja2) || $pieza->altura_sufridera > ($cNominal->altura_sufridera + $tolerancia->altura_sufridera1) || $pieza->altura_sufridera < ($cNominal->altura_sufridera - $tolerancia->altura_sufridera2) || $pieza->diametro_conexion_fondo > ($cNominal->diametro_conexion_fondo + $tolerancia->diametro_conexion_fondo1) || $pieza->diametro_conexion_fondo < ($cNominal->diametro_conexion_fondo - $tolerancia->diametro_conexion_fondo2) || $pieza->diametro_llanta > ($cNominal->diametro_llanta + $tolerancia->diametro_llanta1) || $pieza->diametro_llanta < ($cNominal->diametro_llanta - $tolerancia->diametro_llanta2) || $pieza->diametro_caja_fondo > ($cNominal->diametro_caja_fondo + $tolerancia->diametro_caja_fondo1) || $pieza->diametro_caja_fondo < ($cNominal->diametro_caja_fondo - $tolerancia->diametro_caja_fondo2) || $pieza->altura_conexion_fondo > ($cNominal->altura_conexion_fondo + $tolerancia->altura_conexion_fondo1) || $pieza->altura_conexion_fondo < ($cNominal->altura_conexion_fondo - $tolerancia->altura_conexion_fondo2) || $pieza->profundidad_llanta > ($cNominal->profundidad_llanta + $tolerancia->profundidad_llanta1) || $pieza->profundidad_llanta < ($cNominal->profundidad_llanta - $tolerancia->profundidad_llanta2) || $pieza->profundidad_caja_fondo > ($cNominal->profundidad_caja_fondo + $tolerancia->profundidad_caja_fondo1) || $pieza->profundidad_caja_fondo < ($cNominal->profundidad_caja_fondo - $tolerancia->profundidad_caja_fondo2) || $pieza->simetria > ($cNominal->simetria + $tolerancia->simetria1) || $pieza->simetria < ($cNominal->simetria - $tolerancia->simetria2)) {
            return 0; //Si los datos de la pieza son diferentes a los nominales y de tolerancia, se retorna 0.
        } else {
            return 1; //Si los datos de la pieza son iguales a los nominales y de tolerancia, se retorna 1.
        }
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
        $id = "acabadoMolde_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Acabado Molde.
        $id_proceso = AcabadoMolde::where('id_proceso', $id)->first();;
        $cNominal = AcabadoMolde_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $tolerancia = AcabadoMolde_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
        $pzasCreadas = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
        $pzasAcabadoM = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->get();
        $pzasCalificado = Pieza::where('id_ot', $ot->id)->where('id_clase', $clase->id)->where('proceso', 'Revision Calificado')->where('error', 'Ninguno')->get();
        $pzasRestantes = $this->piezasRestantes($pzasCalificado, $pzasAcabadoM);
        $pzaUtilizar = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
        if (isset($request->n_pieza)) { //Si se obtienen los datos de las piezas, se guardan en la tabla AcabadoMolde_cnominal.
            for ($i = 0; $i < count($request->n_pieza); $i++) {
                $id_pieza = $request->n_pieza[$i] . $id_proceso->id; //Creación de id para tabla AcabadoMolde_cnominal.
                $piezaExistente = AcabadoMolde_pza::where('id_pza', $id_pieza)->first();
                if ($piezaExistente) {
                    $piezaExistente->diametro_mordaza = $request->diametro_mordaza[$i];
                    $piezaExistente->diametro_ceja = $request->diametro_ceja[$i];
                    $piezaExistente->diametro_sufridera = $request->diametro_sufridera[$i];
                    $piezaExistente->altura_mordaza = $request->altura_mordaza[$i];
                    $piezaExistente->altura_ceja = $request->altura_ceja[$i];
                    $piezaExistente->altura_sufridera = $request->altura_sufridera[$i];
                    $piezaExistente->gauge_ceja = $request->gauge_ceja[$i];
                    $piezaExistente->altura_total = $request->altura_total[$i];
                    $piezaExistente->diametro_conexion_fondo = $request->diametro_conexion_fondo[$i];
                    $piezaExistente->diametro_llanta = $request->diametro_llanta[$i];
                    $piezaExistente->diametro_caja_fondo = $request->diametro_caja_fondo[$i];
                    $piezaExistente->altura_conexion_fondo = $request->altura_conexion_fondo[$i];
                    $piezaExistente->profundidad_llanta = $request->profundidad_llanta[$i];
                    $piezaExistente->profundidad_caja_fondo = $request->profundidad_caja_fondo[$i];
                    $piezaExistente->simetria = $request->simetria[$i];
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

                    $pieza = Pieza::where('n_pieza', $piezaExistente->n_juego)->where('proceso', 'Acabado Molde')->where('id_ot', $ot->id)->where('id_clase', $clase->id)->first();
                    //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                    if (!isset($pieza)) {
                        $pieza = new Pieza(); //Creación del obejeto para llenar la tabla pieza.
                    }
                    $pieza->id_clase = $clase->id; //Lenado de id_clase para la tabla pieza.
                    $pieza->id_ot = $ot->id;
                    $pieza->n_pieza = $piezaExistente->n_juego;
                    $pieza->id_operador = $meta->id_usuario;
                    $pieza->maquina = $meta->maquina;
                    $pieza->proceso = "Acabado Molde";
                    $pieza->error = $piezaExistente->error;
                    $pieza->save();
                    if ($pieza->error == 'Ninguno') {
                        //Obtener piezas de la meta
                        $piezasMeta = AcabadoMolde_pza::where('id_meta', $meta->id)->get();
                        $this->controladorPzasLiberadas->liberarPiezasMeta($meta, $piezasMeta, $pieza->n_pieza, "Acabado Molde");
                    }
                }
            }
            //Actualizar resultado de la meta
            $pzasMeta = AcabadoMolde_pza::where('id_meta', $meta->id)->where('error', 'Ninguno')->get(); //Obtención de todas las piezas correctas.
            Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
                'resultado' => $pzasMeta->count(),
            ]);
            $meta = Metas::find($meta->id); //Busco la meta de la OT.
            //Retornar la pieza siguiente
            $pzaUtilizar = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $piezasVacias = AcabadoMolde_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
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
                    $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                }
            }
            $pzasCreadas = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = AcabadoMolde_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = AcabadoMolde_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                return view('processes.revAcabadosMolde', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.revAcabadosMolde', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        } else {
            if (isset($request->password)) { //Si se ingreso una contraseña y la meta existe entonces...
                $usersPasswords = User::all(); //Obtengo todas las contraseñas.
                foreach ($usersPasswords as $userPassword) { //Recorro las contraseñas.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {  //Si la contraseña es correcta.
                        return view('processes.revAcabadosMolde', ['band' => 4, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'clase' => $clase, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'pzasRestantes' => $pzasRestantes]); //Retorno la vista de cepillado.
                    }
                }
            }
            $pzaUtilizar = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where('estado', 1)->where('id_meta', $meta->id)->first(); //Obtención de la pieza a utilizar.
            if ($pzaUtilizar == null) { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $piezasVacias = AcabadoMolde_pza::where('correcto', null)->where('estado', 1)->where('id_proceso', $id_proceso->id)->get();
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
                    $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                }
            }
            $pzasCreadas = AcabadoMolde_pza::where('id_proceso', $id_proceso->id)->where('estado', 2)->where('id_meta', $meta->id)->get(); //Obtención de todas las piezas creadas.
            $cNominal = AcabadoMolde_cnominal::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            $tolerancia = AcabadoMolde_tolerancia::where('id_proceso', $id)->first(); //Busco la meta de la OT.
            if (isset($pzasUtilizar)) { //Si existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                return view('processes.revAcabadosMolde', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => $pzasUtilizar, 'pzasRestantes' => $pzasRestantes]); //Retorno a vista de Cepillado.
            } else { //Si no existe una pieza para utilizar, se retorna a la vista de Desbaste Exterior.
                $pzasUtilizar = $this->piezaUtilizar($ot->id, $clase); //Llamado a función para obtener las piezas disponibles.
                return view('processes.revAcabadosMolde', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $meta, 'cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'nPiezas' => $pzasCreadas, 'clase' => $clase, 'piezasUtilizar' => array(), 'piezaElegida' => $pzaUtilizar, 'pzasRestantes' => $pzasRestantes])->with('success', 'Se han registrado todas las piezas correctamente'); //Retorno a vista de Cepillado.
            }
        }
    }

    public function piezaUtilizar($ot, $clase) //Función para obtener la pieza a utilizar.
    {
        $pzasUtilizar = array();
        $pzasGuardadas = array();
        $procesos = Procesos::where('id_clase', $clase->id)->first();

        //Obtener las piezas que esten terminadas y correctas en la tabla Rectificado para despues comparar cada una con su consecuente y asi armar los juegos
        $id_proceso = "acabadoMolde_" . $clase->nombre . "_" . $ot;
        $proceso = AcabadoMolde::where('id_proceso', $id_proceso)->first();
        $pzasOcupadas = AcabadoMolde_pza::where('id_proceso', $proceso->id)->where('estado', 1)->get(); //Obtención de todas las piezas creadas.
        if ($proceso) {
            $pzasUsadas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Acabado Molde')->get(); //Obtención de todas las piezas creadas en Rectificado
        }

        if ($procesos->calificado != 0) {
            //Obtener las piezas solamente en el proceso de Calificado
            $pzasEncontradas = Pieza::where('id_ot', $ot)->where('id_clase', $clase->id)->where('proceso', 'Revision Calificado')->where('error', 'Ninguno')->get();
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
            } //Recorro las piezas ocupadas en Rectificado
        }
        if (count($pzasOcupadas) > 0) {
            for ($x = 0; $x < count($pzasOcupadas); $x++) {
                array_push($numerosUsados, $pzasOcupadas[$x]->n_juego); //Guardo el número de pieza usada.
            }
        }
        for ($i = 0; $i < count($pzasEncontradas); $i++) { //Recorro las piezas encontradas de Rectificado
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
