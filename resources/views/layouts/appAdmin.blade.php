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
                <li><a href="{{ route('home') }}">Inicio</a></li>
                <li><a href="{{ route('registerMoldura') }}">Registrar nueva moldura</a></li>
                <li><a href="{{ route('registerOT') }}">Registrar o Modificar O.T</a></li>
                <li><a href="{{ route('recoverPassword') }}">Recuperar contraseña</a></li>
                <li><a href="{{ route('procesos') }}">Editar C.Nominales y Tolerancias</a></li>
                <li><a href="{{ route('vistaPiezas') }}">Piezas en progreso</a></li>
                <li><a href="{{ route('verProcesos') }}">Progreso de O.T</a></li>
                <li><a href="{{ route('vistaPzasGenerales') }}">Reporte de piezas</a></li>
                <li><a href="{{ route('vistaOTLiberar')}}">Liberacion de piezas</a></li>
                <li><a href="{{ route('vistaPzasMaquina') }}">Buscar piezas por máquina</a></li>
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