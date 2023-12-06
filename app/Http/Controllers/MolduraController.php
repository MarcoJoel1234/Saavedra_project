<?php

namespace App\Http\Controllers;

use App\Models\Moldura;
use Illuminate\Http\Request;

class MolduraController extends Controller
{
    public function create()
    {
        return view('processesAdmin.registrarMoldura');
    }
    public function store(Request $request){
        $request->validate([
            'nombre' => 'required|unique:molduras',
        ],
        [
            'nombre.required' => 'El campo nombre es obligatorio',
            'nombre.unique' => 'El nombre de la moldura ya existe.',
        ]);
        $moldura = Moldura::create($request->all());
        return redirect()->to('registerMoldura')->with('success', 'Moldura registrada correctamente.');
    }
    public function show(Request $request){
            $moldura = Moldura::where('nombre', $request->nombre )->first();
            $moldura->nombre;
            // return view('processesAdmin.buscarMoldura', ['moldura' => $moldura]);
    }

    public function destroy(Request $request){
        $moldura = Moldura::find($request->id);
        $moldura->delete();
        return redirect()->to('searchMoldura')->with('success', 'Moldura eliminada correctamente.');
    }
}
