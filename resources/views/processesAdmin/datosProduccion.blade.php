@extends('layouts.appMenu')
@section('content')

    <head>
        <title>Datos de producción</title>
        @vite('resources/css/dashboard.css')
    </head>

    <body background="{{ asset('images/fondoLogin.jpg') }}">
        <div class="container">
            <h1>Datos de producción</h1>
            <!--Sección del dashboard de producción-->
            <form method="post" action="{{ route('showProduccion') }}">
                @csrf
                <div class="dashboard">
                    <!-- Cuadro de OT -->
                    <div class="box ot" style="background-color: #fff">
                        <label for="ot-select">Orden de Trabajo:</label>
                        <script src="{{ asset('js/datosProduccion.js') }}"></script>
                        <script>
                            var datos = @json($datos); //Datos de las ordenes de trabajo
                            let habilitar, box, selects = [];

                            //Crear select de OTs y agregarlo al div
                            box = document.querySelector(".ot");
                            selects["ot"] = insertarSelect("ot", datos);
                            box.appendChild(selects["ot"]);

                            //Aplicar acciones cuando se seleccione una OT
                            selects["ot"].addEventListener("change", () => {
                                //Habilitar o deshabilitar campos dependiendo del valor del select OT
                                habilitar = (selects["ot"].value != 0) ? datos[selects["ot"].value]["operadores"] : null;
                                aplicarAccionesToEvents(habilitar, ["operadores", "clases", "pedido", "procesos", "boton"]);

                                //Crear el campo de moldura
                                box = document.querySelector(".ot");
                                let moldura = (habilitar != null) ? datos[selects["ot"].value]["moldura"] : selects["ot"].value;
                                crearInputConValor(box, moldura, "moldura");

                                //Aplicar acciones cuando se seleccione un operador
                                selects["operadores"].addEventListener("change", () => {
                                    // console.log(datos[selects["ot"]]["operadores"]);
                                    habilitar = (selects["operadores"].value != 0) ? datos[selects["ot"].value]["operadores"][
                                        selects["operadores"].value
                                    ]["clases"] : null;
                                    aplicarAccionesToEvents(habilitar, ["clases", "pedido", "procesos", "boton"]);

                                    //Aplicar acciones cuando se seleccione una clase
                                    selects["clases"].addEventListener("change", () => {
                                        // console.log(datos[selects["ot"]]["operadores"]);
                                        habilitar = (selects["clases"].value != 0) ? datos[selects["ot"].value][
                                            "operadores"
                                        ][selects["operadores"].value]["clases"][selects["clases"].value][
                                            "procesos"
                                        ] : null;
                                        aplicarAccionesToEvents(habilitar, ["procesos", "boton"]);

                                        //Crear el campo de pedido
                                        box = document.querySelector(".pedido");
                                        let pedido = (habilitar != null) ? datos[selects["ot"].value]["operadores"][
                                            selects["operadores"].value
                                        ]["clases"][selects["clases"].value]["pedido"] : null;
                                        crearInputConValor(box, pedido, "pedido");

                                        selects["procesos"].addEventListener("change", () => {
                                            habilitar = selects["procesos"].value != 0 ? true : false;
                                            let boton = document.getElementById("button");
                                            if (habilitar) {
                                                boton.style.display = "block";
                                            } else {
                                                boton.style.display = "none";
                                            }
                                        });
                                    });
                                });
                            });
                        </script>
                    </div>

                    <!--Cuadro de Operadores-->
                    <div class="box operadores">
                        <label for="operadores-label">Operadores:</label>
                        <input id="operadores-input" class="filtros" type="text" disabled>
                    </div>

                    <!--Cuadro de clase-->
                    <div class="box clases">
                        <label for="clases-select">Clase:</label>
                        <input id="clases-input" class="filtros" type="text" disabled>
                    </div>


                    <!-- Cuadro de Pedido -->
                    <div class="box pedido">
                        <label for="pedido-select">Pedido:</label>
                        <input id="pedido-input" class="filtros" type="text" disabled>
                    </div>

                    <!-- Cuadro de proceso -->
                    <div class="box procesos">
                        <label for="procesos-select">Proceso:</label>
                        <input id="procesos-input" class="filtros" type="text" disabled>
                    </div>
                </div>
                <!-- Boton de buscar -->
                <div class="button-container">
                    <input type="submit" id="button" class="button" value="Buscar" style="display: none">
                </div>
            </form>

            <!-- Tabla de resultados -->
            {{-- Agregar funcion en js para crear la tabla con los respectivos datos --}}
            @isset($filtros)
                <div class="dashboard2">
                    <div class="datos">
                        <h3>Orden de trabajo</h3>
                        <input class="filtros2" type="text" value="{{ $filtros['ot'] }} {{ $filtros['moldura'] }}" disabled>
                        <h3>Clase</h3>
                        <input class="filtros2" type="text" value="{{ $filtros['clase'] }} {{ $filtros['pedido'] }} piezas"
                            disabled>
                        <h3>Proceso</h3>
                        <input class="filtros2" type="text" value="{{ $filtros['proceso'] }}" disabled>
                        <h3>Operador</h3>
                        <input class="filtros2" type="text" value="{{ $filtros['operador'] }}" disabled style="width: 300px">
                    </div>
                    <div class="div-table">
                        <script>
                            let datosOperadores = @json($operadores);
                            let div_dashboard2 = document.querySelector(".div-table");
                            div_dashboard2.appendChild(crearTabla(datosOperadores));
                        </script>
                    </div>
                </div>                
            @endisset
        </div>
    </body>
@endsection