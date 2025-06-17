@extends('layouts.appMenu')

@section('head')
<title>Orden de trabajo</title>
<script>
    //Rutas de imagenes
    window.deleteImgUrl = "{{ asset('images/delete.png') }}";
    window.cerrarImgUrl = "{{ asset('images/cerrar.png') }}";
</script>
@vite(['resources/css/wo_views/show_wo.css', 'resources/js/wo_views/show_wo.js'])
@endsection

@section('content')
@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

<form action="{{ route('saveClass') }}" method="POST" id="form" class="container-form pt-3">
    @csrf
    <!--Primera parte del formulario-->
    <input type="hidden" name="workOrder" value="{{ $workOrder->id }}">
    <input type="hidden" name="molding" value="{{ $molding->id }}">
    <input type="hidden" name="idClass" id="idClass">
    <div class="main-layout">
        <div class="wrapper">
            <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
            <h3>Información de la orden de trabajo</h3>
            @include('layouts.partials.messages')

            <!--Div en donde se muetran los inputs del formulario-->
            <div class="div-rows">
                <!--Los campos y lase insertan atraves del archivo JavaScript vinculado-->
            </div>
        </div>

        <!--Segunda parte del formulario-->
        <div class="div-boxes" id="casillas">
            <h3>Selecciona los procesos</h3>
            <div class="sections">
                <!--Se inserta el algoritmos para generar las casillas atraves de JavaScript-->
            </div>
        </div>
    </div>
</form>
<div class="div-btns">
    <button type="submit" class="btn-addClass btn" form="form">Guardar</button>
</div>
<script>
    window.workOrder = @json($workOrder);
    window.molding = @json($molding);
    window.classes = @json($classes);
    window.profile = @json(auth()->user()->perfil);
    window.processes = @json($processes);
</script>
@endsection