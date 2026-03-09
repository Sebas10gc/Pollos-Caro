<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$total_pedidos_hoy = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha) = CURDATE()")->fetchColumn();
$productos_bajo_stock = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock <= stock_minimo")->fetchColumn();
$total_ventas_hoy = $pdo->query("SELECT SUM(total) FROM pedidos WHERE DATE(fecha) = CURDATE()")->fetchColumn();
$total_productos = $pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Pollos Caro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: var(--background-card);
            border-radius: 15px;
            padding: 20px 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border-color);
        }
        
        .admin-header h1 {
            color: var(--primary-color);
            font-size: 24px;
        }
        
        .admin-header .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: var(--text-secondary);
        }
        
        .admin-header .user-info i {
            color: var(--primary-color);
        }
        
        .admin-menu {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .admin-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
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
            transform: translateY(-2px);
        }
        
        .admin-menu a.active {
            background: var(--primary-color);
            color: var(--black);
            border-color: var(--primary-color);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--background-card);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        
        .stat-card h3 {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .admin-logout {
            background: #e74c3c !important;
            color: white !important;
            border-color: #e74c3c !important;
        }
        
        .admin-logout:hover {
            background: #c0392b !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-header">
            <div>
                <h1><i class="fas fa-crown"></i> Panel de Administración</h1>
                <p style="color: var(--text-muted); margin-top: 5px;">Bienvenido al sistema de gestión</p>
            </div>
            <div class="user-info">
                <i class="fas fa-user-circle" style="font-size: 24px;"></i>
                <div>
                    <strong style="color: var(--text-primary);"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>
                    <p style="color: var(--text-muted); font-size: 12px;"><?= $_SESSION['usuario_rol'] ?></p>
                </div>
            </div>
        </div>

        <div class="admin-menu">
            <a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="productos.php"><i class="fas fa-box"></i> Productos</a>
            <a href="stock.php"><i class="fas fa-warehouse"></i> Stock</a>
            <a href="pedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a>
            <a href="../index.php"><i class="fas fa-store"></i> Ver Tienda</a>
            <a href="../logout.php" class="admin-logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Pedidos Hoy</h3>
                <div class="stat-number"><?= $total_pedidos_hoy ?></div>
                <div class="stat-label">Pedidos realizados</div>
            </div>
            
            <div class="stat-card">
                <h3>Ventas Hoy</h3>
                <div class="stat-number">$<?= number_format($total_ventas_hoy ?: 0, 0, ',', '.') ?></div>
                <div class="stat-label">Total en ventas</div>
            </div>
            
            <div class="stat-card">
                <h3>Productos</h3>
                <div class="stat-number"><?= $total_productos ?></div>
                <div class="stat-label">Productos activos</div>
            </div>
            
            <div class="stat-card">
                <h3>Stock Bajo</h3>
                <div class="stat-number <?= $productos_bajo_stock > 0 ? 'warning' : '' ?>"><?= $productos_bajo_stock ?></div>
                <div class="stat-label">Productos por reponer</div>
            </div>
        </div>

        <div style="background: var(--background-card); border-radius: 15px; padding: 25px; border: 1px solid var(--border-color);">
            <h2 style="color: var(--text-primary); margin-bottom: 20px;">Accesos Rápidos</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <a href="productos.php" style="display: block; padding: 20px; background: var(--background-light); border-radius: 10px; text-decoration: none; color: var(--text-primary); text-align: center; border: 1px solid var(--border-color);">
                    <i class="fas fa-box" style="font-size: 30px; color: var(--primary-color); margin-bottom: 10px;"></i>
                    <h3>Gestionar Productos</h3>
                </a>
                <a href="stock.php" style="display: block; padding: 20px; background: var(--background-light); border-radius: 10px; text-decoration: none; color: var(--text-primary); text-align: center; border: 1px solid var(--border-color);">
                    <i class="fas fa-warehouse" style="font-size: 30px; color: var(--primary-color); margin-bottom: 10px;"></i>
                    <h3>Control de Stock</h3>
                </a>
                <a href="pedidos.php" style="display: block; padding: 20px; background: var(--background-light); border-radius: 10px; text-decoration: none; color: var(--text-primary); text-align: center; border: 1px solid var(--border-color);">
                    <i class="fas fa-shopping-cart" style="font-size: 30px; color: var(--primary-color); margin-bottom: 10px;"></i>
                    <h3>Ver Pedidos</h3>
                </a>
            </div>
        </div>
    </div>
</body>
</html>