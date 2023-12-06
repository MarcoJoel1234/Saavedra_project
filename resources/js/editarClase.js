const btn_add = document.getElementById('btn-add');
const div = document.getElementById('datos');
const label = document.getElementById('piezas');

function addInput(){
    label.style = "display:none;";
    let newDiv = document.createElement('div');
    let newLabel = document.createElement('label');
    newLabel.innerHTML = "Piezas: ";
    let input = document.createElement('input');
    input.type = "number";
    input.name = "nPiezas";
    input.required = "true";
    newDiv.appendChild(newLabel);
    newDiv.appendChild(input);
    div.appendChild(newDiv);
    div.appendChild('btn-add');
}