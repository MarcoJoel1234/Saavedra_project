document.addEventListener('DOMContentLoaded', () => { //Carga del DOM
    const contextMenu = document.getElementById('context-menu'); //Obtención del manú contextual (se usa su ID con el que se declaro)
    const table = document.querySelector('table'); // Asegúrate de que sea la tabla correcta
    let selectedRow = null; // Variable para rastrear la fila seleccionada

    const showContextMenu = (e) => { //Función para mostrar el menú contextual
        e.preventDefault();
        // Obtener el contenedor de la tabla
        const container = table.parentElement;
        const containerRect = container.getBoundingClientRect();
        // Coordenadas relativas al contenedor
        let posX = e.clientX - containerRect.left;
        let posY = e.clientY - containerRect.top;
        // Dimensiones del menú contextual
        const menuWidth = contextMenu.offsetWidth;
        const menuHeight = contextMenu.offsetHeight;
        // Ajustar la posición si se sale de los límites
        if (posX + menuWidth > container.offsetWidth) {
            posX = container.offsetWidth - menuWidth - 1; //Evita que el menú se salga del contenedor
        }
        if (posY + menuHeight > container.offsetHeight) {
            posY = container.offsetHeight - menuHeight - 1;
        }
        // Mostrar el menú contextual personalizado
        contextMenu.style.left = `${posX}px`;
        contextMenu.style.top = `${posY}px`;
        contextMenu.style.display = 'block';
    };
    //Oculta el menú contextual
    const hideContextMenu = () => {
        contextMenu.style.display = 'none'; 
    };
    const highlightRow = (row) => {
        // Quitar el sombreado de la fila previamente seleccionada
        if (selectedRow) {
            selectedRow.style.backgroundColor = ''; // Restaurar el color original
        }
        // Sombrear la fila actual
        selectedRow = row;
        selectedRow.style.backgroundColor = 'lightblue'; // Cambiar a azul claro
    };
    // Manejar el clic en la tabla (para seleccionar la fila)
    table.addEventListener('click', (e) => {
        const cell = e.target.closest('td'); // Detectar la celda más cercana
        if (cell) {
            const row = cell.closest('tr'); // Obtener la fila de la celda
            highlightRow(row); // Sombrear la fila seleccionada
        }
    });
    // Manejar el clic derecho en la tabla (para mostrar el menú contextual)
    table.addEventListener('contextmenu', (e) => {
        e.preventDefault(); // Bloquear el menú contextual del navegador
        const cell = e.target.closest('td'); // Detectar la celda más cercana

        if (cell) {
            const row = cell.closest('tr'); // Obtener la fila de la celda
            highlightRow(row); // Sombrear la fila seleccionada
            showContextMenu(e); // Mostrar el menú contextual
        }
    });
    // Ocultar menú personalizado al hacer clic fuera de él
    document.addEventListener('click', (e) => {
        if (!contextMenu.contains(e.target)) {
            hideContextMenu();
        }
        // Quitar el sombreado de la fila si se hace clic fuera de la tabla
        if (!table.contains(e.target) && selectedRow) {
            selectedRow.style.backgroundColor = ''; // Restaurar el color original
            selectedRow = null;
        }
    });
});
