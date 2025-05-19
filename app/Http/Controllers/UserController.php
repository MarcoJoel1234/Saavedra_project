<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;

class UserController extends Controller
{
    // This controller handles user-related actions such as creating, storing, and recovering passwords.
    public function __construct(){
        $this->middleware('auth');
    }
    public function getLayout(){
        $profile = auth()->user()->perfil;
        $layout = [
            1 => "layouts.menu.appAdmin",
            2 => "layouts.menu.appProduction",
            3 => "layouts.menu.appMaster",
            4 => "layouts.menu.appQuality",
            5 => "layouts.menu.appWarehouse",
        ];
        return $layout[$profile];
    }
    public function show(){
        $layout = $this->getLayout();
        return view("processesMaster.users", compact("layout"));
    }
    public function create(){
        $layout = auth()->user() && ($this->getLayout() == "layouts.appMaster" || $this->getLayout() == "layouts.appAdmin") ? $this->getLayout() : 'layouts.defaultLayout';
        return view("users_views.create_user", compact("layout"));
    }
    public function store(CreateUserRequest $request){
        $user = User::create($request->validated());
        return redirect()->route('createUser')->with('success', 'Usuario registrado correctamente');
    }
    public function showRecoverPassword(){
        return view('users_views.recoverPassword');
    }
    public function recoverPassword(HttpRequest $request){
        $request->validate([
            'matricula' => 'required',
            'nueva_contraseña' => ['required', 'string', 'min:8', 'confirmed']
        ]);
        $user = User::where('matricula', $request->matricula)->first();
        if(!$user){
            return redirect()->to('recoverPassword')->withErrors('Matricula no encontrada.');
        }
        $user->update(['contrasena' => bcrypt($request->nueva_contraseña)]);
        return redirect()->route('recoverPassword')->with('success', 'Contraseña actualizada.');
    }
}