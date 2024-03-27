@extends('layouts.app')
@section('content')

<head>
    <title>Revisión acabados bombillo</title>
    @vite('resources/css/cepillado.css')
    @vite('resources/js/editarInterfaz.js')
    @vite('resources/js/editarTabla.js')
</head>
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
@if((isset($pzasRestantes) && $pzasRestantes == 0) && $band != 4)
    <script>
        alert("Se han registrado todas las piezas");
    </script>
@endif
<body background="{{ asset('images/hola.jpg') }}">
    <div class="container">
        <!--Formulario en donde se guardara la meta de desbaste-->
        <form action="{{route('saveHeader')}}" method="post">
            @csrf
            <input type="hidden" name="proceso" value="acabadoBombillo">
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
                            <input type="time" id="hora" name="h_termino" value="{{$meta->h_termino}}" style="cursor:auto; margin-left:5%;"readonly>
                        </div>
                        <div class="input-datos">
                            <label for="fecha" style="padding-right: 60px; margin-left:50px;">Fecha:</label>
                            <label for="fecha">Máquina:</label><br>
                            <input type="date" name="fecha" value="{{$meta->fecha}}" style="cursor:auto;" readonly>
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
                                    <label for="pedido" style="margin-left: 20px;">Juegos restantes:</label><br>
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
                                <td id="celda2"><input type="text" value="{{$meta->resultado}}"style="cursor:auto;" readonly></td>
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
                <form action="{{ route('acabadoBombilloHeader')}}" method="post">
                    @csrf
                    <input type="hidden" name="metaData" value="{{$meta->id}}">
                    <div class="scrollabe-table">
                        <table border="1" class="tabla3">
                            <tr>
                                <th class="t-title" style="width:150px">#PZ</th>
                                <th class="t-title">Diametro de mordaza</th>
                                <th class="t-title">Diametro de ceja</th>
                                <th class="t-title">Diametro de sufridera</th>
                                <th class="t-title">Altura de mordaza</th>
                                <th class="t-title">Altura de ceja</th>
                                <th class="t-title">Altura de sufridera</th>
                                <th class="t-title">Gauje ceja</th>
                                <th class="t-title">Gauje corona</th>
                                <th class="t-title">Gauje llanta</th>
                                <th class="t-title">Altura total</th>
                                <th class="t-title">Diametro boca</th>
                                <th class="t-title">Diametro asiento corona</th>
                                <th class="t-title">Diametro llanta</th>
                                <th class="t-title">Diametro caja corona</th>
                                <th class="t-title">Profundidad corona</th>
                                <th class="t-title">Angulo de 30°</th>
                                <th class="t-title">Profundidad caja corona</th>
                                <th class="t-title">Simetría</th>
                                <th class="t-title" style="width:200px">Error</th>
                                <th class="t-title" style="width:700px">Observaciones</th>
                            </tr>

                            @if(!isset($cNominal))
                                <tr>
                                    <td>C.Nominal.</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                
                                <tr>
                                    <td> Tolerancias. </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @else 
                                <tr> 
                                    <td>C.Nominal.</td>
                                    <td><input type="number" value="{{$cNominal->diametro_mordaza}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->diametro_ceja}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->diametro_sufridera}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->altura_mordaza}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->altura_ceja}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->altura_sufridera}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><input type="number" value="{{$cNominal->diametro_boca}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->diametro_asiento_corona}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->diametro_llanta}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->diametro_caja_corona}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->profundidad_corona}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->angulo_30}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->profundidad_caja_corona}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->simetria}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td> Tolerancias. </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->diametro_mordaza1}}" class="input-medio" name="diametro_mordaza1" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_mordaza2}}" class="input-medio" name="diametro_mordaza2" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->diametro_ceja1}}" class="input-medio" name="diametro_ceja1" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_ceja2}}" class="input-medio" name="diametro_ceja2" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->diametro_sufridera1}}" class="input-medio" name="diametro_sufridera1" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_sufridera2}}" class="input-medio" name="diametro_sufridera2" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->altura_mordaza1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->altura_mordaza2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->altura_ceja1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->altura_ceja2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->altura_sufridera1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->altura_sufridera2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->diametro_boca1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_boca2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->diametro_asiento_corona1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_asiento_corona2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->diametro_llanta1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_llanta2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
				                    <td>
                                        <input type="number" value="{{$tolerancia->diametro_caja_corona1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_caja_corona2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->profundidad_corona1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->profundidad_corona2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->angulo_301}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->angulo_302}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->profundidad_caja_corona1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->profundidad_caja_corona2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->simetria1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->simetria2}}" class="input-medio" step="any" inputmode="decimal" readonly>
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
                                                <td><input type="text" class="input"  value="{{$nPiezas->n_juego}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->diametro_mordaza}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->diametro_ceja}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->diametro_sufridera}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->altura_mordaza}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->altura_ceja}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->altura_sufridera}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="text" class="input" style="background-color:#F36456" value="{{$nPiezas->gauge_ceja}}" readonly></td>
                                                <td><input type="text" class="input" style="background-color:#F36456" value="{{$nPiezas->gauge_corona}}" readonly></td>
                                                <td><input type="text" class="input" style="background-color:#F36456" value="{{$nPiezas->gauge_llanta}}" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->altura_total}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->diametro_boca}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->diametro_asiento_corona}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->diametro_llanta}}" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->diametro_caja_corona}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->profundidad_corona}}" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->angulo_30}}" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->profundidad_caja_corona}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->simetria}}" readonly></td>
                                                <td><input type="text" class="input" style="background-color:#F36456" value="{{$nPiezas->error}}" readonly></td>
                                                <td><textarea class="input" style="background-color:#F36456" readonly>{{$nPiezas->observaciones}}</textarea></td>
                                            </tr>
                                        @else  
                                            <tr>
                                                <td><input type="text" class="input"  value="{{$nPiezas->n_juego}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->diametro_mordaza}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->diametro_ceja}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->diametro_sufridera}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->altura_mordaza}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->altura_ceja}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->altura_sufridera}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="text" class="input" style="background-color:#90F77E" value="{{$nPiezas->gauge_ceja}}" readonly></td>
                                                <td><input type="text" class="input" style="background-color:#90F77E" value="{{$nPiezas->gauge_corona}}" readonly></td>
                                                <td><input type="text" class="input" style="background-color:#90F77E" value="{{$nPiezas->gauge_llanta}}" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->altura_total}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->diametro_boca}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->diametro_asiento_corona}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->diametro_llanta}}" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->diametro_caja_corona}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->profundidad_corona}}" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->angulo_30}}" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->profundidad_caja_corona}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->simetria}}" readonly></td>
                                                <td><input type="text" class="input" style="background-color:#90F77E" value="{{$nPiezas->error}}" readonly></td>
                                                <td><textarea class="input" style="background-color:#90F77E" readonly>{{$nPiezas->observaciones}}</textarea></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                                @if ((isset($piezasUtilizar) && count($piezasUtilizar) != 0) && !isset($piezaElegida))
                                    <tr>
                                        <td> 
                                            <select name="n_juegoElegido" class="input">
                                                @foreach ($piezasUtilizar as $piezasUtilizar)
                                                    <option value="{{$piezasUtilizar}}">{{$piezasUtilizar}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                        <td> </td>
                                    </tr>
                                @else
                                    @include('layouts.partials.messages')
                                @endif
                                @if (isset($piezaElegida))
                                    <tr>
                                        <td> <input type="text" name="n_pieza" class="input" value="{{$piezaElegida->n_juego}}" readonly></td>
                                        <td> <input type="number" name="diametro_mordaza" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="diametro_ceja" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="diametro_sufridera" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type= "number" name="altura_mordaza" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type= "number" name="altura_ceja" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="altura_sufridera" class="input" step="any" inputmode="decimal" required></td>
                                        <td>
                                            <select class="input" name="gauge_ceja">
                                                <option value="✓">✓</option>
                                                <option value="X">X</option>
                                            </select>
                                        </td>
                                        <td> <input type="number" name="gauge_corona" class="input" step="any" inputmode="decimal" required></td>
                                        <td>
                                            <select class="input" name="gauge_llanta">
                                                <option value="✓">✓</option>
                                                <option value="X">X</option>
                                            </select>
                                        </td>
                                        <td> <input type="number" name="altura_total" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="diametro_boca" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type= "number" name="diametro_asiento_corona" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type= "number" name="diametro_llanta" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="diametro_caja_corona" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="profundidad_corona" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="angulo_30" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="profundidad_caja_corona" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="simetria" class="input" step="any" inputmode="decimal" required></td>
                                        <td> 
                                            <select name="error" class="input">
                                                <option value="0"></option>
                                                <option value="Fundicion">Fundición</option>
                                            </select>
                                        </td>
                                        <td> <textarea class="input" name="observaciones"></textarea></td>
                                    </tr>
                                @endif
                            @endif
                        </table>
                        @if (isset($piezasUtilizar) && $pzasRestantes != 0 && !isset($piezaElegida))
                            <input type="submit" value="Elegir pieza" class="btn">
                        @endif
                    </div>
                    @if ((isset($cNominal) && isset($tolerancia)) && isset($piezaElegida))
                        <input class="btn" id="submit" type="submit" value="Siguiente Pieza">
                    @endif
                </form>
                @if (isset($nPiezas) && $nPiezas != "[]")
                    <form action="{{ route('editAcabadoBombillo')}}" method="post">
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
                <form action="{{ route('editAcabadoBombillo')}}" method="post">
                    @csrf
                    <input type="hidden" name="metaData" value="{{$meta->id}}">
                    <div class="scrollabe-table">
                        <table border="1" class="tabla3">
                            <!--Encabezado de la tabla Acabados bombillo-->
                            <tr>
                                <th class="t-title" style="width:150px">#PZ</th>
                                <th class="t-title">Diametro de mordaza</th>
                                <th class="t-title">Diametro de ceja</th>
                                <th class="t-title">Diametro de sufridera</th>
                                <th class="t-title">Altura de mordaza</th>
                                <th class="t-title">Altura de ceja</th>
                                <th class="t-title">Altura de sufridera</th>
                                <th class="t-title">Gauge ceja</th>
                                <th class="t-title">Gauge corona</th>
                                <th class="t-title">Gauge llanta</th>
                                <th class="t-title">Altura total</th>
                                <th class="t-title">Diametro boca</th>
                                <th class="t-title">Diametro asiento corona</th>
                                <th class="t-title">Diametro llanta</th>
                                <th class="t-title">Diametro caja corona</th>
                                <th class="t-title">Profundidad corona</th>
                                <th class="t-title">Angulo de 30°</th>
                                <th class="t-title">Profundidad caja corona</th>
                                <th class="t-title">Simetría</th>
                                <th class="t-title" style="width:200px">Error</th>
                                <th class="t-title" style="width:700px">Observaciones</th>
                            </tr>
                            <tr> 
                                <td>C.Nominal.</td>
                                    <td><input type="number" value="{{$cNominal->diametro_mordaza}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->diametro_ceja}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->diametro_sufridera}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->altura_mordaza}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->altura_ceja}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->altura_sufridera}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><input type="number" value="{{$cNominal->diametro_boca}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->diametro_asiento_corona}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->diametro_llanta}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->diametro_caja_corona}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->profundidad_corona}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->angulo_30}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->profundidad_caja_corona}}" class="input" step="any" inputmode="decimal" readonly></td><td><input type="number" value="{{$cNominal->simetria}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td></td>
                                    <td></td>
                            </tr>
                            <tr>
                            <td> Tolerancias. </td>
                            <td>
                                    <input type="number" value="{{$tolerancia->diametro_mordaza1}}" class="input-medio" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_mordaza2}}" class="input-medio" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->diametro_ceja1}}" class="input-medio" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_ceja2}}" class="input-medio" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->diametro_sufridera1}}" class="input-medio" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_sufridera2}}" class="input-medio" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->altura_mordaza1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->altura_mordaza2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->altura_ceja1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->altura_ceja2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->altura_sufridera1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->altura_sufridera2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->diametro_boca1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_boca2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->diametro_asiento_corona1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_asiento_corona2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->diametro_llanta1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_llanta2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
				                    <td>
                                        <input type="number" value="{{$tolerancia->diametro_caja_corona1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->diametro_caja_corona2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->profundidad_corona1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->profundidad_corona2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->angulo_301}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->angulo_302}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->profundidad_caja_corona1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->profundidad_caja_corona2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->simetria1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->simetria2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td></td>
                                    <td></td>
                            </tr>
                            <!--Llenado de piezas-->
                            @if ($nPiezas->count() != 0)
                                @foreach ($nPiezas as $nPiezas)
                                    <tr>
                                        <td><input type="text" class="input" value="{{$nPiezas->n_juego}}" name="n_pieza[]" step="any" inputmode="decimal" readonly></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->diametro_mordaza}}" name="diametro_mordaza[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->diametro_ceja}}" name="diametro_ceja[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->diametro_sufridera}}" name="diametro_sufridera[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->altura_mordaza}}" name="altura_mordaza[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->altura_ceja}}" name="altura_ceja[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->altura_sufridera}}" name="altura_sufridera[]" step="any" inputmode="decimal" required></td>
                                        <td>
                                            <select class="input" name="gauge_ceja[]">
                                                <option value="{{$nPiezas->gauge_ceja}}">{{$nPiezas->gauge_ceja}}</option>
                                                @if ($nPiezas->gauge_ceja == "X")
                                                    <option value="✓">✓</option>
                                                @else
                                                    <option value="X">X</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td><input type="number" class="input" value="{{$nPiezas->gauge_corona}}" name="gauge_corona[]" step="any" inputmode="decimal" required></td>
                                        <td>
                                            <select class="input" name="gauge_llanta[]">
                                                <option value="{{$nPiezas->gauge_llanta}}">{{$nPiezas->gauge_llanta}}</option>
                                                @if ($nPiezas->gauge_llanta == "X")
                                                    <option value="✓">✓</option>
                                                @else
                                                    <option value="X">X</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td><input type="number" class="input" value="{{$nPiezas->altura_total}}" name="altura_total[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->diametro_boca}}" name="diametro_boca[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->diametro_asiento_corona}}" name="diametro_asiento_corona[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->diametro_llanta}}" name="diametro_llanta[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->diametro_caja_corona}}" name="diametro_caja_corona[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->profundidad_corona}}" name="profundidad_corona[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->angulo_30}}" name="angulo_30[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->profundidad_caja_corona}}" name="profundidad_caja_corona[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->simetria}}" name="simetria[]" step="any" inputmode="decimal" required></td>
                                        <td> 
                                            <select name="error[]" class="input">
                                                <option value='{{$nPiezas->error}}'>{{$nPiezas->error}}</option>
                                                @switch($nPiezas->error)
                                                    @case('Ninguno')
                                                        <option value="Fundicion">Fundición</option>
                                                    @break
                                                    @case('Fundicion')
                                                        <option value="Ninguno">Ninguno</option>
                                                    @break
                                                    @case('Maquinado')
                                                        <option value="Ninguno">Ninguno</option>
                                                        <option value="Fundicion">Fundición</option>
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
</body>
@endsection