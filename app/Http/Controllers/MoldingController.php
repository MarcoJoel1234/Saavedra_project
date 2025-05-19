<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoldingRequest;
use App\Models\Moldura;

class MoldingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function create()
    {
        return view('moldings_views.create_molding');
    }
    public function store(MoldingRequest $request)
    {
        $moldura = Moldura::create($request->all());
        return redirect()->to('createMolding')->with('success', 'Moldura registrada correctamente.');
    }

    // public function destroy(Request $request)
    // {
    //     $moldura = Moldura::find($request->id);
    //     $moldura->delete();
    //     return redirect()->to('searchMoldura')->with('success', 'Moldura eliminada correctamente.');
    // }
}
