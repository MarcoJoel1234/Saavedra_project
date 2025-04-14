@extends('layouts.appMenu')

@section('head')
    <title>Orden de trabajo</title>
    <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
    @vite(['resources/css/wOrder_views/showWO.css', 'resources/js/wOrder_views/showWO.js'])
@endsection

@section('content')

<body @section('background-body', '../images/fondoLogin.jpg' )>
    <div class="container-form">

        <form action="{{ route('saveClass') }}" method="POST" id="form" class="pt-3">
            @csrf

            <!--Primera parte del formulario-->
            <input type="hidden" name="workOrder" value="{{ $workOrder->id }}">
            <input type="hidden" name="molding" value="{{ $molding->id }}">
            <input type="hidden" name="idClass" id="idClass">
            <div class="main-layout">
                <div class="wrapper ">
                    <div class="h2 text-center">
                        <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
                    </div>
                    <div class="h4 text-muted text-center">Información de la orden de trabajo</div>
                    @include('layouts.partials.messages')

                    <!--Div en donde se muetran los inputs del formulario-->
                    <div class="div-rows">
                        <!--Los campos y lase insertan atraves del archivo JavaScript vinculado-->
                    </div>
                </div>

                <!--Segunda parte del formulario-->
                <div class="div-boxes" id="casillas">
                    <div class="h4 text-muted text-center mt-10">Selecciona los procesos</div>
                    <div class="sections">
                        <!--Se inserta el algoritmos para generar las casillas atraves de JavaScript-->
                    </div>
                </div>
            </div>
        </form>
        <div class="div-btns">
            <button type="submit" class="btn-addClass btn" form="form">Guardar</button>
        </div>
    </div>
</body>
<script>
    window.workOrder = @json($workOrder);
    window.molding = @json($molding);
    window.classes = @json($classes);
    window.profile = @json(auth()->user()->perfil);
    window.processes = @json($processes);
</script>
@endsection
