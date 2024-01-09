<?php

namespace App\Http\Controllers;

use App\Models\Maquinas;
use App\Models\Metas;
use App\Models\Pza_cepillado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LogoutController extends Controller
{
    public function logout(){
        $metasMaquina = Maquinas::all();
        foreach ($metasMaquina as $metaMaquina) {
            $meta = Metas::find($metaMaquina->id_meta);
            if($meta->id_usuario == Auth::user()->matricula){
                $metaMaquina->delete();
            }
        }
        Session::flush();
        Auth::logout();
        return redirect()->to('login')->with('success', 'Cerraste sesión correctamente.');
    }
}
