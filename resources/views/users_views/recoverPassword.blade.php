@extends('layouts.appMenu')
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
    @vite('resources/css/users_views/recoverPassword.css')
</head>

<body background="{{ asset('images/fondoLogin.jpg') }}">
    <div class="wrapper bg-white">
        <div class="h2 text-center">
            <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
        </div>
        <div class="h4 text-muted text-center pt-2">¡Recupera tu cuenta!</div>
        <form action="{{route('recover')}}" method="post" class="pt-3">
            @csrf
            @include('layouts.partials.messages')
            <div class="form-group py-2">
                <div class="input-field"> <span class="far fa-user p-2"></span> <input name="matricula" type="text" placeholder="Ingresa tu matricula" required class=""></div><br>
                <div class="input-field"> <span class="far fa-user p-2"></span> <input name="nueva_contraseña" type="password" placeholder="Ingresa tu nueva contraseña" required maxlength="12" min="8"></div><br>
                <div class="input-field"> <span class="far fa-user p-2"></span> <input name="nueva_contraseña_confirmation" type="password" placeholder="Confirma tu nueva contraseña" required maxlength="12" minlength="8"> </div>
            </div>
            <div class="d-flex align-items-start">
            </div>

            <div class="text-center pt-3 text-muted">
            <button class="btn btn-block text-center my-3">Restaurar contraseña</button>
            </div>
        </form>
    </div>
</body>

</html>