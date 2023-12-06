@extends('layouts.app')
@section('content')

<head>
    <title>Segunda Operacion Soldadura</title>
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

<body background="{{ asset('images/hola.jpg') }}">
    <div class="container">
        <form action="{{route('saveHeader')}}" method="post">
            @csrf
            <input type="hidden" name="proceso" value="segundaOpeSoldadura">
            <div class="container-header">
                <div class="datos">
                    @include('layouts.partials.messages')
                    @if ((isset($band) && $band == 2) && (!isset($nPiezas) || $nPiezas == "[]"))
                        <div id="editarHeader">
                            <button type="submit" class="boton-editar" id="edit-header">
                                <img src="{{ asset('images/editar.png')}}" id="desbloquear" alt="Desbloquear">
                            </button>
                        </div>
                    @endif
                    
                    <div class="input-datos">
                        <label>Matricula del operador:</label>
                        <input type="text" value="{{auth()->user()->matricula}}" name="id_usuario" style="cursor:auto;" readonly>
                    </div>
                    <!--Si la bandera tiene un valor1-->
                    @if (isset($band) && $band == 1 || isset($band) && $band == 2 || isset($band) && $band == 4)
                        <div class="input-datos">
                            <label>Orden de trabajo:</label>
                            <input type="text" name="ot" value="{{$meta->id_ot}}" style="cursor:auto;" readonly>
                        </div>
                        <div class="input-datos">
                            <label for="hora" style="padding-right: 10px;">Hora de inicio:</label>
                            <label for="hora">Hora de termino:</label><br>
                            <input type="time" id="hora" name="h_inicio" value="{{$meta->h_inicio}}" style="cursor:auto;" readonly>
                            <input type="time" id="hora" name="h_termino" value="{{$meta->h_termino}}" style="cursor:auto;"readonly>
                        </div>
                        <div class="input-datos">
                            <label for="fecha">Fecha:</label>
                            <input type="date" id="fecha" name="fecha" value="{{$meta->fecha}}" style="cursor:auto;" readonly>
                        </div>
                        <div class="input-datos">
                            <label for="maquina">Máquina:</label>
                            <input type="text" id="maquina" value="Maquina {{$meta->maquina}}" style="cursor:auto;" readonly>
                        </div>
                        <!--Si la bandera no tiene un valor1-->
                    @else
                        @if (isset($band) && $band == 3)
                            <div class="input-datos">
                                <label>Orden de trabajo:</label>
                                <input type="text" name="ot" value="{{$meta->id_ot}}" style="cursor:auto;" readonly>
                                <input type="hidden" name="band" value="3">
                                <input type="hidden" name="meta" value="{{$meta->id}}">
                            </div>
                        @else
                            <div class="input-datos">
                                <label>Orden de trabajo:</label>
                                <select id="datos" name="ot">
                                @foreach($ot as $ot)
                                    <option value="{{$ot->id}}">{{$ot->id}}</option>
                                @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="input-datos">
                                <label for="hora" style="padding-right: 10px;">Hora de inicio:</label>
                                <label for="hora">Hora de termino:</label><br>
                                <input type="time" id="hora" name="h_inicio" required>
                                <input type="time" id="hora" name="h_termino" required>
                        </div>
                        <div class="input-datos">
                            <label for="fecha">Fecha:</label>
                            <input type="date" id="fecha" name="fecha" required>
                        </div>
                        <div class="input-datos">
                            <label for="maquina" style="padding-right: 10px;">Selecciona tu máquina:</label>
                            <select name="maquina" class="input">
                                <option value="1">Máquina 1</option>
                                <option value="2">Máquina 2</option>
                                <option value="3">Máquina 3</option>
                                <option value="4">Máquina 4</option>
                                <option value="5">Máquina 5</option>
                                <option value="6">Máquina 6</option>
                                <option value="7">Máquina 7</option>
                            </select>       
                        </div>
                    @endif 
                    <!-- Botón aceptar -->
                        <div class="input-datos" id="div-btn-accept">
                            <button id="btn-accept" style="margin-left:70px;">Aceptar</button><br>
                        </div>

                        <!-- Campos deshabilitados -->
                        <div class="disabled">
                            <div class="input-datos">
                                @if (isset($band) && $band == 1 || isset($band) && $band == 2 || isset($band) && $band == 4)
                                    <label>Nombre de la moldura:</label>
                                    <input type="text" value="{{$moldura}}" style="width: 100%; cursor:auto;" readonly>
                                @else
                                    <label>Nombre de la moldura: No se ha encontrado el nombre de la moldura</label><br>
                                @endif
                            </div>
                            <div class="input-datos" id="div-clases">
                                <p>
                                    Clases:<br>
                                    @if (isset($clases) && isset($band) && $band == 1)
                                        @foreach($clases as $clases)
                                            @if ($clases == "Bombillo")
                                                <input type="radio" name="clases" id="bombillo" value="{{$clases}}">
                                                <label>{{$clases}}</label>
                                            @elseif ($clases == "Molde")
                                                <input type="radio" name="clases" id="molde" value="{{$clases}}">
                                                <label>{{$clases}}</label>
                                            @else
                                                <input type="radio" name="clases" class="sn-tamaños" value="{{$clases}}">
                                                <label>{{$clases}}</label>
                                            @endif
                                        @endforeach
                                    @endif
                                    @if (isset($meta) && isset($band) && $band == 2 || isset($band) && $band == 4)
                                        <label class="clases">{{$clase->nombre}} {{$clase->tamanio}}</label>
                                        <input type="hidden" name="clases" value="{{$meta->clase}}">
                                        <input type="hidden" name="tamaño" value="{{$meta->tamaño}}">
                                        <input type="hidden" name="vista" value='true'>
                                        <label class="clases">{{$juegos}} juegos</label>
                                    @endif
                                </p>
                            </div> 
                            <button class="btn" id="btn-class">Siguiente</button>
                        </div>
                </div>
                <div class="div-tabla2">
                    <table border="1" id="tabla2">
                        <tr>
                            <th id="col1">Tiempo estandar.</th>
                            <th id="col2">Meta de juegos.</th>
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
                            <th> 5 </th>
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
                <form action="{{ route('segundaOpeSoldaduraHeader')}}" method="post">
                    @csrf
                    <input type="hidden" name="metaData" value="{{$meta->id}}">
                    <div class="scrollabe-table">
                        <table border="1" class="tabla3">
                            <tr>
                                <th class="t-title" style="width:150px">#PZ</th>
                                <th class="t-title">Diametro 1 </th>
                                <th class="t-title">Profundidad 1</th>
                                <th class="t-title">Diametro 2</th>
                                <th class="t-title">Profundidad 2</th>
                                <th class="t-title">Diametro 3</th>
                                <th class="t-title">Profundidad 3</th>
                                <th class="t-title">Diametro de soldadura</th><br>
                                <th class="t-title">Profundidad de soldadura</th>
                                <th class="t-title">Altura total</th>
                                <th class="t-title">Simetría a 90° </th><br>
                                <th class="t-title">Simetría línea de partida</th>
                                <th class="t-title" style="width:200px">Error</th><br>
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
                                    <td><input type="number" class="input" disabled></td>
                                    <td><input type="number" class="input" disabled></td>
                                    <td><input type="number" class="input" disabled></td>
                                    <td><input type="number" class="input" disabled></td>
                                    <td><input type="number" class="input" disabled></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                
                                <tr>
                                    <td> Tolerancias. </td>
                                    <td><input type="number" class="input" disabled></td>
                                    <td><input type="number" class="input" disabled></td>
                                    <td><input type="number" class="input" disabled></td>
                                    <td><input type="number" class="input" disabled></td>
                                    <td><input type="number" class="input" disabled></td>
                                    <td><input type="number" class="input" disabled></td>
                                    <td><input type="number" class="input" disabled></td>
                                    <td><input type="number" class="input" disabled></td>
                                    <td><input type="number" class="input-medio" disabled><input type="number" class="input-medio" disabled></td>
                                    <td><input type="number" class="input-medio" disabled><input type="number" class="input-medio" disabled></td>
                                    <td><input type="number" class="input" disabled></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @else 
                                <tr> 
                                    <td>C.Nominal.</td>
                                    <td><input type="number" value="{{$cNominal->diametro1}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->profundidad1}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->diametro2}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->profundidad2}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->diametro3}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->profundidad3}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->diametroSoldadura}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->profundidadSoldadura}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->alturaTotal}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->simetria90G}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$cNominal->simetriaLinea_Partida}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td> Tolerancias. </td>
                                    <td><input type="number" value="{{$tolerancia->diametro1}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$tolerancia->profundidad1}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$tolerancia->diametro2}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$tolerancia->profundidad2}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$tolerancia->diametro3}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$tolerancia->profundidad3}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$tolerancia->diametroSoldadura}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td><input type="number" value="{{$tolerancia->profundidadSoldadura}}" class="input" step="any" inputmode="decimal" readonly></td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->alturaTotal1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->alturaTotal2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td>
                                        <input type="number" value="{{$tolerancia->simetria90G1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->simetria90G2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                    </td>
                                    <td><input type="number" value="{{$tolerancia->simetriaLinea_Partida}}" class="input" step="any" inputmode="decimal" readonly></td>
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
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->diametro1}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->profundidad1}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->diametro2}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->profundidad2}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->diametro3}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->profundidad3}}" step="any" inputmode="decimal" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->diametroSoldadura}}" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->profundidadSoldadura}}" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->alturaTotal}}" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->simetria90G}}" readonly></td>
                                                <td><input type="number" class="input" style="background-color:#F36456" value="{{$nPiezas->simetriaLinea_Partida}}" readonly></td>
                                                <td><input type="text" class="input" style="background-color:#F36456" value="{{$nPiezas->error}}" readonly></td>
                                                <td><textarea class="input" style="background-color:#F36456" readonly>{{$nPiezas->observaciones}}</textarea></td>
                                            </tr>
                                        @else
                                        <tr>
                                            <td><input type="text" class="input" style="background-color:#90F77E" value="{{$nPiezas->n_pieza}}" step="any" inputmode="decimal" readonly></td>
                                            <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->diametro1}}" step="any" inputmode="decimal" readonly></td>
                                            <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->profundidad1}}" step="any" inputmode="decimal" readonly></td>
                                            <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->diametro2}}" step="any" inputmode="decimal" readonly></td>
                                            <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->profundidad2}}" step="any" inputmode="decimal" readonly></td>
                                            <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->diametro3}}" step="any" inputmode="decimal" readonly></td>
                                            <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->profundidad3}}" step="any" inputmode="decimal" readonly></td>
                                            <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->diametroSoldadura}}" readonly></td>
                                            <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->profundidadSoldadura}}" readonly></td>
                                            <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->alturaTotal}}" readonly></td>
                                            <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->simetria90G}}" readonly></td>
                                            <td><input type="number" class="input" style="background-color:#90F77E" value="{{$nPiezas->simetriaLinea_Partida}}" readonly></td>
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
                                    </tr>
                                @else
                                    @include('layouts.partials.messages')
                                @endif
                                @if (isset($piezaElegida))
                                    <tr>
                                        <td> <input type="text" name="n_pieza" class="input" value="{{$piezaElegida->n_pieza}}" readonly></td>
                                        <td> <input type="number" name="diametro1" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="profundidad1" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="diametro2" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type= "number" name="profundidad2" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type= "number" name="diametro3" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="profundidad3" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="diametroSoldadura" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="profundidadSoldadura" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="alturaTotal" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="simetria90G" class="input" step="any" inputmode="decimal" required></td>
                                        <td> <input type="number" name="simetriaLinea_Partida" class="input" step="any" inputmode="decimal" required></td>
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
                        @if (isset($piezasUtilizar) && $juegos != 0 && !isset($piezaElegida))
                            <input type="submit" value="Elegir pieza" class="btn">
                        @endif
                    </div>
                    @if ((isset($cNominal) && isset($tolerancia)) && isset($piezaElegida))
                        <input class="btn" id="submit" type="submit" value="Siguiente Pieza">
                    @endif
                </form>
                @if (isset($nPiezas) && $nPiezas != "[]")
                    <form action="{{ route('editSegundaOpeSoldadura')}}" method="post">
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
                <form action="{{ route('editSegundaOpeSoldadura')}}" method="post">
                    @csrf
                    <input type="hidden" name="metaData" value="{{$meta->id}}">
                    <div class="scrollabe-table">
                        <table border="1" class="tabla3">
                            <!--Encabezado de la tabla Cepillado--> 
                            <tr>
                            <th class="t-title" style="width:150px">#PZ</th>
                                <th class="t-title">Diametro 1 </th>
                                <th class="t-title">Profundidad 1</th>
                                <th class="t-title">Diametro 2</th>
                                <th class="t-title">Profundidad 2</th>
                                <th class="t-title">Diametro 3</th>
                                <th class="t-title">Profundidad 3</th>
                                <th class="t-title">Diametro de soldadura</th><br>
                                <th class="t-title">Profundidad de soldadura</th>
                                <th class="t-title">Altura total</th>
                                <th class="t-title">Simetría a 90° </th><br>
                                <th class="t-title">Simetría línea de partida</th>
                                <th class="t-title" style="width:200px">Error</th><br>
                                <th class="t-title" style="width:700px">Observaciones</th>
                            </tr>
                            <td>C.Nominal.</td>
                                <td><input type="number" value="{{$cNominal->diametro1}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$cNominal->profundidad1}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$cNominal->diametro2}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$cNominal->profundidad2}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$cNominal->diametro3}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$cNominal->profundidad3}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$cNominal->diametroSoldadura}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$cNominal->profundidadSoldadura}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$cNominal->alturaTotal}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$cNominal->simetria90G}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$cNominal->simetriaLinea_Partida}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td> Tolerancias. </td>
                                <td><input type="number" value="{{$tolerancia->diametro1}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$tolerancia->profundidad1}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$tolerancia->diametro2}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$tolerancia->profundidad2}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$tolerancia->diametro3}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$tolerancia->profundidad3}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$tolerancia->diametroSoldadura}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$tolerancia->profundidadSoldadura}}" class="input" step="any" inputmode="decimal" readonly></td>
                                <td>
                                    <input type="number" value="{{$tolerancia->alturaTotal1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->alturaTotal2}}" class="input-medio" step="any" inputmode="decimal" readonly>
                                </td>
                                <td><input type="number" value="{{$tolerancia->simetria90G1}}" class="input-medio" step="any" inputmode="decimal" readonly><input type="number" value="{{$tolerancia->simetria90G2}}" class="input-medio" step="any" inputmode="decimal" readonly></td>
                                <td><input type="number" value="{{$tolerancia->simetriaLinea_Partida}}" class="input-medio" step="any" inputmode="decimal" readonly></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <!--Llenado de piezas-->S
                            @if ($nPiezas->count() != 0)
                                @foreach ($nPiezas as $nPiezas)
                                    <tr>
                                        <td><input type="text" class="input" value="{{$nPiezas->n_pieza}}" name="n_pieza[]" step="any" inputmode="decimal" readonly></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->diametro1}}" name="diametro1[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->profundidad1}}" name="profundidad1[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->diametro2}}" name="diametro2[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->profundidad2}}" name="profundidad2[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->diametro3}}" name="diametro3[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->profundidad3}}" name="profundidad3[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->diametroSoldadura}}" name="diametroSoldadura[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->profundidadSoldadura}}" name="profundidadSoldadura[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->alturaTotal}}" name="alturaTotal[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->simetria90G}}" name="simetria90G[]" step="any" inputmode="decimal" required></td>
                                        <td><input type="number" class="input" value="{{$nPiezas->simetriaLinea_Partida}}" name="simetriaLinea_Partida[]" step="any" inputmode="decimal" required></td>
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