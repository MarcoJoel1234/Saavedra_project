@extends('layouts.appMenu')

@section('head')
<title>Liberación de piezas</title>
@vite(['resources/css/pieces_views/releasePieces/releasePieces_view.css', 'resources/js/pieces_views/releasePieces/releasePieces_view.js'])
@endsection

@section('background-body', '/images/fondoLogin.jpg') <!--Body background Image-->
@section('content')
<form action="{{route('piecesRelease')}}" method="post" class="wrapper">
    @csrf
    <input type="hidden" name="perfil" value="{{ Auth::user()->perfil }}">
    <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra">
    <h2 class="title">Liberación de piezas</h2>
    @isset($arregloOT)
    <div class="select-container">
        <select name="workOrder" class="select-workOrder select">
            <option value="null">Selecciona una opcion</option>
            @foreach ($arregloOT as $ot)
            <option value="{{ $ot[0] }}">{{ $ot[0] }}</option>
            @endforeach
        </select>
        <label for="title" class="label-select">Orden de trabajo</label>
    </div>
    <script>
        window.arregloOT = @json($arregloOT);
    </script>
    @else
    <div class="select-container">
        <label for="title" class="label-select">Sin ordenes de trabajo registradas</label>
    </div>
    @endisset
</form>
@endsection