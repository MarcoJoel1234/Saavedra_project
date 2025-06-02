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
<div class="intro">
    <div class="intro-text">
        @auth
        <h2 class="welcome-title">{{ $welcomeT }} {{ auth()->user()->nombre }}
            {{ auth()->user()->a_paterno }} {{ auth()->user()->a_materno }}
        </h2>
        <p class="welcome-text">{{ $objectiveT }}</p>
        @endauth
    </div>
    <img class="intro-img" src="{{ asset('images/img-index.png') }}" alt="..." />
</div>
@endsection