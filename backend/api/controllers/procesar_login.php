<?php
session_start();

// Incluir conexión centralizada
require_once __DIR__ . '/../../includes/db.php'; 
// Ahora dispones de la variable $conn ya instanciada como PDO

try {
    // Leer los datos enviados en formato JSON
    $data = json_decode(file_get_contents("php://input"), true);

    $usuario   = $data['username'] ?? '';
    $contrasena = $data['password'] ?? '';

    if (empty($usuario) || empty($contrasena)) {
        throw new Exception('Usuario y contraseña son requeridos');
    }

    // Consulta para obtener al usuario
    $sql  = "SELECT * FROM Usuarios WHERE usuario = :usuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica que la contraseña coincida con el hash almacenado
        if (password_verify($contrasena, $user['contrasena'])) {
            // Guardar datos en la sesión
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['rol']     = $user['rol'];

            echo json_encode([
                "success" => true,
                "message" => "Login exitoso",
                "cargo"   => $user['rol']
            ]);
        } else {
            throw new Exception('Contraseña incorrecta');
        }
    } else {
        throw new Exception('Usuario no encontrado');
    }
} catch (PDOException $e) {
    error_log("Error en login (PDO): " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión a la base de datos"
    ]);
} catch (Exception $e) {
    error_log("Error en login: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
