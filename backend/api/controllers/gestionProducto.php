<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once '../../includes/db.php';
$method = $_SERVER['REQUEST_METHOD'];

function obtenerJSON() {
    return json_decode(file_get_contents("php://input"), true);
}

// Analizar el método HTTP y la acción
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} elseif (isset($_POST['action'])) {
    $action = $_POST['action'];
} else {
    $action = null;
}

switch ($action) {
    case 'listar':
        listarProductos();
        break;
    
    case 'agregar':
        agregarProducto($_POST);
        break;
    
    case 'eliminar':
        if (isset($_GET['id'])) {
            eliminarProducto($_GET['id']);
        }
        break;

    case 'editar':
        // Con el uso de FormData en JS, el método es POST
        editarProducto($_POST);
        break;
    
    case 'vender':
        venderProducto($_POST);
        break;

    case 'historial':
        if (isset($_GET['id'])) {
            obtenerHistorial($_GET['id']);
        }
        break;
    
    case 'eliminar_venta':
        // Aquí obtendremos los datos de forma manual, ya que el JS usa JSON
        $data = obtenerJSON();
        if (isset($data['id_venta'])) {
            eliminarVenta($data['id_venta']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método o acción no permitido']);
        break;
}

function listarProductos() {
    global $conn;

    $sql_productos = "SELECT * FROM productos ORDER BY id DESC";
    $stmt_productos = $conn->prepare($sql_productos);
    $stmt_productos->execute();
    $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para obtener la suma total del monto de ventas
    $sql_total_ventas = "SELECT SUM(monto) AS total_monto FROM productos";
    $stmt_total_ventas = $conn->prepare($sql_total_ventas);
    $stmt_total_ventas->execute();
    $total_monto = $stmt_total_ventas->fetch(PDO::FETCH_ASSOC)['total_monto'] ?? 0;
    
    // NUEVO: Consulta para calcular el monto total gastado
    // Suma la inversión inicial (inicial * precio_compra) y la inversión por ingresos (ingreso * precio_compra)
    $sql_total_gastado = "SELECT SUM((inicial + ingreso) * precio_compra) AS total_gastado FROM productos";
    $stmt_total_gastado = $conn->prepare($sql_total_gastado);
    $stmt_total_gastado->execute();
    $total_gastado = $stmt_total_gastado->fetch(PDO::FETCH_ASSOC)['total_gastado'] ?? 0;

    $respuesta = [
        'productos' => $productos,
        'total_monto' => $total_monto,
        // NUEVO: Agrega el monto total gastado al array de respuesta
        'total_gastado' => $total_gastado 
    ];

    echo json_encode($respuesta);
}

function agregarProducto($data) {
    global $conn;

    if (
        !isset($data['descripcion'], $data['precio_compra'], $data['precio_venta'],
                $data['inicial'], $data['ingreso'], $data['venta'],
                $data['monto'], $data['categoria'])
    ) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        return;
    }

    $queda = $data['inicial'] + $data['ingreso'] - $data['venta'];

    $sql = "INSERT INTO productos (descripcion, precio_compra, precio_venta, inicial, ingreso, queda, venta, monto, categoria)
            VALUES (:descripcion, :precio_compra, :precio_venta, :inicial, :ingreso, :queda, :venta, :monto, :categoria)";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':descripcion', $data['descripcion']);
    $stmt->bindValue(':precio_compra', $data['precio_compra']);
    $stmt->bindValue(':precio_venta', $data['precio_venta']);
    $stmt->bindValue(':inicial', $data['inicial']);
    $stmt->bindValue(':ingreso', $data['ingreso']);
    $stmt->bindValue(':queda', $queda);
    $stmt->bindValue(':venta', $data['venta']);
    $stmt->bindValue(':monto', $data['monto']);
    $stmt->bindValue(':categoria', $data['categoria']);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Producto agregado correctamente"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al agregar producto"]);
    }
}

function eliminarProducto($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM productos WHERE id = :id");
    $stmt->bindValue(':id', $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar producto']);
    }
}

// --- Nuevas funciones para manejar los modales ---

