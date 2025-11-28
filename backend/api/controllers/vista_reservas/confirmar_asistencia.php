<?php
// Configuración de zona horaria y cabeceras
date_default_timezone_set('America/Lima');
header('Content-Type: text/html; charset=utf-8');

// Conexión a la base de datos
require_once "../../../includes/db.php"; 

// ----------------------------------------------------
// Lógica de Confirmación
// ----------------------------------------------------

$mensaje = "";
$exito = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    try {
        // 1. Buscar la cita pendiente con ese token
        $stmt = $conn->prepare("SELECT id FROM citas WHERE token_confirmacion = :token AND estado = 'pendiente'");
        $stmt->execute(['token' => $token]);
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cita) {
            // 2. Si la cita existe y está pendiente, la actualizamos a 'confirmada'
            $sql_update = "UPDATE citas SET estado = 'confirmada', token_confirmacion = NULL WHERE id = :id";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute(['id' => $cita['id']]);

            $mensaje = "✅ ¡Cita Confirmada con Éxito! Tu reserva ha sido registrada y está confirmada. ¡Te esperamos!";
            $exito = true;
        } else {
            // 3. Si no existe o ya está confirmada/cancelada
            // Incluye el caso de que el token sea NULL porque ya fue confirmado antes
            $stmt_check_confirmed = $conn->prepare("SELECT id FROM citas WHERE token_confirmacion IS NULL AND estado = 'confirmada' AND id = (SELECT id FROM citas WHERE token_confirmacion = :token_fallback)");
            $stmt_check_confirmed->execute(['token_fallback' => $token]);
            
            if ($stmt_check_confirmed->fetch()) {
                $mensaje = "ℹ️ Esta cita ya había sido confirmada previamente.";
            } else {
                $mensaje = "❌ Error: El enlace de confirmación es inválido o la cita ha expirado/sido cancelada.";
            }
        }
    } catch (PDOException $e) {
        $mensaje = "❌ Error interno de la base de datos: " . $e->getMessage();
    }
} else {
    $mensaje = "❌ Acceso denegado: Se requiere un token de confirmación.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Cita | La Cusqueña</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background-color: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); text-align: center; max-width: 400px; width: 90%; }
        h1 { color: <?php echo $exito ? '#198754' : '#dc3545'; ?>; margin-bottom: 20px; font-size: 24px; }
        p { font-size: 16px; line-height: 1.5; }
        .success-icon { color: #198754; font-size: 50px; line-height: 1; margin-bottom: 15px; }
        .error-icon { color: #dc3545; font-size: 50px; line-height: 1; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="<?php echo $exito ? 'success-icon' : 'error-icon'; ?>">
            <?php echo $exito ? '👍' : '⚠️'; ?>
        </div>
        <h1><?php echo $exito ? '¡Cita Confirmada!' : 'Estatus de la Reserva'; ?></h1>
        <p><?php echo $mensaje; ?></p>
        <p style="margin-top: 25px;">Gracias por usar nuestro sistema de reservas.</p>
    </div>
</body>
</html>