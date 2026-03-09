<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_pedido = $_POST['tipo_pedido'];
    $nombre = $_POST['nombre'] ?? '';
    $nit = $_POST['nit'] ?? '';
    $metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
    $productos_json = $_POST['productos_json'];
    $total = $_POST['total'];
    
    $productos = json_decode($productos_json, true);

    $stock_ok = true;
    foreach ($productos as $item) {
        if (!verificarStock($item['id'], $item['cantidad'])) {
            $stock_ok = false;
            break;
        }
    }
    
    if (!$stock_ok) {
        header('Location: index.php?error=stock');
        exit;
    }
    
    try {
        $pdo->beginTransaction();

        $numero_pedido = generarNumeroPedido();

        $stmt = $pdo->prepare("INSERT INTO pedidos (numero_pedido, tipo_pedido, nombre_cliente, nit, total, metodo_pago, estado) VALUES (?, ?, ?, ?, ?, ?, 'pendiente')");
        $stmt->execute([$numero_pedido, $tipo_pedido, $nombre, $nit, $total, $metodo_pago]);
        $pedido_id = $pdo->lastInsertId();

        foreach ($productos as $item) {
            $precio = $item['precio'];
            if (isset($item['tipo_porcion'])) {
                $stmt = $pdo->prepare("SELECT precio_pequeno, precio_grande FROM productos WHERE id = ?");
                $stmt->execute([$item['id']]);
                $producto_info = $stmt->fetch(PDO::FETCH_ASSOC);
                $precio = ($item['tipo_porcion'] == 'chica') ? $producto_info['precio_pequeno'] : $producto_info['precio_grande'];
            }
            
            $subtotal = $precio * $item['cantidad'];
            
            $stmt = $pdo->prepare("INSERT INTO detalles_pedido (pedido_id, producto_id, producto_nombre, cantidad, precio_unitario, subtotal, tipo_porcion) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$pedido_id, $item['id'], $item['nombre'], $item['cantidad'], $precio, $subtotal, $item['tipo_porcion'] ?? null]);

            actualizarStock($item['id'], $item['cantidad']);
        }
        
        $pdo->commit();

        header("Location: factura.php?pedido=$numero_pedido");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: index.php?error=pedido');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>