@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@section('head')
<title>Producción</title>
@vite(['resources/css/processes_views/processProduction.css', 'resources/js/processes_views/processProduction.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/hola.jpg") . '")')
@section('content')
<div class="container-form">
    <!--Formulario en donde se guardara la meta de cepillado-->
    <form action="{{route('selectProcess')}}" method="post">
        @csrf
        <div class="wrapper">
            <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
            <h1>Elegir orden de trabajo y operación</h1>
            @include('layouts.partials.messages')
            <div class="form-grid">

            </div>
        </div>
    </form>
</div>
<script>
    window.workOrders = @json($workOrders);
</script>
@endsection