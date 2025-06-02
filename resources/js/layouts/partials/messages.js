document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.custom-alert');

    alerts.forEach(alert => {
        // Cierre automático después de 5 segundos (5000 ms)
        setTimeout(() => closeAlert(alert), 5000);

        // Cierre manual al hacer clic en el botón
        const closeBtn = alert.querySelector('.close-alert');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                closeAlert(alert)});
        }
    });

    function closeAlert(alert) {
        if (!alert) return;
        alert.style.opacity = '0';
        alert.addEventListener('transitionend', () => alert.remove());
    }
});