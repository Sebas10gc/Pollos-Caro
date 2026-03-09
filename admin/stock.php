<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}


$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        if ($_POST['accion'] == 'actualizar') {
            $id = $_POST['id'];
            $nuevo_stock = $_POST['nuevo_stock'];
            $stock_minimo = $_POST['stock_minimo'];
            
            try {
                $stmt = $pdo->prepare("UPDATE productos SET stock = ?, stock_minimo = ? WHERE id = ?");
                $stmt->execute([$nuevo_stock, $stock_minimo, $id]);
                $mensaje = "Stock actualizado correctamente";
            } catch (Exception $e) {
                $error = "Error al actualizar stock: " . $e->getMessage();
            }
        }

        if ($_POST['accion'] == 'actualizar_multiple') {
            try {
                $pdo->beginTransaction();
                foreach ($_POST['stocks'] as $id => $stock) {
                    $stock_minimo = $_POST['stocks_minimos'][$id];
                    $stmt = $pdo->prepare("UPDATE productos SET stock = ?, stock_minimo = ? WHERE id = ?");
                    $stmt->execute([$stock, $stock_minimo, $id]);
                }
                $pdo->commit();
                $mensaje = "Stocks actualizados correctamente";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Error al actualizar stocks: " . $e->getMessage();
            }
        }

        if ($_POST['accion'] == 'ajustar') {
            $id = $_POST['id'];
            $ajuste = $_POST['ajuste'];
            $motivo = $_POST['motivo'];
            
            try {
                $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id = ?");
                $stmt->execute([$id]);
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $nuevo_stock = $producto['stock'] + $ajuste;
                if ($nuevo_stock < 0) $nuevo_stock = 0;
                
                $stmt = $pdo->prepare("UPDATE productos SET stock = ? WHERE id = ?");
                $stmt->execute([$nuevo_stock, $id]);

                $mensaje = "Stock ajustado correctamente";
            } catch (Exception $e) {
                $error = "Error al ajustar stock: " . $e->getMessage();
            }
        }
    }
}

