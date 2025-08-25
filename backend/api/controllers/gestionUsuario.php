<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
include '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($conn === null) {
    echo json_encode(["error" => "No se pudo establecer la conexión a la base de datos."]);
    exit();
}

// LISTAR o BUSCAR
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $query = "SELECT * FROM usuarios";

    if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
        $buscar = "%" . $_GET['buscar'] . "%";
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario LIKE :buscar OR correo LIKE :buscar");
        $stmt->execute(['buscar' => $buscar]);
    } else {
        $stmt = $conn->query($query);
    }

    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($usuarios);
}

// AGREGAR USUARIO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $usuario = $data['usuario'];
    $contrasena = password_hash($data['contrasena'], PASSWORD_DEFAULT);
    $correo = $data['correo'];
    $rol = $data['rol'];
    $estado = $data['estado'];

    try {
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contrasena, correo, rol, estado) 
                                VALUES (:usuario, :contrasena, :correo, :rol, :estado)");
        $stmt->execute([
            'usuario' => $usuario,
            'contrasena' => $contrasena,
            'correo' => $correo,
            'rol' => $rol,
            'estado' => $estado
        ]);

        echo json_encode([
            "type" => "success",
            "message" => "Usuario agregado correctamente"
        ]);
    } catch (PDOException $e) {
        // Código 23000 es para violación de restricción de integridad (como UNIQUE)
        if ($e->getCode() == 23000) {
            echo json_encode([
                "type" => "warning",
                "message" => "El correo ya está registrado."
            ]);
        } else {
            echo json_encode([
                "type" => "error",
                "message" => "Error desconocido: " . $e->getMessage()
            ]);
        }
    }
}

// ACTUALIZAR USUARIO
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    $id = $data['id'];
    $usuario = $data['usuario'];
    $contrasena = $data['contrasena'];
    $correo = $data['correo'];
    $rol = $data['rol'];
    $estado = $data['estado'];

     try {
    $stmt = $conn->prepare("UPDATE usuarios SET usuario = :usuario, contrasena = :contrasena, correo = :correo, rol = :rol, estado = :estado WHERE id = :id");
    $stmt->execute([
        'id' => $id,
        'usuario' => $usuario,
        'contrasena' => $contrasena,
        'correo' => $correo,
        'rol' => $rol,
        'estado' => $estado
    ]);

    echo json_encode([
        "type" => "success",
        "message" => "Usuario actualizado correctamente"
    ]);
    } catch (PDOException $e) {
        // Código 23000 es para violación de restricción de integridad (como UNIQUE)
        if ($e->getCode() == 23000) {
            echo json_encode([
                "type" => "warning",
                "message" => "El correo ya está registrado."
            ]);
        } else {
            echo json_encode([
                "type" => "error",
                "message" => "Error desconocido: " . $e->getMessage()
            ]);
        }
    }
}

// ELIMINAR USUARIO
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'];

    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id]);

    echo json_encode(["message" => "Usuario eliminado correctamente"]);
}
?>
