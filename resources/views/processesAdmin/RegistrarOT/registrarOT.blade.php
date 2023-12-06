@extends('layouts.appAdmin')
@section('content')

<head>
    <title>Registrar Orden de trabajo</title>
    <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
    @vite('resources/css/RegistrarOT/agregarOT.css')
    @vite('resources/js/agregarColumnas.js')
</head>
@if(isset($ot))
    <style>
        .container{
            align-items: end;
            width: 100%;
        }
        .wrapper{
            width: 80%;
            height: 85%;
        }
        .lg-saavedra{
            width: 300px;
            margin-bottom: 0;
        }
    </style>
@endif  
<body background="{{ asset('images/fondoLogin.jpg') }}">
    <script>
        function crearSelect(clase, div){
            let titulo = document.createElement('label');
            titulo.style = "color: green; font-weight: bold;";
            titulo.id = "titulo-select";
            let select = document.createElement('select');
            select.className = "selects form-control";
            if(clase.value == 'Obturador'){
                titulo.innerHTML = "Seleccione la sección:";
                select.id == "seccion";
                select.name = "seccion";
                for(let i=0; i<2; i++){
                    let option = document.createElement('option');
                    option.value = i+1;
                    option.text = i+1;
                    select.add(option);
                }
            }else{
                titulo.innerHTML = "Seleccione el tamaño:";
                select.id == "tamaños";
                select.name = "tamanio";
                for(let i=0; i<3; i++){
                    let option = document.createElement('option');
                    switch(i){
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
            div.appendChild(select);
        }
        function modificarSelect(){
            eliminarSelect();
            let clase = document.getElementById('clases');
            let div = document.getElementById('div-select');
            crearSelect(clase, div);
        }
        function eliminarSelect(){
            let select = document.getElementsByClassName('selects');
            if(select){
                let titulo = document.getElementById('titulo-select');
                titulo.remove();    
                select[0].remove();
            }
        }
        function mostrarDiv(ot, moldura){
            let div_padre = document.createElement('div'); //Crear un div para cada checkbox
            div_padre.className = "div-padre"; //Clase del div 
            div_padre.id = "div-padre";
            let div = document.createElement('div'); //Crear un div para cada checkbox
            div.className = "div-delete bg-white"; //Clase del div
            let label = document.createElement('label');
            label.className = "label-delete"; //Clase del label
            label.innerHTML = "¿Estás seguro de eliminar la OT " + ot + " - " + moldura +"?"; //Texto del label
            let image = document.createElement('img');
            image.className = "img-delete"; //Clase del label
            image.src = "{{ asset('images/delete.png') }}"; //Clase del label
            let div_cerrar = document.createElement('div'); 
            div_cerrar.className = "div-cerrar"; //Clase del div cerrar
            let btn_cerrar = document.createElement('button');
            btn_cerrar.className = "btn-cerrar" //Clase del boton cerrar 
            btn_cerrar.addEventListener('click', function(){ //Función para cerrar el div al presionar el boton de x
                cerrarDiv();
            })
            let imageCerrar = document.createElement('img');
            imageCerrar.className = "img-cerrar";
            imageCerrar.src = "{{ asset('images/cerrar.png') }}";
            btn_cerrar.appendChild(imageCerrar); //Agregar la imagen al boton
            div_cerrar.appendChild(btn_cerrar); //Agregar el boton al div 

            let a = document.createElement('a');
            a.className = "btn-delete"; //Clase del label
            a.href = `{{ url('/deleteOT', '') }}/${ot}`;
            a.innerHTML = "Eliminar";

            div.appendChild(div_cerrar); //Agregar el boton al div 
            div.appendChild(image); //Agregar el checkbox al div
            div.appendChild(label); //Agregar el label al div 
            div.appendChild(a); //Agregar el label al div 
            div_padre.appendChild(div); //Agregar el div al div padre
            return div_padre;
        }
        function cerrarDiv(){
            let div_padre = document.getElementById('div-padre');
            div_padre.remove(); //Agragar el div al div padre
        } 
    </script>
    <form action="{{route('saveOT')}}" method="POST" class="pt-3">
        <div class="wrapper bg-white">
            <div class="h2 text-center">
                <img src="{{ asset('images/lg_saavedra.png') }}" class="lg-saavedra rounded-4" alt="" />
            </div>
            @include('layouts.partials.messages')
            @csrf
            @if (isset($clase))
                @if($clase == 1)
                    <div class="alert alert-danger">
                        <i class="fa fa-check"></i>
                        La clase ingresada ya existe en la orden de trabajo.
                    </div>
                @endif

            @endif
            @if (isset($ot))
                <div class="h4 text-muted text-center pt-2">¡Orden de trabajo!</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-outline">
                            <label style="color: green; font-weight: bold;">Moldura:</label><br>
                            <input type="text" id="form3Example2" value="{{$moldura->nombre}}" class="form-control" disabled />
                            <input type="hidden" name="id_moldura" value="{{$moldura->id}}"/>
                        </div>                        
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-outline">
                            <label style="color: green; font-weight: bold;">Orden de trabajo:</label><br>
                            <input type="text" id="form3Example2" class="form-control" value="{{$ot}}" disabled />
                            <input type="hidden" name="ot" value="{{$ot}}"/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="form-outline">
                            <label style="color: green; font-weight: bold;">Inserta el pedido:</label><br>
                            <input type="number" id="pedido" name="pedido" placeholder="Pedido total." class="form-control" required/>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-outline">
                            <label style="color: green; font-weight: bold;">Inserta las piezas:</label><br>
                            <input type="number" id="piezas" name="piezas" placeholder="Cantidad de piezas." class="form-control"   required/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="form-outline">
                            <label style="color: green; font-weight: bold;">Seleccione el tipo:</label><br>
                            <select id="clases" name="clase" class="form-control">
                                <option value="Bombillo">Bombillo</option>                                    
                                <option value="Molde">Molde</option>
                                <option value="Obturador">Obturador</option>
                                <option value="Fondo">Fondo</option>
                                <option value="Corona">Corona</option>
                                <option value="Plato">Plato</option>
                                <option value="Embudo">Embudo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-outline" id="div-select">
                            <label id="titulo-select" style="color: green; font-weight: bold;">Seleccione el tamaño:</label>
                            <select id="tamaños" name="tamanio" class="selects form-control">
                                <option value="Chico">Chico</option>
                                <option value="Mediano">Mediano</option>
                                <option value="Grande">Grande</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="form-outline">
                            <label style="color: green; font-weight: bold;">Fecha de inicio:</label><br>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" required/>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="form-outline">
                            <label style="color: green; font-weight: bold;">Hora de inicio:</label><br>
                            <input type="time" id="hora_inicio" name="hora_inicio" class="form-control" required/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-2" style="display:flex; justify-content: center;">
                        <div class="form-outline">
                            <button type="submit" id="btn-add" class='btn'>Agregar</button>
                        </div>
                    </div>
                </div>
                <!--Tabla de clases agregadas-->
                <div class="scrollabe-table">
                    <table border="1" class="tabla3" id="tabla3">
                        <tr>
                            <th class="t-title" style="width:150px">Tipo</th>
                            <th class="t-title" style="width:150px">Tamaño/Seccion</th>
                            <th class="t-title" style="width:150px">Cantidad</th>
                            <th class="t-title" style="width:150px">Pedido</th>
                            <th class="t-title" style="width:150px">Fecha de inicio</th>
                            <th class="t-title" style="width:150px">Hora de inicio</th>
                            <th class="t-title" style="width:150px">Fecha de termino</th>
                            <th class="t-title" style="width:150px">Hora de termino</th>
                        </tr>
                        @isset($clasesEncontradas)
                            @foreach ($clasesEncontradas as $claseE)
                                <tr>
                                    <td class="t-dato">{{$claseE->nombre}}</td>
                                    @if ($claseE->nombre == "Obturador")
                                        <td class="t-dato">{{$claseE->seccion}}</td>
                                    @else
                                        <td class="t-dato">{{$claseE->tamanio}}</td>
                                    @endif
                                    <td class="t-dato">{{$claseE->piezas}}</td>
                                    <td class="t-dato">{{$claseE->pedido}}</td>
                                    <td class="t-dato">{{$claseE->fecha_inicio}}</td>
                                    <td class="t-dato">{{$claseE->hora_inicio}}</td>
                                    @if ($claseE->fecha_termino == null)
                                        <td class="t-dato">Sin terminar</td>
                                        <td class="t-dato">Sin terminar</td>
                                        @else
                                        <td class="t-dato">{{$claseE->fecha_termino}}</td>
                                        <td class="t-dato">{{$claseE->hora_termino}}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            <div class="alert alert-danger" style="text-align: center;">
                                <i class="fa fa-check"></i>
                                No hay clases registradas en esta orden de trabajo.
                            </div>
                        @endisset
                    </table>
                </div>
                <div class="text-center pt-3 text-muted" style="padding:10px; display:flex; justify-content:center;">
                    <a href="{{route('mostrarClases', ['ot' => $ot])}}" class="btn text-center my-1">Editar OT</a>
                    <a href="#" class="btn text-center my-1" style="background-color: blue; margin-left:10px;">Generar PDF</a>
                    <button id="btn-deleteOT" class="btn text-center my-1" style="background-color: red; margin-left:10px;">Eliminar OT</button>
                </div>
                <script>
                    document.getElementById("btn-deleteOT").addEventListener('click', function(){
                        event.preventDefault();
                        let ot = @json($ot);
                        let moldura = @json($moldura->nombre);
                        document.body.appendChild(mostrarDiv(ot, moldura)); //Mostrar el div de eliminar
                    });
                </script>
            @else
                <div class="botones-ot">
                    <button id="btn-agregar">Agregar OT</button>
                    <button id="btn-seleccionar">Seleccionar OT</button>
                </div>
                <script>
                    document.getElementById('btn-agregar').addEventListener('click', function(){
                        event.preventDefault();
                        document.getElementsByClassName('container-crear')[0].style.display = "block";
                        document.getElementsByClassName('container-select')[0].style.display = "none";
                    });
                    document.getElementById('btn-seleccionar').addEventListener('click', function(){
                        event.preventDefault();
                        document.getElementById("otCreate").value = "";
                        document.getElementsByClassName('container-crear')[0].style.display = "none";
                        document.getElementsByClassName('container-select')[0].style.display = "block";
                    });
                </script>

                <!--Crear orden de trabajo-->
                <div class="container-crear" style="display: none;">
                    <div class="h4 text-muted text-center pt-2">¡Registrar orden de trabajo!</div>
                    <div class="form-group py-2">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-outline">
                                    <label style="color: green; font-weight: bold;">Selecciona la moldura:</label><br>
                                    <select id="datos" class="form-control" name="id_moldura">
                                    @foreach($molduras as $moldura)     
                                        <option value="{{$moldura->id}}">{{$moldura->nombre}}</option>
                                    @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="form-outline">
                                    <label style="color: green; font-weight: bold;">Ingresa O.T:</label><br>
                                    <input type="number" id="otCreate" name="ot" placeholder="Orden de trabajo." class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center pt-3 text-muted" style="padding:10px; display:flex; justify-content:center;">
                        <button type="submit" class="btn btn-block text-center my-3" id="enviar">Aceptar</button>
                    </div>
                </div>

                <!--Seleccionar orden de trabajo-->
                <div class="container-select" style="display: none;">
                    <div class="h4 text-muted text-center pt-2">¡Seleccionar orden de trabajo!</div>
                    <div class="form-group py-2">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="form-outline">
                                    @isset($oTrabajo)
                                        @php
                                            $contador = 0;
                                        @endphp
                                        <select id="datos" class="form-control" name="otSeleccionada">
                                        @foreach($oTrabajo as $oTrabajo)
                                            <option value="{{$oTrabajo->id}}">{{$moldurasOT[$contador]}} </option>
                                            @php
                                                $contador++;
                                            @endphp
                                        @endforeach
                                        </select>
                                    @else
                                        <div class="alert alert-danger">
                                            <i class="fa fa-check"></i>
                                            No hay ordenes de trabajo registradas.
                                        </div>
                                    @endisset
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center pt-3 text-muted" style="padding:10px; display:flex; justify-content:center;">
                        <button type="submit" class="btn btn-block text-center my-3" id="enviar">Aceptar</button>
                    </div>
                </div>
            @endif
        </div>
    </form>
    <script>
        let clase = document.getElementById('clases');
        clase.addEventListener('change', modificarSelect);
    </script>
</body>
@endsection