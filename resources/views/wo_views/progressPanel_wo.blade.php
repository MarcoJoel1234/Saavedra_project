@extends('layouts.appMenu')

@section('head')
<title><!--TITULO DE LA INTERFAZ--></title>
@vite(['resources/css/wo_views/progressPanel_wo.css', 'resources/js/wo_views/progressPanel_wo.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")')

@section('content')

<!--CONTENIDO DE LA INTERFAZ-->

@endsection