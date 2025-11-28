<?php
// Iniciar sesión PHP al principio de CADA archivo que use el navbar
session_start(); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Lubricentro La Cusqueña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        /* Estilos SOLO del Hero (El Navbar ya trae sus propios estilos) */
        body { font-family: 'Poppins', sans-serif; background-color: #1a1b1e; color: #fff; height: 100vh; display: flex; flex-direction: column; }
        .hero-container { flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; width: 100%; margin-top: 60px; } /* Margin top para que el navbar no tape */
        .hero-title { color: #d4af37; font-weight: 700; font-size: 3rem; margin-bottom: 10px; text-transform: uppercase; text-align: center; }
        .hero-subtitle { color: #ccc; margin-bottom: 50px; font-size: 1.2rem; text-align: center; }
        .btn-custom { padding: 15px 30px; font-size: 1.2rem; font-weight: 600; border-radius: 50px; transition: all 0.3s ease; width: 100%; max-width: 300px; margin: 10px; display: inline-block; text-decoration: none; text-align: center; }
        .btn-mantenimiento { border: 2px solid #d4af37; color: #d4af37; }
        .btn-mantenimiento:hover { background-color: #d4af37; color: #000; box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4); transform: translateY(-5px); }
        .btn-lavado { border: 2px solid #0dcaf0; color: #0dcaf0; }
        .btn-lavado:hover { background-color: #0dcaf0; color: #000; box-shadow: 0 5px 15px rgba(13, 202, 240, 0.4); transform: translateY(-5px); }
    </style>
</head>
<body>

    <?php include 'navbarcusquena.php'; ?>

    <div class="hero-container">
        <div class="hero-title">Lubricentro La Cusqueña</div>
        <p class="hero-subtitle">Selecciona el servicio que deseas reservar hoy</p>

        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="gestionCitas.php?tipo=mantenimiento" class="btn-custom btn-mantenimiento">
                <i class="fas fa-tools me-2"></i> Reservar Mantenimiento
            </a>
            <a href="gestionCitas.php?tipo=lavado" class="btn-custom btn-lavado">
                <i class="fas fa-car-wash me-2"></i> Reservar Lavado
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>