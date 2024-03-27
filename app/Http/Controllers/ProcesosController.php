<?php

namespace App\Http\Controllers;

use App\Models\AcabadoBombilo_cnominal;
use App\Models\AcabadoBombilo_tolerancia;
use App\Models\AcabadoMolde_cnominal;
use App\Models\AcabadoMolde_tolerancia;
use App\Models\BarrenoManiobra_cnominal;
use App\Models\BarrenoManiobra_tolerancia;
use App\Models\BarrenoProfundidad;
use App\Models\BarrenoProfundidad_cnominal;
use App\Models\BarrenoProfundidad_tolerancia;
use App\Models\Cavidades_cnominal;
use App\Models\Cavidades_tolerancia;
use App\Models\Cepillado;
use App\Models\Cepillado_cnominal;
use App\Models\Cepillado_tolerancia;
use App\Models\Clase;
use App\Models\Copiado_cnominal;
use App\Models\Copiado_tolerancia;
use App\Models\Desbaste_cnominal;
use App\Models\Desbaste_tolerancia;
use App\Models\EmbudoCM_cnominal;
use App\Models\EmbudoCM_tolerancias;
use App\Models\Metas;
use App\Models\OffSet_cnominal;
use App\Models\OffSet_tolerancia;
use App\Models\Orden_trabajo;
use App\Models\Palomas_cnominal;
use App\Models\Palomas_tolerancia;
use App\Models\Pieza;
use App\Models\PrimeraOpeSoldadura_cnominal;
use App\Models\PrimeraOpeSoldadura_tolerancia;
use App\Models\Procesos;
use App\Models\PySOpeSoldadura_cnominal;
use App\Models\PySOpeSoldadura_tolerancia;
use App\Models\Pza_cepillado;
use App\Models\Rebajes;
use App\Models\Rebajes_cnominal;
use App\Models\Rebajes_tolerancia;
use App\Models\revCalificado_cnominal;
use App\Models\revCalificado_tolerancia;
use App\Models\RevLaterales_cnominal;
use App\Models\RevLaterales_tolerancia;
use App\Models\SegundaOpeSoldadura_cnominal;
use App\Models\SegundaOpeSoldadura_tolerancia;
use ArchTech\Enums\Meta\Meta;
use Illuminate\Http\Request;

