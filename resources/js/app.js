import './bootstrap'; //Importamos boostrap

let btn_abrir = document.getElementById('abrir'); //Obtenemos el elemento por su id.
let btn_cerrar = document.getElementById('cerrar'); //Obtenemos el elemento por su id
let nav = document.getElementById('nav'); //Obtenemos el elemento por su id.

//Agregamos un evento al botón de abrir 
btn_abrir.addEventListener('click', function(){ //Agregamos un evento al botón de abrir
    nav.style.visibility = 'visible'; //Cambiamos la visibilidad del nav.
    nav.style.opacity = '1'; //Cambiamos la opacidad del nav.
    nav.style.transition = '0.5s ease'; //Agregamos una transición al nav.
    nav.style.translationX = '0%'; //Agregamos una transición al nav.
});

//Agregamos un evento al botón de cerrar
btn_cerrar.addEventListener('click', function(){ //Agregamos un evento al botón de cerrar
    nav.style.visibility = 'hidden'; //Cambiamos la visibilidad del nav.
    nav.style.opacity = '0'; //Cambiamos la opacidad del nav
    nav.style.transition = 'all 0.5s ease'; //Agregamos una transición al nav.
    nav.style.translationX = '-100%'; //Agregamos una transición al nav.
});