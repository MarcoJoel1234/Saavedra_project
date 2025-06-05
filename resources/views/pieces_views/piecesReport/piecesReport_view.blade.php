@extends('layouts.appMenu')

@section('head')
<title>Reporte de piezas</title>
@vite(['resources/css/pieces_views/piecesReport/piecesReport_view.css', 'resources/js/pieces_views/piecesReport/piecesReport_view.js'])
@endsection

@section('content')
@section('background-body', 'images/fondoLogin.jpg') <!--Body background Image-->
<div class="container">
    <form action="{{route('searchPzasGenerales')}}" method="post" class="wrapper bg-white">
        @csrf
        <h1 class="title">Reporte de piezas</h1>
        <img src="{{ asset('images/lg_saavedra.png') }}" class="logo-saa">
        <div class="selecttts">
            @isset($arregloOT)
            <div class="select-container">
                <label for="title" class="label-select">Selecciona la O.T:</label>
                <select name="ot" id="ot">
                    <option value="null"></option>
                    @foreach ($arregloOT as $ot)
                    <option value="{{ $ot[0] }}">{{ $ot[0] }}</option>
                    @endforeach
                </select>
                <script>
                    let select_ot = document.getElementById('ot');
                    let container = document.getElementsByClassName('select-container')[0];
                    let arregloOT = @json($arregloOT);
                    select_ot.addEventListener("change", function() {
                        crearSelect(select_ot, arregloOT, container);
                    });
                </script>
            </div><br>
            @else
            <div class="select-container">
                <label for="title" class="label-select">Sin ordenes de trabajo registradas</label>
            </div>
            @endisset
        </div>
    </form>
</div>
@endsection