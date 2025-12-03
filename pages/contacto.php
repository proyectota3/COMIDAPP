
<!DOCTYPE html>
<html lang="es">
<head>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>COMIDAPP</title>
        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome para íconos -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <!-- Enlace a tu CSS -->
        <link href="../styles.css" rel="stylesheet">
    </head>
    
<body>
    <nav class="navbar navbar-expand-lg bg-danger">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="../indexApp.php">ComidAPP</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        
                    <li class="nav-item"><a class="nav-link text-white" href="misCompras.html">Mis compras</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="descargar.html">Descargar</a></li>
                </ul>
                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Buscar" aria-label="Search">
                    <button class="btn btn-outline-light" type="submit">Buscar</button>
                </form>
            </div>
        </div>
    </nav>

<main class="container mt-5 flex-grow-1">
        <h1 class="text-center mb-4">Contáctanos</h1>
        <div class="row">
            <div class="col-md-6">
            
            <form method="POST" action="../../controlador/solicitud.php">
    <!-- DATOS DE TABLA LOCAL COMIDAPP -->
    
    <!-- Campo para el correo electrónico -->
    <div class="form-group">
        <label for="RUT">RUT</label>
        <input 
            type="number" 
            class="form-control" 
            id="RUT" 
            name="RUT" 
            placeholder="RUT de empresa" 
            required>
    </div>
    
    <!-- Campo para el nombre (razón social) -->
    <div class="form-group">
        <label for="Nombre">Razón social</label>
        <input 
            type="text" 
            class="form-control" 
            id="Nombre" 
            name="Nombre" 
            placeholder="Nombre o razón social del negocio" 
            required>
    </div>
    
    <!-- Campo para la dirección -->
    <div class="form-group">
        <label for="Direccion">Domicilio fiscal</label>
        <input 
            type="text" 
            class="form-control" 
            id="Direccion" 
            name="Direccion" 
            placeholder="Domicilio fiscal del negocio" 
            required>
    </div>
    <!-- Campo para Telefono -->
    <div class="form-group">
        <label for="Telefono">Telefono</label>
        <input 
            type="text" 
            class="form-control" 
            id="Telefono" 
            name="Telefono" 
            placeholder="Telefono" 
            required>
    </div>
    <div class="form-group">
        <label for="Mail">Mail</label>
        <input 
            type="mail" 
            class="form-control" 
            id="Mail" 
            name="Mail" 
            placeholder="Tu mail de contacto" 
            required>
    </div>
    
    <!-- Botón para enviar el formulario -->
    <button type="submit" class="btn btn-danger btn-block" name="solicitud">Enviar</button>
</form>

            </div>
            <div class="col-md-6">
                <h4>Información de Contacto</h4>
                <p><strong>Dirección:</strong> UTU Instituto Tecnológico ITI, Mercedes 1131, Montevideo, Uruguay</p>
                <p><strong>Teléfono:</strong> +598 93 754 113</p>
                <p><strong>Email:</strong> soporte@comidapp.com</p>
                
                <h4>Ubicación</h4>
                <div class="map-responsive">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3276.694156432854!2d-56.19385482511051!3d-34.90360627322138!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x959f81e6bc53d73f%3A0x8fe63b2149bde905!2sMercedes%201131%2C%2011100%20Montevideo%2C%20Uruguay!5e0!3m2!1ses!2suy!4v1698768121206!5m2!1ses!2suy"
                        width="100%" height="250" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false"
                        tabindex="0">
                    </iframe>
                </div>
            </div>
            
    </main>
    <footer class="footer bg-danger text-center text-white py-3">
        <div class="container">
            <p class="mb-0">© 2024 ComidApp. Derechos Reservados, Uruguay.</p>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
