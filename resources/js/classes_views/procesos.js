class Proceso {
    constructor(nameProceso, valoresCnomi, valoresTole) { // Constructor
        this.nameProceso = nameProceso; // Nombre del proceso
        this.valoresCnomi = valoresCnomi; // Valores de c.nominal 
        this.valoresTole = valoresTole; // Valores de tolerancias
    }

    crearProceso(subproceso) { // Crear proceso
        let titulos = []; // Titulos de la tabla
        let cNominal = []; // C.nominal
        let cNomiPosiciones = []; // Posiciones de los inputs de c.nominal
        let tolerancias = []; // Tolerancias
        let tolePosiciones = []; // Posiciones de los inputs de tolerancias 
        switch (this.nameProceso) { // Segun el proceso
            case "Cepillado": //Proceso de cepillado
                titulos = ['', 'Radio final de mordaza', 'Radio final mayor', 'Radio final de sufridera', 'Profundidad final conexión Fondo/Corona', 'Profundidad final mitad de Molde/Bombillo', 'Profundidad final Pico/Conexión de obturador', 'Ensamble', 'Distancia de barreno de alineación',  'Profundidad de barreno de alineación Hembra', 'Profundidad de barreno de alineación Macho', 'Altura de vena Hembra', 'Altura de vena Macho', 'Ancho de vena', 'PIN'];

                cNominal = ['C.nominal', 'cNomi_radiof_mordaza', 'cNomi_radiof_mayor', 'cNomi_radiof_sufridera', 'cNomi_profuFinal_CFC', 'cNomi_profuFinal_mitadMB', 'cNomi_profuFinal_PCO', 'cNomi_ensamble', 'cNomi_distancia_barrenoAli', 'cNomi_profu_barrenoAliHembra', 'cNomi_profu_barrenoAliMacho', 'cNomi_altura_venaHembra', 'cNomi_altura_venaMacho', 'cNomi_ancho_vena', 'Hpin1[]', 'Hpin2[]'];
                cNomiPosiciones = [14]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_radiof_mordaza1', 'tole_radiof_mordaza2', 'tole_radiof_mayor1', 'tole_radiof_mayor2', 'tole_radiof_sufridera1', 'tole_radiof_sufridera2', 'tole_profuFinal_CFC1', 'tole_profuFinal_CFC2', 'tole_profuFinal_mitadMB1', 'tole_profuFinal_mitadMB2', 'tole_profuFinal_PCO1', 'tole_profuFinal_PCO2', 'tole_ensamble1', 'tole_ensamble2', 'tole_distancia_barrenoAli1', 'tole_distancia_barrenoAli2', 'tole_profu_barrenoAliHembra1', 'tole_profu_barrenoAliHembra2', 'tole_profu_barrenoAliMacho1', 'tole_profu_barrenoAliMacho2', 'tole_altura_venaHembra1', 'tole_altura_venaHembra2', 'tole_altura_venaMacho1', 'tole_altura_venaMacho2', 'tole_ancho_vena1', 'tole_ancho_vena2', 'Hpin1[]', 'Hpin2[]']; 
                tolePosiciones = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27]; // Posiciones de los inputs de tolerancias
                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['radiof_mordaza'] , this.valoresCnomi['radiof_mayor'], this.valoresCnomi['radiof_sufridera'], this.valoresCnomi['profuFinal_CFC'], this.valoresCnomi['profuFinal_mitadMB'], this.valoresCnomi['profuFinal_PCO'], this.valoresCnomi['ensamble'], this.valoresCnomi['distancia_barrenoAli'], this.valoresCnomi['profu_barrenoAliHembra'], this.valoresCnomi['profu_barrenoAliMacho'], this.valoresCnomi['altura_venaHembra'], this.valoresCnomi['altura_venaMacho'], this.valoresCnomi['ancho_vena'], this.valoresCnomi['pin1'], this.valoresCnomi['pin2']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['radiof_mordaza1'], this.valoresTole['radiof_mordaza2'], this.valoresTole['radiof_mayor1'], this.valoresTole['radiof_mayor2'], this.valoresTole['radiof_sufridera1'], this.valoresTole['radiof_sufridera2'], this.valoresTole['profuFinal_CFC1'], this.valoresTole['profuFinal_CFC2'], this.valoresTole['profuFinal_mitadMB1'], this.valoresTole['profuFinal_mitadMB2'], this.valoresTole['profuFinal_PCO1'], this.valoresTole['profuFinal_PCO2'], this.valoresTole['ensamble1'], this.valoresTole['ensamble2'], this.valoresTole['distancia_barrenoAli1'], this.valoresTole['distancia_barrenoAli2'], this.valoresTole['profu_barrenoAliHembra1'], this.valoresTole['profu_barrenoAliHembra2'], this.valoresTole['profu_barrenoAliMacho1'], this.valoresTole['profu_barrenoAliMacho2'], this.valoresTole['altura_venaHembra1'], this.valoresTole['altura_venaHembra2'], this.valoresTole['altura_venaMacho1'], this.valoresTole['altura_venaMacho2'], this.valoresTole['ancho_vena1'], this.valoresTole['ancho_vena2'], this.valoresTole['pin1'], this.valoresTole['pin2']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'cepillado'); // Crear tabla
                
            case "Desbaste Exterior": //Proceso de desbaste Exterior
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
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'desbaste exterior'); // Crear tabla
            
            case "Revision Laterales": //Proceso de revision laterales
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
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'revision laterales'); // Crear tabla

            case "Primera Operacion": //Proceso de primera operacion
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
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'primera operacion'); // Crear tabla

            case "Barreno Maniobra": //Proceso de barreno maniobra
                titulos = ['', 'Profundidad de Barreno', 'Diametro de machuelo'];

                cNominal = ['C.nominal', 'cNomi_profundidadBarreno', 'cNomi_diametro_machuelo'];
                cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_profundidadBarreno1', 'tole_profundidadBarreno2', 'tole_diametro_machuelo1', 'tole_diametro_machuelo2']; 
                tolePosiciones = [1, 3]; // Posiciones de los inputs de tolerancias
                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['profundidad_barreno'] , this.valoresCnomi['diametro_machuelo']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['profundidad_barreno1'], this.valoresTole['profundidad_barreno2'], this.valoresTole['diametro_machuelo1'], this.valoresTole['diametro_machuelo2']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }   
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'barreno maniobra'); // Crear tabla

            case "Segunda Operacion": //Proceso de segunda operacion
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
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'segunda operacion'); // Crear tabla
                
            case "Operacion Equipo": //Proceso de operacion equipo
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
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'operacion equipo'); // Crear tabla

            case "Calificado": //Proceso de calificado
                titulos = ['', 'Diametro de ceja', 'Diametro de sufridera', 'Altura de sufridera', 'Diametro de conexion', 'Altura de conexion', 'Diametro de caja', 'Altura de caja', 'Altura total', 'Simetria'];

                cNominal = ['C.nominal', 'cNomi_diametro_ceja', 'cNomi_diametro_sufridera', 'cNomi_altura_sufridera', 'cNomi_diametro_conexion', 'cNomi_altura_conexion', 'cNomi_diametro_caja', 'cNomi_altura_caja', 'cNomi_altura_total', 'cNomi_simetria'];
                cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_diametro_ceja1', 'tole_diametro_ceja2', 'tole_diametro_sufridera1', 'tole_diametro_sufridera2', 'tole_altura_sufridera1', 'tole_altura_sufridera2', 'tole_diametro_conexion1', 'tole_diametro_conexion2', 'tole_altura_conexion1', 'tole_altura_conexion2', 'tole_diametro_caja1', 'tole_diametro_caja2', 'tole_altura_caja1', 'tole_altura_caja2', 'tole_altura_total1', 'tole_altura_total2', 'tole_simetria1', 'tole_simetria2']; 
                tolePosiciones = [1, 3, 5, 7, 9, 11, 13, 15, 17]; // Posiciones de los inputs de tolerancias

                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['diametro_ceja'] , this.valoresCnomi['diametro_sufridera'], this.valoresCnomi['altura_sufridera'], this.valoresCnomi['diametro_conexion'], this.valoresCnomi['altura_conexion'], this.valoresCnomi['diametro_caja'], this.valoresCnomi['altura_caja'], this.valoresCnomi['altura_total'], this.valoresCnomi['simetria']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['diametro_ceja1'], this.valoresTole['diametro_ceja2'], this.valoresTole['diametro_sufridera1'], this.valoresTole['diametro_sufridera2'], this.valoresTole['altura_sufridera1'], this.valoresTole['altura_sufridera2'], this.valoresTole['diametro_conexion1'], this.valoresTole['diametro_conexion2'], this.valoresTole['altura_conexion1'], this.valoresTole['altura_conexion2'], this.valoresTole['diametro_caja1'], this.valoresTole['diametro_caja2'], this.valoresTole['altura_caja1'], this.valoresTole['altura_caja2'], this.valoresTole['altura_total1'], this.valoresTole['altura_total2'], this.valoresTole['simetria1'], this.valoresTole['simetria2']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }   
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'calificado'); // Crear tabla

            case "Acabado Bombillo": //Proceso de acabado Bombillo
                titulos = ['', 'Diametro de mordaza', 'Diametro de ceja', 'Diametro de sufridera', 'Altura de mordaza', 'Altura de ceja', 'Altura de sufridera', 'Diametro Boca', 'Diametro Asiento Corona', 'Diametro llanta', 'Diametro caja corona', 'Profundidad corona', 'Angulo de 30', 'Profundidad caja corona', 'Simetria'];

                cNominal = ['C.nominal', 'cNomi_diametro_mordaza', 'cNomi_diametro_ceja', 'cNomi_diametro_sufridera', 'cNomi_altura_mordaza', 'cNomi_altura_ceja', 'cNomi_altura_sufridera', 'cNomi_diametro_boca', 'cNomi_diametro_asiento_corona', 'cNomi_diametro_llanta', 'cNomi_diametro_caja_corona', 'cNomi_profundidad_corona', 'cNomi_angulo_30', 'cNomi_profundidad_caja_corona', 'cNomi_simetria'];
                cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_diametro_mordaza1', 'tole_diametro_mordaza2', 'tole_diametro_ceja1', 'tole_diametro_ceja2', 'tole_diametro_sufridera1', 'tole_diametro_sufridera2', 'tole_altura_mordaza1', 'tole_altura_mordaza2', 'tole_altura_ceja1', 'tole_altura_ceja2', 'tole_altura_sufridera1', 'tole_altura_sufridera2', 'tole_diametro_boca1', 'tole_diametro_boca2', 'tole_diametro_asiento_corona1', 'tole_diametro_asiento_corona2', 'tole_diametro_llanta1', 'tole_diametro_llanta2', 'tole_diametro_caja_corona1', 'tole_diametro_caja_corona2', 'tole_profundidad_corona1', 'tole_profundidad_corona2', 'tole_angulo_301', 'tole_angulo_302', 'tole_profundidad_caja_corona1', 'tole_profundidad_caja_corona2', 'tole_simetria1', 'tole_simetria2']; 
                tolePosiciones = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27]; // Posiciones de los inputs de tolerancias

                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['diametro_mordaza'], this.valoresCnomi['diametro_ceja'] , this.valoresCnomi['diametro_sufridera'], this.valoresCnomi['altura_mordaza'], this.valoresCnomi['altura_ceja'], this.valoresCnomi['altura_sufridera'], this.valoresCnomi['diametro_boca'], this.valoresCnomi['diametro_asiento_corona'], this.valoresCnomi['diametro_llanta'], this.valoresCnomi['diametro_caja_corona'], this.valoresCnomi['profundidad_corona'], this.valoresCnomi['angulo_30'], this.valoresCnomi['profundidad_caja_corona'], this.valoresCnomi['simetria']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['diametro_mordaza1'], this.valoresTole['diametro_mordaza2'], this.valoresTole['diametro_ceja1'], this.valoresTole['diametro_ceja2'], this.valoresTole['diametro_sufridera1'], this.valoresTole['diametro_sufridera2'], this.valoresTole['altura_mordaza1'], this.valoresTole['altura_mordaza2'], this.valoresTole['altura_ceja1'], this.valoresTole['altura_ceja2'], this.valoresTole['altura_sufridera1'], this.valoresTole['altura_sufridera2'], this.valoresTole['diametro_boca1'], this.valoresTole['diametro_boca2'], this.valoresTole['diametro_asiento_corona1'], this.valoresTole['diametro_asiento_corona2'], this.valoresTole['diametro_llanta1'], this.valoresTole['diametro_llanta2'], this.valoresTole['diametro_caja_corona1'], this.valoresTole['diametro_caja_corona2'], this.valoresTole['profundidad_corona1'], this.valoresTole['profundidad_corona2'], this.valoresTole['angulo_301'], this.valoresTole['angulo_302'], this.valoresTole['profundidad_caja_corona1'], this.valoresTole['profundidad_caja_corona2'], this.valoresTole['simetria1'], this.valoresTole['simetria2']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }   
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'acabado bombillo'); // Crear tabla

            case "Acabado Molde": //Proceso de acabado molde
                titulos = ['', 'Diametro de mordaza', 'Diametro de ceja', 'Diametro de sufridera', 'Altura de mordaza', 'Altura de ceja', 'Altura de sufridera', 'Diametro Conexion Fondo', 'Diametro llanta', 'Diametro Caja Fondo', 'Altura Conexion Fondo', 'Profundidad Llanta', 'Profundidad Caja Fondo', 'Simetria'];

                cNominal = ['C.nominal', 'cNomi_diametro_mordaza', 'cNomi_diametro_ceja', 'cNomi_diametro_sufridera', 'cNomi_altura_mordaza', 'cNomi_altura_ceja', 'cNomi_altura_sufridera', 'cNomi_diametro_conexion_fondo', 'cNomi_diametro_llanta', 'cNomi_diametro_caja_fondo', 'cNomi_altura_conexion_fondo', 'cNomi_profundidad_llanta', 'cNomi_profundidad_caja_fondo', 'cNomi_simetria'];
                cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_diametro_mordaza1', 'tole_diametro_mordaza2', 'tole_diametro_ceja1', 'tole_diametro_ceja2', 'tole_diametro_sufridera1', 'tole_diametro_sufridera2', 'tole_altura_mordaza1', 'tole_altura_mordaza2', 'tole_altura_ceja1', 'tole_altura_ceja2', 'tole_altura_sufridera1', 'tole_altura_sufridera2', 'tole_diametro_conexion_fondo1', 'tole_diametro_conexion_fondo2', 'tole_diametro_llanta1', 'tole_diametro_llanta2', 'tole_diametro_caja_fondo1', 'tole_diametro_caja_fondo2', 'tole_altura_conexion_fondo1', 'tole_altura_conexion_fondo2', 'tole_profundidad_llanta1', 'tole_profundidad_llanta2', 'tole_profundidad_caja_fondo1', 'tole_profundidad_caja_fondo2', 'tole_simetria1', 'tole_simetria2']; 
                tolePosiciones = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25]; // Posiciones de los inputs de tolerancias

                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['diametro_mordaza'], this.valoresCnomi['diametro_ceja'] , this.valoresCnomi['diametro_sufridera'], this.valoresCnomi['altura_mordaza'], this.valoresCnomi['altura_ceja'], this.valoresCnomi['altura_sufridera'], this.valoresCnomi['diametro_conexion_fondo'], this.valoresCnomi['diametro_llanta'], this.valoresCnomi['diametro_caja_fondo'], this.valoresCnomi['altura_conexion_fondo'], this.valoresCnomi['profundidad_llanta'], this.valoresCnomi['profundidad_caja_fondo'], this.valoresCnomi['simetria']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['diametro_mordaza1'], this.valoresTole['diametro_mordaza2'], this.valoresTole['diametro_ceja1'], this.valoresTole['diametro_ceja2'], this.valoresTole['diametro_sufridera1'], this.valoresTole['diametro_sufridera2'], this.valoresTole['altura_mordaza1'], this.valoresTole['altura_mordaza2'], this.valoresTole['altura_ceja1'], this.valoresTole['altura_ceja2'], this.valoresTole['altura_sufridera1'], this.valoresTole['altura_sufridera2'], this.valoresTole['diametro_conexion_fondo1'], this.valoresTole['diametro_conexion_fondo2'], this.valoresTole['diametro_llanta1'], this.valoresTole['diametro_llanta2'], this.valoresTole['diametro_caja_fondo1'], this.valoresTole['diametro_caja_fondo2'], this.valoresTole['altura_conexion_fondo1'], this.valoresTole['altura_conexion_fondo2'], this.valoresTole['profundidad_llanta1'], this.valoresTole['profundidad_llanta2'], this.valoresTole['profundidad_caja_fondo1'], this.valoresTole['profundidad_caja_fondo2'], this.valoresTole['simetria1'], this.valoresTole['simetria2']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }   
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'acabado molde'); // Crear tabla
            case "Barreno Profundidad"://Proceso de barrero profundidad
                titulos = ['', 'Broca 1', 'Tiempo 1', 'Broca 2', 'Tiempo2', 'Broca3', 'Tiempo3', 'Entrada / Salida', 'Diametro de arrastre 1', 'Diametro de arrastre 2', 'Diametro de arrastre 3'];

                cNominal = ['C.nominal', 'cNomi_broca1', 'cNomi_tiempo1', 'cNomi_broca2', 'cNomi_tiempo2', 'cNomi_broca3', 'cNomi_tiempo3', 'cNomi_entradaSalida', 'cNomi_diametro_arrastre1', 'cNomi_diametro_arrastre2', 'cNomi_diametro_arrastre3'];
                cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_broca1', 'tole_tiempo1', 'tole_broca2', 'tole_tiempo2', 'tole_broca3', 'tole_tiempo3', 'tole_entrada', 'tole_salida', 'tole_diametro_arrastre1', 'tole_diametro_arrastre2', 'tole_diametro_arrastre3'];
                tolePosiciones = [7]; // Posiciones de los inputs de tolerancias

                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['broca1'], this.valoresCnomi['tiempo1'], this.valoresCnomi['broca2'], this.valoresCnomi['tiempo2'], this.valoresCnomi['broca3'], this.valoresCnomi['tiempo3'], this.valoresCnomi['entradaSalida'], this.valoresCnomi['diametro_arrastre1'], this.valoresCnomi['diametro_arrastre2'], this.valoresCnomi['diametro_arrastre3']]; 
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['broca1'], this.valoresTole['tiempo1'], this.valoresTole['broca2'], this.valoresTole['tiempo2'], this.valoresTole['broca3'], this.valoresTole['tiempo3'], this.valoresTole['entrada'], this.valoresTole['salida'], this.valoresTole['diametro_arrastre1'], this.valoresTole['diametro_arrastre2'], this.valoresTole['diametro_arrastre3']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }   
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'palomas'); // Crear tabla
            case "Copiado": //Proceso de copiado
                if(subproceso == 'Cilindrado'){
                    titulos = ['', 'Diametro 1', 'Profundidad 1', 'Diametro 2', 'Profundidad 2', 'Diametro de sufridera', 'Diametro Ranura', 'Profundidad Ranura', 'Profundidad de sufridera', 'ALTURA TOTAL'];

                    cNominal = ['C.nominal', 'cNomi_diametro1_cilindrado', 'cNomi_profundidad1_cilindrado', 'cNomi_diametro2_cilindrado', 'cNomi_profundidad2_cilindrado', 'cNomi_diametro_sufridera', 'cNomi_diametro_ranura', 'cNomi_profundidad_ranura', 'cNomi_profundidad_sufridera', 'cNomi_altura_total'];
                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                    tolerancias = ['Tolerancias', 'tole_diametro1_cilindrado', 'tole_profundidad1_cilindrado', 'tole_diametro2_cilindrado', 'tole_profundidad2_cilindrado', 'tole_diametro_sufridera', 'tole_diametro_ranura', 'tole_profundidad_ranura', 'tole_profundidad_sufridera', 'tole_altura_total']; 
                    tolePosiciones = [null]; // Posiciones de los inputs de tolerancias
                        
                    if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                        let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['diametro1_cilindrado'], this.valoresCnomi['profundidad1_cilindrado'] , this.valoresCnomi['diametro2_cilindrado'], this.valoresCnomi['profundidad2_cilindrado'], this.valoresCnomi['diametro_sufridera'], this.valoresCnomi['diametro_ranura'], this.valoresCnomi['profundidad_ranura'], this.valoresCnomi['profundidad_sufridera'], this.valoresCnomi['altura_total']];

                        let valoresTole = [this.valoresTole['id'], this.valoresTole['diametro1_cilindrado'], this.valoresTole['profundidad1_cilindrado'], this.valoresTole['diametro2_cilindrado'], this.valoresTole['profundidad2_cilindrado'], this.valoresTole['diametro_sufridera'], this.valoresTole['diametro_ranura'], this.valoresTole['profundidad_ranura'], this.valoresTole['profundidad_sufridera'], this.valoresTole['altura_total']];
                        return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                        }   
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'copiado'); //Crear tabla
                }else{
                    titulos = ['', 'Diametro 1', 'Profundidad 1', 'Diametro 2', 'Profundidad 2', 'Diametro 3', 'Profundidad 3', 'Diametro 4', 'Profundidad 4', ' VOLUMEN '];

                    cNominal = ['C.nominal', 'cNomi_diametro1_cavidades', 'cNomi_profundidad1_cavidades', 'cNomi_diametro2_cavidades', 'cNomi_profundidad2_cavidades', 'cNomi_diametro3', 'cNomi_profundidad3', 'cNomi_diametro4', 'cNomi_profundidad4', 'cNomi_volumen'];
                    cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                    tolerancias = ['Tolerancias', 'tole_diametro1_cavidades', 'tole_profundidad1_cavidades', 'tole_diametro2_cavidades', 'tole_profundidad2_cavidades', 'tole_diametro3', 'tole_profundidad3', 'tole_diametro4', 'tole_profundidad4', 'tole_volumen'];
                        tolePosiciones = [null]; // Posiciones de los inputs de tolerancias

                    if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                        let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['diametro1_cavidades'], this.valoresCnomi['profundidad1_cavidades'] , this.valoresCnomi['diametro2_cavidades'], this.valoresCnomi['profundidad2_cavidades'], this.valoresCnomi['diametro3'], this.valoresCnomi['profundidad3'], this.valoresCnomi['diametro4'], this.valoresCnomi['profundidad4'], this.valoresCnomi['volumen']];

                        let valoresTole = [this.valoresTole['id'], this.valoresTole['diametro1_cavidades'], this.valoresTole['profundidad1_cavidades'], this.valoresTole['diametro2_cavidades'], this.valoresTole['profundidad2_cavidades'], this.valoresTole['diametro3'], this.valoresTole['profundidad3'], this.valoresTole['diametro4'], this.valoresTole['profundidad4'], this.valoresTole['volumen']];

                        return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole, 'copiado'); // Crear tabla
                    }
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'copiado'); // Crear tabla
                }
            case "Palomas"://Proceso de palomas
                titulos = ['', 'Ancho de Paloma', 'Grueso de Paloma', 'Profundidad de Paloma', 'Rebaje de llanta'];

                cNominal = ['C.nominal', 'cNomi_ancho_paloma', 'cNomi_grueso_paloma', 'cNomi_profundidad_paloma', 'cNomi_rebaje_llanta'];
                cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_ancho_paloma', 'tole_grueso_paloma', 'tole_profundidad_paloma', 'tole_rebaje_llanta'];
                tolePosiciones = [null]; // Posiciones de los inputs de tolerancias

                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['anchoPaloma'], this.valoresCnomi['gruesoPaloma'] , this.valoresCnomi['profundidadPaloma'], this.valoresCnomi['rebajeLlanta']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['anchoPaloma'], this.valoresTole['gruesoPaloma'], this.valoresTole['profundidadPaloma'], this.valoresTole['rebajeLlanta']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }   
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'palomas'); // Crear tabla
            case "Rebajes": //Proceso de Rebajes
                titulos = ['', 'Rebaje 1', 'Rebaje 2', 'Rebaje 3', 'Profundidad de Bordonio', 'Vena 1', 'Vena 2', 'Simetria'];

                cNominal = ['C.nominal', 'cNomi_rebaje1', 'cNomi_rebaje2', 'cNomi_rebaje3', 'cNomi_profundidad_bordonio', 'cNomi_vena1', 'cNomi_vena2', 'cNomi_simetria'];
                cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_rebaje1', 'tole_rebaje2', 'tole_rebaje3', 'tole_profundidad_bordonio', 'tole_vena1', 'tole_vena2', 'tole_simetria'];
                tolePosiciones = [null]; // Posiciones de los inputs de tolerancias

                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['rebaje1'], this.valoresCnomi['rebaje2'] , this.valoresCnomi['rebaje3'], this.valoresCnomi['profundidad_bordonio'], this.valoresCnomi['vena1'], this.valoresCnomi['vena2'], this.valoresCnomi['simetria']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['rebaje1'], this.valoresTole['rebaje2'], this.valoresTole['rebaje3'], this.valoresTole['profundidad_bordonio'], this.valoresTole['vena1'], this.valoresTole['vena2'], this.valoresTole['simetria']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }   
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'rebajes'); // Crear tabla
            case "1 y 2 Operacion Equipo": //Proceso de Rebajes
                titulos = ['', 'Altura', 'ø Altura de candado', 'Altura asiento obturador', 'ø Profundidad de soldadura', 'ø De push up',];
                cNominal = ['C.nominal', 'cNomi_altura', 'cNomi_alturaCandado1', 'cNomi_alturaCandado2', 'cNomi_alturaAsientoObturador1', 'cNomi_alturaAsientoObturador2', 'cNomi_profundidadSoldadura1', 'cNomi_profundidadSoldadura2', 'cNomi_pushUp'];
                cNomiPosiciones = [2, 4, 6]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_altura', 'tole_alturaCandado1', 'tole_alturaCandado2', 'tole_alturaAsientoObturador1', 'tole_alturaAsientoObturador2', 'tole_profundidadSoldadura1', 'tole_profundidadSoldadura2', 'tole_pushUp'];
                tolePosiciones = [2, 4, 6]; // Posiciones de los inputs de tolerancias

                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['altura'], this.valoresCnomi['alturaCandado1'] , this.valoresCnomi['alturaCandado2'], this.valoresCnomi['alturaAsientoObturador1'], this.valoresCnomi['alturaAsientoObturador2'], this.valoresCnomi['profundidadSoldadura1'], this.valoresCnomi['profundidadSoldadura2'], this.valoresCnomi['pushUp']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['altura'], this.valoresTole['alturaCandado1'], this.valoresTole['alturaCandado2'], this.valoresTole['alturaAsientoObturador1'], this.valoresTole['alturaAsientoObturador2'], this.valoresTole['profundidadSoldadura1'], this.valoresTole['profundidadSoldadura2'], this.valoresTole['pushUp']];  
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }   
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'rebajes'); // Crear tabla
            case "Embudo CM": //Proceso de Rebajes
                titulos = ['', 'Conexion de linea de partida', 'Conexión 90G', 'Altura de conexión', 'Diametro de embudo'];

                cNominal = ['C.nominal', 'cNomi_conexion_lineaPartida', 'cNomi_conexion_90G', 'cNomi_altura_conexion', 'cNomi_diametro_embudo'];
                cNomiPosiciones = [null]; // Posiciones de los inputs de c.nominal

                tolerancias = ['Tolerancias', 'tole_conexion_lineaPartida', 'tole_conexion_90G', 'tole_altura_conexion', 'tole_diametro_embudo'];
                tolePosiciones = [null]; // Posiciones de los inputs de tolerancias

                if(this.valoresCnomi != undefined && this.valoresTole != undefined){
                    let valoresCnomi = [this.valoresCnomi['id'], this.valoresCnomi['conexion_lineaPartida'], this.valoresCnomi['conexion_90G'] , this.valoresCnomi['altura_conexion'], this.valoresCnomi['diametro_embudo']];
                
                    let valoresTole = [this.valoresTole['id'], this.valoresTole['conexion_lineaPartida'], this.valoresTole['conexion_90G'], this.valoresTole['altura_conexion'], this.valoresTole['diametro_embudo']];
                    return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCnomi, valoresTole); // Crear tabla
                }   
                return this.crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, undefined, undefined, 'embudoCM'); // Crear tabla
            default:
                return 'No se encontro el proceso'; //Retorna el mensaje de que el proceso no existe
        }
    }

    crearTabla(titulos, cNominal, cNomiPosiciones, tolerancias, tolePosiciones, valoresCNomi, valoresTole, proceso) { // Crear tabla
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
                    for (let j = 0; j < cNominal.length; j++) { // Crear columnas
                        const td = document.createElement('td'); // Crear columna
                        if (j != 0) { //Si no es la primera columna.
                            if (cNomiPosiciones.includes(j)) { //Si la posición esta en el array de posiciones.
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
                                    if (proceso == 'Cepillado') { 
                                        if(j >= 15 && j <= 22){//Si es H o M
                                            let input = this.crearInputs('input-medio', tolerancias[j]); // Crear inputs
                                            input.type = 'text'; // Agregar tipo al input
                                            input.value = tolerancias[j]; // Agregar valor al input
                                            input.readOnly = true; // Agregar readonly al input
                                            td.appendChild(input); // Agregar input a la columna
                                        }
                                    } else {
                                        if(valoresTole != undefined){ //Valores de tolerancias
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
    addSelect(window.workOrders, "workOrder"); // Agregar select

    function addSelect(array, name){
        if(name == "workOrder"){
            let workOrders = Object.keys(array);
            let options = [];
            workOrders.forEach((workOrder) => {
                options.push(workOrder);
            });
            let form = document.querySelector("form-search"); // Form donde se agregara el select
            form.appendChild(createSelect(options, name)); // Crear select
        }
        // if(name == "class"){
        //     array.foreach((classItem, index) => {
        //         console.log(classItem);
        //     });
        //     let select = createSelect(array, name); // Crear select
        //     select.id = 'select-clase'; // Agregar id al select
        //     select.name = 'clase'; // Agregar nombre al select
        //     select.style = "width: 380px;"; // Agregar estilo al select
        //     let div = document.getElementById("row"); // Div donde se agregara el select
        //     div.appendChild(select); // Agregar select al div
        // }else{

        // }
    }
    function createSelect(options, name){
        let select = document.createElement('select');
        select.name = name;
        //Agregar opciones al select
        for(let i=0; i<options.length; i++){
            let option = document.createElement('option');
            option.value = options[i];
            option.innerHTML = options[i];
            select.appendChild(option);
        }
        return select;
    }
    function agregarSelect(selectOp){
        let div = document.getElementById("row"); //Div donde se agregara el select
        //Limpiar
        if(document.getElementById("select-subproceso") != null){
            let labelExistente = document.getElementById("row-title");
            labelExistente.removeChild(document.getElementsByTagName('label')[2]);
            div.removeChild(document.getElementsByTagName('select')[2]);
        }
        if(selectOp.value == "1 y 2 Operacion Equipo"){ //Si el proceso es pysOpeSoldadura
            divTitle = document.getElementById("row-title");
            let label = document.createElement('label'); // Crear label
            label.className = "title"; // Agregar clase al label
            label.innerHTML = 'Selecciona la operacion'; // Agregar texto al label
            divTitle.appendChild(label); // Agregar label al div

            let select = document.createElement('select'); // Crear select
            select.id = 'select-operacion'; // Agregar id al select
            select.name = 'operacion'; // Agregar nombre al select
            for(let i=1; i<=2; i++){ //Crear option de operaciones 
                let option = document.createElement('option'); // Crear option
                option.value = i; // Agregar valor al option 
                option.innerHTML = i + ' operacion'; // Agregar texto al option
                select.appendChild(option); // Agregar option al select
            }
            div.appendChild(select); // Agregar select al div
        }else if(selectOp.value == "Copiado"){
            divTitle = document.getElementById("row-title");
            let label = document.createElement('label'); // Crear label
            label.className = "title"; // Agregar clase al label
            label.innerHTML = 'Selecciona el subproceso'; // Agregar texto al label
            divTitle.appendChild(label); // Agregar label al div

            let select = document.createElement('select'); // Crear select
            select.id = 'select-subproceso'; // Agregar id al select
            select.name = 'subproceso'; // Agregar nombre al select

            let option = document.createElement('option'); // Crear option
            option.value = 'Cilindrado'; // Agregar valor al option 
            option.innerHTML = 'Cilindrado'; // Agregar texto al option
            select.appendChild(option); // Agregar option al select

            let option1 = document.createElement('option'); // Crear option
            option1.value = 'Cavidades'; // Agregar valor al option 
            option1.innerHTML = 'Cavidades'; // Agregar texto al option
            select.appendChild(option1); // Agregar option al select
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
                selectCreate.style = "width: 380px;"; //Agrega el estilo al select
                
                for(let j=0; j<procesos[i].length; j++){ //Crea la opción de los procesos
                    let option = document.createElement("option"); //Crea el option 
                    if(j == 0){
                        option.text = ' '; //Agrega el texto al option
                        option.value = ' '; //Agrega el valor al option 
                        selectCreate.appendChild(option); //Agrega el option al select     
                    }
                    option = document.createElement("option"); //Crea el option
                    option.text = procesos[i][j]; //Agrega el texto al option
                    option.value = procesos[i][j]; //Agrega el valor al option 
                    selectCreate.appendChild(option); //Agrega el option al select 
                }
                selectCreate.addEventListener("change", function(){
                    agregarSelect(selectCreate);
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