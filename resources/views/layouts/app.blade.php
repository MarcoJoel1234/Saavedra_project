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
                <li><a href="home">Inicio</a></li>
                <li><a href="{{route ('cepillado', ['error' => 0])}}">Cepillado</a></li>
                <li><a href="{{route ('desbasteExterior')}}">Desbaste exterior</a></li>
                <li><a href="{{route ('revisionLaterales')}}">Revisión de laterales</a></li>
                <li><a href="{{route ('primeraOpeSoldadura')}}">1ra Operación soldadura</a></li>
                <li><a href="{{route ('barrenoManiobra')}}">Barreno de maniobra</a></li>
                <li><a href="{{route ('segundaOpeSoldadura')}}">2da Operación soldadura</a></li>
                <li><a href="{{route ('soldadura')}}">Soldadura</a></li>
                <li><a href="{{route ('soldaduraPTA')}}">Soldadura PTA</a></li>
                <li><a href="{{route ('rectificado')}}">Reporte diario de rectificado</a></li>
                <li><a href="{{route ('asentado')}}">Reporte diario de asentado</a></li>
                <li><a href="{{route ('calificado')}}">Revisión calificado</a></li>
                <li><a href="{{route ('acabadoBombillo')}}">Revisión acabados bombillo</a></li>
                <li><a href="{{route ('acabadoMolde')}}">Revisión acabados molde</a></li>
                <li><a href="{{route ('cavidades')}}">Reporte diario de Cavidades</a></li>
                <li><a href="{{route ('copiado')}}">Reporte de Copiado</a></li>
                <li><a href="{{route ('offSet')}}">Ranura Off-Set</a></li>
                <li><a href="{{route ('palomas')}}">Reporte de Palomas</a></li>
                <li><a href="{{route ('rebajes')}}">Rebajes</a></li>
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