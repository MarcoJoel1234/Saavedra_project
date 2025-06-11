let select = document.querySelector('.form-select');

select.addEventListener('change', function () {
    inicializarVariables();
});
document.addEventListener('DOMContentLoaded', function () {
    inicializarVariables();
});

function inicializarVariables(){
    let form = document.querySelector(".form");
    insertarvalorSelect(select.value, form);
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
        crearTabla(procesos, procesosDB, encabezados, tabla, boton, tiempos, select.value, form);
    } else {
        eliminarTabla(tabla, boton);
    }
}

function crearTabla(procesos, procesosDB, encabezados, tabla, boton, tiempos, clase, form) {
    eliminarTabla(tabla, boton);
    let div_table = document.createElement("div");
    div_table.className = "div-table";

    let btn = document.createElement('input');
    btn.type = "submit";
    btn.textContent = "Guardar cambios";
    btn.id = "btnTabla";
    let table = document.createElement('table');
    table.id = "tabla"
    table.classList.add("table");

    //Crear encabezado
    let encabezadosT = document.createElement('tr');
    for (let encabezado of encabezados) {
        let th = document.createElement('th');

        //Cambiar guiones por espacios
        th.textContent = encabezado;
        encabezadosT.appendChild(th);
    }
    table.appendChild(encabezadosT);

    //Crear filas
    for (let proceso in procesos) {
        let contador = 1;
        let fila = document.createElement('tr');
        for (let i = 0; i < encabezados.length; i++) {
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
    div_table.appendChild(table);
    div_table.appendChild(btn);
    form.appendChild(div_table);
    setTimeout(() => {
        div_table.classList.add("show");
    }, 100);
}

function eliminarTabla(tabla, boton) {
    let div = document.querySelector(".div-table");
    if (tabla != undefined) {
        tabla.remove();
        boton.remove();
        div.classList.remove("show");
        setTimeout(() => {
            div.remove();
        }, 100);
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
