<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperacion Contraseña</title>
    <link rel="stylesheet" href="../css/recuperar_contraseña.css">
</head>
<body>
    <div class="container">
        <div class="reset-box">
            <h2>Recupera tu contraseña</h2>
            <form action="../../backend/api/controllers/procesar_recuperacion.php" method="POST">
                <input type="email" name="email" placeholder="Tu correo electrónico" required>
                <button type="submit">Enviar enlace de recuperación</button>
                 <a href="../../index.html" class="button">Volver</a>
            </form>
            
            <!-- Mostrar mensajes de error/éxito -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
        
            fetch('http://localhost/cusquena/backend/api/controllers/enviar_recuperacion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email })
            })
            .then(res => res.json())
            .then(data => alert(data.message))
            .catch(err => alert('Error al enviar solicitud'));
        });
    </script>

</body>
</html>





