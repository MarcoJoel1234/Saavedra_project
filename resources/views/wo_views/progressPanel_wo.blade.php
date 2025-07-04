@extends('layouts.appMenu')

@section('head')
    <title>Panel de OT's</title>
    <meta http-equiv="refresh" content="10" />
    @vite(['resources/css/wo_views/progressPanel_wo.css', 'resources/js/wo_views/progressPanel_wo.js'])
@endsection

@section('content')
    <div class="tablas">
        <!-- A Tiempo tabla-->
        <div class="tabla-box">
            <h2 style="color: #0a8504; font-weight: bold;">A TIEMPO</h2>
            <div class="fila header">
                <div class="tex-center">OT</div>
                <div class="tex-center">NOMBRE</div>
                <div class="tex-center">TIPO</div>
                <div>F. PRONÓSTICO</div>
                <div>F. COMPROMISO</div>
                <div>FECHA REAL</div>
            </div>

            <!-- Fila 1 -->
            <div class="fila">
                <div class="tex-center">6507</div>
                <div class="tex-center">SRA. LEONA EXTRA GRANDE 2 SET NUEVA EDICIÓN</div>
                <div class="tex-center">BOMBILLO</div>
                <div>2025-06-21</div>
                <div>2025-06-23</div>
                <div>2025-06-23</div>
                <div class="barra">
                    <div class="progreso" style="width: 95%"></div>
                </div>
            </div>

            <!-- Fila 2 -->
            <div class="fila">
                <div class="tex-center">6541</div>
                <div class="tex-center">FRASCO DE AGUA MINERAL 355ML</div>
                <div class="tex-center">MOLDE</div>
                <div>2025-06-25</div>
                <div>2025-06-28</div>
                <div>2025-06-27</div>
                <div class="barra">
                    <div class="progreso" style="width: 88%"></div>
                </div>
            </div>
            <!-- Fila 3 -->
            <div class="fila">
                <div class="tex-center">2013</div>
                <div class="tex-center">MASON GARAT 16 OZ CUADRADO</div>
                <div class="tex-center">MOLDE</div>
                <div>2025-06-25</div>
                <div>2025-06-28</div>
                <div>2025-06-27</div>
                <div class="barra">
                    <div class="progreso" style="width: 88%"></div>
                </div>
            </div>
        </div>

        <!-- Demora tabla-->
        <div class="tabla-box">
            <h2 style="color: #9c0303; font-weight: bold;">DEMORA</h2>
            <div class="fila header">
                <div class="tex-center">OT</div>
                <div class="tex-center">NOMBRE</div>
                <div class="tex-center">TIPO</div>
                <div>F. PRONÓSTICO</div>
                <div>F. COMPROMISO</div>
                <div>FECHA REAL</div>
            </div>

            <!-- Fila 1 -->
            <div class="fila demora">
                <div class="tex-center">8754</div>
                <div class="tex-center">BIKENDI 60 ML</div>
                <div class="tex-center">MOLDE</div>
                <div>2025-06-18</div>
                <div>2025-06-20</div>
                <div>2025-06-22</div>
                <div class="barra">
                    <div class="progreso" style="width: 40%"></div>
                </div>
            </div>

            <!-- Fila 2 -->
            <div class="fila demora">
                <div class="tex-center">8999</div>
                <div class="tex-center">PANTALONES 70CL</div>
                <div class="tex-center">MOLDE</div>
                <div>2025-06-15</div>
                <div>2025-06-18</div>
                <div>2025-06-25</div>
                <div class="barra">
                    <div class="progreso" style="width: 25%"></div>
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
