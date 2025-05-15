//Aparecer input para ingresar una contraseña
const btnEdit_header = document.getElementById("edit-header"); //Botón para editar el header
const divEdit_header = document.getElementById("editarHeader");

btnEdit_header.addEventListener("click", function () { //Aparecer el input para ingresar la contraseña de un admin
    event.preventDefault(); //Evitar que se recargue la página
    const input = document.createElement("input");
    const submit = document.createElement("input");
    input.type = "password"; //Tipo de input para ingresar la contraseña
    input.name = "password"; //Nombre del input
    input.id = "password"; //Id del input
    input.maxLength =12; //Máximo de caracteres
    input.minLength = 8; //Mínimo de caracteres
    input.placeholder = "Solicita la contraseña a un Admin."; //Placeholder del input
    
    submit.type = "submit"; //Tipo de input para enviar el formulario
    submit.value = "Aceptar"; //Valor del input
    submit.id = "submit-password"; //Id del input

    divEdit_header.appendChild(input); //Agregar el input al div
    divEdit_header.appendChild(submit); //Agregar el input al div
    btnEdit_header.style.display = "none"; //Ocultar el botón.
});