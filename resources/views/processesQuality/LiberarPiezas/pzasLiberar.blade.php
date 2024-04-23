@extends($layout)
@section('content')

<head>
    <title>Liberación de piezas</title>
    @vite('resources/css/adminPzas.css')
</head>
@if(!isset($otElegida))
<style>
    form {
        width: 30%;
        height: 30vh;
        display: flex;
        flex-direction: column;
    }

    @media screen and (max-width: 600px) {
        .container {
            width: 100%;
        }

        form {
            overflow: hidden;
            width: 80%;
        }
        .icono-liberar, .icono-rechazar {
            width: 20px; /* Ancho */
            height: 20px; /* Alto */
        }

    }
</style>
@endif
<script>
    var operacion = false;

    function crearTabla(piezas, infoPiezas) { //Crea la tabla de piezas trabajadas en la O.T
        console.log(piezas);
        const table = document.getElementById('table'); 
        const tbody = document.createElement('tbody');
        //Convertir el objeto a un array
        piezas = convertirObjectToArray(piezas);
        for (let i = 0; i < piezas.length; i++) {
            const tr = document.createElement('tr');
            for (let j = 1; j < (piezas[i].length + 1); j++) {
                let td = document.createElement('td');
                if (piezas[i][4] == "Operacion Equipo") {
                    switch(j){
                        case 7:
                            td.textContent = crearFecha(piezas[i][j]);
                            break;
                        case 10:
                            if(!piezas[i][6].includes("Incompleto") && piezas[i][piezas[i].length - 2] != 1){
                                td.appendChild(crearBotonLiberar(infoPiezas, i, piezas));
                            }
                            break;
                        case 11:
                            td.appendChild(crearBotonRechazar(infoPiezas, i));
                            break;
                        case 12:
                            td.appendChild(crearBotonVer(infoPiezas, i, piezas[i][2]));
                            break;
                        default:
                            if(piezas[i][j] != undefined){
                                td.textContent = piezas[i][j];
                            }else{
                                td.textContent = "";
                            }
                            break;
                    }
                    tr.appendChild(td);
                    switch(piezas[i][piezas[i].length - 2]){
                        case 0:
                            if(piezas[i][6].includes("Incompleto")){
                                tr.style.backgroundColor = "#FFFF99";
                            }
                            break;
                        case 1:
                            tr.style.backgroundColor = "#ACF980";
                            break;
                        case 2:
                            tr.style.backgroundColor = "#EC7063";
                            break;
                    }
                } else {
                    if (operacion) {
                        let inputEmpty = false;
                        switch(j){
                            case 5:
                                tr.appendChild(td);
                                let td1 = document.createElement('td');
                                td1.textContent = piezas[i][j];
                                tr.appendChild(td1);
                                inputEmpty = true;
                                break;
                            case 6:
                                td.textContent = crearFecha(piezas[i][j]);
                                break;
                            case 9:
                                if(!piezas[i][5].includes("Incompleto") && piezas[i][piezas[i].length - 2] != 1){
                                    td.appendChild(crearBotonLiberar(infoPiezas, i, piezas));
                                }
                                break;
                            case 10:
                                td.appendChild(crearBotonRechazar(infoPiezas, i));
                                    break;
                            case 11:
                                td.appendChild(crearBotonVer(infoPiezas, i, piezas[i][2]));
                                    break;
                            default:
                                if(piezas[i][j] != undefined){
                                    td.textContent = piezas[i][j];
                                }else{
                                    td.textContent = "";
                                }
                                break;
                        }
                        if(!inputEmpty){
                            tr.appendChild(td);
                        }
                    } else {
                        console.log(piezas[i][8]);
                        switch(j){
                            case 6:
                                td.textContent = crearFecha(piezas[i][j]);
                                break;
                            case 9:
                                if(!piezas[i][5].includes("Incompleto") && piezas[i][piezas[i].length - 2] != 1){
                                    td.appendChild(crearBotonLiberar(infoPiezas, i, piezas));
                                    tr.appendChild(td);
                                }
                                break;
                            case 10:
                                td.appendChild(crearBotonRechazar(infoPiezas, i));
                                break;
                            case 11:
                                td.appendChild(crearBotonVer(infoPiezas, i, piezas[i][2]));
                                break;
                            default:
                                if(piezas[i][j] != undefined){
                                    td.textContent = piezas[i][j];
                                }else{
                                    td.textContent = "";
                                }
                                break;
                        }
                        tr.appendChild(td);
                    }
                    switch(piezas[i][piezas[i].length - 2]){
                        case 0:
                            if(piezas[i][5].includes("Incompleto")){
                                tr.style.backgroundColor = "#FFFF99";
                            }
                            break;
                        case 1:
                            tr.style.backgroundColor = "#ACF980";
                            break;
                        case 2:
                            tr.style.backgroundColor = "#EC7063";
                            break;
                    }
                }
            }
            tbody.appendChild(tr);
        }
        table.appendChild(tbody);
    }

    function convertirObjectToArray(obj) {
        let array = [];
        for(let i = 0; i < obj.length; i++){
            array.push(Object.values(obj[i]));
        }
        return array;
    } 

    function crearFecha(fecha) {
        let cadena = "";
        if(fecha != 'No liberado'){
            let array = fecha.split('T');
            cadena = array[0] + "\n " + array[1].slice(0, 8);    
        }else{
            cadena = fecha;
        }
        return cadena;
    }

    function crearBotonLiberar(infoPiezas, i, piezas) {
        const a = document.createElement('a');
        a.className = "btn-liberar";
        let url = "{{ route('liberar_rechazar', ['pieza' => ':pieza', 'proceso' => ':proceso', 'liberar' => ':liberar', 'buena' => ':buena', 'request' => ':request']) }}";
        url = url.replace(':pieza', infoPiezas[i][0]);
        url = url.replace(':proceso', infoPiezas[i][1]);
        url = url.replace(':liberar', true);
        url = url.replace(':request', this.obtenerRequest());

        if(infoPiezas[i][2] == "Ninguno" && piezas[i][piezas[i].length - 2] != 2 ){
            url = url.replace(':buena', true);
        }else{
            url = url.replace(':buena', false);
        }
        a.href = url;

        const image = document.createElement('img');
        image.src = "{{ asset('images/Liberar.png') }}";
        image.alt = "Liberar";
        image.className = "ver";
        a.appendChild(image);
        return a;
    }
    function crearBotonRechazar(infoPiezas, i) {
        const a = document.createElement('a');
        a.className = "btn-liberar";
        let url = "{{ route('liberar_rechazar', ['pieza' => ':pieza', 'proceso' => ':proceso', 'liberar' => ':liberar', 'buena' => ':buena', 'request' => ':request']) }}";
        url = url.replace(':pieza', infoPiezas[i][0]);
        url = url.replace(':proceso', infoPiezas[i][1]);
        url = url.replace(':liberar', false);
        url = url.replace(':buena', false);
        url = url.replace(':request', this.obtenerRequest());
        a.href = url;

        const image = document.createElement('img');
        image.src = "{{ asset('images/Rechazar.png') }}";
        image.alt = "Rechazar";
        image.className = "ver";
        a.appendChild(image);
        return a;
    }
    function crearBotonVer(infoPiezas, i, usuarios) {
        const a = document.createElement('a');
        a.className = "btn-pza";
        let url = "{{ route('piezaElegida', ['piezas' => ':piezas', 'proceso' => ':proceso', 'perfil' => ':perfil']) }}";
        
        let nPiezas = [];
        for(let j = 0; j < infoPiezas[i][0].length; j++){
            nPiezas.push(infoPiezas[i][0][j]);
        }
        //INFORMACIÓN DE LAS PIEZAS O PIEZA
        url = url.replace(':piezas', nPiezas);
        url = url.replace(':proceso', infoPiezas[i][1]);
        url = url.replace(':perfil', 'quality');
        a.href = url;

        const image = document.createElement('img');
        image.src = "{{ asset('images/ojito.png') }}";
        image.alt = "Ver";
        image.className = "ver";
        a.appendChild(image);
        return a;
    }
    function obtenerRequest(){
        let names = ['ot', 'clase', 'operador', 'maquina', 'proceso', 'error', 'fecha'];
        let request = [];
        for(let i = 0; i < names.length; i++){
            let value = document.getElementsByName(names[i])[0].value;
            request.push(value);
        }
        return request;
    }
