<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

// Verifica el permiso del usuario. Asumo que solo los administradores pueden eliminar.
// Si Secretarias también pueden eliminar, cambia a ['Administrador', 'Secretaria']
verificarPermiso(['Administrador']);

// Decodifica los datos JSON enviados en el cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Valida si el ID de la cotización fue proporcionado
if (!isset($data['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'ID de cotización no proporcionado.']);
    exit();
}

$id = $data['id']; // Obtiene el ID de la cotización a eliminar

try {
    // Prepara la consulta SQL para eliminar la cotización por su ID
    $stmt = $conn->prepare("DELETE FROM cotizaciones WHERE id = :id");
    // Vincula el parámetro ID de forma segura para prevenir inyecciones SQL
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Verifica si se eliminó alguna fila
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Cotización eliminada correctamente.']);
    } else {
        // Si no se eliminó ninguna fila, es porque el ID no existía
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'message' => 'La cotización con el ID especificado no fue encontrada.']);
    }

    $stmt = null;
    $conn = null;

} catch (PDOException $e) {
    // Captura cualquier excepción de PDO (errores de base de datos)
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la cotización: ' . $e->getMessage()]);
}
?>