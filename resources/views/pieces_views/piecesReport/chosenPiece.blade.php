@extends('layouts.appMenu')

@section('head')
<title>Visualizacion de pieza</title>
@vite('resources/css/pieces_views/piecesReport/chosenPiece.css')
@endsection

@section('background-body', 'background-image:url("' . asset("images/fondoLogin.jpg") . '")') <!--Body background Image-->
@section('content')
<script>
    class Proceso {
        constructor(nameProceso, valoresCnomi, valoresTole, valorPieza) { // Constructor
            this.nameProceso = nameProceso; // Nombre del proceso
            this.valoresCnomi = valoresCnomi; // Valores de c.nominal 
            this.valoresTole = valoresTole; // Valores de tolerancias
            this.valoresPieza = valorPieza;
        }

        crearProceso() { // Crear proceso
            let titulos = []; // Titulos de la tabla
            let cNomiPosiciones = []; // Posiciones de los inputs de c.nominal
            let tolePosiciones = []; // Posiciones de los inputs de tolerancias
            let piezaPosiciones = []; // Posiciones de los inputs de tolerancias
            let valoresCnomi = []; // Valores de c.nominal
            let valoresTole = []; // Valores de tolerancias
            let valoresPieza = []; // Valores de la pieza
            let nombres = []; // Nombres de los inputs de pieza
            let nombresCnomi = []; // Nombres de los inputs de c.nominal
            let nombresTole = []; // Nombres de los inputs de tolerancias

            switch (this.nameProceso) { // Segun el proceso
                case "Cepillado": //Proceso de cepillado
                    titulos = ['No.Pieza', 'Radio final de mordaza', 'Radio final mayor', 'Radio final de sufridera', 'Profundidad final conexión Fondo/Corona', 'Profundidad final mitad de Molde/Bombillo', 'Profundidad final Pico/Conexión de obturador', 'Ensamble', 'Distancia de barreno de alineación', 'Profundidad de barreno de alineación Hembra', 'Profundidad de barreno de alineación Macho', 'Altura de vena Hembra', 'Altura de vena Macho', 'Ancho de vena', 'Laterales', 'PIN', 'Error', 'Observaciones'];

                    cNomiPosiciones = [15]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [15];

                    nombresCnomi = ['id', 'radiof_mordaza', 'radiof_mayor', 'radiof_sufridera', 'profuFinal_CFC', 'profuFinal_mitadMB', 'profuFinal_PCO', 'ensamble', 'distancia_barrenoAli', 'profu_barrenoAliHembra', 'profu_barrenoAliMacho', 'altura_venaHembra', 'altura_venaMacho', 'ancho_vena', 'laterales', 'pin1', 'pin2'];

                    nombresTole = ['id', 'radiof_mordaza1', 'radiof_mordaza2', 'radiof_mayor1', 'radiof_mayor2', 'radiof_sufridera1', 'radiof_sufridera2', 'profuFinal_CFC1', 'profuFinal_CFC2', 'profuFinal_mitadMB1', 'profuFinal_mitadMB2', 'profuFinal_PCO1', 'profuFinal_PCO2', 'ensamble1', 'ensamble2', 'distancia_barrenoAli1', 'distancia_barrenoAli2', 'profu_barrenoAliHembra1', 'profu_barrenoAliHembra2', 'profu_barrenoAliMacho1', 'profu_barrenoAliMacho2', 'altura_venaHembra1', 'altura_venaHembra2', 'altura_venaMacho1', 'altura_venaMacho2', 'ancho_vena1', 'ancho_vena2', 'laterales1', 'laterales2', 'pin1', 'pin2'];

                    nombres = ['n_pieza', 'radiof_mordaza', 'radiof_mayor', 'radiof_sufridera', 'profuFinal_CFC', 'profuFinal_mitadMB', 'profuFinal_PCO', 'ensamble', 'distancia_barrenoAli', 'profu_barrenoAliHembra', 'profu_barrenoAliMacho', 'altura_venaHembra', 'altura_venaMacho', 'ancho_vena', 'laterales', 'pin1', 'pin2', 'error', 'observaciones'];
                    break;

                case "Desbaste Exterior": //Proceso de desbaste Exterior
                    titulos = ['No.Pieza', 'Diametro de mordaza', 'Diametro de ceja', 'Diametro de sufridera/Extra', 'Simetría ceja', 'Simetría Mordaza', 'Altura de ceja', 'Altura sufridera', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [1, 3, 5, 7, 9, 11, 13]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null];

                    nombresCnomi = ['id', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufrideraExtra', 'simetria_ceja', 'simetria_mordaza', 'altura_ceja', 'altura_sufridera'];
                    nombresTole = ['id', 'diametro_mordaza1', 'diametro_mordaza2', 'diametro_ceja1', 'diametro_ceja2', 'diametro_sufrideraExtra1', 'diametro_sufrideraExtra2', 'simetria_ceja1', 'simetria_ceja2', 'simetria_mordaza1', 'simetria_mordaza2', 'altura_ceja1', 'altura_ceja2', 'altura_sufridera1', 'altura_sufridera2'];
                    nombres = ['n_pieza', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufrideraExtra', 'simetria_ceja', 'simetria_mordaza', 'altura_ceja', 'altura_sufridera', 'error', 'observaciones'];
                    break;
                case "Revision Laterales": //Proceso de revision laterales
                    titulos = ['No.Pieza', 'Desfasamiento Entrada', 'Desfasamiento Salida', 'Ancho de simetria Entrada', 'Ancho de simetria Salida', 'Angulo de corte', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]
                    tolePosiciones = [1, 3, 5, 7, 9]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null]

                    nombresCnomi = ['id', 'desfasamiento_entrada', 'desfasamiento_salida', 'ancho_simetriaEntrada', 'ancho_simetriaSalida', 'angulo_corte'];

                    nombresTole = ['id', 'desfasamiento_entrada1', 'desfasamiento_entrada2', 'desfasamiento_salida1', 'desfasamiento_salida2', 'ancho_simetriaEntrada1', 'ancho_simetriaEntrada2', 'ancho_simetriaSalida1', 'ancho_simetriaSalida2', 'angulo_corte1', 'angulo_corte2'];

                    nombres = ['n_pieza', 'desfasamiento_entrada', 'desfasamiento_salida', 'ancho_simetriaEntrada', 'ancho_simetriaSalida', 'angulo_corte', 'error', 'observaciones'];
                    break;

                case "Primera Operacion Soldadura": //Proceso de primera operacion
                    titulos = ['No.Pieza', 'Diametro 1', 'Profundidad 1 ', 'Diametro 2', 'Profundidad 2', 'Diametro 3', 'Profunfidad 3', 'Diametro de soldadura', 'Profundidad de soldadura', 'Diametro de barreno', 'Simetria línea de partida', 'Perno de alineación', 'Simetría a 90°', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [9, 11]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null];

                    nombresCnomi = ['id', 'diametro1', 'profundidad1', 'diametro2', 'profundidad2', 'diametro3', 'profundidad3', 'diametroSoldadura', 'profundidadSoldadura', 'diametroBarreno', 'simetriaLinea_partida', 'pernoAlineacion', 'Simetria90G'];

                    nombresTole = ['id', 'diametro1', 'profundidad1', 'diametro2', 'profundidad2', 'diametro3', 'profundidad3', 'diametroSoldadura', 'profundidadSoldadura', 'diametroBarreno1', 'diametroBarreno2', 'simetriaLinea_partida1', 'simetriaLinea_partida2', 'pernoAlineacion', 'Simetria90G'];

                    nombres = ['n_pieza', 'diametro1', 'profundidad1', 'diametro2', 'profundidad2', 'diametro3', 'profundidad3', 'diametroSoldadura', 'profundidadSoldadura', 'diametroBarreno', 'simetriaLinea_partida', 'pernoAlineacion', 'Simetria90G', 'error', 'observaciones'];
                    break;

                case "Barreno Maniobra": //Proceso de barreno maniobra
                    titulos = ['No. Pieza', 'Profundidad de Barreno', 'Diametro de machuelo', 'Acetato B/M', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [1, 3]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null];

                    nombresCnomi = ['id', 'profundidad_barreno', 'diametro_machuelo', ''];
                    nombresTole = ['id', 'profundidad_barreno1', 'profundidad_barreno2', 'diametro_machuelo1', 'diametro_machuelo2', ''];
                    nombres = ['n_pieza', 'profundidad_barreno', 'diametro_machuelo', 'acetatoBM', 'error', 'observaciones'];
                    break;

                case "Segunda Operacion Soldadura": //Proceso de segunda operacion
                    titulos = ['No. Pieza', 'Diametro 1', 'Profundidad 1 ', 'Diametro 2', 'Profundidad 2', 'Diametro 3', 'Profunfidad 3', 'Diametro de soldadura', 'Profundidad de soldadura', 'Altura total', 'Simetría a 90°', 'Simetria línea de partida', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [9, 11]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null];

                    nombresCnomi = ['id', 'diametro1', 'profundidad1', 'diametro2', 'profundidad2', 'diametro3', 'profundidad3', 'diametroSoldadura', 'profundidadSoldadura', 'alturaTotal', 'simetria90G', 'simetriaLinea_Partida'];

                    nombresTole = ['id', 'diametro1', 'profundidad1', 'diametro2', 'profundidad2', 'diametro3', 'profundidad3', 'diametroSoldadura', 'profundidadSoldadura', 'alturaTotal1', 'alturaTotal2', 'simetria90G1', 'simetria90G2', 'simetriaLinea_Partida'];

                    nombres = ['n_pieza', 'diametro1', 'profundidad1', 'diametro2', 'profundidad2', 'diametro3', 'profundidad3', 'diametroSoldadura', 'profundidadSoldadura', 'alturaTotal', 'simetria90G', 'simetriaLinea_Partida', 'error', 'observaciones'];
                    break;

                case 'Soldadura':
                    titulos = ['No. Pieza', 'Peso x Pieza', 'Temperatura precalentado ', 'Tiempo de aplicación', 'Tipo de soldadura', 'Lote', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [null]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null];

                    nombres = ['n_juego', 'pesoxpieza', 'temperatura_precalentado', 'tiempo_aplicacion', 'tipo_soldadura', 'lote', 'error', 'observaciones'];
                    break;

                case 'Soldadura PTA':
                    titulos = ['No. Pieza', 'Temperatura de calentado', 'Temperatura en dispositivo ', 'Limpieza', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [null]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null];

                    valoresCnomi = null;

                    valoresTole = null;

                    nombres = ['n_juego', 'temp_calentado', 'temp_dispositivo', 'limpieza', 'error', 'observaciones'];
                    break;

                case 'Rectificado':
                    titulos = ['No. Pieza', 'cumple', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [null]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null];

                    valoresCnomi = null;

                    valoresTole = null;

                    nombres = ['n_juego', 'cumple', 'error', 'observaciones'];
                    break;

                case "Calificado": //Proceso de calificado
                    titulos = ['No. Pieza', 'Diametro de ceja', 'Diametro de sufridera', 'Altura de sufridera', 'Diametro de conexion', 'Altura de conexion', 'Diametro de caja', 'Altura de caja', 'Altura total', 'Simetria', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [1, 3, 5, 7, 9, 11, 13, 15, 17]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null];

                    nombresCnomi = ['id', 'diametro_ceja', 'diametro_sufridera', 'altura_sufridera', 'diametro_conexion', 'altura_conexion', 'diametro_caja', 'altura_caja', 'altura_total', 'simetria'];

                    nombresTole = ['id', 'diametro_ceja1', 'diametro_ceja2', 'diametro_sufridera1', 'diametro_sufridera2', 'altura_sufridera1', 'altura_sufridera2', 'diametro_conexion1', 'diametro_conexion2', 'altura_conexion1', 'altura_conexion2', 'diametro_caja1', 'diametro_caja2', 'altura_caja1', 'altura_caja2', 'altura_total1', 'altura_total2', 'simetria1', 'simetria2'];

                    nombres = ['n_juego', 'diametro_ceja', 'diametro_sufridera', 'altura_sufridera', 'diametro_conexion', 'altura_conexion', 'diametro_caja', 'altura_caja', 'altura_total', 'simetria', 'error', 'observaciones'];
                    break;

                case "Acabado Bombillo": //Proceso de acabado Bombillo
                    titulos = ['No. Pieza', 'Diametro de mordaza', 'Diametro de ceja', 'Diametro de sufridera', 'Altura de mordaza', 'Altura de ceja', 'Altura de sufridera', 'Guage Ceja', 'Guage Corona', 'Guage Llanta', 'Altura total', 'Diametro Boca', 'Diametro Asiento Corona', 'Diametro llanta', 'Diametro caja corona', 'Profundidad corona', 'Angulo de 30', 'Profundidad caja corona', 'Simetria', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [1, 3, 5, 7, 9, 11, 17, 19, 21, 23, 25, 27, 29, 31]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null];

                    nombresCnomi = ['id', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufridera', 'altura_mordaza', 'altura_ceja', 'altura_sufridera', '', '', '', 'altura_total', 'diametro_boca', 'diametro_asiento_corona', 'diametro_llanta', 'diametro_caja_corona', 'profundidad_corona', 'angulo_30', 'profundidad_caja_corona', 'simetria'];

                    nombresTole = ['id', 'diametro_mordaza1', 'diametro_mordaza2', 'diametro_ceja1', 'diametro_ceja2', 'diametro_sufridera1', 'diametro_sufridera2', 'altura_mordaza1', 'altura_mordaza2', 'altura_ceja1', 'altura_ceja2', 'altura_sufridera1', 'altura_sufridera2', '', '', '', '', 'diametro_boca1', 'diametro_boca2', 'diametro_asiento_corona1', 'diametro_asiento_corona2', 'diametro_llanta1', 'diametro_llanta2', 'diametro_caja_corona1', 'diametro_caja_corona2', 'profundidad_corona1', 'profundidad_corona2', 'angulo_301', 'angulo_302', 'profundidad_caja_corona1', 'profundidad_caja_corona2', 'simetria1', 'simetria2'];

                    nombres = ['n_juego', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufridera', 'altura_mordaza', 'altura_ceja', 'altura_sufridera', 'gauge_ceja', 'gauge_corona', 'gauge_llanta', 'altura_total', 'diametro_boca', 'diametro_asiento_corona', 'diametro_llanta', 'diametro_caja_corona', 'profundidad_corona', 'angulo_30', 'profundidad_caja_corona', 'simetria', 'error', 'observaciones'];
                    break;

                case "Acabado Molde": //Proceso de acabado molde
                    titulos = ['No. Pieza', 'Diametro de mordaza', 'Diametro de ceja', 'Diametro de sufridera', 'Altura de mordaza', 'Altura de ceja', 'Altura de sufridera', 'gaugue_ceja', 'altura_total', 'Diametro Conexion Fondo', 'Diametro llanta', 'Diametro Caja Fondo', 'Altura Conexion Fondo', 'Profundidad Llanta', 'Profundidad Caja Fondo', 'Simetria', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [1, 3, 5, 7, 9, 11, 15, 17, 19, 21, 23, 25, 27]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null];

                    nombresCnomi = ['id', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufridera', 'altura_mordaza', 'altura_ceja', 'altura_sufridera', 'gauge_ceja', 'altura_total', 'diametro_conexion_fondo', 'diametro_llanta', 'diametro_caja_fondo', 'altura_conexion_fondo', 'profundidad_llanta', 'profundidad_caja_fondo', 'simetria'];

                    nombresTole = ['id', 'diametro_mordaza1', 'diametro_mordaza2', 'diametro_ceja1', 'diametro_ceja2', 'diametro_sufridera1', 'diametro_sufridera2', 'altura_mordaza1', 'altura_mordaza2', 'altura_ceja1', 'altura_ceja2', 'altura_sufridera1', 'altura_sufridera2', '', '', 'diametro_conexion_fondo1', 'diametro_conexion_fondo2', 'diametro_llanta1', 'diametro_llanta2', 'diametro_caja_fondo1', 'diametro_caja_fondo2', 'altura_conexion_fondo1', 'altura_conexion_fondo2', 'profundidad_llanta1', 'profundidad_llanta2', 'profundidad_caja_fondo1', 'profundidad_caja_fondo2', 'simetria1', 'simetria2'];

                    nombres = ['n_juego', 'diametro_mordaza', 'diametro_ceja', 'diametro_sufridera', 'altura_mordaza', 'altura_ceja', 'altura_sufridera', 'gauge_ceja', 'altura_total', 'diametro_conexion_fondo', 'diametro_llanta', 'diametro_caja_fondo', 'altura_conexion_fondo', 'profundidad_llanta', 'profundidad_caja_fondo', 'simetria', 'error', 'observaciones'];
                    break;

                case 'Barreno Profundidad':
                    titulos = ['No. Pieza', 'Broca 1', 'Tiempo 1', 'Broca 2', 'Tiempo 2', 'Broca 3', 'Tiempo 3', 'Entrada / Salida', 'Diametro de arrastre 1', 'Diametro de arrastre 2', 'Diametro de arrastre 3', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [7]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [7];

                    nombresTole = ['id', 'broca1', 'tiempo1', 'broca2', 'tiempo2', 'broca3', 'tiempo3', 'entrada', 'salida', 'diametro_arrastre1', 'diametro_arrastre2', 'diametro_arrastre3'];
                    nombresCnomi = ['id', 'broca1', 'tiempo1', 'broca2', 'tiempo2', 'broca3', 'tiempo3', 'entradaSalida', 'diametro_arrastre1', 'diametro_arrastre2', 'diametro_arrastre3'];
                    nombres = ['n_juego', 'broca1', 'tiempo1', 'broca2', 'tiempo2', 'broca3', 'tiempo3', 'entrada', 'salida', 'diametro_arrastre1', 'diametro_arrastre2', 'diametro_arrastre3', 'error', 'observaciones'];
                    break;

                case "Copiado": //Proceso de copiado
                    if (subproceso == 'Cilindrado') {
                        titulos = ['No. Pieza', 'Diametro 1', 'Profundidad 1', 'Diametro 2', 'Profundidad 2', 'Diametro de sufridera', 'Diametro Ranura', 'Profundidad Ranura', 'Profundidad de sufridera', 'ALTURA TOTAL', 'Error', 'Observaciones'];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [null]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'diametro1_cilindrado', 'profundidad1_cilindrado', 'diametro2_cilindrado', 'profundidad2_cilindrado', 'diametro_sufridera', 'diametro_ranura', 'profundidad_ranura', 'profundidad_sufridera', 'altura_total'];
                        nombresTole = ['id', 'diametro1_cilindrado', 'profundidad1_cilindrado', 'diametro2_cilindrado', 'profundidad2_cilindrado', 'diametro_sufridera', 'diametro_ranura', 'profundidad_ranura', 'profundidad_sufridera', 'altura_total'];
                        nombres = ['n_juego', 'diametro1_cilindrado', 'profundidad1_cilindrado', 'diametro2_cilindrado', 'profundidad2_cilindrado', 'diametro_sufridera', 'diametro_ranura', 'profundidad_ranura', 'profundidad_sufridera', 'altura_total', 'error', 'observaciones'];
                    } else {
                        titulos = ['No. Pieza', 'Diametro 1', 'Profundidad 1', 'Diametro 2', 'Profundidad 2', 'Diametro 3', 'Profundidad 3', 'Diametro 4', 'Profundidad 4', ' VOLUMEN ', 'Error', 'Observaciones'];

                        cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                        tolePosiciones = [null]; // Posiciones de los inputs de tolerancias
                        piezaPosiciones = [null];

                        nombresCnomi = ['id', 'diametro1_cavidades', 'profundidad1_cavidades', 'diametro2_cavidades', 'profundidad2_cavidades', 'diametro3', 'profundidad3', 'diametro4', 'profundidad4', 'volumen'];
                        nombresTole = ['id', 'diametro1_cavidades', 'profundidad1_cavidades', 'diametro2_cavidades', 'profundidad2_cavidades', 'diametro3', 'profundidad3', 'diametro4', 'profundidad4', 'volumen'];
                        nombres = ['n_juego', 'diametro1_cavidades', 'profundidad1_cavidades', 'diametro2_cavidades', 'profundidad2_cavidades', 'diametro3', 'profundidad3', 'diametro4', 'profundidad4', 'volumen', 'error', 'observaciones'];
                    }
                    break;

                case "Palomas": //Proceso de palomas
                    titulos = ['No. Pieza', 'Ancho de Paloma', 'Grueso de Paloma', 'Profundidad de Paloma', 'Rebaje de llanta', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [null]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null];

                    nombresCnomi = ['id', 'anchoPaloma', 'gruesoPaloma', 'profundidadPaloma', 'rebajeLlanta'];
                    nombresTole = ['id', 'anchoPaloma', 'gruesoPaloma', 'profundidadPaloma', 'rebajeLlanta'];
                    nombres = ['n_juego', 'anchoPaloma', 'gruesoPaloma', 'profundidadPaloma', 'rebajeLlanta', 'error', 'observaciones'];
                    break;

                case "Rebajes": //Proceso de Rebajes
                    titulos = ['No. Pieza', 'Rebaje 1', 'Rebaje 2', 'Rebaje 3', 'Profundidad de Bordonio', 'Vena 1', 'Vena 2', 'Simetria', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [null]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null];

                    nombresCnomi = ['id', 'rebaje1', 'rebaje2', 'rebaje3', 'profundidad_bordonio', 'vena1', 'vena2', 'simetria'];
                    nombresTole = ['id', 'rebaje1', 'rebaje2', 'rebaje3', 'profundidad_bordonio', 'vena1', 'vena2', 'simetria'];
                    nombres = ['n_juego', 'rebaje1', 'rebaje2', 'rebaje3', 'profundidad_bordonio', 'vena1', 'vena2', 'simetria', 'error', 'observaciones'];
                    break;
                case "Operacion Equipo": //Proceso de Rebajes
                    titulos = ['No. Pieza', 'Altura', 'ø  Altura de candado', 'Altura asiento obturador', 'ø Profundidad Soldadura', 'ø de PushUp', 'Error', 'Observaciones'];

                    cNomiPosiciones = [2, 4, 6]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [2, 4, 6]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [2, 4, 6];

                    nombresCnomi = ['id', 'altura', 'alturaCandado1', 'alturaCandado2', 'alturaAsientoObturador1', 'alturaAsientoObturador2', 'profundidadSoldadura1', 'profundidadSoldadura2', 'pushUp'];
                    nombresTole = ['id', 'altura', 'alturaCandado1', 'alturaCandado2', 'alturaAsientoObturador1', 'alturaAsientoObturador2', 'profundidadSoldadura1', 'profundidadSoldadura2', 'pushUp'];
                    nombres = ['n_pieza', 'altura', 'alturaCandado1', 'alturaCandado2', 'alturaAsientoObturador1', 'alturaAsientoObturador2', 'profundidadSoldadura1', 'profundidadSoldadura2', 'pushUp', 'error', 'observaciones'];
                    break;
                case "Embudo CM": //Proceso de Rebajes
                    titulos = ['No. Pieza', 'Conexión línea de partida', 'Conexión a 90°', 'Altura de conexión', 'Diametro embudo', 'Error', 'Observaciones'];

                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal
                    tolePosiciones = [null]; // Posiciones de los inputs de tolerancias
                    piezaPosiciones = [null];

                    nombresCnomi = ['id', 'conexion_lineaPartida', 'conexion_90G', 'altura_conexion', 'diametro_embudo'];
                    nombresTole = ['id', 'conexion_lineaPartida', 'conexion_90G', 'altura_conexion', 'diametro_embudo'];
                    nombres = ['n_juego', 'conexion_lineaPartida', 'conexion_90G', 'altura_conexion', 'diametro_embudo', 'error', 'observaciones'];
                    break;
                default:
                    return 'No se encontro el proceso'; //Retorna el mensaje de que el proceso no existe
            }
            //Almacenar valores
            valoresCnomi = this.almacenarCNomiAndTole(nombresCnomi, this.valoresCnomi);
            valoresTole = this.almacenarCNomiAndTole(nombresTole, this.valoresTole);
            valoresPieza = this.almacenarPieza(nombres);

            return this.crearTabla(titulos, cNomiPosiciones, tolePosiciones, piezaPosiciones, valoresCnomi, valoresTole, valoresPieza); // Crear tabla
        }

        almacenarCNomiAndTole(nombres, valoresReales) {
            let valores = [];
            //Insertar valores
            if (valoresReales != 0) {
                for (let i = 0; i < nombres.length; i++) {
                    if (valoresReales[nombres[i]] == undefined) {
                        valores.push('');
                    } else {
                        valores.push(valoresReales[nombres[i]]);
                    }
                }
                //Insertar espacios vacios
                for (let i = 0; i < 2; i++) {
                    valores.push('');
                }
            } else {
                valores = null;
            }
            return valores;
        }

        almacenarPieza(nombres) {
            let valores = [];
            for (let i = 0; i < this.valoresPieza.length; i++) {
                valores.push([]);
                for (let j = 0; j < nombres.length; j++) {
                    if (this.valoresPieza[i]['correcto'] == null && j != 0) {
                        valores[i].push('----');
                    } else {
                        valores[i].push(this.valoresPieza[i][nombres[j]]);
                    }
                }
            }
            return valores;
        }

        crearTabla(titulos, cNomiPosiciones, tolePosiciones, piezaPosiciones, valoresCNomi, valoresTole, valoresPieza) { // Crear tabla
            const table = document.createElement('table'); // Crear tabla
            table.className = 'tabla3';

            for (let i = 0; i < 4; i++) { // Crear filas
                const tr = document.createElement('tr'); // Crear fila
                switch (i) { // Crear columnas
                    case 0: // Crear columnas de titulos
                        for (let j = 0; j < titulos.length; j++) { // Crear columnas
                            const th = document.createElement('th'); // Crear columna
                            th.className = 't-title'; // Agregar clase a la columna
                            if (j == 0) { // Si es la primera columna
                                th.style = "width:150px;"; // Agregar estilo a la columna
                            }
                            if (titulos[j] == 'Observaciones') {
                                th.style = "width:1050px;"; // Agregar estilo a la columna
                            }
                            th.innerHTML = titulos[j]; // Agregar texto a la columna
                            tr.appendChild(th); // Agregar columna a la fila
                        }
                        table.appendChild(tr); //Agregar fila a la tabla.
                        break;

                    case 1: // Crear columnas de cNominal
                        if (valoresCNomi != null) {
                            for (let j = 0; j < valoresCNomi.length; j++) { // Crear columnas
                                const td = document.createElement('td'); // Crear columna
                                if (j != 0) { //Si no es la primera columna.
                                    if (cNomiPosiciones.includes(j)) { //Si la posición esta en el array de posiciones.
                                        for (let k = 0; k < 2; k++) { // Crear inputs
                                            //Valores de c nominal
                                            td.appendChild(this.crearInputs('input-medio', valoresCNomi[j])); // Crear inputs
                                            if (k != 1) {
                                                j++; //Aumentar j
                                            }
                                        }
                                    } else {
                                        td.appendChild(this.crearInputs('input', valoresCNomi[j])); // Crear inputs
                                    }
                                } else {
                                    td.innerHTML = 'C.Nominal'; //Agregar texto a la columna.
                                }
                                tr.appendChild(td); //Agregar columna a la fila.
                            }
                        }
                        table.appendChild(tr); //Agregar fila a la tabla.
                        break;

                    case 2: // Crear columnas de tolerancias
                        if (valoresTole != null) {
                            for (let j = 0; j < valoresTole.length; j++) { // Crear columnas
                                if (j == valoresTole.length) {
                                    break;
                                }
                                const td = document.createElement('td'); // Crear columna
                                if (j != 0) { //Si no es la primera columna 
                                    if (tolePosiciones.includes(j)) { // Si la posicion esta en el array de posiciones
                                        for (let k = 0; k < 2; k++) { // Crear inputs
                                            td.appendChild(this.crearInputs('input-medio', valoresTole[j])); // Crear inputs
                                            if (k != 1) { //Si no es el segundo input.
                                                j++; // Aumentar j
                                            }
                                        }
                                    } else {
                                        td.appendChild(this.crearInputs('input', valoresTole[j])); // Crear inputs
                                    }
                                } else {
                                    td.innerHTML = 'Tolerancias'; // Agregar texto a la columna
                                }
                                tr.appendChild(td); // Agregar columna a la fila
                            }
                        }
                        table.appendChild(tr); //Agregar fila a la tabla.
                        break;
                    case 3: // Crear columnas de pieza
                        for (let j = 0; j < valoresPieza.length; j++) { // Crear columnas
                            let tr = document.createElement('tr'); // Crear fila
                            for (let p = 0; p < valoresPieza[j].length; p++) {
                                let error = false;
                                const td = document.createElement('td'); // Crear celda
                                if (piezaPosiciones.includes(p)) { // Si la posicion esta en el array de posiciones de la pieza
                                    for (let k = 0; k < 2; k++) { // Crear inputs
                                        if (valoresCNomi != null && valoresTole != null) {
                                            error = this.getError(valoresPieza[j][p], p, valoresCNomi, valoresTole, tolePosiciones, cNomiPosiciones);
                                        }
                                        td.appendChild(this.crearInputs('input-medio', valoresPieza[j][p], error)); // Crear inputs
                                        if (k != 1) { //Si no es el segundo input.
                                            p++; // Aumentar p
                                        }
                                    }
                                } else {
                                    if (p != 0) {
                                        // console.log(valoresPieza[j][p]);
                                        if (valoresCNomi != null && valoresTole != null) {
                                            error = this.getError(valoresPieza[j][p], p, valoresCNomi, valoresTole, tolePosiciones, cNomiPosiciones);
                                        }
                                        // console.log(error);
                                    }
                                    td.appendChild(this.crearInputs('input', valoresPieza[j][p], error)); // Crear inputs
                                }
                                tr.appendChild(td); // Agregar columna a la fila
                            }
                            table.appendChild(tr); //Agregar fila a la tabla
                        }
                        break;
                }
            }
            return table; // Retornar tabla.
        }
        crearInputs(className, valor, error) { // Crear inputs
            let input = document.createElement('input'); // Crear input
            input.className = className; // Agregar clase al input
            input.type = 'text'; // Agregar tipo al input
            input.step = 'any'; // Agregar step al input
            input.inputMode = "decimal"; // Agregar inputMode al input
            input.value = valor; // Agregar valor al input
            input.disabled = 'true';
            if (error != undefined) {
                if (error === true) {
                    input.style = 'border: 3px solid red;';
                }
            }
            return input; // Retornar input
        }
        getError(valorPieza, posicion, valoresCnomi, valoresTole, tolePosiciones, cNomiPosiciones) {
            let posicionesTole = [];
            for (let i = 0; i < valoresTole.length; i++) {
                posicionesTole.push(i);
                if (tolePosiciones.includes(i)) {
                    i++;
                }
            }
            let error = false;
            if (cNomiPosiciones.includes(posicion) || cNomiPosiciones.includes(posicion - 1)) {
                if (cNomiPosiciones.includes(posicion - 1)) {
                    if (parseFloat(valorPieza) < parseFloat(parseFloat(valoresCnomi[posicion]) - parseFloat(valoresTole[posicionesTole[posicion - 1]] + 1)).toFixed(3) || parseFloat(valorPieza) > parseFloat(parseFloat(valoresCnomi[posicion]) + parseFloat(valoresTole[posicionesTole[posicion - 1]] + 1)).toFixed(3)) {
                        error = true;
                    }
                }
                if (parseFloat(valorPieza) < parseFloat(parseFloat(valoresCnomi[posicion]) - parseFloat(valoresTole[posicionesTole[posicion]])).toFixed(3) || parseFloat(valorPieza) > parseFloat(parseFloat(valoresCnomi[posicion]) + parseFloat(valoresTole[posicionesTole[posicion]])).toFixed(3)) {
                    error = true;
                }
            } else {
                if (tolePosiciones.includes(posicionesTole[posicion])) {
                    for (let i = 0; i < 2; i++) {
                        if (i == 0) {
                            if (parseFloat(valorPieza) > parseFloat(parseFloat(valoresCnomi[posicion]) + parseFloat(valoresTole[posicionesTole[posicion]])).toFixed(3)) {
                                error = true;
                            }
                        } else {
                            if (parseFloat(valorPieza) < parseFloat(parseFloat(valoresCnomi[posicion]) - parseFloat(valoresTole[posicionesTole[posicion] + 1])).toFixed(3)) {
                                error = true;
                            }
                        }
                    }
                } else {
                    // console.log(parseFloat(valoresTole[posicion]).toFixed(3));
                    // if (parseFloat(valorPieza) < parseFloat(parseFloat(valoresCnomi[posicion]) - parseFloat(valoresTole[posicionesTole[posicion]])).toFixed(3) || parseFloat(valorPieza) > parseFloat(parseFloat(valoresCnomi[posicion]) + parseFloat(valoresTole[posicionesTole[posicion]])).toFixed(3)) {
                    //     error = true;
                    // }
                    if (parseFloat(valorPieza) < parseFloat(parseFloat(valoresCnomi[posicion]) - parseFloat(valoresTole[posicion])).toFixed(3) || parseFloat(valorPieza) > parseFloat(parseFloat(valoresCnomi[posicion]) + parseFloat(valoresTole[posicion])).toFixed(3)) {
                        error = true;
                    }
                }
            }
            console.log('Valor pieza: ' + valorPieza + ' Posicion: ' + posicion + ' Valores Cnomi: ' + valoresCnomi[posicion] + ' Valores Tole: ' + valoresTole[posicionesTole[posicion]] + ' Error: ' + error);
            return error;
        }

        convertirObjectToArray(obj) {
            let array = [];
            for (let i = 0; i < obj.length; i++) {
                array.push(Object.values(obj[i]));
            }
            return array;
        }
        crearTablaOperadores(operadores) {
            const table = document.createElement('table');
            table.className = "tablaOperadores";
            table.style.borderCollapse = 'collapse'; // Colapsar los bordes de las celdas
            table.style.border = '1px solid black';


            const tbody = document.createElement('tbody');
            for (let i = 0; i < (operadores.length + 1); i++) {
                const tr = document.createElement('tr');
                switch (i) {
                    case 0:
                        const th1 = document.createElement('th');
                        th1.textContent = "No. Pieza";
                        const th2 = document.createElement('th');
                        th2.textContent = "Operador";
                        tr.appendChild(th1);
                        tr.appendChild(th2);
                        th1.style.border = '1px solid black';
                        th2.style.border = '1px solid black';

                        break;

                    default:
                        for (let j = 0; j < operadores[i - 1].length; j++) {
                            const td = document.createElement('td');
                            td.textContent = operadores[i - 1][j];
                            tr.appendChild(td);
                        }
                        tr.querySelectorAll('td').forEach(td => {
                            td.style.border = '1px solid black';

                            tr.appendChild(td);
                        });
                        break;
                }
                tr.style.border = '1px solid black';
                tbody.appendChild(tr);
            }
            table.appendChild(tbody);
            return table;
        }
    }
</script>
@if ($process == 'Soldadura' || $process == 'Soldadura PTA' || $process == 'Rectificado' || $process == 'Palomas')
<style>
    .tabla3 {
        width: 130%;
    }
</style>
@endif
@if ($process == 'Copiado')
<style>
    .scrollabe-table {
        height: 500px;
    }
</style>
@endif

<body background="{{ asset('images/fondoLogin.jpg') }}">

    <div class="container" id="container">

        <a href="javascript:history.back()"" class=" btn-regresar">Regresar</a>

        <script>
            let process = new Proceso(@json($process), @json($cNominal), @json($tolerance), @json($piecesInfo)); // Crear el proceso
            document.getElementById('container').appendChild(process.crearTablaOperadores(@json($operadores))); // Crear tabla de operadores
        </script>
        @csrf
        <div class="titles">
            <label class="title">{{$ot}}</label>
            <label class="title">{{$clase}} - {{$process}}</label>
        </div>
        <div class="scrollabe-table" id="scrollabe-table">
            @if ($process == 'Asentado')
            <table border="1" class="tabla3" style="width: 100%;">
                <tr>
                    <th class="t-title" style="width:150px">#PZ</th>
                    <th class="t-title">Sin juego</th>
                    <th class="t-title">Sin luz</th>
                    <th class="t-title">Error</th>
                    <th class="t-title" style="width:700px">Observaciones</th>
                </tr>
                <tr>
                    <td><input type="text" class="input" value="{{$piezasInfo->n_juego}}" disabled></td>
                    <td><input type="text" class="input" value="{{$piezasInfo->sin_juego}}" disabled></td>
                    <td><input type="text" class="input" value="{{$piezasInfo->sin_luz}}" disabled></td>
                    <td><input type="text" class="input" value="{{$piezasInfo->error}}" disabled /></td>
                    <td><input type="text" class="input" value="{{$piezasInfo->observaciones}}" disabled /></td>
                </tr>
            </table>

            @elseif ($process == 'Cavidades')
            <table class="tabla3">
                <tr>
                    <th class="t-title" style="width:150px; border:none;">#PZ</th>
                    <th class="t-title" colspan="2">Altura 1</th>
                    <th class="t-title" colspan="2">Altura 2</th>
                    <th class="t-title" colspan="2">Altura 3</th>
                    <th class="t-title"></th>
                    <th class="t-title" colspan="2"></th>
                </tr>
                <tr>
                    <th class="t-title" style="border:none;"></th>
                    <th>Profundidad</th>
                    <th>Diametro</th>
                    <th>Profundidad</th>
                    <th>Diametro</th>
                    <th>Profundidad</th>
                    <th>Diametro</th>
                    <th>Acetato B/M</th>
                    <th>Error</th>
                    <th style="width: 1000px;">Observaciones</th>
                </tr>
                <tr>
                    <td>C.Nominal</td>
                    <td><input type="number" value="{{$cNominal->profundidad1}}" class="input" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" value="{{$cNominal->diametro1}}" class="input" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" value="{{$cNominal->profundidad2}}" class="input" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" value="{{$cNominal->diametro2}}" class="input" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" value="{{$cNominal->profundidad3}}" class="input" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" value="{{$cNominal->diametro3}}" class="input" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" disabled></td>
                    <td><input type="number" class="input" disabled></td>
                    <td><input type="number" class="input" disabled></td>
                </tr>
                <tr>
                    <td> Tolerancias </td>
                    <td><input type="number" value="{{$tolerancia->profundidad1_1}}" class="input-medio" step="any" inputmode="decimal" disabled><input type="number" value="{{$tolerancia->profundidad2_1}}" class="input-medio" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" value="{{$tolerancia->diametro1_1}}" class="input-medio" step="any" inputmode="decimal" disabled><input type="number" value="{{$tolerancia->diametro2_1}}" class="input-medio" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" value="{{$tolerancia->profundidad1_2}}" class="input-medio" step="any" inputmode="decimal" disabled><input type="number" value="{{$tolerancia->profundidad2_2}}" class="input-medio" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" value="{{$tolerancia->diametro1_2}}" class="input-medio" step="any" inputmode="decimal" disabled><input type="number" value="{{$tolerancia->diametro2_2}}" class="input-medio" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" value="{{$tolerancia->profundidad1_3}}" class="input-medio" step="any" inputmode="decimal" disabled><input type="number" value="{{$tolerancia->profundidad2_3}}" class="input-medio" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" value="{{$tolerancia->diametro1_3}}" class="input-medio" step="any" inputmode="decimal" disabled><input type="number" value="{{$tolerancia->diametro2_3}}" class="input-medio" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" disabled></td>
                    <td><input type="number" class="input" disabled></td>
                    <td><input type="number" class="input" disabled></td>
                </tr>
                <tr>
                    <td><input type="text" class="input" value="{{$piezasInfo->n_juego}}" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->profundidad1}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->diametro1}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->profundidad2}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->diametro2}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->profundidad3}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->diametro3}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="text" class="input" value="{{$piezasInfo->acetatoBM}}" disabled></td>
                    <td><input type="text" class="input" value="{{$piezasInfo->error}}" disabled></td>
                    <td><input type="text" class="input" value="{{$piezasInfo->observaciones}}" disabled></td>
                </tr>
            </table>
            @elseif ($process == 'Copiado')
            <table border="1" class="tabla3">
                <label class="title-subproceso"> C I L I N D R A D O</label>
                <tr>
                    <th class="t-title" style="width:150px">#PZ</th>
                    <th class="t-title">Diametro 1</th>
                    <th class="t-title">Profundidad 1</th>
                    <th class="t-title">Diametro 2</th>
                    <th class="t-title">Profundidad 2</th>
                    <th class="t-title">Diametro de sufridera</th>
                    <th class="t-title">Diametro de ranura</th>
                    <th class="t-title">Profundidad de ranura</th>
                    <th class="t-title">Profundidad de sufridera</th>
                    <th class="t-title">Altura total</th>
                    <th class="t-title" style="width:200px">Error</th><br>
                    <th class="t-title" style="width:700px">Observaciones</th>
                </tr>
                <tr>
                    <td>C.Nominal.</td>
                    <td><input type="number" class="input" value="{{$cNominal->diametro1_cilindrado}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->profundidad1_cilindrado}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->diametro2_cilindrado}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->profundidad2_cilindrado}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->diametro_sufridera}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->diametro_ranura}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->profundidad_ranura}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->profundidad_sufridera}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->altura_total}}" disabled></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td> Tolerancias. </td>
                    <td><input type="number" class="input" value="{{$tolerancia->diametro1_cilindrado}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->profundidad1_cilindrado}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->diametro2_cilindrado}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->profundidad2_cilindrado}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->diametro_sufridera}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->diametro_ranura}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->profundidad_ranura}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->profundidad_sufridera}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->altura_total}}" disabled></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><input type="text" class="input" value="{{$piezasInfo->n_juego}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->diametro1_cilindrado}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->profundidad1_cilindrado}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->diametro2_cilindrado}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->profundidad2_cilindrado}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->diametro_sufridera}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->diametro_ranura}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->profundidad_ranura}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->profundidad_sufridera}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->altura_total}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="text" class="input" value="{{$piezasInfo->error_cilindrado}}" disabled></td>
                    <td><input type="text" class="input" value="{{$piezasInfo->observaciones_cilindrado}}" disabled></td>
                </tr>
            </table>
            <table border="1" class="tabla3">
                <label class="title-subproceso"> C A V I D A D E S</label>
                <tr>
                    <th class="t-title" style="width:150px">#PZ</th>
                    <th class="t-title">Diametro 1</th>
                    <th class="t-title">Profundidad 1</th>
                    <th class="t-title">Diametro 2</th>
                    <th class="t-title">Profundidad 2</th>
                    <th class="t-title">Diametro 3</th>
                    <th class="t-title">Profundidad 3</th>
                    <th class="t-title">Diametro 4 </th>
                    <th class="t-title">Profundidad 4</th>
                    <th class="t-title">Volumen</th>
                    <th class="t-title" style="width:200px">Error</th><br>
                    <th class="t-title" style="width:700px">Observaciones</th>
                </tr>
                <tr>
                    <td>C.Nominal.</td>
                    <td><input type="number" class="input" value="{{$cNominal->diametro1_cavidades}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->profundidad1_cavidades}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->diametro2_cavidades}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->profundidad2_cavidades}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->diametro3}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->profundidad3}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->diametro4}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->profundidad4}}" disabled></td>
                    <td><input type="number" class="input" value="{{$cNominal->volumen}}" disabled></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td> Tolerancias. </td>
                    <td><input type="number" class="input" value="{{$tolerancia->diametro1_cavidades}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->profundidad1_cavidades}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->diametro2_cavidades}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->profundidad2_cavidades}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->diametro3}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->profundidad3}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->diametro4}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->profundidad4}}" disabled></td>
                    <td><input type="number" class="input" value="{{$tolerancia->volumen}}" disabled></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><input type="text" class="input" value="{{$piezasInfo->n_juego}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->diametro1_cavidades}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->profundidad1_cavidades}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->diametro2_cavidades}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->profundidad2_cavidades}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->diametro3}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->profundidad3}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->diametro4}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->profundidad4}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="number" class="input" value="{{$piezasInfo->volumen}}" step="any" inputmode="decimal" disabled></td>
                    <td><input type="text" class="input" value="{{$piezasInfo->error_cavidades}}" disabled></td>
                    <td><input type="text" class="input" value="{{$piezasInfo->observaciones_cavidades}}" disabled></td>
                </tr>
            </table>
            @elseif($process == 'Off Set')
            <table border="1" class="tabla3">
                <>
                    <tr>
                        <th class="t-title" style="width:150px; border:none;">#PZ</th>
                        <th class="t-title" colspan="1" style="width:200px; border-bottom:none;">Ancho de altura</th>
                        <th class="t-title" colspan="2">Profundidad de tacon</th>
                        <th class="t-title" colspan="2">Simetría</th>
                        <th class="t-title" colspan="1" style="width:200px; border-bottom:none;">Ancho del tacon</th>
                        <th class="t-title" colspan="2">Barreno Lateral</th>
                        <th class="t-title" colspan="1" style="width:200px; border-bottom:none;">Altura tacon inicial</th>
                        <th class="t-title" colspan="1" style="width:200px; border-bottom:none;">Altura tacon intermedia</th>
                        <th class="t-title" style="width:200px; border-bottom:none;">Error</th>
                        <th class="t-title" style="width:700px; border-bottom:none;">Observaciones</th>
                    </tr>
                    <tr>
                        <th class="t-title" style="border:none;"></th>
                        <th style="border-bottom:none; border-top:none;"></th>
                        <th>Hembra</th>
                        <th>Macho</th>
                        <th>Hembra</th>
                        <th>Macho</th>
                        <th style="border-bottom:none; border-top:none;"></th>
                        <th>Hembra</th>
                        <th>Macho</th>
                        <th style="border-bottom:none; border-top:none;"></th>
                        <th style="border-bottom:none; border-top:none;"></th>
                        <th style="border-bottom:none; border-top:none;"></th>
                        <th style="border-bottom:none; border-top:none;"></th>
                    </tr>
                    <tr>
                        <td>C.Nominal</td>
                        <td><input type="number" name="cNomi_anchoRanura" value="{{$cNominal->anchoRanura}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="cNomi_profuTaconHembra" value="{{$cNominal->profuTaconHembra}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="cNomi_profuTaconMacho" value="{{$cNominal->profuTaconMacho}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="cNomi_simetriaHembra" value="{{$cNominal->simetriaHembra}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="cNomi_simetriaMacho" value="{{$cNominal->simetriaMacho}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="cNomi_anchoTacon" value="{{$cNominal->anchoTacon}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="cNomi_barrenoLateralHembra" value="{{$cNominal->barrenoLateralHembra}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="cNomi_barrenoLateralMacho" value="{{$cNominal->barrenoLateralMacho}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="cNomi_alturaTaconInicial" value="{{$cNominal->alturaTaconInicial}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="cNomi_alturaTaconIntermedia" value="{{$cNominal->alturaTaconIntermedia}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                    </tr>
                    <tr>
                        <td> Tolerancias </td>
                        <td><input type="number" name="tole_anchoRanura" value="{{$tolerancia->anchoRanura}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="tole_profuTaconHembra" value="{{$tolerancia->profuTaconHembra}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="tole_profuTaconMacho" value="{{$tolerancia->profuTaconMacho}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="tole_simetriaHembra" value="{{$tolerancia->simetriaHembra}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="tole_simetriaMacho" value="{{$tolerancia->simetriaMacho}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="tole_anchoTacon" value="{{$tolerancia->anchoTacon}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="tole_barrenoLateralHembra" value="{{$tolerancia->barrenoLateralHembra}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="tole_barrenoLateralMacho" value="{{$tolerancia->barrenoLateralMacho}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="tole_alturaTaconInicial" value="{{$tolerancia->alturaTaconInicial}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" name="tole_alturaTaconIntermedia" value="{{$tolerancia->alturaTaconIntermedia}}" class="input" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                        <td><input type="number" class="input" disabled></td>
                    </tr>
                    <tr>
                        <td><input type="text" class="input" value="{{$piezasInfo->n_juego}}" disabled></td>
                        <td><input type="number" class="input" value="{{$piezasInfo->anchoRanura}}" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" class="input" value="{{$piezasInfo->profuTaconHembra}}" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" class="input" value="{{$piezasInfo->profuTaconMacho}}" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" class="input" value="{{$piezasInfo->simetriaHembra}}" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" class="input" value="{{$piezasInfo->simetriaMacho}}" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" class="input" value="{{$piezasInfo->anchoTacon}}" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" class="input" value="{{$piezasInfo->barrenoLateralHembra}}" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" class="input" value="{{$piezasInfo->barrenoLateralMacho}}" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" class="input" value="{{$piezasInfo->alturaTaconInicial}}" step="any" inputmode="decimal" disabled></td>
                        <td><input type="number" class="input" value="{{$piezasInfo->alturaTaconIntermedia}}" step="any" inputmode="decimal" disabled></td>
                        <td><input type="text" class="input" value="{{$piezasInfo->error}}" disabled></td>
                        <td><input type="text" class="input" value="{{$piezasInfo->observaciones}}" disabled></td>
                    </tr>
            </table>

            @else
            <script>
                console.log();
                document.getElementById('scrollabe-table').appendChild(process.crearProceso()); //Agregar tabla al div.
            </script>
            @endif
        </div>
    </div>
</body>
@endsection