function editarProducto($data) {
    global $conn;

    if (!isset($data['idProducto']) || !isset($data['descripcion']) || !isset($data['precio_compra'])) {
        echo json_encode(["success" => false, "message" => "Datos incompletos para editar"]);
        return;
    }

    $queda = $data['inicial'] + $data['ingreso'] - $data['venta'];

    $sql = "UPDATE productos SET 
                descripcion = :descripcion, 
                precio_compra = :precio_compra, 
                precio_venta = :precio_venta, 
                inicial = :inicial, 
                ingreso = :ingreso, 
                queda = :queda, 
                venta = :venta, 
                monto = :monto, 
                categoria = :categoria
            WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $data['idProducto']);
    $stmt->bindValue(':descripcion', $data['descripcion']);
    $stmt->bindValue(':precio_compra', $data['precio_compra']);
    $stmt->bindValue(':precio_venta', $data['precio_venta']);
    $stmt->bindValue(':inicial', $data['inicial']);
    $stmt->bindValue(':ingreso', $data['ingreso']);
    $stmt->bindValue(':queda', $queda);
    $stmt->bindValue(':venta', $data['venta']);
    $stmt->bindValue(':monto', $data['monto']);
    $stmt->bindValue(':categoria', $data['categoria']);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Producto actualizado correctamente"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al actualizar producto"]);
    }
}

function venderProducto($data) {
    global $conn;

    if (!isset($data['idProducto']) || !isset($data['cantidad'])) {
        echo json_encode(["success" => false, "message" => "Datos incompletos para la venta"]);
        return;
    }

    $idProducto = $data['idProducto'];
    $cantidad = $data['cantidad'];

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("SELECT queda, venta, precio_venta, monto FROM productos WHERE id = :id");
        $stmt->bindValue(':id', $idProducto);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto || $producto['queda'] < $cantidad) {
            $conn->rollBack();
            echo json_encode(["success" => false, "message" => "No hay suficiente stock disponible"]);
            return;
        }

        $montoVenta = $cantidad * $producto['precio_venta'];

        $stmt = $conn->prepare("UPDATE productos SET queda = queda - :cantidad, venta = venta + :cantidad, monto = monto + :montoVenta WHERE id = :id");
        $stmt->bindValue(':cantidad', $cantidad);
        $stmt->bindValue(':montoVenta', $montoVenta);
        $stmt->bindValue(':id', $idProducto);
        $stmt->execute();

        $stmtVenta = $conn->prepare("INSERT INTO ventas (producto_id, cantidad_vendida, fecha_venta, monto_venta) VALUES (:idProducto, :cantidad, NOW(), :montoVenta)");
        $stmtVenta->bindValue(':idProducto', $idProducto);
        $stmtVenta->bindValue(':cantidad', $cantidad);
        $stmtVenta->bindValue(':montoVenta', $montoVenta);
        $stmtVenta->execute();

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Venta registrada y monto actualizado correctamente"]);

    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(["success" => false, "message" => "Error al registrar la venta: " . $e->getMessage()]);
    }
}
function obtenerHistorial($idProducto) {
    global $conn;

    $sql = "SELECT id, cantidad_vendida, fecha_venta, monto_venta FROM ventas WHERE producto_id = :idProducto ORDER BY fecha_venta DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':idProducto', $idProducto);
    $stmt->execute();
    
    $ventas = [];
    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ventas[] = $fila;
    }

    echo json_encode($ventas);
}

function eliminarVenta($id_venta) {
    global $conn;

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("SELECT producto_id, cantidad_vendida, monto_venta FROM ventas WHERE id = :id_venta");
        $stmt->bindValue(':id_venta', $id_venta);
        $stmt->execute();
        $venta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$venta) {
            $conn->rollBack();
            echo json_encode(["success" => false, "message" => "Venta no encontrada"]);
            return;
        }

        $id_producto = $venta['producto_id'];
        $cantidad = $venta['cantidad_vendida'];
        $monto = $venta['monto_venta'];

        $stmt = $conn->prepare("DELETE FROM ventas WHERE id = :id_venta");
        $stmt->bindValue(':id_venta', $id_venta);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE productos SET queda = queda + :cantidad, venta = venta - :cantidad, monto = monto - :monto WHERE id = :id_producto");
        $stmt->bindValue(':cantidad', $cantidad);
        $stmt->bindValue(':monto', $monto);
        $stmt->bindValue(':id_producto', $id_producto);
        $stmt->execute();

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Venta eliminada y stock revertido correctamente"]);

    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(["success" => false, "message" => "Error al eliminar la venta: " . $e->getMessage()]);
    }
}