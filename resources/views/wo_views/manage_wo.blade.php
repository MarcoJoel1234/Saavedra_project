@extends('layouts.appMenu')

@section('head')
    <title>Registrar Orden de trabajo</title>
    <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
    @vite(['resources/css/wo_views/manage_wo.css', 'resources/js/wo_views/manage_wo.js'])
@endsection

@section('background-body', 'images/fondoLogin.jpg')

@section('content')
    <!--Obtener el valor del perfil del usuario en sesion-->
    
    <!--Formulario para seleccionar o crear una orden de trabajo-->
    <form action="{{ route('storeWO') }}" method="POST" class="pt-3">
        @csrf
        <input type="hidden" value="{{ auth()->user()->perfil }}" name="profile" />
        <div class="wrapper bg-white">
            <div class="h2 text-center">
                <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
            </div>
            @include('layouts.partials.messages') <!--Mensajes de error o exito en la creacion o seleccion de una orden de trabajo-->
            <label class="sub-title">¡Agrega o selecciona una orden de trabajo!</label>
            <div class="div-bttns"></div>
        </div>
    </form>
    <script>
        window.workOrders = @json($workOrders);
        window.moldings = @json($moldings);
    </script>
@endsection