@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@section('head')
<title>Tiempos de producción</title>
@vite(['resources/css/processes_views/productionTimes.css', 'resources/js/processes_views/productionTimes.js'])
@endsection

@section('background-body', '/images/fondoLogin.jpg')
@section('content')

<!--Sección de busqueda-->
<form action="{{ route('storeTimes') }}" method="post" class="form">
    @csrf
    <div class="search left">
        <img src="{{ asset('/images/lg_saavedra.png') }}" alt="lg-saavedra" class="lg-saavedra">
        <h2>Modificar tiempos de producción</h2>
        @csrf
        @include('layouts.partials.messages')
        @if(isset($clase))
        <select name="clase" id="clase" class="form-select">
            <option value="0" {{ $clase == 0 ? 'selected' : '' }}>Selecciona una opción</option>
            <option value="Bombillo" {{ $clase == 'Bombillo' ? 'selected' : '' }}>Bombillo</option>
            <option value="Molde" {{ $clase == 'Molde' ? 'selected' : '' }}>Molde</option>
        </select>
        @else
        <select name="clase" id="clase" class="form-select">
            <option value="0">Selecciona una opción</option>
            <option value="Bombillo">Bombillo</option>
            <option value="Molde">Molde</option>
        </select>
        @endif
        <label for="class" class="form-label">Clase</label>
    </div>

</form>
<script>
    const tiempos = @json($tiempos);
</script>
@endsection