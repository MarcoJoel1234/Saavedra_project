function createSelects(labelText, className) {
    let form_grid = document.querySelector(".form-grid");

    let form_group = document.createElement("div");
    form_group.className = "form-group";

    let select = document.createElement("select");
    select.className = `form-control ${className}`;
    if (className === "workOrder") {
        modifySelects(window.workOrders, select, labelText);
    } else {
        select.disabled = true;
        let optionEmpty = document.createElement("option");
        optionEmpty.value = "";
        optionEmpty.textContent = "Sin opciones disponibles";
        select.appendChild(optionEmpty);
    }

    let label = document.createElement("label");
    label.textContent = labelText;
    label.className = "form-label";
    label.setAttribute("for", className);

    select.addEventListener("change", function () {
        disabledSelects(className);
        if (select.value) {
            select.style.backgroundColor = "#03396610";
            select.style.color = "#000000";
        } else {
            select.style.backgroundColor = "#033966";
            select.style.color = "#ffffff";
        }
    });

    form_group.appendChild(select);
    form_group.appendChild(label);
    form_grid.appendChild(form_group);

    return select;
}
function modifySelects(array, select, labelText) {
    select.disabled = false; // Habilitar el select si ya existe
    select.innerHTML = ""; // Limpiar las opciones existentes

    // Crear opciones para cada orden de trabajo

    let arrayElements =
        labelText == "Proceso" || labelText == "Subproceso"
            ? Object.values(array)
            : Object.keys(array);

    arrayElements.forEach((element, key) => {
        // Crear un elemento de opción vacio
        if (key == 0) {
            let optionEmpty = document.createElement("option");
            optionEmpty.value = "";
            optionEmpty.textContent = "Selecciona una opción";
            select.appendChild(optionEmpty);
        }
        // Evitar crear opción si element es "moldura" y labelText es "Clase"
        if (labelText === "Clase" && element === "moldura") {
            return; // o `continue;` si estás en un for tradicional
        }

        // Crear un elemento de opción con el valor de la orden de trabajo
        let option = document.createElement("option");
        option.value = element;
        option.textContent =
            labelText == "Orden de trabajo"
                ? `${element} - ${array[element]["moldura"]}`
                : element;
        select.appendChild(option);
    });
}
function disabledSelects(className) {
    //Deshabilitar selects
    let array = ["subprocess", "process", "class"];
    for (let i = 0; i < array.length; i++) {
        if (array[i] === className) {
            break; // No deshabilitar el select actual
        }
        let select = document.querySelector(`.${array[i]}`);
        if (select) {
            if (array[i] !== "subprocess") {
                select.style.backgroundColor = "#033966";
                select.style.color = "#ffffff";
                select.disabled = true;
                select.innerHTML = ""; // Limpiar las opciones existentes
                let optionEmpty = document.createElement("option");
                optionEmpty.value = "";
                optionEmpty.textContent = "Sin opciones disponibles";
                select.appendChild(optionEmpty);
            } else {
                let parent = select.parentElement;
                if (parent) {
                    parent.innerHTML = ""; // Limpiar el contenido del div padre
                    parent.remove(); // Eliminar el div padre
                }
            }
        }
    }
}

//Ejecucion del script
let form_grid = document.querySelector(".form-grid");
if (window.workOrders != null) {
    //prettier-ignore
    let selectWO = createSelects("Orden de trabajo", "workOrder");
    let selectClasses = createSelects("Clase", "class");
    let selectProcesses = createSelects("Proceso", "process");

    selectWO.addEventListener("change", function () {
        let selectedValue = selectWO.value;
        if (selectedValue) {
            let classes = window.workOrders[selectedValue];
            modifySelects(classes, document.querySelector(".class"), "Clase");
        }
    });
    //prettier-ignore
    selectClasses.addEventListener("change", function () {
        let selectedClass = selectClasses.value;
        if (selectedClass) {
            let processes = window.workOrders[selectWO.value][selectedClass];
            modifySelects(processes, document.querySelector(".process"), "Proceso");
        }
    });
    //prettier-ignore
    selectProcesses.addEventListener("change", function () {
        let selectedProcess = selectProcesses.value;
        if (selectedProcess && selectedProcess === "Operacion Equipo") {
            let selectSubprocesses = createSelects("Subproceso", "subprocess");
            modifySelects(["1ra Operacion", "2da Operacion"], selectSubprocesses, "Subproceso");
        }
    });
} else {
    let p = document.createElement("p");
    p.textContent = "No hay órdenes de trabajo disponibles.";
    form_grid.appendChild(p);
}
