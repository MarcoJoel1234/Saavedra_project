<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Piezas PDF</title>
</head>
<body>
    <style>
        #title_ot{
            font-size: 24px;
            font-weight: bold;
            color:blue;
            margin-left: 220px;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }
        table{
            margin-left: 0px;
            margin-top: 20px;
            width: 80%;
            border: 1px solid ;
            border-collapse: collapse; /* Colapsa los bordes de las celdas */
            width: 100%; 
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }
        .etiquetas {
            color: black;
            font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
            font-size: 24px;
        }
        label {
            margin-right: 10px; /* Agrega un margen derecho para separar las etiquetas */
            font-size: 16px; /* Tamaño de fuente */
            color: #333; /* Color del texto */
            display: inline-block;
        }
        .contenedor {
            display: flex;
            justify-content: flex-end; /* Centra horizontalmente el contenido */
            width: 100%;
            height: 100vh; /* Altura del viewport */
        }
        img {
            max-width: 100%; /* Ajusta el tamaño máximo de la imagen al ancho del contenedor */
            max-height: 100%; /* Ajusta el tamaño máximo de la imagen a la altura del contenedor */
        }
        tr, td{
            text-align: center;
            padding: 5px; 
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
        }

    </style>
        <label id="title_ot">Orden de trabajo: {{$otElegida->id}} </label>
        <div class="etiquetas">
            <label for="etiqueta1">Clase: {{$array[0]}}</label> <br>
            <label for="etiqueta2">Operador: {{$array[1]}}</label><br>
            <label for="etiqueta3">Máquina: {{$array[2]}}</label><br>
            <label for="etiqueta4">Proceso: {{$array[3]}}</label><br>
        </div>
        <table border="1">
            <thead>
                <tr>
                    <th>N_pieza</th>
                    <th>Clase</th>
                    <th>Nombre del operador</th>
                    <th>Máquina</th>
                    <th>Proceso</th>
                    <th>Error</th>
                </tr>
            </thead>
            @for ($i=0; $i < count($piezas); $i++)
                @if ($piezas[$i][5] == "Ninguno")
                    <tr style="background-color: #ACF980">
                @else 
                <tr style="background-color: #EC7063">
                @endif

                @for ($j=0; $j < count($piezas[$i]); $j++)
                    <td>{{$piezas[$i][$j]}}</td>
                @endfor
                </tr>
            @endfor
        </table>
    </div>
</body>
</html>
