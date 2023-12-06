@extends('layouts.appAdmin')
@vite('resources/css/procesos.css')
@section('content')
<script>
class Proceso {
    constructor(nameProceso, valoresCnomi, valoresTole) { // Constructor
        this.nameProceso = nameProceso; // Nombre del proceso
        this.valoresCnomi = valoresCnomi; // Valores de c.nominal 
        this.valoresTole = valoresTole; // Valores de tolerancias
    }

    crearProceso() { // Crear proceso
        let titulos = []; // Titulos de la tabla
        let cNominal = []; // C.nominal
        let cNomiPosiciones = []; // Posiciones de los inputs de c.nominal
        let tolerancias = []; // Tolerancias
        let tolePosiciones = []; // Posiciones de los inputs de tolerancias 
        switch (this.nameProceso) { // Segun el proceso
            case "Cepillado": //Proceso de cepillado
                titulos = ['', 'Radio final de mordaza', 'Radio final mayor', 'Radio final de sufridera', 'Profundidad final conexión Fondo/Corona', 'Profundidad final mitad de Molde/Bombillo', 'Profundidad final Pico/Conexión de obturador', 'Ensamble', 'Distancia de barreno de alineación', 'Profundidad de barreno de alineación', 'Altura de vena', 'Ancho de vena', 'PIN'];

                cNominal = ['C.nominal', 'cNomi_radiof_mordaza', 'cNomi_radiof_mayor', 'cNomi_radiof_sufridera', 'cNomi_profuFinal_CFC', 'cNomi_profuFinal_mitadMB', 'cNomi_profuFinal_PCO', 'cNomi_ensamble', 'cNomi_distancia_barrenoAli', 'cNomi_profu_barrenoAli', 'cNomi_altura_vena', 'cNomi_ancho_vena', 'Hpin1[]', 'Hpin2[]'];
                cNomiPosiciones = [12]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_radiof_mordaza1', 'tole_radiof_mordaza2', 'tole_radiof_mayor1', 'tole_radiof_mayor2', 'tole_radiof_sufridera1', 'tole_radiof_sufridera2', 'tole_profuFinal_CFC1', 'tole_profuFinal_CFC2', 'tole_profuFinal_mitadMB1', 'tole_profuFinal_mitadMB2', 'tole_profuFinal_PCO1', 'tole_profuFinal_PCO2', 'tole_ensamble1', 'tole_ensamble2', 'H', 'M', 'H', 'M', 'H', 'M', 'H', 'M', 'Hpin1[]', 'Hpin2[]']; 
                tolePosiciones = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23]; // Posiciones de los inputs de tolerancias
                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['radiof_mordaza'] , this.valoresCnomi['radiof_mayor'], this.valoresCnomi['radiof_sufridera'], this.valoresCnomi['profuFinal_CFC'], this.valoresCnomi['profuFinal_mitadMB'], this.valoresCnomi['profuFinal_PCO'], this.valoresCnomi['ensamble'], this.valoresCnomi['distancia_barrenoAli'], this.valoresCnomi['profu_barrenoAli'], this.valoresCnomi['altura_vena'], this.valoresCnomi['ancho_vena'], this.valoresCnomi['pin1'], this.valoresCnomi['pin2']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['radiof_mordaza1'], this.valoresTole['radiof_mordaza2'], this.valoresTole['radiof_mayor1'], this.valoresTole['radiof_mayor2'], this.valoresTole['radiof_sufridera1'], this.valoresTole['radiof_sufridera2'], this.valoresTole['profuFinal_CFC1'], this.valoresTole['profuFinal_CFC2'], this.valoresTole['profuFinal_mitadMB1'], this.valoresTole['profuFinal_mitadMB2'], this.valoresTole['profuFinal_PCO1'], this.valoresTole['profuFinal_PCO2'], this.valoresTole['ensamble1'], this.valoresTole['ensamble2'], 'H', 'M', 'H', 'M', 'H', 'M', 'H', 'M', this.valoresTole['pin1'], this.valoresTole['pin2']];
                    console.log(this.valoresTole['pin2']);
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined); // Crear tabla
                
            case "desbaste": //Proceso de cepillado
                titulos = ['', 'Diametro de mordaza', 'Diametro de ceja', 'Diametro de sufridera/Extra', 'Simetría ceja', 'Simetría Mordaza', 'Altura de ceja', 'Altura sufridera'];

                cNominal = ['C.nominal', 'cNomi_diametro_mordaza', 'cNomi_diametro_ceja', 'cNomi_diametro_sufrideraExtra', 'cNomi_simetria_ceja', 'cNomi_simetria_mordaza', 'cNomi_altura_ceja', 'cNomi_altura_sufridera'];
                cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_diametro_mordaza1', 'tole_diametro_mordaza2', 'tole_diametro_ceja1', 'tole_diametro_ceja2', 'tole_diametro_sufrideraExtra1', 'tole_diametro_sufrideraExtra2', 'tole_simetria_ceja1', 'tole_simetria_ceja2', 'tole_simetria_mordaza1', 'tole_simetria_mordaza2', 'tole_altura_ceja1', 'tole_altura_ceja2', 'tole_altura_sufridera1', 'tole_altura_sufridera2']; 
                tolePosiciones = [1, 3, 5, 7, 9, 11, 13]; // Posiciones de los inputs de tolerancias
                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['diametro_mordaza'] , this.valoresCnomi['diametro_ceja'], this.valoresCnomi['diametro_sufrideraExtra'], this.valoresCnomi['simetria_ceja'], this.valoresCnomi['simetria_mordaza'], this.valoresCnomi['altura_ceja'], this.valoresCnomi['altura_sufridera']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['diametro_mordaza1'], this.valoresTole['diametro_mordaza2'], this.valoresTole['diametro_ceja1'], this.valoresTole['diametro_ceja2'], this.valoresTole['diametro_sufrideraExtra1'], this.valoresTole['diametro_sufrideraExtra2'], this.valoresTole['simetria_ceja1'], this.valoresTole['simetria_ceja2'], this.valoresTole['simetria_mordaza1'], this.valoresTole['simetria_mordaza2'], this.valoresTole['altura_ceja1'], this.valoresTole['altura_ceja2'], this.valoresTole['altura_sufridera1'], this.valoresTole['altura_sufridera2']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined); // Crear tabla
            
            case "revLaterales": //Proceso de cepillado
                titulos = ['', 'Desfasamiento Entrada', 'Desfasamiento Salida', 'Ancho de simetria Entrada', 'Ancho de simetria Salida', 'Angulo de corte'];

                cNominal = ['C.nominal', 'cNomi_desfasamiento_entrada', 'cNomi_desfasamiento_salida', 'cNomi_ancho_simetriaEntrada', 'cNomi_ancho_simetriaSalida', 'cNomi_angulo_corte'];
                cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_desfasamiento_entrada1', 'tole_desfasamiento_entrada2', 'tole_desfasamiento_salida1', 'tole_desfasamiento_salida2', 'tole_ancho_simetriaEntrada1', 'tole_ancho_simetriaEntrada2', 'tole_ancho_simetriaSalida1', 'tole_ancho_simetriaSalida2', 'tole_angulo_corte1', 'tole_angulo_corte2']; 
                tolePosiciones = [1, 3, 5, 7, 9]; // Posiciones de los inputs de tolerancias
                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['desfasamiento_entrada'] , this.valoresCnomi['desfasamiento_salida'], this.valoresCnomi['ancho_simetriaEntrada'], this.valoresCnomi['ancho_simetriaSalida'], this.valoresCnomi['angulo_corte']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['desfasamiento_entrada1'], this.valoresTole['desfasamiento_entrada2'], this.valoresTole['desfasamiento_salida1'], this.valoresTole['desfasamiento_salida2'], this.valoresTole['ancho_simetriaEntrada1'], this.valoresTole['ancho_simetriaEntrada2'], this.valoresTole['ancho_simetriaSalida1'], this.valoresTole['ancho_simetriaSalida2'], this.valoresTole['angulo_corte1'], this.valoresTole['angulo_corte2']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }   
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined); // Crear tabla

            case "primeraOpeSoldadura": //Proceso de cepillado
                titulos = ['', 'Diametro 1', 'Profundidad 1 ', 'Diametro 2', 'Profundidad 2', 'Diametro 3', 'Profunfidad 3','Diametro de soldadura', 'Profundidad de soldadura', 'Diametro de barreno', 'Simetria línea de partida', 'Perno de alineación', 'Simetría a 90°'];

                cNominal = ['C.nominal', 'cNomi_diametro1', 'cNomi_profundidad1', 'cNomi_diametro2', 'cNomi_profundidad2', 'cNomi_diametro3', 'cNomi_profundidad3', 'cNomi_diametroSoldadura', 'cNomi_diametroBarreno', 'cNomi_profundidadSoldadura', 'cNomi_simetriaLinea_partida', 'cNomi_pernoAlineacion', 'cNomi_Simetria90G'];
                cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_diametro1', 'tole_profundidad1', 'tole_diametro2', 'tole_profundidad2', 'tole_diametro3', 'tole_profundidad3', 'tole_diametroSoldadura', 'tole_profundidadSoldadura', 'tole_diametroBarreno1', 'tole_diametroBarreno2', 'tole_simetriaLinea_partida1', 'tole_simetriaLinea_partida2', 'tole_pernoAlineacion', 'tole_Simetria90G']; 
                tolePosiciones = [9, 11]; // Posiciones de los inputs de tolerancias
                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['diametro1'] , this.valoresCnomi['profundidad1'], this.valoresCnomi['diametro2'], this.valoresCnomi['profundidad2'], this.valoresCnomi['diametro3'], this.valoresCnomi['profundidad3'], this.valoresCnomi['diametroSoldadura'], this.valoresCnomi['diametroBarreno'], this.valoresCnomi['profundidadSoldadura'], this.valoresCnomi['simetriaLinea_partida'], this.valoresCnomi['pernoAlineacion'], this.valoresCnomi['Simetria90G']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['diametro1'], this.valoresTole['profundidad1'], this.valoresTole['diametro2'], this.valoresTole['profundidad2'], this.valoresTole['diametro3'], this.valoresTole['profundidad3'], this.valoresTole['diametroSoldadura'], this.valoresTole['profundidadSoldadura'], this.valoresTole['diametroBarreno1'], this.valoresTole['diametroBarreno2'], this.valoresTole['simetriaLinea_partida1'], this.valoresTole['simetriaLinea_partida2'], this.valoresTole['pernoAlineacion'], this.valoresTole['Simetria90G']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }   
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined); // Crear tabla

            case "segundaOpeSoldadura": //Proceso de cepillado
                titulos = ['', 'Diametro 1', 'Profundidad 1 ', 'Diametro 2', 'Profundidad 2', 'Diametro 3', 'Profunfidad 3','Diametro de soldadura', 'Profundidad de soldadura', 'Altura total', 'Simetría a 90°', 'Simetria línea de partida'];

                cNominal = ['C.nominal', 'cNomi_diametro1', 'cNomi_profundidad1', 'cNomi_diametro2', 'cNomi_profundidad2', 'cNomi_diametro3', 'cNomi_profundidad3', 'cNomi_diametroSoldadura', 'cNomi_profundidadSoldadura', 'cNomi_alturaTotal', 'cNomi_Simetria90G', 'cNomi_simetriaLinea_partida'];
                cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_diametro1', 'tole_profundidad1', 'tole_diametro2', 'tole_profundidad2', 'tole_diametro3', 'tole_profundidad3', 'tole_diametroSoldadura', 'tole_profundidadSoldadura', 'tole_alturaTotal1', 'tole_alturaTotal2', 'tole_Simetria90G1', 'tole_Simetria90G2', 'tole_simetriaLinea_partida']; 
                tolePosiciones = [9, 11]; // Posiciones de los inputs de tolerancias
                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['diametro1'] , this.valoresCnomi['profundidad1'], this.valoresCnomi['diametro2'], this.valoresCnomi['profundidad2'], this.valoresCnomi['diametro3'], this.valoresCnomi['profundidad3'], this.valoresCnomi['diametroSoldadura'], this.valoresCnomi['profundidadSoldadura'], this.valoresCnomi['alturaTotal'], this.valoresCnomi['Simetria90G'], this.valoresCnomi['simetriaLinea_partida']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['diametro1'], this.valoresTole['profundidad1'], this.valoresTole['diametro2'], this.valoresTole['profundidad2'], this.valoresTole['diametro3'], this.valoresTole['profundidad3'], this.valoresTole['diametroSoldadura'], this.valoresTole['profundidadSoldadura'], this.valoresTole['alturaTotal1'], this.valoresTole['alturaTotal2'], this.valoresTole['Simetria90G1'], this.valoresTole['Simetria90G2'], this.valoresTole['simetriaLinea_partida']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }   
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined); // Crear tabla
                
            case "pysOpeSoldadura": //Proceso de cepillado
                titulos = ['', 'Altura', 'ø Altura de candado', 'Altura asiento obturador', 'ø Profundidad de soldadura', 'ø de PushUp'];

                cNominal = ['C.nominal', 'cNomi_altura', 'cNomi_alturaCandado1', 'cNomi_alturaCandado2', 'cNomi_alturaAsientoObturador1', 'cNomi_alturaAsientoObturador2', 'cNomi_profundidadSoldadura1', 'cNomi_profundidadSoldadura2', 'cNomi_pushUp'];
                cNomiPosiciones = [2, 4, 6]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_altura', 'tole_alturaCandado1', 'tole_alturaCandado2', 'tole_alturaAsientoObturador1', 'tole_alturaAsientoObturador2', 'tole_profundidadSoldadura1', 'tole_profundidadSoldadura2', 'tole_pushUp']; 
                tolePosiciones = [2, 4, 6]; // Posiciones de los inputs de tolerancias

                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['altura'] , this.valoresCnomi['alturaCandado1'], this.valoresCnomi['alturaCandado2'], this.valoresCnomi['alturaAsientoObturador1'], this.valoresCnomi['alturaAsientoObturador2'], this.valoresCnomi['profundidadSoldadura1'], this.valoresCnomi['profundidadSoldadura2'], this.valoresCnomi['pushUp']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['altura'], this.valoresTole['alturaCandado1'], this.valoresTole['alturaCandado2'], this.valoresTole['alturaAsientoObturador1'], this.valoresTole['alturaAsientoObturador2'], this.valoresTole['profundidadSoldadura1'], this.valoresTole['profundidadSoldadura2'], this.valoresTole['pushUp']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }   
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined); // Crear tabla

            default:
                return 'No se encontro el proceso'; //Retorna el mensaje de que el proceso no existe
        }
    }

    crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCNomi, valoresTole) { // Crear tabla
        const table = document.createElement('table'); // Crear tabla
        table.className = 'tabla3'; // Agregar clase a la tabla

        for (let i = 0; i < 3; i++) { // Crear filas
            const tr = document.createElement('tr'); // Crear fila

            switch (i) { // Crear columnas
                case 0: // Crear columnas de titulos
                    for (let j = 0; j < titulos.length; j++) { // Crear columnas
                        const th = document.createElement('th'); // Crear columna
                        th.className = 't-title'; // Agregar clase a la columna
                        if (j == 0) { // Si es la primera columna
                            th.style = "width:150px;"; // Agregar estilo a la columna
                        }
                        th.innerHTML = titulos[j]; // Agregar texto a la columna
                        tr.appendChild(th); // Agregar columna a la fila
                    }
                    break;

                case 1: // Crear columnas de cNominal
                    console.log(cNomiPosiciones);
                    for (let j = 0; j < cNominal.length; j++) { // Crear columnas
                        const td = document.createElement('td'); // Crear columna
                        if (j != 0) { //Si no es la primera columna.
                            if (cNomiPosiciones.includes(j)) { //Si la posición esta en el array de posiciones.
                                console.log(j);
                                for (let k = 0; k < 2; k++) { // Crear inputs
                                    if(valoresCNomi != undefined){
                                        //Valores de c nominal
                                        td.appendChild(this.crearInputs('input-medio', cNominal[j], valoresCNomi[j])); // Crear inputs
                                    }else{
                                        td.appendChild(this.crearInputs('input-medio', cNominal[j], undefined)); // Crear inputs
                                    }
                                    if(k != 1){
                                        j++; //Aumentar j
                                    }
                                }
                            } else {
                                if(valoresCNomi != undefined){
                                    //Valores de c nominal
                                    td.appendChild(this.crearInputs('input', cNominal[j], valoresCNomi[j])); // Crear inputs
                                }else{
                                    td.appendChild(this.crearInputs('input', cNominal[j], undefined)); // Crear inputs            
                                }
                            }
                        } else {
                            td.innerHTML = cNominal[j]; //Agregar texto a la columna.
                        }
                        tr.appendChild(td); //Agregar columna a la fila.
                    }
                    break;

                case 2: // Crear columnas de tolerancias
                    for (let j = 0; j < tolerancias.length; j++) { // Crear columnas
                        const td = document.createElement('td'); // Crear columna
                        if (j != 0) { //Si no es la primera columna 
                            if (tolePosiciones.includes(j)) { // Si la posicion esta en el array de posiciones
                                for (let k = 0; k < 2; k++) { // Crear inputs
                                    if (j >= 15 && j <= 22) { //Si es H o M
                                        let input = this.crearInputs('input-medio', tolerancias[j]); // Crear inputs
                                        input.type = 'text'; // Agregar tipo al input
                                        input.value = tolerancias[j] ; // Agregar valor al input
                                        input.readOnly = true; // Agregar readonly al input
                                        td.appendChild(input); // Agregar input a la columna
                                    } else {
                                        if(valoresTole != undefined){ //Valores de tolerancia
                                            td.appendChild(this.crearInputs('input-medio', tolerancias[j], valoresTole[j])); // Crear inputs
                                        }else{
                                            td.appendChild(this.crearInputs('input-medio', tolerancias[j], undefined)); // Crear inputs
                                        }
                                    }
                                    if (k != 1) { //Si no es el segundo input.
                                        j++; // Aumentar j
                                    } 
                                }
                            } else {
                                if(valoresTole != undefined){ //Valores de tolerancias
                                    td.appendChild(this.crearInputs('input', tolerancias[j], valoresTole[j])); // Crear inputs
                                }else{
                                    td.appendChild(this.crearInputs('input', tolerancias[j], undefined)); // Crear inputs
                                }
                            }
                        } else {
                            td.innerHTML = tolerancias[j]; // Agregar texto a la columna
                        }
                        tr.appendChild(td); // Agregar columna a la fila
                    }
                    break; //Termina
            }
            table.appendChild(tr); //Agregar fila a la tabla.
        }
        return table; // Retornar tabla.
    }
    crearInputs(className, name, valor) { // Crear inputs
        let input = document.createElement('input'); // Crear input
        input.className = className; // Agregar clase al input
        input.type = 'text'; // Agregar tipo al input
        input.name = name; // Agregar nombre al input
        input.step = 'any'; // Agregar step al input
        input.inputMode = "decimal"; // Agregar inputMode al input
        input.required = "true";
        //Agregar el required al input
        if(valor != undefined){
            //Si el valor es diferente de undefined
            input.value = valor;
            //Agrega el valor al input
        }
        return input; // Retornar input
    }
}

    //Funciones individuales
    function agregarSelect(){
        let div = document.getElementById("row"); //Div donde se agregara el select
        if(selectOp.value == "pysOpeSoldadura"){ //Si el proceso es pysOpeSoldadura
            let select = document.createElement('select'); // Crear select
            select.id = 'select-operacion'; // Agregar id al select
            select.name = 'operacion'; // Agregar nombre al select
            for(let i=1; i<=2; i++){ //Crear option de operaciones 
                let option = document.createElement('option'); // Crear option
                option.value = i; // Agregar valor al option 
                option.innerHTML = i + ' operacion soldadura'; // Agregar texto al option
                select.appendChild(option); // Agregar option al select
            }
            div.appendChild(select); // Agregar select al div
        }else{
            if(document.getElementById("select-operacion") != null){
                div.removeChild(document.getElementsByTagName('select')[2]);
            }
        }
    }

    function selectProcesos(procesos, clases){
        let title_div = document.getElementById("row-title");
        let label = document.createElement('label');
        label.className = "title";
        label.id = "proceso-label";
        label.innerHTML = 'Selecciona el proceso';
        let labelExistente = document.getElementById("proceso-label");
        eliminarElemento(labelExistente);
        title_div.appendChild(label);

        let selectExistente = document.getElementById("select-proceso");
        let selectClase = document.getElementById("select-clase"); //Select de las clases 
        if(selectClase.length > clases.length){
            selectClase.removeChild(selectClase.options[0]);
        }
        let div = document.getElementById("row"); //Div donde se agregara el select 
        for(let i=0; i<clases.length; i++){ //Recorre las clases
            if(selectClase.value == clases[i][0]["id"]){ //Si el valor del select es igual a la clase
                eliminarElemento(selectExistente);
                let selectCreate = document.createElement("select"); //Crear un select 
                selectCreate.id = "select-proceso"; //Agrega el id al select
                selectCreate.name = "proceso"; //Agrega el nombre de los procesos al select
                for(let j=0; j<procesos[i].length; j++){ //Crea la opción de los procesos
                    let option = document.createElement("option"); //Crea el option 
                    option.text = procesos[i][j]; //Agrega el texto al option
                    option.value = procesos[i][j]; //Agrega el valor al option 
                    selectCreate.appendChild(option); //Agrega el option al select 
                }
                selectCreate.addEventListener("change", function(){
                    agregarSelect();
                });
                div.appendChild(selectCreate); //Agrega el select al div
                break;
            }
        }
        let form = document.getElementsByClassName("form-search");
        let button = document.createElement('button');
        button.className = "btn";
        button.innerHTML = "Aceptar";
        button.type = "submit";
        button.id = "btn-sub";
        
        let btnExistente = document.getElementById("btn-sub");
        eliminarElemento(btnExistente);
        form[0].appendChild(button);
    }
    function eliminarElemento(elemento){
        if(elemento != null){
            elemento.remove();
        }
    }