class ProcesosController extends Controller
{
    public function show()
    {
        $ot = Orden_trabajo::all();
        return view('processesAdmin.procesos', ['ot' => $ot]);
    }
    public function verificarProceso(Request $request)
    {
        if (isset($request->ot)) {
            $clasesFound = Clase::where('id_ot', $request->ot)->get();
            if ($clasesFound) {
                $clases = array(); //Creación del array para almacenar las clases.
                $procesos = array();
                $contador = 0;
                foreach ($clasesFound as $class) {
                    $proceso = Procesos::where('id_clase', $class->id)->first();
                    if ($proceso) {
                        $proceso = $proceso->toArray();
                        $clases[$contador][0] = $class;
                        $camposNoCero = array_filter($proceso, function ($valor) {
                            return $valor != 0;
                        });
                        $procesos[$contador] = array();
                        foreach (array_keys($camposNoCero) as $nombreCampo) {
                            array_push($procesos[$contador], $nombreCampo);
                        }
                        array_splice($procesos[$contador], 0, 2);
                        $procesos[$contador] = $this->convertirString($procesos[$contador]);
                        $contador++;
                    }
                }
            }
            return view('processesAdmin.procesos', ['ot' => $request->ot, 'clases' => $clases, 'procesos' => $procesos]);
        } else {
            $clase = Clase::find($request->clase); //Busqueda de clase 
            switch ($request->proceso) { //Verificación de proceso.
                case 'Cepillado':
                    $id_proceso = 'Cepillado_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = Cepillado_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla Cepillado_cnominal.
                    $tolerancia = Cepillado_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla Cepillado_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas Cepillado_cnominal y Cepillado_tolerancia.
                        $this->actualizarPiezas($id_proceso, $cNominal, $tolerancia, 'Cepillado'); //Llamando a la función actualizarPiezas.
                        $existe = 1; //Variable para verificar existencia de datos en tablas Cepillado_cnominal y Cepillado_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas Cepillado_cnominal y Cepillado_tolerancia.
                        if (isset($request->cNomi_radiof_mordaza)) { //Verificación de existencia de datos en tabla Cepillado_cnominal.
                            $cNominal = new Cepillado_cnominal(); //Creación de objeto Cepillado_cnominal.
                            $tolerancia = new Cepillado_tolerancia(); //Creación de objeto Cepillado_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas Cepillado_cnominal y Cepillado_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_radiof_mordaza)) { //Verificación de ela existencia de datos en la tabla Cepillado_cnominal.
                        $array = $this->cepillado($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editToleCepillado.
                        $this->actualizarPiezas($id_proceso, $cNominal, $tolerancia, 'Cepillado'); //Llamando a la función actualizarPiezas.
                        $cNomi = $array[0]; //Creación de objeto Cepillado_cnominal
                        $tole = $array[1]; //Creación de objeto Cepillado_tolerancia.
                        $cNominal = $cNomi->toArray();
                        $tolerancia = $tole->toArray();
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;

                case 'Desbaste Exterior':
                    $id_proceso = 'desbaste_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = Desbaste_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla Desbaste_cnominal.
                    $tolerancia = Desbaste_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla Desbaste_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas Desbaste_cnominal y Desbaste_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas Desbaste_cnominal y Desbaste_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas Cepillado_cnominal y Desbaste_tolerancia.
                        if (isset($request->cNomi_diametro_mordaza)) { //Verificación de existencia de datos en tabla Desbaste_cnominal.
                            $cNominal = new Desbaste_cnominal(); //Creación de objeto Desbaste_cnominal.
                            $tolerancia = new Desbaste_tolerancia(); //Creación de objeto Desbaste_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas Desbaste_cnominal y Desbaste_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_diametro_mordaza)) { //Verificación de ela existencia de datos en la tabla Desbaste_cnominal.
                        $array = $this->desbasteExterior($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editToleDesbaste.
                        $cNomi = $array[0]; //Creación de objeto Desbaste_cnominal
                        $tole = $array[1]; //Creación de objeto Desbaste_tolerancia.
                        $cNominal = $cNomi->toArray();
                        $tolerancia = $tole->toArray();
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;
                case 'Revision Laterales':
                    $id_proceso = 'revLaterales_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = RevLaterales_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla RevLaterales_cnominal.
                    $tolerancia = RevLaterales_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla RevLaterales_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas RevLaterales_cnominal y RevLaterales_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas RevLaterales_cnominal y RevLaterales_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas RevLaterales_cnominal y RevLaterales_tolerancia.
                        if (isset($request->cNomi_desfasamiento_entrada)) { //Verificación de existencia de datos en tabla RevLaterales_cnominal.
                            $cNominal = new RevLaterales_cnominal(); //Creación de objeto RevLaterales_cnominal.
                            $tolerancia = new RevLaterales_tolerancia(); //Creación de objeto RevLaterales_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas RevLaterales_cnominal y RevLaterales_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_desfasamiento_entrada)) { //Verificación de ela existencia de datos en la tabla RevLaterales_cnominal.
                        $array = $this->revisionLaterales($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editToleRevLaterales.
                        $cNomi = $array[0]; //Creación de objeto Revlaterales_cnominal
                        $tole = $array[1]; //Creación de objeto RevLaterales_tolerancia.
                        $cNominal = $cNomi->toArray();
                        $tolerancia = $tole->toArray();
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;
                case 'Primera Operacion':
                    $id_proceso = '1opeSoldadura_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = PrimeraOpeSoldadura_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla 1opeSoldadura_cnominal.
                    $tolerancia = PrimeraOpeSoldadura_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla 1opeSoldadura_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas 1opeSoldadura_cnominal y 1opeSoldadura_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas 1opeSoldadura_cnominal y 1opeSoldadura_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas 1opeSoldadura_cnominal y 1opeSoldadura_tolerancia.
                        if (isset($request->cNomi_diametro1)) { //Verificación de existencia de datos en tabla 1opeSoldadura_cnominal.
                            $cNominal = new PrimeraOpeSoldadura_cnominal(); //Creación de objeto 1opeSoldadura_cnominal.
                            $tolerancia = new PrimeraOpeSoldadura_tolerancia(); //Creación de objeto 1opeSoldadura_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas 1opeSoldadura_cnominal y 1opeSoldadura_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_diametro1)) { //Verificación de ela existencia de datos en la tabla 1opeSoldadura_cnominal.
                        $array = $this->primeraOpeSoldadura($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editTole1opeSoldadura.
                        $cNomi = $array[0]; //Creación de objeto 1opeSoldadura_cnominal
                        $tole = $array[1]; //Creación de objeto 1opeSoldadura_tolerancia.
                        $cNominal = $cNomi->toArray();
                        $tolerancia = $tole->toArray();
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;
                case 'Barreno Maniobra':
                    $id_proceso = 'barrenoManiobra_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = BarrenoManiobra_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla de BarrenoManiobra_cnominal.
                    $tolerancia = BarrenoManiobra_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla de BarrenoManiobra_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas de BarrenoManiobra_cnominal y BarrenoManiobra_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas de BarrenoManiobra_cnominal y BarrenoManiobra_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas de BarrenoManiobra_cnominal y BarrenoManiobra_tolerancia.
                        if (isset($request->cNomi_profundidadBarreno)) { //Verificación de existencia de datos en tabla 1opeSoldadura_cnominal.
                            $cNominal = new BarrenoManiobra_cnominal(); //Creación de objeto 1opeSoldadura_cnominal.
                            $tolerancia = new BarrenoManiobra_tolerancia(); //Creación de objeto 1opeSoldadura_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas 1opeSoldadura_cnominal y 1opeSoldadura_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_profundidadBarreno)) { //Verificación de ela existencia de datos en la tabla BarrenoManiobra_cnominal.
                        $array = $this->barrenoManiobra($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editBarrenoManiobra.
                        $cNomi = $array[0]; //Creación de objeto BarrenoManiobra_cnominal
                        $tole = $array[1]; //Creación de objeto BarrenoManiobra_tolerancia.
                        $cNominal = $cNomi->toArray();
                        $tolerancia = $tole->toArray();
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;

                case 'Segunda Operacion':
                    $id_proceso = '2opeSoldadura_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = SegundaOpeSoldadura_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla 2opeSoldadura_cnominal.
                    $tolerancia = SegundaOpeSoldadura_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla 2opeSoldadura_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas 2opeSoldadura_cnominal y 2opeSoldadura_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas 2opeSoldadura_cnominal y 2opeSoldadura_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas 2opeSoldadura_cnominal y 2opeSoldadura_tolerancia.
                        if (isset($request->cNomi_diametro1)) { //Verificación de existencia de datos en tabla 2opeSoldadura_cnominal.
                            $cNominal = new SegundaOpeSoldadura_cnominal(); //Creación de objeto 2opeSoldadura_cnominal.
                            $tolerancia = new SegundaOpeSoldadura_tolerancia(); //Creación de objeto 2opeSoldadura_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas 2opeSoldadura_cnominal y 2opeSoldadura_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_diametro1)) { //Verificación de ela existencia de datos en la tabla 2opeSoldadura_cnominal.
                        $array = $this->segundaOpeSoldadura($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editTole2opeSoldadura.
                        $cNomi = $array[0]; //Creación de objeto 2opeSoldadura_cnominal
                        $tole = $array[1]; //Creación de objeto 2opeSoldadura_tolerancia.
                        $cNominal = $cNomi->toArray();
                        $tolerancia = $tole->toArray();
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;
                case 'Calificado':
                    $id_proceso = 'revCalificado_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = revCalificado_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla RevCalificado_cnominal.
                    $tolerancia = revCalificado_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla RevCalificado_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas RevCalificado_cnominal y RevCalificado_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas RevCalificado_cnominal y RevCalificado_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas RevCalificado_cnominal y 2opeSoldadura_tolerancia.
                        if (isset($request->cNomi_diametro_ceja)) { //Verificación de existencia de datos en tabla RevCalificado_cnominal.
                            $cNominal = new revCalificado_cnominal(); //Creación de objeto RevCalificado_cnominal.
                            $tolerancia = new revCalificado_tolerancia(); //Creación de objeto RevCalificado_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas RevCalificado_cnominal y RevCalificado_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_diametro_ceja)) { //Verificación de ela existencia de datos en la tabla RevCalificado_cnominal.
                        $array = $this->calificado($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editToleRevCalificado
                        $cNomi = $array[0]; //Creación de objeto RevCalificado_cnominal
                        $tole = $array[1]; //Creación de objeto RevCalificado_tolerancia.
                        $cNominal = $cNomi->toArray();
                        $tolerancia = $tole->toArray();
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;
                case 'Acabado Bombillo':
                    $id_proceso = 'acabadoBombillo_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = AcabadoBombilo_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla AcabadoBombillo_cnominal.
                    $tolerancia = AcabadoBombilo_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla AcabadoBombillo_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas AcabadoBombillo_cnominal y AcabadoBombillo_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas AcabadoBombillo_cnominal y AcabadoBombillo_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas AcabadoBombillo_cnominal y AcabadoBombillo_tolerancia.
                        if (isset($request->cNomi_diametro_mordaza)) { //Verificación de existencia de datos en tabla AcabadoBombillo_cnominal.
                            $cNominal = new AcabadoBombilo_cnominal(); //Creación de objeto AcabadoBombillo_cnominal.
                            $tolerancia = new AcabadoBombilo_tolerancia(); //Creación de objeto AcabadoBombillo_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas AcabadoBombillo_cnominal y AcabadoBombillo_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_diametro_mordaza)) { //Verificación de ela existencia de datos en la tabla AcabadoBombillo_cnominal.
                        $array = $this->acabadoBombillo($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editToleAcabadoBombillo
                        $cNomi = $array[0]; //Creación de objeto AcabadoBombillo_cnominal
                        $tole = $array[1]; //Creación de objeto AcabadoBombillo_tolerancia.
                        $cNominal = $cNomi->toArray();
                        $tolerancia = $tole->toArray();
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;
                case 'Acabado Molde':
                    $id_proceso = 'acabadoMolde_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = AcabadoMolde_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla AcabadoMolde_cnominal.
                    $tolerancia = AcabadoMolde_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla AcabadoMolde_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas AcabadoMolde_cnominal y AcabadoMolde_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas AcabadoMolde_cnominal y AcabadoMolde_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas AcabadoMolde_cnominal y AcabadoMolde_tolerancia.
                        if (isset($request->cNomi_diametro_mordaza)) { //Verificación de existencia de datos en tabla AcabadoMolde_cnominal.
                            $cNominal = new AcabadoMolde_cnominal(); //Creación de objeto AcabadoMolde_cnominal.
                            $tolerancia = new AcabadoMolde_tolerancia(); //Creación de objeto AcabadoMolde_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas AcabadoMolde_cnominal y AcabadoMolde_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_diametro_mordaza)) { //Verificación de ela existencia de datos en la tabla AcabadoMolde_cnominal.
                        $array = $this->acabadoMolde($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editToleAcabadoMolde.
                        $cNomi = $array[0]; //Creación de objeto AcabadoMolde_cnominal
                        $tole = $array[1]; //Creación de objeto AcabadoMolde_tolerancia.
                        $cNominal = $cNomi->toArray();
                        $tolerancia = $tole->toArray();
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;
                case 'Barreno Profundidad':
                    $id_proceso = 'barrenoProfundidad_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = BarrenoProfundidad_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla barrenoProfundidad_cnominal.
                    $tolerancia = BarrenoProfundidad_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla barrenoProfundidad_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas barrenoProfundidad_cnominal y barrenoProfundidad_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas barrenoProfundidad_cnominal y barrenoProfundidad_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas barrenoProfundidad_cnominal y barrenoProfundidad_tolerancia.
                        if (isset($request->cNomi_broca1)) { //Verificación de existencia de datos en tabla barrenoProfundidad_cnominal.
                            $cNominal = new BarrenoProfundidad_cnominal(); //Creación de objeto barrenoProfundidad_cnominal.
                            $tolerancia = new BarrenoProfundidad_tolerancia(); //Creación de objeto barrenoProfundidad_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas barrenoProfundidad_cnominal y barrenoProfundidad_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_broca1)) { //Verificación de ela existencia de datos en la tabla barrenoProfundidad_cnominal.
                        $array = $this->barrenoProfundidad($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editTolebarrenoProfundidad.
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;
                case 'Cavidades':
                    $id_proceso = 'cavidades_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = Cavidades_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla Cavidades_cnominal.
                    $tolerancia = Cavidades_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla Cavidades_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas Cavidades_cnominal y Cavidades_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas Cavidades_cnominal y Cavidades_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas Cavidades_cnominal y Cavidades_tolerancia.
                        if (isset($request->cNomi_profundidad1)) { //Verificación de existencia de datos en tabla Cavidades_cnominal.
                            $cNominal = new Cavidades_cnominal(); //Creación de objeto Cavidades_cnominal.
                            $tolerancia = new Cavidades_tolerancia(); //Creación de objeto Cavidades_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas Cavidades_cnominal y Cavidades_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_profundidad1)) { //Verificación de ela existencia de datos en la tabla Cavidades_cnominal.
                        $array = $this->cavidades($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editToleCavidades.
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;
                case 'Copiado':
                    $id_proceso = 'copiado_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = Copiado_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla copiado_cnominal.
                    $tolerancia = Copiado_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla copiado_tolerancia.
                    if ($request->subproceso == 'Cilindrado') {
                        if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas copiado_cnominal y copiado_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas copiado_cnominal y copiado_tolerancia.
                        } else {
                            $existe = 0; //Variable para verificar existencia de datos en tablas copiado_cnominal y copiado_tolerancia.
                            if (isset($request->cNomi_diametro1_cilindrado)) { //Verificación de existencia de datos en tabla copiado_cnominal.
                                $cNominal = new Copiado_cnominal(); //Creación de objeto copiado_cnominal.
                                $tolerancia = new Copiado_tolerancia(); //Creación de objeto copiado_tolerancia.
                                $existe = 1; //Variable para verificar existencia de datos en tablas copiado_cnominal y copiado_tolerancia.
                            } else {
                                return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot, 'subproceso' => $request->subproceso]); //Retorno a vista de procesos.
                            }
                        }
                        if (isset($request->cNomi_diametro1_cilindrado)) { //Verificación de ela existencia de datos en la tabla copiado_cnominal.
                            $array = $this->copiado($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editToleCopiado.
                        }
                    } else {
                        if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas copiado_cnominal y copiado_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas copiado_cnominal y copiado_tolerancia.
                        } else {
                            $existe = 0; //Variable para verificar existencia de datos en tablas copiado_cnominal y copiado_tolerancia.
                            if (isset($request->cNomi_diametro1_cavidades)) { //Verificación de existencia de datos en tabla copiado_cnominal.
                                $cNominal = new Copiado_cnominal(); //Creación de objeto copiado_cnominal.
                                $tolerancia = new Copiado_tolerancia(); //Creación de objeto copiado_tolerancia.
                                $existe = 1; //Variable para verificar existencia de datos en tablas copiado_cnominal y copiado_tolerancia.
                            } else {
                                return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot, 'subproceso' => $request->subproceso]); //Retorno a vista de procesos.
                            }
                        }
                        if (isset($request->cNomi_diametro1_cavidades)) { //Verificación de ela existencia de datos en la tabla copiado_cnominal.
                            $array = $this->copiado($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editToleCopiado
                        }
                    }
                    $subproceso = $request->subproceso;
                    $operacion = 0;
                    break;
                case 'Off Set':
                    $id_proceso = 'offSet_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = OffSet_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla OffSet_cnominal.
                    $tolerancia = OffSet_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla OffSet_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas OffSet_cnominal y OffSet_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas OffSet_cnominal y OffSet_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas OffSet_cnominal y OffSet_tolerancia.
                        if (isset($request->cNomi_anchoRanura)) { //Verificación de existencia de datos en tabla OffSet_cnominal.
                            $cNominal = new OffSet_cnominal(); //Creación de objeto OffSet_cnominal.
                            $tolerancia = new OffSet_tolerancia(); //Creación de objeto OffSet_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas OffSet_cnominal y OffSet_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_anchoRanura)) { //Verificación de ela existencia de datos en la tabla OffSet_cnominal.
                        $array = $this->offSet($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editToleOffSet.
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;
                case 'Palomas':
                    $id_proceso = 'palomas_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = Palomas_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla Palomas_cnominal.
                    $tolerancia = Palomas_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla Palomas_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas Palomas_cnominal y Palomas_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas Palomas_cnominal y Palomas_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas Palomas_cnominal y Palomas_tolerancia.
                        if (isset($request->cNomi_ancho_paloma)) { //Verificación de existencia de datos en tabla Palomas_cnominal.
                            $cNominal = new Palomas_cnominal(); //Creación de objeto Palomas_cnominal.
                            $tolerancia = new Palomas_tolerancia(); //Creación de objeto Palomas_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas Palomas_cnominal y Palomas_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_ancho_paloma)) { //Verificación de ela existencia de datos en la tabla Palomas_cnominal.
                        $array = $this->palomas($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editTolePalomas.
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;
                case 'Rebajes':
                    $id_proceso = 'rebajes_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = Rebajes_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla Rebajes_cnominal.
                    $tolerancia = Rebajes_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla Rebajes_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas Rebajes_cnominal y Rebajes_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas Rebajes_cnominal y Rebajes_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas Rebajes_cnominal y Rebajes_tolerancia.
                        if (isset($request->cNomi_rebaje1)) { //Verificación de existencia de datos en tabla Rebajes_cnominal.
                            $cNominal = new Rebajes_cnominal(); //Creación de objeto Rebajes_cnominal.
                            $tolerancia = new Rebajes_tolerancia(); //Creación de objeto Rebajes_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas Rebajes_cnominal y Palomas_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_rebaje1)) { //Verificación de ela existencia de datos en la tabla Rebajes_cnominal.
                        $array = $this->rebajes($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editToleRebajes.
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;
                case '1 y 2 Operacion Equipo':
                    $id_proceso = '1y2opeSoldadura_' . $clase->nombre . "_" . $clase->id_ot . "_" . $request->operacion; //Creación de id_proceso.
                    $cNominal = PySOpeSoldadura_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla 1y2opeSoldadura_cnominal.
                    $tolerancia = PySOpeSoldadura_tolerancia::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla 1y2opeSoldadura_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas 1y2opeSoldadura__cnominal y 1y2opeSoldadura__tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas 1y2opeSoldadura__cnominal y 1y2opeSoldadura__tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas 1y2opeSoldadura__cnominal y 1y2opeSoldadura__tolerancia.
                        if (isset($request->cNomi_altura)) { //Verificación de existencia de datos en tabla 1y2opeSoldadura__cnominal.
                            $cNominal = new PySOpeSoldadura_cnominal(); //Creación de objeto 1y2opeSoldadura__cnominal.
                            $tolerancia = new PySOpeSoldadura_tolerancia(); //Creación de objeto 1y2opeSoldadura__tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas 1y2opeSoldadura__cnominal y 1y2opeSoldadura__tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot, 'operacion' => $request->operacion]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_altura)) { //Verificación de ela existencia de datos en la tabla 1y2opeSoldadura__cnominal.
                        $array = $this->pysOpeSoldadura($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editTole1y2opeSoldadura_.
                        $cNomi = $array[0]; //Creación de objeto 1y2opeSoldadura__cnominal
                        $tole = $array[1]; //Creación de objeto 1y2opeSoldadura__tolerancia.
                        $cNominal = $cNomi->toArray();
                        $tolerancia = $tole->toArray();
                    }
                    $subproceso = 0;
                    $operacion = $request->operacion;
                    break;
                case 'Embudo CM':
                    $id_proceso = 'embudoCM_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
                    $cNominal = EmbudoCM_cnominal::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla Rebajes_cnominal.
                    $tolerancia = EmbudoCM_tolerancias::where('id_proceso', $id_proceso)->first(); //Verificación de existencia de datos en tabla Rebajes_tolerancia.
                    if (isset($cNominal) && isset($tolerancia)) { //Verificación de existencia de datos en tablas Rebajes_cnominal y Rebajes_tolerancia.
                        $existe = 1; //Variable para verificar existencia de datos en tablas Rebajes_cnominal y Rebajes_tolerancia.
                    } else {
                        $existe = 0; //Variable para verificar existencia de datos en tablas Rebajes_cnominal y Rebajes_tolerancia.
                        if (isset($request->cNomi_conexion_lineaPartida)) { //Verificación de existencia de datos en tabla Rebajes_cnominal.
                            $cNominal = new EmbudoCM_cnominal(); //Creación de objeto Rebajes_cnominal.
                            $tolerancia = new EmbudoCM_tolerancias(); //Creación de objeto Rebajes_tolerancia.
                            $existe = 1; //Variable para verificar existencia de datos en tablas Rebajes_cnominal y Palomas_tolerancia.
                        } else {
                            return view('processesAdmin.procesos', ['existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot]); //Retorno a vista de procesos.
                        }
                    }
                    if (isset($request->cNomi_conexion_lineaPartida)) { //Verificación de ela existencia de datos en la tabla Rebajes_cnominal.
                        $array = $this->embudoCM($id_proceso, $cNominal, $tolerancia, $request); //Llamando a la función editToleRebajes.
                    }
                    $subproceso = 0;
                    $operacion = 0;
                    break;
            }
            return view('processesAdmin.procesos', ['cNominal' => $cNominal, 'tolerancia' => $tolerancia, 'existe' => $existe, 'proceso' => $request->proceso, 'clase' => $clase, 'ot' => $clase->id_ot, 'subproceso' => $subproceso, 'operacion' => $operacion]); //Retorno a vista de procesos.
        }
    }
    public function convertirString($procesos)
    {
        $stringProcesos = array();
        foreach ($procesos as $proceso) {
            switch ($proceso) {
                case "cepillado":
                    array_push($stringProcesos, "Cepillado");
                    break;
                case "desbaste_exterior":
                    array_push($stringProcesos, "Desbaste Exterior");
                    break;
                case "revision_laterales":
                    array_push($stringProcesos, "Revision Laterales");
                    break;
                case "pOperacion":
                    array_push($stringProcesos, "Primera Operacion");
                    break;
                case "barreno_maniobra":
                    array_push($stringProcesos, "Barreno Maniobra");
                    break;
                case "sOperacion":
                    array_push($stringProcesos, "Segunda Operacion");
                    break;
                case "calificado":
                    array_push($stringProcesos, "Calificado");
                    break;
                case "acabadoBombillo":
                    array_push($stringProcesos, "Acabado Bombillo");
                    break;
                case "acabadoMolde":
                    array_push($stringProcesos, "Acabado Molde");
                    break;
                case "barreno_profundidad":
                    array_push($stringProcesos, "Barreno Profundidad");
                    break;
                case "cavidades":
                    array_push($stringProcesos, "Cavidades");
                    break;
                case "copiado":
                    array_push($stringProcesos, "Copiado");
                    break;
                case "offSet":
                    array_push($stringProcesos, "Off Set");
                    break;
                case "palomas":
                    array_push($stringProcesos, "Palomas");
                    break;
                case "rebajes":
                    array_push($stringProcesos, "Rebajes");
                    break;
                case "grabado":
                    array_push($stringProcesos, "Grabado");
                    break;
                case "operacionEquipo":
                    array_push($stringProcesos, "1 y 2 Operacion Equipo");
                    break;
                case "embudoCM":
                    array_push($stringProcesos, "Embudo CM");
                    break;
            }
        }
        return $stringProcesos;
    }
    //Se actualiza las piezas de cada proceso para verificar que este correcta
    public function actualizarPiezas($id_proceso, $cNominal, $tolerancia, $proceso)
    {
        switch ($proceso) {
            case 'Cepillado':
                $idProceso = Cepillado::where('id_proceso', $id_proceso)->first();
                if ($idProceso) {
                    $piezas = Pza_cepillado::where('id_proceso', $idProceso->id)->where('estado', 2)->get();

                    if ($piezas->count() > 0) {
                        $controladorCepillado = new CepilladoController();
                        foreach ($piezas as $pieza) {
                            $this->actualizarError($controladorCepillado, $pieza, $cNominal, $tolerancia, $idProceso, $proceso);
                            //Actualizar resultado de la meta
                            $pzasCorrectas = Pza_cepillado::where('id_meta', $pieza->id_meta)->where('correcto', 1)->get(); //Obtención de todas las piezas correctas.
                            $meta = Metas::find($pieza->id_meta);
                            $this->actualizarMetas($pzasCorrectas, $meta);
                        }
                    }
                }
                break;
        }
    }
    public function actualizarError($controlador, $piezaControlador, $cNominal, $tolerancia, $proceso, $stringProceso)
    {
        if ($controlador->compararDatosPieza($piezaControlador, $cNominal, $tolerancia) == 0) {
            $piezaControlador->error = 'Maquinado';
            $piezaControlador->correcto = 0;
        } else {
            $piezaControlador->error = 'Ninguno';
            $piezaControlador->correcto = 1;
        }
        $piezaControlador->save();

        $clases = Clase::where('id_ot', $proceso->id_ot)->get();
        foreach ($clases as $clase) {
            $id_proceso = $stringProceso . '_' . $clase->nombre . "_" . $clase->id_ot; //Creación de id_proceso.
            if ($proceso->id_proceso == $id_proceso) {
                $pieza = Pieza::where('n_pieza', $piezaControlador->n_pieza)->where('id_ot', $proceso->id_ot)->where('id_clase', $clase->id)->where('proceso', $stringProceso)->first();
                //Guardar los datos de las pieza en la tabla pieza (En donde se almacenan todas las piezas)
                if (!isset($pieza)) {
                    $pieza = new Pieza();
                }
                $pieza->error = $piezaControlador->error;
                $pieza->save();
                break;
            }
        }
    }
    public function actualizarMetas($pzasCorrectas, $meta)
    {
        $contadorPzas = 0;
        $juegosUsados = array();
        foreach ($pzasCorrectas as $pzaCorrecta) {
            $pzaCorrecta2 = Pza_cepillado::where('n_juego', $pzaCorrecta->n_juego)->where('id_meta', $meta->id)->where('correcto', 1)->get();
            if (count($pzaCorrecta2) == 2) {
                if (!in_array($pzaCorrecta->n_juego, $juegosUsados)) {
                    array_push($juegosUsados, $pzaCorrecta->n_juego);
                    $contadorPzas++;
                }
            }
        }
        Metas::where('id', $meta->id)->update([ //Actualización de datos en tabla Metas.
            'resultado' => $contadorPzas,
        ]);
    }
    public function cepillado($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla Cepillado_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla Cepillado_cnominal.
        $cNominal->radiof_mordaza = $request->cNomi_radiof_mordaza; //Llenado de radiof_mordaza para tabla Cepillado_cnominal.
        $cNominal->radiof_mayor = $request->cNomi_radiof_mayor;
        $cNominal->radiof_sufridera = $request->cNomi_radiof_sufridera;
        $cNominal->profuFinal_CFC = $request->cNomi_profuFinal_CFC;
        $cNominal->profuFinal_mitadMB = $request->cNomi_profuFinal_mitadMB;
        $cNominal->profuFinal_PCO = $request->cNomi_profuFinal_PCO;
        $cNominal->ensamble = $request->cNomi_ensamble;
        $cNominal->distancia_barrenoAli = $request->cNomi_distancia_barrenoAli;
        $cNominal->profu_barrenoAliHembra = $request->cNomi_profu_barrenoAliHembra;
        $cNominal->profu_barrenoAliMacho = $request->cNomi_profu_barrenoAliMacho;
        $cNominal->altura_venaHembra = $request->cNomi_altura_venaHembra;
        $cNominal->altura_venaMacho = $request->cNomi_altura_venaMacho;
        $cNominal->ancho_vena = $request->cNomi_ancho_vena;
        $cNominal->pin1 = $request->Hpin1[0];
        $cNominal->pin2 = $request->Hpin2[0];

        //Llenado de tabla Cepillado_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->radiof_mordaza1 = $request->tole_radiof_mordaza1;
        $tolerancia->radiof_mordaza2 = $request->tole_radiof_mordaza2;
        $tolerancia->radiof_mayor1 = $request->tole_radiof_mayor1;
        $tolerancia->radiof_mayor2  = $request->tole_radiof_mayor2;
        $tolerancia->radiof_sufridera1 = $request->tole_radiof_sufridera1;
        $tolerancia->radiof_sufridera2 = $request->tole_radiof_sufridera2;
        $tolerancia->profuFinal_CFC1 = $request->tole_profuFinal_CFC1;
        $tolerancia->profuFinal_CFC2 = $request->tole_profuFinal_CFC2;
        $tolerancia->profuFinal_mitadMB1 = $request->tole_profuFinal_mitadMB1;
        $tolerancia->profuFinal_mitadMB2 = $request->tole_profuFinal_mitadMB2;
        $tolerancia->profuFinal_PCO1 = $request->tole_profuFinal_PCO1;
        $tolerancia->profuFinal_PCO2 = $request->tole_profuFinal_PCO2;
        $tolerancia->ensamble1 = $request->tole_ensamble1;
        $tolerancia->ensamble2 = $request->tole_ensamble2;
        $tolerancia->distancia_barrenoAli1 = $request->tole_distancia_barrenoAli1;
        $tolerancia->distancia_barrenoAli2 = $request->tole_distancia_barrenoAli2;
        $tolerancia->profu_barrenoAliHembra1 = $request->tole_profu_barrenoAliHembra1;
        $tolerancia->profu_barrenoAliHembra2 = $request->tole_profu_barrenoAliHembra2;
        $tolerancia->profu_barrenoAliMacho1 = $request->tole_profu_barrenoAliMacho1;
        $tolerancia->profu_barrenoAliMacho2 = $request->tole_profu_barrenoAliMacho2;
        $tolerancia->altura_venaHembra1 = $request->tole_altura_venaHembra1;
        $tolerancia->altura_venaHembra2 = $request->tole_altura_venaHembra2;
        $tolerancia->altura_venaMacho1 = $request->tole_altura_venaMacho1;
        $tolerancia->altura_venaMacho2 = $request->tole_altura_venaMacho2;
        $tolerancia->ancho_vena1 = $request->tole_ancho_vena1;
        $tolerancia->ancho_vena2 = $request->tole_ancho_vena2;
        $tolerancia->pin1 = $request->Hpin1[1];
        $tolerancia->pin2 = $request->Hpin2[1];

        $cNominal->save(); //Guardado de datos en tabla Cepillado_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla Cepillado_tolerancia.
        return array($cNominal, $tolerancia);
        //Marco puto
    }

    public function desbasteExterior($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla desbaste_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla desbaste_cnominal.
        $cNominal->diametro_mordaza = $request->cNomi_diametro_mordaza;
        $cNominal->diametro_ceja = $request->cNomi_diametro_ceja;
        $cNominal->diametro_sufrideraExtra = $request->cNomi_diametro_sufrideraExtra;
        $cNominal->simetria_ceja = $request->cNomi_simetria_ceja;
        $cNominal->simetria_mordaza = $request->cNomi_simetria_mordaza;
        $cNominal->altura_ceja = $request->cNomi_altura_ceja;
        $cNominal->altura_sufridera = $request->cNomi_altura_sufridera;

        //Llenado de tabla desbaste_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->diametro_mordaza1 = $request->tole_diametro_mordaza1;
        $tolerancia->diametro_mordaza2 = $request->tole_diametro_mordaza2;
        $tolerancia->diametro_ceja1 = $request->tole_diametro_ceja1;
        $tolerancia->diametro_ceja2  = $request->tole_diametro_ceja2;
        $tolerancia->diametro_sufrideraExtra1 = $request->tole_diametro_sufrideraExtra1;
        $tolerancia->diametro_sufrideraExtra2 = $request->tole_diametro_sufrideraExtra2;
        $tolerancia->simetria_ceja1 = $request->tole_simetria_ceja1;
        $tolerancia->simetria_ceja2 = $request->tole_simetria_ceja2;
        $tolerancia->simetria_mordaza1 = $request->tole_simetria_mordaza1;
        $tolerancia->simetria_mordaza2 = $request->tole_simetria_mordaza2;
        $tolerancia->altura_ceja1 = $request->tole_altura_ceja1;
        $tolerancia->altura_ceja2 = $request->tole_altura_ceja2;
        $tolerancia->altura_sufridera1 = $request->tole_altura_sufridera1;
        $tolerancia->altura_sufridera2 = $request->tole_altura_sufridera2;

        $cNominal->save(); //Guardado de datos en tabla Desbaste_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla Desbaste_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function revisionLaterales($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla revLaterales_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla revLaterales_cnominal.
        $cNominal->desfasamiento_entrada = $request->cNomi_desfasamiento_entrada;
        $cNominal->desfasamiento_salida = $request->cNomi_desfasamiento_salida;
        $cNominal->ancho_simetriaEntrada = $request->cNomi_ancho_simetriaEntrada;
        $cNominal->ancho_simetriaSalida = $request->cNomi_ancho_simetriaSalida;
        $cNominal->angulo_corte = $request->cNomi_angulo_corte;

        //Llenado de tabla revLaterales_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->desfasamiento_entrada1 = $request->tole_desfasamiento_entrada1;
        $tolerancia->desfasamiento_entrada2 = $request->tole_desfasamiento_entrada2;
        $tolerancia->desfasamiento_salida1 = $request->tole_desfasamiento_salida1;
        $tolerancia->desfasamiento_salida2  = $request->tole_desfasamiento_salida2;
        $tolerancia->ancho_simetriaEntrada1 = $request->tole_ancho_simetriaEntrada1;
        $tolerancia->ancho_simetriaEntrada2 = $request->tole_ancho_simetriaEntrada2;
        $tolerancia->ancho_simetriaSalida1 = $request->tole_ancho_simetriaSalida1;
        $tolerancia->ancho_simetriaSalida2 = $request->tole_ancho_simetriaSalida2;
        $tolerancia->angulo_corte1 = $request->tole_angulo_corte1;
        $tolerancia->angulo_corte2 = $request->tole_angulo_corte2;

        $cNominal->save(); //Guardado de datos en tabla revLaterales_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla revLaterales_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function primeraOpeSoldadura($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla primeraOpeSoldadura
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla primeraOpeSoldadura_cnominal.
        $cNominal->diametro1 = $request->cNomi_diametro1;
        $cNominal->profundidad1 = $request->cNomi_profundidad1;
        $cNominal->diametro2 = $request->cNomi_diametro2;
        $cNominal->profundidad2 = $request->cNomi_profundidad2;
        $cNominal->diametro3 = $request->cNomi_diametro3;
        $cNominal->profundidad3 = $request->cNomi_profundidad3;
        $cNominal->diametroSoldadura = $request->cNomi_diametroSoldadura;
        $cNominal->diametroBarreno = $request->cNomi_diametroBarreno;
        $cNominal->profundidadSoldadura = $request->cNomi_profundidadSoldadura;
        $cNominal->simetriaLinea_partida = $request->cNomi_simetriaLinea_partida;
        $cNominal->pernoAlineacion = $request->cNomi_pernoAlineacion;
        $cNominal->Simetria90G = $request->cNomi_Simetria90G;

        //Llenado de tabla primeraOpeSoldadura_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->diametro1 = $request->tole_diametro1;
        $tolerancia->profundidad1 = $request->tole_profundidad1;
        $tolerancia->diametro2 = $request->tole_diametro2;
        $tolerancia->profundidad2  = $request->tole_profundidad2;
        $tolerancia->diametro3 = $request->tole_diametro3;
        $tolerancia->profundidad3 = $request->tole_profundidad3;
        $tolerancia->diametroSoldadura = $request->tole_diametroSoldadura;
        $tolerancia->profundidadSoldadura = $request->tole_profundidadSoldadura;
        $tolerancia->diametroBarreno1 = $request->tole_diametroBarreno1;
        $tolerancia->diametroBarreno2 = $request->tole_diametroBarreno2;
        $tolerancia->simetriaLinea_partida1 = $request->tole_simetriaLinea_partida1;
        $tolerancia->simetriaLinea_partida2 = $request->tole_simetriaLinea_partida2;
        $tolerancia->pernoAlineacion = $request->tole_pernoAlineacion;
        $tolerancia->Simetria90G = $request->tole_Simetria90G;

        $cNominal->save(); //Guardado de datos en tabla primeraOpeSoldadura_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla primeraOpeSoldadura_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function barrenoManiobra($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla BarrenoManiobra_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla BarrenoManiobra_cnominal.
        $cNominal->profundidad_barreno = $request->cNomi_profundidadBarreno;
        $cNominal->diametro_machuelo = $request->cNomi_diametro_machuelo;

        //Llenado de tabla BarrenoManiobra_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->profundidad_barreno1 = $request->tole_profundidadBarreno1;
        $tolerancia->profundidad_barreno2 = $request->tole_profundidadBarreno2;
        $tolerancia->diametro_machuelo1 = $request->tole_diametro_machuelo1;
        $tolerancia->diametro_machuelo2 = $request->tole_diametro_machuelo2;

        $cNominal->save(); //Guardado de datos en tabla barrenoManiobra_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla barrenoManiobra_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function segundaOpeSoldadura($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla segundaOpeSoldadura_cnominal.
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla segundaOpeSoldadura_cnominal.
        $cNominal->diametro1 = $request->cNomi_diametro1;
        $cNominal->profundidad1 = $request->cNomi_profundidad1;
        $cNominal->diametro2 = $request->cNomi_diametro2;
        $cNominal->profundidad2 = $request->cNomi_profundidad2;
        $cNominal->diametro3 = $request->cNomi_diametro3;
        $cNominal->profundidad3 = $request->cNomi_profundidad3;
        $cNominal->diametroSoldadura = $request->cNomi_diametroSoldadura;
        $cNominal->profundidadSoldadura = $request->cNomi_profundidadSoldadura;
        $cNominal->alturaTotal = $request->cNomi_alturaTotal;
        $cNominal->Simetria90G = $request->cNomi_Simetria90G;
        $cNominal->simetriaLinea_partida = $request->cNomi_simetriaLinea_partida;

        //Llenado de tabla segundaOpeSoldadura_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->diametro1 = $request->tole_diametro1;
        $tolerancia->profundidad1 = $request->tole_profundidad1;
        $tolerancia->diametro2 = $request->tole_diametro2;
        $tolerancia->profundidad2  = $request->tole_profundidad2;
        $tolerancia->diametro3 = $request->tole_diametro3;
        $tolerancia->profundidad3 = $request->tole_profundidad3;
        $tolerancia->diametroSoldadura = $request->tole_diametroSoldadura;
        $tolerancia->profundidadSoldadura = $request->tole_profundidadSoldadura;
        $tolerancia->alturaTotal1 = $request->tole_alturaTotal1;
        $tolerancia->alturaTotal2 = $request->tole_alturaTotal2;
        $tolerancia->Simetria90G1 = $request->tole_Simetria90G1;
        $tolerancia->Simetria90G2 = $request->tole_Simetria90G2;
        $tolerancia->simetriaLinea_partida = $request->tole_simetriaLinea_partida;

        $cNominal->save(); //Guardado de datos en tabla segundaOpeSoldadura_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla segundaOpeSoldadura_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function calificado($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla calificado_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla calificado_cnominal.
        $cNominal->diametro_ceja = $request->cNomi_diametro_ceja;
        $cNominal->diametro_sufridera = $request->cNomi_diametro_sufridera;
        $cNominal->altura_sufridera = $request->cNomi_altura_sufridera;
        $cNominal->diametro_conexion = $request->cNomi_diametro_conexion;
        $cNominal->altura_conexion = $request->cNomi_altura_conexion;
        $cNominal->diametro_caja = $request->cNomi_diametro_caja;
        $cNominal->altura_caja = $request->cNomi_altura_caja;
        $cNominal->altura_total = $request->cNomi_altura_total;
        $cNominal->simetria = $request->cNomi_simetria;

        //Llenado de tabla calificado_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->diametro_ceja1 = $request->tole_diametro_ceja1;
        $tolerancia->diametro_ceja2 = $request->tole_diametro_ceja2;
        $tolerancia->diametro_sufridera1 = $request->tole_diametro_sufridera1;
        $tolerancia->diametro_sufridera2  = $request->tole_diametro_sufridera2;
        $tolerancia->altura_sufridera1 = $request->tole_altura_sufridera1;
        $tolerancia->altura_sufridera2 = $request->tole_altura_sufridera2;
        $tolerancia->diametro_conexion1 = $request->tole_diametro_conexion1;
        $tolerancia->diametro_conexion2 = $request->tole_diametro_conexion2;
        $tolerancia->altura_conexion1 = $request->tole_altura_conexion1;
        $tolerancia->altura_conexion2 = $request->tole_altura_conexion2;
        $tolerancia->diametro_caja1 = $request->tole_diametro_caja1;
        $tolerancia->diametro_caja2 = $request->tole_diametro_caja2;
        $tolerancia->altura_caja1 = $request->tole_altura_caja1;
        $tolerancia->altura_caja2 = $request->tole_altura_caja2;
        $tolerancia->altura_total1 = $request->tole_altura_total1;
        $tolerancia->altura_total2 = $request->tole_altura_total2;
        $tolerancia->simetria1 = $request->tole_simetria1;
        $tolerancia->simetria2 = $request->tole_simetria2;

        $cNominal->save(); //Guardado de datos en tabla calificado_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla calificado_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function acabadoBombillo($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla acabadoBombillo_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla acabadoBombillo_cnominal.
        $cNominal->diametro_mordaza = $request->cNomi_diametro_mordaza;
        $cNominal->diametro_ceja = $request->cNomi_diametro_ceja;
        $cNominal->diametro_sufridera = $request->cNomi_diametro_sufridera;
        $cNominal->altura_mordaza = $request->cNomi_altura_mordaza;
        $cNominal->altura_ceja = $request->cNomi_altura_ceja;
        $cNominal->altura_sufridera = $request->cNomi_altura_sufridera;
        $cNominal->diametro_boca = $request->cNomi_diametro_boca;
        $cNominal->diametro_asiento_corona = $request->cNomi_diametro_asiento_corona;
        $cNominal->diametro_llanta = $request->cNomi_diametro_llanta;
        $cNominal->diametro_caja_corona = $request->cNomi_diametro_caja_corona;
        $cNominal->profundidad_corona = $request->cNomi_profundidad_corona;
        $cNominal->angulo_30 = $request->cNomi_angulo_30;
        $cNominal->profundidad_caja_corona = $request->cNomi_profundidad_caja_corona;
        $cNominal->simetria = $request->cNomi_simetria;

        //Llenado de tabla acabadoBombillo_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->diametro_mordaza1 = $request->tole_diametro_mordaza1;
        $tolerancia->diametro_mordaza2 = $request->tole_diametro_mordaza2;
        $tolerancia->diametro_ceja1 = $request->tole_diametro_ceja1;
        $tolerancia->diametro_ceja2 = $request->tole_diametro_ceja2;
        $tolerancia->diametro_sufridera1 = $request->tole_diametro_sufridera1;
        $tolerancia->diametro_sufridera2 = $request->tole_diametro_sufridera2;
        $tolerancia->altura_mordaza1 = $request->tole_altura_mordaza1;
        $tolerancia->altura_mordaza2 = $request->tole_altura_mordaza2;
        $tolerancia->altura_ceja1 = $request->tole_altura_ceja1;
        $tolerancia->altura_ceja2 = $request->tole_altura_ceja2;
        $tolerancia->altura_sufridera1 = $request->tole_altura_sufridera1;
        $tolerancia->altura_sufridera2 = $request->tole_altura_sufridera2;
        $tolerancia->diametro_boca1 = $request->tole_diametro_boca1;
        $tolerancia->diametro_boca2 = $request->tole_diametro_boca2;
        $tolerancia->diametro_asiento_corona1 = $request->tole_diametro_asiento_corona1;
        $tolerancia->diametro_asiento_corona2 = $request->tole_diametro_asiento_corona2;
        $tolerancia->diametro_llanta1 = $request->tole_diametro_llanta1;
        $tolerancia->diametro_llanta2 = $request->tole_diametro_llanta2;
        $tolerancia->diametro_caja_corona1 = $request->tole_diametro_caja_corona1;
        $tolerancia->diametro_caja_corona2 = $request->tole_diametro_caja_corona2;
        $tolerancia->profundidad_corona1 = $request->tole_profundidad_corona1;
        $tolerancia->profundidad_corona2 = $request->tole_profundidad_corona2;
        $tolerancia->angulo_301 = $request->tole_angulo_301;
        $tolerancia->angulo_302 = $request->tole_angulo_302;
        $tolerancia->profundidad_caja_corona1 = $request->tole_profundidad_caja_corona1;
        $tolerancia->profundidad_caja_corona2 = $request->tole_profundidad_caja_corona2;
        $tolerancia->simetria1 = $request->tole_simetria1;
        $tolerancia->simetria2 = $request->tole_simetria2;

        $cNominal->save(); //Guardado de datos en tabla acabadoBombillo_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla acabadoBombillo_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function acabadoMolde($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla acabadoMolde_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla acabadoMolde_cnominal.
        $cNominal->diametro_mordaza = $request->cNomi_diametro_mordaza;
        $cNominal->diametro_ceja = $request->cNomi_diametro_ceja;
        $cNominal->diametro_sufridera = $request->cNomi_diametro_sufridera;
        $cNominal->altura_mordaza = $request->cNomi_altura_mordaza;
        $cNominal->altura_ceja = $request->cNomi_altura_ceja;
        $cNominal->altura_sufridera = $request->cNomi_altura_sufridera;
        $cNominal->diametro_conexion_fondo = $request->cNomi_diametro_conexion_fondo;
        $cNominal->diametro_llanta = $request->cNomi_diametro_llanta;
        $cNominal->diametro_caja_fondo = $request->cNomi_diametro_caja_fondo;
        $cNominal->altura_conexion_fondo = $request->cNomi_altura_conexion_fondo;
        $cNominal->profundidad_llanta = $request->cNomi_profundidad_llanta;
        $cNominal->profundidad_caja_fondo = $request->cNomi_profundidad_caja_fondo;
        $cNominal->simetria = $request->cNomi_simetria;

        //Llenado de tabla acabadoMolde_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->diametro_mordaza1 = $request->tole_diametro_mordaza1;
        $tolerancia->diametro_mordaza2 = $request->tole_diametro_mordaza2;
        $tolerancia->diametro_ceja1 = $request->tole_diametro_ceja1;
        $tolerancia->diametro_ceja2 = $request->tole_diametro_ceja2;
        $tolerancia->diametro_sufridera1 = $request->tole_diametro_sufridera1;
        $tolerancia->diametro_sufridera2 = $request->tole_diametro_sufridera2;
        $tolerancia->altura_mordaza1 = $request->tole_altura_mordaza1;
        $tolerancia->altura_mordaza2 = $request->tole_altura_mordaza2;
        $tolerancia->altura_ceja1 = $request->tole_altura_ceja1;
        $tolerancia->altura_ceja2 = $request->tole_altura_ceja2;
        $tolerancia->altura_sufridera1 = $request->tole_altura_sufridera1;
        $tolerancia->altura_sufridera2 = $request->tole_altura_sufridera2;
        $tolerancia->diametro_conexion_fondo1 = $request->tole_diametro_conexion_fondo1;
        $tolerancia->diametro_conexion_fondo2 = $request->tole_diametro_conexion_fondo2;
        $tolerancia->diametro_llanta1 = $request->tole_diametro_llanta1;
        $tolerancia->diametro_llanta2 = $request->tole_diametro_llanta2;
        $tolerancia->diametro_caja_fondo1 = $request->tole_diametro_caja_fondo1;
        $tolerancia->diametro_caja_fondo2 = $request->tole_diametro_caja_fondo2;
        $tolerancia->altura_conexion_fondo1 = $request->tole_altura_conexion_fondo1;
        $tolerancia->altura_conexion_fondo2 = $request->tole_altura_conexion_fondo2;
        $tolerancia->profundidad_llanta1 = $request->tole_profundidad_llanta1;
        $tolerancia->profundidad_llanta2 = $request->tole_profundidad_llanta2;
        $tolerancia->profundidad_caja_fondo1 = $request->tole_profundidad_caja_fondo1;
        $tolerancia->profundidad_caja_fondo2 = $request->tole_profundidad_caja_fondo2;
        $tolerancia->simetria1 = $request->tole_simetria1;
        $tolerancia->simetria2 = $request->tole_simetria2;

        $cNominal->save(); //Guardado de datos en tabla acabadoMolde_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla acabadoMolde_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function barrenoProfundidad($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla barrenoProfundidad cNominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla acabadoMolde_cnominal.
        $cNominal->broca1 = $request->cNomi_broca1;
        $cNominal->tiempo1 = $request->cNomi_tiempo1;
        $cNominal->broca2 = $request->cNomi_broca2;
        $cNominal->tiempo2 = $request->cNomi_tiempo2;
        $cNominal->broca3 = $request->cNomi_broca3;
        $cNominal->tiempo3 = $request->cNomi_tiempo3;
        $cNominal->entradaSalida = $request->cNomi_entradaSalida;
        $cNominal->diametro_arrastre1 = $request->cNomi_diametro_arrastre1;
        $cNominal->diametro_arrastre2 = $request->cNomi_diametro_arrastre2;
        $cNominal->diametro_arrastre3 = $request->cNomi_diametro_arrastre3;

        //Llenado de tabla barrenoProfundidad tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->broca1 = $request->tole_broca1;
        $tolerancia->tiempo1 = $request->tole_tiempo1;
        $tolerancia->broca2 = $request->tole_broca2;
        $tolerancia->tiempo2  = $request->tole_tiempo2;
        $tolerancia->broca3 = $request->tole_broca3;
        $tolerancia->tiempo3 = $request->tole_tiempo3;
        $tolerancia->entrada = $request->tole_entrada;
        $tolerancia->salida = $request->tole_salida;
        $tolerancia->diametro_arrastre1 = $request->tole_diametro_arrastre1;
        $tolerancia->diametro_arrastre2 = $request->tole_diametro_arrastre2;
        $tolerancia->diametro_arrastre3 = $request->tole_diametro_arrastre3;

        $cNominal->save(); //Guardado de datos en tabla acabadoMolde_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla acabadoMolde_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function pysOpeSoldadura($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla pysOpeSoldadura_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla pysOpeSoldadura_cnominal.
        $cNominal->altura = $request->cNomi_altura;
        $cNominal->alturaCandado1 = $request->cNomi_alturaCandado1;
        $cNominal->alturaCandado2 = $request->cNomi_alturaCandado2;
        $cNominal->alturaAsientoObturador1 = $request->cNomi_alturaAsientoObturador1;
        $cNominal->alturaAsientoObturador2 = $request->cNomi_alturaAsientoObturador2;
        $cNominal->profundidadSoldadura1 = $request->cNomi_profundidadSoldadura1;
        $cNominal->profundidadSoldadura2 = $request->cNomi_profundidadSoldadura2;
        $cNominal->pushUp = $request->cNomi_pushUp;

        //Llenado de tabla pysOpeSoldadura_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->altura = $request->tole_altura;
        $tolerancia->alturaCandado1 = $request->tole_alturaCandado1;
        $tolerancia->alturaCandado2 = $request->tole_alturaCandado2;
        $tolerancia->alturaAsientoObturador1  = $request->tole_alturaAsientoObturador1;
        $tolerancia->alturaAsientoObturador2 = $request->tole_alturaAsientoObturador2;
        $tolerancia->profundidadSoldadura1 = $request->tole_profundidadSoldadura1;
        $tolerancia->profundidadSoldadura2 = $request->tole_profundidadSoldadura2;
        $tolerancia->pushUp = $request->tole_pushUp;

        $cNominal->save(); //Guardado de datos en tabla pysOpeSoldadura_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla pysOpeSoldadura_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function cavidades($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla cavidades_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla cavidades_cnominal.
        $cNominal->profundidad1 = $request->cNomi_profundidad1;
        $cNominal->diametro1 = $request->cNomi_diametro1;
        $cNominal->profundidad2 = $request->cNomi_profundidad2;
        $cNominal->diametro2 = $request->cNomi_diametro2;
        $cNominal->profundidad3 = $request->cNomi_profundidad3;
        $cNominal->diametro3 = $request->cNomi_diametro3;


        //Llenado de tabla cavidades_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->profundidad1_1 = $request->tole_profundidad1_1;
        $tolerancia->profundidad2_1 = $request->tole_profundidad2_1;
        $tolerancia->diametro1_1 = $request->tole_diametro1_1;
        $tolerancia->diametro2_1 = $request->tole_diametro2_1;
        $tolerancia->profundidad1_2 = $request->tole_profundidad1_2;
        $tolerancia->profundidad2_2 = $request->tole_profundidad2_2;
        $tolerancia->diametro1_2 = $request->tole_diametro1_2;
        $tolerancia->diametro2_2 = $request->tole_diametro2_2;
        $tolerancia->profundidad1_3 = $request->tole_profundidad1_3;
        $tolerancia->profundidad2_3 = $request->tole_profundidad2_3;
        $tolerancia->diametro1_3 = $request->tole_diametro1_3;
        $tolerancia->diametro2_3 = $request->tole_diametro2_3;

        $cNominal->save(); //Guardado de datos en tabla cavidades_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla cavidades_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function copiado($id_proceso, $cNominal, $tolerancia, $request)
    {
        if ($request->subproceso == 'Cilindrado') {
            //Llenado de tabla copiado_cnominal en el subproceso Cilindrado.
            $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla copiado_cnominal en el subproceso Cilindrado.
            $cNominal->diametro1_cilindrado = $request->cNomi_diametro1_cilindrado;
            $cNominal->profundidad1_cilindrado = $request->cNomi_profundidad1_cilindrado;
            $cNominal->diametro2_cilindrado = $request->cNomi_diametro2_cilindrado;
            $cNominal->profundidad2_cilindrado = $request->cNomi_profundidad2_cilindrado;
            $cNominal->diametro_sufridera = $request->cNomi_diametro_sufridera;
            $cNominal->diametro_ranura = $request->cNomi_diametro_ranura;
            $cNominal->profundidad_ranura = $request->cNomi_profundidad_ranura;
            $cNominal->profundidad_sufridera = $request->cNomi_profundidad_sufridera;
            $cNominal->altura_Total = $request->cNomi_altura_total;

            //Llenado de tabla copiado_tolerancia en el subproceso Cilindrado.
            $tolerancia->id_proceso = $id_proceso;
            $tolerancia->diametro1_cilindrado = $request->tole_diametro1_cilindrado;
            $tolerancia->profundidad1_cilindrado = $request->tole_profundidad1_cilindrado;
            $tolerancia->diametro2_cilindrado = $request->tole_diametro2_cilindrado;
            $tolerancia->profundidad2_cilindrado = $request->tole_profundidad2_cilindrado;
            $tolerancia->diametro_sufridera = $request->tole_diametro_sufridera;
            $tolerancia->diametro_ranura = $request->tole_diametro_ranura;
            $tolerancia->profundidad_ranura = $request->tole_profundidad_ranura;
            $tolerancia->profundidad_sufridera = $request->tole_profundidad_sufridera;
            $tolerancia->altura_total = $request->tole_altura_total;
        } else {
            $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla copiado_cnominal en el subproceso Cavidades.
            $cNominal->diametro1_cavidades = $request->cNomi_diametro1_cavidades;
            $cNominal->profundidad1_cavidades = $request->cNomi_profundidad1_cavidades;
            $cNominal->diametro2_cavidades = $request->cNomi_diametro2_cavidades;
            $cNominal->profundidad2_cavidades = $request->cNomi_profundidad2_cavidades;
            $cNominal->diametro3 = $request->cNomi_diametro3;
            $cNominal->profundidad3 = $request->cNomi_profundidad3;
            $cNominal->diametro4 = $request->cNomi_diametro4;
            $cNominal->profundidad4 = $request->cNomi_profundidad4;
            $cNominal->volumen = $request->cNomi_volumen;

            //Llenado de tabla copiado_tolerancia en el subproceso Cavidades.
            $tolerancia->id_proceso = $id_proceso;
            $tolerancia->diametro1_cavidades = $request->tole_diametro1_cavidades;
            $tolerancia->profundidad1_cavidades = $request->tole_profundidad1_cavidades;
            $tolerancia->diametro2_cavidades = $request->tole_diametro2_cavidades;
            $tolerancia->profundidad2_cavidades = $request->tole_profundidad2_cavidades;
            $tolerancia->diametro3 = $request->tole_diametro3;
            $tolerancia->profundidad3 = $request->tole_profundidad3;
            $tolerancia->diametro4 = $request->tole_diametro4;
            $tolerancia->profundidad4 = $request->tole_profundidad4;
            $tolerancia->volumen = $request->tole_volumen;
        }
        $cNominal->save(); //Guardado de datos en tabla copiado_cnominal en el subproceso Cilindrado.
        $tolerancia->save(); //Guardado de datos en tabla copiado_tolerancia en el subproceso Cilindrado.
        return array($cNominal, $tolerancia);
    }
    public function offSet($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla OffSet_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla OffSet_cnominal.
        $cNominal->anchoRanura = $request->cNomi_anchoRanura;
        $cNominal->profuTaconHembra = $request->cNomi_profuTaconHembra;
        $cNominal->profuTaconMacho = $request->cNomi_profuTaconMacho;
        $cNominal->simetriaHembra = $request->cNomi_simetriaHembra;
        $cNominal->simetriaMacho = $request->cNomi_simetriaMacho;
        $cNominal->anchoTacon = $request->cNomi_anchoTacon;
        $cNominal->barrenoLateralHembra = $request->cNomi_barrenoLateralHembra;
        $cNominal->barrenoLateralMacho = $request->cNomi_barrenoLateralMacho;
        $cNominal->alturaTaconInicial = $request->cNomi_alturaTaconInicial;
        $cNominal->alturaTaconIntermedia = $request->cNomi_alturaTaconIntermedia;

        //Llenado de tabla OffSet_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->anchoRanura = $request->tole_anchoRanura;
        $tolerancia->profuTaconHembra = $request->tole_profuTaconHembra;
        $tolerancia->profuTaconMacho = $request->tole_profuTaconMacho;
        $tolerancia->simetriaHembra = $request->tole_simetriaHembra;
        $tolerancia->simetriaMacho = $request->tole_simetriaMacho;
        $tolerancia->anchoTacon = $request->tole_anchoTacon;
        $tolerancia->barrenoLateralHembra = $request->tole_barrenoLateralHembra;
        $tolerancia->barrenoLateralMacho = $request->tole_barrenoLateralMacho;
        $tolerancia->alturaTaconInicial = $request->tole_alturaTaconInicial;
        $tolerancia->alturaTaconIntermedia = $request->tole_alturaTaconIntermedia;

        $cNominal->save(); //Guardado de datos en tabla OffSet_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla OffSet_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function palomas($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla Palomas_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla Palomas_cnominal.
        $cNominal->anchoPaloma = $request->cNomi_ancho_paloma;
        $cNominal->gruesoPaloma = $request->cNomi_grueso_paloma;
        $cNominal->profundidadPaloma = $request->cNomi_profundidad_paloma;
        $cNominal->rebajeLlanta = $request->cNomi_rebaje_llanta;

        //Llenado de tabla Palomas_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->anchoPaloma = $request->tole_ancho_paloma;
        $tolerancia->gruesoPaloma = $request->tole_grueso_paloma;
        $tolerancia->profundidadPaloma = $request->tole_profundidad_paloma;
        $tolerancia->rebajeLlanta = $request->tole_rebaje_llanta;;

        $cNominal->save(); //Guardado de datos en tabla Palomas_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla Palomas_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function rebajes($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla Rebajes_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla Rebajes_cnominal.
        $cNominal->rebaje1 = $request->cNomi_rebaje1;
        $cNominal->rebaje2 = $request->cNomi_rebaje2;
        $cNominal->rebaje3 = $request->cNomi_rebaje3;
        $cNominal->profundidad_bordonio = $request->cNomi_profundidad_bordonio;
        $cNominal->vena1 = $request->cNomi_vena1;
        $cNominal->vena2 = $request->cNomi_vena2;
        $cNominal->simetria = $request->cNomi_simetria;

        //Llenado de tabla Rebajes_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->rebaje1 = $request->tole_rebaje1;
        $tolerancia->rebaje2 = $request->tole_rebaje2;
        $tolerancia->rebaje3 = $request->tole_rebaje3;
        $tolerancia->profundidad_bordonio = $request->tole_profundidad_bordonio;;
        $tolerancia->vena1 = $request->tole_vena1;
        $tolerancia->vena2 = $request->tole_vena2;
        $tolerancia->simetria = $request->tole_simetria;

        $cNominal->save(); //Guardado de datos en tabla Rebajes_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla Rebajes_tolerancia.
        return array($cNominal, $tolerancia);
    }
    public function embudoCM($id_proceso, $cNominal, $tolerancia, $request)
    {
        //Llenado de tabla Palomas_cnominal
        $cNominal->id_proceso = $id_proceso; //Llenado de id_proceso para tabla Palomas_cnominal.
        $cNominal->conexion_lineaPartida = $request->cNomi_conexion_lineaPartida;
        $cNominal->conexion_90G = $request->cNomi_conexion_90G;
        $cNominal->altura_conexion = $request->cNomi_altura_conexion;
        $cNominal->diametro_embudo = $request->cNomi_diametro_embudo;

        //Llenado de tabla Palomas_tolerancia
        $tolerancia->id_proceso = $id_proceso;
        $tolerancia->conexion_lineaPartida = $request->tole_conexion_lineaPartida;
        $tolerancia->conexion_90G = $request->tole_conexion_90G;
        $tolerancia->altura_conexion = $request->tole_altura_conexion;
        $tolerancia->diametro_embudo = $request->tole_diametro_embudo;

        $cNominal->save(); //Guardado de datos en tabla Palomas_cnominal.
        $tolerancia->save(); //Guardado de datos en tabla Palomas_tolerancia.
        return array($cNominal, $tolerancia);
    }
}
