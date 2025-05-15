@extends($layout)
@section('content')
<head>
    <title>Registrarse</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS v5.2.1 -->
    <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
    <!-- Link estilos de CSS -->
    @vite(['resources/css/login.css'])
    @section('title', "Registrar usuario")
</head>

<body background="{{ asset('images/fondoLogin.jpg') }}">
    <!-- Section: Design Block -->
    <section class="text-center text-lg-start">
        <!-- Jumbotron -->
        <div class="container py-4">
            <div class="row g-0 align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="card cascading-right" style="background: hsla(0, 0%, 100%, 0.55); backdrop-filter: blur(30px);">
                        <div class="card-body p-5 shadow-5 text-center">
                            <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
                            <h2 class="fw-bold mb-4">REGISTRATE</h2>
                            <form action="{{route('registerUser')}}" method="post">
                                @csrf
                                @include('layouts.partials.messages')
                                <!-- 2 column grid layout with text inputs for the first and last names -->
                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <div class="form-outline">
                                            <input type="text" id="form3Example1" class="form-control" name="nombre" required />
                                            <label class="form-label" for="form3Example1">Nombre (s)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-outline">
                                            <input type="text" id="form3Example2" class="form-control" name="a_paterno" required />
                                            <label class="form-label" for="form3Example2">Apellido Paterno</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-outline">
                                            <input type="text" id="form3Example3" class="form-control" name="a_materno" required />
                                            <label class="form-label" for="form3Example3">Apellido Materno</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <!-- Email input -->
                                    <div class="col-md-6 form-outline mb-2">
                                        <input type="text" id="form3Example3" class="form-control" maxlength="7" minlength="4" name="matricula" required />
                                        <label class="form-label" for="form3Example3">Matrícula</label>
                                    </div>

                                    <!-- Password input -->
                                    <div class="col-md-6 form-outline mb-2">
                                        <input type="password" id="form3Example4" class="form-control" maxlength="12" minlength="8" name="contrasena" required />
                                        <label class="form-label" for="form3Example4">Contraseña</label>
                                    </div>
                                </div>

                                <div class="form-floating">
                                    <select class="form-select" id="floatingSelect" name="perfil">
                                        <option value="1">Administrador</option>
                                        <option value="2">Operador</option>
                                        <option value="3">Maestro</option>
                                        <option value="4">Calidad</option>
                                        <option value="5">Almacen</option>
                                    </select>
                                    <label for="floatingSelect">Tipo de usuario</label>
                                </div>

                                <!-- Submit button -->
                                <button type="submit" class="custom-btn">
                                    Registrarse
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-6 mb-lg-0">
                    <img src="{{ asset('images/img-login.png') }}" class="w-100 rounded-4 shadow-10" alt="" />
                </div>
            </div>
        </div>
        <!-- Jumbotron -->
    </section>
    <!-- Section: Design Block -->

    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js" integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
    </script>
</body>
@endsection