$productos = $pdo->query("
    SELECT p.*, c.nombre as categoria_nombre,
           CASE 
               WHEN p.stock <= 0 THEN 'AGOTADO'
               WHEN p.stock <= p.stock_minimo THEN 'BAJO'
               ELSE 'NORMAL'
           END as estado_stock
    FROM productos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    WHERE p.activo = 1
    ORDER BY 
        CASE 
            WHEN p.stock <= 0 THEN 1
            WHEN p.stock <= p.stock_minimo THEN 2
            ELSE 3
        END,
        p.categoria_id, 
        p.nombre
")->fetchAll(PDO::FETCH_ASSOC);

$stats = [
    'total_productos' => count($productos),
    'agotados' => $pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1 AND stock <= 0")->fetchColumn(),
    'bajo_stock' => $pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1 AND stock > 0 AND stock <= stock_minimo")->fetchColumn(),
    'stock_normal' => $pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1 AND stock > stock_minimo")->fetchColumn(),
    'valor_total_stock' => $pdo->query("
        SELECT SUM(
            CASE 
                WHEN precio_pequeno IS NOT NULL THEN precio_pequeno * stock
                WHEN precio_grande IS NOT NULL THEN precio_grande * stock
                ELSE 0
            END
        ) as total FROM productos WHERE activo = 1
    ")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Stock - Pollos Caro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        
        .stock-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #c41e1e;
        }
        
        .stat-number.warning {
            color: #f39c12;
        }
        
        .stat-number.danger {
            color: #e74c3c;
        }
        
        .stock-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .stock-table th,
        .stock-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .stock-table th {
            background-color: #c41e1e;
            color: white;
        }
        
        .estado-agotado {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
        }
        
        .estado-bajo {
            background-color: #fff3cd;
            color: #856404;
            font-weight: bold;
        }
        
        .estado-normal {
            background-color: #d4edda;
            color: #155724;
        }
        
        .stock-input {
            width: 80px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .btn-actualizar {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .btn-ajustar {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .btn-guardar-todo {
            background-color: #c41e1e;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        
        .filtros {
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filtros select,
        .filtros input {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .alertas-stock {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alerta-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ffeeba;
        }
        
        .alerta-item:last-child {
            border-bottom: none;
        }
        
        .admin-menu {
            margin-bottom: 20px;
        }
        
        .admin-menu a {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 10px;
            background-color: #c41e1e;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .admin-menu a:hover {
            background-color: #a01818;
        }
    </style>
</head>
<body>
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
        <div class="stock-container">
            <?php if ($mensaje): ?>
                <div class="mensaje"><?= $mensaje ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Productos</h3>
                    <div class="stat-number"><?= $stats['total_productos'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Productos Agotados</h3>
                    <div class="stat-number danger"><?= $stats['agotados'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Stock Bajo</h3>
                    <div class="stat-number warning"><?= $stats['bajo_stock'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Stock Normal</h3>
                    <div class="stat-number"><?= $stats['stock_normal'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Valor Total Stock</h3>
                    <div class="stat-number">$<?= number_format($stats['valor_total_stock'], 0, ',', '.') ?></div>
                </div>
            </div>

            <?php if ($stats['bajo_stock'] > 0 || $stats['agotados'] > 0): ?>
            <div class="alertas-stock">
                <h3>⚠️ Alertas de Stock</h3>
                <?php foreach ($productos as $producto): ?>
                    <?php if ($producto['stock'] <= $producto['stock_minimo']): ?>
                    <div class="alerta-item">
                        <span>
                            <strong><?= $producto['nombre'] ?></strong> 
                            (Stock: <?= $producto['stock'] ?> / Mínimo: <?= $producto['stock_minimo'] ?>)
                        </span>
                        <span class="<?= $producto['stock'] <= 0 ? 'danger' : 'warning' ?>">
                            <?= $producto['stock'] <= 0 ? '🚫 AGOTADO' : '⚠️ BAJO STOCK' ?>
                        </span>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="filtros">
                <label>Filtrar por estado:</label>
                <select id="filtro-estado" onchange="filtrarStock()">
                    <option value="todos">Todos</option>
                    <option value="agotados">Agotados</option>
                    <option value="bajo">Stock Bajo</option>
                    <option value="normal">Stock Normal</option>
                </select>
                
                <label>Buscar:</label>
                <input type="text" id="buscar" placeholder="Nombre del producto..." onkeyup="filtrarStock()">
            </div>

            <form method="POST" id="form-stock-multiple">
                <input type="hidden" name="accion" value="actualizar_multiple">
                
                <table class="stock-table" id="tabla-stock">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): 
                            $estado_class = '';
                            $estado_texto = '';
                            
                            if ($producto['stock'] <= 0) {
                                $estado_class = 'estado-agotado';
                                $estado_texto = 'AGOTADO';
                            } elseif ($producto['stock'] <= $producto['stock_minimo']) {
                                $estado_class = 'estado-bajo';
                                $estado_texto = 'BAJO STOCK';
                            } else {
                                $estado_class = 'estado-normal';
                                $estado_texto = 'NORMAL';
                            }
                        ?>
                        <tr class="producto-row" data-nombre="<?= strtolower($producto['nombre']) ?>" data-estado="<?= $estado_texto ?>">
                            <td>
                                <strong><?= htmlspecialchars($producto['nombre']) ?></strong>
                                <br>
                                <small><?= $producto['unidad_medida'] ?></small>
                            </td>
                            <td><?= $producto['categoria_nombre'] ?></td>
                            <td>
                                <input type="number" name="stocks[<?= $producto['id'] ?>]" value="<?= $producto['stock'] ?>" min="0" class="stock-input" required>
                            </td>
                            <td>
                                <input type="number" name="stocks_minimos[<?= $producto['id'] ?>]" value="<?= $producto['stock_minimo'] ?>" min="0" class="stock-input" required>
                            </td>
                            <td class="<?= $estado_class ?>"><?= $estado_texto ?></td>
                            <td>
                                <button type="button" onclick="ajustarStock(<?= $producto['id'] ?>, '<?= $producto['nombre'] ?>', <?= $producto['stock'] ?>)" class="btn-ajustar">Ajustar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <button type="submit" class="btn-guardar-todo">Guardar Todos los Cambios</button>
            </form>
        </div>
    </main>

    <div id="modalAjustar" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <span class="close">&times;</span>
            <h2>Ajustar Stock</h2>
            <form method="POST" id="form-ajustar">
                <input type="hidden" name="accion" value="ajustar">
                <input type="hidden" name="id" id="ajustar_id">
                
                <div class="form-group">
                    <label id="ajustar_producto_nombre"></label>
                </div>
                
                <div class="form-group">
                    <label for="ajuste">Cantidad a ajustar:</label>
                    <input type="number" id="ajuste" name="ajuste" required placeholder="Ej: 10 para agregar, -5 para quitar">
                    <small>Usar números positivos para agregar, negativos para quitar</small>
                </div>
                
                <div class="form-group">
                    <label for="motivo">Motivo del ajuste:</label>
                    <select id="motivo" name="motivo" required>
                        <option value="compra">Compra a proveedor</option>
                        <option value="devolucion">Devolución</option>
                        <option value="ajuste_inventario">Ajuste de inventario</option>
                        <option value="perdida">Pérdida/Desperdicio</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-guardar">Aplicar Ajuste</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const modalAjustar = document.getElementById('modalAjustar');
        const spanAjustar = modalAjustar.getElementsByClassName('close')[0];
        
        spanAjustar.onclick = function() {
            modalAjustar.style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == modalAjustar) {
                modalAjustar.style.display = 'none';
            }
        }
        
        function ajustarStock(id, nombre, stockActual) {
            document.getElementById('ajustar_id').value = id;
            document.getElementById('ajustar_producto_nombre').innerHTML = 
                `<strong>${nombre}</strong><br>Stock actual: ${stockActual}`;
            document.getElementById('ajuste').value = '';
            modalAjustar.style.display = 'block';
        }
        
        function filtrarStock() {
            const filtroEstado = document.getElementById('filtro-estado').value;
            const busqueda = document.getElementById('buscar').value.toLowerCase();
            const filas = document.querySelectorAll('.producto-row');
            
            filas.forEach(fila => {
                const nombre = fila.dataset.nombre;
                const estado = fila.dataset.estado.toLowerCase();
                let mostrar = true;

                if (busqueda && !nombre.includes(busqueda)) {
                    mostrar = false;
                }

                if (filtroEstado !== 'todos') {
                    if (filtroEstado === 'agotados' && !estado.includes('agotado')) {
                        mostrar = false;
                    } else if (filtroEstado === 'bajo' && !estado.includes('bajo')) {
                        mostrar = false;
                    } else if (filtroEstado === 'normal' && !estado.includes('normal')) {
                        mostrar = false;
                    }
                }
                
                fila.style.display = mostrar ? '' : 'none';
            });
        }

        document.getElementById('form-stock-multiple').addEventListener('submit', function(e) {
            if (!confirm('¿Guardar todos los cambios de stock?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>