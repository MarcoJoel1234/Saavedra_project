@extends('layouts.appMenu')

@section('head')
<title>Progreso de piezas</title>
@vite(['resources/css/wo_views/piecesInProgress_view.css', 'resources/js/wo_views/piecesInProgress_view.js'])
@endsection

@section('background-body', 'images/fondoLogin.jpg')

@section('content')
<script>
    window.wOInProgress = @json($wOInProgress);
    setTimeout(() => {
        location.reload();
    }, 50000);
</script>
@endsection