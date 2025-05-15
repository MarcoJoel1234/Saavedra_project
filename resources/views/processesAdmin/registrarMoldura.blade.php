@extends('layouts.appAdmin')
@section('content')

<head>
    <title>Registrar molduras</title>

    <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
    @vite('resources/css/recoverPassword.css')
</head>
<body background="{{ asset('images/fondoLogin.jpg') }}">
    <div class="wrapper bg-white">
        <div class="h2 text-center">
            <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
        </div>
        <div class="h4 text-muted text-center pt-2">¡Registra nuevas molduras!</div>
        <form action="{{route('registerMolduras')}}" method="post" class="pt-3">
            @csrf
            @include('layouts.partials.messages')
            <div class="form-group py-2">
                <div class="input-field"> <span class="far fa-user p-2"></span> <input name="nombre" type="text" placeholder="Ingresa el nombre de la moldura" required class="" maxlength="50"></div>
            </div>
            <div class="d-flex align-items-start"></div>
            <div class="text-center pt-3 text-muted">
                <button class="btn btn-block text-center my-3">Guardar moldura</button>
            </div>
        </form>
    </div>
</body>
@endsection