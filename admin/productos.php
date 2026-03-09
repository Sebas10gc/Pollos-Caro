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
        if ($_POST['accion'] == 'agregar') {
            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'] ?? '';
            $precio_pequeno = !empty($_POST['precio_pequeno']) ? $_POST['precio_pequeno'] : null;
            $precio_grande = !empty($_POST['precio_grande']) ? $_POST['precio_grande'] : null;
            $categoria_id = $_POST['categoria_id'];
            $stock = $_POST['stock'];
            $stock_minimo = $_POST['stock_minimo'];
            $unidad_medida = $_POST['unidad_medida'];
            
            try {
                $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio_pequeno, precio_grande, categoria_id, stock, stock_minimo, unidad_medida) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $descripcion, $precio_pequeno, $precio_grande, $categoria_id, $stock, $stock_minimo, $unidad_medida]);
                $mensaje = "Producto agregado correctamente";
            } catch (Exception $e) {
                $error = "Error al agregar producto: " . $e->getMessage();
            }
        }

        if ($_POST['accion'] == 'editar') {
            $id = $_POST['id'];
            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'] ?? '';
            $precio_pequeno = !empty($_POST['precio_pequeno']) ? $_POST['precio_pequeno'] : null;
            $precio_grande = !empty($_POST['precio_grande']) ? $_POST['precio_grande'] : null;
            $categoria_id = $_POST['categoria_id'];
            $stock = $_POST['stock'];
            $stock_minimo = $_POST['stock_minimo'];
            $unidad_medida = $_POST['unidad_medida'];
            $activo = isset($_POST['activo']) ? 1 : 0;
            
            try {
                $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio_pequeno = ?, precio_grande = ?, categoria_id = ?, stock = ?, stock_minimo = ?, unidad_medida = ?, activo = ? WHERE id = ?");
                $stmt->execute([$nombre, $descripcion, $precio_pequeno, $precio_grande, $categoria_id, $stock, $stock_minimo, $unidad_medida, $activo, $id]);
                $mensaje = "Producto actualizado correctamente";
            } catch (Exception $e) {
                $error = "Error al actualizar producto: " . $e->getMessage();
            }
        }

        if ($_POST['accion'] == 'eliminar') {
            $id = $_POST['id'];
            try {
                $stmt = $pdo->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
                $stmt->execute([$id]);
                $mensaje = "Producto desactivado correctamente";
            } catch (Exception $e) {
                $error = "Error al desactivar producto: " . $e->getMessage();
            }
        }

        if ($_POST['accion'] == 'activar') {
            $id = $_POST['id'];
            try {
                $stmt = $pdo->prepare("UPDATE productos SET activo = 1 WHERE id = ?");
                $stmt->execute([$id]);
                $mensaje = "Producto activado correctamente";
            } catch (Exception $e) {
                $error = "Error al activar producto: " . $e->getMessage();
            }
        }
    }
}

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

