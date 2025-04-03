<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoldingRequest;
use App\Models\Moldura;

class MoldingController extends Controller
{
    public function create()
    {
        return view('moldings_views.create');
    }
    public function store(MoldingRequest $request)
    {
        $moldura = Moldura::create($request->all());
        return redirect()->to('createMolding')->with('success', 'Moldura registrada correctamente.');
    }
}
