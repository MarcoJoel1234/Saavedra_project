@extends('layouts.appMenu')

@section('head')
<title>Reporte de piezas</title>
@vite(['resources/css/pieces_views/piecesReport/piecesReport_view.css', 'resources/js/pieces_views/piecesReport/piecesReport_view.js'])
@endsection

@section('background-body', 'images/fondoLogin.jpg') <!--Body background Image-->
@section('content')

<div class="wrapper">
    <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra">
    <h2>Reporte de piezas</h2>
    @if(count($dataWO) > 0)
    <form action="{{route('searchPieces')}}" method="post" class="form">
        @csrf
        <select name="workOrder" class="select-workOrder select">
            <option value="null">Selecciona una opcion</option>
            @foreach ($dataWO as $workOrder)
            <option value="{{ $workOrder[0] }}">{{ $workOrder[0] }}</option>
            @endforeach
        </select>
        <label for="workOrder" class="label-select">Orden de trabajo</label>
    </form>
    <script>
        let dataWO = @json($dataWO);
    </script>
    @else
    <div class="select-container">
        <label for="title" class="label-select">Sin ordenes de trabajo registradas</label>
    </div>
    @endisset
</div>
@endsection