$productos = $pdo->query("
    SELECT p.*, c.nombre as categoria_nombre 
    FROM productos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    ORDER BY p.categoria_id, p.nombre
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Pollos Caro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .mensaje {
            background-color: #000000;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .productos-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .productos-table th,
        .productos-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .productos-table th {
            background-color: #c41e1e;
            color: white;
        }
        
        .productos-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .stock-bajo {
            background-color: #fff3cd;
            color: #856404;
            font-weight: bold;
        }
        
        .stock-cero {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
        }
        
        .btn-editar,
        .btn-eliminar,
        .btn-activar {
            padding: 5px 10px;
            margin: 0 2px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
        }
        
        .btn-editar {
            background-color: #007bff;
            color: white;
        }
        
        .btn-eliminar {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-activar {
            background-color: #28a745;
            color: white;
        }
        
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .form-group {
            margin-bottom: 15px;
            color: #000;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .btn-guardar {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-guardar:hover {
            background-color: #218838;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 50px auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #c41e1e;
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
        <div class="admin-container">
            <?php if ($mensaje): ?>
                <div class="mensaje"><?= $mensaje ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <h2>Agregar Nuevo Producto</h2>
                <form method="POST" class="form-grid">
                    <input type="hidden" name="accion" value="agregar">
                    
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto *</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion"></textarea>

                    </div>
                    
                    <div class="form-group">
                        <label for="categoria_id">Categoría *</label>
                        <select id="categoria_id" name="categoria_id" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>"><?= $categoria['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="precio_pequeno">Precio CH / Pequeño</label>
                        <input type="number" id="precio_pequeno" name="precio_pequeno" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="precio_grande">Precio GR / Grande</label>
                        <input type="number" id="precio_grande" name="precio_grande" step="0.01" min="0">
                    </div>

                    <div class="form-group">
                        <label for="stock">Stock Inicial *</label>
                        <input type="number" id="stock" name="stock" required min="0" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_minimo">Stock Mínimo *</label>
                        <input type="number" id="stock_minimo" name="stock_minimo" required min="0" value="5">
                    </div>
                    
                    <div class="form-group">
                        <label for="unidad_medida">Unidad de Medida *</label>
                        <select id="unidad_medida" name="unidad_medida" required>
                            <option value="unidad">Unidad</option>
                            <option value="porcion">Porción</option>
                            <option value="jarra">Jarra</option>
                            <option value="vaso">Vaso</option>
                            <option value="litro">Litro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-guardar">Agregar Producto</button>
                    </div>
                </form>
            </div>
            
            <h2>Productos Existentes</h2>
            <table class="productos-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precios</th>
                        <th>Stock</th>
                        <th>Stock Mínimo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): 
                        $stock_class = '';
                        if ($producto['stock'] <= 0) {
                            $stock_class = 'stock-cero';
                        } elseif ($producto['stock'] <= $producto['stock_minimo']) {
                            $stock_class = 'stock-bajo';
                        }
                    ?>
                    <tr>
                        <td><?= $producto['id'] ?></td>
                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td><?= $producto['categoria_nombre'] ?></td>
                        <td>
                            <?php if ($producto['precio_pequeno']): ?>CH: $<?= number_format($producto['precio_pequeno'], 0, ',', '.') ?><br><?php endif; ?>
                            <?php if ($producto['precio_grande']): ?>GR: $<?= number_format($producto['precio_grande'], 0, ',', '.') ?><?php endif; ?>
                        </td>
                        <td class="<?= $stock_class ?>"><?= $producto['stock'] ?> <?= $producto['unidad_medida'] ?></td>
                        <td><?= $producto['stock_minimo'] ?></td>
                        <td>
                            <?php if ($producto['activo']): ?>
                                <span style="color: green;">Activo</span>
                            <?php else: ?>
                                <span style="color: red;">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick="editarProducto(<?= htmlspecialchars(json_encode($producto)) ?>)" class="btn-editar">Editar</button>
                            
                            <?php if ($producto['activo']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('¿Desactivar este producto?')">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= $producto['id'] ?>">
                                    <button type="submit" class="btn-eliminar">Desactivar</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="accion" value="activar">
                                    <input type="hidden" name="id" value="<?= $producto['id'] ?>">
                                    <button type="submit" class="btn-activar">Activar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Editar Producto</h2>
            <form method="POST" id="form-editar">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label for="edit_nombre">Nombre del Producto *</label>
                    <input type="text" id="edit_nombre" name="nombre" required>
                </div>

                <div class="form-group">
                    <label for="edit_descripcion">Descripción</label>
                    <textarea id="edit_descripcion" name="descripcion"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_categoria_id">Categoría *</label>
                    <select id="edit_categoria_id" name="categoria_id" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= $categoria['id'] ?>"><?= $categoria['nombre'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_precio_pequeno">Precio CH / Pequeño</label>
                    <input type="number" id="edit_precio_pequeno" name="precio_pequeno" step="0.01" min="0">
                </div>
                
                <div class="form-group">
                    <label for="edit_precio_grande">Precio GR / Grande</label>
                    <input type="number" id="edit_precio_grande" name="precio_grande" step="0.01" min="0">
                </div>

                <div class="form-group">
                    <label for="edit_stock">Stock *</label>
                    <input type="number" id="edit_stock" name="stock" required min="0">
                </div>
                
                <div class="form-group">
                    <label for="edit_stock_minimo">Stock Mínimo *</label>
                    <input type="number" id="edit_stock_minimo" name="stock_minimo" required min="0">
                </div>
                
                <div class="form-group">
                    <label for="edit_unidad_medida">Unidad de Medida *</label>
                    <select id="edit_unidad_medida" name="unidad_medida" required>
                        <option value="unidad">Unidad</option>
                        <option value="porcion">Porción</option>
                        <option value="jarra">Jarra</option>
                        <option value="vaso">Vaso</option>
                        <option value="litro">Litro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="edit_activo" name="activo"> Producto Activo
                    </label>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-guardar">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const modal = document.getElementById('modalEditar');
        const span = document.getElementsByClassName('close')[0];
        
        span.onclick = function() {
            modal.style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        function editarProducto(producto) {
            document.getElementById('edit_id').value = producto.id;
            document.getElementById('edit_nombre').value = producto.nombre;
            document.getElementById('edit_descripcion').value = producto.descripcion;
            document.getElementById('edit_categoria_id').value = producto.categoria_id;
            document.getElementById('edit_precio_pequeno').value = producto.precio_pequeno || '';
            document.getElementById('edit_precio_grande').value = producto.precio_grande || '';
            document.getElementById('edit_stock').value = producto.stock;
            document.getElementById('edit_stock_minimo').value = producto.stock_minimo;
            document.getElementById('edit_unidad_medida').value = producto.unidad_medida;
            document.getElementById('edit_activo').checked = producto.activo == 1;
            
            modal.style.display = 'block';
        }
    </script>
</body>
</html>