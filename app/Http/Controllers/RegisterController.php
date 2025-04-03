<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    //
    protected $userController;
    public function __construct(){
        $this->userController = new UserController;
    }
    public function show(){
        $layout = auth()->user() && ($this->userController->getLayout() == "layouts.appMaster" || $this->userController->getLayout() == "layouts.appAdmin") ? $this->userController->getLayout() : 'layouts.defaultLayout';
        return view("processesMaster.register", compact("layout"));
    }
    public function register(RegisterRequest $request){
        $user = User::create($request->validated());
        return redirect()->to('register')->with('success', 'Usuario registrado correctamente');
    }
}