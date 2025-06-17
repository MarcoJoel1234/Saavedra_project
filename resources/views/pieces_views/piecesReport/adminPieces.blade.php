@extends('layouts.appMenu')

@section('head')
<title>Reporte de piezas</title>
@vite(['resources/css/pieces_views/piecesReport/adminPieces.css', 'resources/js/pieces_views/piecesReport/adminPieces.js'])
@endsection
<script>
    window.liberar = "{{ asset('images/Liberar.png') }}"
    window.rechazar = "{{ asset('images/Rechazar.png') }}"
    window.ojito = "{{ asset('images/ojito.png') }}"

    window.baseUrl = "{{ url('/') }}";
</script>
@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")') <!--Body background Image-->
@section('content')

@if(!isset($workOrder))
<style>
    form {
        width: 30%;
        height: 30vh;
        display: flex;
        flex-direction: column;
    }

    @media (max-width: 991.98px){
        .container {
            width: 100%;
        }
        .title_ot {
            width: 100%;
            text-align: center;
            font-size: 1rem;
        }
        /* .generar_pdf {
            width: 10%;
            height: 10%;
        }

        form {
            overflow: hidden;
            width: 100%;
        } */

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

<form action="{{ route('searchPieces') }}" method="post">
    @csrf
    <input type="hidden" name="profile" value="admin">
    @isset($workOrder)
    <!-- FILTROS DE BÚSQUEDA Y RESULTADOS DE PIEZAS EN GENERAL. -->
    <h1>Reporte de piezas</h1>
    <label class="title_ot">Orden de trabajo: {{$workOrder->id}} </label>
    <label class="title_ot">Clase: {{$class->nombre}} </label>
    <input type="hidden" name="workOrder" value="{{$workOrder->id}}">
    <input type="hidden" name="class" value="{{$class->id}}">

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
        <table class="table">
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
                    <th>Ver</th>
                </tr>
            </thead>
        </table>
    </div>
    @else
    <div class="letrero">
        <label class="advertence"> No hay piezas trabajadas.</label>
    </div>
    @endif
    <a href="{{route('showPiecesReport_view')}}" class="btn-back">Regresar</a>
    @endisset
</form>

<script>
    let pieces = @json($piezas);
    let piecesData = @json($infoPiezas);
</script>
@endsection