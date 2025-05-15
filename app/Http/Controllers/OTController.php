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
use App\Models\Rebajes;
use App\Models\Rectificado;
use App\Models\revCalificado;
use App\Models\RevLaterales;
use App\Models\SegundaOpeSoldadura;
use App\Models\Soldadura;
use App\Models\SoldaduraPTA;
use App\Models\tiempoproduccion;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Mockery\Undefined;
use Symfony\Component\Console\Input\Input;

class OTController extends Controller
{
    protected $userController;
    public function __construct()
    {
        $this->userController = new UserController;
    }
    public function getLayout()
    {
        return $this->userController->getLayout();
    }
    public function show()
    {
        $molduras = Moldura::all();
        $oTrabajoAll = Orden_trabajo::all();
        //Si existen ordenes de trabajo registradas.
        if ($oTrabajoAll != "[]") {
            $ordenesTrabajo = []; //Arreglo para guardar las molduras de cada OT
            $contador = 0; // Contador para las molduras y OT
            foreach ($oTrabajoAll as $ot) {
                if (auth()->user()->perfil == 5) {
                    $clases = Clase::where("id_ot", $ot->id)->get();
                    if ($clases->count() == 0) {
                        continue;
                    }
                }
                $moldura = Moldura::find($ot->id_moldura);
                $ordenesTrabajo[$contador]['ot'] = $ot->id;
                $ordenesTrabajo[$contador]['moldura'] = $moldura->nombre;
                $contador++;
            }
            return view('processesAdmin.RegistrarOT.registrarOT', ['molduras' => $molduras, 'ordenesTrabajo' => $ordenesTrabajo, 'layout' => $this->getLayout()]); //Retorno la vista de registro de OT con las molduras.
        }
        return view('processesAdmin.RegistrarOT.registrarOT', ['molduras' => $molduras, 'layout' => $this->getLayout()]); //Retorno la vista de registro de OT con las molduras.
    }

