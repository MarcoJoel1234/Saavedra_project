<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <header>
        @auth
        <button class="abrir-menu" id="abrir"><img class="icono" src="{{asset('images/icono.png')}}"></button>
        <nav class="nav" id="nav">
            <button class="cerrar-menu" id="cerrar"><img class="icono" src="{{asset('images/icono.png')}}"></button>
            <ul class="nav-list">
                <li><a href="{{route('home')}}">Inicio</a></li>
                <li><a href="{{route ('cepillado', ['error' => 0])}}">Cepillado</a></li>
                <li><a href="{{route ('desbasteExterior', ['error' => 0])}}">Desbaste exterior</a></li>
                <li><a href="{{route ('revisionLaterales', ['error' => 0])}}">Revisión de laterales</a></li>
                <li><a href="{{route ('primeraOpeSoldadura', ['error' => 0])}}">1ra Operación Soldadura </a></li>
                <li><a href="{{route ('barrenoManiobra', ['error' => 0])}}">Barreno de maniobra</a></li>
                <li><a href="{{route ('segundaOpeSoldadura', ['error' => 0])}}">2da Operación Soldadura</a></li>
                <li><a href="{{route ('soldadura', ['error' => 0])}}">Soldadura</a></li>
                <li><a href="{{route ('soldaduraPTA', ['error' => 0])}}">Soldadura PTA</a></li>
                <li><a href="{{route ('rectificado', ['error' => 0])}}">Reporte diario de rectificado</a></li>
                <li><a href="{{route ('asentado', ['error' => 0])}}">Reporte diario de asentado</a></li>
                <li><a href="{{route ('calificado', ['error' => 0])}}">Revisión calificado</a></li>
                <li><a href="{{route ('acabadoBombillo', ['error' => 0])}}">Revisión acabados bombillo</a></li>
                <li><a href="{{route ('acabadoMolde', ['error' => 0])}}">Revisión acabados molde</a></li>
                <li><a href="{{route ('barrenoProfundidad', ['error' => 0])}}">Barreno de profundidad</a></li>
                <li><a href="{{route ('cavidades', ['error' => 0])}}">Reporte diario de Cavidades</a></li>
                <li><a href="{{route ('copiado', ['error' => 0])}}">Reporte de Copiado</a></li>
                <li><a href="{{route ('offSet', ['error' => 0])}}">Ranura Off-Set</a></li>
                <li><a href="{{route ('palomas', ['error' => 0])}}">Reporte de Palomas</a></li>
                <li><a href="{{route ('rebajes', ['error' => 0])}}">Rebajes</a></li>
                <li><a href="{{route ('1y2OpeSoldadura', ['error' => 0])}}">1ra y 2da Operación Equipo</a></li>
                <li><a href="{{route ('embudoCM', ['error' => 0])}}">Embudo C.M</a></li>
                <ul>
                    <a class="btn1" href="{{ route('logout') }}">Cerrar sesión</a>
                </ul>
            </ul>
        </nav>
        <p class="titulo" id="t-principal">MAQUINADOS Y FUSIONES MEXICANAS S DE R.L DE C.V.</p>
        <img src="{!! asset('images/lg_saavedra.png') !!}" alt="logo" class="logo">
        @endauth
    </header>
    @yield('content')
</body>

</html>