<?php
// --- CABECERAS Y CONFIGURACIÓN INICIAL ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Manejar la solicitud pre-vuelo OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

header('Content-Type: application/json');

// Asegúrate de que la ruta a tu conexión de BD sea correcta
require_once '../../includes/db.php'; 

// Verificar la conexión a la base de datos
if ($conn === null) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Error de conexión a la base de datos."]);
    exit;
}

// --- ENRUTADOR DE ACCIONES ---
$accion = $_REQUEST['accion'] ?? '';

switch ($accion) {
    case 'listar':
        listarDeudas($conn);
        break;
    case 'registrar':
        registrarDeuda($conn);
        break;
    case 'modificar':
        modificarDeuda($conn);
        break;
    case 'eliminar':
        eliminarDeuda($conn);
        break;
    case 'listarPagos':
        listarPagos($conn);
        break;
    case 'registrarPago':
        registrarPago($conn);
        break;
    case 'eliminarPago':
        eliminarPago($conn);
        break;
    case 'obtenerTotales': // <-- NUEVA ACCIÓN
        obtenerTotales($conn);
        break;
    default:
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Acción no válida."]);
}

// --- FUNCIONES PARA GESTIÓN DE DEUDAS ---

function listarDeudas($conn) {
    // Recoger todos los parámetros de filtrado de GET
    $buscar = $_GET['buscar'] ?? '';
    $filtroTipoDeuda = $_GET['filtroTipoDeuda'] ?? '';
    $filtroTipoPersona = $_GET['filtroTipoPersona'] ?? '';
    $filtroEstado = $_GET['filtroEstado'] ?? '';

    // Iniciar la consulta base
    $sql = "SELECT * FROM deudas WHERE 1=1"; // '1=1' es una cláusula para facilitar la adición de condiciones
    $params = [];

    // Construir la consulta dinámicamente
    if (!empty($buscar)) {
        $sql .= " AND nombre LIKE ?";
        $params[] = "%$buscar%";
    }
    if (!empty($filtroTipoDeuda)) {
        $sql .= " AND tipo_deuda = ?";
        $params[] = $filtroTipoDeuda;
    }
    if (!empty($filtroTipoPersona)) {
        $sql .= " AND tipo_persona = ?";
        $params[] = $filtroTipoPersona;
    }
    if (!empty($filtroEstado)) {
        $sql .= " AND estado = ?";
        $params[] = $filtroEstado;
    }

    $sql .= " ORDER BY fecha_inicio DESC";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $deudas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($deudas);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Error al listar deudas: " . $e->getMessage()]);
    }
}

function registrarDeuda($conn) {
    // Recoger datos del POST
    $nombre = $_POST['nombre'] ?? '';
    $tipoDeuda = $_POST['tipoDeuda'] ?? '';
    $tipoPersona = $_POST['tipoPersona'] ?? '';
    $montoDeuda = $_POST['montoDeuda'] ?? 0;
    $saldoPendiente = $_POST['saldoPendiente'] ?? $montoDeuda; // Si no se especifica, el saldo es el monto total
    $fechaInicio = $_POST['fechaInicioDeuda'] ?? date('Y-m-d');
    $tasaInteres = $_POST['tasaInteres'] ?? 0;
    $estado = $_POST['estado'] ?? 'pendiente';

    try {
        $sql = "INSERT INTO deudas (nombre, tipo_deuda, tipo_persona, monto_deuda, saldo_pendiente, fecha_inicio, tasa_interes, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([$nombre, $tipoDeuda, $tipoPersona, $montoDeuda, $saldoPendiente, $fechaInicio, $tasaInteres, $estado]);
        
        echo json_encode(["success" => $success]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Error al registrar la deuda: " . $e->getMessage()]);
    }
}

function modificarDeuda($conn) {
    // Recoger datos del POST
    $id = $_POST['deudaId'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $tipoDeuda = $_POST['tipoDeuda'] ?? '';
    $tipoPersona = $_POST['tipoPersona'] ?? '';
    $montoDeuda = $_POST['montoDeuda'] ?? 0;
    $saldoPendiente = $_POST['saldoPendiente'] ?? 0;
    $fechaInicio = $_POST['fechaInicioDeuda'] ?? date('Y-m-d');
    $tasaInteres = $_POST['tasaInteres'] ?? 0;
    $estado = $_POST['estado'] ?? 'pendiente';

    if (!$id) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "ID de deuda no proporcionado."]);
        return;
    }

    try {
        $sql = "UPDATE deudas SET nombre=?, tipo_deuda=?, tipo_persona=?, monto_deuda=?, saldo_pendiente=?, fecha_inicio=?, tasa_interes=?, estado=? 
                WHERE id=?";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([$nombre, $tipoDeuda, $tipoPersona, $montoDeuda, $saldoPendiente, $fechaInicio, $tasaInteres, $estado, $id]);
        
        echo json_encode(["success" => $success]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Error al modificar la deuda: " . $e->getMessage()]);
    }
}

function eliminarDeuda($conn) {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "ID de deuda no proporcionado."]);
        return;
    }

    $conn->beginTransaction();
    try {
        // 1. Eliminar pagos asociados (CASCADE en la DB se encargará, pero esto es una doble seguridad o para ON DELETE RESTRICT)
        // Si usas ON DELETE CASCADE en la definición de la FK, esta línea es opcional
        $sqlPagos = "DELETE FROM pagos WHERE deuda_id = ?";
        $stmtPagos = $conn->prepare($sqlPagos);
        $stmtPagos->execute([$id]);

        // 2. Eliminar la deuda
        $sqlDeuda = "DELETE FROM deudas WHERE id = ?";
        $stmtDeuda = $conn->prepare($sqlDeuda);
        $success = $stmtDeuda->execute([$id]);

        $conn->commit();
        echo json_encode(["success" => $success]);
    } catch (PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Error al eliminar la deuda: " . $e->getMessage()]);
    }
}


