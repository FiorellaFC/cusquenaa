<?php
session_start();

// Configuración base de rutas
$base_url = '/cusquena';

// 1. Verificar sesión y método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['recovery_data'])) {
    $_SESSION['error'] = "Solicitud inválida o sesión expirada";
    header("Location: $base_url/frontend/pages/recuperar_contrasena.php");
    exit();
}

// 2. Conexión a la base de datos
$host = 'localhost';
$dbname = 'la_cusquena';
$db_username = 'root';
$db_password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $_SESSION['error'] = "Error de conexión a la base de datos";
    header("Location: $base_url/frontend/pages/contrasena_nueva.php");
    exit();
}

// 3. Obtener y validar datos del formulario
$correo = $_POST['correo'] ?? ''; // Cambiado de 'email' a 'correo'
$password = $_POST['password'] ?? '';
$passwordRepeat = $_POST['password-repeat'] ?? '';

// Verificar que el correo coincide con la sesión
if ($correo !== $_SESSION['recovery_data']['correo']) {
    $_SESSION['error'] = "Inconsistencia en los datos de usuario. Esperado: {$_SESSION['recovery_data']['correo']}, Recibido: $correo";
    header("Location: $base_url/frontend/pages/contrasena_nueva.php.");
    exit();
}

// [...] Resto del código permanece igual

// 4. Validar contraseñas
if (empty($password) || empty($passwordRepeat)) {
    $_SESSION['error'] = "Las contraseñas no pueden estar vacías";
    header("Location: $base_url/frontend/pages/contrasena_nueva.php.");
    exit();
}

if ($password !== $passwordRepeat) {
    $_SESSION['error'] = "Las contraseñas no coinciden";
    header("Location: $base_url/frontend/pages/contrasena_nueva.php.");
    exit();
}

if (strlen($password) < 8) {
    $_SESSION['error'] = "La contraseña debe tener al menos 8 caracteres";
    header("Location: $base_url/frontend/pages/contrasena_nueva.php");
    exit();
}

// 5. Actualizar contraseña
try {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Iniciar transacción
    $conn->beginTransaction();
    
    // Actualizar contraseña (usando los nombres correctos de la tabla Usuario)
    $stmt = $conn->prepare("UPDATE Usuarios SET contrasena = :contrasena WHERE correo = :correo");
    $stmt->bindParam(':contrasena', $hashedPassword);
    $stmt->bindParam(':correo', $correo);
    
    if (!$stmt->execute()) {
        throw new Exception("No se pudo actualizar la contraseña");
    }
    
    // Registrar el cambio en histórico (opcional)
    if (isset($_SESSION['recovery_data']['usuario'])) {
        $stmt = $conn->prepare("INSERT INTO historico_cambios (usuario, accion, fecha) 
                               VALUES (:usuario, 'Cambio de contraseña', NOW())");
        $stmt->bindParam(':usuario', $_SESSION['recovery_data']['usuario']);
        $stmt->execute();
    }
    
    // Confirmar transacción
    $conn->commit();
    
    // Limpiar sesión y redirigir
    $usuario = $_SESSION['recovery_data']['usuario'] ?? '';
    unset($_SESSION['recovery_data']);
    
    $_SESSION['mensaje'] = "¡Contraseña actualizada con éxito para $usuario!";
    header("Location: $base_url/index.html");
    exit();

} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollBack();
    
    $_SESSION['error'] = "Error al actualizar: " . $e->getMessage();
    header("Location: $base_url/frontend/pages/contrasena_nueva.php");
    exit();
}