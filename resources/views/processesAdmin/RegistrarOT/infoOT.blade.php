@extends($layout)
@section('content')

    <head>
        <title>Registrar Orden de trabajo</title>
        <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
        @vite('resources/css/RegistrarOT/agregarClass.css')
    </head>


    <body background="{{ asset('images/fondoLogin.jpg') }}">
        <script>
            let layout = @json($layout);

            function changeStatus() {
                let checkboxes = document.querySelectorAll('.checkbox-input-soldaduras');
                let input_maq = document.querySelectorAll('.input-maq-soldaduras');
                // Agregar un evento de cambio a cada checkbox
                checkboxes.forEach((checkbox, index) => {
                    checkbox.addEventListener('change', function() {
                        // Deshabilitar el input-maq correspondiente según el estado de la checkbox
                        input_maq[index].disabled = !checkbox.checked;
                        // Desmarcar el otro checkbox cuando uno se selecciona
                        checkboxes.forEach((otherCheckbox, otherIndex) => {
                            if (otherCheckbox !== checkbox) {
                                otherCheckbox.checked = false;
                                // Deshabilitar el input-maq correspondiente si la checkbox no está marcada
                                input_maq[otherIndex].disabled = !otherCheckbox.checked;
                            }
                        });
                        input_maq.forEach((input) => {
                            if (input.disabled) {
                                input.style.backgroundColor = "#ced4da";
                                input.style.border = "none";
                                input.value = "";
                            } else {
                                input.style.backgroundColor = "white";
                                input.style.border = "1px solid #000000";
                            }
                        });
                    });
                });
            }

            function crearCheckbox(clase, proceso, maquinas, editar) {
                let procesos = [];
                let procesosArray = [];
                switch (clase) {
                    case "Bombillo":
                        procesos = ['Cepillado', 'Desbaste exterior', 'Revision Laterales', '1ra Operación',
                            'Barreno maniobra', '2da Operación', 'Soldadura', 'Soldadura PTA', 'Rectificado',
                            'Asentado', 'Calificado', 'Acabado Bombillo', 'Barreno profundidad', 'Cavidades', 'Copiado',
                            'Offset', 'Palomas', 'Rebajes', 'Grabado'
                        ];
                        procesosArray = ["cepillado", "desbaste_exterior", "revision_laterales", "pOperacion",
                            "barreno_maniobra", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado",
                            "calificado", "acabadoBombillo", "barreno_profundidad", "cavidades", "copiado", "offSet",
                            "palomas",
                            "rebajes", "grabado"
                        ];
                        break;
                    case "Molde":
                        procesos = ['Cepillado', 'Desbaste exterior', 'Revision Laterales', '1ra Operación',
                            'Barreno maniobra', '2da Operación', 'Soldadura', 'Soldadura PTA', 'Rectificado',
                            'Asentado', 'Calificado', "Acabado Molde", 'Barreno profundidad', 'Cavidades', 'Copiado',
                            'Offset', 'Palomas', 'Rebajes', 'Grabado'
                        ];
                        procesosArray = ["cepillado", "desbaste_exterior", "revision_laterales", "pOperacion",
                            "barreno_maniobra", "sOperacion", "soldadura", "soldaduraPTA", "rectificado", "asentado",
                            "calificado", "acabadoMolde", "barreno_profundidad", "cavidades", "copiado", "offSet",
                            "palomas",
                            "rebajes", "grabado"
                        ];
                        break;
                    case "Obturador":
                        procesos = ['Soldadura', 'Soldadura PTA', '1ra y 2da Operación Equipo', ];
                        procesosArray = ["soldadura", "soldaduraPTA", "operacionEquipo"];
                        break;
                    case "Fondo":
                        procesos = ['1ra y 2da Operación Equipo', 'Soldadura', 'Soldadura PTA'];
                        procesosArray = ["operacionEquipo", "soldadura", "soldaduraPTA"];
                        break;
                    case "Corona":
                        procesos = ['Cepillado', 'Desbaste exterior'];
                        procesosArray = ["cepillado", "desbaste_exterior"];
                        break;
                    case "Plato":
                        procesos = ['1ra y 2da Operación Equipo', 'Barreno de Profundidad'];
                        procesosArray = ["operacionEquipo", "barreno_profundidad"];
                        break;
                    case "Embudo":
                        procesos = ['1ra y 2da Operación Equipo', 'Embudo C.M.'];
                        procesosArray = ["operacionEquipo", "embudoCM"];
                        break;
                }
                crearCasillas(procesos, procesosArray, proceso, maquinas, editar);
            }

            function crearCasillas(procesos, procesosArray, proceso, maquinas, editar) {
                let div_casillas = document.getElementById('casillas');
                let secciones = document.getElementById('secciones');
                let seccion1 = document.createElement('div');
                seccion1.className = "seccion1"; //Clase de la sección 1
                let seccion2 = document.createElement('div');
                seccion2.className = "seccion2";

                let contadorMaquinas = 0;
                console.log(maquinas);
                for (let i = 0; i < procesos.length; i++) {
                    let div = document.createElement('div'); //Crear un div para cada checkbox
                    div.className = "checkbox-container"; //Clase del div
                    let label = document.createElement('label'); //Crear un label para cada checkbox
                    label.className = "checkbox-label"; //Clase del label
                    label.innerHTML = procesos[i]; //Texto del label 
                    let input_maq = document.createElement('input'); //Crear un input en donde se insertaran las maquinas
                    input_maq.type = "number";
                    input_maq.name = "maquinas[]";
                    input_maq.className = "input-maq";
                    input_maq.id = "input-maq" + i + 1;
                    input_maq.required = true;
                    let input = document.createElement('input'); //Crear un input para cada checkbox
                    input.type = "checkbox"; //Tipo de input checkbox
                    input.name = "procesos[]"; //Nombre del input
                    input.value = procesosArray[i];
                    if (procesosArray[i] == "soldadura" || procesosArray[i] == "soldaduraPTA") {
                        input.className = "checkbox-input-soldaduras"; //Clase del input
                        input_maq.className = "input-maq-soldaduras"; //Clase del input 
                        input.checked = false;
                        input_maq.disabled = true;
                    } else {
                        input.className = "checkbox-input";
                    }
                    if (proceso != 0) {
                        if (proceso.includes(procesosArray[i])) {
                            input.checked = true;
                            input_maq.value = maquinas[contadorMaquinas];
                            contadorMaquinas++;
                            if (editar) {
                                input.disabled = false;
                                input_maq.disabled = false;
                            } else {
                                input.disabled = true;
                                input_maq.disabled = true;
                            }
                        } else {
                            if (editar) {
                                input.disabled = false;
                                input_maq.disabled = true;
                            } else {
                                input.disabled = true;
                                input_maq.disabled = true;
                            }
                        }
                    } else {
                        if (procesosArray[i] == "soldadura" || procesosArray[i] == "soldaduraPTA") {
                            input.checked = false;
                            input_maq.disabled = true;
                        } else {
                            input.checked = true;
                            input_maq.disabled = false;
                        }
                    }
                    //Si se ingresa a la interfaz con el perfil de almacen deshabilitar las casillas de los procesos
                    if (layout == "layouts.appAlmacen") {
                        input_maq.disabled = true;
                        input.disabled = true;
                        if (proceso == 0) { // Si el proceso no ha sido seleccionado en la OT
                            input.checked = false;
                        }
                    }
                    input.addEventListener('change', function() {
                        if (input.checked) {
                            input_maq.disabled = false; //Habilitar el input
                            input_maq.style.backgroundColor = "white"; //Cambiar el color del input
                            input_maq.style.border = "1px solid #000000";
                        } else {
                            input_maq.disabled = true;
                            input_maq.style.backgroundColor = "#ced4da";
                            input_maq.style.border = "none";
                            input_maq.value = "";
                        }
                    });
                    if (input_maq.disabled) {
                        input_maq.style.backgroundColor = "#ced4da";
                        input_maq.style.border = "none";
                    }
                    div.appendChild(input_maq); //Agregar el input al div
                    div.appendChild(input); //Agegar el input al div 
                    div.appendChild(label); //Agregar el label al div

                    if (i < parseInt((procesos.length / 2))) {
                        seccion1.appendChild(div); //Agregar a la sección 1
                    } else {
                        seccion2.appendChild(div); //Agregar a la sección 2
                    }
                }
                secciones.appendChild(seccion1); //Agregar a la sección 1
                secciones.appendChild(seccion2); //Agregar a la sección 2
                div_casillas.appendChild(secciones); //Agregar a las casillas

                changeStatus();
            }

            function mostrarDiv(clase, id_clase) {
                let div_padre = document.createElement('div'); //Crear un div para cada checkbox
                div_padre.className = "div-padre";
                div_padre.id = "div-padre";
                let div = document.createElement('div'); //Crear un div para cada checkbox
                div.className = "div-delete bg-white"; //Clase del div
                let label = document.createElement('label');
                label.className = "label-delete"; //Clase del label
                label.innerHTML = "¿Estás seguro de eliminar la clase '" + clase + "'?";
                let image = document.createElement('img');
                image.className = "img-delete"; //Clase del label
                image.src = "{{ asset('images/delete.png') }}"; //Clase del label
                let div_cerrar = document.createElement('div');
                div_cerrar.className = "div-cerrar"; //
                let btn_cerrar = document.createElement('button');
                btn_cerrar.className = "btn-cerrar"
                btn_cerrar.addEventListener('click', function() {
                    cerrarDiv();
                })
                let imageCerrar = document.createElement('img');
                imageCerrar.className = "img-cerrar";
                imageCerrar.src = "{{ asset('images/cerrar.png') }}";
                btn_cerrar.appendChild(imageCerrar);
                div_cerrar.appendChild(btn_cerrar);

                let a = document.createElement('a');
                a.className = "btn-delete"; //Clase del label
                a.href = `{{ url('/deleteClass', '') }}/${id_clase}`;
                a.innerHTML = "Eliminar";

                div.appendChild(div_cerrar);
                div.appendChild(image);
                div.appendChild(label);
                div.appendChild(a);
                div_padre.appendChild(div);
                return div_padre;
            }

            function cerrarDiv() {
                let div_padre = document.getElementById('div-padre');
                div_padre.remove();
            }

            function crearSelect(clase, div) {
                let titulo = document.createElement('label');
                titulo.style = "color: green; font-weight: bold;";
                titulo.id = "titulo-select";
                let select = document.createElement('select');
                select.className = "selects form-control";
                if (clase.value == 'Obturador') {
                    titulo.innerHTML = "Seleccione la sección:";
                    select.id == "seccion";
                    select.name = "seccion";
                    for (let i = 0; i < 2; i++) {
                        let option = document.createElement('option');
                        option.value = i + 1;
                        option.text = i + 1;
                        select.add(option);
                    }
                } else {
                    titulo.innerHTML = "Seleccione el tamaño:";
                    select.id == "tamaños";
                    select.name = "tamanio";
                    for (let i = 0; i < 3; i++) {
                        let option = document.createElement('option');
                        switch (i) {
                            case 0:
                                option.value = "Chico";
                                option.text = "Chico";
                                break;
                            case 1:
                                option.value = "Mediano";
                                option.text = "Mediano";
                                break;
                            case 2:
                                option.value = "Grande";
                                option.text = "Grande";
                                break;
                        }
                        select.add(option);
                    }
                }
                div.appendChild(titulo);
                div.appendChild(select); //Agregar el select al div
            }

            function modificarSelect() {
                eliminarSelect();
                let clase = document.getElementById('clases'); //Obtener el select de la clase actual
                let div = document.getElementById('div-select'); //Obtener el div del select
                crearSelect(clase, div); //Crear el select de acuerdo a la clase
                let secciones = document.getElementById('secciones'); //Obtener el div de las casillas
                secciones.innerHTML = ""; //Eliminar las casillas
                crearCheckbox(clase.value, 0, 0, false); //Crear los checkbox de acuerdo a la clase
            }

            function eliminarSelect() { //Eliminar el select anterior 
                let select = document.getElementsByClassName('selects'); //Obtener los datos de los selects
                if (select) {
                    let titulo = document.getElementById('titulo-select'); //Obtener el titulo del select anterior
                    titulo.remove(); //Eliminar el titulo
                    select[0].remove(); //Eliminar el select de la clase anterior
                }
            }
        </script>
        <form action="{{ route('saveClass') }}" method="POST" class="pt-3">
            <input type="hidden" name="id_clase" value="{{ $clase->id }}" />
            <div class="wrapper bg-white">
                <div class="h2 text-center">
                    <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
                </div>
                <div class="h4 text-muted text-center pt-2">¡Registra nueva OT!</div>
                @csrf
                @include('layouts.partials.messages')
                @if (isset($ot))
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-outline">
                                <label style="color: green; font-weight: bold;">Moldura:</label><br>
                                <input type="text" id="form3Example2" value="{{ $moldura->nombre }}" class="form-control"
                                    disabled />
                                <input type="hidden" name="id_moldura" value="{{ $moldura->id }}" />
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-outline">
                                <label style="color: green; font-weight: bold;">Orden de trabajo:</label><br>
                                <input type="text" id="form3Example2" class="form-control" value="{{ $ot }}"
                                    disabled />
                                <input type="hidden" name="ot" value="{{ $ot }}" />
                            </div>
                        </div>
                    </div>
                    @isset($edit)
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="form-outline">
                                    <label style="color: green; font-weight: bold;">Inserta el pedido:</label><br>
                                    <input type="number" id="pedido" name="pedido" placeholder="Pedido total."
                                        value="{{ $clase->pedido }}" class="form-control" required />
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="form-outline">
                                    <label style="color: green; font-weight: bold;">Inserta las piezas:</label><br>
                                    <input type="number" id="piezas" name="piezas" placeholder="Cantidad de piezas."
                                        value="{{ $clase->piezas }}" class="form-control" required />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="form-outline">
                                    @if (count($piezas) == 0 && count($metas) == 0)
                                        <label style="color: green; font-weight: bold;">Seleccione el tipo:</label><br>
                                        <select id="clases" name="clase" class="form-control">
                                            <option value="{{ $clase->nombre }}">{{ $clase->nombre }}</option>
                                            @foreach ($clasesName as $cl)
                                                @if ($cl != $clase->nombre)
                                                    <option value="{{ $cl }}">{{ $cl }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @else
                                        <label style="color: green; font-weight: bold;">Tipo:</label><br>
                                        <input type="text" value="{{ $clase->nombre }}" class="form-control" disabled />
                                        <input type="hidden" name="clase" value="{{ $clase->nombre }}" />
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="form-outline" id="div-select">
                                    @if ($clase->nombre == 'Obturador')
                                        <label id="titulo-select" style="color: green; font-weight: bold;">Seleccione la
                                            sección:</label>
                                        <select id="tamaños" name="seccion" class="selects form-control">
                                            @for ($i = 1; $i <= 3; $i++)
                                                @if ($i == $clase->seccion)
                                                    <option value="{{ $i }}" selected>{{ $i }}</option>
                                                @else
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endif
                                            @endfor
                                        </select>
                                    @else
                                        <label id="titulo-select" style="color: green; font-weight: bold;">Seleccione el
                                            tamaño:</label>
                                        <select id="tamaños" name="tamanio" class="selects form-control">
                                            <option value="{{ $clase->tamanio }}">{{ $clase->tamanio }}</option>
                                            @for ($i = 1; $i <= 3; $i++)
                                                @if ($i == 1 && $clase->tamanio != 'Chico')
                                                    <option value="Chico">Chico</option>
                                                @elseif ($i == 2 && $clase->tamanio != 'Mediano')
                                                    <option value="Mediano">Mediano</option>
                                                @elseif ($i == 3 && $clase->tamanio != 'Grande')
                                                    <option value="Grande">Grande</option>
                                                @endif
                                            @endfor
                                        </select>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="form-outline">
                                    <label style="color: green; font-weight: bold;">Fecha de inicio:</label><br>
                                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control"
                                        value="{{ $clase->fecha_inicio }}" required />
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="form-outline">
                                    <label style="color: green; font-weight: bold;">Hora de inicio:</label><br>
                                    <input type="time" id="hora_inicio" name="hora_inicio" class="form-control"
                                        value="{{ $clase->hora_inicio }}" required />
                                </div>
                            </div>
                        </div>
                        <!--Deshabilitar inputs cuando se ingresa con el perfil de almacen-->
                        <script>
                            let inputs = document.querySelectorAll(".form-control");
                            if (@json($layout) == "layouts.appAlmacen") {
                                inputs.forEach(input => {
                                    if (!input.disabled && (input.id != "pedido" && input.id != "piezas")) {
                                        input.disabled = true;
                                    }
                                });
                            }
                        </script>
                    @else
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="form-outline">
                                    <label style="color: green; font-weight: bold;">Pedido Total:</label><br>
                                    <input type="number" id="pedido" value="{{ $clase->pedido }}" class="form-control"
                                        disabled />
                                    <input type="hidden" name="pedido" value="{{ $clase->pedido }}" />
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="form-outline">
                                    <label style="color: green; font-weight: bold;">Piezas ingresadas:</label><br>
                                    <input type="number" id="piezas" value="{{ $clase->piezas }}" class="form-control"
                                        disabled />
                                    <input type="hidden" name="piezas" value="{{ $clase->piezas }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="form-outline">
                                    <label style="color: green; font-weight: bold;">Tipo:</label><br>
                                    <input type="text" value="{{ $clase->nombre }}" class="form-control" disabled />
                                    <input type="hidden" name="clase" value="{{ $clase->nombre }}" />
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="form-outline" id="div-select">
                                    @if ($clase->nombre == 'Obturador')
                                        <label id="titulo-select" style="color: green; font-weight: bold;">Sección:</label>
                                        <input type="text" id="tamaños" value="{{ $clase->seccion }}"
                                            class="form-control" disabled />
                                        <input type="hidden" name="seccion" value="{{ $clase->seccion }}" />
                                    @else
                                        <label id="titulo-select" style="color: green; font-weight: bold;">Tamaño:</label>
                                        <input type="text" id="tamaños" value="{{ $clase->tamanio }}"
                                            class="form-control" disabled />
                                        <input type="hidden" name="tamanio" value="{{ $clase->tamanio }}" />
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="form-outline">
                                    <label style="color: green; font-weight: bold;">Fecha de inicio:</label><br>
                                    <input type="date" id="fecha_inicio" value="{{ $clase->fecha_inicio }}"
                                        class="form-control" disabled />
                                    <input type="hidden" name="fecha_inicio" value="{{ $clase->fecha_inicio }}" />
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="form-outline">
                                    <label style="color: green; font-weight: bold;">Hora de inicio:</label><br>
                                    <input type="time" id="fecha_entrega" value="{{ $clase->hora_inicio }}"
                                        class="form-control" disabled />
                                    <input type="hidden" name="hora_inicio" value="{{ $clase->hora_inicio }}" />
                                </div>
                            </div>
                        </div>
                    @endisset
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="form-outline">
                                <label style="color: green; font-weight: bold;">Fecha de termino:</label><br>
                                @if ($clase->fecha_termino == null)
                                    <input type="text" value="-" class="form-control" disabled />
                                @else
                                    <input type="date" id="fecha_termino" value="{{ $clase->fecha_termino }}"
                                        class="form-control" disabled />
                                    <input type="hidden" name="fecha_termino" value="{{ $clase->fecha_termino }}" />
                                @endif

                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-outline">
                                <label style="color: green; font-weight: bold;">Hora de termino:</label><br>
                                @if ($clase->hora_termino == null)
                                    <input type="text" value="-" class="form-control" disabled />
                                @else
                                    <input type="time" id="hora_termino" value="{{ $clase->hora_termino }}"
                                        class="form-control" disabled />
                                    <input type="hidden" name="fecha_entrega" value="{{ $clase->hora_termino }}" />
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="scrollabe-table">
                        <table border="1" class="tabla3" id="tabla3">
                            <tr>
                                <th class="t-title" style="width:150px">Tipo</th>
                                <th class="t-title" style="width:150px">Tamaño/Sección</th>
                                <th class="t-title" style="width:150px">Cantidad</th>
                                <th class="t-title" style="width:150px">Pedido</th>
                                @if (!isset($edit))
                                    <th class="t-title" style="width:150px">Acciones</th>
                                @endif
                            </tr>
                            @if (isset($clases))
                                @foreach ($clases as $class)
                                    <tr>
                                        @if ($class->id == $clase->id)
                                            <td style="background-color: #C8E6C9;" class="t-dato">{{ $class->nombre }}
                                            </td>
                                            @if ($class->nombre == 'Obturador')
                                                <td style="background-color: #C8E6C9;" class="t-dato">
                                                    {{ $class->seccion }}</td>
                                            @else
                                                <td style="background-color: #C8E6C9;" class="t-dato">
                                                    {{ $class->tamanio }}</td>
                                            @endif
                                            <td class="t-dato" style="background-color: #C8E6C9;">{{ $class->piezas }}
                                            </td>
                                            <td class="t-dato" style="background-color: #C8E6C9;">{{ $class->pedido }}
                                            </td>
                                            @if (!isset($edit))
                                                <td class="t-dato-btn" style="background-color: #C8E6C9;">
                                                    <div class="images">
                                                        <a href="{{ route('editClase', ['clase' => $class->id]) }}"
                                                            class="btn-table"><img
                                                                src="{{ asset('images/edit.png') }}"></a>
                                                    </div>
                                                    @if ($layout != 'layouts.appAlmacen')
                                                        <div class="images">
                                                            <button class="btn-table"
                                                                id="btn-delete{{ $class->id }}"><img
                                                                    src="{{ asset('images/delete.png') }}"></button>
                                                        </div>
                                                    @endif
                                                </td>
                                            @endif
                                        @else
                                            <td class="t-dato">{{ $class->nombre }}</td>
                                            @if ($class->nombre == 'Obturador')
                                                <td class="t-dato">{{ $class->seccion }}</td>
                                            @else
                                                <td class="t-dato">{{ $class->tamanio }}</td>
                                            @endif
                                            <td class="t-dato">{{ $class->piezas }}</td>
                                            <td class="t-dato">{{ $class->pedido }}</td>
                                            <td class="t-dato-btn">
                                                <div class="images">
                                                    <a href="{{ route('editClase', ['clase' => $class->id]) }}"
                                                        class="btn-table"><img src="{{ asset('images/edit.png') }}"></a>
                                                </div>
                                                @if ($layout != 'layouts.appAlmacen')
                                                    <div class="images">
                                                        <button class="btn-table" id="btn-delete{{ $class->id }}"><img
                                                                src="{{ asset('images/delete.png') }}"></button>
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
                                        <script>
                                            if (@json($layout) !=
                                                "layouts.appAlmacen") { //Si el perfil es de almacen deshabilitar el boton de eliminar OT
                                                document.getElementById('btn-delete' + @json($class->id)).addEventListener('click', function() {
                                                    event.preventDefault();
                                                    document.body.appendChild(mostrarDiv(@json($class->nombre), @json($class->id)));
                                                });
                                            } else {
                                                //Centrar div de Editar
                                                let div_images = document.querySelectorAll('.images');
                                                div_images.forEach(div => {
                                                    div.style = "width: 100%";
                                                });
                                            }
                                        </script>
                                    </tr>
                                @endforeach
                            @endif
                        </table>
                @endif
            </div>
            </div>
            <div class="casillas bg-white" id="casillas">
                <h2>Procesos</h2>
                @if (isset($proceso))
                    @if (isset($edit))
                        <div class="secciones" id="secciones">
                            <script>
                                let claseName = @json($clase->nombre); //Nombre de la clase 
                                let procesoI = @json($proceso); //Proceso que se selecciono
                                let maquinas = @json($maquinas);
                                crearCheckbox(claseName, procesoI, maquinas, true);
                                let clase = document.getElementById('clases');
                                clase.addEventListener('change', modificarSelect);
                            </script>
                        </div>
                        <div class="row-btn row">
                            <div class="col-md-6 mb-3">
                                <button type="submit" style="width: 100%;" class="btn">Guardar cambios</button>
                            </div>
                            <div class="col-md-6 mb-3" style="margin-top:20px; display:flex; justify-content:center;">
                                <a href="{{ route('mostrarClases', ['ot' => $ot]) }}" class="btnTerminar">Regresar</a>
                            </div>
                        </div>
                    @else
                        <div class="secciones" id="secciones">
                            <script>
                                let claseName = @json($clase->nombre); //Nombre de la clase 
                                let procesoI = @json($proceso); //Proceso que se selecciono
                                let maquinas = @json($maquinas);
                                crearCheckbox(claseName, procesoI, maquinas, false);
                            </script>
                        </div>
                        <div class="row" style="display:flex; align-items:center; justify-content:center">
                            <div class="col-md-6 mb-3" style="margin-top:20px; display:flex; justify-content:center;">
                                <a href="{{ route('registerOT') }}" class="btnTerminar">Terminar OT</a>
                            </div>
                            @if (auth()->user()->perfil != 5)
                                <div class="col-md-6 mb-3" style="display:flex; justify-content:center;">
                                    <a href="{{ route('registerClass', ['ot' => $ot]) }}" style="width: 100%;"
                                        class="btn">Agregar clase</a>
                                </div>
                            @endif
                        </div>
                    @endif
                @else
                    @if (isset($edit))
                        <div class="secciones" id="secciones">
                            <script>
                                let claseName = @json($clase->nombre); //Nombre de la clase
                                crearCheckbox(claseName, 0, 0, true); //0 para que no se marque ninguno

                                let clase = document.getElementById('clases');
                                clase.addEventListener('change', modificarSelect);
                            </script>
                        </div>
                        <div class="row-btn row">
                            <div class="col-md-6 mb-3">
                                <button type="submit" style="width: 100%;" class="btn">Guardar cambios</button>
                            </div>
                            <div class="col-md-6 mb-3" style="margin-top:20px; display:flex; justify-content:center;">
                                <a href="{{ route('mostrarClases', ['ot' => $ot]) }}" class="btnTerminar">Regresar</a>
                            </div>
                        </div>
                    @else
                        <div class="secciones" id="secciones">
                            <script>
                                let claseName = @json($clase->nombre); //Nombre de la clase
                                crearCheckbox(claseName, 0, 0, false); //0 para que no se marque ninguno
                            </script>
                        </div>
                        <input type="submit" id="btn-add" class='btn' value="Guardar" />
                    @endif
                @endif
            </div>
        </form>
    </body>
@endsection
