@extends('layouts.appAdmin')
@section('content')
<head>
    <title>Procesos</title>
    @vite('resources/css/verProcesos.css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.10.1/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.7/ScrollMagic.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.7/plugins/animation.gsap.min.js"></script>
</head>
<script>
    class OrdenTrabajo{
        constructor(ot){
            this.ot = ot;
        }
        crearTabla(){
            let div_table = document.getElementById('scrollabe-table');
            let table = document.createElement('table');
            table.id = "scroll-content";
            table = this.generarEncabezado(table);

            for(let i=0; i<this.ot.length; i++){
                this.generarFilas(table, i);
            }
            div_table.appendChild(table);
        }
        generarEncabezado(table){
            let tr = document.createElement('tr');
            tr.style = "background-color: #D1D2DB";
            //Crear el encabezado de la tabla
            let encabezado = ['O.T', 'Nombre', 'Clase', 'Cepillado', 'Desbaste exterior', 'Revision de laterales', '1ra operación soldadura', '2da operación soldadura','1ra y 2da operación equipo','Barreno maniobra', 'Reporte diario de soldaduras', 'Soldadura PTA', 'Reporte diario de rectificado', 'Reporte diario de asentado', 'Revision calificado', 'Revision acabados bombillo', 'Revision acabados molde', 'Reporte diario de cavidades', 'Barreno para platos', 'Maquinado embudos', 'Barreno de profundidad', 'Reporte de copiado', 'Ranura OffSet', 'Grabado', 'Palomas', 'Rebajes al centro'];

            //Crear el encabezado de las tablas
            for(let i=0; i<encabezado.length; i++){
                let th = document.createElement('th');
                th.innerHTML = encabezado[i];
                if(i == 0 || i == 1 || i == 2){
                    if(i == 0){
                        th.style.width = '50px';
                    }else{
                        th.style.width = '300px';
                    }
                }else{
                    th.className = "t-title"
                }
                tr.appendChild(th);
            }
            table.appendChild(tr);
            return table;
        }
        generarFilas(table, indice){
            let tr = document.createElement('tr');
            if(indice % 2 == 1){
                tr.style = "background-color: #D1D2DB";
            }
            for(let j=0; j<this.ot[indice].length; j++){
                //Si no es par
                let td = document.createElement('td');
                if(j == 0 || j == 1 || j == 2){
                    td.innerHTML = this.ot[indice][j];
                    td.style = "padding: 20px"
                }else{
                    if(j != this.ot[indice].length - 1){
                        //Barra de progreso
                        let div = document.createElement('div');
                        div.className = 'progress-bar-container';
                        let progress_bar = document.createElement('div');
                        progress_bar.className = "progress-bar";
                        if(this.ot[indice][j] != 0){
                            progress_bar.style = "width:" + this.calcularPorcentaje(this.ot[indice][j], this.ot[indice][this.ot[indice].length - 1]) + "%;";
                        }
                        div.innerHTML = this.ot[indice][j] + "/" + this.ot[indice][this.ot[indice].length - 1];
                        div.appendChild(progress_bar);
                        td.appendChild(div);
                    }
                }
                tr.appendChild(td);
            }
            table.appendChild(tr);
        }
        calcularPorcentaje(progreso, total){
            return (progreso * 100) / total;

        }
        startScroll(){
            let step = 1;
            intervalo = setInterval(function () {
                scroll.scrollLeft += step;
                if (scroll.scrollLeft >= scroll.scrollWidth - scroll.offsetWidth) {
                    step = -1;
                }else if(scroll.scrollLeft === 0){
                    step = 1;
                }
            }, 10);
        }
        stopScroll(intervalo){
            clearInterval(intervalo);
        }

    }
</script>
<body background="{{ asset('images/fondopzas.png') }}">
    <div class="container">
        <h1>Progreso de las OT</h1>
        <div id="scrollabe-table"></div>
        @if (isset($ot))
            <script>
                let ot = @json($ot);
                let ordenT = new OrdenTrabajo(ot);
                console.log(ot);
                ordenT.crearTabla();

                //Scroll automtico
                let scroll = document.getElementById('scrollabe-table');
                let intervalo;
                ordenT.startScroll();
            </script>
        @endif
    </div>
@endsection