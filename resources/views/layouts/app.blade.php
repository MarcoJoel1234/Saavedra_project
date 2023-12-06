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
            <h2 class="label-menu">Menú.</h2>
            <ul class="nav-list">
                <li><a href="home">Inicio</a></li>
                <li><a href="{{route ('cepillado')}}">Cepillado</a></li>
                <li><a href="{{route ('desbasteExterior')}}">Desbaste Exterior</a></li>
                <li><a href="{{route ('revisionLaterales')}}">Revisión de laterales</a></li>
                <li><a href="{{route ('primeraOpeSoldadura')}}">1ra Operación Soldadura</a></li>
                <li><a href="{{route ('segundaOpeSoldadura')}}">2da Operación Soldadura</a></li>
                <li><a href="{{route ('1y2OpeSoldadura')}}">1ra y 2da operacion soldadura</a></li>
                <li><a href="#">Barreno de maniobra.</a></li>
                <li><a href="#">Reporte diario de soldaduras.</a></li>
                <li><a href="#">Soldadura PTA.</a></li>
                <li><a href="#">Reporte diario de rectificado.</a></li>
                <li><a href="#">Reporte diario de asentado.</a></li>
                <li><a href="#">Revisión calificado.</a></li>
                <li><a href="#">Revisión Acabados Bombillo.</a></li>
                <li><a href="#">Revisión Acabados Molde.</a></li>
                <li><a href="#">Reporte diario de cavidades.</a>></li>
                <li><a href="#">Barreno para platos.</a></li>
                <li><a href="#">Maquinado embudos.</a></li>
                <li><a href="#">Barreno de Profundidad.</a></li>
                <li><a href="#">Reporte de copiado.</a></li>
                <li><a href="#">Ranura OffSet.</a></li>
                <li><a href="#">Grabado.</a></li>
                <li><a href="#">Palomas.</a></li>
                <li><a href="#">Rebajes al centro.</a></li>
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