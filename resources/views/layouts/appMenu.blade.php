<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/layouts/appMenu.css', 'resources/js/layouts/appMenu.js'])
    @yield('head')
</head>

<body background=@yield('background-body')>
    <header>
        @auth
        <button class="open-menu" id="open">
            <img class="icon" src="{{ asset('images/icono.png') }}">
        </button>
        <nav class="nav" id="nav">
            <button class="close-menu" id="close">
                <img class="icon" src="{{ asset('images/icono.png') }}">
            </button>
            <ul class="nav-list">
                <ul>
                    <a class="btn1" href="{{ route('logout') }}">Cerrar sesión</a>
                </ul>
            </ul>
            <input type="hidden" value="{{ auth()->user()->perfil }}" id="profile">
        </nav>
        <p class="text-header" id="t-principal">MAQUINADOS Y FUSIONES MEXICANAS S. DE R.L DE C.V.</p>
        <img src="{!! asset('images/lg_saavedra.png') !!}" alt="logo" class="logo">
        @endauth
    </header>
    @yield('content')
</body>

<!--Creacion de rutas de laravel para pasarlas a JS-->
<script>
    window.routes = {
        home: @json(route('home')),
        createMolding: @json(route('createMolding')),
        manageWO: @json(route('manageWO')),
        users: @json(route('users')), // PENDING
        createUser: @json(route('createUser')),
        recoverPassword: @json(route('recoverPassword')),
        cNominals: @json(route('cNominals')),

        vistaPiezas: @json(route('vistaPiezas')),
        vistaPzasGenerales: @json(route('vistaPzasGenerales')),
        vistaOTLiberar: @json(route('vistaOTLiberar')),
        mostrarTiempos: @json(route('mostrarTiempos')),
        datosProduccion: @json(route('datosProduccion')),
        cepillado: @json(route('cepillado', ['error' => 0])),
        desbasteExterior: @json(route('desbasteExterior', ['error' => 0])),
        revisionLaterales: @json(route('revisionLaterales', ['error' => 0])),
        primeraOpeSoldadura: @json(route('primeraOpeSoldadura', ['error' => 0])),
        barrenoManiobra: @json(route('barrenoManiobra', ['error' => 0])),
        segundaOpeSoldadura: @json(route('segundaOpeSoldadura', ['error' => 0])),
        soldadura: @json(route('soldadura', ['error' => 0])),
        soldaduraPTA: @json(route('soldaduraPTA', ['error' => 0])),
        rectificado: @json(route('rectificado', ['error' => 0])),
        asentado: @json(route('asentado', ['error' => 0])),
        calificado: @json(route('calificado', ['error' => 0])),
        acabadoBombillo: @json(route('acabadoBombillo', ['error' => 0])),
        acabadoMolde: @json(route('acabadoMolde', ['error' => 0])),
        barrenoProfundidad: @json(route('barrenoProfundidad', ['error' => 0])),
        cavidades: @json(route('cavidades', ['error' => 0])),
        copiado: @json(route('copiado', ['error' => 0])),
        offSet: @json(route('offSet', ['error' => 0])),
        palomas: @json(route('palomas', ['error' => 0])),
        rebajes: @json(route('rebajes', ['error' => 0])),
        operacionEquipo: @json(route('1y2OpeSoldadura', ['error' => 0])),
        embudoCM: @json(route('embudoCM', ['error' => 0]))
    };
</script>

</html>