<?php
// Iniciar sesión para el navbar
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios - Lubricentro La Cusqueña</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* Ajuste para que el contenido no quede oculto tras el navbar fijo */
        body { padding-top: 70px; font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }

        /* HERO */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                url('../css/imagenes/lubricentro.jpg') center/cover no-repeat;
            height: 40vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-shadow: 2px 2px 10px black;
            border-radius: 0 0 50px 50px;
            margin-bottom: 40px;
        }

        /* Cards */
        .card-service {
            border-radius: 15px;
            background-color: white;
            transition: 0.3s;
            height: 100%; /* Altura igualada */
            border: none;
        }

        .card-service:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        /* SECCIÓN OSCURA */
        .servicio-dark {
            background-color: #2b2b2b;
            color: white;
            border-radius: 10px;
            padding: 50px 30px;
            margin-bottom: 60px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .servicio-dark h2, .servicio-dark h5, .servicio-dark p, .servicio-dark li {
            color: white !important;
        }

        /* Cards modo oscuro */
        .servicio-dark .card-service {
            background-color: #3a3a3a;
            border: 1px solid #555;
            color: white;
        }

        .servicio-dark .card-service:hover {
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.1);
            border-color: #d4af37;
        }

        .btn-gold-black {
            background-color: transparent;
            color: #d4af37;
            border: 2px solid #d4af37;
            padding: 12px 35px;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 40px;
            transition: all 0.3s ease-in-out;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-gold-black:hover {
            background-color: #d4af37;
            color: #000;
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.6);
        }
        
        /* Lista sin estilo */
        ul { padding-left: 20px; }
        li { margin-bottom: 5px; }
    </style>
</head>

<body>

    <?php include 'navbarcusquena.php'; ?>

    <section class="hero" data-aos="fade-down">
        <h1 class="display-4 fw-bold">Nuestros Servicios</h1>
    </section>

    <div class="container py-4">

        <div class="servicio-dark" data-aos="fade-up">

            <h2 class="mb-4 fw-bold border-bottom border-warning pb-2 d-inline-block">
                <i class="fas fa-tools me-2 text-warning"></i> Mantenimiento
            </h2>

            <div class="row g-4 mt-2">

                <div class="col-md-4" data-aos="zoom-in">
                    <div class="card card-service p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-oil-can text-warning me-2"></i> Cambio de Aceite</h5>
                        <p class="text-muted small">Incluye reemplazo de aceite de motor, cambio de filtro de aceite y revisión general de niveles.</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="card card-service p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-cogs text-warning me-2"></i> Afinamiento Completo</h5>
                        <p class="text-muted small">Limpieza de inyectores, cambio de bujías, filtro de aire y combustible, escaneo electrónico.</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="card card-service p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-car-crash text-warning me-2"></i> Frenos</h5>
                        <p class="text-muted small">Limpieza y regulación de frenos, cambio de pastillas, rectificado de discos si es necesario.</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in">
                    <div class="card card-service p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-car-side text-warning me-2"></i> Alineamiento 3D</h5>
                        <p class="text-muted small">Corrección de ángulos de las ruedas para evitar desgaste irregular de llantas y mejorar estabilidad.</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="card card-service p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-battery-full text-warning me-2"></i> Sistema Eléctrico</h5>
                        <p class="text-muted small">Revisión de batería, alternador, arranque, luces y fusibles del vehículo.</p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="card card-service p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-search text-warning me-2"></i> Inspección Preventiva</h5>
                        <p class="text-muted small">Revisión completa de 25 puntos de seguridad antes de viajes largos.</p>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <a href="gestionCitas.php?tipo=mantenimiento" class="btn-gold-black">
                        Reservar Mantenimiento
                    </a>
                </div>

            </div>
        </div>

        <div class="servicio-dark" data-aos="fade-up">

            <h2 class="mb-4 fw-bold border-bottom border-info pb-2 d-inline-block">
                <i class="fas fa-shower me-2 text-info"></i> Lavado Profesional
            </h2>

            <div class="row g-4 mt-2">

                <div class="col-md-4" data-aos="fade-up">
                    <div class="card card-service p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-water text-info me-2"></i> Lavado Básico</h5>
                        <ul class="text-muted small">
                            <li>Enjuague a presión</li>
                            <li>Shampoo con cera</li>
                            <li>Limpieza de llantas</li>
                            <li>Secado rápido</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card card-service p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-spray-can text-info me-2"></i> Lavado Completo</h5>
                        <ul class="text-muted small">
                            <li>Todo lo del básico</li>
                            <li>Aspirado de interiores</li>
                            <li>Limpieza de tablero y puertas</li>
                            <li>Silicona en neumáticos</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card card-service p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-star text-info me-2"></i> Lavado Premium</h5>
                        <ul class="text-muted small">
                            <li>Lavado de motor a vapor</li>
                            <li>Lavado de asientos y techo</li>
                            <li>Encerado orbital</li>
                            <li>Desinfección de ductos de A/C</li>
                        </ul>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <a href="gestionCitas.php?tipo=lavado" class="btn-gold-black" style="border-color: #0dcaf0; color: #0dcaf0;">
                        Reservar Lavado
                    </a>
                </div>

            </div>
        </div>

    </div>

    <footer class="bg-dark text-light text-center p-3 mt-5">
        <small>© 2025 Lubricentro La Cusqueña — Todos los derechos reservados</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init();</script>

</body>
</html>