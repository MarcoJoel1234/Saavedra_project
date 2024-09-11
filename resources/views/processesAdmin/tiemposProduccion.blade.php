@extends($layout)
@section('content')

    <head>
        <title>Modificar tiempos</title>
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
