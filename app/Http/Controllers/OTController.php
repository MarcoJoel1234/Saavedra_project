<?php

namespace App\Http\Controllers;

use App\Http\Requests\OTRequest;
use App\Models\AcabadoBombilo;
use App\Models\AcabadoMolde;
use App\Models\Asentado;
use App\Models\BarrenoManiobra;
use App\Models\Cavidades;
use App\Models\Cepillado;
use App\Models\Clase;
use App\Models\Copiado;
use App\Models\DesbasteExterior;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\PrimeraOpeSoldadura;
use App\Models\Procesos;
use App\Models\PySOpeSoldadura;
use App\Models\Rectificado;
use App\Models\revCalificado;
use App\Models\RevLaterales;
use App\Models\SegundaOpeSoldadura;
use App\Models\Soldadura;
use App\Models\SoldaduraPTA;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Input\Input;

class OTController extends Controller
{
    public function show()
    {
        $molduras = Moldura::all();
        $oTrabajo = Orden_trabajo::all();
        //Si existen ordenes de trabajo registradas.
        if ($oTrabajo != "[]") {
            $moldurasOT = []; //Arreglo para guardar las molduras de cada OT
            $contador = 0; // Contador para las molduras y OT
            foreach ($oTrabajo as $ot) {
                $moldura = Moldura::find($ot->id_moldura);
                $moldurasOT[$contador] = $ot->id . " - " . $moldura->nombre;
                $contador++;
            }
            return view('processesAdmin.RegistrarOT.registrarOT', ['molduras' => $molduras, 'moldurasOT' => $moldurasOT, 'oTrabajo' => $oTrabajo]); //Retorno la vista de registro de OT con las molduras.
        }
        return view('processesAdmin.RegistrarOT.registrarOT', ['molduras' => $molduras]); //Retorno la vista de registro de OT con las molduras.
    }

