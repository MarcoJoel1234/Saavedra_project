@extends('layouts.appMenu')

@section('head')
<title>Reporte diario de Asentado</title>
@vite(['resources/css/barreno.css', 'resources/js/editarInterfaz.js', 'resources/js/editarTabla.js'])
@endsection

@section('background-body', '/images/hola.jpg') <!--Body background Image-->
@section('content')

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
<div class="container">
    <!--Formulario en donde se guardara la meta de SoldaduraPTA-->
    <form action="{{route('saveHeader')}}" method="post">
        @csrf
        <input type="hidden" name="proceso" value="asentado">
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
                    <script>
                        let today = new Date().toISOString().split("T")[0];
                        document.getElementById("fecha").setAttribute("min", today);
                        document.getElementById("fecha").setAttribute("max", today);
                    </script>
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
                        <th> F- PRO - CPT</th>
                    </tr>
                    <tr>
                        <th>Versión</th>
                        <th> 05 </th>
                    </tr>
                    <tr>
                        <th>Fecha de revisión: </th>
                        <th> 23 - Agosto - 23</th>
                    </tr>
                </table>
            </div>
        </div>
    </form>
    <!--Formulario para los datos de la tabla-->
    @if (isset($band) && $band == 2)
    <div class="disabled-tabla">
        <form action="{{ route('asentadoHeader')}}" method="post">
            @csrf
            <input type="hidden" name="metaData" value="{{$meta->id}}">
            <div class="scrollabe-table">
                <table border="1" class="tabla3">
                    <tr>
                        <th class="t-title" style="width:150px">#PZ</th>
                        <th class="t-title">Sin juego</th>
                        <th class="t-title">Sin luz</th>
                        <th class="t-title">Error</th>
                        <th class="t-title" style="width:700px">Observaciones</th>
                    </tr>

                    <!--Llenado de piezas-->
                    @if (isset($nPiezas))
                    @if ($nPiezas->count() != 0)
                    @foreach ($nPiezas as $nPiezas)
                    @if ($nPiezas->sin_juego == "X" || $nPiezas->sin_luz == "X")
                    <tr>
                        <td><input type="text" class="input" style="background-color:#F36456" value="{{$nPiezas->n_juego}}" readonly></td>
                        <td><input type="text" class="input" style="background-color:#F36456" value="{{$nPiezas->sin_juego}}" readonly></td>
                        <td><input type="text" class="input" style="background-color:#F36456" value="{{$nPiezas->sin_luz}}" readonly></td>
                        <td><input type="text" class="input" style="background-color:#F36456" value="{{$nPiezas->error}}"></td>
                        <td><textarea class="input" style="background-color:#F36456" readonly>{{$nPiezas->observaciones}}</textarea></td>
                    </tr>
                    @else
                    <tr>
                        <td><input type="text" class="input" style="background-color:#90F77E" value="{{$nPiezas->n_juego}}" readonly></td>
                        <td><input type="text" class="input" style="background-color:#90F77E" value="{{$nPiezas->sin_juego}}" readonly></td>
                        <td><input type="text" class="input" style="background-color:#90F77E" value="{{$nPiezas->sin_luz}}" readonly></td>
                        <td><input type="text" class="input" style="background-color:#90F77E" value="{{$nPiezas->error}}"></td>
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
                        <td></td>
                    </tr>
                    @else
                    @include('layouts.partials.messages')
                    @endif
                    @if (isset($piezaElegida))
                    <tr>
                        <td> <input type="text" name="n_pieza" class="input" value="{{$piezaElegida->n_juego}}" readonly></td>
                        <td><select name="sin_juego" style="width: 100%">
                                <option value="✓">✓</option>
                                <option value="X">X</option>
                            </select>
                        </td>
                        <td>
                            <select name="sin_luz" style="width: 100%;">
                                <option value="✓">✓</option>
                                <option value="X">X</option>
                            </select>
                        </td>
                        <td></td>
                        <td> <textarea class="input" name="observaciones"></textarea></td>
                    </tr>
                    @endif
                    @endif
                </table>
                @if (isset($piezasUtilizar) && $pzasRestantes != 0 && !isset($piezaElegida))
                <input type="submit" value="Elegir pieza" class="btn">
                @endif
            </div>
            @if (isset($piezaElegida))
            <input class="btn" id="submit" type="submit" value="Siguiente Pieza">
            @endif
        </form>
        @if (isset($nPiezas) && $nPiezas != "[]")
        <form action="{{ route('editAsentado')}}" method="post">
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
        <form action="{{ route('editAsentado')}}" method="post">
            @csrf
            <input type="hidden" name="metaData" value="{{$meta->id}}">
            <div class="scrollabe-table">
                <table border="1" class="tabla3">
                    <!--Encabezado de la tabla  SoldaduraPTA-->
                    <tr>
                        <th class="t-title" style="width:150px">#PZ</th>
                        <th class="t-title">Sin juego</th>
                        <th class="t-title">Sin luz</th>
                        <th class="t-title">Error</th>
                        <th class="t-title" style="width:700px">Observaciones</th>
                    </tr>

                    <!--Llenado de piezas-->
                    @if ($nPiezas->count() != 0)
                    @foreach ($nPiezas as $nPiezas)
                    <tr>
                        <td><input type="text" class="input" value="{{$nPiezas->n_juego}}" name="n_pieza[]" step="any" inputmode="decimal" readonly></td>
                        <td>
                            <select name="sin_juego[]" class="input">
                                <option value="{{$nPiezas->sin_juego}}">{{$nPiezas->sin_juego}}</option>
                                @if ($nPiezas->sin_juego == "✓")
                                <option value="X">X</option>
                                @else
                                <option value="✓">✓</option>
                                @endif
                            </select>
                        </td>
                        <td>
                            <select name="sin_luz[]" class="input">
                                <option value="{{$nPiezas->sin_luz}}">{{$nPiezas->sin_luz}}</option>
                                @if ($nPiezas->sin_luz == "✓")
                                <option value="X">X</option>
                                @else
                                <option value="✓">✓</option>
                                @endif
                            </select>
                        </td>
                        <td><input type="text" class="input" value="{{$nPiezas->error}}" readonly></td>
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