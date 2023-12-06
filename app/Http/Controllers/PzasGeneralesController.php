<?php

namespace App\Http\Controllers;

use App\Models\Cepillado;
use App\Models\Clase;
use App\Models\Metas;
use App\Models\Orden_trabajo;
use App\Models\Pieza;
use App\Models\Pza_cepillado;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

use function Symfony\Component\String\b;
//Clase para el control de las piezas generales
class PzasGeneralesController extends Controller
{
    public function show()
    {
        $ot = Orden_trabajo::all();
        return view('processesAdmin.AdminPzas', compact('ot'));
    }
    public function search(Request $request)
    {
        $action = $request->input('action');

        $array = array();
        $otElegida = Orden_trabajo::find($request->ot);
        $clases = Clase::where('id_ot', $otElegida->id)->get();
        $operadores = $this->getOperadores($otElegida->id);
        $maquina = Pieza::where('id_ot', $otElegida->id)->distinct('maquina')->pluck('maquina');
        $proceso = ["Cepillado", "Desbaste Exterior", "Revision Laterales", "Primera Operacion Soldadura", "Segunda Operacion Soldadura"];

        $piezas = $this->buscarPiezas($otElegida, $request->clase, $request->operador, $request->maquina, $request->proceso, $array);
        $array = $piezas[1];
        $piezas = $piezas[0];
        if ($action != 'pdf' || $action == null) {
            return view('processesAdmin.AdminPzas', compact('piezas', 'otElegida', 'clases', 'operadores', 'maquina', 'array', 'proceso'));
        }else{
            $pdf = Pdf::loadView('processesAdmin.pdf', compact('piezas', 'otElegida', 'clases', 'operadores', 'maquina', 'array', 'proceso'));
            return $pdf->download('Reporte de piezas.pdf');
            
        }
    }
    public function buscarPiezas($ot,  $clase, $operador, $maquina, $proceso, $itemElegidos)
    {
        $array = array();
        if ($ot != null) {
            $array = Pieza::where('id_ot', $ot->id)->get();
            $array = $this->saveInArray($array);
            if (($clase != "todos" && isset($clase)) && $array != "[]") {
                $array = $this->buscarElemento($array, 1, $clase);
                $itemElegidos[0] = $clase;
            } else {
                $itemElegidos[0] = "Todos";
            }
            if (($operador != "todos" && isset($operador)) && $array != "[]") {
                $array = $this->buscarElemento($array, 2, $operador);
                $itemElegidos[1] = $operador;
            } else {
                $itemElegidos[1] = "Todos";
            }
            if (($maquina != "todos" && isset($maquina)) && $array != "[]") {
                $array = $this->buscarElemento($array, 3, $maquina);
                $itemElegidos[2] = $maquina;
            } else {
                $itemElegidos[2] = "Todos";
            }
            if (($proceso != "todos" && isset($proceso)) && $array != "[]") {
                $array = $this->buscarElemento($array, 4, $proceso);
                $itemElegidos[3] = $proceso;
            } else {
                $itemElegidos[3] = "Todos";
            }
        }
        return [$array, $itemElegidos];
    }
    public function buscarElemento($arrayP, $posicion, $elemento)
    {
        $array = array();
        for ($i = 0; $i < count($arrayP); $i++) {
            if (strpos($arrayP[$i][$posicion], $elemento) !== false) {
                array_push($array, $arrayP[$i]);
            }
        }
        return $array;
    }
    public function saveInArray($arrayP)
    {
        $array = array();
        $contador = 0;
        foreach ($arrayP as $item) {
            $array[$contador][0] = $item->n_pieza;
            $array[$contador][1] = $this->getNameClase($item->id_clase);
            $array[$contador][2] = $this->getNameOperador($item->id_operador);
            $array[$contador][3] = $item->maquina;
            $array[$contador][4] = $item->proceso;
            $array[$contador][5] = $item->error;
            $contador++;
        }
        return $array;
    }
    public function getOperadores($ot)
    {
        $operadores = Pieza::where('id_ot', $ot)->distinct('id_operador')->pluck('id_operador');
        for ($i = 0; $i < count($operadores); $i++) {
            $operadores[$i] = User::where('matricula', $operadores[$i])->first();
        }
        return $operadores;
    }
    public function getNameOperador($matricula)
    {
        $operador = User::where('matricula', $matricula)->first();
        return $operador->nombre . " " . $operador->a_paterno . " " . $operador->a_materno;
    }
    public function getNameClase($id)
    {
        $clase = Clase::find($id);
        return $clase->nombre . " " . $clase->tamanio;
    }
}




































































































































































