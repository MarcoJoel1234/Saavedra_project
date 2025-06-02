import '../layouts/partials/messages.js';

//Dar funcionalidad al bonton del menu
let btn_open = document.querySelector('.open-menu'); //Obtenemos el elemento por su id.
let nav = document.querySelector('.filter-opacity'); //Obtenemos el elemento por su id.

//Agregamos un evento al botón de abrir 
btn_open.addEventListener('click', function () { //Agregamos un evento al botón de abrir
    if(nav.style.visibility == 'hidden' || nav.style.visibility == ''){
        nav.style.visibility = 'visible'; //Cambiamos la visibilidad del nav.
        nav.style.opacity = '1'; //Cambiamos la opacidad del nav.
    }else{
        nav.style.visibility = 'hidden'; //Cambiamos la visibilidad del nav.
        nav.style.opacity = '0'; //Cambiamos la opacidad del nav.
    }
    nav.style.transition = '0.5s ease'; //Agregamos una transición al nav.
    nav.style.translationX = '0%'; //Agregamos una transición al nav.
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
    const currentPath = window.location.pathname;

    routes.forEach(route => {
        let li = document.createElement('li');
        let a = document.createElement('a');
        a.classList.add('nav-link');
        a.href = window.routes[route[0]];
        a.innerHTML = route[1];

        // Agregar la clase 'active' si la ruta coincide con la ruta actual
        const linkPath = new URL(a.href, window.location.origin).pathname;
        if (currentPath === linkPath) {
            a.classList.add('active');
        }
        
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
                // ['users', 'Ver usuarios'],
                ['createUser', 'Registrar usuario'],
                ['recoverPassword', 'Recuperar contraseña'],
                ['cNominals', 'Editar C.Nominales y Tolerancias'],
                ['piecesInProgress', 'Piezas en progreso'],
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
                ['createUser', 'Registrar usuario'],
                // ['users', 'Ver usuarios']
            ];
            break;
        case '4':
            routes = [
                ['vistaOTLiberar', 'Liberacion de piezas'],
                ['cNominals', 'Editar C.Nominales y Tolerancias'],
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