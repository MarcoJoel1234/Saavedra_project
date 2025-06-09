@extends('layouts.appMenu')

@section('head')
<title>Liberación de piezas</title>
@vite(['resources/js/pieces_views/releasePieces/releasePieces.js', 'resources/css/pieces_views/piecesReport/adminPieces.css'])
@endsection

@section('background-body', '/images/fondoLogin.jpg') <!--Body background Image-->

@section('content')
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

        .icono-liberar,
        .icono-rechazar {
            width: 20px;
            /* Ancho */
            height: 20px;
            /* Alto */
        }

    }
</style>
@endif
<div class="container">
    <form action="{{ route('piecesRelease') }}" method="post">
        @csrf
        @isset($otElegida)
        <!-- FILTROS DE BÚSQUEDA Y RESULTADOS DE PIEZAS EN GENERAL. -->
        <h1>Liberación de piezas</h1>
        <label class="title_ot">Orden de trabajo: {{$otElegida->id}} </label>
        <label class="title_ot">Clase: {{$clase->nombre}} </label>
        <input type="hidden" name="workOrder" value="{{$otElegida->id}}">
        <input type="hidden" name="class" value="{{$clase->id}}">

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
            </table>
        </div>
        <a href="{{route('showReleasePieces_view')}}" class="btn-back">Regresar</a>
        @else
        <div class="letrero">
            <label class="advertence"> No hay piezas trabajadas.</label>
        </div>
        @endif
        @endisset
    </form>
</div>
<script>
    window.piezas = @json($piezas);
    window.infoPiezas = @json($infoPiezas);
</script>
@endsection