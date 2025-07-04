<?php

namespace App\Http\Controllers;

use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Procesos;
use Illuminate\Http\Request;

class ProcessProductionController extends Controller
{
    protected $classController;
    protected $processesController;
    public function __construct()
    {
        $this->middleware('auth');
        $this->classController = new ClassController();
        $this->processesController = new ProcessesController();
    }
    public function show()
    {
        $wOrdersFounded = Orden_trabajo::all();
        $workOrders = array();
        if (count($wOrdersFounded) > 0) {
            foreach ($wOrdersFounded as $workOrder) {
                $classes = $this->classController->getClasses($workOrder);
                if (count($classes) > 0) {
                    $workOrders[$workOrder->id] = array();
                    $molding = Moldura::find($workOrder->id_moldura);
                    $workOrders[$workOrder->id]['moldura'] = $molding ? $molding->nombre : 'Moldura no encontrada';
                    foreach ($classes as $class) {
                        $processes = Procesos::where('id_clase', $class->id)->first();
                        if ($processes) {
                            $workOrders[$workOrder->id][$class->nombre] = array();
                            foreach ($processes->getAttributes() as $process => $valor) {
                                if (($process != "id" && $process != "id_clase" && $process != "soldadura" && $process != "soldaduraPTA" && $process != "rectificado" && $process != "asentado") && $valor != 0) {
                                    $process = $this->processesController->convertProcessToString($process);
                                    array_push($workOrders[$workOrder->id][$class->nombre], $process);
                                }
                            }
                        }
                    }
                }
            }
        }
        $workOrders = count($workOrders) > 0 ? $workOrders : null;
        return view('processes_views.processProduction_view', compact('workOrders'));
    }


    public function saveHeader(Request $request)
    {
        echo "Hola";
        die();
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
