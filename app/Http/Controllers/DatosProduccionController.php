<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Metas;
use App\Models\Moldura;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Procesos;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Mockery\Undefined;

class DatosProduccionController extends Controller
{
    protected $controladorPzas;
    public function __construct()
    {
        $this->controladorPzas = new PzasLiberadasController();
    }
    public function index($operadores = null, $filtros = null)
    {
        //Obtener el perfil del usuario
        $layout = $this->controladorPzas->obtenerLayout();
        //Obtener todas las ordenes de trabajo y sus respectivas clases
        $datos = $this->obtenerDatos($this->obtenerOtArray());
        if ($operadores == null) {
            return view('processesAdmin.datosProduccion', compact('layout', 'datos'));
        } else {
            return view('processesAdmin.datosProduccion', compact('layout', 'datos', 'operadores', 'filtros'));
        }
    }
    public function obtenerOtArray()
    {
        $otArray = Orden_trabajo::all();
        return $otArray;
    }
    public function obtenerDatos($OTs)
    {
        $datos = [];
        foreach ($OTs as $ot) {
            //Asignar nombre de la moldura en el arreglo
            $this->insertarMoldura($ot, $datos[$ot->id]["moldura"]);

            //Agregar operadores de la orden de trabajo 
            $this->insertarDatosRestantes($ot, $datos[$ot->id]["operadores"]);
        }

        return $datos;
    }
    public function insertarMoldura($ot, &$array)
    {
        $moldura =  Moldura::find($ot->id_moldura);
        $array = $moldura->nombre;
    }
    public function insertarDatosRestantes($ot, &$arrayOperadores)
    {
        $arrayOperadores = [];
        $piezas = Pieza::where("id_ot", $ot->id)->get(); //Obtener las piezas en la que se ha trabjado la OT
        foreach ($piezas as $pieza) {
            //Verificar que el operador no se haya agregado previamente
            if (!array_key_exists($pieza->id_operador, $arrayOperadores)) {
                $arrayOperadores[$pieza->id_operador] = [];
                //Obtener el nombre del operador
                $operador = User::where("matricula", $pieza->id_operador)->first();
                $arrayOperadores[$pieza->id_operador]["nombre"] = $operador->nombre . " " . $operador->a_paterno . " " . $operador->a_materno;

                $arrayOperadores[$operador->matricula]["clases"] = [];

                //Obtener el nombre de la clase en la que ha trabajado el operador
                $clase = Clase::find($pieza->id_clase);
                //Verificar que la clase no se haya agregado previamente
                if (!array_key_exists($clase->nombre, $arrayOperadores[$operador->matricula]["clases"])) {
                    $arrayOperadores[$operador->matricula]["clases"][$clase->nombre]["pedido"] = $clase->pedido;
                    $arrayOperadores[$operador->matricula]["clases"][$clase->nombre]["procesos"] = $this->asignarProcesosOperador($clase->id, $operador->matricula);
                }
            }
        }
    }
    public function asignarProcesosOperador($idClase, $operador)
    {
        //Solo retorar las columnas que tengan un valor diferente a 0
        $procesosClase_Operador = Pieza::where("id_clase", $idClase)
            ->where("id_operador", $operador)
            ->distinct()
            ->pluck("proceso")
            ->toArray();
        return $procesosClase_Operador;
    }
    public function show(Request $request)
    {
        //Obtener la ot conforme a su ID
        $ot = Orden_trabajo::find($request->ot);
        $moldura = Moldura::find($ot->id_moldura);

        //Obtener la clase conforme a su ID
        $clase = Clase::where("id_ot", $request->ot)->where("nombre", $request->clases)->first();

        //Obtener el nombre del operador
        $operador = User::where("matricula", $request->operadores)->first();
        $operador = $operador->nombre . " " . $operador->a_paterno . " " . $operador->a_materno;

        //Obtener las piezas conforme a la OT, la clase, el operador y el proceso
        $piezas = Pieza::where("id_ot", $request->ot)->where("id_clase", $clase->id)->where("id_operador", $request->operadores)->where("proceso", $request->procesos)->get();

        $operadores = $this->obtenerInformacionPiezas($piezas);
        //Guardar los datos buscados de los filtros en un arreglo
        $filtros = [
            "ot" => $request->ot,
            "moldura" => $moldura->nombre,
            "clase" => $request->clases,
            "pedido" => $clase->pedido,
            "operador" => $operador,
            "proceso" => $request->procesos
        ];
        return $this->index($operadores, $filtros);
    }
    public function obtenerInformacionPiezas($piezas)
    {
        $operadores = [];
        foreach ($piezas as $pieza) {
            //Se otiene el nombre del operador
            $operadorName = User::where("matricula", $pieza->id_operador)->first();
            $operadorName = $operadorName->nombre . " " . $operadorName->a_paterno . " " . $operadorName->a_materno;

            //Se obtiene la fecha en la que se trabajo la pieza
            $fecha = $pieza->created_at;
            $fechaMeta = new DateTime($fecha);
            $fechaMeta = $fechaMeta->format("Y-m-d");
            $fecha = $fecha->format("d/m/Y");
            if (!array_key_exists($operadorName, $operadores)) {
                $operadores[$operadorName] = [];
                $meta = $this->obtenerMeta($pieza->id_ot, $pieza->id_operador, $fechaMeta, $pieza->proceso);
                $operadores[$operadorName][$fecha] = ["Piezas buenas" => 0, "Piezas malas" => 0, "meta" => $meta, "Productividad" => 0];
            } else if (!array_key_exists($fecha, $operadores[$operadorName])) {
                $operadores[$operadorName][$fecha] = ["Piezas buenas" => 0, "Piezas malas" => 0];
                $meta = $this->obtenerMeta($pieza->id_ot, $pieza->id_operador, $fechaMeta, $pieza->proceso);
                $operadores[$operadorName][$fecha] = ["Piezas buenas" => 0, "Piezas malas" => 0, "meta" => $meta, "Productividad" => 0];
            }

            $cantidad = ($pieza->proceso == "Cepillado" || $pieza->proceso == "Desbaste Exterior" || $pieza->proceso == "Revision laterales") ? 0.5 : 1;
            if ($pieza->error != "Ninguno") {
                if ($pieza->liberacion == 1) {
                    $operadores[$operadorName][$fecha]["Piezas buenas"] += $cantidad;
                } else {
                    $operadores[$operadorName][$fecha]["Piezas malas"] += $cantidad;
                }
            } else {
                if ($pieza->liberacion != 2) {
                    $operadores[$operadorName][$fecha]["Piezas buenas"] += $cantidad;
                } else {
                    $operadores[$operadorName][$fecha]["Piezas malas"] += $cantidad;
                }
            }
            $operadores[$operadorName][$fecha]["Productividad"] = ($operadores[$operadorName][$fecha]["Piezas buenas"] / $operadores[$operadorName][$fecha]["meta"]) * 100;
            $operadores[$operadorName][$fecha]["Productividad"] = round($operadores[$operadorName][$fecha]["Productividad"], 2);
        }
        // Iprimir los datos del arreglo
        // foreach ($operadores as $operador => $fechas) {
        //     echo $operador . "<br>";
        //     foreach ($fechas as $fecha => $piezas) {
        //         echo $fecha . "<br>";
        //         echo "Piezas buenas: " . $piezas["Piezas buenas"] . "<br>";
        //         echo "Piezas malas: " . $piezas["Piezas malas"] . "<br>";
        //         echo "Meta: " . $piezas["meta"] . "<br>";
        //         echo "<br><br>";
        //     }
        // }
        // die();
        return $operadores;
    }
    public function renombrarProceso($proceso)
    {
        $procesoName = match ($proceso) {
            "Cepillado" => ["cepillado", null],
            "Desbaste Exterior" => ["desbaste", null],
            "Revision Laterales" => ["revLaterales", null],
            "Primera Operacion Soldadura" => ["primeraOpeSoldadura", null],
            "Barreno Maniobra" => ["barrenoManiobra", null],
            "Segunda Operacion Soldadura" => ["segundaOpeSoldadura", null],
            "Soldadura" => ["soldadura", null],
            "Soldadura PTA" => ["soldaduraPTA", null],
            "Rectificado" => ["rectificado", null],
            "Asentado" => ["asentado", null],
            "Revision Calificado" => ["revCalificado", null],
            "Acabado Bombillo" => ["acabadoBombillo", null],
            "Acabado Molde" => ["acabadoMolde", null],
            "Cavidades" => "cavidades",
            "Barreno Profundidad" => ["barrenoProfundidad", null],
            "Copiado" => ["copiado", null],
            "Off Set" => ["offSet", null],
            "Palomas" => ["palomas", null],
            "Rebajes" => ["rebajes", null],
            "Grabado" => ["grabado", null],
            "Operacion Equipo_1" => ["pysOpeSoldadura", 1],
            "Operacion Equipo_2" => ["pysOpeSoldadura", 2],
            "embudoCm" => ["EmbudoCM", null],
        };
        return $procesoName;
    }
    public function obtenerMeta($ot, $operador, $fecha, $proceso)
    {
        $proceso = $this->renombrarProceso($proceso);
        $meta = Metas::where("id_ot", $ot)->where("id_usuario", $operador)->where("fecha", $fecha)->where("proceso", $proceso[0])->where("id_proceso", $proceso[1])->first();

        return $meta->meta;
    }
}
