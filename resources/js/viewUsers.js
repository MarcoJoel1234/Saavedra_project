document.addEventListener("DOMContentLoaded", () => {
    const contextMenu = document.getElementById("context-menu");
    const tableRows = document.querySelectorAll("table tbody tr");

    // Función para mostrar el menú contextual
    const showContextMenu = (event, row) => {
        event.preventDefault();

        // Configurar posición del menú contextual
        const x = event.pageX;
        const y = event.pageY;
        contextMenu.style.top = `${y}px`;
        contextMenu.style.left = `${x}px`;
        contextMenu.style.display = "block";

        // Vincular acciones al menú contextual según la fila seleccionada
        const altaOption = contextMenu.querySelector(".menu-item[href*='alta_usuario']");
        const bajaOption = contextMenu.querySelector(".menu-item[href*='baja_usuario']");
        const eliminarOption = contextMenu.querySelector(".eliminar-option");

        altaOption.href = `/alta_usuario/${row.cells[1].innerText}`;
        bajaOption.href = `/baja_usuario/${row.cells[1].innerText}`;
        eliminarOption.onclick = () => {
            if (confirm(`¿Estás seguro de que deseas eliminar al usuario con matrícula ${row.cells[1].innerText}?`)) {
                // Lógica para eliminar el usuario
                alert(`Usuario con matrícula ${row.cells[1].innerText} eliminado.`);
            }
        };
    };

    // Ocultar el menú contextual
    const hideContextMenu = () => {
        contextMenu.style.display = "none";
    };

    // Agregar eventos a cada fila
    tableRows.forEach((row) => {
        row.addEventListener("contextmenu", (event) => showContextMenu(event, row));
    });

    // Ocultar menú al hacer clic fuera de él
    document.addEventListener("click", hideContextMenu);
    document.addEventListener("scroll", hideContextMenu); // Opcional para ocultar en desplazamientos
});
