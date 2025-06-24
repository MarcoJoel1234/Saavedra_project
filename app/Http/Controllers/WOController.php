<?php

namespace App\Http\Controllers;

use App\Http\Requests\OTRequest;
use App\Models\AcabadoBombilo;
use App\Models\AcabadoMolde;
use App\Models\Asentado;
use App\Models\BarrenoManiobra;
use App\Models\BarrenoProfundidad;
use App\Models\Cavidades;
use App\Models\Cepillado;
use App\Models\Clase;
use App\Models\Copiado;
use App\Models\DesbasteExterior;
use App\Models\EmbudoCM;
use App\Models\Fecha_proceso;
use App\Models\Maquinas;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\OffSet;
use App\Models\Orden_trabajo;
use App\Models\Palomas;
use App\Models\Pieza;
use App\Models\PrimeraOpeSoldadura;
use App\Models\Procesos;
use App\Models\PySOpeSoldadura;
use App\Models\PySOpeSoldadura_pza;
use App\Models\Rebajes;
use App\Models\Rectificado;
use App\Models\revCalificado;
use App\Models\RevLaterales;
use App\Models\SegundaOpeSoldadura;
use App\Models\Soldadura;
use App\Models\SoldaduraPTA;
use App\Models\tiempoproduccion;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WOController extends Controller
{
    protected $classController;
    protected $processesController;

    public function __construct()
    {
        $this->middleware('auth');
        $this->classController = new ClassController();
        $this->processesController = new ProcessesController();
    }

    //Mostrar la vista para seleccionar o crear una Orden de Trabajo
    public function manage()
    {
        $moldings = Moldura::all();
        $workOrdersAll = Orden_trabajo::all();
        //Si existen ordenes de trabajo registradas.
        $workOrders = null;
        if ($workOrdersAll != "[]") {
            $workOrders = []; //Arreglo para guardar las molduras de cada OT
            $counter = 0; // Contador para las molduras y OT
            foreach ($workOrdersAll as $workOrder) { //Recorro las ordenes de trabajo
                if (auth()->user()->perfil == 5) {
                    $clases = Clase::where("id_ot", $workOrder->id)->get();
                    if ($clases->count() == 0) {
                        continue;
                    }
                }
                $moldura = Moldura::find($workOrder->id_moldura);
                $workOrders[$counter]['workOrder'] = $workOrder->id;
                $workOrders[$counter]['molding'] = $moldura->nombre;
                $counter++;
            }
        }
        return view('wo_views.manage_wo', compact('moldings', 'workOrders'));
    }

    public function store(OTRequest $request) //Funcion para registrar una OT.
    {
        if (isset($request->workOrderAdded)) {
            //Creacion de la orden de trabajo registrada
            $newWorkOrder = new Orden_trabajo();
            $newWorkOrder->id = $request->workOrderAdded;
            $newWorkOrder->id_moldura = $request->moldingSelected;
            $newWorkOrder->save();
        }
        //Busqueda de la orden de trabajo ingresada o creada
        $workOrder = Orden_trabajo::find(isset($request->workOrderAdded) ? $request->workOrderAdded : $request->workOrderSelected);

        return redirect()->route('showWO', ['workOrder' => $workOrder]);
    }

    public function show($workOrder)
    {
        $workOrder = Orden_trabajo::find($workOrder);
        $molding = Moldura::find($workOrder->id_moldura);

        //Se obtienen las clases de la Orden de trabajo
        $classes = $this->classController->getClasses($workOrder);
        $classes = $classes->count() == 0 ? null : $classes;

        //Se obtienen las maquinas de los procesos guardados
        $processes = $this->classController->getClassProcesses($classes);

        return view('wo_views.show_wo', compact('workOrder', 'molding', 'classes', 'processes'));
    }

    public function destroy($idWOrder)
    {
        $pieces = Pieza::where('id_ot', $idWOrder)->get(); //Busco las piezas de la OT
        $goal = Metas::where('id_ot', $idWOrder)->get();
        if (count($pieces) == 0 && count($goal) == 0) { //Si la OT no tiene piezas ni metas asociadas entonces
            $classes = Clase::where('id_ot', $idWOrder)->get(); //Busco todas las clases que pertenecen a la OT
            foreach ($classes as $class) { //Recorro las clases de la OT
                $this->classController->destroy($class->id, $idWOrder); //Elimino las clases
            }
            $workOrder = Orden_trabajo::find($idWOrder);
            if ($workOrder) {
                $workOrder->delete(); //Eliminar OT
            }
            return redirect()->route('manageWO')->with('success', '¡Orden de trabajo eliminada con éxito!'); //Redirecciono a la vista de registro de la OT
        }
        return redirect()->route('showWO', ['workOrder' => $idWOrder])->with('error', '¡La orden de trabajo no se puede eliminar porque tiene piezas o metas asociadas!');
    }
    public function generatePDF($idWOrder)
    {
        $workOrder = Orden_trabajo::find($idWOrder);
        $molding = Moldura::find($workOrder->id_moldura);

        $classes = $this->classController->getClasses($workOrder);
        $classes = $classes->count() == 0 ? null : $classes;
        $processes = null;
        if ($classes) {
            $processesFounded = $this->classController->getClassProcesses($classes);
            if ($processesFounded != null) {
                $processes = [];
                //Obtener el nombre del campo del proceso
                foreach ($processesFounded as $idClass => $process) {
                    $processes[$idClass] = "";
                    foreach ($process as $processName => $value) {
                        $processes[$idClass] .= $this->processesController->convertProcessToString($processName) . ", ";
                    }
                }
            }
        }
        $pdf = FacadePdf::loadView('wo_views.pdf_wo', compact('workOrder', 'molding', 'classes', 'processes'));
        return $pdf->download('Orden_de_trabajo_' . $workOrder->id . '.pdf');
    }


    public function getMolding($moldingId)
    {
        $molding = Moldura::find($moldingId);
        return $molding ? $molding->nombre : null;
    }
    public function insertClassesData(&$array, $class)
    {
        $array[$class->nombre] = array();
        $array[$class->nombre]["pieces"] = $class->piezas;
        // $array[$class->nombre]["pieces"] = $class->piezas;
        $array[$class->nombre]["order"] = $class->pedido;
        $array[$class->nombre]["startDate"] = $this->getStringDate($class->fecha_inicio, $class->hora_inicio);
        $array[$class->nombre]["endDate"] = $class->fecha_termino ? $this->getStringDate($class->fecha_termino, $class->hora_termino) : "-";
        $array[$class->nombre]["processes"] = $this->insertProcessesData($class);
    }
    public function insertProcessesData($class)
    {
        $processes = array();
        $processesFounded = Procesos::where('id_clase', $class->id)->first();
        if ($processesFounded) {
            foreach ($processesFounded->getAttributes() as $field => $value) {
                if ($value != 0 && $field != 'id' && $field != 'id_clase') {
                    $processName = $this->processesController->convertProcessToString($field);
                    $processes[$processName] = array();
                    $piecesBadData = array();
                    $processes[$processName]['pieces'] = $this->getPieces($class, $processName, $piecesBadData);
                    $processes[$processName]['piecesBadData'] = $piecesBadData; //Informacion de las piezas malas
                    $processes[$processName]['endDate'] = $this->getDateEndFromProcess($field, $class->id); //Fecha de termino del proceso
                }
            }
        }
        return $processes;
    }
    public function getDateEndFromProcess($process, $class)
    {
        $dateEnd = Fecha_proceso::where('clase', $class)->where('proceso', $process)->first();
        if ($dateEnd) {
            $formattedDate = new DateTime($dateEnd->fecha_fin);
            $formattedDate = $formattedDate->format('d-m-Y');

            $formattedTime = new DateTime($dateEnd->fecha_fin);
            $formattedTime = $formattedTime->format('H:i:s');
            return $this->getStringDate($formattedDate, $formattedTime);
        } else {
            return "---";
        }
    }

    public function showViewPiecesInProgress()
    {
        //Obtener las ordenes de trabajo que aun siguen en progreso, es decir, que tienen clases que no han sido finalizadas.
        $wOInProgress = array();
        $workOrders = Orden_trabajo::all();
        foreach ($workOrders as $workOrder) {
            //Obtener las clases que pertenecen a la orden de trabajo y que no han sido finalizadas.
            $classes = Clase::where('id_ot', $workOrder->id)->where('finalizada', 0)->get();
            if (count($classes) > 0) {
                foreach ($classes as $index => $class) {
                    //Verificar si ya se asignaron procesos a la clase
                    $process = Procesos::where('id_clase', $class->id)->first();
                    if ($process) {
                        if ($index == 0) {
                            $wOInProgress[$workOrder->id] = array();
                            $wOInProgress[$workOrder->id]['molding'] = $this->getMolding($workOrder->id_moldura); //Insertar el nombre de la moldura
                            //Insertar las clases de la orden de trabajo
                            $wOInProgress[$workOrder->id]['classes'] = array();
                        }
                        $this->insertClassesData($wOInProgress[$workOrder->id]['classes'], $class);
                    }
                }
            }
        }
        return view('pieces_views.piecesInProgress_view', compact('wOInProgress'));
    }
    public function getStringDate($date, $time)
    {
        $formattedDate = new DateTime($date);
        $formattedDate = $formattedDate->format('d-m-Y');

        //Establecer la fecha en español
        $dayName = new DateTime($date);
        $dayName = $dayName->format('l');

        switch ($dayName) {
            case "Monday":
                $dayName = "Lunes";
                break;
            case "Tuesday":
                $dayName = "Martes";
                break;
            case "Wednesday":
                $dayName = "Miercoles";
                break;
            case "Thursday":
                $dayName = "Jueves";
                break;
            case "Friday":
                $dayName = "Viernes";
                break;
            case "Saturday":
                $dayName = "Sabado";
                break;
            case "Sunday":
                $dayName = "Domingo";
                break;
        }

        $formattedTime = new DateTime($time);
        $formattedTime = $formattedTime->format('H:i:s A');

        return $dayName . " " . $formattedDate . " " . $formattedTime;
    }
    public function nombreProceso($proceso)
    {
        switch ($proceso) {
            case "cepillado":
                return "Cepillado";
            case "desbaste_exterior":
                return "Desbaste Exterior";
            case "revision_laterales":
                return "Revision Laterales";
            case "pOperacion":
                return "Primera Operacion Soldadura";
            case "barreno_maniobra":
                return "Barreno maniobra";
            case "sOperacion":
                return "Segunda Operacion Soldadura";
            case "soldadura":
                return "Soldadura";
            case "soldaduraPTA":
                return "Soldadura PTA";
            case "rectificado":
                return "Rectificado";
            case "asentado":
                return "Asentado";
            case "calificado":
                return "Revision Calificado";
            case "acabadoBombillo":
                return "Acabado Bombillo";
            case "acabadoMolde":
                return "Acabado Molde";
            case "cavidades":
                return "Cavidades";
            case "barreno_profundidad":
                return "Barreno Profundidad";
            case "copiado":
                return "Copiado";
            case "offSet":
                return "Off Set";
            case "palomas":
                return "Palomas";
            case "rebajes":
                return "Rebajes";
            case "grabado":
                return "Grabado";
            case "operacionEquipo":
                return "Operación Equipo";
            case "embudoCM":
                return "Embudo CM";
        }
    }
    function finishOrder(Request $request)
    {
        $clase = Clase::where('id_ot', $request->wOrderName)->where('nombre', $request->className)->first();
        $clase->finalizada = 1;
        $clase->save();
        return redirect()->route('showPiecesInProgress');
    }
    function getPieces($class, $processName, &$piecesBadData)
    {
        $setStoredParts = array();
        $piecesArray = array();
        $piecesArray["good"] = array();
        $piecesArray["bad"] = array();
        $piecesArray["total"] = 0;
        if ($processName == "Operacion Equipo") {
            $process1 = PySOpeSoldadura::where('id_ot', $class->id_ot)->where('id_proceso', "Operacion_Equipo_1_operacion_" . $class->nombre . "_" . $class->id_ot)->first();
            $process2 = PySOpeSoldadura::where('id_ot', $class->id_ot)->where('id_proceso', "Operacion_Equipo_2_operacion_" . $class->nombre . "_" . $class->id_ot)->first();

            if ($process1 && $process2) {
                //Calcular las piezas totales
                $pieces1 = PySOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $process1->id)->get();
                $pieces2 = PySOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $process2->id)->get();

                $piecesProcess = [$pieces1, $pieces2];
                $totalPieces = array();
                foreach ($piecesProcess as $index => $pieceProcess) {
                    $totalPieces["operacion_" . ($index + 1)] = array();
                    $storedSets = array();
                    foreach ($pieceProcess as $piece) {
                        if (!in_array($piece->n_juego, $storedSets)) {
                            array_push($storedSets, $piece->n_juego);
                            $piecesFounded = PySOpeSoldadura_pza::where('estado', 2)->where('id_proceso', $piece->id_proceso)->where('n_juego', $piece->n_juego)->get();
                            if (count($piecesFounded) > 1) {
                                array_push($totalPieces["operacion_" . ($index + 1)], $piece->n_juego);
                            }
                        }
                    }
                }

                //Obtener piezas Totales en las dos operaciones
                $counterTotalPieces = 0;
                foreach ($totalPieces["operacion_2"] as $piece2) {
                    if (in_array($piece2, $totalPieces["operacion_1"])) {
                        $counterTotalPieces++;
                    } else {
                        $counterTotalPieces += .5;
                    }
                }

                //Obtener las piezas buenas
                if ($process1) { //Si existe el proceso
                    $goodPieces = array();
                    foreach ($totalPieces as $index => $piecesOperation) {
                        $storedSets = array();
                        $process = $index == "operacion_1" ? $process1 : $process2;
                        $goodPieces["operacion_" . ($index + 1)] = array();
                        foreach ($piecesOperation as $piece) {
                            $piecesFounded = PySOpeSoldadura_pza::where('estado', 2)->where('error', 'Ninguno')->where('id_proceso', $process->id)->where('n_juego', $piece)->get();
                            if (count($piecesFounded) > 1) {
                                array_push($goodPieces["operacion_" . ($index + 1)], $piece);
                            }
                        }
                    }
                    $counterGoodPieces = 0;
                    //Obtener piezas buenas en las dos operaciones
                    foreach ($goodPieces["operacion_2"] as $goodSet2) {
                        if (in_array($goodSet2, $goodPieces["operacion_1"])) {
                            $counterGoodPieces++;
                        }
                    }


                    //Obtener las piezas malas en cada operación
                    $badPieces = array();
                    foreach ($totalPieces as $index => $piecesOperation) {
                        $storedSets = array();
                        $process = $index == "operacion_1" ? $process1 : $process2;
                        $badPieces["operacion_" . ($index + 1)] = array();
                        foreach ($piecesOperation as $piece) {
                            $piecesFounded = PySOpeSoldadura_pza::where('estado', 2)->where('correcto', 0)->where('id_proceso', $process->id)->where('n_juego', $piece)->get();
                            if (count($piecesFounded) > 1) {
                                array_push($badPieces["operacion_" . ($index + 1)], $piece->n_juego);
                                foreach ($piecesFounded as $badPiece) {
                                    if ($badPiece->error != "Ninguno") {
                                        $pieceFounded = Pieza::where('n_pieza', $badPiece->n_juego)->where('proceso', $processName)->where('id_clase', $class->id)->first();
                                        array_push($piecesBadData, $this->getBadPiecesData($pieceFounded, null, $index + 1));
                                    }
                                }
                            }
                        }
                    }

                    $counterBadPieces = 0;
                    //Obtener piezas malas en las dos operaciones
                    $badSets = array();
                    if (count($badPieces["operacion_2"]) > count($badPieces["operacion_1"])) {
                        foreach ($badPieces["operacion_2"] as $badPiece2) {
                            if (!in_array($badPiece2, $badPieces["operacion_1"])) {
                                $counterBadPieces++;
                                array_push($badSets, $badPiece2);
                            }
                        }
                    } else {
                        foreach ($badPieces["operacion_1"] as $badPiece1) {
                            if (!in_array($badPiece1, $badPieces["operacion_2"])) {
                                $counterBadPieces++;
                                array_push($badSets, $badPiece1);
                            }
                        }
                    }

                    //Obtener toda la informacion de todas las piezas malas encontradas
                    foreach ($badPieces["operacion_2"] as $piece) {
                        if (!in_array($piece, $badSets)) {
                            $counterBadPieces++;
                        }
                    }
                    foreach ($badPieces["operacion_1"] as $piece) {
                        if (!in_array($piece, $badSets)) {
                            $counterBadPieces++;
                        }
                    }
                } else {
                    $counterGoodPieces = 0;
                    $counterBadPieces = 0;
                    $counterTotalPieces = 0;
                }
            } else {
                $counterGoodPieces = 0;
                $counterBadPieces = 0;
                $counterTotalPieces = 0;
            }
            $piecesArray["total"] = $counterTotalPieces;
            $piecesArray["good"] = $counterGoodPieces;
            $piecesArray["bad"] = $counterBadPieces;
        } else {
            $pieces = Pieza::where("proceso", $processName)->where('id_clase', $class->id)->get();

            if (count($pieces) > 0) {
                //Recorrer cada una de las piezas
                foreach ($pieces as $piece) {
                    //Verificar si es juego o pieza
                    if (substr($piece->n_pieza, -1, 1) != "J") { // Si no es un juego y se divide en hembra y macho
                        $pares = true;
                        preg_match('/^\d+/', $piece->n_pieza, $noSet); //Obtener el numero de juego de la pieza
                        $noSet = $noSet[0];
                        //Comprobar si el juego ya fue almacenado en el array
                        if (!in_array($noSet, $setStoredParts)) {
                            array_push($setStoredParts, $noSet); //Almacenar el juego en el array

                            //Obtener las piezas del juego
                            $pFemale = Pieza::where("n_pieza", $noSet . "H")->where('id_clase', $class->id)->where('proceso', $processName)->first();
                            $pMale = Pieza::where("n_pieza", $noSet . "M")->where('id_clase', $class->id)->where('proceso', $processName)->first();

                            //Verificar si ambas piezas existen
                            if ($pFemale && $pMale) {
                                //Verificar si el juego esta rechazado o liberado
                                if ($pFemale->liberacion == 0) {
                                    //Verificar si las pieza son correctas o no
                                    if ($pFemale->error == "Ninguno" && $pMale->error == "Ninguno") {
                                        array_push($piecesArray["good"], $pFemale, $pMale);
                                    } else {
                                        //Guardar el juego completo como malo
                                        array_push($piecesArray["bad"], $pFemale, $pMale);

                                        if ($pFemale->error != "Ninguno") {
                                            array_push($piecesBadData, $this->getBadPiecesData($pFemale));
                                        }
                                        if ($pMale->error != "Ninguno") {
                                            array_push($piecesBadData, $this->getBadPiecesData($pMale));
                                        }
                                    }
                                } else if ($pFemale->liberacion == 1) {
                                    array_push($piecesArray["good"], $pFemale, $pMale);
                                } else {
                                    array_push($piecesArray["bad"], $pFemale, $pMale);

                                    if ($pFemale->error != "Ninguno") {
                                        array_push($piecesBadData, $this->getBadPiecesData($pFemale));
                                    } else {
                                        array_push($piecesBadData, $this->getBadPiecesData($pFemale, "Rechazada"));
                                    }
                                    if ($pMale->error != "Ninguno") {
                                        array_push($piecesBadData, $this->getBadPiecesData($pMale));
                                    } else {
                                        array_push($piecesBadData, $this->getBadPiecesData($pMale, "Rechazada"));
                                    }
                                }
                            } else {
                                //Si no existe una de las piezas, se guarda la pieza incompleta como mala
                                $imcompletePiece = $pFemale ? $pFemale : $pMale;

                                if ($imcompletePiece->liberacion == 2) {
                                    array_push($piecesArray["bad"], $imcompletePiece, $imcompletePiece);
                                    array_push($piecesBadData, $this->getBadPiecesData($imcompletePiece, "Rechazada"));
                                }
                            }
                        }
                    } else {
                        $pares = false;
                        $piecesArray["total"] = count($pieces);
                        //Verificar si el juego esta rechazado o liberado
                        if ($piece->liberacion == 0) {
                            //Verificar si las pieza son correctas o no
                            if ($piece->error == "Ninguno") {
                                array_push($piecesArray["good"], $piece);
                            } else {
                                //Guardar el juego completo como malo
                                array_push($piecesArray["bad"], $piece);
                                array_push($piecesBadData, $this->getBadPiecesData($piece));
                            }
                        } else if ($piece->liberacion == 1) {
                            array_push($piecesArray["good"], $piece);
                        } else {
                            array_push($piecesArray["bad"], $piece);
                            if ($piece->error != "Ninguno") {
                                array_push($piecesBadData, $this->getBadPiecesData($piece));
                            } else {
                                array_push($piecesBadData, $this->getBadPiecesData($piece, "Rechazada"));
                            }
                        }
                    }
                }
                if (isset($pares)) {
                    if ($pares) {
                        $piecesArray["total"] = count($pieces) / 2;
                        $piecesArray["good"] = count($piecesArray["good"]) / 2;
                        $piecesArray["bad"] = count($piecesArray["bad"]) / 2;
                    } else {
                        $piecesArray["total"] = count($pieces);
                        $piecesArray["good"] = count($piecesArray["good"]);
                        $piecesArray["bad"] = count($piecesArray["bad"]);
                    }
                }
            } else {
                $piecesArray = [
                    "total" => 0,
                    "good" => 0,
                    "bad" => 0
                ];
            }
        }
        return $piecesArray;
    }
    function getBadPiecesData($piece, $rechazada = null, $operation = "- - - ")
    {
        $array = array();
        $operador = User::where('matricula', $piece->id_operador)->first();
        $array["piece"] = $piece->n_pieza;
        //Obtener el numero de juego
        preg_match('/^\d+/', $piece->n_pieza, $n_juego);
        $array["setNumber"] = $n_juego[0] . "J";
        $array["operator"] = $operador->nombre . " " . $operador->a_paterno . " "  . $operador->a_materno;
        $array["process"] = $piece->proceso;
        $array["operation"] = $operation;
        $array["error"] = $rechazada ? $rechazada : $piece->error; //Si la pieza no tiene ningun error pero esta rechazada
        return $array;
    }





















































































    public function saveHeader(Request $request)
    {
        //Si se quiere editar la meta.
        if (isset($request->band)) {
            $metaExistente = Metas::find($request->meta);
        } else {
            //Se verifica si la meta existe.
            $metaExistente = Metas::where('id_usuario', $request->id_usuario)->where('id_ot', $request->ot)->where('h_inicio', $request->h_inicio)->where('h_termino', $request->h_termino)->where('fecha', $request->fecha)->where('maquina', $request->maquina)->first();
        }
        $ot = Orden_trabajo::find($request->ot); //Busco la OT ingresada.
        $moldura = Moldura::find($ot->id_moldura);

        if (isset($metaExistente)) { //Si la meta existe.
            $moldura = Moldura::find($ot->id_moldura);
            if (isset($metaExistente->id_clase) && !isset($request->clases)) { //Si la meta existe pero aun no se selecciona la clase
                $clase = Clase::find($metaExistente->id_clase);
            } else { //Si se ingresa una meta ya existente
                $clase = Clase::where('id_ot', $ot->id)->where('nombre', $request->clases)->first(); //Busco la clase.
            }
            //Actualizar la maquina
            $maquina = Maquinas::where('id_meta', $metaExistente->id)->first();
            if (!$maquina) {
                $maquina = new Maquinas();
                $maquina->maquina = $request->maquina;
                $maquina->id_meta = $metaExistente->id;
                $maquina->proceso = $request->proceso;
                $maquina->save();
            }

            //Calculo de las horas trabajadas.
            $hrsTrabajadas = $this->calcularHrs($request->h_inicio, $request->h_termino);
            //Si se solicita editar la meta existente y se ingreso una contraseña.
            if ($metaExistente && isset($request->password)) {
                $usersPasswords = User::all(); //Se obtienen todas los usuarios.
                foreach ($usersPasswords as $userPassword) {
                    //Se verifica si la contraseña ingresada es correcta y es de un administrador.
                    if (Hash::check($request->password, $userPassword->contrasena) && $userPassword->perfil == 1) {
                        //Se retornan a las vistas correspondientes con los campos habilitados para editar.
                        switch ($request->proceso) {
                            case "cepillado":
                                return view('processes.cepillado', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "desbaste":
                                return view('processes.desbaste', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "revLaterales":
                                return view('processes.rev-laterales', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "primeraOpeSoldadura":
                                return view('processes.primeraOpeSoldadura', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "barrenoManiobra":
                                return view('processes.barrenoManiobra', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "segundaOpeSoldadura":
                                return view('processes.segundaOpeSoldadura', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "soldadura":
                                return view('processes.soldadura', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "soldaduraPTA":
                                return view('processes.soldaduraPTA', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "rectificado":
                                return view('processes.rectificado', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "asentado":
                                return view('processes.asentado', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case 'revCalificado':
                                return view('processes.revCalificado', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "acabadoBombillo":
                                return view('processes.revAcabadosBombillo', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "acabadoMolde":
                                return view('processes.revAcabadosMolde', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case 'barrenoProfundidad':
                                return view('processes.barrenoProfundidad', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "cavidades":
                                return view('processes.cavidades', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "copiado":
                                return view('processes.copiado', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "offSet":
                                return view('processes.offSet', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "palomas":
                                return view('processes.palomas', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "rebajes":
                                return view('processes.rebajes', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "pysOpeSoldadura":
                                return view('processes.pysOpeSoldadura', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "embudoCM":
                                return view('processes.embudoCM', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                        }
                    }
                }
                //Si se ingreso una contraseña incorrecta se retornan a las vistas correspondientes con los campos deshabilitados.
                switch ($request->proceso) {
                    case "cepillado":
                        return redirect()->route('cepilladoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "desbaste":
                        return redirect()->route('desbasteHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "revLaterales":
                        return redirect()->route('revLateralesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "primeraOpeSoldadura":
                        return redirect()->route('primeraOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "barrenoManiobra":
                        return redirect()->route('barrenoManiobraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "segundaOpeSoldadura":
                        return redirect()->route('segundaOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "soldadura":
                        return redirect()->route('soldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "soldaduraPTA":
                        return redirect()->route('soldaduraPTAHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "rectificado":
                        return redirect()->route('rectificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "asentado":
                        return redirect()->route('asentadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "acabadoBombillo":
                        return redirect()->route('acabadoBombilloHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "acabadoMolde":
                        return redirect()->route('acabadoMoldeHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case 'barrenoProfundidad':
                        return redirect()->route('barrenoProfundidadHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "cavidades":
                        return redirect()->route('cavidadesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "copiado":
                        return redirect()->route('copiadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "offSet":
                        return redirect()->route('offSetHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "palomas":
                        return redirect()->route('palomasHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "rebajes":
                        return redirect()->route('rebajesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "pysOpeSoldadura":
                        return redirect()->route('1y2OpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase, 'operacion' => $request->operacion]);
                    case "embudoCM":
                        return redirect()->route('embudoCMHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                }
                //Si aun no se ha calculado la meta o se ingresan los datos editados o se ingresa la clase elegida
            } elseif ($metaExistente->meta === null || isset($request->band) || isset($request->clases)) {
                //Si se ingresan los datos de la primera parte de la meta editados y no se ha ingresado la clase.
                if (isset($request->band) || !isset($request->clases)) {
                    $metaExistente->fecha = $request->fecha;
                    $metaExistente->h_inicio = $request->h_inicio;
                    $metaExistente->h_termino = $request->h_termino;
                    $metaExistente->maquina = $request->maquina;
                    $metaExistente->save();

                    $metaMaquina = Maquinas::where('id_meta', $metaExistente->id)->first();
                    $metaMaquina->maquina = $request->maquina;
                    $metaMaquina->save();

                    //Se retornan a sus correspondientes vistas con los campos habilitados para editar la segunda parte de la meta.
                    switch ($request->proceso) {
                        case "cepillado":
                            $clases = $this->ClaseEncontradas($ot->id, "cepillado"); //Se obtienen las clases disponibles en cepillado
                            return view('processes.cepillado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]);
                        case "desbaste":
                            $clases = $this->ClaseEncontradas($ot->id, "desbaste_exterior"); //Se obtienen las clases disponibles en desbaste exterior
                            return view('processes.desbaste', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]);
                        case "revLaterales":
                            $clases = $this->ClaseEncontradas($ot->id, "revision_laterales"); //Se obtienen las clases disponibles en revision laterales
                            return view('processes.rev-laterales', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]);
                        case "primeraOpeSoldadura":
                            $clases = $this->ClaseEncontradas($ot->id, "pOperacion"); //Se obtienen las clases disponibles en primera operacion
                            return view('processes.primeraOpeSoldadura', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]);
                        case "barrenoManiobra":
                            $clases = $this->ClaseEncontradas($ot->id, "barreno_maniobra"); //Se obtienen las clases disponibles en barreno maniobra
                            return view('processes.barrenoManiobra', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]);
                        case "segundaOpeSoldadura":
                            $clases = $this->ClaseEncontradas($ot->id, "sOperacion"); //Se obtienen las clases disponibles en segunda operacion
                            return view('processes.segundaOpeSoldadura', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]);
                        case "soldadura":
                            $clases = $this->ClaseEncontradas($ot->id, "soldadura"); //Se obtienen las clases disponibles en soldadura
                            return view('processes.soldadura', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]);
                        case "soldaduraPTA":
                            $clases = $this->ClaseEncontradas($ot->id, "soldaduraPTA"); //Se obtienen las clases disponibles en soldadura PTA
                            return view('processes.soldaduraPTA', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]);
                        case "rectificado":
                            $clases = $this->ClaseEncontradas($ot->id, "rectificado"); //Se obtienen las clases disponibles en rectificado
                            return view('processes.rectificado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]);
                        case "asentado":
                            $clases = $this->ClaseEncontradas($ot->id, "asentado"); //Se obtienen las clases disponibles en asentado
                            return view('processes.asentado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]);
                        case 'revCalificado':
                            $clases = $this->ClaseEncontradas($ot->id, "calificado"); //Se obtienen las clases disponibles en calificado
                            return view('processes.revCalificado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]);
                        case 'acabadoBombillo':
                            $clases = $this->ClaseEncontradas($ot->id, "acabadoBombillo"); //Se obtienen las clases disponibles en acabado bombillo
                            return view('processes.revAcabadosBombillo', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]); //Retorno la vista de acabado bombillo
                        case 'acabadoMolde':
                            $clases = $this->ClaseEncontradas($ot->id, "acabadoMolde"); //Se obtienen las clases disponibles en acabado molde
                            return view('processes.revAcabadosMolde', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]); //Retorno la vista de acabado molde
                        case 'barrenoProfundidad':
                            $clases = $this->ClaseEncontradas($ot->id, "barreno_profundidad"); //Se obtienen las clases disponibles en acabado molde
                            return view('processes.barrenoProfundidad', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]); //Retorno la vista de acabado molde
                        case 'cavidades':
                            $clases = $this->ClaseEncontradas($ot->id, "cavidades"); //Se obtienen las clases disponibles en cavidades
                            return view('processes.cavidades', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]); //Retorno la vista de cavidades
                        case 'copiado':
                            $clases = $this->ClaseEncontradas($ot->id, "copiado"); //Se obtienen las clases disponibles en copiado
                            return view('processes.copiado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]); //Retorno la vista de copiado
                        case 'offSet':
                            $clases = $this->ClaseEncontradas($ot->id, "offSet"); //Se obtienen las clases disponibles en OffSet
                            return view('processes.offSet', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]); //Retorno la vista deOffSet
                        case 'palomas':
                            $clases = $this->ClaseEncontradas($ot->id, "palomas"); //Se obtienen las clases disponibles en Palomas
                            return view('processes.palomas', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]); //Retorno la vista Palomas
                        case 'rebajes':
                            $clases = $this->ClaseEncontradas($ot->id, "rebajes"); //Se obtienen las clases disponibles en Rebajes
                            return view('processes.rebajes', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]); //Retorno la vista Rebajes
                        case "pysOpeSoldadura": //Se obtienen las clases disponibles en 1ra y 2da operación de soldadura.
                            $clases = $this->ClaseEncontradas($ot->id, "operacionEquipo"); //Se obtienen las clases disponibles en 1 y 2 operacion equipo
                            return view('processes.pysOpeSoldadura', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]);
                        case 'embudoCM':
                            $clases = $this->ClaseEncontradas($ot->id, "embudoCM"); //Se obtienen las clases disponibles en Embudo CM
                            return view('processes.embudoCM', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]); //Retorno la vista Embudo CM
                    }
                }
                if (isset($request->clases)) { //Si existe una clase ingresada.
                    if ($request->proceso == "pysOpeSoldadura") { //Si el proceso es pysOpeSoldadura.
                        if (isset($request->operacion)) { //Si la operación existe
                            $clase = $this->AsignarDatos_Meta($metaExistente, $hrsTrabajadas, $ot, $request->clases, $request->proceso); //Asigno los datos de la meta.
                        } else {
                            $clases = $this->ClaseEncontradas($ot->id, "operacionEquipo"); //Obtengo las clases que no son nulas.
                            return view('processes.pysOpeSoldadura', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]); //Retorno la vista de primera y segunda operación de soldadura
                        }
                    } else {
                        $clase = $this->AsignarDatos_Meta($metaExistente, $hrsTrabajadas, $ot, $request->clases, $request->proceso); //Asigno los datos de la meta.
                    }
                }
                //Se retorna a sus correspondientes vistas para el registro de las piezas
                switch ($request->proceso) {
                    case "cepillado":
                        $id = "cepillado_" . $request->clases . "_" . $ot->id;
                        $cepillado = Cepillado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        //Si existe el proceso
                        if (isset($cepillado)) {
                            return redirect()->route('cepilladoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de cepillado.
                        return redirect()->route('cepilladoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "desbaste":
                        $id = "desbaste_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Desbaste.
                        $desbaste = DesbasteExterior::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($desbaste)) {
                            return redirect()->route('desbasteHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('desbasteHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "revLaterales": //Creación de id para la tabla de Revision Laterales.
                        $id = "revLaterales_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Revision Laterales.
                        $revLaterales = RevLaterales::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($revLaterales)) { //Si existe la OT.
                            return redirect()->route('revLateralesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de Revision Laterales.
                        return redirect()->route('revLateralesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "primeraOpeSoldadura":
                        $id = "1opeSoldadura_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Primera Operación de Soldadura.
                        $primeraOpeSoldadura = PrimeraOpeSoldadura::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($primeraOpeSoldadura)) {
                            return redirect()->route('primeraOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('primeraOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);

                    case "barrenoManiobra":
                        $id = "barrenoManiobra_" . $request->clases . "_" . $ot->id;
                        $barrenoManiobra = BarrenoManiobra::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($barrenoManiobra)) {
                            return redirect()->route('barrenoManiobraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de Barreno Maniobra.
                        return redirect()->route('barrenoManiobraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "segundaOpeSoldadura":
                        $id = "2opeSoldadura_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Segunda Operación de Soldadura.
                        $segundaOpeSoldadura = SegundaOpeSoldadura::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($segundaOpeSoldadura)) {
                            return redirect()->route('segundaOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de Segunda Operación de Soldadura.
                        return redirect()->route('segundaOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);

                    case "soldadura":
                        $id = "soldadura_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Soldadura
                        $soldadura = Soldadura::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($soldadura)) {
                            return redirect()->route('soldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de Soldadura.
                        return redirect()->route('soldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "soldaduraPTA":
                        $id = "soldaduraPTA_" . $request->clases . "_" . $ot->id; //Creación de id para tabla SoldaduraPTA
                        $soldaduraPTA = SoldaduraPTA::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($soldaduraPTA)) {
                            return redirect()->route('soldaduraPTAHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de SoldaduraPTA.
                        return redirect()->route('soldaduraPTAHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "rectificado":
                        $id = "rectificado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $rectificado = Rectificado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($rectificado)) {
                            return redirect()->route('rectificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de Rectificado.
                        return redirect()->route('rectificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "asentado":
                        $id = "asentado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Asentado
                        $rectificado = Asentado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($rectificado)) {
                            return redirect()->route('asentadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de Asentado.
                        return redirect()->route('asentadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "revCalificado":
                        $id = "revCalificado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Revisión Calificado
                        $calificado = revCalificado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($calificado)) {
                            return redirect()->route('calificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Revisión Calificado.
                        }
                        //Retorno la vista de Revisión Calificado.
                        return redirect()->route('calificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "acabadoBombillo":
                        $id = "acabadoBombillo_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Acabado Bombillo
                        $acabadoBombillo = AcabadoBombilo::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($acabadoBombillo)) {
                            return redirect()->route('acabadoBombilloHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Acabado Bombillo.
                        }
                        //Retorno la vista de Acabado Bombillo.
                        return redirect()->route('acabadoBombilloHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "acabadoMolde":
                        $id = "acabadoMolde_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Acabado Molde
                        $acabadoMolde = AcabadoMolde::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($acabadoMolde)) {
                            return redirect()->route('acabadoMoldeHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Acabado Molde.
                        }
                        //Retorno la vista de Acabado Molde.
                        return redirect()->route('acabadoMoldeHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "barrenoProfundidad":
                        $id = "barrenoProfundidad_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Acabado Molde
                        $barrenoProfundidad = BarrenoProfundidad::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($barrenoProfundidad)) {
                            return redirect()->route('barrenoProfundidadHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Acabado Molde.
                        }
                        //Retorno la vista de Acabado Molde.
                        return redirect()->route('barrenoProfundidadHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "cavidades":
                        $id = "cavidades_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Cavidades
                        $cavidades = Cavidades::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($cavidades)) {
                            return redirect()->route('cavidadesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Cavidades.
                        }
                        //Retorno la vista de Cavidades.
                        return redirect()->route('cavidadesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "copiado":
                        $id = "copiado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Copiado
                        $copiado = Copiado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($copiado)) {
                            return redirect()->route('copiadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Copiado.
                        }
                        //Retorno la vista de Copiado.
                        return redirect()->route('copiadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "offSet":
                        $id = "offSet_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Offset
                        $offSet = OffSet::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($offSet)) {
                            return redirect()->route('offSetHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de OffSet
                        }
                        //Retorno la vista de OffSet.
                        return redirect()->route('offSetHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "palomas":
                        $id = "palomas_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Palomas
                        $palomas = Palomas::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($palomas)) {
                            return redirect()->route('palomasHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Palomas
                        }
                        //Retorno la vista de Palomas
                        return redirect()->route('palomasHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "rebajes":
                        $id = "rebajes_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rebajes
                        $rebajes = Rebajes::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($rebajes)) {
                            return redirect()->route('rebajesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Rebajes
                        }
                        //Retorno la vista de Rebajes
                        return redirect()->route('rebajesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "pysOpeSoldadura":
                        $pysOpeSoldadura = PySOpeSoldadura::where('id_clase', $request->clases)->where('id_ot', $ot->id)->where('operacion', $request->operacion)->first(); //Busco la OT que se quiere editar.
                        if (isset($pysOpeSoldadura)) {
                            return redirect()->route('1y2OpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase, 'operacion' => $request->operacion]);
                        }
                        // //Retorno la vista de 1ra y 2da operación de soldadura
                        return redirect()->route('1y2OpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'operacion' => $request->operacion]);
                    case "embudoCM":
                        $id = "embudoCM_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Embudo CM
                        $embudoCM = EmbudoCM::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($embudoCM)) {
                            return redirect()->route('embudoCMHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Embudo CM
                        }
                        //Retorno la vista de Embudo CM
                        return redirect()->route('embudoCMHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                }
            } else {
                //Cuando ya se han registrado todos los datos de la meta.
                switch ($request->proceso) {
                    case "cepillado":
                        $id = "cepillado_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Cepillado.
                        $cepillado = Cepillado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($cepillado)) {
                            return redirect()->route('cepilladoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de cepillado.
                        }
                        //Retorno la vista de cepillado.
                        return redirect()->route('cepilladoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]); //Retorno la vista de cepillado.
                    case "desbaste":
                        $id = "desbaste_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Desbaste.
                        $desbaste = DesbasteExterior::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($desbaste)) {
                            return redirect()->route('desbasteHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('desbasteHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "revLaterales":
                        $id = "revLaterales_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Revision Laterales.
                        $revLaterales = RevLaterales::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($revLaterales)) {
                            return redirect()->route('revLateralesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Revision Laterales.
                        }
                        //Retorno la vista de Revision Laterales.
                        return redirect()->route('revLateralesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "primeraOpeSoldadura": //Creación de id para la tabla de primera operación de Primera Operación de Soldadura.
                        $id = "1opeSoldadura_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Primera Operación de Soldadura.
                        $primeraOpeSoldadura = PrimeraOpeSoldadura::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($primeraOpeSoldadura)) { //Si existe la OT.
                            return redirect()->route('primeraOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); ////Retorno la vista de Primera Operación de Soldadura.
                        }
                        //Retorno la vista de Primera Operación de Soldadura.
                        return redirect()->route('primeraOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "barrenoManiobra":
                        $id = "barrenoManiobra_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Barreno Maniobra.
                        $barrenoManiobra = BarrenoManiobra::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($barrenoManiobra)) { //Si existe la OT.
                            return redirect()->route('barrenoManiobraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Barreno Maniobra.
                        }
                        //Retorno la vista de Barreno Maniobra.
                        return redirect()->route('barrenoManiobraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "segundaOpeSoldadura":
                        $id = "2opeSoldadura_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Segunda Operación de Soldadura
                        $segundaOpeSoldadura = SegundaOpeSoldadura::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($segundaOpeSoldadura)) { //Si existe la OT.
                            return redirect()->route('segundaOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Segunda Operación de Soldadura
                        }
                        //Retorno la vista de Segunda Operación de Soldadura
                        return redirect()->route('segundaOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "soldadura":
                        $id = "soldadura_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Soldadura
                        $soldadura = Soldadura::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($soldadura)) { //Si existe la OT.
                            return redirect()->route('soldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Soldadura
                        }
                        //Retorno la vista de Soldadura
                        return redirect()->route('soldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "soldaduraPTA":
                        $id = "soldaduraPTA_" . $request->clases . "_" . $ot->id; //Creación de id para tabla SoldaduraPTA
                        $soldaduraPTA = SoldaduraPTA::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($soldaduraPTA)) { //Si existe la OT.
                            return redirect()->route('soldaduraPTAHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de SoldaduraPTA
                        }
                        //Retorno la vista de SoldaduraPTA
                        return redirect()->route('soldaduraPTAHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "rectificado":
                        $id = "rectificado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $rectificado = Rectificado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($rectificado)) { //Si existe la OT.
                            return redirect()->route('rectificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Rectificado
                        }
                        //Retorno la vista de Rectificado
                        return redirect()->route('rectificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "asentado":
                        $id = "asentado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Asentado
                        $asentado = Asentado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($asentado)) { //Si existe la OT.
                            return redirect()->route('asentadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Asentado
                        }
                        //Retorno la vista de Asentado
                        return redirect()->route('asentadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'revCalificado':
                        $id = "revCalificado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Revisión Calificado
                        $calificado = revCalificado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($calificado)) { //Si existe la OT.
                            return redirect()->route('calificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Revisión Calificado
                        }
                        //Retorno la vista de Revisión Calificado
                        return redirect()->route('calificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'acabadoBombillo':
                        $id = "acabadoBombillo_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Acabado Bombillo
                        $acabadoBombillo = AcabadoBombilo::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($acabadoBombillo)) { //Si existe la OT.
                            return redirect()->route('acabadoBombilloHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Acabado Bombillo
                        }
                        //Retorno la vista de Acabado Bombillo
                        return redirect()->route('acabadoBombilloHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'acabadoMolde':
                        $id = "acabadoMolde_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Acabado Molde
                        $acabadoMolde = AcabadoMolde::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($acabadoMolde)) { //Si existe la OT.
                            return redirect()->route('acabadoMoldeHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Acabado Molde
                        }
                        //Retorno la vista de Acabado Molde
                        return redirect()->route('acabadoMoldeHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "barrenoProfundidad":
                        $id = "barrenoProfundidad_" . $request->clases . "_" . $ot->id; //Creación de id para tabla barrenoProfundidad
                        $barrenoProfundidad = BarrenoProfundidad::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($barrenoProfundidad)) { //Si existe la OT.
                            return redirect()->route('barrenoProfundidadHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Acabado Molde
                        }
                        //Retorno la vista de Barrero Profundidad
                        return redirect()->route('barrenoProfundidadHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'cavidades':
                        $id = "cavidades_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Cavidades
                        $cavidades = Cavidades::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($cavidades)) { //Si existe la OT.
                            return redirect()->route('cavidadesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Cavidades
                        }
                        //Retorno la vista de Cavidades
                        return redirect()->route('cavidadesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'copiado':
                        $id = "copiado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Copiado
                        $copiado = Copiado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($copiado)) { //Si existe la OT.
                            return redirect()->route('copiadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Copiado
                        }
                        //Retorno la vista de Copiado
                        return redirect()->route('copiadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'offSet':
                        $id = "offSet_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Offset
                        $offSet = OffSet::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($offSet)) { //Si existe la OT.
                            return redirect()->route('offSetHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de OffSet
                        }
                        //Retorno la vista de OffSet
                        return redirect()->route('offSetHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'palomas':
                        $id = "palomas_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Palomas
                        $palomas = Palomas::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($palomas)) { //Si existe la OT.
                            return redirect()->route('palomasHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Palomas
                        }
                        //Retorno la vista de Palomas
                        return redirect()->route('palomasHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'rebajes':
                        $id = "rebajes_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rebajes
                        $rebajes = Rebajes::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($rebajes)) { //Si existe la OT.
                            return redirect()->route('rebajesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Rebajes
                        }
                        //Retorno la vista de Rebajes
                        return redirect()->route('rebajesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "pysOpeSoldadura": //Creación de id para la tabla de Primera Operación y Segunda Operación de Soldadura.
                        echo $proceso = PySOpeSoldadura::find($metaExistente->id_proceso); //Busco la OT que se requiere editar
                        if (isset($proceso)) {
                            return redirect()->route('1y2OpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase, 'operacion' => $proceso->operacion]);
                        }
                        return redirect()->route('1y2OpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'embudoCM':
                        $id = "embudoCM_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Embudo CM
                        $embudoCM = EmbudoCM::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($embudoCM)) { //Si existe la OT.
                            return redirect()->route('embudoCMHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de Rebajes
                        }
                        //Retorno la vista de Embudo CM
                        return redirect()->route('embudoCMHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                }
            }
        } else {
            //Si no existe la meta ingresada se crea una nueva.
            //Se verifica que la maquina no este ocupada
            $maquinaOcupada = Maquinas::where('maquina', $request->maquina)->where('proceso', $request->proceso)->get();
            $var = 0;
            foreach ($maquinaOcupada as $maquina) {
                $metaMaquina = Metas::find($maquina->id_meta);
                if ($metaMaquina->id_ot == $request->ot && $maquina->proceso == $request->proceso) {
                    $var = 1;
                    break;
                }
            }
            if ($var == 0) {
                $meta = new Metas();
                $meta->id_ot = $request->ot;
                $meta->id_usuario = $request->id_usuario;
                $meta->fecha = $request->fecha;
                $meta->h_inicio = $request->h_inicio;
                $meta->h_termino = $request->h_termino;
                $meta->maquina = $request->maquina;
                $meta->proceso = $request->proceso;
                $meta->save();

                $maquina = new Maquinas();
                $maquina->id_meta = $meta->id;
                $maquina->maquina = $request->maquina;
                $maquina->proceso = $request->proceso;
                $maquina->save();

                $moldura = Moldura::find($ot->id_moldura);
                switch ($request->proceso) {
                    case "cepillado":
                        $clases = $this->ClaseEncontradas($meta->id_ot, "cepillado"); //Obtengo las clases que no son nulas.
                        return view('processes.cepillado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de cepillado.
                    case "desbaste":
                        $clases = $this->ClaseEncontradas($meta->id_ot, "desbaste_exterior"); //Obtengo las clases que no son nulas.
                        return view('processes.desbaste', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de desbaste.
                    case "revLaterales":
                        $clases = $this->ClaseEncontradas($meta->id_ot, "revision_laterales"); //Obtengo las clases que no son nulas.
                        return view('processes.rev-laterales', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de revLaterales.
                    case "primeraOpeSoldadura":
                        $clases = $this->ClaseEncontradas($meta->id_ot, "pOperacion"); //Obtengo las clases que no son nulas.
                        return view('processes.primeraOpeSoldadura', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de primeraOpeSoldadura.
                    case "barrenoManiobra":
                        $clases = $this->ClaseEncontradas($meta->id_ot, "barreno_maniobra"); //Obtengo las clases que no son nulas.
                        return view('processes.barrenoManiobra', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de barrenoManiobra.
                    case "segundaOpeSoldadura":
                        $clases = $this->ClaseEncontradas($meta->id_ot, "sOperacion"); //Obtengo las clases que no son nulas.
                        return view('processes.segundaOpeSoldadura', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de segundaOpeSoldadura.
                    case "soldadura":
                        $clases = $this->ClaseEncontradas($meta->id_ot, "soldadura"); //Obtengo las clases que no son nulas.
                        return view('processes.soldadura', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de soldadura.
                    case "soldaduraPTA":
                        $clases = $this->ClaseEncontradas($meta->id_ot, "soldaduraPTA"); //Obtengo las clases que no son nulas.
                        return view('processes.soldaduraPTA', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de soldaduraPTA.
                    case "rectificado":
                        $clases = $this->ClaseEncontradas($meta->id_ot, "rectificado"); //Obtengo las clases que no son nulas.
                        return view('processes.rectificado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de rectificado.
                    case "asentado":
                        $clases = $this->ClaseEncontradas($meta->id_ot, "rectificado"); //Obtengo las clases que no son nulas.
                        return view('processes.asentado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de asentado
                    case 'revCalificado':
                        $clases = $this->ClaseEncontradas($meta->id_ot, "calificado"); //Obtengo las clases que no son nulas.
                        return view('processes.revCalificado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de revCalificado.
                    case 'acabadoBombillo':
                        $clases = $this->ClaseEncontradas($meta->id_ot, "acabadoBombillo"); //Obtengo las clases que no son nulas.
                        return view('processes.revAcabadosBombillo', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de acabadoBombillo.
                    case 'acabadoMolde':
                        $clases = $this->ClaseEncontradas($meta->id_ot, "acabadoMolde"); //Obtengo las clases que no son nulas.
                        return view('processes.revAcabadosMolde', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista acabadoMolde.
                    case 'barrenoProfundidad':
                        $clases = $this->ClaseEncontradas($meta->id_ot, "barreno_profundidad"); //Obtengo las clases que no son nulas.
                        return view('processes.barrenoProfundidad', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista acabadoMolde.
                    case 'cavidades':
                        $clases = $this->ClaseEncontradas($meta->id_ot, "cavidades"); //Obtengo las clases que no son nulas.
                        return view('processes.cavidades', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de cavidades.
                    case 'copiado':
                        $clases = $this->ClaseEncontradas($meta->id_ot, "copiado"); //Obtengo las clases que no son nulas.
                        return view('processes.copiado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de copiado.
                    case 'offSet':
                        $clases = $this->ClaseEncontradas($meta->id_ot, "offSet"); //Obtengo las clases que no son nulas.
                        return view('processes.offSet', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de OffSet.
                    case 'palomas':
                        $clases = $this->ClaseEncontradas($meta->id_ot, "palomas"); //Obtengo las clases que no son nulas.
                        return view('processes.palomas', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorn la vista de Palomas.
                    case 'rebajes':
                        $clases = $this->ClaseEncontradas($meta->id_ot, "rebajes"); //Obtengo las clases que no son nulas.
                        return view('processes.rebajes', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de Rebajes
                    case "pysOpeSoldadura":
                        $clases = $this->ClaseEncontradas($meta->id_ot, "operacionEquipo"); //Obtengo las clases que no son nulas.
                        return view('processes.pysOpeSoldadura', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de pysOpeSoldadura.
                    case 'embudoCM':
                        $clases = $this->ClaseEncontradas($meta->id_ot, "embudoCM"); //Obtengo las clases que no son nulas.
                        return view('processes.embudoCM', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de Rebajes
                }
            } else {
                switch ($request->proceso) {
                    case "cepillado":
                        return redirect()->route('cepillado', ['error' => 1]);
                    case 'desbaste':
                        return redirect()->route('desbasteExterior', ['error' => 1]);
                    case 'revLaterales':
                        return redirect()->route('revisionLaterales', ['error' => 1]);
                    case 'primeraOpeSoldadura':
                        return redirect()->route('primeraOpeSoldadura', ['error' => 1]);
                    case 'barrenoManiobra':
                        return redirect()->route('barrenoManiobra', ['error' => 1]);
                    case 'segundaOpeSoldadura':
                        return redirect()->route('segundaOpeSoldadura', ['error' => 1]);
                    case 'soldadura':
                        return redirect()->route('soldadura', ['error' => 1]);
                    case 'soldaduraPTA':
                        return redirect()->route('soldaduraPTA', ['error' => 1]);
                    case 'rectificado':
                        return redirect()->route('rectificado', ['error' => 1]);
                    case 'asentado':
                        return redirect()->route('asentado', ['error' => 1]);
                    case 'revCalificado':
                        return redirect()->route('calificado', ['error' => 1]);
                    case 'acabadoBombillo':
                        return redirect()->route('acabadoBombillo', ['error' => 1]);
                    case 'acabadoMolde':
                        return redirect()->route('acabadoMolde', ['error' => 1]);
                    case 'barrenoProfundidad':
                        return redirect()->route('barrenoProfundidad', ['error' => 1]);
                    case 'cavidades':
                        return redirect()->route('cavidades', ['error' => 1]);
                    case 'copiado':
                        return redirect()->route('copiado', ['error' => 1]);
                    case 'offSet':
                        return redirect()->route('offSet', ['error' => 1]);
                    case 'palomas':
                        return redirect()->route('palomas', ['error' => 1]);
                    case 'rebajes':
                        return redirect()->route('rebajes', ['error' => 1]);
                    case 'pysOpeSoldadura':
                        return redirect()->route('1y2OpeSoldadura', ['error' => 1]);
                    case 'embudoCM':
                        return redirect()->route('1y2OpeSoldadura', ['error' => 1]);
                }
            }
        }
    }

    public function ClaseEncontradas($ot, $proceso)
    {
        $string = $proceso; //Asigno el nombre del proceso
        $clases = Clase::where('id_ot', $ot)->get(); //Obtengo las clases de la OT.
        $clasesEncontradas = array(); //Creo una matriz para guardar las clases y sus respectivas maquinas que se mostraran en cepillado.
        $contador = 0;
        foreach ($clases as $clase) { //Recorro las clases.
            $proceso = Procesos::where('id_clase', $clase->id)->first(); //Se obtienen los procesos de la clase.
            //Si existe el proceso
            if ($proceso && $proceso->$string != 0) { //Si el proceso es diferente de 0
                $clasesEncontradas[$contador][0] = $clase; //Guardo el nombre de la clase
                $clasesEncontradas[$contador][1] = $proceso->$string; //Guardo el proceso
                $contador++;
            }
        }
        return $clasesEncontradas; //Retorno las clases.
    }
    public function calcularHrs($h_inicio, $h_termino) //Función para calcular las horas trabajadas.
    {
        // $carbon1 = Carbon::createFromFormat('H:i', $h_inicio);
        $carbon1 = Carbon::parse($h_inicio);
        $carbon2 = Carbon::parse($h_termino);
        // $carbon2 = Carbon::createFromFormat('H:i', $h_termino);

        //Calcular la diferencia entre las horas en minutos
        $diferencia = $carbon1->diffInMinutes($carbon2) - 60; //Calculo de las horas trabajadas.
        return $diferencia; //Retorno las horas trabajadas.
    }
        public function calcularMeta($t_estandar, $hrsTrabajadas) //Función para calcular la meta.
    {
        //Calculo de la meta.
        $tiempo = $t_estandar != 0 ? round(($hrsTrabajadas / $t_estandar)) : 0;
        return $tiempo;
    }
    public function AsignarDatos_Meta($meta, $hrsTrabajadas, $ot, $reqClase, $proceso) //Función para asignar los datos de la meta.
    {
        $clase = Clase::where('id_ot', $ot->id)->where('nombre', $reqClase)->first(); //Busco la clase.
        $meta->id_clase = $clase->id;

        $tiempo = tiempoproduccion::where('id_clase', $clase->id)->where('proceso', $proceso)->first();
        $meta->t_estandar = $tiempo->tiempo ?? 0;
        $meta->meta = $this->calcularMeta($meta->t_estandar, $hrsTrabajadas) ?? 0; //Se calcula la meta.

        $meta->save();
        return $clase; //Se retorna la clase.
    }
}
