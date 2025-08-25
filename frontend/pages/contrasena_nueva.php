<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña | Cusqueña</title>
    <link rel="stylesheet" href="../css/contrasena_nueva.css">
</head>
<body>
    <div class="container">
        <div class="reset-box">
        <img src="imagenes/logohumano.png" class="img">
            <h2>Crear nueva contraseña</h2>
            
            <?php if (isset($_SESSION['recovery_data'])): ?>
                <div class="user-info">
                    <p>Estás actualizando la contraseña para:</p>
                    <h3><?php echo htmlspecialchars($_SESSION['recovery_data']['usuario'] ?? ''); ?></h3>
                    <p class="user-role">Rol: <?php echo htmlspecialchars($_SESSION['recovery_data']['rol'] ?? ''); ?></p>
                </div>
            <?php endif; ?>
            
            <form id="passwordForm" action="../../backend/api/controllers/actualizar_contrasena.php" method="POST">
                <input type="hidden" name="correo" value="<?php echo htmlspecialchars($_SESSION['recovery_data']['correo'] ?? ''); ?>">
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="Nueva contraseña" required>
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password-repeat" placeholder="Confirmar nueva contraseña" required>
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                </div>
                
                <div class="password-rules">
                    <p>La contraseña debe cumplir con:</p>
                    <ul>
                        <li>Mínimo 8 caracteres</li>
                        <li>Mayúsculas y minúsculas</li>
                        <li>Al menos un número</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-sync-alt"></i> Actualizar Contraseña
                </button>
            </form>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje']); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Script de validación -->
    <script src="../../js/contrasena_nueva.js"></script>
</body>
</html>