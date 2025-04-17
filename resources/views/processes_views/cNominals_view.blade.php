@extends('layouts.appMenu')

<!--Estilos y codigo JS-->
@vite(['resources/css/classes_views/procesos.css', 'resources/js/classes_views/procesos.js'])


@section('head')
<title>C.Nominales y tolerancias</title>
@endsection

@section('background-body', 'images/fondoLogin.jpg')

@section('content')
<div class="container-select">
    <a href="{{route('cNominals')}}" class="btn-back">Regresar</a>
    <div class="search">
        <img src="{{asset('/images/lg_saavedra.png')}}" alt="lg-saavedra" class="search-img">
        <form action="{{ route('verificarProceso') }}" method="post" class="form-search">
            @csrf
            @include('layouts.partials.messages')
            <div class="row">
                <h1 class="title">Selecciona la orden de trabajo:</h1>
            </div>
            @if (isset($clases) || isset($clase))
                <input type="text" class="ot" value="{{$ot}}" readonly>
                <div class="disabled">
                    <div class="row" id="row-title">
                        @if(!isset($proceso))
                        <label class="title" style="margin-left: 60px;">Selecciona la clase:</label>
                        @endif
    
                    </div>
                    @if (isset($proceso))
                    <div class="row">
                        <label class="title" style="margin-left: 150px;">Clase:</label>
                        <label class="title" style="margin-left: 200px;">Proceso:</label>
                        @if ($proceso == 'Copiado')
                        <label class="title" style="margin-left: 200px;">Subproceso:</label>
                        @elseif ($proceso == '1 y 2 Operacion Equipo')
                        <label class="title" style="margin-left: 200px;">Operación:</label>
                        @endif
                    </div>
                    <div class="row" id="row">
                        <!--Valor de la clase elegida-->
                        <input type="text" class="ot" value="{{$clase->nombre}}" readonly>
                        <input type="hidden" name="clase" class="ot" value="{{$clase->id}}">
                        <!--Valor del proceso elegido-->
                        <input type="text" name="proceso" id="select-proceso" class="ot" value="{{$proceso}}" style="width: 380px;" readonly>
                        @if ($proceso == 'Copiado')
                        <!--Valor del subproceso elegido-->
                        <input type="text" name="subproceso" id="select-proceso" class="ot" value="{{$subproceso}}" readonly>
                        @elseif ($proceso == '1 y 2 Operacion Equipo')
                        <!--Valor de la operación elegida-->
                        <input type="text" id="select-proceso" class="ot" value="{{$operacion}} operación" readonly>
                        @endif
                    </div>
                    @else
                    <div class="row" id="row">
                        <select id="select-clase" name="clase">
                            <option></option>
                            @foreach ($clases as $class)
                            <option value="{{$class[0]->id}}">{{$class[0]->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                    <script>
                        //Modificar los procesos de acuerdo a la clase seleccionada
                        let select_clase = document.getElementById("select-clase");
                        select_clase.addEventListener("change", function() {
                            selectProcesos(@json($procesos), @json($clases));
                        });
                    </script>
                    @endif
                </div>
            @else
                <select name="wOrder-select">
                    <option value="" selected disabled></option>
                    @foreach ($workOrders as $workOrder => $class)
                        <option value="{{$workOrder}}">{{$workOrder}}</option>
                    @endforeach
                </select>
            @endif
        </form>
    </div>
</div>
<div class="container">
    <form action="{{ route('verificarProceso') }}" method="post" class="form-search">
        @csrf
        <div class="scrollabe-table" id="scrollabe-table" style="display: none;">
            @if (isset($existe) && $existe == 0)
            <script>
                alert('No se ha encontrado el proceso, es necesario crearlo.');
                let div = document.getElementById('scrollabe-table'); //Div de la tabla.
                div.style = "display:block;"; // Limpiar div de la tabla
            </script>
            @if (isset($proceso) && $proceso == "Cavidades")
            <table class="tabla3">
                <tr>
                    <th class="t-title" style="width:150px; border:none;">#PZ</th>
                    <th class="t-title" colspan="2">Altura 1</th>
                    <th class="t-title" colspan="2">Altura 2</th>
                    <th class="t-title" colspan="2">Altura 3</th>
                </tr>
                <tr>
                    <th class="t-title" style="border:none;"></th>
                    <th>Profundidad</th>
                    <th>Diametro</th>
                    <th>Profundidad</th>
                    <th>Diametro</th>
                    <th>Profundidad</th>
                    <th>Diametro</th>
                </tr>
                <tr>
                    <td>C.Nominal</td>
                    <td><input type="number" name="cNomi_profundidad1" class="input" step="any" inputmode="decimal" required /></td>
                    <td><input type="number" name="cNomi_diametro1" class="input" step="any" inputmode="decimal" required /></td>
                    <td><input type="number" name="cNomi_profundidad2" class="input" step="any" inputmode="decimal" required /></td>
                    <td><input type="number" name="cNomi_diametro2" class="input" step="any" inputmode="decimal" required /></td>
                    <td><input type="number" name="cNomi_profundidad3" class="input" step="any" inputmode="decimal" required /></td>
                    <td><input type="number" name="cNomi_diametro3" class="input" step="any" inputmode="decimal" required /></td>
                </tr>
                <tr>
                    <td> Tolerancias</td>
                    <td><input type="number" name="tole_profundidad1_1" class="input-medio" step="any" inputmode="decimal" required /><input type="number" name="tole_profundidad2_1" class="input-medio" step="any" inputmode="decimal" required /></td>
                    <td><input type="number" name="tole_diametro1_1" class="input-medio" step="any" inputmode="decimal" required /><input type="number" name="tole_diametro2_1" class="input-medio" step="any" inputmode="decimal" required /></td>
                    <td><input type="number" name="tole_profundidad1_2" class="input-medio" step="any" inputmode="decimal" required /><input type="number" name="tole_profundidad2_2" class="input-medio" step="any" inputmode="decimal" required /></td>
                    <td><input type="number" name="tole_diametro1_2" class="input-medio" step="any" inputmode="decimal" required /><input type="number" name="tole_diametro2_2" class="input-medio" step="any" inputmode="decimal" required /></td>
                    <td><input type="number" name="tole_profundidad1_3" class="input-medio" step="any" inputmode="decimal" required /><input type="number" name="tole_profundidad2_3" class="input-medio" step="any" inputmode="decimal" required /></td>
                    <td><input type="number" name="tole_diametro1_3" class="input-medio" step="any" inputmode="decimal" required /><input type="number" name="tole_diametro2_3" class="input-medio" step="any" inputmode="decimal" required /></td>
                </tr>
            </table>
            @elseif(isset($proceso) && $proceso == 'Off Set')
            <table border="1" class="tabla3">
                <tr>
                <tr>
                    <th class="t-title" style="width:150px; border:none;">#PZ</th>
                    <th class="t-title" style="width:200px; border-bottom:none;">Ancho de altura</th>
                    <th class="t-title" colspan="2">Profundidad de tacon</th>
                    <th class="t-title" colspan="2">Simetría</th>
                    <th class="t-title" style="width:200px; border-bottom:none;">Ancho del tacon</th>
                    <th class="t-title" colspan="2">Barreno Lateral</th>
                    <th class="t-title" style="width:200px; border-bottom:none;">Altura tacon inicial</th>
                    <th class="t-title" style="width:200px; border-bottom:none;">Altura tacon intermedia</th>
                </tr>
                <tr>
                    <th class="t-title" style="border:none;"></th>
                    <th style="border-bottom:none; border-top:none;"></th>
                    <th>Hembra</th>
                    <th>Macho</th>
                    <th>Hembra</th>
                    <th>Macho</th>
                    <th style="border-bottom:none; border-top:none;"></th>
                    <th>Hembra</th>
                    <th>Macho</th>
                    <th style="border-bottom:none; border-top:none;"></th>
                    <th style="border-bottom:none; border-top:none;"></th>
                </tr>
                <tr>
                    <td>C.Nominal</td>
                    <td><input type="number" name="cNomi_anchoRanura" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_profuTaconHembra" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_profuTaconMacho" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_simetriaHembra" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_simetriaMacho" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_anchoTacon" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_barrenoLateralHembra" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_barrenoLateralMacho" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_alturaTaconInicial" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_alturaTaconIntermedia" class="input" step="any" inputmode="decimal" required></td>
                </tr>
                <tr>
                    <td>Tolerancias</td>
                    <td><input type="number" name="tole_anchoRanura" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_profuTaconHembra" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_profuTaconMacho" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_simetriaHembra" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_simetriaMacho" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_anchoTacon" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_barrenoLateralHembra" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_barrenoLateralMacho" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_alturaTaconInicial" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_alturaTaconIntermedia" class="input" step="any" inputmode="decimal" required></td>
                </tr>
                </tr>
            </table>
            @else
            <script>
                const select = document.getElementById('select-proceso'); // Select de proceso.
                if (select.value != "") { // Si el select tiene un valor.
                    let proceso = new Proceso(select.value, undefined, undefined); // Crear proceso
                    let div = document.getElementById('scrollabe-table'); //Div de la tabla.
                    div.innerHTML = ""; // Limpiar div de la tabla 
                    div.style = "display:block;"; //No acepta a ningún otro elemento más en esa fila, es decir los demás elementos bajan
                    @if(isset($subproceso))
                    div.appendChild(proceso.crearProceso(@json($subproceso))); //Agregar tabla al div de la tabla
                    @else
                    div.appendChild(proceso.crearProceso(0)); //Agregar tabla al div de la tabla
                    @endif
                }
            </script>
            @endif
            @elseif (isset($existe) && $existe == 1)
            <script>
                alert('Datos de cotas nominales Encontrados/Guardados');
                let div = document.getElementById('scrollabe-table'); //Div de la tabla.
                div.style = "display:block;";
            </script>
            @if ($proceso == 'Cavidades')
            <table class="tabla3">
                <tr>
                    <th class="t-title" style="width:150px; border:none;">#PZ</th>
                    <th class="t-title" colspan="2">Altura 1</th>
                    <th class="t-title" colspan="2">Altura 2</th>
                    <th class="t-title" colspan="2">Altura 3</th>
                </tr>
                <tr>
                    <th class="t-title" style="border:none;"></th>
                    <th>Profundidad</th>
                    <th>Diametro</th>
                    <th>Profundidad</th>
                    <th>Diametro</th>
                    <th>Profundidad</th>
                    <th>Diametro</th>
                </tr>
                <tr>
                    <td>C.Nominal</td>
                    <td><input type="number" name="cNomi_profundidad1" value="{{$cNominal->profundidad1}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_diametro1" value="{{$cNominal->diametro1}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_profundidad2" value="{{$cNominal->profundidad2}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_diametro2" value="{{$cNominal->diametro2}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_profundidad3" value="{{$cNominal->profundidad3}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_diametro3" value="{{$cNominal->diametro3}}" class="input" step="any" inputmode="decimal" required></td>
                </tr>
                <tr>
                    <td> Tolerancias </td>
                    <td><input type="number" value="{{$tolerancia->profundidad1_1}}" name="tole_profundidad1_1" class="input-medio" step="any" inputmode="decimal" required><input type="number" value="{{$tolerancia->profundidad2_1}}" name="tole_profundidad2_1" class="input-medio" step="any" inputmode="decimal" required></td>
                    <td><input type="number" value="{{$tolerancia->diametro1_1}}" name="tole_diametro1_1" class="input-medio" step="any" inputmode="decimal" required><input type="number" value="{{$tolerancia->diametro2_1}}" name="tole_diametro2_1" class="input-medio" step="any" inputmode="decimal" required></td>
                    <td><input type="number" value="{{$tolerancia->profundidad1_2}}" name="tole_profundidad1_2" class="input-medio" step="any" inputmode="decimal" required><input type="number" value="{{$tolerancia->profundidad2_2}}" name="tole_profundidad2_2" class="input-medio" step="any" inputmode="decimal" required></td>
                    <td><input type="number" value="{{$tolerancia->diametro1_2}}" name="tole_diametro1_2" class="input-medio" step="any" inputmode="decimal" required><input type="number" value="{{$tolerancia->diametro2_2}}" name="tole_diametro2_2" class="input-medio" step="any" inputmode="decimal" required></td>
                    <td><input type="number" value="{{$tolerancia->profundidad1_3}}" name="tole_profundidad1_3" class="input-medio" step="any" inputmode="decimal" required><input type="number" value="{{$tolerancia->profundidad2_3}}" name="tole_profundidad2_3" class="input-medio" step="any" inputmode="decimal" required></td>
                    <td><input type="number" value="{{$tolerancia->diametro1_3}}" name="tole_diametro1_3" class="input-medio" step="any" inputmode="decimal" required><input type="number" value="{{$tolerancia->diametro2_3}}" name="tole_diametro2_3" class="input-medio" step="any" inputmode="decimal" required></td>
                </tr>
            </table>
            @elseif($proceso == 'Off Set')
            <table border="1" class="tabla3">
                <tr>
                <tr>
                    <th class="t-title" style="width:150px; border:none;">#PZ</th>
                    <th class="t-title" colspan="1" style="width:200px; border-bottom:none;">Ancho de altura</th>
                    <th class="t-title" colspan="2">Profundidad de tacon</th>
                    <th class="t-title" colspan="2">Simetría</th>
                    <th class="t-title" colspan="1" style="width:200px; border-bottom:none;">Ancho del tacon</th>
                    <th class="t-title" colspan="2">Barreno Lateral</th>
                    <th class="t-title" colspan="1" style="width:200px; border-bottom:none;">Altura tacon inicial</th>
                    <th class="t-title" colspan="1" style="width:200px; border-bottom:none;">Altura tacon intermedia</th>
                </tr>
                <tr>
                    <th class="t-title" style="border:none;"></th>
                    <th style="border-bottom:none; border-top:none;"></th>
                    <th>Hembra</th>
                    <th>Macho</th>
                    <th>Hembra</th>
                    <th>Macho</th>
                    <th style="border-bottom:none; border-top:none;"></th>
                    <th>Hembra</th>
                    <th>Macho</th>
                    <th style="border-bottom:none; border-top:none;"></th>
                    <th style="border-bottom:none; border-top:none;"></th>
                </tr>
                <tr>
                    <td>C.Nominal</td>
                    <td><input type="number" name="cNomi_anchoRanura" value="{{$cNominal->anchoRanura}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_profuTaconHembra" value="{{$cNominal->profuTaconHembra}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_profuTaconMacho" value="{{$cNominal->profuTaconMacho}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_simetriaHembra" value="{{$cNominal->simetriaHembra}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_simetriaMacho" value="{{$cNominal->simetriaMacho}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_anchoTacon" value="{{$cNominal->anchoTacon}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_barrenoLateralHembra" value="{{$cNominal->barrenoLateralHembra}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_barrenoLateralMacho" value="{{$cNominal->barrenoLateralMacho}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_alturaTaconInicial" value="{{$cNominal->alturaTaconInicial}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="cNomi_alturaTaconIntermedia" value="{{$cNominal->alturaTaconIntermedia}}" class="input" step="any" inputmode="decimal" required></td>
                </tr>
                <tr>
                    <td> Tolerancias </td>
                    <td><input type="number" name="tole_anchoRanura" value="{{$tolerancia->anchoRanura}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_profuTaconHembra" value="{{$tolerancia->profuTaconHembra}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_profuTaconMacho" value="{{$tolerancia->profuTaconMacho}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_simetriaHembra" value="{{$tolerancia->simetriaHembra}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_simetriaMacho" value="{{$tolerancia->simetriaMacho}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_anchoTacon" value="{{$tolerancia->anchoTacon}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_barrenoLateralHembra" value="{{$tolerancia->barrenoLateralHembra}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_barrenoLateralMacho" value="{{$tolerancia->barrenoLateralMacho}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_alturaTaconInicial" value="{{$tolerancia->alturaTaconInicial}}" class="input" step="any" inputmode="decimal" required></td>
                    <td><input type="number" name="tole_alturaTaconIntermedia" value="{{$tolerancia->alturaTaconIntermedia}}" class="input" step="any" inputmode="decimal" required></td>
                </tr>
                </tr>
            </table>
            @else
            <script>
                let proceso = new Proceso(@json($proceso), @json($cNominal), @json($tolerancia)); // Crear el proceso
                div.appendChild(proceso.crearProceso(@json($subproceso))); //Agregar tabla al div.
            </script>
            @endif

            @endif
            <!--Enviar valores de procesos-->
            @isset($proceso)
            <input type="hidden" name="proceso" value="{{$proceso}}">
            <input type="hidden" name="clase" class="ot" value="{{$clase->id}}">
            @if ($proceso == 'Copiado')
            <input type="hidden" name="subproceso" value="{{$subproceso}}">
            @elseif ($proceso == '1 y 2 Operacion Equipo')
            <input type="hidden" name="operacion" value="{{$operacion}}">
            @endif
            @endisset
        </div>
        <!--Aparecer boton-->
        @if(isset($existe))
        <button class="btn" id="btn"">Guardar</button>
            <style>
                .btn-back{
                    display: block;
                }
            </style>
        @endif
    </form>
</div>
<script>
    window.workOrders = @json($workOrders);
</script>
@endsection