    public function deleteOT($ot)
    {
        $piezas = Pieza::where('id_ot', $ot)->get(); //Busco las piezas de la OT
        $meta = Metas::where('id_ot', $ot)->get();
        if (count($piezas) == 0 && count($meta) == 0) { //Si la OT no tiene piezas ni metas asociadas entonces
            $clase = Clase::where('id_ot', $ot)->get(); //Busco todas las clases que pertenecen a la OT
            foreach ($clase as $clase) { //Recorro las clases de la OT
                $proceso = Procesos::where('id_clase', $clase->id)->first();
                if ($proceso) {
                    Procesos::find($proceso->id)->delete(); //Elimino el proceso de la clase                    
                }
                Clase::find($clase->id)->delete(); //Elimino la clase de la OT                
            }
            Orden_trabajo::find($ot)->delete(); //Elimino la OT
            return redirect()->route('registerOT')->with('success', '¡Orden de trabajo eliminada con éxito!'); //Redirecciono a la vista de registro de la OT
        }
        return redirect()->route('registerOT')->withErrors('¡La orden de trabajo no se puede eliminar porque tiene piezas o metas asociadas!');
    }
    public function store(OTRequest $request) //Funcion para registrar una OT.
    {
        if (isset($request->ot)) {
            $ot = Orden_trabajo::find($request->ot); //Busco la OT ingresada
        } else {
            $ot = Orden_trabajo::find($request->otSeleccionada); //Busco a la OT seleccionada
        }
        //Si la orden de trabajo existe.
        if ($ot) {
            $moldura = Moldura::find($request->id_moldura);
            //Si el usuario ingreso datos una clase.
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
                    $camposConValorUno = [];
                    $contador = 0; //Contador para los campos
                    foreach ($proceso->getAttributes() as $campo => $valor) { //Recorro los campos.
                        if ($valor == 1) { //Si el valor es igual a 1.
                            $camposConValorUno[$contador] = $campo; //Guardo los campos.
                            $contador++;
                        }
                    }
                    $clasesEncontradas = Clase::where('id_ot', $request->ot)->get(); //Busco la clase.
                    return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clase' => $clase, 'proceso' => $camposConValorUno, 'clases' => $clasesEncontradas]); //Redirecciono a la vista de registro de OT.
                } else {
                    $clasesEncontradas = Clase::where('id_ot', $request->ot)->get(); //Busco la clase.
                    return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clase' => $clase, 'clases' => $clasesEncontradas]); //Redirecciono a la vista de registro de OT.
                }
            }
            //Cuando solamente se ingresa una OT que ya existe.
            $clasesEncontradas = Clase::where('id_ot', $ot->id)->get(); //Busco la clase.
            if ($clasesEncontradas) {
                return view('processesAdmin.RegistrarOT.registrarOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clasesEncontradas' => $clasesEncontradas]); //Redirecciono a la vista de registro de OT.
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
                return redirect()->to('/registerOT')->withErrors('La orden de trabajo seleccionada ya existe con otra moldura'); //Redirecciono a la vista de registro de OT.
            }
        }
        return view('processesAdmin.RegistrarOT.registrarOT', ['ot' => $request->ot, 'moldura' => $moldura]); //Redirecciono a la vista de registro de OT.
    }
    public function registerClass($ot)
    {
        $otEncontrada = Orden_trabajo::find($ot); //Busco la OT ingresada.
        $moldura = Moldura::find($otEncontrada->id_moldura); //Busco la OT ingresada.
        $clasesEncontradas = Clase::where('id_ot', $ot)->get(); //Busco la clase.
        return view('processesAdmin.RegistrarOT.registrarOT', ['ot' => $ot, 'moldura' => $moldura, 'clasesEncontradas' => $clasesEncontradas]); //Redirecciono a la vista de registro de OT.
    }
    public function deleteClass($clase, $claseIndice)
    {
        $clase = Clase::find($clase);
        $claseIndice = Clase::find($claseIndice);
        $proceso = Procesos::where('id_clase', $clase->id)->first();
        //Si el proceso existe.
        if ($proceso) {
            $proceso->delete(); //Elimino el proceso de la clase
        }
        $ot = $clase->id_ot; //Busco la OT ingresada.
        $ot = Orden_trabajo::find($ot); //Busco la OT ingresada
        Clase::destroy($clase->id); //Elimino la clase
        $ruta = route('mostrarClases', ['ot' => $ot]);
        return redirect($ruta); //Redirecciono a la vista de registro de la OT
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
            return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clase' => $clase, 'proceso' => $camposConValor, 'clases' => $clases, 'maquinas' => $maquinas, 'clasesName' => $clasesName, 'edit' => true, 'piezas' => $piezas, 'metas' => $metas])->with('success', '¡Orden de trabajo registrada con éxito!'); //Redirecciono a la vista de editar de OT.
        }
        return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clase' => $clase, 'clases' => $clases, 'edit' => true, 'clasesName' => $clasesName, 'piezas' => $piezas, 'metas' => $metas]); //Redirecciono a la vista de registro de OT.
    }
    public function mostrarClases($ot)
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
                return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clase' => $clase, 'proceso' => $camposConValor, 'clases' => $clasesEncontradas, 'maquinas' => $maquinas])->with('success', '¡Orden de trabajo registrada con éxito!'); //Redirecciono a la vista de editar de OT.
            }
            return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $ot->id, 'moldura' => $moldura, 'clases' => $clasesEncontradas, 'clase' => $clase]); //Redirecciono a la vista de editar de OT.
        }
        return view('processesAdmin.RegistrarOT.registrarOT', ['ot' => $ot->id, 'moldura' => $moldura]); //Redirecciono a la vista de registro de OT.
    }
    public function saveProcess(Request $request) //Función para registrar una OT.
    {
        if (isset($request->pedido)) {
            $clase = Clase::find($request->id_clase); //Busco la clase ingresada.
            $clase->nombre = $request->clase;
            $clase->piezas = $request->piezas;
            $clase->pedido = $request->pedido;
            $clase->fecha_inicio = $request->fecha_inicio;
            $clase->hora_inicio = $request->hora_inicio;
            if ($request->clase != "Obturador") { //Si la clase no es obturador.
                $clase->tamanio = $request->tamanio; //Actualizo
                $clase->seccion = null;
            } else { //Si la clase es obturador.
                $clase->seccion = $request->seccion;
                $clase->tamanio = null;
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
            if (!$proceso || isset($request->pedido)) { //Si la clase no existe entonces...
                if (!$proceso) {
                    $proceso = new Procesos();
                }
                $this->verificarCasillas($clase, $request->procesos, $request->maquinas, $proceso); //Verifico las casillas.
            }
            $procesoI = Procesos::where('id_clase', $request->id_clase)->first(); //Busco la clase.
            if ($procesoI) {
                // Obtener los campos donde el valor es igual a 1
                $camposConValor = [];
                $maquinas = [];
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
            return view('processesAdmin.RegistrarOT.infoOT', ['ot' => $request->ot, 'moldura' => $moldura, 'clase' => $clase, 'proceso' => $camposConValor, 'clases' => $clases, 'maquinas' => $maquinas])->with('success', '¡Orden de trabajo registrada con éxito!'); //Redirecciono a la vista de registro de OT.
        } else {
            //Cuando solamente se ingresa una OT que ya existe.
            $clasesEncontradas = Clase::where('id_ot', $request->ot)->get(); //Busco la clase.
            return view('processesAdmin.RegistrarOT.registrarOT', ['ot' => $request->ot, 'moldura' => $moldura, 'clasesEncontradas' => $clasesEncontradas]); //Redirecciono a la vista de registro de OT.
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
                $procesos = array("operacionEquipo", "soldadura", "soldaduraPTA"); //Asigno los procesos.
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
        for ($i = 0; $i < count($procesos); $i++) {
            if (in_array($procesos[$i], $procesosData)) {
                $string = $procesos[$i]; //Asigno el nombre del proceso.   
                $proceso->$string = $maquinas[$contadorMaquinas];
                $contadorMaquinas++;
            } else {
                $string = $procesos[$i]; //Asigno el nombre del proceso.   
                $proceso->$string = 0;
            }
        }
        $proceso->save(); //Guardo los cambios.
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
                $clase = Clase::where('id_ot', $ot->id)->where('nombre', $request->clase)->first(); //Busco la clase.
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
                            case "cavidades":
                                return view('processes.cavidades', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "copiado":
                                return view('processes.copiado', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                            case "pysOpeSoldadura":
                                return view('processes.pysOpeSoldadura', ['band' => 3, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clase' => $clase]);
                        }
                    }
                }
                //Si se ingreso una contraseña incorrecta se retornan a las vistas correspondientes con los campos deshabilitados.
                switch ($request->proceso) {
                    case "Cepillado":
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
                    case "cavidades":
                        return redirect()->route('cavidadesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "copiado":
                        return redirect()->route('copiadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                    case "pysOpeSoldadura":
                        return redirect()->route('1y2OpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase, 'operacion' => $request->operacion]);
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
                        case 'cavidades':
                            $clases = $this->ClaseEncontradas($ot->id, "cavidades"); //Se obtienen las clases disponibles en cavidades
                            return view('processes.cavidades', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]); //Retorno la vista de acabado molde
                        case 'copiado':
                            $clases = $this->ClaseEncontradas($ot->id, "copiado"); //Se obtienen las clases disponibles en copiado
                            return view('processes.copiado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]); //Retorno la vista de acabado molde
                        case "pysOpeSoldadura": //Se obtienen las clases disponibles en 1ra y 2da operación de soldadura.
                            $clases = $this->ClaseEncontradas($ot->id, "operacionEquipo"); //Se obtienen las clases disponibles en 1 y 2 operacion equipo
                            return view('processes.pysOpeSoldadura', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $metaExistente, 'clases' => $clases]);
                    }
                }
                if (isset($request->clases)) { //Si existe una clase ingresada.
                    if ($request->proceso == "pysOpeSoldadura") { //Si el proceso es pysOpeSoldadura.
                        if (isset($request->operacion)) { //Si la operación existe  
                            $clase = $this->AsignarDatos_Meta($metaExistente, $hrsTrabajadas, $ot, $request->clases, $request->proceso); //Asigno los datos de la meta.
                        } else {
                            $clases = $this->ClaseEncontradas($ot->id, "cepillado"); //Obtengo las clases que no son nulas.
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
                        //Retorno la vista de desbaste.
                        return redirect()->route('barrenoManiobraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "segundaOpeSoldadura":
                        $id = "2opeSoldadura_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Segunda Operación de Soldadura.
                        $segundaOpeSoldadura = SegundaOpeSoldadura::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($segundaOpeSoldadura)) {
                            return redirect()->route('segundaOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('segundaOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);

                    case "soldadura":
                        $id = "soldadura_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Soldadura
                        $soldadura = Soldadura::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($soldadura)) {
                            return redirect()->route('soldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('soldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "soldaduraPTA":
                        $id = "soldaduraPTA_" . $request->clases . "_" . $ot->id; //Creación de id para tabla SoldaduraPTA
                        $soldaduraPTA = SoldaduraPTA::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($soldaduraPTA)) {
                            return redirect()->route('soldaduraPTAHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('soldaduraPTAHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "rectificado":
                        $id = "rectificado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $rectificado = Rectificado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($rectificado)) {
                            return redirect()->route('rectificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('rectificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "asentado":
                        $id = "asentado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $rectificado = Asentado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($rectificado)) {
                            return redirect()->route('asentadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('asentadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "revCalificado":
                        $id = "revCalificado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $calificado = revCalificado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($calificado)) {
                            return redirect()->route('calificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de desbaste.
                        }
                        return redirect()->route('calificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "acabadoBombillo":
                        $id = "acabadoBombillo_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $acabadoBombillo = AcabadoBombilo::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($acabadoBombillo)) {
                            return redirect()->route('acabadoBombilloHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de desbaste.
                        }
                        return redirect()->route('acabadoBombilloHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "acabadoMolde":
                        $id = "acabadoMolde_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $acabadoMolde = AcabadoMolde::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($acabadoMolde)) {
                            return redirect()->route('acabadoMoldeHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de desbaste.
                        }
                        return redirect()->route('acabadoMoldeHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "cavidades":
                        $id = "cavidades_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $cavidades = Cavidades::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($cavidades)) {
                            return redirect()->route('cavidadesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de desbaste.
                        }
                        return redirect()->route('cavidadesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "copiado":
                        $id = "copiado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Copiado
                        $copiado = Copiado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($copiado)) {
                            return redirect()->route('copiadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de copiado
                        }
                        return redirect()->route('copiadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "pysOpeSoldadura":
                        $pysOpeSoldadura = PySOpeSoldadura::where('id_clase', $request->clases)->where('id_ot', $ot->id)->where('operacion', $request->operacion)->first(); //Busco la OT que se quiere editar.
                        if (isset($pysOpeSoldadura)) {
                            return redirect()->route('1y2OpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase, 'operacion' => $request->operacion]);
                        }
                        // //Retorno la vista de desbaste.
                        return redirect()->route('1y2OpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'operacion' => $request->operacion]);
                }
            } else {
                //Cuando ya se han registrado todos los datos de la meta.
                switch ($request->proceso) {
                    case "cepillado":
                        $id = "cepillado_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Cepillado.
                        $cepillado = Cepillado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($cepillado)) {
                            return redirect()->route('cepilladoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]); //Retorno la vista de cepillado.
                        }
                        return view('processes.cepillado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $metaExistente, 'clase' => $clase]); //Retorno la vista de cepillado.
                    case "desbaste":
                        $id = "desbaste_" . $clase->nombre . "_" . $ot->id; //Creación de id para tabla Desbaste.
                        $desbaste = DesbasteExterior::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($desbaste)) {
                            return redirect()->route('desbasteHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                        }
                        return view('desbaste.cepillado', ['band' => 2, 'moldura' => $moldura->nombre, 'ot' => $ot, 'meta' => $metaExistente, 'clase' => $clase]); //Retorno la vista de cepillado.
                    case "revLaterales":
                        $id = "revLaterales_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Revision Laterales.
                        $revLaterales = RevLaterales::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($revLaterales)) {
                            return redirect()->route('revLateralesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('revLateralesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "primeraOpeSoldadura": //Creación de id para la tabla de primera operación de Primera Operación de Soldadura.
                        $id = "1opeSoldadura_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Primera Operación de Soldadura.
                        $primeraOpeSoldadura = PrimeraOpeSoldadura::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($primeraOpeSoldadura)) { //Si existe la OT.
                            return redirect()->route('primeraOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('primeraOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "barrenoManiobra":
                        $id = "barrenoManiobra_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Barreno Maniobra.
                        $barrenoManiobra = BarrenoManiobra::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($barrenoManiobra)) { //Si existe la OT.
                            return redirect()->route('barrenoManiobraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]);
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('barrenoManiobraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "segundaOpeSoldadura":
                        $id = "2opeSoldadura_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Segunda Operación de Soldadura
                        $segundaOpeSoldadura = SegundaOpeSoldadura::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($segundaOpeSoldadura)) { //Si existe la OT.
                            return redirect()->route('segundaOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de cepillado
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('segundaOpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "soldadura":
                        $id = "soldadura_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Soldadura
                        $soldadura = Soldadura::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($soldadura)) { //Si existe la OT.
                            return redirect()->route('soldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de cepillado
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('soldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "soldaduraPTA":
                        $id = "soldaduraPTA_" . $request->clases . "_" . $ot->id; //Creación de id para tabla SoldaduraPTA
                        $soldaduraPTA = SoldaduraPTA::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($soldaduraPTA)) { //Si existe la OT.
                            return redirect()->route('soldaduraPTAHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de cepillado
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('soldaduraPTAHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "rectificado":
                        $id = "rectificado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $rectificado = Rectificado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($rectificado)) { //Si existe la OT.
                            return redirect()->route('rectificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de cepillado
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('rectificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "asentado":
                        $id = "asentado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $asentado = Asentado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($asentado)) { //Si existe la OT.
                            return redirect()->route('asentadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de cepillado
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('asentadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'revCalificado':
                        $id = "revCalificado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $calificado = revCalificado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($calificado)) { //Si existe la OT.
                            return redirect()->route('calificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de cepillado
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('calificadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'acabadoBombillo':
                        $id = "acabadoBombillo_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $acabadoBombillo = AcabadoBombilo::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($acabadoBombillo)) { //Si existe la OT.
                            return redirect()->route('acabadoBombilloHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de cepillado
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('acabadoBombilloHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'acabadoMolde':
                        $id = "acabadoMolde_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $acabadoMolde = AcabadoMolde::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($acabadoMolde)) { //Si existe la OT.
                            return redirect()->route('acabadoMoldeHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de cepillado
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('acabadoMoldeHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'cavidades':
                        $id = "cavidades_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $cavidades = Cavidades::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($cavidades)) { //Si existe la OT.
                            return redirect()->route('cavidadesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de cepillado
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('cavidadesHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case 'copiado':
                        $id = "copiado_" . $request->clases . "_" . $ot->id; //Creación de id para tabla Rectificado
                        $copiado = Copiado::where('id_proceso', $id)->first(); //Busco la OT que se quiere editar.
                        if (isset($copiado)) { //Si existe la OT.
                            return redirect()->route('copiadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase]); //Retorno la vista de cepillado
                        }
                        //Retorno la vista de desbaste.
                        return redirect()->route('copiadoHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id]);
                    case "pysOpeSoldadura": //Creación de id para la tabla de Primera Operación y Segunda Operación de Soldadura.
                        echo $proceso = PySOpeSoldadura::find($metaExistente->id_proceso); //Busco la OT que se requiere editar
                        if (isset($proceso)) {
                            return redirect()->route('1y2OpeSoldaduraHeaderGet')->with(['controller' => 3, 'meta' => $metaExistente->id, 'clase' => $clase, 'operacion' => $proceso->operacion]);
                        }
                }
            }
        } else {
            //Si no existe la meta ingresada se crea una nueva.
            $meta = new Metas();
            $meta->id_ot = $request->ot;
            $meta->id_usuario = $request->id_usuario;
            $meta->fecha = $request->fecha;
            $meta->h_inicio = $request->h_inicio;
            $meta->h_termino = $request->h_termino;
            $meta->maquina = $request->maquina;
            $meta->proceso = $request->proceso;
            $meta->save();

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
                    return view('processes.asentado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de asentado1
                case 'revCalificado':
                    $clases = $this->ClaseEncontradas($meta->id_ot, "calificado"); //Obtengo las clases que no son nulas.
                    return view('processes.revCalificado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]);
                case 'acabadoBombillo':
                    $clases = $this->ClaseEncontradas($meta->id_ot, "acabadoBombillo"); //Obtengo las clases que no son nulas.
                    return view('processes.revAcabadosBombillo', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]);
                case 'acabadoMolde':
                    $clases = $this->ClaseEncontradas($meta->id_ot, "acabadoMolde"); //Obtengo las clases que no son nulas.
                    return view('processes.revAcabadosMolde', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]);
                case 'cavidades':
                    $clases = $this->ClaseEncontradas($meta->id_ot, "cavidades"); //Obtengo las clases que no son nulas.
                    return view('processes.cavidades', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]);
                case 'copiado':
                    $clases = $this->ClaseEncontradas($meta->id_ot, "copiado"); //Obtengo las clases que no son nulas.
                    return view('processes.copiado', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]);
                case "pysOpeSoldadura":
                    $clases = $this->ClaseEncontradas($meta->id_ot, "operacionEquipo"); //Obtengo las clases que no son nulas.
                    return view('processes.pysOpeSoldadura', ['band' => 1, 'moldura' => $moldura->nombre, 'meta' => $meta, 'clases' => $clases]); //Retorno la vista de pysOpeSoldadura.
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
        return round(($hrsTrabajadas / $t_estandar)); //Calculo de la meta.
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
        $meta->id_clase = $clase->id; //Asigno los datos de la OT.
        switch ($proceso) {
            case "cepillado":
                $meta->t_estandar = $this->asignarMetas($clase, 52, 60, 90, 53, 64, 120,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case "desbaste":
                $meta->t_estandar = $this->asignarMetas($clase, 26, 30, 35, 26, 30, 35,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case "revLaterales":
                $meta->t_estandar = $this->asignarMetas($clase, 20, 24, 26, 20, 24, 26,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case "primeraOpeSoldadura":
                $meta->t_estandar = $this->asignarMetas($clase, 24, 28, 30, 20, 24, 26,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case "barrenoManiobra":
                $meta->t_estandar = $this->asignarMetas($clase, 15, 15, 15, 15, 15, 15,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case "segundaOpeSoldadura":
                $meta->t_estandar = $this->asignarMetas($clase, 24, 28, 28, 24, 28, 30,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case "soldadura":
                $meta->t_estandar = $this->asignarMetas($clase, 24, 30, 34,  24, 30, 70,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case "soldaduraPTA":
                $meta->t_estandar = $this->asignarMetas($clase, 24, 30, 34,  24, 30, 70,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case "rectificado":
                $meta->t_estandar = $this->asignarMetas($clase, 12, 13, 14,  12, 13, 20,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case "asentado":
                $meta->t_estandar = $this->asignarMetas($clase, 20, 24, 30,  20, 24, 30,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case 'revCalificado':
                $meta->t_estandar = $this->asignarMetas($clase, 22, 24, 26,  22, 24, 26,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case 'acabadoBombillo':
                $meta->t_estandar = $this->asignarMetas($clase, 25, 27, 28,  0, 0, 0,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case 'acabadoMolde':
                $meta->t_estandar = $this->asignarMetas($clase, 0, 0, 0,  24, 26, 30,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case 'cavidades':
                $meta->t_estandar = $this->asignarMetas($clase, 0, 0, 0,  0, 0, 0,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case 'copiado':
                $meta->t_estandar = $this->asignarMetas($clase, 27, 29, 0,  0, 0, 0,  0, 0, 0); //Asigno el tiempo estandar.
                break;
            case "pysOpeSoldadura":
                $meta->t_estandar = $this->asignarMetas($clase, 20, 20, 20,  24, 24, 24,  0, 0, 0); //Asigno el tiempo estandar.
                break;
        }
        if ($meta->t_estandar != 0) {
            $meta->meta = $this->calcularMeta($meta->t_estandar, $hrsTrabajadas); //Calculo la meta.
        } else {
            $meta->meta = 0;
        }

        $meta->save(); //Guardo los cambios.
        return $clase; //Retorno la clase.
    }
    public function asignarMetas($clase, $b1, $b2, $b3,  $m1, $m2, $m3,  $c1, $c2, $c3) //Función para asignar los tiempos estándar.
    {
        switch ($clase->nombre) { //Switch para asignar el tiempo estandar.
            case "Bombillo":
                switch ($clase->tamanio) { //Asigno el tiempo estandar.
                    case "Chico":
                        $t_estantar = $b1; //Asigno el tiempo estandar.    
                        break;
                    case "Mediano":
                        $t_estantar = $b2; //Asigno el tiempo estandar.
                        break;
                    case "Grande":
                        $t_estantar = $b3; //Asigno el tiempo estandar.
                        break;
                }
                break;
            case "Molde":
                switch ($clase->tamanio) { //Asigno el tiempo estandar.
                    case "Chico":
                        $t_estantar = $m1; //Asigno el tiempo estandar.
                        break;
                    case "Mediano":
                        $t_estantar = $m2; //Asigno el tiempo estandar.
                        break; //Asigno el tiempo estandar.
                    case "Grande":
                        $t_estantar = $m3; //Asigno el tiempo estandar.
                        break; //Asigno el tiempo estandar.
                }
                break;
            case "Corona":
                switch ($clase->tamanio) { //Asigno el tiempo estandar.
                    case "Chico":
                        $t_estantar = $c1; //Asigno el tiempo estandar.
                        break;
                    case "Mediano":
                        $t_estantar = $c2; //Asigno el tiempo estandar.
                        break; //Asigno el tiempo estandar.
                    case "Grande":
                        $t_estantar = $c3; //Asigno el tiempo estandar.
                        break; //Asigno el tiempo estandar.
                }
                break;
        }
        return $t_estantar; //Retorno el tiempo estandar.
    }
}
