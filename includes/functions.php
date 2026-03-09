<?php
require_once __DIR__ . '/db.php';

function generarNumeroPedido() {
    return 'P' . date('Ymd') . rand(100, 999);
}

function obtenerProductos($categoria_id = null) {
    global $pdo;
    if ($categoria_id) {
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE categoria_id = ? AND activo = 1 ORDER BY nombre");
        $stmt->execute([$categoria_id]);
    } else {
        $stmt = $pdo->query("SELECT * FROM productos WHERE activo = 1 ORDER BY categoria_id, nombre");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function verificarStock($producto_id, $cantidad) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    return $producto && $producto['stock'] >= $cantidad;
}

function actualizarStock($producto_id, $cantidad) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
    return $stmt->execute([$cantidad, $producto_id]);
}

function crearUsuario($nombre, $email, $password, $rol = 'vendedor') {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false;
    }
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$nombre, $email, $password_hash, $rol]);
}

function generarQRData($pedido_id, $total, $fecha) {
    $data = [
        'pedido' => $pedido_id,
        'total' => $total,
        'fecha' => $fecha,
        'empresa' => 'Pollos Caro',
        'valido' => true
    ];
    return json_encode($data);
}

function getMetodoPagoTexto($metodo) {
    switch($metodo) {
        case 'efectivo':
            return ['icono' => 'fas fa-money-bill', 'texto' => 'Efectivo', 'color' => '#27ae60'];
        case 'tarjeta':
            return ['icono' => 'fas fa-credit-card', 'texto' => 'Tarjeta', 'color' => '#2980b9'];
        case 'qr':
            return ['icono' => 'fas fa-qrcode', 'texto' => 'QR', 'color' => '#c41e1e'];
        default:
            return ['icono' => 'fas fa-money-bill', 'texto' => 'Efectivo', 'color' => '#27ae60'];
    }
}
?>