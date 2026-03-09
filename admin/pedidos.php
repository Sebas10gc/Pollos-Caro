<?php
// admin/pedidos.php
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Procesar cambios de estado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] == 'cambiar_estado') {
        $pedido_id = $_POST['pedido_id'];
        $nuevo_estado = $_POST['estado'];
        
        try {
            $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
            $stmt->execute([$nuevo_estado, $pedido_id]);
            $mensaje = "Estado del pedido actualizado";
        } catch (Exception $e) {
            $error = "Error al actualizar estado: " . $e->getMessage();
        }
    }
}

// Filtros
$filtro_fecha = $_GET['fecha'] ?? date('Y-m-d');
$filtro_estado = $_GET['estado'] ?? 'todos';
$filtro_tipo = $_GET['tipo'] ?? 'todos';

// Construir query con filtros
$sql = "SELECT p.*, 
               COUNT(dp.id) as total_items 
        FROM pedidos p 
        LEFT JOIN detalles_pedido dp ON p.id = dp.pedido_id 
        WHERE DATE(p.fecha) = ? ";
        
$params = [$filtro_fecha];

if ($filtro_estado != 'todos') {
    $sql .= " AND p.estado = ? ";
    $params[] = $filtro_estado;
}

if ($filtro_tipo != 'todos') {
    $sql .= " AND p.tipo_pedido = ? ";
    $params[] = $filtro_tipo;
}

