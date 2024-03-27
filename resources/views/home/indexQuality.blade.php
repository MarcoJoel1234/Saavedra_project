@extends('layouts.appQuality')
@section('content')

<head>
@vite('resources/css/index.css')
<title>Home</title>
</head>
<body>
    <div class="filter-blur1">
        <img src="{{ asset('images/calidad.png') }}" alt="..." />
    </div>
    <!-- Contenido principal -->
    <div class="container">
        <div class="intro">
            <div class="intro-text">
                @auth
                <h2 class="section-heading">
                    <span class="section-heading-lower">¡Bienvenido a control de Calidad {{auth()->user()->nombre}}
                        {{auth()->user()->a_paterno}} {{auth()->user()->a_materno}}!</span>
                </h2>
                <p class="mb-3">"En nuestro perfil de calidad, cada milímetro importa. Nos comprometemos a inspeccionar con precisión cada pieza, asegurando medidas exactas y calidad impecable. En la búsqueda constante de la excelencia, nos destacamos por nuestra meticulosidad y compromiso con la perfección."</p>
                @endauth
                @guest
                <h2 class="section-heading">
                    <span class="section-heading-lower">¡Hola Usuario!</span>
                </h2>
                <p class="mb-3">Para ver el contenido de nuestra página por favor inicia sesión</p><br><br>
                <a href="login" class="custom-btn" style="text-decoration:none">Iniciar sesión</a><table>
                </table>
                @endguest
            </div>
            <img class="intro-img" src="{{ asset('images/img-index.png') }}" alt="..." />
        </div>
    </div>
</body>
@endsection