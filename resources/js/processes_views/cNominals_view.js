console.log(window.workOrders);
insertSelect(window.workOrders, document.querySelector(".search-form"), "workOrders"); //

function insertSelect(array, fatherElement, name) {
    if (array) {
        chooseSelectType(array, fatherElement, name);
    } else {
        let div_alert = document.createElement('div');
        div_alert.className = "w-100 alert alert-danger text-center";
        let i = document.createElement('i');
        i.className = "fa-solid fa-spinner fa-spin";
        i.textContent = "Aun no hay clases registradas";
        div_alert.appendChild(i);
        form.appendChild(div_alert);
    }
}

function chooseSelectType(array, name, form) {
    let array;
    switch (name) {
        case "workOrders":
            array = Object.keys(array);
            
            break;
        case "classes":
            break;
        case "processes":
            break;
    }
    
    let options = [];
    workOrders.forEach((workOrder) => {
        options.push(workOrder);
    });

    form.appendChild(createSelect(options, name)); // Crear select
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

function createSelect(options, name) {
    let select = document.createElement('select');
    select.name = name;
    //Agregar opciones al select
    for (let i = 0; i < options.length; i++) {
        let option = document.createElement('option');
        option.value = options[i];
        option.innerHTML = options[i];
        select.appendChild(option);
    }
    return select;
}

function agregarSelect(selectOp) {
    let div = document.getElementById("row"); //Div donde se agregara el select
    //Limpiar
    if (document.getElementById("select-subproceso") != null) {
        let labelExistente = document.getElementById("row-title");
        labelExistente.removeChild(document.getElementsByTagName('label')[2]);
        div.removeChild(document.getElementsByTagName('select')[2]);
    }
    if (selectOp.value == "1 y 2 Operacion Equipo") { //Si el proceso es pysOpeSoldadura
        divTitle = document.getElementById("row-title");
        let label = document.createElement('label'); // Crear label
        label.className = "title"; // Agregar clase al label
        label.innerHTML = 'Selecciona la operacion'; // Agregar texto al label
        divTitle.appendChild(label); // Agregar label al div

        let select = document.createElement('select'); // Crear select
        select.id = 'select-operacion'; // Agregar id al select
        select.name = 'operacion'; // Agregar nombre al select
        for (let i = 1; i <= 2; i++) { //Crear option de operaciones 
            let option = document.createElement('option'); // Crear option
            option.value = i; // Agregar valor al option 
            option.innerHTML = i + ' operacion'; // Agregar texto al option
            select.appendChild(option); // Agregar option al select
        }
        div.appendChild(select); // Agregar select al div
    } else if (selectOp.value == "Copiado") {
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
    } else {
        if (document.getElementById("select-operacion") != null) {
            div.removeChild(document.getElementsByTagName('select')[2]);
        }
    }
}

function selectProcesos(procesos, clases) {
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
    if (selectClase.length > clases.length) {
        selectClase.removeChild(selectClase.options[0]);
    }
    let div = document.getElementById("row"); //Div donde se agregara el select 
    for (let i = 0; i < clases.length; i++) { //Recorre las clases
        if (selectClase.value == clases[i][0]["id"]) { //Si el valor del select es igual a la clase
            eliminarElemento(selectExistente);
            let selectCreate = document.createElement("select"); //Crear un select
            selectCreate.id = "select-proceso"; //Agrega el id al select
            selectCreate.name = "proceso"; //Agrega el nombre de los procesos al select
            selectCreate.style = "width: 380px;"; //Agrega el estilo al select

            for (let j = 0; j < procesos[i].length; j++) { //Crea la opción de los procesos
                let option = document.createElement("option"); //Crea el option 
                if (j == 0) {
                    option.text = ' '; //Agrega el texto al option
                    option.value = ' '; //Agrega el valor al option 
                    selectCreate.appendChild(option); //Agrega el option al select     
                }
                option = document.createElement("option"); //Crea el option
                option.text = procesos[i][j]; //Agrega el texto al option
                option.value = procesos[i][j]; //Agrega el valor al option 
                selectCreate.appendChild(option); //Agrega el option al select 
            }
            selectCreate.addEventListener("change", function () {
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

function eliminarElemento(elemento) {
    if (elemento != null) {
        elemento.remove();
    }
}