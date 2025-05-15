<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getLayout(){
        $profile = auth()->user()->perfil;
        $layout = [
            1 => "layouts.appAdmin",
            2 => "layouts.app",
            3 => "layouts.appMaster",
            4 => "layouts.appQuality",
            5 => "layouts.appAlmacen",
        ];
        return $layout[$profile];
    }
    public function show(){
        $layout = $this->getLayout();
        return view("processesMaster.users", compact("layout"));
    }
}
