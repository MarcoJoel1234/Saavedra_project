import { Process } from "./Process.js";

let selectNames = ["workOrder"];
let array = window.workOrders;

console.log(window.workOrders);
insertSelect(window.workOrders, "workOrder", 1);

function insertSelect(array, name, id) {
    let wrapper = document.querySelector(".wrapper");
    if (array) {
        let options = Object.keys(array);
        createSelect(options, name, id);
    } else {
        let div_alert = document.createElement("div");
        div_alert.className = "alert alert-warning";
        div_alert.innerHTML = "No hay ordenes de trabajo registradas";
        wrapper.appendChild(div_alert);
    }
}

function createSelect(options, name, id) {
    //Crear un div para el select y el label
    let div = document.createElement("div");
    div.className = `form-group ${name} animated-div`;

    //Crear un select
    let select = document.createElement("select");
    select.name = name;
    select.className = `form-control select-${name}`;
    select.id = id; // Agregar id al select

    //Insertar opcion vacia
    let option = document.createElement("option");
    option.value = "";
    option.innerHTML = " Selecciona una opcion ";
    select.appendChild(option);

    //Agregar opciones al select
    for (let i = 0; i < options.length; i++) {
        option = document.createElement("option");
        option.value = options[i];
        option.innerHTML = options[i];
        select.appendChild(option);
    }

    //Agregar evento al select
    select.addEventListener("change", function () {
        // Se cambia el color del select
        select.style.backgroundColor = "#03396610";
        select.style.color = "#000000";

        deleteSelects(selectNames.slice(parseInt(select.id)));
        if (select.value != "") {
            hideTable();
            addSelect(select);
        } else {
            // Se cambia el color del select
            select.style.backgroundColor = "#033966";
            select.style.color = "#ffffff";
            hideTable();
        }
    });

    //Crear un label para el select
    let label = document.createElement("label");
    label.className = `title`;
    label.innerHTML = getLabelText(name);

    div.appendChild(select);
    div.appendChild(label);

    let row = name === "workOrder"? document.querySelector(".row-principal"): document.querySelector(".row");row.appendChild(div);
}

function addSelect(select) {
    //Obtener los valores de las opciones del siguiente select
    let newArray = array;
    selectNames.forEach((name) => {
        let select = document.querySelector(`.select-${name}`);
        if (select.value == "") {
            return;
        } else {
            newArray = newArray[select.value];
        }
    });

    //Obtener el siguiente select
    switch (select.id) {
        case "1":
            selectNames.push("class");
            break;
        case "2":
            selectNames.push("process");
            break;
        case "3":
            if (select.value == "Copiado") {
                selectNames.push("subProcess");
            } else if (select.value == "1 y 2 Operacion Equipo") {
                selectNames.push("operation");
            } else {
                insertTable(newArray, select.value);
                return;
            }
            break;
        case "4":
            let selectProcess = document.querySelector(`.select-process`);
            insertTable(newArray, selectProcess.value, select.value);
            return;
        default:
            insertTable(newArray, select.value);
            return;
    }
    insertSelect( newArray, selectNames[selectNames.length - 1], parseInt(select.id) + 1 );
}

function deleteSelects(elements) {
    elements.forEach((element) => {
        selectNames = selectNames.filter((name) => name !== element);
        let elementHTML = document.querySelector(`.${element}`);
        if (elementHTML) {
            elementHTML.classList.add("remove-div");
            setTimeout(() => {
                elementHTML.remove();
            }, 500);
        }
    });
}

function getLabelText(name) {
    const text = {
        workOrder: "Orden de trabajo",
        class: "Clase",
        process: "Proceso",
        subProcess: "Subproceso",
        operation: "Operación",
    };
    return text[name];
}

function insertTable(array, processSelected, subProcessSelected = null) {
    //Obtener el div donde se insertara la tabla
    let scrollable_table = document.querySelector(".scrollable-table");
    scrollable_table.classList.add("visible");

    //Posicionar el foormulario de busqueda
    let wrapper = document.querySelector(".wrapper");
    wrapper.style.position = "relative";
    wrapper.style.margin = "3.5em auto";
    wrapper.style.left = "0";
    wrapper.style.setProperty("transform", "none", "important");

    //Crear la tabla
    if (array.length === 0) {
        // let parent = scrollable_table.parentNode;
        // let div_alert = document.createElement("div");
        // div_alert.className = "alert alert-warning";
        // div_alert.innerHTML = "No hay procesos registrados para esta orden de trabajo";
        // parent.appendChild(div_alert);
    }
    let process = new Process(processSelected, subProcessSelected, array[0], array[1]);
    scrollable_table.appendChild(process.createProcess());
}
function hideTable() {
    //Posicionar el formulario de busqueda
    let wrapper = document.querySelector(".wrapper");
    wrapper.style.position = "absolute";
    wrapper.style.margin = "0 auto";
    wrapper.style.top = "50%";
    wrapper.style.left = "50%";
    wrapper.style.setProperty(
        "transform",
        "translate(-50%, -50%)",
        "important"
    );

    //Ocultar la tabla
    let scrollable_table = document.querySelector(".scrollable-table");
    scrollable_table.innerHTML = "";
    scrollable_table.classList.remove("visible");
}
