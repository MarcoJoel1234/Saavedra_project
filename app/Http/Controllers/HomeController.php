<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(){
        try {
            $perfil = auth()->user()->perfil;
        } catch (\Exception $e) {
            // Manejar la excepción aquí
            $perfil = null;
        }
        if($perfil !== null){
            if(auth()->user()->perfil == 1){
                return view ('home.indexAdmin');
            }else if(auth()->user()->perfil == 2){
                return view ('home.index');
            }else if(auth()->user()->perfil == 3){
                return view ('home.indexMaster');
            }else{
                return view ('home.indexQuality');
            }
        }else{
            return view ('auth.login');
        }
        
    }
}