// --- FUNCIONES PARA GESTIÓN DE PAGOS ---

function listarPagos($conn) {
    $deudaId = $_GET['deudaId'] ?? null;
    if (!$deudaId) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "ID de deuda no proporcionado."]);
        return;
    }
    try {
        $sql = "SELECT * FROM pagos WHERE deuda_id = ? ORDER BY fecha_pago DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$deudaId]);
        $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($pagos);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Error al listar pagos: " . $e->getMessage()]);
    }
}

function registrarPago($conn) {
    $deudaId = $_POST['deudaId'] ?? null;
    $fechaPago = $_POST['fechaPago'] ?? date('Y-m-d');
    $montoPago = $_POST['montoPago'] ?? 0;

    if (!$deudaId || $montoPago <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Datos de pago inválidos (ID de deuda o monto no válidos)."]);
        return;
    }

    $conn->beginTransaction();
    try {
        // 1. Registrar el nuevo pago
        $sqlInsert = "INSERT INTO pagos (deuda_id, fecha_pago, monto_pagado) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->execute([$deudaId, $fechaPago, $montoPago]);

        // 2. Actualizar el saldo pendiente de la deuda
        $sqlUpdate = "UPDATE deudas SET saldo_pendiente = saldo_pendiente - ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->execute([$montoPago, $deudaId]);

        // 3. Opcional: Actualizar el estado de la deuda si el saldo_pendiente llega a 0
        $sqlCheckSaldo = "SELECT saldo_pendiente FROM deudas WHERE id = ?";
        $stmtCheckSaldo = $conn->prepare($sqlCheckSaldo);
        $stmtCheckSaldo->execute([$deudaId]);
        $saldoActual = $stmtCheckSaldo->fetchColumn();

        if ($saldoActual !== false && $saldoActual <= 0) {
            $sqlUpdateEstado = "UPDATE deudas SET estado = 'pagada' WHERE id = ?";
            $stmtUpdateEstado = $conn->prepare($sqlUpdateEstado);
            $stmtUpdateEstado->execute([$deudaId]);
        }


        $conn->commit();
        echo json_encode(["success" => true]);
    } catch (PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Error al registrar el pago: " . $e->getMessage()]);
    }
}

function eliminarPago($conn) {
    $pagoId = $_GET['id'] ?? null; // El ID del pago a eliminar

    if (!$pagoId) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "ID de pago no proporcionado."]);
        return;
    }
    
    $conn->beginTransaction();
    try {
        // 1. Obtener el monto y el id de la deuda del pago a eliminar
        $sqlSelect = "SELECT deuda_id, monto_pagado FROM pagos WHERE id = ?";
        $stmtSelect = $conn->prepare($sqlSelect);
        $stmtSelect->execute([$pagoId]);
        $pago = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if (!$pago) {
            throw new Exception("Pago no encontrado.");
        }

        $deudaId = $pago['deuda_id'];
        $montoPagado = $pago['monto_pagado'];

        // 2. Eliminar el pago
        $sqlDelete = "DELETE FROM pagos WHERE id = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->execute([$pagoId]);

        // 3. Devolver el monto al saldo pendiente de la deuda
        $sqlUpdate = "UPDATE deudas SET saldo_pendiente = saldo_pendiente + ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->execute([$montoPagado, $deudaId]);

        // 4. Opcional: Actualizar el estado de la deuda si se eliminó un pago y el saldo deja de ser 0 o es mayor
        $sqlCheckSaldo = "SELECT saldo_pendiente FROM deudas WHERE id = ?";
        $stmtCheckSaldo = $conn->prepare($sqlCheckSaldo);
        $stmtCheckSaldo->execute([$deudaId]);
        $saldoActual = $stmtCheckSaldo->fetchColumn();

        // Si el saldo es mayor a 0, la deuda debería volver a estado 'pendiente'
        // Si el saldo es <= 0, podría permanecer 'pagada' (si ya estaba) o 'en_atraso' (si la lógica lo determina)
        // Aquí simplificamos: si hay saldo > 0, es 'pendiente'. Puedes refinar esta lógica.
        if ($saldoActual !== false && $saldoActual > 0) { 
            $sqlUpdateEstado = "UPDATE deudas SET estado = 'pendiente' WHERE id = ?";
            $stmtUpdateEstado = $conn->prepare($sqlUpdateEstado);
            $stmtUpdateEstado->execute([$deudaId]);
        } else if ($saldoActual !== false && $saldoActual <= 0) { // Si el saldo sigue siendo 0 o negativo
             $sqlUpdateEstado = "UPDATE deudas SET estado = 'pagada' WHERE id = ?";
            $stmtUpdateEstado = $conn->prepare($sqlUpdateEstado);
            $stmtUpdateEstado->execute([$deudaId]);
        }

        $conn->commit();
        echo json_encode(["success" => true]);

    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Error al eliminar el pago: " . $e->getMessage()]);
    }
}
function obtenerTotales($conn) {
    try {
        $sql = "SELECT SUM(monto_deuda) AS total_prestado, SUM(saldo_pendiente) AS total_pendiente FROM deudas";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $totales = $stmt->fetch(PDO::FETCH_ASSOC);

        $total_prestado = $totales['total_prestado'] ?? 0.00;
        $total_pendiente = $totales['total_pendiente'] ?? 0.00;

        echo json_encode(["success" => true, "total_prestado" => $total_prestado, "total_pendiente" => $total_pendiente]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Error al obtener los totales: " . $e->getMessage()]);
    }
}