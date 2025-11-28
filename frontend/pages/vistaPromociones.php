<?php
// Iniciar sesión para el navbar
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promociones - Lubricentro La Cusqueña</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* Ajuste Navbar Fijo */
        body { padding-top: 70px; font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }

        /* Hero Promociones */
        #hero-promos {
            background: linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)),
                url('../css/imagenes/img2.jpg') center center/cover no-repeat;
            height: 60vh; /* Ajustado para que no sea tan alto */
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            border-radius: 0 0 50px 50px;
            margin-bottom: 40px;
        }

        #hero-promos h1 {
            font-size: 3.5rem;
            font-weight: 900;
            letter-spacing: 2px;
            text-shadow: 4px 4px 12px rgba(0, 0, 0, 0.7);
        }

        #hero-promos p {
            font-size: 1.5rem;
            opacity: 0.9;
        }

        /* Título sección */
        section h2 {
            font-weight: 800;
            font-size: 2.4rem;
            color: #343a40;
            position: relative;
        }

        section h2::after {
            content: "";
            width: 80px;
            height: 4px;
            background: #dc3545;
            display: block;
            margin: 10px auto 0;
            border-radius: 5px;
        }

        /* Cards Promos */
        .promo-card {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            padding: 35px 20px;
            background: white;
            transition: all 0.4s ease-in-out;
            position: relative;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            height: 100%; /* Para que todas tengan la misma altura */
        }

        .promo-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.25);
        }

        .promo-card i {
            color: #dc3545 !important;
            transition: 0.3s;
        }

        .promo-card:hover i {
            transform: scale(1.2);
        }

        /* Badge */
        .promo-badge {
            background: #dc3545;
            color: white;
            font-weight: bold;
            padding: 7px 15px;
            border-radius: 50px;
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 0.85rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* Footer */
        footer {
            background-color: #111;
            padding: 40px 0;
            text-align: center;
            color: white;
            margin-top: 50px;
        }

        footer p {
            opacity: 0.8;
            letter-spacing: 1px;
            margin: 0;
        }
    </style>
</head>

<body>

    <?php include 'navbarcusquena.php'; ?>

    <section id="hero-promos">
        <div class="content" data-aos="zoom-in">
            <h1>Promociones Especiales</h1>
            <p>Aprovecha nuestros descuentos exclusivos por tiempo limitado</p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Ofertas del Mes</h2>
            <div class="row g-4" id="promocionesContainer">
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-danger" style="width:3rem;height:3rem;"></div>
                    <p class="mt-3 text-muted">Cargando ofertas...</p>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Cargar promociones desde el backend
        // Ajusta la ruta si es necesario, usé ../.. asumiendo que estás en frontend/pages
        fetch('../../backend/api/controllers/vista_promociones/getPromociones.php')
            .then(r => {
                if (!r.ok) throw new Error("Error de red");
                return r.json();
            })
            .then(promos => {
                const cont = document.getElementById('promocionesContainer');
                
                if (!Array.isArray(promos) || promos.length === 0) {
                    cont.innerHTML = '<div class="col-12 text-center py-5"><h4 class="text-muted">No hay promociones activas en este momento</h4></div>';
                    return;
                }
                
                cont.innerHTML = promos.map(p => `
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="promo-card p-4 text-center shadow">
                            ${p.badge ? `<span class="promo-badge">${p.badge}</span>` : ''}
                            <i class="fas ${p.icono} fa-3x my-3 text-primary"></i>
                            <h5 class="fw-bold">${p.titulo}</h5>
                            <p>${p.descripcion}</p>
                            <p class="fw-bold text-success fs-4">S/ ${parseFloat(p.precio).toFixed(2)}</p>
                            <a href="gestionCitas.php?tipo=mantenimiento" class="btn btn-outline-danger btn-sm rounded-pill mt-2">Reservar Ahora</a>
                        </div>
                    </div>
                `).join('');
                
                // Refrescar animaciones AOS para los nuevos elementos
                if(typeof AOS !== 'undefined') AOS.refresh();
            })
            .catch((err) => {
                console.error(err);
                document.getElementById('promocionesContainer').innerHTML =
                    '<div class="col-12 text-center text-danger"><i class="fas fa-exclamation-circle fa-2x mb-3"></i><br>No se pudieron cargar las promociones.</div>';
            });
    </script>

    <footer>
        <p>© 2025 Lubricentro La Cusqueña — Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1100,
            once: true
        });
    </script>

</body>
</html>