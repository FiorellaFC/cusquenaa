<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

// Solo Administradores pueden eliminar (según tu ejemplo)
verificarPermiso(['Administrador']);

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'error' => 'ID de balance no proporcionado.']);
    exit();
}

$id = (int)$data['id']; // Asegúrate de que el ID sea un entero

try {
    // CORRECCIÓN: Cambiado 'prestamos' a 'balances_empresa'
    $stmt = $conn->prepare("DELETE FROM balances_empresa WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Comprobar si se eliminó alguna fila
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Balance eliminado correctamente.']);
    } else {
        // Si rowCount es 0, significa que no se encontró el ID o no se eliminó nada
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'error' => 'No se encontró el balance con el ID proporcionado.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de base de datos al eliminar balance: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al eliminar balance: ' . $e->getMessage()]);
}
?>