<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        try {
            $perfil = auth()->user()->perfil;
        } catch (\Exception $e) {
            // Manejar la excepción aquí
            $perfil = null;
        }

        if ($perfil !== null) {
            $vista = [
                1 => 'home.indexAdmin',
                2=> 'home.index',
                3=> 'home.indexMaster',
                4=> 'home.indexQuality',
                5=> 'home.indexAlmacen',
            ];
            return view($vista[auth()->user()->perfil]);
        }else{
            return view('auth.login');
        }
    }
}
