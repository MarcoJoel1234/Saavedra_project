let select = document.getElementsByClassName('form-select')[0];

select.addEventListener('change', function () {
    inicializarVariables();
});
document.addEventListener('DOMContentLoaded', function () {
    inicializarVariables();
});

function inicializarVariables(){
    let form = document.getElementsByClassName("form")[0];
    insertarvalorSelect(select.value, form);
    let div_tabla = document.getElementsByClassName('tabla-procesos')[0];
    let tabla = document.getElementById("tabla");
    let boton = document.getElementById("btnTabla");
    let procesos = [];
    let procesosDB = [];
    let encabezados = ["Proceso", "Chico", "Mediano", "Grande"];
    if (select.value != 0) {
        if (select.value == 'Bombillo') {
            procesos = ['Cepillado', 'Desbaste-exterior', 'Laterales', '1ra-Operación', 'Barreno-Maniobra',
                '2da-Operación', 'Soldadura', 'Soldadura-PTA', 'Rectificado', 'Asentado',
                'Calificado', 'Acabado-Bombillo', 'Barreno-Profundidad', 'Cavidades', 'Copiado',
                'OffSet', 'Palomas', 'Rebajes'
            ];
            procesosDB = ["cepillado", "desbaste", "revLaterales", "primeraOpeSoldadura", "barrenoManiobra", "segundaOpeSoldadura", "soldadura", "soldaduraPTA", "rectificado", "asentado", "revCalificado", "acabadoBombillo", "barrenoProfundidad", "cavidades", "copiado", "offset", "palomas", "rebajes"];
        } else {
            procesos = ['Cepillado', 'Desbaste-exterior', 'Laterales', '1ra-Operación', 'Barreno-Maniobra',
                '2da-Operación', 'Soldadura', 'Soldadura-PTA', 'Rectificado', 'Asentado',
                'Calificado', 'Acabado-Molde', 'Barreno-Profundidad', 'Cavidades', 'Copiado',
                'OffSet', 'Palomas', 'Rebajes'
            ];
            procesosDB = ["cepillado", "desbaste", "revLaterales", "primeraOpeSoldadura", "barrenoManiobra", "segundaOpeSoldadura", "soldadura", "soldaduraPTA", "rectificado", "asentado", "revCalificado", "acabadoMolde", "barrenoProfundidad", "cavidades", "copiado", "offset", "palomas", "rebajes"];
        }
        crearTabla(procesos, procesosDB, encabezados, div_tabla, tabla, boton, tiempos, select.value, form);
    } else {
        eliminarTabla(tabla, boton, div_tabla);
    }
}

function crearTabla(procesos, procesosDB, encabezados, div, tabla, boton, tiempos, clase, form) {
    eliminarTabla(tabla, boton, div);
    let div_search = document.getElementsByClassName("search")[0];
    div_search.style.marginTop =  "0";
    div_search.style.height = "35%";
    div.style.display = "block";

    let btn = document.createElement('input');
    btn.type = "submit";
    btn.textContent = "Guardar cambios";
    btn.id = "btnTabla";
    let table = document.createElement('table');
    table.id = "tabla"
    //Crear encabezado
    let encabezadosT = document.createElement('tr');
    for (encabezado of encabezados) {
        let th = document.createElement('th');

        //Cambiar guiones por espacios
        th.textContent = encabezado;
        encabezadosT.appendChild(th);
    }
    table.appendChild(encabezadosT);

    //Crear filas
    for (proceso in procesos) {
        let contador = 1;
        let fila = document.createElement('tr');
        for (i = 0; i < encabezados.length; i++) {
            let columna = document.createElement('td');
            columna.style.padding = "3px";
            if (i === 0) {
                let title = procesos[proceso].replace(/-/g, " ");
                columna.textContent = title;
            } else {
                let input = document.createElement('input');
                input.type = "number";
                input.className = "celdas";
                
                //Mandar la seleccion del select a la ruta para identificar la clase
                input.name = `${procesosDB[proceso]}[]`;
                let valorInput = 0;
                if(tiempos != null){
                    valorInput = recorrerArrayTiempos(tiempos[clase], procesosDB[proceso], encabezados[contador]);
                }
                input.value = valorInput;
                columna.appendChild(input);
                contador++;
            }
            fila.appendChild(columna);
        }
        table.appendChild(fila);
    }
    div.appendChild(table);
    form.appendChild(btn);
}

function eliminarTabla(tabla, boton, div) {
    if (tabla != undefined) {
        tabla.remove();
        boton.remove();
        div.style.display = "none";
        let div_search = document.getElementsByClassName("search")[0];
        div_search.style.margin = "auto"
        div_search.style.height = "40%";
    }
}

function recorrerArrayTiempos(array, proceso, tamanio) {
    return array?.[proceso]?.[tamanio]?.["tiempo"] ?? 0;
}

function insertarvalorSelect(valor, form){
    let input = document.createElement('input');
    input.type = "hidden"
    input.name = "clase"
    input.value = valor;

    form.appendChild(input);
}
