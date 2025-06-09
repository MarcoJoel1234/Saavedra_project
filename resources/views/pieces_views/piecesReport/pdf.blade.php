<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Piezas PDF</title>
</head>

<body>
    <style>
        .contenedor {
            text-align: center;
            margin: 0 auto;
        }

        .title_ot {

            font-size: 24px;
            font-weight: bold;
            color: blue;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }

        table {
            margin-left: 0px;
            margin-top: 20px;
            width: 80%;
            border: 1px solid;
            border-collapse: collapse;
            /* Colapsa los bordes de las celdas */
            width: 100%;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }

        .etiquetas {
            color: black;
            font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
            font-size: 24px;
        }

        label {
            margin-right: 10px;
            /* Agrega un margen derecho para separar las etiquetas */
            font-size: 16px;
            /* Tamaño de fuente */
            color: #333;
            /* Color del texto */
            display: inline-block;
        }

        .contenedor {
            display: flex;
            justify-content: flex-end;
            /* Centra horizontalmente el contenido */
            width: 100%;
            height: 100vh;
            /* Altura del viewport */
        }

        img {
            max-width: 100%;
            /* Ajusta el tamaño máximo de la imagen al ancho del contenedor */
            max-height: 100%;
            /* Ajusta el tamaño máximo de la imagen a la altura del contenedor */
        }

        tr,
        td {
            text-align: center;
            padding: 5px;
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
        }
    </style>
    <div class="contenedor">
        <label class="title_ot">Orden de trabajo: {{ $workOrder->id }}</label><br>
        <label class="title_ot">Clase: {{ $class->nombre }}</label>
    </div>
    <div class="etiquetas">
        <label for="etiqueta2">Operador: {{ $array[0] }}</label><br>
        <label for="etiqueta3">Máquina: {{ $array[1] }}</label><br>
        <label for="etiqueta4">Proceso: {{ $array[2] }}</label><br>
        <label for="etiqueta5">Error: {{ $array[3] }}</label><br>
        <label for="etiqueta6">Fecha: {{ $array[4] }}</label><br>

    </div>
    <table border="1" id="table">
        <thead>
            <tr>
                <th>N_pieza</th>
                <th>Nombre del operador</th>
                <th>Máquina</th>
                <th>Proceso</th>
                @foreach ($piezas as $pieza)
                    @if ($pieza[4] == 'Operacion Equipo')
                        <th>Operación</th>
                        @php
                            $band = true;
                        @endphp
                    @break
                @endif
            @endforeach
            <th>Errores</th>
            <th>Fecha de máquinado</th>
            @if ($profile == "quality")
                <th>Fecha de liberación</th>
                <th>Liberado por</th>
            @endif
        </tr>
    </thead>

    @for ($i = 0; $i < count($piezas); $i++) ¿
        @if ($piezas[$i][4] == 'Operacion Equipo')
            @if ($perfil == "quality")
                @if ($piezas[$i][10] == 1)    
                    <tr style="background-color: #acf980a8">
                @elseif ($piezas[$i][6] == 'Incompleto')
                    <tr style="background-color: #f9f9a8">
                @elseif ($piezas[$i][10] == 2)
                    <tr style="background-color: #ec7163cd">
                @else
                    <tr>
                @endif
            @else
                @if ($piezas[$i][6] == "Ninguno")    
                    <tr style="background-color: #acf980a8">
                @elseif ($piezas[$i][6] == 'Incompleto')
                    <tr style="background-color: #f9f9a8">
                @elseif($piezas[$i][6] != "Ninguno")
                    <tr style="background-color: #ec7163cd">
                @else
                    <tr>
                @endif
            @endif
        
            @if ($perfil == "quality")
                @for ($j = 1; $j < count($piezas[$i]) - 1; $j++)
                    <td>{{ $piezas[$i][$j] }}</td>
                @endfor
            @else
                @for ($j = 1; $j < count($piezas[$i]) - 4; $j++)
                    <td>{{ $piezas[$i][$j] }}</td>
                @endfor
            @endif
            </tr>
        @elseif (isset($band))
            @if($perfil == "quality")
                @if ($piezas[$i][9] == 1)
                    <tr style="background-color: #acf980a8">
                @elseif ($piezas[$i][5] == 'Incompleto')
                    <tr style="background-color: #f9f9a8">
                @elseif ($piezas[$i][9] == 2)
                    <tr style="background-color: #ec7163cd">
                @else
                    <tr>
                @endif
            @else
                @if ($piezas[$i][5] == "Ninguno")
                    <tr style="background-color: #acf980a8">
                @elseif ($piezas[$i][5] == 'Incompleto')
                    <tr style="background-color: #f9f9a8">
                @elseif($piezas[$i][5] != 'Ninguno')
                    <tr style="background-color: #ec7163cd">
                @else
                    <tr>
                @endif
            @endif
            @if ($profile == "quality")
                @for ($j = 1; $j < count($piezas[$i]) - 1; $j++)
                    @if ($j == 5)
                        <td></td>
                        <td>{{ $piezas[$i][$j] }}</td>
                    @else
                        <td>{{ $piezas[$i][$j] }}</td>
                    @endif
                @endfor
            @else
                @for ($j = 1; $j < count($piezas[$i]) - 4; $j++)
                    @if ($j == 5)
                        <td></td>
                        <td>{{ $piezas[$i][$j] }}</td>
                    @else
                        <td>{{ $piezas[$i][$j] }}</td>
                    @endif
                @endfor
            @endif
            </tr>
        @else
            @if ($profile == "quality")
                @if ($piezas[$i][9] == 1)
                    <tr style="background-color: #acf980a8">
                @elseif ($piezas[$i][5] == 'Incompleto')
                    <tr style="background-color: #f9f9a8">
                @elseif ($piezas[$i][9] == 2)
                    <tr style="background-color: #ec7163cd">
                @else
                    <tr>
                @endif
            @else
                @if ($piezas[$i][5] == "Ninguno")
                    <tr style="background-color: #acf980a8">
                @elseif ($piezas[$i][5] == 'Incompleto')
                    <tr style="background-color: #f9f9a8">
                @elseif ($piezas[$i][5] != "Ninguno")
                    <tr style="background-color: #ec7163cd">
                @else
                    <tr>
                @endif
            @endif
            @if ($profile == "quality")
                @for ($j = 1; $j < count($piezas[$i]) - 1; $j++)
                    <td>{{ $piezas[$i][$j] }}</td>
                @endfor
            @else
                @for ($j = 1; $j < count($piezas[$i]) - 4; $j++)
                    <td>{{ $piezas[$i][$j] }}</td>
                @endfor
            @endif
            </tr>
        @endif
    @endfor

</table>
</div>
</body>

</html>
