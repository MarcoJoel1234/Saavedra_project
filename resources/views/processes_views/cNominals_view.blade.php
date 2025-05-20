@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@vite(['resources/css/processes_views/cNominals_view.css', 'resources/js/processes_views/cNominals_view.js', 'resorces/js/processes_views/Proceso.js'])


@section('head')
<title>C.Nominales y tolerancias</title>
@endsection

@section('background-body', 'images/fondoLogin.jpg')
@section('content')
<div class="container-select">
    <h1>Cotas Nominales y Tolerancias</h1>
    <label for="select">Selecciona la orden de trabajo</label>
    <select class="form-control"></select>
</div>
@endsection