function crearSelect(ot, arregloOT, div) {
    if (ot.value != "null") {
        for (let i = 0; i < arregloOT.length; i++) {
            if (arregloOT[i][0] == ot.value) {
                //Eliminar select de clases si existe
                let label = document.getElementsByClassName("label-select")[1];
                let select_clases = document.getElementById("clases");
                let button = document.getElementsByClassName("btn-search")[0];
                if (select_clases != null) {
                    label.remove();
                    select_clases.remove();
                    button.remove();
                }

                //Creacion de la etiqueta para el select de clases
                label = document.createElement("label");
                label.for = "clases";
                label.className = "label-select";
                label.innerHTML = "Selecciona la clase:";

                //Creacion del select de clases
                select_clases = document.createElement("select");
                select_clases.name = "clase";
                select_clases.id = "clases";

                //Creacion de las opciones para el select de clases
                arregloOT[i][1].forEach((clase) => {
                    let option = document.createElement("option");
                    console.log(clase[0] + " " + clase[1]);
                    option.value = clase[0];
                    option.text = clase[1];
                    select_clases.appendChild(option);
                });

                //Creacion del boton
                button = document.createElement("button");
                button.className = "btn-search";
                button.type = "submit";
                button.innerHTML = "Buscar máquinas";

                //Agregar elementos al div
                div.appendChild(label);
                div.appendChild(select_clases);
                div.appendChild(button);
            }
        }
    } else {
        //Eliminar select de clases
        let label = document.getElementsByClassName("label-select")[1];
        let select_clases = document.getElementById("clases");
        let button = document.getElementsByClassName("btn-search")[0];
        if (select_clases != null) {
            label.remove();
            select_clases.remove();
            button.remove();
        }
    }
}
