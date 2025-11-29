<?php
// 1. INICIAR SESIÓN (Obligatorio para que funcione el Login en el Navbar)
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lubricentro La Cusqueña</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* Reset y base */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            overflow-x: hidden;
        }
        /* Hero Section con efecto parallax */
        #hero {
            background: url('../css/imagenes/img1.jpg') center center / cover no-repeat fixed;
            height: 100vh;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #fff;
            /* Ajuste para que el contenido baje un poco si el navbar es fijo */
            padding-top: 60px; 
        }
frontend/pages/vistaCusquena.html
        #hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
        }

        #hero .content {
            position: relative;
            z-index: 1;
            padding: 0 15px;
        }

        #hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            text-transform: uppercase;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.8);
        }

        #hero p {
            font-size: 1.3rem;
            margin-top: 1rem;
            font-weight: 400;
            color: #e0e0e0;
        }

        /* Botón del hero */
        .btn-hero {
            background-color: #ffc107;
            border: none;
            color: #212529;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease-in-out;
            margin-top: 2rem;
            display: inline-block;
            text-decoration: none;
        }

        .btn-hero:hover {
            background-color: #e0a800;
            transform: scale(1.05);
            box-shadow: 0 8px 15px rgba(255, 193, 7, 0.3);
            color: #212529;
        }

        /* Footer */
        footer {
            background-color: #212529;
            color: #fff;
            padding: 50px 0 30px 0;
            text-align: center;
        }

        footer p { margin: 0; }

        @media (max-width: 768px) {
            #hero h1 { font-size: 2.5rem; }
            #hero p { font-size: 1.1rem; }
        }
    </style>
</head>

<body>
    
    <?php include 'navbarcusquena.php'; ?>

    <section id="hero">
        <div class="content" data-aos="zoom-in" data-aos-delay="100">
            <h1>Lubricentro La Cusqueña</h1>
            <p>Calidad, confianza y rapidez para el cuidado de tu vehículo</p>
            <a href="vistaServicios.php" class="btn-hero">Ver Servicios</a>
        </div>
    </section>
 
    <section id="nosotros" class="py-5" style="background-color: #fff;">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="fw-bold text-uppercase">Nosotros</h2>
                <p class="text-muted">Comprometidos con el cuidado y mantenimiento de tu vehículo</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-right">
                    <div class="p-4 shadow rounded bg-light h-100">
                        <h4 class="fw-bold text-dark">¿Quiénes Somos?</h4>
                        <p>
                            En <strong>Lubricentro La Cusqueña</strong> somos un equipo especializado en mantenimiento automotriz, 
                            dedicados a brindar atención rápida, confiable y transparente. Nuestro objetivo es asegurar 
                            el buen rendimiento y la vida útil de cada vehículo que atendemos.
                        </p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-up">
                    <div class="p-4 shadow rounded bg-light h-100">
                        <h4 class="fw-bold text-dark">Misión</h4>
                        <p>
                            Brindar un servicio automotriz honesto y de calidad en Lima, cuidando cada vehículo con responsabilidad 
                            y ofreciendo a nuestros clientes la confianza de que están en buenas manos.
                        </p>
                    </div>
                </div>

                <div class="col-md-4" data-aos="fade-left">
                    <div class="p-4 shadow rounded bg-light h-100">
                        <h4 class="fw-bold text-dark">Visión</h4>
                        <p>
                            Ser reconocidos en nuestra comunidad como el lubricentro de mayor confianza, destacando por nuestra 
                            atención personalizada, responsabilidad y excelencia en el mantenimiento automotriz.
                        </p>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-12" data-aos="fade-up">
                    <div class="p-4 shadow rounded bg-light">
                        <h4 class="fw-bold text-dark">¿Qué Realizamos?</h4>
                        <p>
                            En Lubricentro La Cusqueña ofrecemos servicios especializados para el cuidado completo de tu vehículo.
                            Nuestro equipo realiza:
                        </p>
                        <ul>
                            <li>Cambio de aceite y filtros.</li>
                            <li>Lavado vehicular completo (exterior, interior y motor).</li>
                            <li>Mantenimiento preventivo y correctivo.</li>
                            <li>Revisión de frenos, suspensión y niveles.</li>
                            <li>Diagnóstico rápido de fallas menores.</li>
                            <li>Limpieza de motor y mantenimiento general.</li>
                        </ul>
                        <p>
                            Cada servicio es realizado con productos de calidad y mano de obra profesional, garantizando 
                            el buen desempeño y la buena imagen de tu vehículo.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-light text-center p-3 mt-5">
        <small>© 2025 Lubricentro La Cusqueña — Todos los derechos reservados</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1200,
            once: true
        });
    </script>
</body>
</html>