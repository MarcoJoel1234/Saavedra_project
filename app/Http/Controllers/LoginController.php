<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }
    public function login(LoginRequest $request)
    {
        $user = User::where('matricula', $request->matricula)->first();
        if (!$user ||!Hash::check($request->contrasena, $user->contrasena)) {
            return redirect()->to('/login')->withErrors('Matricula y/o contraseña incorrecta');
        }
        Auth::login($user);
        return $this->authenticated($request, $user);
    }
    public function authenticated(Request $request, $user)
    {
        return redirect()->route('home');
    }
}
