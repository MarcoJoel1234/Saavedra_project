@extends('layouts.appMenu')

<!--Estilos y codigo JS-->


@section('head')
<title>C.Nominales y tolerancias</title>
@vite(['resources/css/processes_views/cNominals_view.css', 'resources/js/processes_views/cNominals_view.js'])
@endsection

@section('background-body', 'images/fondoLogin.jpg')
@section('content')
<div class="wrapper">
    <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
    <h1>Cotas Nominales y Tolerancias</h1>
    <div class="row-principal"></div>
    <div class="row"></div>
</div>
<form action="#" method="post" class="form-search">
    @csrf
    <div class="scrollable-table">
        <input type="submit" class="btn-submit visible" value="Guardar" />
    </div>
</form>
<script>
    window.workOrders = @json($workOrders);
</script>
@endsection