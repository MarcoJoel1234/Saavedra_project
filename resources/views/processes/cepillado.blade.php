@extends('layouts.appMenu')

@section('head')
<title>Cepillado</title>
@vite(['resources/css/cepillado.css', 'resources/js/editarInterfaz.js', 'resources/js/editarTabla.js'])
@endsection

@section('background-body', 'background-image:url("' . asset("images/hola.jpg") . '")') <!--Body background Image-->
@section('content')

<!--Si ya se ha registrado la primera parte de la meta-->
@if (isset($band) && $band == 1 || isset($band) && $band == 2 || isset($band) && $band == 4)
<style>
    .disabled {
        display: block;
    }

    #div-btn-accept {
        display: none;
    }
</style>
@else
<style>
    .disabled {
        display: none;
    }
</style>
@endif

@if (isset($band) && $band == 2 || isset($band) && $band == 4)
<style>
    #btn-class {
        display: none;
    }
</style>
@endif
@isset($error)
<script>
    alert("La máquina elegida esta ocupada, por favor elige otra");
</script>
@endisset
@if((isset($pzasRestantes) && $pzasRestantes <= 0) && $band != 4)
<script>
    alert("Se han registrado todas las piezas");
</script>
@endif
<div class="container">
    <!--Formulario en donde se guardara la meta de cepillado-->
    <form action="{{route('saveHeader')}}" method="post">
        @csrf
        <input type="hidden" name="proceso" value="cepillado">
        <!--Div para el header del proceso-->
        <div class="container-header">
            <!--Div para los datos ingresados por el usuario-->
            @if (isset($ot) || isset($meta))
            <div class="datos">
                <!--Si no existen piezas registradas en esa meta y el usuario ya ha completado los campos requeridos de la meta -->
                @if ((isset($band) && $band == 2) && (!isset($nPiezas) || $nPiezas == "[]"))
                <!--Boton de editar Meta-->
                <div id="editarHeader">
                    <button type="submit" class="boton-editar" id="edit-header">
                        <img src="{{ asset('images/editar.png')}}" id="desbloquear" alt="Desbloquear">
                    </button>
                </div>
                @endif
                <!--Matricula del operador que ha iniciado sesion-->
                <div class="input-datos">
                    <label>Matricula del operador:</label>
                    <input type="text" value="{{auth()->user()->matricula}}" name="id_usuario" style="cursor:auto;" readonly>
                </div>
                <!--Si ya se ha registrado la primera parte de la meta-->
                @if (isset($band) && $band == 1 || isset($band) && $band == 2 || isset($band) && $band == 4)
                <div class="input-datos">
                    <label>Orden de trabajo:</label>
                    <input type="text" name="ot" value="{{$meta->id_ot}}" style="cursor:auto; width:20%" readonly>
                </div>
                <div class="input-datos">
                    <label>Nombre de la moldura:</label>
                    <input type="text" value="{{$moldura}}" style="width: 100%; cursor:auto;" readonly>
                </div>
                <div class="input-datos">
                    <label for="hora" style="padding-right: 10px;">Hora de inicio:</label>
                    <label for="hora">Hora de termino:</label><br>
                    <input type="time" id="hora" name="h_inicio" value="{{$meta->h_inicio}}" style="cursor:auto;" readonly>
                    <input type="time" id="hora" name="h_termino" value="{{$meta->h_termino}}" style="cursor:auto; margin-left:5%;" readonly>
                </div>
                <div class="input-datos">
                    <label for="fecha" style="padding-right: 60px; margin-left:50px;">Fecha:</label>
                    <label for="fecha">Máquina:</label><br>
                    <input type="date" name="fecha" id="fecha" value="{{$meta->fecha}}" style="cursor:auto;" readonly>
                    <input type="text" name="maquina" value="{{$meta->maquina}}" style="cursor:auto; width:20%;" readonly>
                </div>
                @else
                <!--Si se requiere editar la meta-->
                @if (isset($band) && $band == 3)
                <!--Orden de trabajo deshabilitada-->
                <div class="input-datos">
                    <label>Orden de trabajo:</label>
                    <input type="text" name="ot" value="{{$meta->id_ot}}" style="cursor:auto;" readonly>
                    <input type="hidden" name="band" value="3">
                    <input type="hidden" name="meta" value="{{$meta->id}}">
                </div>
                <!--Si aun no se ha registrado la primera parte de la meta-->
                @else
                <!--Orden de trabajo habilitada-->
                <div class="input-datos">
                    <label>Orden de trabajo:</label>
                    <select id="datos" name="ot">
                        @foreach($ot as $ot)
                        <option value="{{$ot->id}}">{{$ot->id}}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <!-- Primera parte de la meta habilitada -->
                <div class="input-datos">
                    <label for="hora" style="padding-right: 10px;">Hora de inicio:</label>
                    <label for="hora">Hora de termino:</label><br>
                    <input type="time" id="hora" name="h_inicio" required>
                    <input type="time" id="hora" name="h_termino" required>
                </div>
                <div class="input-datos">
                    <label for="fecha" style="padding-right: 70px; margin-left:50px;">Fecha:</label>
                    <label for="fecha">Máquina:</label><br>
                    <input type="date" id="fecha" name="fecha" required>
                    <!-- <script>
                                let today = new Date().toISOString().split("T")[0];
                                document.getElementById("fecha").setAttribute("min", today);
                                document.getElementById("fecha").setAttribute("max", today);
                            </script> -->
                    <select name="maquina">
                        @for ($i=1; $i<=7; $i++)
                            <option value="{{$i}}">Máquina {{$i}}</option>
                            @endfor
                    </select>
                </div>
                @endif
                <!-- Botón aceptar -->
                <div class="input-datos" id="div-btn-accept">
                    <button id="btn-accept" style="margin-left:70px;">Aceptar</button><br>
                </div>
                <!--Div para seleccionar la clase-->
                <div class="disabled">
                    <div class="input-datos" id="div-clases">
                        @if (isset($clases) && isset($band) && $band == 1)
                        <label for="clase">Clase:</label><br>
                        @for ($i = 0; $i < count($clases); $i++)
                            <input type="radio" id="" name="clases" class="clases" value="{{$clases[$i][0]->nombre}}">
                            <label>{{$clases[$i][0]->nombre}}</label>
                            <input type="hidden" name="tamaño" value="{{$clases[$i][0]->tamanio}}">
                            <input type="hidden" name="piezas" value="{{$clases[$i][0]->piezas}}">
                            @endfor
                            @endif
                            @if (isset($meta) && isset($band) && $band == 2 || isset($band) && $band == 4)
                            <label for="clase">Clase:</label>
                            <label for="pedido" style="margin-left: 95px;">Pedido:</label><br>
                            <label class="clases">{{$clase->nombre}} {{$clase->tamanio}}</label>
                            <input type="hidden" name="clases" value="{{$meta->clase}}">
                            <input type="hidden" name="tamaño" value="{{$meta->tamaño}}">
                            <input type="hidden" name="vista" value='true'>
                            <label class="clases">{{$clase->pedido}} piezas</label>
                            @endif
                    </div>
                    <div class="input-datos" id="div-clases">
                        @if (isset($meta) && isset($band) && $band == 2 || isset($band) && $band == 4)
                        <label for="pedido">Piezas ingresadas:</label>
                        <label for="pedido" style="margin-left: 20px;">Piezas restantes:</label><br>
                        <label class="clases" style="margin-left: 50px;">{{$clase->piezas}} piezas</label>
                        <label class="clases" style="margin-left: 120px;">{{$pzasRestantes}} piezas</label>
                        @endif
                    </div>
                    <button class="btn" id="btn-class">Siguiente</button>
                </div>
            </div>
            @else
            <div class="datos" style="text-align: center;">
                <h3 style="color: red;">Sin ordenes de trabajo </h3>
            </div>
            @endif
            <div class="div-tabla2">
                <table border="1" id="tabla2">
                    <tr>
                        <th id="col1">Tiempo estandar.</th>
                        <th id="col2">Meta piezas/juegos.</th>
                        <th id="col3">Resultado.</th>
                    </tr>
                    @if (isset($meta->meta))
                    <td id="celda1"><input type="text" value="{{$meta->t_estandar}} min" style="cursor:auto;" readonly></td>
                    <td id="celda2"><input type="text" value="{{$meta->meta}}" style="cursor:auto;" readonly></td>
                    @if (isset($meta->resultado))
                    <td id="celda2"><input type="text" value="{{$meta->resultado}}" style="cursor:auto;" readonly></td>
                    @else
                    <td id="celda2"><input type="text" value="0" style="cursor:auto;" readonly></td>
                    @endif
                    @else
                    <td id="celda1"><input type="text" value="0 min" style="cursor:auto;" readonly></td>
                    <td id="celda2"><input type="text" value="0" style="cursor:auto;" readonly></td>
                    <td id="celda2"><input type="text" value="0" style="cursor:auto;" readonly></td>
                    @endif
                </table>
            </div>
            <div class="div-tabla1">
                <table border="4" id="tabla1">
                    <tr>
                        <th>Código</th>
                        <th> F- PRO - CTP</th>
                    </tr>
                    <tr>
                        <th>Versión</th>
                        <th> 2</th>
                    </tr>
                    <tr>
                        <th>Fecha de revisión: </th>
                        <th> 23 - Agosto- 23</th>
                    </tr>
                </table>
            </div>
        </div>
    </form>
    <!--Formulario para los datos de la tabla-->
    @if (isset($band) && $band == 2)
    <div class="disabled-tabla">
        <form action="{{ route('cepilladoHeader')}}" method="post">
            @csrf
            <input type="hidden" name="metaData" value="{{$meta->id}}">
            <div class="scrollabe-table">
                <table border="1" class="tabla3">
                    <tr>
                        <th class="t-title" style="width:150px">#PZ</th>
                        <th class="t-title">Radio final de mordaza</th>
                        <th class="t-title">Radio final mayor</th>
                        <th class="t-title">Radio final de sufridera</th>
                        <th class="t-title">Profundidad final conexión Fondo/Corona</th>
                        <th class="t-title">Profundidad final mitad de Molde/Bombillo</th>
                        <th class="t-title">Profundidad final Pico/Conexión de obturador</th>
                        <th class="t-title">Acetato B/M</th><br>
                        <th class="t-title">Ensamble</th>
                        <th class="t-title">Distancia de barreno de alineación</th>
                        <th class="t-title">Profundidad de barreno de alineación Hembra</th>
                        <th class="t-title">Profundidad de barreno de alineación Macho</th>
                        <th class="t-title">Altura de vena Hembra</th>
                        <th class="t-title">Altura de vena Macho</th>
                        <th class="t-title">Ancho de vena</th>
                        <th class="t-title">Laterales</th>
                        <th class="t-title">PIN</th>
                        <th class="t-title" style="width:200px">Error</th>
                        <th class="t-title" style="width:700px">Observaciones</th>
                    </tr>

                    @if(!isset($cNominal))
                    <tr>
                        <td>C.Nominal.</td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input-medio" disabled><input type="number" class="input-medio" disabled></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <tr>
                        <td> Tolerancias. </td>
                        <td><input type="number" class="input-medio" disabled><input type="number" class="input-medio" disabled></td>
                        <td><input type="number" class="input-medio" disabled><input type="number" class="input-medio" disabled></td>
                        <td><input type="number" class="input-medio" disabled><input type="number" class="input-medio" disabled></td>
                        <td><input type="number" class="input-medio" disabled><input type="number" class="input-medio" disabled></td>
                        <td><input type="number" class="input-medio" disabled><input type="number" class="input-medio" disabled></td>
                        <td><input type="number" class="input-medio" disabled><input type="number" class="input-medio" disabled></td>
                        <td></td>
                        <td><input type="number" class="input-medio" disabled><input type="number" class="input-medio" disabled></td>
                        <td><input type="text" class="input-medio" disabled><input type="text" class="input-medio" disabled></td>
                        <td><input type="text" class="input-medio" disabled><input type="text" class="input-medio" disabled></td>
                        <td><input type="text" class="input-medio" disabled><input type="text" class="input-medio" disabled></td>
                        <td><input type="text" class="input-medio" disabled><input type="text" class="input-medio" disabled></td>
                        <td><input type="number" class="input-medio" disabled><input type="number" class="input-medio" disabled></td>
                        <td><input type="text" class="input-medio" disabled><input type="text" class="input-medio" disabled></td>
                        <td><input type="text" class="input-medio" disabled><input type="text" class="input-medio" disabled></td>
                        <td><input type="number" class="input-medio" disabled><input type="number" class="input-medio" disabled></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @else
                    <tr>
                        <td>C.Nominal.</td>
                        <td><input type="number" value="{{$cNominal->radiof_mordaza}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->radiof_mayor}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->radiof_sufridera}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->profuFinal_CFC}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->profuFinal_mitadMB}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->profuFinal_PCO}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td></td>
                        <td><input type="number" value="{{$cNominal->ensamble}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->distancia_barrenoAli}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->profu_barrenoAliHembra}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->profu_barrenoAliMacho}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->altura_venaHembra}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->altura_venaMacho}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->ancho_vena}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->laterales}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td>
                            <input type="number" value="{{$cNominal->pin1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$cNominal->pin2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td> Tolerancias. </td>
                        <td>
                            <input type="number" value="{{$tolerancia->radiof_mordaza1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->radiof_mordaza2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td>
                            <input type="number" value="{{$tolerancia->radiof_mayor1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->radiof_mayor2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td>
                            <input type="number" value="{{$tolerancia->radiof_sufridera1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->radiof_sufridera2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td>
                            <input type="number" value="{{$tolerancia->profuFinal_CFC1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->profuFinal_CFC2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td>
                            <input type="number" value="{{$tolerancia->profuFinal_mitadMB1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->profuFinal_mitadMB2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td>
                            <input type="number" value="{{$tolerancia->profuFinal_PCO1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->profuFinal_PCO2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td></td>
                        <td>
                            <input type="number" value="{{$tolerancia->ensamble1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->ensamble2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->distancia_barrenoAli1}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->distancia_barrenoAli2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->profu_barrenoAliHembra1}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->profu_barrenoAliHembra2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->profu_barrenoAliMacho1}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->profu_barrenoAliMacho2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->altura_venaHembra1}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->altura_venaHembra2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->altura_venaMacho1}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->altura_venaMacho2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->ancho_vena1}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->ancho_vena2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->laterales1}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->laterales2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="number" value="{{$tolerancia->pin1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->pin2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endif
                    <!--Llenado de piezas-->

                    @if (isset($nPiezas))
                    @if ($nPiezas->count() != 0)
                    @foreach ($nPiezas as $nPiezas)
                    @if ($nPiezas->correcto == 0)
                    <tr>
                        <td><input type="text" class="input" style="background-color:#F36456" value="{{$nPiezas->n_pieza}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->radiof_mordaza}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->radiof_mayor}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->radiof_sufridera}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->profuFinal_CFC}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->profuFinal_mitadMB}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->profuFinal_PCO}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="text" class="input" style="background-color:#F36456" value="{{$nPiezas->acetato_MB}}" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->ensamble}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->distancia_barrenoAli}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->profu_barrenoAliHembra}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->profu_barrenoAliMacho}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->altura_venaHembra}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->altura_venaMacho}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->ancho_vena}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->laterales}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input-medio" style="background-color:#F36456" value="{{$nPiezas->pin1}}" step="any" inputmode="decimal" readonly><input type="number" class="input-medio" style="background-color:#F36456" value="{{$nPiezas->pin2}}" step="any" inputmode="decimal" readonly></td>
                        <td> <input type="text" class="input" style="background-color:#F36456" value="{{$nPiezas->error}}" readonly></td>
                        <td><textarea class="input" style="background-color:#F36456" readonly>{{$nPiezas->observaciones}}</textarea></td>
                    </tr>
                    @else
                    <tr>
                        <td><input type="text" class="input" style="background-color:#90F77E" value="{{$nPiezas->n_pieza}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->radiof_mordaza}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->radiof_mayor}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->radiof_sufridera}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->profuFinal_CFC}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->profuFinal_mitadMB}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->profuFinal_PCO}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="text" class="input" style="background-color:#90F77E" value="{{$nPiezas->acetato_MB}}" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->ensamble}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->distancia_barrenoAli}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->profu_barrenoAliHembra}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->profu_barrenoAliMacho}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->altura_venaHembra}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->altura_venaMacho}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->ancho_vena}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->laterales}}" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input-medio" style="background-color:#90F77E" value="{{$nPiezas->pin1}}" step="any" inputmode="decimal" readonly><input type="number" class="input-medio" style="background-color:#90F77E" value="{{$nPiezas->pin2}}" step="any" inputmode="decimal" readonly></td>
                        <td> <input type="text" class="input" style="background-color:#90F77E" value="{{$nPiezas->error}}" readonly></td>
                        <td><textarea class="input" style="background-color:#90F77E" readonly>{{$nPiezas->observaciones}}</textarea></td>
                    </tr>
                    @endif
                    @endforeach
                    @endif
                    @if (isset($piezaUtilizar))
                    <tr>
                        <td> <input type="text" name="n_pieza" class="input" value="{{$piezaUtilizar->n_pieza}}" readonly></td>
                        <td> <input type="number" name="radiof_mordaza" class="input" step="any" inputmode="decimal" required></td>
                        <td> <input type="number" name="radiof_mayor" class="input" step="any" inputmode="decimal" required></td>
                        <td> <input type="number" name="radiof_sufridera" class="input" step="any" inputmode="decimal" required></td>
                        <td> <input type="number" name="profuFinal_CFC" class="input" step="any" inputmode="decimal" required></td>
                        <td> <input type="number" name="profuFinal_mitadMB" class="input" step="any" inputmode="decimal" required></td>
                        <td> <input type="number" name="profuFinal_PCO" class="input" step="any" inputmode="decimal" required></td>
                        <td>
                            <select name="acetato_MB" class="input">
                                <option value="Bien">Bien</option>
                                <option value="Mal">Mal</option>
                            </select>
                        </td>
                        <td><input type="number" name="ensamble" class="input" step="any" inputmode="decimal" required></td>
                        <td> <input type="number" name="distancia_barrenoAli" class="input" step="any" inputmode="decimal" required></td>
                        <td> <input type="number" name="profu_barrenoAliHembra" class="input" step="any" inputmode="decimal" required></td>
                        <td> <input type="number" name="profu_barrenoAliMacho" class="input" step="any" inputmode="decimal" required></td>
                        <td> <input type="number" name="altura_venaHembra" class="input" step="any" inputmode="decimal" required></td>
                        <td> <input type="number" name="altura_venaMacho" class="input" step="any" inputmode="decimal" required></td>
                        <td> <input type="number" name="ancho_vena" class="input" step="any" inputmode="decimal" required></td>
                        <td> <input type="number" name="laterales" class="input" step="any" inputmode="decimal" required></td>
                        <td> <input type="number" name="pin1" class="input-medio" step="any" inputmode="decimal" required><input type="number" name="pin2" class="input-medio" step="any" inputmode="decimal" required></td>
                        <td>
                            <select name="error" class="input">
                                <option value="0"></option>
                                <option value="Fundicion">Fundición.</option>
                            </select>
                        </td>
                        <td> <textarea class="input" name="observaciones"></textarea></td>
                    </tr>
                    @else
                    @include('layouts.partials.messages')
                    @endif
                    @endif
                </table>
            </div>
            @if (isset($cNominal) && isset($tolerancia))
            @if ($pzasRestantes > 0)
            <input class="btn" id="submit" type="submit" value="Siguiente Pieza">
            @endif
            @endif
        </form>
        @if (isset($nPiezas) && $nPiezas != "[]")
        <form action="{{ route('editCepillado')}}" method="post">
            @csrf
            <div class="editar-table" id="editar-table">
                <img src="{{ asset('images/editar.png')}}" alt="Desbloquear" id="edit-table" class="boton-editar-table">
                <input type="hidden" name="editar" value="1">
                <input type="hidden" name="ot" value="{{$ot->id}}">
                <input type="hidden" name="metaData" value="{{$meta->id}}">
            </div>
        </form>
        @endif
    </div>
    @endif
    @if (isset($band) && $band == 4)
    <div class="disabled-tabla">
        <form action="{{ route('editCepillado')}}" method="post">
            @csrf
            <input type="hidden" name="metaData" value="{{$meta->id}}">
            <div class="scrollabe-table">
                <table border="1" class="tabla3">
                    <!--Encabezado de la tabla Cepillado-->
                    <tr>
                        <th class="t-title" style="width:150px">#PZ</th>
                        <th class="t-title">Radio final de mordaza</th>
                        <th class="t-title">Radio final mayor</th>
                        <th class="t-title">Radio final de sufridera</th>
                        <th class="t-title">Profundidad final conexión Fondo/Corona</th>
                        <th class="t-title">Profundidad final mitad de Molde/Bombillo</th>
                        <th class="t-title">Profundidad final Pico/Conexión de obturador</th>
                        <th class="t-title">Acetato B/M</th><br>
                        <th class="t-title">Ensamble</th>
                        <th class="t-title">Distancia de barreno de alineación</th>
                        <th class="t-title">Profundidad de barreno de alineación Hembra</th>
                        <th class="t-title">Profundidad de barreno de alineación Macho</th>
                        <th class="t-title">Altura de vena Hembra</th>
                        <th class="t-title">Altura de vena Macho</th>
                        <th class="t-title">Ancho de vena</th>
                        <th class="t-title">Laterales</th>
                        <th class="t-title">PIN</th>
                        <th class="t-title" style="width:200px">Error</th>
                        <th class="t-title" style="width:700px">Observaciones</th>
                    </tr>
                    <tr>
                        <td>C.Nominal.</td>
                        <td><input type="number" value="{{$cNominal->radiof_mordaza}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->radiof_mayor}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->radiof_sufridera}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->profuFinal_CFC}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->profuFinal_mitadMB}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->profuFinal_PCO}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td></td>
                        <td><input type="number" value="{{$cNominal->ensamble}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->distancia_barrenoAli}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->profu_barrenoAliHembra}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->profu_barrenoAliMacho}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->altura_venaHembra}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->altura_venaMacho}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->ancho_vena}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" value="{{$cNominal->laterales}}" class="input" step="any" inputmode="decimal" readonly></td>
                        <td>
                            <input type="number" value="{{$cNominal->pin1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$cNominal->pin2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td> Tolerancias. </td>
                        <td>
                            <input type="number" value="{{$tolerancia->radiof_mordaza1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->radiof_mordaza2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td>
                            <input type="number" value="{{$tolerancia->radiof_mayor1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->radiof_mayor2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td>
                            <input type="number" value="{{$tolerancia->radiof_sufridera1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->radiof_sufridera2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td>
                            <input type="number" value="{{$tolerancia->profuFinal_CFC1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->profuFinal_CFC2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td>
                            <input type="number" value="{{$tolerancia->profuFinal_mitadMB1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->profuFinal_mitadMB2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td>
                            <input type="number" value="{{$tolerancia->profuFinal_PCO1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->profuFinal_PCO2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td></td>
                        <td>
                            <input type="number" value="{{$tolerancia->ensamble1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->ensamble2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->distancia_barrenoAli}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->distancia_barrenoAliHembra2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->profu_barrenoAliHembra1}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->profu_barrenoAliHembra2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->profu_barrenoAliMacho1}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->profu_barrenoAliMacho2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->altura_venaHembra1}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->altura_venaHembra2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->altura_venaMacho1}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->altura_venaMacho2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->ancho_vena1}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->ancho_vena2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="text" value="{{$tolerancia->laterales1}}" class="input-medio" readonly><input type="text" value="{{$tolerancia->laterales2}}" class="input-medio" readonly>
                        </td>
                        <td>
                            <input type="number" value="{{$tolerancia->pin1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->pin2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <!--Llenado d_b piezas-->
                    @if ($nPiezas->count() != 0)
                    @foreach ($nPiezas as $nPiezas)
                    <tr>
                        <td><input type="text" class="input" value="{{$nPiezas->n_pieza}}" name="n_pieza[]" step="any" inputmode="decimal" readonly></td>
                        <td><input type="number" class="input" value="{{$nPiezas->radiof_mordaza}}" name="radiof_mordaza[]" step="any" inputmode="decimal" required></td>
                        <td><input type="number" class="input" value="{{$nPiezas->radiof_mayor}}" name="radiof_mayor[]" step="any" inputmode="decimal" required></td>
                        <td><input type="number" class="input" value="{{$nPiezas->radiof_sufridera}}" name="radiof_sufridera[]" step="any" inputmode="decimal" required></td>
                        <td><input type="number" class="input" value="{{$nPiezas->profuFinal_CFC}}" name="profuFinal_CFC[]" step="any" inputmode="decimal" required></td>
                        <td><input type="number" class="input" value="{{$nPiezas->profuFinal_mitadMB}}" name="profuFinal_mitadMB[]" step="any" inputmode="decimal" required></td>
                        <td><input type="number" class="input" value="{{$nPiezas->profuFinal_PCO}}" name="profuFinal_PCO[]" step="any" inputmode="decimal" required></td>
                        <td>
                            <select name="acetato_MB[]" class="input">
                                <option value="{{$nPiezas->acetato_MB}}">{{$nPiezas->acetato_MB}}</option>
                                @switch($nPiezas->acetato_MB)
                                @case('Bien')
                                <option value="Mal">Mal.</option>
                                @break
                                @case('Mal')
                                <option value="Bien">Bien.</option>
                                @break
                                @endswitch
                            </select>
                        </td>
                        <td><input type="number" class="input" value="{{$nPiezas->ensamble}}" name="ensamble[]" step="any" inputmode="decimal" required></td>
                        <td><input type="number" class="input" value="{{$nPiezas->distancia_barrenoAli}}" name="distancia_barrenoAli[]" step="any" inputmode="decimal" required></td>
                        <td><input type="number" class="input" value="{{$nPiezas->profu_barrenoAliHembra}}" name="profu_barrenoAliHembra[]" step="any" inputmode="decimal" required></td>
                        <td><input type="number" class="input" value="{{$nPiezas->profu_barrenoAliMacho}}" name="profu_barrenoAliMacho[]" step="any" inputmode="decimal" required></td>
                        <td><input type="number" class="input" value="{{$nPiezas->altura_venaHembra}}" name="altura_venaHembra[]" step="any" inputmode="decimal" required></td>
                        <td><input type="number" class="input" value="{{$nPiezas->altura_venaMacho}}" name="altura_venaMacho[]" step="any" inputmode="decimal" required></td>
                        <td><input type="number" class="input" value="{{$nPiezas->ancho_vena}}" name="ancho_vena[]" step="any" inputmode="decimal" required></td>
                        <td><input type="number" class="input" value="{{$nPiezas->laterales}}" name="laterales[]" step="any" inputmode="decimal" required></td>
                        <td><input type="number" class="input-medio" value="{{$nPiezas->pin1}}" name="pin1[]" step="any" inputmode="decimal" required><input type="number" class="input-medio" value="{{$nPiezas->pin2}}" name="pin2[]" step="any" inputmode="decimal" required></td>
                        <td>
                            <select name="error[]" class="input">
                                <option value='{{$nPiezas->error}}'>{{$nPiezas->error}}</option>
                                @switch($nPiezas->error)
                                @case('Ninguno')
                                <option value="Fundicion">Fundición.</option>
                                @break
                                @case('Fundicion')
                                <option value="Ninguno">Ninguno.</option>
                                @break
                                @case('Maquinado')
                                <option value="Ninguno">Ninguno.</option>
                                <option value="Fundicion">Fundición.</option>
                                @break
                                @default
                                @endswitch
                            </select>
                        </td>
                        <td><textarea name="observaciones[]" class="input">{{$nPiezas->observaciones}}</textarea></td>
                    </tr>
                    @endforeach
                    @endif
                </table>
            </div>
            <input class="btn" id="submit" type="submit" value="Guardar">
        </form>
    </div>
    @endif
</div>
@endsection