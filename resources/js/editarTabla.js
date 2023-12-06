//Aparecer input para ingresar una contraseña
const btnEdit_table = document.getElementById("edit-table");
const divEdit_table = document.getElementById("editar-table");

btnEdit_table.addEventListener("click", function () {
    event.preventDefault();
    const input = document.createElement("input");
    const submit = document.createElement("input");
    input.type = "password";
    input.name = "password";
    input.id = "password";
    input.maxLength =12;
    input.minLength = 8;
    input.placeholder = "Solicita la contraseña a un Admin.";
    
    submit.type = "submit";
    submit.value = "Aceptar";
    submit.id = "submit-password";

    divEdit_table.appendChild(input);
    divEdit_table.appendChild(submit);
    btnEdit_table.style.display = "none";
});