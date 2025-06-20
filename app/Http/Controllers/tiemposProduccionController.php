<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Procesos;
use App\Models\tiempoproduccion;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Break_;

class tiemposProduccionController extends Controller
{
    protected $controladorPzas;
    protected $classController;
    public function __construct()
    {
        $this->controladorPzas = new PzasLiberadasController();
        $this->classController = new ClassController();
        $this->middleware('auth');
    }
    public function show($clase = false)
    {

        $wOrdersFounded = Orden_trabajo::all();
        $workOrders = array();
        if (count($wOrdersFounded) > 0) {
            foreach ($wOrdersFounded as $workOrder) {
                $classes = $this->classController->getClasses($workOrder);
                if (count($classes) > 0) {
                    $workOrders[$workOrder->id] = array();
                    foreach ($classes as $class) {
                        $workOrders[$workOrder->id][$class->nombre] = array();
                        $tiemposProduccion = tiempoproduccion::where('id_clase', $class->id)->get();
                        if ($tiemposProduccion->count() > 0) {
                            foreach ($tiemposProduccion as $tiempo) {
                                // Inicializar el array si no existe
                                $workOrders[$workOrder->id][$class->nombre][$tiempo->proceso] = $workOrders[$workOrder->id][$class->nombre][$tiempo->proceso] ?? [];
                                // Inicializar el array de tiempos si no existe
                                foreach ($tiempo->toArray() as $columna => $valor) {
                                    if ($columna == 'id_clase' || $columna == 'clase' || $columna == 'proceso' || $columna == 'tamanio' || $columna == 'created_at' || $columna == 'updated_at') {
                                        continue;
                                    }
                                    $workOrders[$workOrder->id][$class->nombre][$tiempo->proceso][$columna] = $valor;
                                }
                            }
                        } else {
                            $workOrders[$workOrder->id][$class->nombre] = null;
                        }
                    }
                }
            }
        }

        if ($clase) {
            return view('processes_views.productionTimes', compact('workOrders', 'clase'));
        }
        return view('processes_views.productionTimes', compact('workOrders'));
    }
    public function store(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            if ($key == '_token' || $key == "class" || $key == "workOrder") {
                continue;
            }
            $class = Clase::where('nombre', $request->input('class'))->where("id_ot", $request->input('workOrder'))->first();
            $tiempo = tiempoproduccion::where('id_clase', $class->id)->where('proceso', $key)->first();
            if ($tiempo) {
                $tiempo->tamanio = "DISABLED";
                $tiempo->tiempo = $value;
                $tiempo->save();
            } else {
                $tiempo = new tiempoproduccion();
                $tiempo->id_clase = $class->id;
                $tiempo->clase = $request->input('class');
                $tiempo->tamanio = "DISABLED";
                $tiempo->proceso = $key;
                $tiempo->tiempo = $value;
                $tiempo->save();
            }
        }
        $clase = $request->input('class');


        //Actualizar todas las Clases
        $this->update();
        return redirect()->route("showTimes", compact('clase'))->with('success', 'Tiempos de producción actualizados correctamente.');
    }
    public function update()
    {
        $clases = $this->guardarClasesInArray();
        if ($clases != null) {
            //Se hace el algoritmo
            foreach ($clases as $clase) {
                //Se obtienen los procesos de la clase
                $procesos = $this->asignarProcesos($clase[0]->nombre);
                if ($procesos != null) {
                    $this->calcularFechas($procesos, $clase);
                }
            }
        }
    }
    public function guardarClasesInArray()
    {
        //Se obtienen todas las clases de la tabla fechas_procesos
        $idClase = Procesos::select('id_clase')->distinct()->get();

        if ($idClase->count() == 0) {
            return null;
        }
        //Se guardan los procesos de cada clase en una array bidimensional
        $contadorClases = 0;
        $clases = array();
        foreach ($idClase as $id) {
            //Obtener la clase por el id
            $clase = Clase::find($id->id_clase);
            $clases[$contadorClases] = array();
            $clases[$contadorClases][0] = $clase;
            $clases[$contadorClases][1] = array();
            // $clases[$contadorClases][0] = $clase->id; //Distinguir como se conforma el array

            //Obtener todos los procesos creados (tiempos) de esa clase
            $procesos = $this->getProcesos($clase);
            $clases[$contadorClases][1] = $procesos;
            $contadorClases++;
        }
        return $clases;
    }
    public function asignarProcesos($clase)
    {
        switch ($clase) {
            case "Bombillo":
            case "Molde":
                return array("cepillado", "desbaste_exterior", "revision_laterales", "pOperacion", "barreno_maniobra", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado", "calificado", "acabadoBombillo", "acabadoMolde", "barreno_profundidad", "cavidades", "copiado", "offSet", "palomas", "rebajes");
            case "Obturador":
            case "Fondo":
                return array("operacionEquipo", "soldadura", "soldaduraPTA");
                break;
            case "Corona":
                return array("cepillado", "desbaste_exterior");
            case "Plato":
                return array("operacionEquipo", "barreno_profundidad", "soldaduraPTA");
            case "Embudo":
                return array("operacionEquipo", "embudoCM");
            default:
                return;
        }
    }
    public function getProcesos($clase)
    {
        $registroProcesos = Procesos::where('id_clase', $clase->id)->first();
        if ($registroProcesos) {
            $columnas = $registroProcesos->getAttributes();

            $procesos = array_keys(array_filter($columnas, function ($value) {
                return $value != 0;
            }));

            //Eliminar los campos que no son procesos
            $procesos = array_slice($procesos, 2);
            return $procesos;
        }
    }
    public function calcularFechas($procesos, $clase)
    {
        $noProceso = 0;
        for ($i = 0; $i < count($procesos); $i++) {
            $pos = array_search($procesos[$i], $clase[1]);
            if ($pos !== false) {
                $maquinas = $this->obtenerMaquinasClase($clase[0]->id, $clase[1][$pos]);
                $procesoFechas = $this->classController->registerProcessDates($clase[0], $procesos, $i, $noProceso, $maquinas);
                $noProceso++;
            }
        }

        //Guardar unicamente la fecha de termino
        $clase = Clase::find($clase[0]->id);
        $clase->fecha_termino = $procesoFechas->fecha_fin->format('Y-m-d');
        $clase->hora_termino = $procesoFechas->fecha_fin->format('H:i:s');
        // echo $clase->nombre;
        // echo $clase->fecha_termino;
        // echo $clase->hora_termino;
        // echo "<br>";
        $clase->save();
    }
    public function obtenerMaquinasClase($claseID, $proceso)
    {
        $maquinas = Procesos::where('id_clase', $claseID)->distinct()->value($proceso);
        return $maquinas;
    }
}
