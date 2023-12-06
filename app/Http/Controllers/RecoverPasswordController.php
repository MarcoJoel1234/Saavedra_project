<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecoverPasswordController extends Controller
{
    public function show(){
        return view('auth.recoverPassword');
    }
    public function recover(Request $request){
        $request->validate([
            'matricula' => 'required',
            'nueva_contraseña' => ['required', 'string', 'min:8', 'confirmed']
        ]);
        $user = User::where('matricula', $request->matricula)->first();
        if(!$user){
            return redirect()->to('recoverPassword')->withErrors('Matricula no encontrada.');
        }
        $user->update(['contrasena' => bcrypt($request->nueva_contraseña)]);
        return redirect()->to('recoverPassword')->with('success', 'Contraseña actualizada.');
    }
}