$sql .= " GROUP BY p.id ORDER BY p.fecha DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - Pollos Caro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .filtros-pedidos {
            background: var(--background-card);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            border: 1px solid var(--border-color);
        }
        
        .pedidos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .pedido-card {
            background: var(--background-card);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .pedido-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border-color: var(--primary-color);
        }
        
        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .pedido-numero {
            font-size: 18px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .pedido-estado {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .estado-pendiente {
            background: rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
        }
        
        .estado-preparando {
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
        }
        
        .estado-listo {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
        }
        
        .estado-entregado {
            background: rgba(149, 165, 166, 0.2);
            color: #95a5a6;
        }
        
        .estado-cancelado {
            background: rgba(231, 76, 60, 0.2);
            color: var(--danger-color);
        }
        
        .pedido-info {
            margin-bottom: 15px;
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .pedido-info p {
            margin: 5px 0;
        }
        
        .pedido-items {
            background: var(--background-dark);
            padding: 10px;
            border-radius: 10px;
            margin: 15px 0;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .pedido-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .pedido-total {
            font-size: 20px;
            font-weight: bold;
            color: var(--primary-color);
            text-align: right;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
        }
        
        .pedido-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .pedido-actions select,
        .pedido-actions button,
        .pedido-actions a {
            padding: 8px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            text-align: center;
            flex: 1;
        }
        
        .pedido-actions select {
            background: var(--background-dark);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .btn-ver {
            background: var(--primary-color);
            color: var(--black);
        }
        
        .admin-menu {
            display: flex;
            gap: 10px;
        }
        
        .admin-menu a {
            padding: 10px 20px;
            background: var(--background-card);
            color: var(--text-primary);
            text-decoration: none;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .admin-menu a:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo-section">
                <div class="logo">
                    <img src="../assets/img/logo.png" alt="Pollos Caro" class="logo">
                </div>
                <div class="restaurant-info">
                    <h1>Gestión de Pedidos - Pollos Caro</h1>
                </div>
            </div>
            <div class="admin-menu">
                <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="productos.php"><i class="fas fa-box"></i> Productos</a>
                <a href="stock.php"><i class="fas fa-warehouse"></i> Stock</a>
                <a href="pedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a>
                <a href="../index.php"><i class="fas fa-store"></i> Tienda</a>
            </div>
        </header>

        <main>
            <?php if (isset($mensaje)): ?>
                <div class="alerta alerta-success"><?= $mensaje ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alerta alerta-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="filtros-pedidos">
                <div>
                    <label>Fecha:</label>
                    <input type="date" id="filtro-fecha" value="<?= $filtro_fecha ?>" class="admin-input" onchange="aplicarFiltros()">
                </div>
                
                <div>
                    <label>Estado:</label>
                    <select id="filtro-estado" class="admin-input" onchange="aplicarFiltros()">
                        <option value="todos" <?= $filtro_estado == 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="pendiente" <?= $filtro_estado == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="preparando" <?= $filtro_estado == 'preparando' ? 'selected' : '' ?>>Preparando</option>
                        <option value="listo" <?= $filtro_estado == 'listo' ? 'selected' : '' ?>>Listo</option>
                        <option value="entregado" <?= $filtro_estado == 'entregado' ? 'selected' : '' ?>>Entregado</option>
                        <option value="cancelado" <?= $filtro_estado == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                
                <div>
                    <label>Tipo:</label>
                    <select id="filtro-tipo" class="admin-input" onchange="aplicarFiltros()">
                        <option value="todos" <?= $filtro_tipo == 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="local" <?= $filtro_tipo == 'local' ? 'selected' : '' ?>>Servir aquí</option>
                        <option value="llevar" <?= $filtro_tipo == 'llevar' ? 'selected' : '' ?>>Para llevar</option>
                    </select>
                </div>
            </div>
            
            <div class="pedidos-grid">
                <?php foreach ($pedidos as $pedido): 
                    $stmt = $pdo->prepare("SELECT * FROM detalles_pedido WHERE pedido_id = ?");
                    $stmt->execute([$pedido['id']]);
                    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="pedido-card">
                    <div class="pedido-header">
                        <span class="pedido-numero">#<?= $pedido['numero_pedido'] ?></span>
                        <span class="pedido-estado estado-<?= $pedido['estado'] ?>">
                            <?= ucfirst($pedido['estado']) ?>
                        </span>
                    </div>
                    
                    <div class="pedido-info">
                        <p><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></p>
                        <p><i class="fas fa-<?= $pedido['tipo_pedido'] == 'local' ? 'store' : 'shopping-bag' ?>"></i> 
                            <?= $pedido['tipo_pedido'] == 'local' ? 'Servir aquí' : 'Para llevar' ?>
                        </p>
                        <?php if ($pedido['nombre_cliente']): ?>
                            <p><i class="fas fa-user"></i> <?= $pedido['nombre_cliente'] ?></p>
                        <?php endif; ?>
                        <?php if ($pedido['nit']): ?>
                            <p><i class="fas fa-id-card"></i> NIT: <?= $pedido['nit'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="pedido-info-item">
                        <strong>PAGO</strong>
                        <span>
                            <?php
                            $metodo = $pedido['metodo_pago'] ?? 'efectivo';
                            switch($metodo) {
                                case 'efectivo': echo '💵 Efectivo'; break;
                                case 'tarjeta': echo '💳 Tarjeta'; break;
                                case 'qr': echo '📱 QR'; break;
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="pedido-items">
                        <strong>Productos:</strong>
                        <?php foreach ($detalles as $detalle): ?>
                        <div class="pedido-item">
                            <span><?= $detalle['producto_nombre'] ?> 
                                <?= $detalle['tipo_porcion'] ? '(' . $detalle['tipo_porcion'] . ')' : '' ?>
                                x<?= $detalle['cantidad'] ?>
                            </span>
                            <span>$<?= number_format($detalle['subtotal'], 0, ',', '.') ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="pedido-total">
                        Total: $<?= number_format($pedido['total'], 0, ',', '.') ?>
                    </div>
                    
                    <div class="pedido-actions">
                        <form method="POST" style="flex: 2; display: flex; gap: 5px;">
                            <input type="hidden" name="accion" value="cambiar_estado">
                            <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                            <select name="estado" onchange="this.form.submit()">
                                <option value="pendiente" <?= $pedido['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="preparando" <?= $pedido['estado'] == 'preparando' ? 'selected' : '' ?>>Preparando</option>
                                <option value="listo" <?= $pedido['estado'] == 'listo' ? 'selected' : '' ?>>Listo</option>
                                <option value="entregado" <?= $pedido['estado'] == 'entregado' ? 'selected' : '' ?>>Entregado</option>
                                <option value="cancelado" <?= $pedido['estado'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                        </form>
                        <a href="../factura.php?pedido=<?= $pedido['numero_pedido'] ?>" class="btn-ver" target="_blank">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                    </div>

                </div>
                <?php endforeach; ?>
                
                <?php if (empty($pedidos)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 50px; background: var(--background-card); border-radius: 15px;">
                    <i class="fas fa-shopping-cart" style="font-size: 50px; color: var(--border-color); margin-bottom: 15px;"></i>
                    <p>No hay pedidos para esta fecha</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        function aplicarFiltros() {
            const fecha = document.getElementById('filtro-fecha').value;
            const estado = document.getElementById('filtro-estado').value;
            const tipo = document.getElementById('filtro-tipo').value;
            
            window.location.href = `pedidos.php?fecha=${fecha}&estado=${estado}&tipo=${tipo}`;
        }
    </script>
</body>
</html>