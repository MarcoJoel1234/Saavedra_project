@extends('layouts.appMenu')

@section('head')
<title>Progreso de piezas</title>
@vite(['resources/css/wo_views/piecesInProgress_view.css', 'resources/js/wo_views/piecesInProgress_view.js'])
@endsection

@section('background-body', 'images/fondoLogin.jpg')

@section('content')
@if(count($wOInProgress) > 0)
@else
<div class="fondo">
    <div class="alerta">
        <!-- Imagen de error dentro del formulario -->
        <img src="{{ asset('images/error.png') }}" alt="Error">
        <label>No hay ordenes de trabajo en proceso.</label>
    </div>
</div>
@endisset
<script>
    window.wOInProgress = @json($wOInProgress);
</script>
@endsection