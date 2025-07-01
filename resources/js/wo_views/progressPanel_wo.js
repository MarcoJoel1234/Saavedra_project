function updateProgressBar(percent) {
    const bar = document.getElementById('progress-bar');
    const avanceText = document.getElementById('avance-num');
    const estado = document.getElementById('estado');

    bar.style.width = percent + '%';
    bar.textContent = percent + '%';
    avanceText.textContent = percent + '%';

    if (percent >= 100) {
        estado.textContent = 'Completado';
        bar.style.borderRadius = '25px'; // redondear todo si está completo
    }
}

// Simula que el proceso avanza
function simulateProgress() {
    let progress = 0;
    const interval = setInterval(() => {
        if (progress > 100) {
            clearInterval(interval);
        } else {
            updateProgressBar(progress);
            progress += 10;
        }
    }, 500);
}