    public function deleteOT($ot)
    {
        $piezas = Pieza::where('id_ot', $ot)->get(); //Busco las piezas de la OT
        $meta = Metas::where('id_ot', $ot)->get();
        if (count($piezas) == 0 && count($meta) == 0) { //Si la OT no tiene piezas ni metas asociadas entonces
            $clase = Clase::where('id_ot', $ot)->get(); //Busco todas las clases que pertenecen a la OT
            foreach ($clase as $clase ) { //Recorro las clases de la OT
                $this->deleteClass($clase->id); //Elimino las clases
            }
            Orden_trabajo::find($ot)->delete(); //Elimino la OT
            return redirect()->route('registerOT')->with('success', '¡Orden de trabajo eliminada con éxito!'); //Redirecciono a la vista de registro de la OT
        }
        return redirect()->route('registerOT')->withErrors('¡La orden de trabajo no se puede eliminar porque tiene piezas o metas asociadas!');
    }
    public function store(OTRequest $request) //Funcion para registrar una OT.
    {
        echo "Hola";
        //Si el perfil ingresado es Almacen, se redirige a la interfaz de editar la OT
        if (auth()->user()->perfil == 5) {
            return redirect()->route('mostrarClases', ['ot' => $request->otSeleccionada]);
        }
        if (isset($request->ot)) {
            $ot = Orden_trabajo::find($request->ot); //Busco la OT ingresada
        } else {
            $ot = Orden_trabajo::find($request->otSeleccionada); //Busco a la OT seleccionada
        }
        //Si la orden de trabajo existe.
        if ($ot && $request->ot == null || isset($request->clase)) {
            $moldura = Moldura::find($request->id_moldura);
            //Si el usuario ingreso datos de una clase.
            if (isset($request->clase)) {
                $clase = Clase::where('id_ot', $request->ot)->where('nombre', $request->clase)->first(); //Busco la clase.
                if (!$clase) { //Si la clase no existe.
                    //Asigno los datos de la clase.
                    $clase = new Clase();
                    $clase->id_ot = $request->ot;
                    $clase->nombre = $request->clase;
                    $clase->piezas = $request->piezas;
                    $clase->pedido = $request->pedido;
                    $clase->fecha_inicio = $request->fecha_inicio;
                    $clase->hora_inicio = $request->hora_inicio;
                    //Si la clase no es obturador.
                    if ($request->clase != "Obturador") {
                        $clase->tamanio = $request->tamanio;
                    } else { //Si la clase es obturador.
                        $clase->seccion = $request->seccion;
                    }
                    $clase->save();
                }
                //Cuando solamente se ingresa una clase que ya existe.
                $proceso = Procesos::where('id_clase', $clase->id)->first(); //Busco la clase.
                if ($proceso) {
                    // Obtener los campos donde el valor es igual a 1
                    $camposConValor = [];
                    $maquinas = []; //Guardo las máquinas que se utilizaron en el proceso
                    $contador = 0;
                    foreach ($proceso->getAttributes() as $campo => $valor) { //Recorro los campos.
                        if ($campo != "id" && $campo != "id_clase") {
                            if ($valor != 0) { //Si el valor es diferente de 0
                                $camposConValor[$contador] = $campo; //Guardo los campos.
                                $maquinas[$contador] = $valor;
                                $contador++;
                            }
                        }
                    }
                    $clasesEncontradas = Clase::where('id_ot', $request->ot)->get(); //Busco la clase.
                    return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clase' => $clase, 'proceso' => $camposConValor, 'maquinas' => $maquinas, 'clases' => $clasesEncontradas, 'layout' => $this->getLayout()]); //Redirecciono a la vista de editar de OT.
                } else {
                    $clasesEncontradas = Clase::where('id_ot', $request->ot)->get(); //Busco la clase.
                    return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clase' => $clase, 'clases' => $clasesEncontradas, 'layout' => $this->getLayout()]); //Redirecciono a la vista de registro de OT.
                }
            }
            //Cuando solamente se ingresa una OT que ya existe.
            $clasesEncontradas = Clase::where('id_ot', $ot->id)->get(); //Busco la clase.
            if ($clasesEncontradas) {
                return view('processesAdmin.RegistrarOT.registrarOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clasesEncontradas' => $clasesEncontradas, 'layout' => $this->getLayout()]); //Redirecciono a la vista de registro de OT.
            }
        } else {
            $otExistente = Orden_trabajo::find($request->ot); //Busco la OT ingresada.
            if (!$otExistente) {
                $ot = new Orden_trabajo(); //Creo una nueva OT.
                //Asigno los datos de la OT.
                $ot->id = $request->ot; //Actualizo los datos de la OT.
                $ot->id_moldura = $request->id_moldura;
                $ot->save(); //Guardo los cambios.

                $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.
            } else {
                return redirect()->to('/registerOT')->withErrors('La orden de trabajo ingresada ya existe'); //Redirecciono a la vista de registro de OT.
            }
        }
        return view('processesAdmin.RegistrarOT.registrarOT', ['ot' => $request->ot, 'moldura' => $moldura, 'layout' => $this->getLayout()]); //Redirecciono a la vista de registro de OT.
    }
    public function registerClass($ot)
    {
        $otEncontrada = Orden_trabajo::find($ot); //Busco la OT ingresada.
        $moldura = Moldura::find($otEncontrada->id_moldura); //Busco la OT ingresada.
        $clasesEncontradas = Clase::where('id_ot', $ot)->get(); //Busco la clase.
        return view('processesAdmin.RegistrarOT.registrarOT', ['ot' => $ot, 'moldura' => $moldura, 'clasesEncontradas' => $clasesEncontradas, 'layout' => $this->getLayout()]); //Redirecciono a la vista de registro de OT.
    }
    public function deleteClass($clase)
    {
        $clase = Clase::find($clase);
        $ot = $clase->id_ot; //Busco la OT ingresada.
        $ot = Orden_trabajo::find($ot); //Busco la OT ingresada

        //Si existen metas asociadas a la clase no se elimina
        $text = "La clase {$clase->nombre} no se puede eliminar porque ya tiene metas asociadas";
        $param = "error";
        $metas = Metas::where('id_clase', $clase->id)->get();
        if(count($metas) == 0){
            $proceso = Procesos::where('id_clase', $clase->id)->first();
            //Si el proceso existe.
            if ($proceso) {
                $proceso->delete(); //Elimino el proceso de la clase
                $fechasProcesos = Fecha_proceso::where('clase', $clase->id)->get();
                if (count($fechasProcesos) > 0) {
                    foreach ($fechasProcesos as $fecha) {
                        $fecha->delete();
                    }
                }
            }
            Clase::destroy($clase->id); //Elimino la clase
            $text = "La clase {$clase->nombre} se elimino exitosamente";
            $param = "success";
        }
        $ruta = route('mostrarClases', ['ot' => $ot, 'message' => $text]);
        return redirect($ruta)->with($param, $text); //Redirecciono a la vista de registro de la OT
    }
    public function editClass($clase)
    {
        // Se buscan los elementos de la clase que se solicita editar
        $clase = Clase::find($clase);
        $ot = Orden_trabajo::find($clase->id_ot);

        $clases = Clase::where('id', $clase->id)->get(); //Unica clase que aparecera editando en la vista
        $clasesName = ['Bombillo', 'Molde', 'Fondo', 'Obturador', 'Corona', 'Plato', 'Embudo']; //Clases que apareceran para seleccionar
        $clasesRegistradas = Clase::where('id_ot', $ot->id)->get(); //Clases ya registradas
        //Clases que ya estan registradas y no se deben mostrar
        foreach ($clasesRegistradas as $cls) {
            if ($cls->nombre == "Bombillo") {
                unset($clasesName[0]);
            } else if (array_search($cls->nombre, $clasesName)) {
                unset($clasesName[array_search($cls->nombre, $clasesName)]);
            }
        }

        $moldura = Moldura::find($ot->id_moldura);
        $proceso = Procesos::where('id_clase', $clase->id)->first();

        //Obtener el numero de piezas que se han registrado en esa clase
        $piezas = Pieza::where('id_clase', $clase->id)->get();
        $metas = Metas::where('id_clase', $clase->id)->get();

        if ($proceso) {
            // Obtener los campos donde el valor es igual a 1
            $camposConValor = [];
            $maquinas = []; //Guardo las máquinas que se utilizaron en el proceso
            $contador = 0;
            foreach ($proceso->getAttributes() as $campo => $valor) { //Recorro los campos.
                if ($campo != "id" && $campo != "id_clase") {
                    if ($valor != 0) { //Si el valor es diferente de 0
                        $camposConValor[$contador] = $campo; //Guardo los campos.
                        $maquinas[$contador] = $valor;
                        $contador++;
                    }
                }
            }
            return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clase' => $clase, 'proceso' => $camposConValor, 'clases' => $clases, 'maquinas' => $maquinas, 'clasesName' => $clasesName, 'edit' => true, 'piezas' => $piezas, 'metas' => $metas, 'layout' => $this->getLayout()])->with('success', '¡Orden de trabajo registrada con éxito!'); //Redirecciono a la vista de editar de OT.
        }
        return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clase' => $clase, 'clases' => $clases, 'edit' => true, 'clasesName' => $clasesName, 'piezas' => $piezas, 'metas' => $metas, 'layout' => $this->getLayout()]); //Redirecciono a la vista de registro de OT.
    }
    public function mostrarClases($ot, $message = null)
    {
        $ot = Orden_trabajo::find($ot); //Busco la OT ingresada.
        $moldura = Moldura::find($ot->id_moldura); //Busco la moldura de la OT.

        $clasesEncontradas = Clase::where('id_ot', $ot->id)->get(); //Busco la clase.
        if ($clasesEncontradas != "[]") { //Si la clase existe 
            $clase = Clase::find($clasesEncontradas[0]->id);
            $proceso = Procesos::where('id_clase', $clase->id)->first(); //Busco la clase.
            if ($proceso) {
                // Obtener los campos donde el valor es igual a 1
                $camposConValor = []; //Guarda los campos que tienen valor a 1
                $maquinas = []; //Guarda las máquinas que se utilizaron en el proceso
                $contador = 0;  //Contador para las máquinas 
                foreach ($proceso->getAttributes() as $campo => $valor) { //Recorro los campos.
                    if ($campo != "id" && $campo != "id_clase") {
                        if ($valor != 0) { //Si el valor es diferente de 0
                            $camposConValor[$contador] = $campo; //Guardo los campos.
                            $maquinas[$contador] = $valor; //Guarda las máquinas que se utilizaron en el proceso
                            $contador++; //Aumento en el contador
                        }
                    }
                }
                return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clase' => $clase, 'proceso' => $camposConValor, 'clases' => $clasesEncontradas, 'maquinas' => $maquinas, 'layout' => $this->getLayout()]); //Redirecciono a la vista de editar de OT.
            }
            return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clases' => $clasesEncontradas, 'clase' => $clase, 'layout' => $this->getLayout()]); //Redirecciono a la vista de editar de OT.
        }
        return view('processesAdmin.RegistrarOT.registrarOT', ['ot' => $ot->id, 'moldura' => $moldura, 'layout' => $this->getLayout()]); //Redirecciono a la vista de registro de OT.
    }
    public function saveProcess(Request $request) //Función para registrar una OT.
    {
        if (isset($request->pedido)) {
            $clase = Clase::find($request->id_clase); //Busco la clase ingresada.
            if(auth()->user()->perfil != 5) {
                $clase->nombre = $request->clase;
                $clase->piezas = $request->piezas;
                $clase->pedido = $request->pedido;
                $clase->fecha_inicio = $request->fecha_inicio;
                $clase->hora_inicio = $request->hora_inicio;
                if ($request->clase != "Obturador") { //Si la clase no es obturador.
                    $clase->tamanio = $request->tamanio;
                    $clase->seccion = null;
                } else { //Si la clase es obturador.
                    $clase->seccion = $request->seccion;
                    $clase->tamanio = null;
                }
            }else{
                $clase->piezas = $request->piezas;
                $clase->pedido = $request->pedido;
            }
            $clase->save(); //Guardo los cambios.

            $metas = Metas::where('id_clase', $clase->id)->get();
            if (count($metas) > 0) {
                foreach ($metas as $meta) {
                    $hrsTrabajadas = $this->calcularHrs($meta->h_inicio, $meta->h_termino);
                    $ot = Orden_trabajo::find($clase->id_ot);
                    $clase = $this->AsignarDatos_Meta($meta, $hrsTrabajadas, $ot, $clase->nombre, $meta->proceso); //Asigno los datos de la meta.
                }
            }
        }
        $clase = Clase::find($request->id_clase); //Busco la clase ingresada.
        $moldura = Moldura::find($request->id_moldura); //Busco la moldura de la OT.
        //Si la clase existe.
        if ($clase) {
            $proceso = Procesos::where('id_clase', $clase->id)->first(); //Busco el proceso.
            if (!$proceso && $request->procesos != null) { //Si no se ha creado el registro y se han seleccionado procesos
                if (!$proceso) { //Si el registro no existe
                    $proceso = new Procesos();
                }
                $this->verificarCasillas($clase, $request->procesos, $request->maquinas, $proceso); //Verifico las casillas.
            }
            $this->verificarCasillas($clase, $request->procesos, $request->maquinas, $proceso); //Verifico las casillas.
            $procesoI = Procesos::where('id_clase', $request->id_clase)->first(); //Busco la clase.
            $camposConValor = [];
            $maquinas = [];
            if ($procesoI) {
                // Obtener los campos donde el valor es igual a 1
                $contador = 0;
                foreach ($procesoI->getAttributes() as $campo => $valor) { //Recorro los campos.
                    if ($campo != "id" && $campo != "id_clase") {
                        if ($valor != 0) { //Si el valor es diferente de 0
                            $camposConValor[$contador] = $campo; //Guardo los campos.
                            $maquinas[$contador] = $valor;
                            $contador++;
                        }
                    }
                }
            }
            $clases = Clase::where('id_ot', $request->ot)->get(); //Busco la clase.
            return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $request->ot, 'moldura' => $moldura, 'clase' => $clase, 'proceso' => $camposConValor, 'clases' => $clases, 'maquinas' => $maquinas, 'layout' => $this->getLayout()])->with('success', '¡Orden de trabajo registrada con éxito!'); //Redirecciono a la vista de registro de OT.
        } else {
            //Cuando solamente se ingresa una OT que ya existe.
            $clasesEncontradas = Clase::where('id_ot', $request->ot)->get(); //Busco la clase.
            return view('processesAdmin.RegistrarOT.registrarOT', ['ot' => $request->ot, 'moldura' => $moldura, 'clasesEncontradas' => $clasesEncontradas, 'layout' => $this->getLayout()]); //Redirecciono a la vista de registro de OT.
        }
    }
    public function verificarCasillas($clase, $procesosData, $maquinas, $proceso)
    {
        //Verificar la clase para asignar los valores respecto a ella
        switch ($clase->nombre) {
            case "Bombillo":
            case "Molde":
                $procesos = array("cepillado", "desbaste_exterior", "revision_laterales", "pOperacion", "barreno_maniobra", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado", "calificado", "acabadoBombillo", "acabadoMolde", "barreno_profundidad", "cavidades", "copiado", "offSet", "palomas", "rebajes", "grabado");
                break;
            case "Fondo":
            case "Obturador":
                $procesos = array("soldadura", "soldaduraPTA", "operacionEquipo"); //Asigno los procesos.
                break;
            case "Corona":
                $procesos = array("cepillado", "desbaste_exterior");
                break;
            case "Plato":
                $procesos = array("operacionEquipo", "barreno_profundidad");
                break;
            case "Embudo":
                $procesos = array("operacionEquipo", "embudoCM");
                break;
        }

        //Almacenar los valores en la base de datos
        $contadorMaquinas = 0;
        $proceso->id_clase = $clase->id;

        //Implementar una forma mas agil para hacer este proceso
        $proceso->cepillado = 0;
        $proceso->desbaste_exterior = 0;
        $proceso->revision_laterales = 0;
        $proceso->pOperacion = 0;
        $proceso->barreno_maniobra = 0;
        $proceso->sOperacion = 0;
        $proceso->soldadura = 0;
        $proceso->soldaduraPTA = 0;
        $proceso->rectificado = 0;
        $proceso->asentado = 0;
        $proceso->calificado = 0;
        $proceso->acabadoBombillo = 0;
        $proceso->acabadoMolde = 0;
        $proceso->barreno_profundidad = 0;
        $proceso->cavidades = 0;
        $proceso->copiado = 0;
        $proceso->offSet = 0;
        $proceso->palomas = 0;
        $proceso->rebajes = 0;
        $proceso->grabado = 0;
        $proceso->operacionEquipo = 0;
        $proceso->embudoCM = 0;

        $noProceso = 0;
        for ($i = 0; $i < count($procesos); $i++) {
            if (in_array($procesos[$i], $procesosData)) {
                $string = $procesos[$i]; //Asigno el nombre del proceso.
                //Asigno el valor de la máquina al campo correspondiente del proceso
                $proceso->$string = $maquinas[$contadorMaquinas];
                $contadorMaquinas++;

                //***IMPLEMENTAR FUNCION PARA CREAR REGISTRO EM LA TABLA fechas_procesos***/
                $procesoFechas = $this->crearRegistroFechaProceso($clase, $procesos, $i, $noProceso, $maquinas[$contadorMaquinas - 1]);
                $noProceso++;
            } else {
                $string = $procesos[$i]; //Asigno el nombre del proceso.   
                $proceso->$string = 0;
            }
        }
        //Guardar unicamente la fecha de termino
        $clase->fecha_termino = $procesoFechas->fecha_fin->format('Y-m-d');
        $clase->hora_termino = $procesoFechas->fecha_fin->format('H:i:s');
        $clase->save();
        $proceso->save(); //Guardo los cambios.
    }
    public function crearRegistroFechaProceso($clase, $procesos, $i, $noProceso, $maquinas)
    {
        $procesoExistente = Fecha_proceso::where('clase', $clase->id)->where('proceso', $procesos[$i])->first();
        if ($procesoExistente) {
            $procesoExistente->delete();
        }
        $nuevoProceso = new Fecha_proceso();
        $nuevoProceso->clase = $clase->id;
        $nuevoProceso->proceso = $procesos[$i];
        $fechaInicio = $this->calcularFechaInicio($clase, $procesos, $i, $noProceso);
        $nuevoProceso->fecha_inicio = $fechaInicio;
        $nuevoProceso->fecha_fin = $this->calcularFechaTermino($clase, $procesos, $i, $maquinas, $fechaInicio, $noProceso);
        $nuevoProceso->save();
        return $nuevoProceso;
    }
    public function calcularFechaInicio($clase, $procesos, $i, $noProceso)
    {
        $fechaInicio = "";
        $clases = ["Bombillo", "Molde", "Corona"];
        $fechaInicio = $clase->fecha_inicio . " " . $clase->hora_inicio;
        $fechaInicio = new DateTime($fechaInicio);
        // if ((in_array($clase->nombre, $clases) && $procesos[$i] == "cepillado") || (!in_array($clase->nombre, $clases) && $procesos[$i] == "operacionEquipo")) {
        if ($noProceso != 0) {
            //Obtener el anterior proceso
            $fechaInicio = $this->tiempoRetraso_Inicio_termino($procesos, $i, $clase, "inicio");
        }
        return $fechaInicio;
    }
    public function calcularFechaTermino($clase, $procesos, $i, $maquinas, $fecha, $noProceso)
    {
        // if ((in_array($clase->nombre, $clases) && $procesos[$i] == "cepillado") || (!in_array($clase->nombre, $clases) && $procesos[$i] == "operacionEquipo")) {
        if ($noProceso == 0) {
            $fechaInicio = new DateTime($fecha->format('Y-m-d H:i:s'));

            // echo "Proceso: " . $proceso . "<br>";
            // echo "Pedido: " . $clase->pedido . "<br>";

            //Calcular los dias que tarda en maquinar el proceso
            $diasMaq = $this->calcularDiasMaquinar($clase, $i, $maquinas);

            // echo "Dias maquinar: " . $diasMaq . "<br>";

            //Convertir los dias a horas y minutos
            $tiempo = $this->convertirDiasMaqAHoras($diasMaq);
            $horas = $tiempo[0];
            $minutos = $tiempo[1];
            $fecha_termino = $this->sumarHrsMnts($fechaInicio, $horas, $minutos); //Sumar horas y minutos
        } else {
            //Obtener la fecha de termino con el tiempo de retraso
            $fecha_termino = $this->tiempoRetraso_Inicio_termino($procesos, $i, $clase, "termino");
        }

        // echo "Horas: " . $horas . " Minutos: " . $minutos . "<br>";
        // echo "Fecha inicio: " . $fecha->format('l') . " " . $fecha->format('Y-m-d H:i:s') . "<br>";
        // echo "Fecha termino: " . $fecha_termino->format('l') . " " . $fecha_termino->format('Y-m-d H:i:s') . "<br>";

        return $fecha_termino;
    }
    public function calcularProcesoAnterior($procesos, $i, $clase)
    {
        $contador = 1;
        do {
            $procesoAnterior = Fecha_proceso::where('proceso', $procesos[$i - $contador])->where('clase', $clase->id)->first();
            if ($procesoAnterior == null) {
                $contador++;
            } else {
            }
        } while ($procesoAnterior == null);
        return [$procesoAnterior, $contador];
    }
    public function tiempoRetraso_Inicio_termino($procesos, $i, $clase, $etapa)
    {
        //Obtener el anterior proceso
        // echo "OT" . $clase->id_ot . $clase->nombre . "<br>";
        // echo $procesos[$i] . "<br>";
        $proceso_contador = $this->calcularProcesoAnterior($procesos, $i, $clase);
        $procesoAnterior = $proceso_contador[0];
        $contador = $proceso_contador[1];
        //Calcular los juegos por maquina y por turno
        $juegosMaqTurn = $this->juegosPorMaqTurn($i - $contador, $clase);
        if ($juegosMaqTurn != 0) {
            //Si se desea calcular la fecha de inicio
            if ($etapa == "inicio") {
                $fecha = new DateTime($procesoAnterior->fecha_inicio);
            } else { //Si se desea calcular la fecha de termmino
                $fecha = new DateTime($procesoAnterior->fecha_fin);
            }
            $fechaAux = new DateTime($fecha->format('Y-m-d H:i:s'));
            //Se calcula cuanto tiempo se tarda en generar una pieza para calcular el tiempo de retraso entre el proceso
            //Falta agregar el factor de riesgo
            $procesosJuegos = ["cepillado", "desbaste", "revLaterales", "primeraOpeSoldadura", "barrenoManiobra", "segundaOpeSoldadura", "soldadura", "soldaduraPTA", "rectificado", "asentado", "revCalificado", "acabadoBombillo", "acabadoMolde", "barrenoProfundidad", "cavidades", "copiado", "offset", "palomas", "rebajes"];
            $tiempoRetraso = tiempoproduccion::where('clase', $clase->nombre)->where('proceso', $procesosJuegos[$i - $contador])->where('tamanio', $clase->tamanio)->first();
            if ($tiempoRetraso) {
                //Agregar el factor de seguridad
                $factorSeguridad = $tiempoRetraso->tiempo * .08;
                $factorSeguridad = round($factorSeguridad);
                $tiempoRetraso = $tiempoRetraso->tiempo + $factorSeguridad;
            } else {
                $tiempoRetraso = 0;
            }

            // echo 'Tiempo retraso: ' . $tiempoRetraso . "<br>";
            $fechaAux->modify("+{$tiempoRetraso} minutes");

            if ($fechaAux->format('H') >= 22) {
                $fecha->modify("+1 days");
                $fecha->setTime(6, 0, 0);
                $fecha->modify("+{$tiempoRetraso} minutes");
            } else if ($fechaAux->format('H') >= 19 && $fechaAux->format('l') == "Saturday") {
                $fecha->modify("+2 days");
                $fecha->setTime(6, 0, 0);
                $fecha->modify("+{$tiempoRetraso} minutes");
            } else {
                $fecha->modify("+{$tiempoRetraso} minutes");
            }
        } else {
            //Obtener el anterior proceso
            $proceso_contador = $this->calcularProcesoAnterior($procesos, $i, $clase);
            $procesoAnterior = $proceso_contador[0];
            $contador = $proceso_contador[1];

            $fecha = new DateTime($procesoAnterior->fecha_fin);
        }
        // echo $fecha->format('Y-m-d H:i:s') . "<br>";
        return $fecha;
    }
    public function calcularDiasMaquinar($clase, $i, $maquinas)
    {
        $juegosTurn = $maquinas * $this->juegosPorMaqTurn($i, $clase);
        $juegosDia = $juegosTurn * 2;
        if ($juegosDia != 0) {
            $diasMaq = $clase->pedido / $juegosDia;
            $diasMaq = floor($diasMaq * 100) / 100; //Tomar solo dos numeros despues del punto y redondearlo
        } else {
            $diasMaq = 0;
        }
        return $diasMaq;
    }
    public function convertirDiasMaqAHoras($diasMaq)
    {
        $tiempoMaq = $diasMaq * 16;
        $hrsMaq = (int)$tiempoMaq;
        $mntsMaq = round($tiempoMaq - $hrsMaq, 2) * 100;
        if ($mntsMaq >= 60) {
            $hrsMaq++;
            $mntsMaq -= 60;
        }
        return [$hrsMaq, $mntsMaq];
    }
    public function sumarHrsMnts($fecha, $horas, $minutos)
    {
        while ($horas != 0) {
            if ($minutos >= 60) {
                $horas++;
                $minutos -= 60;
            }
            if ($fecha->format('H') == 21) {
                if ($fecha->format('i') > 0) {
                    $mntosSobrantes = 60 - $fecha->format('i');
                    $minutos += $mntosSobrantes;
                }
                $fecha->modify("+1 days");
                $horas--;
                $fecha->setTime(6, 0, 0);
            } else if ($fecha->format('H') == 18 && $fecha->format('l') == "Saturday") {
                if ($fecha->format('i') > 0) {
                    $mntosSobrantes = 60 - $fecha->format('i');
                    $minutos += $mntosSobrantes;
                }
                $horas--;
                $fecha->modify("+2 days");
                $fecha->setTime(6, 0, 0);
            } else {
                $horas--;
                $fecha->modify("+1 hours");
            }
        }
        if ($minutos > 0) {
            $fecha->modify("+{$minutos} minutes");
        }
        return $fecha;
    }
    public function juegosPorMaqTurn($i, $clase)
    {
        $procesos = ["cepillado", "desbaste", "revLaterales", "primeraOpeSoldadura", "barrenoManiobra", "segundaOpeSoldadura", "soldadura", "soldaduraPTA", "rectificado", "asentado", "revCalificado", "acabadoBombillo", "acabadoMolde", "barrenoProfundidad", "cavidades", "copiado", "offset", "palomas", "rebajes"];

        $juegos = 0;
        $t_estandar = tiempoproduccion::where('clase', $clase->nombre)->where('proceso', $procesos[$i])->where('tamanio', $clase->tamanio)->first();
        if ($t_estandar && $t_estandar->tiempo != 0) {
            $juegos = 420 / $t_estandar->tiempo;
            $juegos = floor($juegos * 10) / 10;
        }
        return $juegos;
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

    public function calcularMeta($t_estandar, $hrsTrabajadas) //Función para calcular la meta.
    {
        //Calculo de la meta.
        $tiempo = $t_estandar != 0 ? round(($hrsTrabajadas / $t_estandar)) : 0;
        return $tiempo;
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

    public function AsignarDatos_Meta($meta, $hrsTrabajadas, $ot, $reqClase, $proceso) //Función para asignar los datos de la meta.
    {
        $clase = Clase::where('id_ot', $ot->id)->where('nombre', $reqClase)->first(); //Busco la clase.
        $meta->id_clase = $clase->id;

        $tiempo = tiempoproduccion::where('clase', $clase->nombre)->where('tamanio', $clase->tamanio)->where('proceso', $proceso)->first();
        $meta->t_estandar = $tiempo->tiempo ?? 0;
        $meta->meta = $this->calcularMeta($meta->t_estandar, $hrsTrabajadas) ?? 0; //Se calcula la meta.

        $meta->save();
        return $clase; //Se retorna la clase.
    }
}
