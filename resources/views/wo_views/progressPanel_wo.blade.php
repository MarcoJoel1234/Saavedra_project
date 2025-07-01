@extends('layouts.appMenu')

@section('head')
    <title>Panel de OT's</title>
    <meta http-equiv="refresh" content="10" />
    @vite(['resources/css/wo_views/progressPanel_wo.css', 'resources/js/wo_views/progressPanel_wo.js'])
@endsection


@section('content')
    <div class="tablas">
        <!-- A Tiempo -->
        <div class="tabla-box">
            <h2 style="color: #0a8504">A TIEMPO</h2>
            <div class="fila header">
                <div class="tex-center">OT</div>
                <div class="tex-center">Nombre</div>
                <div class="tex-center">Tipo</div>
                <div>Fecha Pronóstico</div>
                <div>Fecha Compromiso</div>
                <div>Fecha Real</div>
            </div>

            <!-- Ejemplo de fila -->
            <div class="fila">
                <div class="tex-center">6507</div>
                <div class="tex-center">Sra. Leona Extra Grande Especial 2da Serie</div>
                <div class="tex-center">Molde</div>
                <div>2025-06-21</div>
                <div>2025-06-23</div>
                <div>2025-06-23</div>
                <div class="barra">
                    <div class="progreso" style="width: 95%"></div>
                </div>
            </div>
        </div>

        <!-- Demora -->
        <div class="tabla-box">
            <h2 style="color: #9c0303">DEMORA</h2>
            <div class="fila header">
                <div class="tex-center">OT</div>
                <div class="tex-center">Nombre</div>
                <div class="tex-center">Tipo</div>
                <div>Fecha Pronóstico</div>
                <div>Fecha Compromiso</div>
                <div>Fecha Real</div>
            </div>

            <div class="fila demora">
                <div class="tex-center">8754</div>
                <div class="tex-center">Bikendi 60ml</div>
                <div class="tex-center">Molde</div>
                <div>2025-06-18</div>
                <div>2025-06-20</div>
                <div>2025-06-22</div>
                <div class="barra">
                    <div class="progreso" style="width: 40%"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('background-body')
background-image: url('{{ asset('images/fondoLogin.jpg') }}');
background-size: cover;
background-position: center;
background-repeat: no-repeat;
background-attachment: fixed;
@endsection
