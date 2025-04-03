<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Fecha_proceso;
use App\Models\Orden_trabajo;
use App\Models\Procesos;
use App\Models\tiempoproduccion;
use Illuminate\Http\Request;

class tiemposProduccionController extends Controller
{
    protected $controladorPzas;
    protected $controladorOT;
    public function __construct()
    {
        $this->controladorPzas = new PzasLiberadasController();
        $this->controladorOT = new OTController();
    }
    public function show($clase = false)
    {
        //Obtener el perfil del usuario
        $layout = $this->controladorPzas->obtenerLayout();

        $tiempos = [];
        $tiemposProduccion = tiempoproduccion::whereIn('clase', ['Bombillo', 'Molde'])->get();
        if ($tiemposProduccion->count() > 0) {
            foreach ($tiemposProduccion as $tiempo) {
                $tiempos[$tiempo->clase][$tiempo->proceso][$tiempo->tamanio] = $tiempos[$tiempo->clase][$tiempo->proceso][$tiempo->tamanio] ?? [];
                foreach ($tiempo->toArray() as $columna => $valor) {
                    if ($columna == 'clase' || $columna == 'proceso' || $columna == 'tamanio' || $columna == 'created_at' || $columna == 'updated_at') {
                        continue;
                    }
                    $tiempos[$tiempo->clase][$tiempo->proceso][$tiempo->tamanio][$columna] = $valor;
                }
            }
        } else {
            $tiempos = null;
        }
        if ($clase) {
            return view('processesAdmin.tiemposProduccion', compact('tiempos', 'clase', 'layout'));
        }
        return view('processesAdmin.tiemposProduccion', compact('tiempos', 'layout'));
    }
    public function store(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            if ($key == '_token' || $key == "clase") {
                continue;
            }
            $tamanios = ['Chico', 'Mediano', 'Grande'];
            foreach ($value as $k => $v) {
                $tiempo = tiempoproduccion::where('clase', $request->input('clase'))->where('tamanio', $tamanios[$k])->where('proceso', $key)->first();
                if ($tiempo) {
                    if ($v == null) {
                        $v = 0;
                    } else {
                        $tiempo->tiempo = $v;
                    }
                    $tiempo->save();
                } else {
                    $tiempo = new tiempoproduccion();
                    $tiempo->clase = $request->input('clase');
                    $tiempo->tamanio = $tamanios[$k];
                    $tiempo->proceso = $key;
                    if ($v == null) {
                        $v = 0;
                    } else {
                        $tiempo->tiempo = $v;
                    }
                    $tiempo->save();
                }
            }
        }
        $clase = $request->input('clase');


        //Actualizar todas las Clases
        $this->update();
        return redirect()->route("mostrarTiempos", compact('clase'))->with('success', 'Tiempos de producción actualizados correctamente.');
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
        // die();
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
        for ($j = 0; $j < count($clase[1]); $j++) {
            for ($i = 0; $i < count($procesos); $i++) {
                if ($procesos[$i] == $clase[1][$j]) {
                    $maquinas = $this->obtenerMaquinasClase($clase[0]->id, $clase[1][$j]);
                    $procesoFechas = $this->controladorOT->crearRegistroFechaProceso($clase[0], $procesos, $i, $noProceso, $maquinas);
                    $noProceso++;
                }
            }
            // echo "<br>" . "<br>";
        }
        //obtener la clase
        $clase = Clase::find($clase[0]->id);
        //Guardar unicamente la fecha de termino
        $clase->fecha_termino = $procesoFechas->fecha_fin->format('Y-m-d');
        $clase->hora_termino = $procesoFechas->fecha_fin->format('H:i:s');
        $clase->save();
    }
    public function obtenerMaquinasClase($claseID, $proceso)
    {
        $maquinas = Procesos::where('id_clase', $claseID)->distinct()->value($proceso);
        return $maquinas;
    }
}