</script>

<body background="{{ asset('images/fondoLogin.jpg') }}">
    <div class="container">
        <form action="{{ route('vistaPiezasLiberar') }}" method="post"> 
            @csrf
            @isset($otElegida)
                <!-- FILTROS DE BÚSQUEDA Y RESULTADOS DE PIEZAS EN GENERAL. -->
                <label id="title_ot">Orden de trabajo: {{$otElegida->id}} </label>
                <label id="title_ot">Clase: {{$clase->nombre}} </label>
                <input type="hidden" name="ot" value="{{$otElegida->id}}">
                <input type="hidden" name="clase" value="{{$clase->id}}">

                <!-- Filtros de búsqueda -->
                <select class="filter-select" name="operador">
                    @if (isset($array) && $array[0] != "Todos")
                        <option value="{{$array[0]}}">{{$array[0]}}</option>
                        <option value="todos">Operadores</option>
                        @foreach ($operadores as $operadores)
                            @if (($operadores->nombre . " " . $operadores->a_paterno . " " . $operadores->a_materno) != $array[0])
                                <option value="{{$operadores->nombre}} {{$operadores->a_paterno}} {{$operadores->a_materno}}">{{$operadores->nombre}} {{$operadores->a_paterno}} {{$operadores->a_materno}}</option>
                            @endif
                        @endforeach
                    @else
                        <option value="todos">Operadores</option>
                        @foreach ($operadores as $operadores)
                            <option value="{{$operadores->nombre}} {{$operadores->a_paterno}} {{$operadores->a_materno}}">{{$operadores->nombre}} {{$operadores->a_paterno}} {{$operadores->a_materno}}</option>
                        @endforeach
                    @endif
                </select>

                <select class="filter-select" name="maquina">
                    @if (isset($array) && $array[1] != "Todos")
                        <option value="{{$array[1]}}">{{$array[1]}}</option>
                        <option value="todos">Máquina</option>
                        @foreach ($maquina as $maquina)
                            @if ($maquina != $array[1])
                                <option value="{{ $maquina }}">{{ $maquina }}</option>
                            @endif
                        @endforeach
                    @else
                        <option value="todos">Máquina</option>
                        @foreach ($maquina as $maquina)
                            <option value="{{ $maquina }}">{{ $maquina }}</option>
                        @endforeach
                    @endif
                </select>

                <select class="filter-select" name="proceso">
                    @if (isset($array) && $array[2] != "Todos")
                        <option value="{{$array[2]}}">{{$array[2]}}</option>
                        <option value="todos">Proceso</option>
                        @foreach ($proceso as $proceso)
                            @if ($proceso != $array[2])
                                <option value="{{ $proceso }}">{{ $proceso }}</option>
                            @endif
                        @endforeach
                    @else
                        <option value="todos">Proceso</option>
                        @foreach ($proceso as $proceso)
                            <option value="{{ $proceso }}">{{ $proceso }}</option>
                        @endforeach
                    @endif
                </select>

            <select class="filter-select" name="error">
                @if (isset($array) && $array[3] != "Todos")
                    <option value="{{$array[3]}}">{{$array[3]}}</option>
                    <option value="todos">Error</option>
                    @foreach ($error as $error)
                        @if ($error != $array[3])
                            <option value="{{ $error }}">{{ $error }}</option>
                        @endif
                    @endforeach
                @else
                    <option value="todos">Error</option>
                    @foreach ($error as $error)
                        <option value="{{ $error }}">{{ $error }}</option>
                    @endforeach
                @endif
            </select>

                @if ( isset($array) && $array[4] != "Todos")
                    <label for="title" class="date-label">Fecha:</label>
                    <input type="date" name="fecha" class="filter-select" value="{{$array[4]}}" />
                @else
                    <label for="title" class="date-label">Fecha:</label>
                    <input type="date" class="filter-select" name="fecha" />
                @endif

                <button class="btns" type="submit" name="action" value="search">Buscar</button>
                <!-- IMAGEN DE PDF -->
                <button type="submit" name="action" value="pdf" class="btn-PDF">
                    <img src="{{ asset('images/pdf.png')}}" alt="pdf" id="pdf" class="generar_pdf">
                </button>
                
                @if (count($piezas) > 0)
                    <div class="div-table">
                        <table id="table">
                            <thead>
                                <tr>
                                    <th>N_juego</th>
                                    <th style="width: 500px;">Nombre del operador</th>
                                    <th>Máquina</th>
                                    <th style="width: 500px;">Proceso</th>
                                    @foreach ($piezas as $pieza)
                                        @if ($pieza[4] == "Operacion Equipo")
                                            <th>Operacion</th>
                                            <script>
                                                operacion = true;
                                            </script>
                                            @break
                                        @endif
                                    @endforeach
                                    <th style="width: 300px;">Errores</th>
                                    <th>Fecha de Maquinado</th>
                                    <th>Fecha de Liberación</th>
                                    <th>Liberado/Rechazado por</th>
                                    <th>Liberar</th>
                                    <th>Rechazar</th>
                                    <th>Ver</th>
                                </tr>
                            </thead>
                            <script>
                                crearTabla(@json($piezas), @json($infoPiezas));
                                const pdf = document.getElementById('pdf');
                            </script>
                        </table>
                    </div>
                @else
                    <div class="letrero">
                        <label class="advertence"> No hay piezas trabajadas.</label>
                    </div>
                @endif
            @endisset
        </form>
    </div>
</body>
@endsection