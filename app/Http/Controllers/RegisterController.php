<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    //
    public function show(){
        return view('processesMaster.register');
    }
    public function register(RegisterRequest $request){
        $user = User::create($request->validated());
        return redirect()->to('register')->with('success', 'Usuario registrado correctamente');
    }
}