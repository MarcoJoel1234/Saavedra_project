import './bootstrap'; //Importamos boostrap

//Dar funcionalidad al bonton del menu
let btn_open = document.getElementById('open'); //Obtenemos el elemento por su id.
let btn_close = document.getElementById('close'); //Obtenemos el elemento por su id
let nav = document.getElementById('nav'); //Obtenemos el elemento por su id.

//Agregamos un evento al botón de abrir 
btn_open.addEventListener('click', function () { //Agregamos un evento al botón de abrir
    nav.style.visibility = 'visible'; //Cambiamos la visibilidad del nav.
    nav.style.opacity = '1'; //Cambiamos la opacidad del nav.
    nav.style.transition = '0.5s ease'; //Agregamos una transición al nav.
    nav.style.translationX = '0%'; //Agregamos una transición al nav.
});

//Agregamos un evento al botón de cerrar
btn_close.addEventListener('click', function () { //Agregamos un evento al botón de cerrar
    nav.style.visibility = 'hidden'; //Cambiamos la visibilidad del nav.
    nav.style.opacity = '0'; //Cambiamos la opacidad del nav
    nav.style.transition = 'all 0.5s ease'; //Agregamos una transición al nav.
    nav.style.translationX = '-100%'; //Agregamos una transición al nav.
});


// Crear la lista de rutas
let profile = document.getElementById('profile').value;
createMenu(profile);

//Funcion para crear La lista de rutas para el menu
function createMenu(profile) {
    let routes = getRoutes(profile);
    let ul = document.querySelector('.nav-list');
    ul.appendChild(createList(routes));
}
function createList(routes){
    let fragment = document.createDocumentFragment();
    routes.forEach(route => {
        let li = document.createElement('li');
        let a = document.createElement('a');
        a.href = window.routes[route[0]];
        a.innerHTML = route[1];
        li.appendChild(a);
        fragment.appendChild(li);
    });
    return fragment;
}
function getRoutes(profile) {
    let routes = [];
    const routeHome = ['home', 'Inicio'];
    switch (profile) {
        case '1':
            routes = [
                ['createMolding', 'Crear nueva moldura'],
                ['manageWO', 'Crear o Modificar O.T'],
                ['users', 'Ver usuarios'],
                ['createUser', 'Registrar usuario'],
                ['recoverPassword', 'Recuperar contraseña'],
                ['cNominals', 'Editar C.Nominales y Tolerancias'],
                ['vistaPiezas', 'Piezas en progreso'],
                ['vistaPzasGenerales', 'Reporte de piezas'],
                ['vistaOTLiberar', 'Liberacion de piezas'],
                ['mostrarTiempos', 'Modificar tiempos de producción'],
                ['datosProduccion', 'Datos de produccion'],
            ];
            break;
        case '2':
            routes = [
                ["cepillado", "Cepillado"],
                ["desbasteExterior", "Desbaste exterior"],
                ["revisionLaterales", "Revisión de laterales"],
                ["primeraOpeSoldadura", "1ra Operación Soldadura"],
                ["barrenoManiobra", "Barreno de maniobra"],
                ["segundaOpeSoldadura", "2da Operación Soldadura"],
                ["soldadura", "Soldadura"],
                ["soldaduraPTA", "Soldadura PTA"],
                ["rectificado", "Reporte diario de rectificado"],
                ["asentado", "Asentado"],
                ["calificado", "Calificado"],
                ["acabadoBombillo", "Revisión acabados bombillo"],
                ["acabadoMolde", "Revisión acabados molde"],
                ["barrenoProfundidad", "Barreno de profundidad"],
                ["cavidades", "Reporte diario de Cavidades"],
                ["copiado", "Reporte de Copiado"],
                ["offSet", "Ranura Off-Set"],
                ["palomas", "Reporte de Palomas"], ,
                ["rebajes", "Rebajes"],
                ["operacionEquipo", "1ra y 2da Operación Equipo"],
                ["embudoCM", "Embudo C.M"],
            ];
            break;
        case '3':
            routes = [
                ['register', 'Registrar usuarios'],
                ['users', 'Ver usuarios']
            ];
            break;
        case '4':
            routes = [
                ['vistaOTLiberar', 'Liberacion de piezas'],
                ['procesos', 'Editar C.Nominales y Tolerancias'],
                ['mostrarTiempos', 'Modificar tiempos de producción']
            ];
            break;
        case '5':
            routes = [
                ['manageWO', 'Registrar o Modificar O.T'],
            ];
            break;
    }
    routes.unshift(routeHome);
    return routes;
}