</script>
<body background="{{ asset('images/fondoLogin.jpg') }}">
<div class="container-select">
    <div class="search">
        <img src="{{asset('/images/lg_saavedra.png')}}" alt="lg-saavedra" class="search-img">
        <form action="{{ route('verificarProceso') }}" method="post" class="form-search">
            @csrf
            @include('layouts.partials.messages')
            <div class="row">
                <h1 class="title">Selecciona la orden de trabajo:</h1>
            </div>
            @if (isset($clases) || isset($clase))
                <input type="text" class="ot" value="{{$ot}}" readonly>
                <div class="disabled">
                    <div class="row" id="row-title">
                        @isset($proceso)
                            @if ($proceso == "pysOpeSoldadura")
                                <label class="title">Selecciona la operación:</label>
                            @endif
                        @else
                            <label class="title" style="margin-left: 60px;">Selecciona la clase:</label>
                        @endisset
                        
                    </div>
                    @if (isset($proceso))
                        <div class="row">
                            <label class="title" style="margin-left: 150px;">Clase:</label>
                            <label class="title" style="margin-left: 200px;">Proceso:</label>
                        </div>
                        <div class="row" id="row">
                            <!--Valor de la clase elegida-->
                            <input type="text" class="ot" value="{{$clase->nombre}}" readonly>
                            <input type="hidden" name="clase" class="ot" value="{{$clase->id}}">
                            <!--Valor del proceso elegido-->
                            <input type="text" id="select-proceso" class="ot" value="{{$proceso}}" readonly>
                        </div>
                    @else
                        <div class="row" id="row">
                            <select id="select-clase" name="clase">
                                <option></option>
                                @foreach ($clases as $class)
                                    <option value="{{$class[0]->id}}">{{$class[0]->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                        <script>
                            //Modificar los procesos de acuerdo a la clase seleccionada
                            let select_clase = document.getElementById("select-clase"); 
                            select_clase.addEventListener("change", function(){  
                                selectProcesos(@json($procesos), @json($clases));
                            });
                        </script>
                    @endif
                </div>
            @else
                <select name="ot">
                    @foreach ($ot as $ot)
                        <option value="{{$ot->id}}">{{$ot->id}}</option>
                    @endforeach
                </select>
                <button class="btn">Aceptar</button>
            @endif
        </form>
    </div>
</div>
<div class="container">
    <form action="{{ route('verificarProceso') }}" method="post" class="form-search">
    @csrf
        <div class="scrollabe-table" id="scrollabe-table" style="display: none;">
            @if (isset($existe) && $existe == 0) 
                <script>
                    alert('No se ha encontrado el proceso, es necesario crearlo.'); 
                    const select = document.getElementById('select-proceso'); // Select de proceso.
                    if (select.value != "") { // Si el select tiene un valor.
                        let proceso = new Proceso(select.value, undefined, undefined); // Crear proceso
                        let div = document.getElementById('scrollabe-table'); //Div de la tabla.
                        div.innerHTML = ""; // Limpiar div
                        div.style = "display:block;"; //No acepta a ningún otro elemento más en esa fila, es decir los demás elementos bajan 
                        div.appendChild(proceso.crearProceso()); //Agregar tabla al div.
                    }
                </script>
            @elseif (isset($existe) && $existe == 1)
            <script>
                alert('Datos de cotas nominales Encontrados/Guardados');
                let proceso = new Proceso(@json($proceso), @json($cNominal), @json($tolerancia)); // Crear el proceso
                let div = document.getElementById('scrollabe-table'); //Div de la tabla.
                div.style = "display:block;"; 
                div.appendChild(proceso.crearProceso()); //Agregar tabla al div.
            </script>
                
            @endif
            <!--Enviar valores de procesos-->
            @if(isset($proceso) && $proceso == 'Cepillado')
                <input type="hidden" name="proceso" value="Cepillado">
                <input type="hidden" name="clase" class="ot" value="{{$clase->id}}">
            @elseif (isset($proceso) && $proceso == 'desbaste')
                <input type="hidden" name="proceso" value="desbaste">
                <input type="hidden" name="clase" class="ot" value="{{$clase->id}}">
            @elseif (isset($proceso) && $proceso == 'revLaterales')
                <input type="hidden" name="proceso" value="revLaterales">
                <input type="hidden" name="clase" class="ot" value="{{$clase->id}}">
            @elseif (isset($proceso) && $proceso == 'primeraOpeSoldadura')
                <input type="hidden" name="proceso" value="primeraOpeSoldadura">
                <input type="hidden" name="clase" class="ot" value="{{$clase->id}}">
            @elseif (isset($proceso) && $proceso == 'segundaOpeSoldadura')
                <input type="hidden" name="proceso" value="segundaOpeSoldadura">
                <input type="hidden" name="clase" class="ot" value="{{$clase->id}}">
            @elseif (isset($proceso) && $proceso == 'pysOpeSoldadura')
                <input type="hidden" name="proceso" value="pysOpeSoldadura">
                <input type="hidden" name="clase" class="ot" value="{{$clase->id}}">
                <input type="hidden" name="operacion" class="ot" value="{{$operacion}}">
            @endif
        </div>
        <!--Aparecer boton-->
        @if(isset($existe))
            <button class="btn" id="btn"">Guardar</button>
        @endif
    </form>
</div>
</body>
@endsection