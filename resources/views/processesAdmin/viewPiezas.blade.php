@extends('layouts.appAdmin')
@section('content')

<head>
    <title>Piezas</title>
    @vite('resources/css/viewpiezas.css')
</head>

<body>
    <script>
        class Carrusel {
            constructor(otArray, molduras, clases, pedidos, procesos, infoPzMala, csrf, procesosClase) {
                this.indiCarru = 0;
                this.otArray = otArray;
                this.molduras = molduras;
                this.clases = clases;
                this.pedidos = pedidos;
                this.procesos = procesos;
                this.infoPzMala = infoPzMala;
                this.currentIndex = 0;
                this.contador = 0;
                this.csrf = csrf;
                this.procesosClase = procesosClase;
            }
            //Función para general carrusel
            generarCarrusel() {
                //Carrusel por ordenes de trabajo
                // console.log(this.clases);
                // console.log(this.otArray);
                // console.log(this.molduras);
                // console.log(this.pedidos);
                // console.log(this.procesosClase);
                // console.log(this.procesos);
                for (let indice = 0; indice < otArray.length; indice++) {
                    for (this.indiCarru = 0; this.indiCarru < this.clases[indice].length; this.indiCarru++) {
                        let main = document.querySelector('main');
                        //Nombres de los procesos insertados en un arreglo

                        let form = document.createElement('form');
                        form.method = "POST";
                        form.action = "{{ route('UpdatePiezas') }}";
                        //Agregar csrf a cada formulario
                        let csrf = document.createElement('input');
                        csrf.type = "hidden";
                        csrf.name = "_token";
                        csrf.value = this.csrf;
                        form.appendChild(csrf);
                        let section = document.createElement('section');
                        let carousel_container = document.createElement('div');
                        carousel_container.className = "carousel-container";

                        let carousel = document.createElement('div');
                        carousel.className = "carousel";

                        //Botones del carrusel
                        let prevBtn = document.createElement('button');
                        prevBtn.className = "prevBtn";
                        prevBtn.id = "prevBtn" + this.contador;
                        prevBtn.innerHTML = "<";
                        let nextBtn = document.createElement('button');
                        nextBtn.className = "nextBtn";
                        nextBtn.id = "nextBtn" + this.contador;
                        nextBtn.innerHTML = ">";
                        this.contador++;
                        let carousel_item_container = document.createElement('div');
                        carousel_item_container.className = "carousel-item-container";
                        carousel.appendChild(prevBtn);
                        let containers = this.procesosClase[indice][this.indiCarru].length / 3;//Numero de carruseles
                        let contadorElementos = [];
                        if (!Number.isInteger(containers)) {
                            containers = Math.trunc(containers) + 1;//Numero de carruseles
                            let aux = 0;
                            //Elementos por carrusel
                            for (let j = 0; j < containers; j++) {
                                if (containers - j == 1) {
                                    contadorElementos[j] = this.procesosClase[indice][this.indiCarru].length - aux;
                                } else {
                                        contadorElementos[j] = 3;
                                        aux += 3;
                                }
                            }
                        }else{
                            //Elementos por carrusel
                            for (let j = 0; j < containers; j++) {
                                contadorElementos[j] = 3;
                            }
                        }
                        //Carrusel por clases
                        let contadorProcesos = 0;
                        for (let i = 0; i < containers; i++) {
                            let carousel_item_container = document.createElement('div');
                            carousel_item_container.className = "carousel-item-container";
                            for (let j = 0; j < contadorElementos[i]; j++) {
                                console.log(this.otArray[indice]);
                                let carousel_item = document.createElement('div');
                                carousel_item.className = "carousel-item item";

                                //Titulo del proceso que se esta realizando
                                let h2 = document.createElement('h2');
                                h2.className = "title-proceso";
                                h2.innerHTML = this.procesosClase[indice][this.indiCarru][contadorProcesos];
                                carousel_item.appendChild(h2);
                                //Barra de progreso de piezas buenas, malas y progreso de piezas
                                for (let x = 0; x < 3; x++) {
                                    let label = document.createElement('label');
                                    label.className = "title-pieza";
                                    let bProgreso = document.createElement('div');
                                    bProgreso.className = "progress-bar";
                                    let progreso = document.createElement('div');
                                    progreso.className = "progreso";
                                    let progresoBarra;
                                    switch (x) {
                                        case 0:
                                            bProgreso.id = "b-buenas";
                                            progreso.id = "p-buenas";
                                            //Convertir a porcentaje
                                            progresoBarra = (this.procesos[indice][this.indiCarru][contadorProcesos][0] * 100) / this.pedidos[indice][this.indiCarru];
                                            progresoBarra = progresoBarra.toFixed(1);
                                            if(progresoBarra > 100){
                                                progresoBarra = 100;
                                            }
                                            progreso.style.width = progresoBarra + "%";
                                            label.innerHTML = "Juegos buenos: " + this.procesos[indice][this.indiCarru][contadorProcesos][0] + "/" + this.pedidos[indice][0] + " - Porcentaje: " + progresoBarra + "%";
                                            break;
                                        case 1:
                                            bProgreso.id = "b-malas";
                                            progreso.id = "p-malas";
                                            //Convertir a porcentaje
                                            progresoBarra = (this.procesos[indice][this.indiCarru][contadorProcesos][1] * 100) / this.pedidos[indice][this.indiCarru];
                                            progresoBarra = progresoBarra.toFixed(1);
                                            label.innerHTML = "Juegos malos: " + this.procesos[indice][this.indiCarru][contadorProcesos][1] + " - Porcentaje: " + progresoBarra + "%";
                                            progreso.style.width = progresoBarra + "%";
                                            break;
                                        case 2:
                                            //Convertir a porcentaje
                                            progresoBarra = (this.procesos[indice][this.indiCarru][contadorProcesos][2] * 100) / this.pedidos[indice][this.indiCarru];
                                            if(progresoBarra > 100){
                                                progresoBarra = 100;
                                            }
                                            progresoBarra = progresoBarra.toFixed(1);
                                            label.innerHTML = "Porcentaje de juegos finalizados: " + progresoBarra + "%";
                                            bProgreso.id = "b-progreso";
                                            progreso.id = "p-progreso";
                                            progreso.style.width = progresoBarra + "%";
                                            break;
                                    }
                                    carousel_item.appendChild(label);
                                    bProgreso.appendChild(progreso);
                                    carousel_item.appendChild(bProgreso);
                                }

                                carousel_item_container.appendChild(carousel_item);
                                if (i == 7) {
                                    break;
                                }
                                contadorProcesos++;
                            }
                            carousel.appendChild(carousel_item_container);
                        }
                        carousel.appendChild(nextBtn);
                        carousel_container.appendChild(carousel);
                        section.appendChild(carousel_container);

                        //Seccion de abajo
                        let section_abajo = document.createElement('div');
                        section_abajo.className = "seccion-abajo";

                        let div1 = document.createElement('div');
                        div1.className = "div1 item";
                        let div_ot = document.createElement('div');
                        div_ot.className = "div-ot";
                        let label = document.createElement('label');
                        label.innerHTML = this.otArray[indice] + " - " + this.clases[indice][this.indiCarru];
                        label.className = "ot";
                        let moldura = document.createElement('label');
                        moldura.className = "moldura";
                        moldura.innerHTML = this.molduras[indice];
                        div_ot.appendChild(label);
                        div_ot.appendChild(moldura);
                        div1.appendChild(div_ot);

                        let div_pedido = document.createElement('div');
                        div_pedido.className = "div-pedido";
                        let btn_pedido = document.createElement('button');
                        btn_pedido.className = "btn-pedido";
                        btn_pedido.innerHTML = "Terminar pedido";
                        btn_pedido.type = "submit";
                        let input_hidd = document.createElement('input');
                        input_hidd.type = "hidden";
                        input_hidd.name = "ot";
                        input_hidd.value = this.otArray[indice];
                        let input_hidd2 = document.createElement('input');
                        input_hidd2.type = "hidden";
                        input_hidd2.name = "clase";
                        input_hidd2.value = this.clases[indice][this.indiCarru];

                        let label1 = document.createElement('label');
                        label1.className = "piezas";
                        label1.innerHTML = this.procesos[indice][this.indiCarru][0][0] + "/" + this.pedidos[indice][this.indiCarru];

                        div_pedido.appendChild(label1);
                        div_pedido.appendChild(btn_pedido);
                        div_pedido.appendChild(input_hidd);
                        div_pedido.appendChild(input_hidd2);
                        div1.appendChild(div_pedido);

                        let div2 = document.createElement('div');
                        div2.className = "div2 item";

                        // Crer una tabla para los operadores que realizaron mal alguna pieza 
                        var table = document.createElement('table');
                        // Crear la fila de encabezado 
                        var thead = document.createElement('thead');
                        var headerRow = document.createElement('tr');

                        // Crear las celdas de encabezado en la cual va el nombre, no. de pieza, proceso, error de la pieza que esta mal creada
                        var headers = ['No. de pieza', 'No. de juego', 'Operador', 'Proceso', 'Operación', 'Error'];

                        headers.forEach(function(headerText) {
                            var th = document.createElement('th');
                            th.appendChild(document.createTextNode(headerText));
                            headerRow.appendChild(th);
                        });

                        thead.appendChild(headerRow);
                        table.appendChild(thead);

                        // Crear el cuerpo de la tabla
                        var tbody = document.createElement('tbody');
                        if (this.infoPzMala.length != 0) {
                            if (this.infoPzMala[indice] != undefined) {
                                if (this.infoPzMala[indice][this.indiCarru] != undefined) {
                                    for (let p = 0; p < this.infoPzMala[indice][this.indiCarru].length; p++) {
                                        // Agregar filas de datos (puedes repetir este bloque para agregar más filas)
                                        var dataRow = document.createElement('tr');
                                        for (let d = 0; d < this.infoPzMala[indice][this.indiCarru][p].length; d++) {
                                            var td = document.createElement('td');
                                            td.innerHTML = this.infoPzMala[indice][this.indiCarru][p][d];
                                            dataRow.appendChild(td);
                                        }
                                        tbody.appendChild(dataRow);
                                    }
                                }
                            }
                        }
                        // Agregar la fila de datos al cuerpo de la tabla

                        table.appendChild(tbody);
                        // Aplicar estilos CSS básicos
                        table.style.border = '1px solid #ccc';
                        table.style.borderCollapse = 'collapse';
                        table.style.width = '100%';

                        // Estilos para las celdas
                        var cellStyle = 'border: 1px solid #ccc; padding: 8px; text-align: left;';
                        var headerCellStyle = cellStyle + ' background-color: #f2f2f2;';

                        // Aplicar estilos a las celdas de encabezado 
                        table.querySelectorAll('td').forEach(function(td) {
                            td.style.cssText = cellStyle;
                        });

                        table.querySelectorAll('th').forEach(function(th) {
                            th.style.cssText = headerCellStyle;
                        });

                        // Agregar la tabla al documento
                        div2.appendChild(table);

                        section_abajo.appendChild(div1);
                        section_abajo.appendChild(div2);
                        section.appendChild(section_abajo);
                        form.appendChild(section);
                        main.appendChild(form);
                        this.proceso = 0;
                    }
                }
                this.movimientoCarrusel();
            }

            movimientoCarrusel() {
                const carousel = document.querySelectorAll('.carousel');
                const prevBtn = document.querySelectorAll('.prevBtn');
                const nextBtn = document.querySelectorAll('.nextBtn');
                let itemContainers = [];
                for (let i = 0; i < carousel.length; i++) {
                    itemContainers[i] = carousel[i].querySelectorAll('.carousel-item-container');
                    nextBtn[i].addEventListener('click', (event) => {
                        event.preventDefault();
                        this.nextItem(itemContainers[i])
                    });
                    prevBtn[i].addEventListener('click', (event) => {
                        event.preventDefault();
                        this.prevItem(itemContainers[i])
                    });
                }
                for (let i = 0; i < carousel.length; i++) {
                    setInterval(() => this.nextItem(itemContainers[i]), 10000);
                }
                for (let i = 0; i < carousel.length; i++) {
                    this.showItems(i, itemContainers);
                }
            }
            //Mostrar containers
            showItems(j, itemContainers) {
                itemContainers[j].forEach((container, i) => {
                    if (i === this.currentIndex) {
                        container.style.display = 'flex';
                    } else {
                        container.style.display = 'none';
                    }
                });
            }
            // Función para avanzar al siguiente elemento en un carrusel
            nextItem(items) {
                for (let i = 0; i < items.length; i++) {
                    if (items[i].style.display === 'flex') {
                        items[i].style.display = 'none';
                        this.currentIndex = (i + 1) % items.length;
                        items[this.currentIndex].style.display = 'flex';
                        break;
                    }
                }
            }
            // Función para retroceder al elemento anterior en un carrusel
            prevItem(items) {
                for (let i = 0; i < items.length; i++) {
                    if (items[i].style.display === 'flex') {
                        items[i].style.display = 'none';
                        this.currentIndex = (i - 1 + items.length) % items.length;
                        items[this.currentIndex].style.display = 'flex';
                        break;
                    }
                }
            }
        }
    </script>
    @isset($ot)
    <div class="container">
        <main>
            <script>
                let otArray = @json($otArray);
                let molduras = @json($molduras);
                let clases = @json($clases);
                let pedidos = @json($pedidos);
                let procesos = @json($procesos);
                let infoPzMala = @json($infoPzMala);
                let csrf = "{{ csrf_token() }}";
                let procesosClase = @json($procesosClase);
                let carrusel = new Carrusel(otArray, molduras, clases, pedidos, procesos, infoPzMala, csrf, procesosClase);
                carrusel.generarCarrusel();
                setTimeout(function() {
                    location.reload();
                }, 50000);
            </script>
        </main>
    </div>
    @else
    <div class="fondo">
        <div class="alerta">
            <!-- Imagen de error dentro del formulario -->
            <img src="{{ asset('images/error.png') }}" alt="Error">
            <label>No hay ordenes de trabajo en proceso.</label>
        </div>
    </div>
    @endisset
</body>
@endsection