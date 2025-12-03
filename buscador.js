document.getElementById('buscar').addEventListener('input', function() {
    const query = this.value.trim();
    const resultados = document.getElementById('resultados');

    if (query.length > 2) { // Solo busca si hay más de 2 caracteres
        fetch(`comidApp.php?q=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                resultados.innerHTML = ''; // Limpia resultados anteriores

                if (data.length > 0) {
                    const fragment = document.createDocumentFragment();
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = `${item.Nombre} - ${item.Direccion}`;
                        li.classList.add('list-group-item');
                        fragment.appendChild(li);
                    });
                    resultados.appendChild(fragment);
                } else {
                    resultados.innerHTML = '<li class="list-group-item">Sin resultados</li>';
                }
            })
            .catch(error => {
                console.error('Error en la búsqueda:', error);
                resultados.innerHTML = '<li class="list-group-item text-danger">Error en la búsqueda</li>';
            });
    } else {
        resultados.innerHTML = ''; // Limpia si el input es corto
    }
});
