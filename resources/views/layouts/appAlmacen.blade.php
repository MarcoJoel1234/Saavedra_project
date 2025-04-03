<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>@yield('title')</title>
</head>

<body>
    <header>
        @auth
        <button class="abrir-menu" id="abrir"><img class="icono" src="{{asset('images/icono.png')}}"></button>
        <nav class="nav" id="nav">
            <button class="cerrar-menu" id="cerrar"><img class="icono" src="{{asset('images/icono.png')}}"></button>
            <ul class="nav-list">
                <li><a href="{{ route('home') }}">Inicio</a></li>
                <li><a href="{{ route('registerOT') }}">Modificar OT</a></li>
                <ul>
                    <a class="btn1" href="{{ route('logout') }}">Cerrar sesión</a>
                </ul>
            </ul>
        </nav>
        <p class="titulo" id="t-principal">MAQUINADOS Y FUSIONES MEXICANAS S. DE R.L DE C.V.</p>
        <img src="{!! asset('images/lg_saavedra.png') !!}" alt="logo" class="logo">
        @endauth
    </header>
    @yield('content')
</body>

</html>