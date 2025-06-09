@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@section('head')
<title>Tiempos de producción</title>
@vite(['resources/css/processes_views/cNominals_view.css', 'resources/js/processes_views/cNominals_view.js'])
@endsection

@section('background-body', 'images/fondoLogin.jpg')
@section('content')


@extends($layout)
@section('content')

    <head>
        <title>Modificar tiempos</title>
        <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
        @vite(['resources/css/tiemposProduccion.css', 'resources/css/recoverPassword.css'])
    </head>

    <body background="{{ asset('images/fondoLogin.jpg') }}">
        <!--Sección de busqueda-->
        <div class="container">
            <div class="search">
                <img src="{{ asset('/images/lg_saavedra.png') }}" alt="lg-saavedra" class="search-img">
                <h1 class="title">Modificar tiempos de producción</h1>
                <form action="{{ route('verificarProceso') }}" method="post" class="form-search">
                    @csrf
                    @include('layouts.partials.messages')
                    <div class="div-select">
                        @if(isset($clase))
                            <select name="clase" id="clase" class="form-select">
                                <option value="0" {{ $clase == 0 ? 'selected' : '' }}>Selecciona una opción</option>
                                <option value="Bombillo" {{ $clase == 'Bombillo' ? 'selected' : '' }}>Bombillo</option>
                                <option value="Molde" {{ $clase == 'Molde' ? 'selected' : '' }}>Molde</option>
                            </select>
                        @else
                            <select name="clase" id="clase" class="form-select">
                                <option value="0">Selecciona una opción</option>
                                <option value="Bombillo">Bombillo</option>
                                <option value="Molde">Molde</option>
                            </select>
                        @endif
                    </div>
                </form>
            </div>
    
            <!--Sección de tabla de procesos-->
            <form action="{{ route('guardarTiempos') }}" method="post" class="form">
                @csrf
                <div class="tabla-procesos" style="display: none">
                    <script src="{{ asset('js/tiemposProduccion.js') }}"></script>
                    <script>
                        const tiempos = @json($tiempos);
                    </script>
                </div>
            </form>
        </div>
    </body>
@endsection
