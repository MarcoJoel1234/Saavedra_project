@extends($layout) 

@section('content')

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestión de usuarios</title>
        @vite(['resources/css/app.css', 'resources/css/viewUsers.css', 'resources/js/viewUsers.js'])
        <title>@yield('title')</title>
    </head>
    <body background="{{ asset('images/fondoLogin.jpg') }}">
        <div class="container1">
            <!-- Buscador -->
            <div class="search-container">
                <h1>Gestión de usuarios</h1>
                <div>
                    <input type="text" class="search-input" placeholder="Buscar por matrícula o nombre">
                    <button class="btn">Buscar</button>
                 </div>
            </div>
            <!-- Tabla con scroll -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>
                                <select class="role-select">
                                    <option value="todos">Rol</option>
                                    <option value="admin">Administrador</option>
                                    <option value="operador">Operador</option>
                                    <option value="almacen">Almacen</option>
                                    <option value="master">Master</option>
                                    <option value="calidad">Calidad</option>
                                </select>
                            </th>
                            <th>Matrícula</th>
                            <th>Usuario</th>
                            <th>Fecha de registro</th>
                            <th>Fecha de alta/baja</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Administrador</td>
                        <td>11111</td>
                        <td>Abraham Saavedra Ricalde</td>
                        <td>2025-01-01</td>
                        <td>2025-01-01</td>
                    </tr>
                    <tr>
                        <td>Administrador</td>
                        <td>4171</td>
                        <td>Marco Joel Angel Velasco</td>
                        <td>2025-01-02</td>
                        <td>2025-01-01</td>
                    </tr>
                    <tr>
                        <td>Operador</td>
                        <td>4070</td>
                        <td>Natali Joselin Alemán Perez</td>
                        <td>2025-01-03</td>
                        <td>2025-01-01</td>
                    </tr>
                    <tr>
                        <td>Calidad</td>
                        <td>11111</td>
                        <td>Yordi Emanuel Resendiz Ramirez</td>
                        <td>2025-01-01</td>
                        <td>2025-01-01</td>
                    </tr>
                    <tr>
                        <td>Administrador</td>
                        <td>4171</td>
                        <td>Marco Joel Angel Velasco</td>
                        <td>2025-01-02</td>
                        <td>2025-01-01</td>
                    </tr>   
                    <tr>
                        <td>Operador</td>
                        <td>4070</td>
                        <td>Natali Joselin Alemán Perez</td>
                        <td>2025-01-03</td>
                        <td>2025-01-01</td>
                    </tr>
                    <tr>
                        <td>Administrador</td>
                        <td>11111</td>
                        <td>Abraham Saavedra Ricalde</td>
                        <td>2025-01-01</td>
                        <td>2025-01-01</td>
                    </tr>
                    <tr>
                        <td>Administrador</td>
                        <td>4171</td>
                        <td>Marco Joel Angel Velasco</td>
                        <td>2025-01-02</td>
                        <td>2025-01-01</td>
                    </tr>
                    <tr>
                        <td>Operador</td>
                        <td>4070</td>
                        <td>Natali Joselin Alemán Perez</td>
                        <td>2025-01-03</td>
                        <td>2025-01-01</td>
                    </tr>
                    <tr>
                        <td>Administrador</td>
                        <td>11111</td>
                        <td>Abraham Saavedra Ricalde</td>
                        <td>2025-01-01</td>
                        <td>2025-01-01</td>
                    </tr>
                    <tr>
                        <td>Administrador</td>
                        <td>4171</td>
                        <td>Marco Joel Angel Velasco</td>
                        <td>2025-01-02</td>
                        <td>2025-01-01</td>
                    </tr>
                    <tr>
                        <td>Operador</td>
                        <td>4070</td>
                        <td>Natali Joselin Alemán Perez</td>
                        <td>2025-01-03</td>
                        <td>2025-01-01</td>
                    </tr>
                    <tr>
                        <td>Administrador</td>
                        <td>11111</td>
                        <td>Abraham Saavedra Ricalde</td>
                        <td>2025-01-01</td>
                        <td>2025-01-01</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <!-- Menú Contextual -->
            <ul id="context-menu" class="context-menu">
                <li class="menu-item"><a href="#">Ver detalles</a></li>
                <li class="menu-item"><a href="#">Editar</a></li>
                <li class="menu-item eliminar-option"><a href="#">Eliminar</a></li>
            </ul>
        </div>
        <script src="viewUsers.js"></script>
    </body>
@endsection
