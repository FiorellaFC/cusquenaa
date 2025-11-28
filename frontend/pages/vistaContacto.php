<?php
// Iniciar sesión para el navbar
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Lubricentro La Cusqueña</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* Ajuste para Navbar Fijo */
        body {
            padding-top: 70px;
            font-family: "Poppins", sans-serif;
            background: #f5f7fa;
        }

        /* HERO */
        #hero {
            background: linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)),
                        url('../css/imagenes/img1.jpg') center center/cover no-repeat fixed;
            height: 45vh;
            position: relative;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-radius: 0 0 50px 50px;
            margin-bottom: 40px;
        }

        #hero h1 {
            font-size: 3.3rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
            text-transform: uppercase;
        }

        #hero p {
            font-size: 1.2rem;
            margin-top: 10px;
            opacity: 0.9;
        }

        /* SECCIÓN: INFORMACIÓN */
        .info-box {
            background: #ffffff;
            border-radius: 18px;
            padding: 35px 25px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            transition: .3s;
            height: 100%;
            border: 1px solid #eee;
        }

        .info-box:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.1);
            border-color: #d4af37;
        }

        .info-box i {
            font-size: 2.5rem;
            color: #d4af37; /* Dorado */
            margin-bottom: 15px;
        }

        /* FORMULARIO */
        form {
            background: #fff;
            border-radius: 18px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }

        form input, form textarea {
            border-radius: 10px;
            padding: 12px;
            background-color: #fcfcfc;
        }
        
        form input:focus, form textarea:focus {
            border-color: #d4af37;
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
        }

        form button {
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            background-color: #000;
            border: 2px solid #d4af37;
            color: #d4af37;
            transition: 0.3s;
        }
        
        form button:hover {
            background-color: #d4af37;
            color: #000;
            border-color: #d4af37;
        }

        /* MAPA */
        .map-container {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        /* FOOTER */
        footer {
            background: #1f1f1f;
            color: white;
            padding: 40px 0;
            margin-top: 60px;
        }
        footer p { margin: 0; opacity: 0.8; }
    </style>
</head>

<body>

    <?php include 'navbarcusquena.php'; ?>

    <section id="hero" data-aos="fade-down">
        <div class="content">
            <h1>Contáctanos</h1>
            <p>Estamos aquí para ayudarte con el cuidado de tu vehículo</p>
        </div>
    </section>

    <section class="container py-5">
        <h2 class="text-center mb-5 fw-bold text-uppercase" data-aos="fade-up">Información de Contacto</h2>

        <div class="row g-4">

            <div class="col-md-4" data-aos="fade-right">
                <div class="info-box">
                    <i class="fas fa-phone"></i>
                    <h5 class="mt-3 fw-bold">Teléfono</h5>
                    <p class="text-muted">+51 1 7651393 – +51 1 4551880</p>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up">
                <div class="info-box">
                    <i class="fas fa-map-marker-alt"></i>
                    <h5 class="mt-3 fw-bold">Dirección</h5>
                    <p class="text-muted">Pro. Castilla Lote 1, Predio Rural La Poncho<br>Lurín – Lima, Perú</p>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-left">
                <div class="info-box">
                    <i class="fas fa-clock"></i>
                    <h5 class="mt-3 fw-bold">Horarios</h5>
                    <p class="text-muted">Lunes a Sábado: 8:00 a.m. – 6:00 p.m.<br>Domingos: 8:00 a.m. – 1:00 p.m.</p>
                </div>
            </div>

        </div>
    </section>

    <section class="container pb-5">
        <h2 class="text-center mb-4 fw-bold text-uppercase" data-aos="fade-up">Envíanos un Mensaje</h2>

        <div class="row justify-content-center">
            <div class="col-md-8" data-aos="zoom-in">

                <form id="formContacto">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre completo</label>
                        <input type="text" id="nombre" class="form-control" required placeholder="Tu nombre">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Correo</label>
                        <input type="email" id="correo" class="form-control" required placeholder="tucorreo@email.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mensaje</label>
                        <textarea id="mensaje" class="form-control" rows="4" required placeholder="Escribe aquí tu consulta..."></textarea>
                    </div>

                    <button type="submit" class="btn w-100">ENVIAR MENSAJE</button>
                </form>

            </div>
        </div>
    </section>

    <section class="container pb-5">
        <h2 class="text-center mb-4 fw-bold text-uppercase" data-aos="fade-up">Ubícanos</h2>

        <div class="map-container ratio ratio-16x9" data-aos="zoom-in">
            <iframe
                src="https://maps.google.com/maps?q=Lurin,Lima&t=&z=13&ie=UTF8&iwloc=&output=embed"
                allowfullscreen="" loading="lazy"></iframe>
        </div>
    </section>

    <footer class="text-center">
        <div class="container">
            <p>© 2025 Lubricentro La Cusqueña — Todos los derechos reservados.</p>
        </div>
    </footer>

    <div class="modal fade" id="modalSuccess" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <div style="font-size: 3rem; color: #198754; margin-bottom: 15px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h4 class="fw-bold">¡Mensaje enviado!</h4>
                <p class="text-muted">Gracias por escribirnos, te responderemos pronto.</p>
                <button class="btn btn-dark mt-2" data-bs-dismiss="modal" style="border: 1px solid #d4af37;">Aceptar</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });
    </script>

    <script>
        document.getElementById("formContacto").addEventListener("submit", async function (e) {
            e.preventDefault();
            
            // Efecto visual de carga en el botón
            const btnSubmit = this.querySelector('button[type="submit"]');
            const originalText = btnSubmit.innerText;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';
            btnSubmit.disabled = true;

            const payload = {
                nombre_completo: document.getElementById("nombre").value,
                correo: document.getElementById("correo").value,
                mensaje: document.getElementById("mensaje").value
            };

            try {
                const response = await fetch("../../backend/api/controllers/vista_contacto/contacto.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.message) {
                    const successModal = new bootstrap.Modal(document.getElementById("modalSuccess"));
                    successModal.show();
                    this.reset();
                } else {
                    alert("Error: " + (result.error ?? "No se pudo enviar."));
                }

            } catch (error) {
                alert("Error al conectar con el servidor.");
                console.log(error);
            } finally {
                // Restaurar botón
                btnSubmit.innerText = originalText;
                btnSubmit.disabled = false;
            }
        });
    </script>

</body>
</html>