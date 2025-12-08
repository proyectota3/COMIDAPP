document.addEventListener("DOMContentLoaded", () => {

    const inputBuscar = document.getElementById('buscar');
    const stores = Array.from(document.querySelectorAll('.store'));

    // Si no hay buscador o no hay carritos, salimos
    if (!inputBuscar || stores.length === 0) {
        console.warn("No se encontró el input #buscar o no hay elementos .store en esta página.");
        return;
    }

    // Guardamos el display original de cada .store para poder restaurarlo
    const displayOriginal = new Map();
    stores.forEach(store => {
        displayOriginal.set(store, getComputedStyle(store).display || 'block');
    });

    inputBuscar.addEventListener('input', function() {
        const texto = this.value.trim().toLowerCase();

        // Si no hay texto, mostramos todos los carritos
        if (texto === '') {
            stores.forEach(store => {
                store.style.display = displayOriginal.get(store);
            });
            return;
        }

        // Filtramos según el nombre del carrito (.store-name)
        stores.forEach(store => {
            const nombreEl = store.querySelector('.store-name');
            const nombre = (nombreEl ? nombreEl.textContent : '').toLowerCase();

            if (nombre.includes(texto)) {
                store.style.display = displayOriginal.get(store); // mostrar
            } else {
                store.style.display = 'none'; // ocultar
            }
        });
    });
});
