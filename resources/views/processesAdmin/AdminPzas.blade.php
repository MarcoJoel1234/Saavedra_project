@extends('layouts.appAdmin')
@section('content')

<head>
    <title>Buscador de OT</title>
    @vite('resources/css/adminPzas.css')
</head>
@if(!isset($otElegida))
    <style>
        form{
            width: 30%;
            height: 30vh;
            display: flex;
            flex-direction: column;
        }
        @media screen and (max-width: 600px){
            .container{
                width: 100%;
            }
            form {
                overflow: hidden;
                width: 80%;
            }
}
    </style>
@endif
<script>
    function crearTabla(piezas){ //Crea la tabla de peizas trabajadas en la O.T
        const table = document.getElementById('table');
        const tbody = document.createElement('tbody');
        for(let i=0; i<piezas.length; i++ ){
            const tr = document.createElement('tr');
            for(let j=0; j<6; j++){
                const td = document.createElement('td');
                td.textContent = piezas[i][j];
                tr.appendChild(td);
            }
            if(piezas[i][5] != "Ninguno"){ //Si la pieza tiene un error, se pinta de rojo
                tr.style.backgroundColor = "#EC7063";
            }else{
                tr.style.backgroundColor = "#ACF980"; //Si no, se pinta de verde
            }
            tbody.appendChild(tr);
        }
        table.appendChild(tbody);
    }
</script>
<body background="{{ asset('images/fondoLogin.jpg') }}">
    <div class="container">
        <form action="{{ route('searchPzasGenerales') }}" method="post">
            @csrf
            @isset($otElegida)
                <!-- FILTROS DE BUSQUEDA Y RESULTADOS DE PIEZAS EN GENERAL. -->
                <label id="title_ot">Orden de trabajo: {{$otElegida->id}} </label>
                <input type="hidden" name="ot" value="{{$otElegida->id}}">
                <select class="filter-select" name="clase">
                    @if (isset($array) && $array[0] != "Todos")
                        <option value="{{$array[0]}}">{{$array[0]}}</option>
                        <option value="todos">Clase</option>
                        @foreach ($clases as $clases)
                            @if ($clases->nombre != $array[0])
                                <option value="{{ $clases->nombre }}"> {{ $clases->nombre }}</option>
                            @endif
                        @endforeach
                        
                    @else
                        <option value="todos">Clase</option>
                        @foreach ($clases as $clases)
                            <option value="{{ $clases->nombre }}"> {{ $clases->nombre }}</option>
                        @endforeach
                    @endif
                </select>

                <select class="filter-select" name="operador">
                    @if (isset($array) && $array[1] != "Todos")   
                        <option value="{{$array[1]}}">{{$array[1]}}</option>
                        <option value="todos">Operadores</option>
                        @foreach ($operadores as $operadores)
                            @if (($operadores->nombre . " " . $operadores->a_paterno . " " . $operadores->a_materno) != $array[1])
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
                    @if (isset($array) && $array[2] != "Todos")
                        <option value="{{$array[2]}}">{{$array[2]}}</option>
                        <option value="todos">Máquina</option>
                        @foreach ($maquina as $maquina)
                            @if ($maquina != $array[2])
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
                    @if (isset($array) && $array[3] != "Todos")
                        <option value="{{$array[3]}}">{{$array[3]}}</option>
                        <option value="todos">Proceso</option>
                        @foreach ($proceso as $proceso)
                            @if ($proceso != $array[3])
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
                <button class="btns" type="submit" name="action" value="search">Buscar</button>
                <!-- IMAGEN DE PDF -->
                <button type="submit" name="action" value="pdf" class="btn-PDF">
                    <img src="{{ asset('images/pdf.png')}}" alt="pdf" id="pdf" class="generar_pdf">
                </button>
                @if (count($piezas))
                    <table id="table">
                        <thead>
                            <tr>
                                <th>N_pieza</th>
                                <th>Clase</th>
                                <th>Nombre del operador</th>
                                <th>Máquina</th>
                                <th>Proceso</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <script>
                            let piezas = @json($piezas);
                            crearTabla(piezas);

                            const pdf = document.getElementById('pdf');
                        </script>
                    </table>
                @else
                    <div class="letrero">
                        <label class="advertence"> No hay piezas trabajadas.</label>
                    </div>
                @endif
            @else
                <!-- SELECCIONA O.T A BUSCAR -->
                <label for="title" class="ot-label">Selecciona la O.T</label>
                <div>
                    <select name="ot" id="ot">
                        @foreach ($ot as $ot)
                            <option value='{{$ot->id}}'>{{$ot->id}}</option>
                        @endforeach
                    </select>
                    <button class="btns" type="submit">Buscar</button>
                </div>
            @endisset
        </form>
    </div>
</body>
@endsection
