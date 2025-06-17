var operacion = false;

function crearTabla(piezas, infoPiezas) {
    //Crea la tabla de piezas trabajadas en la O.T
    console.log(piezas);
    const table = document.querySelector(".table");
    const tbody = document.createElement("tbody");
    //Convertir el objeto a un array
    piezas = convertirObjectToArray(piezas);
    for (let i = 0; i < piezas.length; i++) {
        const tr = document.createElement("tr");
        for (let j = 1; j < piezas[i].length - 3; j++) {
            let td = document.createElement("td");
            if (piezas[i][4] == "Operacion Equipo") {
                switch (j) {
                    case 7:
                        td.textContent = crearFecha(piezas[i][j]);
                        break;
                    case piezas[i].length - 4:
                        td.appendChild(
                            crearBotonVer(infoPiezas, i, piezas[i][2])
                        );
                        break;
                    default:
                        if (piezas[i][j] != undefined) {
                            td.textContent = piezas[i][j];
                        } else {
                            td.textContent = "";
                        }
                        break;
                }
                tr.appendChild(td);
                if (piezas[i][6].includes("Incompleto")) {
                    tr.style.backgroundColor = "#FFFF99";
                } else if (piezas[i][piezas[i].length - 2] == 1) {
                    tr.style.backgroundColor = "#AED6F1";
                } else if (
                    piezas[i][6] != "Ninguno" ||
                    piezas[i][piezas[i].length - 2] == 2
                ) {
                    tr.style.backgroundColor = "#EC7063";
                } else {
                    tr.style.backgroundColor = "#ACF980";
                }
            } else {
                if (operacion) {
                    switch (j) {
                        case 5:
                            tr.appendChild(td);
                            let td1 = document.createElement("td");
                            td1.textContent = piezas[i][j];
                            tr.appendChild(td1);
                            break;
                        case 6:
                            td.textContent = crearFecha(piezas[i][j]);
                            tr.appendChild(td);
                            break;
                        case piezas[i].length - 4:
                            td.appendChild(
                                crearBotonVer(infoPiezas, i, piezas[i][2])
                            );
                            tr.appendChild(td);
                            break;
                        default:
                            if (piezas[i][j] != undefined) {
                                td.textContent = piezas[i][j];
                            } else {
                                td.textContent = "";
                            }
                            tr.appendChild(td);
                            break;
                    }
                } else {
                    switch (j) {
                        case 6:
                            td.textContent = crearFecha(piezas[i][j]);
                            break;
                        case piezas[i].length - 4:
                            td.appendChild(
                                crearBotonVer(infoPiezas, i, piezas[i][2])
                            );
                            break;
                        default:
                            if (piezas[i][j] != undefined) {
                                td.textContent = piezas[i][j];
                            } else {
                                td.textContent = "";
                            }
                            break;
                    }
                    tr.appendChild(td);
                }
                if (piezas[i][5].includes("Incompleto")) {
                    tr.style.backgroundColor = "#FFFF99";
                } else if (piezas[i][piezas[i].length - 2] == 1) {
                    tr.style.backgroundColor = "#AED6F1";
                } else if (
                    piezas[i][5] != "Ninguno" ||
                    piezas[i][piezas[i].length - 2] == 2
                ) {
                    tr.style.backgroundColor = "#EC7063";
                } else {
                    tr.style.backgroundColor = "#ACF980";
                }
            }
        }
        tbody.appendChild(tr);
    }
    table.appendChild(tbody);
}

function convertirObjectToArray(obj) {
    let array = [];
    for (let i = 0; i < obj.length; i++) {
        array.push(Object.values(obj[i]));
    }
    return array;
}

function crearFecha(fecha) {
    let cadena = "";
    if (fecha != "No liberado") {
        let array = fecha.split("T");
        cadena = array[0] + "\n " + array[1].slice(0, 8);
    } else {
        cadena = fecha;
    }
    return cadena;
}

function crearBotonLiberar(infoPiezas, i, piezas) {
    const a = document.createElement("a");
    a.className = "btn-liberar";

    let boolean;
    if (infoPiezas[i][2] == "Ninguno" && piezas[i][piezas[i].length - 2] != 2) {
        boolean = true;
    } else {
        boolean = false;
    }
    let url = `${window.baseUrl}/piezasLiberar/${infoPiezas[i][0]}/${infoPiezas[i][1]}/${true}/${boolean}/${this.obtenerRequest()}`;
    a.href = url;

    const image = document.createElement("img");
    image.src = window.liberar;
    image.alt = "Liberar";
    image.className = "ver";
    a.appendChild(image);
    return a;
}
function crearBotonRechazar(infoPiezas, i) {
    const a = document.createElement("a");
    a.className = "btn-liberar";

    let url = `${window.baseUrl}/piezasLiberar/${infoPiezas[i][0]}/${infoPiezas[i][1]}/${false}/${false}/${this.obtenerRequest()}`;


    a.href = url;

    const image = document.createElement("img");
    image.src = window.rechazar;
    image.alt = "Rechazar";
    image.className = "ver";
    a.appendChild(image);
    return a;
}
function crearBotonVer(infoPiezas, i, usuarios) {
    const a = document.createElement("a");
    a.className = "btn-pza";

    let nPiezas = [];
    for (let j = 0; j < infoPiezas[i][0].length; j++) {
        nPiezas.push(infoPiezas[i][0][j]);
    }
    let url = `${window.baseUrl}/pieces/${nPiezas}/${infoPiezas[i][1]}/${document.getElementsByName("profile")[0].value}`;
    a.href = url;

    console.log(url);
    const image = document.createElement("img");
    image.src = window.ojito;
    image.alt = "Ver pieza";
    image.className = "ver";
    a.appendChild(image);
    return a;
}
function obtenerRequest() {
    let names = [
        "ot",
        "clase",
        "operador",
        "maquina",
        "proceso",
        "error",
        "fecha",
    ];
    let request = [];
    for (let i = 0; i < names.length; i++) {
        let value = document.getElementsByName(names[i])[0].value;
        request.push(value);
    }
    return request;
}
if (pieces.length > 0) {
    crearTabla(pieces, piecesData);
}
const pdf = document.getElementById("pdf");
