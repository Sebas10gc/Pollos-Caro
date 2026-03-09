<?php
require_once __DIR__ . '/includes/functions.php';

$numero_pedido = $_GET['pedido'] ?? '';

if (!$numero_pedido) {
    die('Pedido no encontrado');
}

$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE numero_pedido = ?");
$stmt->execute([$numero_pedido]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    die('Pedido no encontrado');
}

$stmt = $pdo->prepare("SELECT * FROM detalles_pedido WHERE pedido_id = ?");
$stmt->execute([$pedido['id']]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura Pública - Pollos Caro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .factura-publica {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #c41e1e;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #c41e1e;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background: #c41e1e;
            color: white;
            padding: 10px;
            font-size: 14px;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .total {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #c41e1e;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #ddd;
            color: #666;
            font-size: 12px;
        }
        .badge {
            background: #27ae60;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="factura-publica">
        <div class="badge">
            <i class="fas fa-check-circle"></i> FACTURA VÁLIDA
        </div>
        
        <div class="header">
            <h1>POLLOS AL SPIEDO "CARO"</h1>
            <p><i class="fas fa-map-marker-alt"></i> Villa Celina, Carriego 1023</p>
            <p><i class="fas fa-phone"></i> 11-5053-1202</p>
        </div>

        <div class="info">
            <p><strong>Pedido:</strong> <?= $numero_pedido ?></p>
            <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></p>
            <p><strong>Tipo:</strong> <?= $pedido['tipo_pedido'] == 'local' ? 'Servir aquí' : 'Para llevar' ?></p>
            <?php if ($pedido['nombre_cliente']): ?>
                <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['nombre_cliente']) ?></p>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cant</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                <tr>
                    <td><?= htmlspecialchars($detalle['producto_nombre']) ?></td>
                    <td><?= $detalle['cantidad'] ?></td>
                    <td>$<?= number_format($detalle['precio_unitario'], 0, ',', '.') ?></td>
                    <td>$<?= number_format($detalle['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total">
            TOTAL: $<?= number_format($pedido['total'], 0, ',', '.') ?>
        </div>

        <div class="footer">
            <p>¡Gracias por su compra!</p>
            <p>Esta factura es válida digitalmente</p>
            <p style="font-size: 10px; margin-top: 10px;"><?= date('d/m/Y H:i:s') ?></p>
        </div>
    </div>
</body>
</html>