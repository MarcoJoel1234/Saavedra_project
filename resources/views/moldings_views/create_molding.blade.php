@extends('layouts.appMenu')

@section('head')
    <title>Registrar molduras</title>
    <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
    @vite('resources/css/moldings_views/create_molding.css')
@endsection

@section('content')
    @section('background-body', 'images/fondoLogin.jpg') <!--Body background Image-->
    <div class="wrapper bg-white">
        <div class="h2 text-center">
            <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
        </div>
        <div class="h4 text-muted text-center pt-2">¡Crea nuevas molduras!</div>
        <form action="{{ route('storeMolding') }}" method="post" class="pt-3">
            @csrf
            @include('layouts.partials.messages')
            <div class="form-group py-2">
                <div class="input-field"> <span class="far fa-user p-2"></span> <input name="nombre" type="text"
                    placeholder="Ingresa el nombre de la moldura" class="" maxlength="50"></div>
            </div>
            <div class="d-flex align-items-start"></div>
            <div class="text-center pt-3 text-muted">
                <button class="btn btn-block text-center my-3">Guardar moldura</button>
            </div>
        </form>
    </div>
@endsection