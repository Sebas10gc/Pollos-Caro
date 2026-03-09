<?php
require_once __DIR__ . '/includes/functions.php';

$numero_pedido = $_GET['pedido'] ?? '';

if (!$numero_pedido) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE numero_pedido = ?");
$stmt->execute([$numero_pedido]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM detalles_pedido WHERE pedido_id = ?");
$stmt->execute([$pedido['id']]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$qr_apis = [
    'google' => "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=",
    'qrcode' => "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=",
    'goqr' => "http://goqr.me/api/?size=200x200&data="
];

$mi_ip = "192.168.56.1"; 

$protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
$base_url = $protocol . $mi_ip . '/Pollos%20Caro'; 
$factura_url = $base_url . "/factura_publica.php?pedido=" . urlencode($numero_pedido);

$encoded_url = urlencode($factura_url);

$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . $encoded_url;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura - Pollos Caro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #1a1a1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .factura-container {
            max-width: 500px;
            width: 100%;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .factura {
            padding: 20px 18px;
        }

        /* Header compacto */
        .factura-header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 2px solid #c41e1e;
        }

        .factura-header h2 {
            color: #c41e1e;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .restaurante-info {
            font-size: 11px;
            color: #000000;
            line-height: 1.4;
        }

        .restaurante-info p {
            margin: 2px 0;
        }

        .restaurante-info i {
            color: #c41e1e;
            width: 14px;
            font-size: 10px;
        }

        /* Información del pedido compacta */
        .pedido-info {
            background: #f2f2f2;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 3px solid #c41e1e;
            font-size: 12px;
        }

        .pedido-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .pedido-info-item {
            color: #000000;
        }

        .pedido-info-item strong {
            color: #c41e1e;
            font-weight: 600;
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .pedido-info-item span {
            font-size: 11px;
            font-weight: 500;
        }

        /* 🔹 NUEVO: Estilo para método de pago */
        .metodo-pago-factura {
            margin: 15px 0;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid;
        }

        /* Tabla de productos compacta */
        .productos-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .productos-tabla th {
            background: #c41e1e;
            color: white;
            padding: 8px 5px;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .productos-tabla td {
            padding: 8px 5px;
            border-bottom: 1px solid #cccccc;
            color: #000000;
        }

        .productos-tabla tbody tr:last-child td {
            border-bottom: none;
        }

        .producto-nombre {
            font-weight: 600;
        }

        .producto-detalle {
            font-size: 9px;
            color: #666666;
            margin-top: 2px;
        }

        .productos-tabla .cantidad,
        .productos-tabla .precio,
        .productos-tabla .subtotal {
            text-align: center;
        }

        .productos-tabla .subtotal {
            font-weight: 600;
            color: #000000;
        }

        /* Total compacto */
        .total-section {
            background: #f2f2f2;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: right;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
        }

        .total-label {
            color: #000000;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
        }

        .total-amount {
            color: #c41e1e;
            font-size: 18px;
            font-weight: 800;
        }

        .qr-code {
            width: 200px;
            height: 200px;
            margin: 10px auto;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f9f9f9;
            border-radius: 10px;
            padding: 10px;
        }
        
        .qr-code img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .qr-error {
            color: red;
            font-size: 11px;
            padding: 10px;
            background: #ffeeee;
            border-radius: 5px;
            margin: 10px 0;
        }

        .factura-footer {
            text-align: center;
            border-top: 1px dashed #999999;
            padding-top: 12px;
        }

        .gracias {
            color: #c41e1e;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .direccion {
            color: #000000;
            font-size: 9px;
            line-height: 1.4;
            margin: 3px 0;
        }

        .direccion i {
            color: #c41e1e;
            width: 12px;
            font-size: 8px;
        }

        .copyright {
            color: #666666;
            font-size: 7px;
            margin-top: 8px;
        }

        .botones-accion {
            display: flex;
            gap: 8px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .btn-imprimir, .btn-volver, .btn-descargar {
            flex: 1;
            min-width: 100px;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-imprimir {
            background: #c41e1e;
            color: white;
        }

        .btn-volver {
            background: #333333;
            color: white;
        }

        .btn-descargar {
            background: #27ae60;
            color: white;
        }

        .btn-imprimir:hover {
            background: #8b1515;
        }

        .btn-volver:hover {
            background: #1a1a1a;
        }

        .btn-descargar:hover {
            background: #219a52;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .factura-container {
                max-width: 100%;
                box-shadow: none;
            }

            .factura {
                padding: 10px;
            }

            .botones-accion {
                display: none;
            }

            .qr-section {
                border: 1px solid #000;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .productos-tabla th {
                background: #333333 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .pedido-info {
                background: #f5f5f5;
                border-left: 3px solid black;
            }

            .total-section {
                background: #f5f5f5;
            }

            .total-amount {
                color: black;
            }

            .metodo-pago-factura {
                border: 1px solid #000;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        @media (max-width: 400px) {
            .factura {
                padding: 15px;
            }

            .pedido-info-grid {
                grid-template-columns: 1fr;
                gap: 5px;
            }

            .productos-tabla {
                font-size: 10px;
            }

            .botones-accion {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="factura-container">
        <div class="factura">

            <div class="factura-header">
                <h2>POLLOS CARO</h2>
                <div class="restaurante-info">
                    <p><i class="fas fa-map-marker-alt"></i> Villa Celina, Carriego 1023</p>
                    <p><i class="fas fa-phone"></i> 11-5053-1202</p>
                    <p><i class="fas fa-clock"></i> 13:00 - 23:00hs</p>
                </div>
            </div>

            <div class="pedido-info">
                <div class="pedido-info-grid">
                    <div class="pedido-info-item">
                        <strong>N° PEDIDO</strong>
                        <span><?= $pedido['numero_pedido'] ?></span>
                    </div>
                    <div class="pedido-info-item">
                        <strong>FECHA</strong>
                        <span><?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></span>
                    </div>
                    <div class="pedido-info-item">
                        <strong>TIPO</strong>
                        <span><?= $pedido['tipo_pedido'] == 'local' ? '🍽️ LOCAL' : '🛵 LLEVAR' ?></span>
                    </div>
                    <?php if ($pedido['tipo_pedido'] == 'llevar'): ?>
                    <div class="pedido-info-item">
                        <strong># LLEVAR</strong>
                        <span><?= $pedido['numero_pedido'] ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($pedido['nombre_cliente']): ?>
                    <div class="pedido-info-item">
                        <strong>CLIENTE</strong>
                        <span><?= htmlspecialchars($pedido['nombre_cliente']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($pedido['nit']): ?>
                    <div class="pedido-info-item">
                        <strong>NIT</strong>
                        <span><?= htmlspecialchars($pedido['nit']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            $metodo_pago = $pedido['metodo_pago'] ?? 'efectivo';
            $icono_pago = '';
            $texto_pago = '';
            $color_pago = '';

            switch($metodo_pago) {
                case 'efectivo':
                    $icono_pago = 'fas fa-money-bill';
                    $texto_pago = 'Pago en Efectivo';
                    $color_pago = '#27ae60';
                    break;
                case 'tarjeta':
                    $icono_pago = 'fas fa-credit-card';
                    $texto_pago = 'Pago con Tarjeta';
                    $color_pago = '#2980b9';
                    break;
                case 'qr':
                    $icono_pago = 'fas fa-qrcode';
                    $texto_pago = 'Pago con QR';
                    $color_pago = '#c41e1e';
                    break;
            }
            ?>
            <div class="metodo-pago-factura" style="border-left-color: <?= $color_pago ?>;">
                <i class="<?= $icono_pago ?>" style="color: <?= $color_pago ?>; font-size: 20px; width: 30px; text-align: center;"></i>
                <div>
                    <div style="font-size: 11px; color: #666;">MÉTODO DE PAGO</div>
                    <div style="font-size: 14px; font-weight: 600; color: #000;"><?= $texto_pago ?></div>
                </div>
            </div>

            <table class="productos-tabla">
                <thead>
                    <tr>
                        <th>PRODUCTO</th>
                        <th>CANT</th>
                        <th>P/U</th>
                        <th>SUBT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $detalle): ?>
                    <tr>
                        <td>
                            <div class="producto-nombre"><?= htmlspecialchars($detalle['producto_nombre']) ?></div>
                            <?php if ($detalle['tipo_porcion']): ?>
                                <div class="producto-detalle">(<?= $detalle['tipo_porcion'] == 'chica' ? 'CH' : 'GR' ?>)</div>
                            <?php endif; ?>
                        </td>
                        <td class="cantidad"><?= $detalle['cantidad'] ?></td>
                        <td class="precio">$<?= number_format($detalle['precio_unitario'], 0, ',', '.') ?></td>
                        <td class="subtotal">$<?= number_format($detalle['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total-section">
                <div class="total-row">
                    <span class="total-label">TOTAL</span>
                    <span class="total-amount">$<?= number_format($pedido['total'], 0, ',', '.') ?></span>
                </div>
            </div>

            <div style="text-align: center; margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 10px; border: 2px dashed #c41e1e;">
                <h4 style="color: #c41e1e; margin-bottom: 10px;">
                    <i class="fas fa-qrcode"></i> CÓDIGO QR - FACTURA DIGITAL
                </h4>

                <div class="qr-code">
                    <img src="<?= $qr_url ?>" alt="QR Factura <?= $numero_pedido ?>" 
                         onerror="this.onerror=null; this.src='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($factura_url) ?>';">
                </div>
                
                <p style="font-size: 12px; margin-top: 10px;">
                    <i class="fas fa-camera"></i> Escanea para ver tu factura digital
                </p>
                <p style="font-size: 10px; color: #666;">
                    Pedido: <?= $numero_pedido ?>
                </p>
                
                <div style="font-size: 9px; background: #eee; padding: 5px; border-radius: 5px; margin-top: 10px; word-break: break-all;">
                    <small>URL: <?= $factura_url ?></small>
                </div>
            </div>

            <div class="factura-footer">
                <div class="gracias">¡GRACIAS POR TU COMPRA!</div>
                <div class="direccion">
                    <i class="fas fa-map-marker-alt"></i> Carriego 1023, Villa Celina<br>
                    <i class="fas fa-clock"></i> Todos los días 13:00 a 23:00hs
                </div>
                <div class="copyright">
                    Pollos al Spiedo "CARO" © 2026
                </div>
            </div>

            <div class="botones-accion">
                <button onclick="window.print()" class="btn-imprimir">
                    <i class="fas fa-print"></i> IMPRIMIR
                </button>
                <a href="index.php" class="btn-volver">
                    <i class="fas fa-plus"></i> NUEVO
                </a>
            </div>
        </div>
    </div>
</body>
</html>