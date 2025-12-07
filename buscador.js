document.addEventListener("DOMContentLoaded", () => {

    const inputBuscar = document.getElementById('buscar');
    const resultados = document.getElementById('resultados');

    if (!inputBuscar || !resultados) {
        console.warn("No se encontró el input #buscar o la lista #resultados.");
        return;
    }

    // Detectar si estamos dentro de /pages/ o en la raíz
    const estaEnPages = window.location.pathname.includes('/pages/');
    const basePath = estaEnPages ? '../' : './';

    inputBuscar.addEventListener('input', function() {
        const query = this.value.trim();

        if (query.length > 2) {

            fetch(`${basePath}controlador/buscadorComida.php?q=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    resultados.innerHTML = '';

                    // Si vino un error desde PHP
                    if (data.error) {
                        console.error('Error desde PHP:', data.error);
                        resultados.innerHTML = '<li class="list-group-item text-danger">Error en la búsqueda</li>';
                        return;
                    }

                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(item => {
                            const li = document.createElement('li');
                            li.textContent = `${item.Nombre} - ${item.Direccion}`;
                            li.classList.add('list-group-item');
                            resultados.appendChild(li);
                        });
                    } else {
                        resultados.innerHTML = '<li class="list-group-item">Sin resultados</li>';
                    }
                })
                .catch(err => {
                    console.error("Error en la búsqueda:", err);
                    resultados.innerHTML = '<li class="list-group-item text-danger">Error en la búsqueda</li>';
                });

        } else {
            resultados.innerHTML = ''; // limpiar si escribe poco
        }
    });
});
