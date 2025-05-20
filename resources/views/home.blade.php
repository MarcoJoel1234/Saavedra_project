@extends('layouts.appMenu')

@section('head')
    <title>Inicio</title>
    <!--Styles-->
    @vite('resources/css/home.css')
@endsection
@section('background-body', asset($backgroundImage))
@section('content')
    <!-- Main content -->
    <div class="filter-blur"></div>
    <div class="container">
        <div class="intro">
            <div class="intro-text">
                @auth
                    <h2 class="section-heading">
                        <span class="section-heading-lower">{{ $welcomeT }} {{ auth()->user()->nombre }}
                            {{ auth()->user()->a_paterno }} {{ auth()->user()->a_materno }}!</span>
                    </h2>
                    <p class="mb-3">{{ $objectiveT }}</p>
                @endauth
                @guest
                    <h2 class="section-heading">
                        <span class="section-heading-lower">¡Hola Usuario!</span>
                    </h2>
                    <p class="mb-3">Para ver el contenido de nuestra página por favor inicia sesión</p><br><br>
                    <a href="login" class="custom-btn" style="text-decoration:none">Iniciar sesión</a>
                @endguest
            </div>
            <img class="intro-img" src="{{ asset('images/img-index.png') }}" alt="..." />
        </div>
    </div>
@endsection