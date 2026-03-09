<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
$productos_pollos = obtenerProductos(1);
$productos_porciones = obtenerProductos(2);
$productos_bebidas = obtenerProductos(3);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pollos Caro - Sistema de Ventas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .navbar {
            background: var(--background-card);
            border-radius: 15px;
            padding: 15px 25px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border-color);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .nav-brand i {
            font-size: 24px;
        }
        
        .nav-menu {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--background-light);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-item:hover {
            background: var(--primary-color);
            color: var(--black);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .nav-item i {
            font-size: 16px;
        }
        
        .nav-item.admin {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--black);
            border: none;
        }
        
        .nav-item.admin:hover {
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.3);
        }

        .nav-item.logout {
            background: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }

        .nav-item.logout:hover {
            background: #c0392b;
        }

        .nav-item.login {
            background: var(--primary-color);
            color: var(--black);
            border-color: var(--primary-color);
        }
        
        .btn-volver-arriba {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            color: var(--black);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .btn-volver-arriba:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(243, 156, 18, 0.4);
        }
        
        .btn-volver-arriba.show {
            display: flex;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-menu {
                justify-content: center;
            }
            
            .btn-volver-arriba {
                bottom: 20px;
                right: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="nav-brand">
                <i class="fas fa-drumstick-bite"></i>
                <span>POLLOS CARO</span>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    Inicio
                </a>
                <a href="#pollos" class="nav-item">
                    <i class="fas fa-drumstick-bite"></i>
                    Pollos
                </a>
                <a href="#porciones" class="nav-item">
                    <i class="fas fa-utensils"></i>
                    Porciones
                </a>
                <a href="#bebidas" class="nav-item">
                    <i class="fas fa-wine-bottle"></i>
                    Bebidas
                </a>
                
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <div class="nav-item" style="background: transparent; border-color: #f39c12; cursor: default;">
                        <i class="fas fa-user"></i>
                        <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                        <small style="color: #f39c12; margin-left: 5px;">(<?= $_SESSION['usuario_rol'] ?>)</small>
                    </div>
                    
                    <?php if ($_SESSION['usuario_rol'] == 'admin'): ?>
                        <a href="admin/index.php" class="nav-item admin">
                            <i class="fas fa-cog"></i>
                            Admin
                        </a>
                    <?php endif; ?>
                    
                    <a href="logout.php" class="nav-item logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Salir
                    </a>
                <?php else: ?>
                    <a href="login.php" class="nav-item login">
                        <i class="fas fa-sign-in-alt"></i>
                        Iniciar Sesión
                    </a>
                    <a href="admin/index.php" class="nav-item admin">
                        <i class="fas fa-cog"></i>
                        Admin
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </div>

    <div class="container">
        <header class="header">
            <div class="logo-section">
                <div class="logo">
                    <img src="../assets/img/logo.png" alt="Pollos Caro" class="logo">
                </div>
                <div class="restaurant-info">
                    <h1>Pollos al Spiedo "CARO"</h1>
                    <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                        <p style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-store"></i>
                            <span>Restaurante</span>
                        </p>
                        <p style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-clock"></i>
                            <span>Todos los días de 13:00 - 23:00hs</span>
                        </p>
                        <p style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Villa Celina, Carriego 1023</span>
                        </p>
                        <p style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Tongui, Bragado y Campo Amor <small>(consultar horario)</small></span>
                        </p>
                        <p style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-motorcycle"></i>
                            <span>Delivery a toda Villa Celina - <strong>11-5053-1202</strong></span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="contact-info">
                <i class="fas fa-phone-alt"></i>
                11-5053-1202
            </div>
        </header>

        <div class="tipo-pedido">
            <h3>¿Cómo deseas tu pedido?</h3>
            <div class="tipo-options">
                <div class="tipo-option">
                    <input type="radio" name="tipo_pedido" id="local" value="local" checked>
                    <label for="local">
                        <i class="fas fa-store"></i>
                        <span>Para servir aquí</span>
                        <small>Mesa</small>
                    </label>
                </div>
                <div class="tipo-option">
                    <input type="radio" name="tipo_pedido" id="llevar" value="llevar">
                    <label for="llevar">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Para llevar</span>
                        <small>Delivery</small>
                    </label>
                </div>
            </div>
        </div>

        <div class="main-grid">
            <div class="productos-section">
                <h2 class="section-title">Selecciona tus Productos</h2>

                <div class="categoria" id="pollos">
                    <h3>Pollos</h3>
                    <div class="productos-grid">
                        <?php foreach ($productos_pollos as $producto): ?>
                        <div class="producto-card" data-id="<?= $producto['id'] ?>" 
                             data-nombre="<?= $producto['nombre'] ?>" 
                             data-precio="<?= $producto['precio_pequeno'] ?>" 
                             data-stock="<?= $producto['stock'] ?>">
                            <h4 class="producto-nombre"><?= $producto['nombre'] ?></h4>
                            <p class="producto-descripcion"><?= $producto['descripcion'] ?: 'Delicioso pollo al spiedo' ?></p>
                            <div class="producto-precio">
                                $. <?= number_format($producto['precio_pequeno'], 2, ',', '.') ?>
                            </div>
                            <div class="producto-stock <?= $producto['stock'] <= 0 ? 'stock-agotado' : ($producto['stock'] <= $producto['stock_minimo'] ? 'stock-bajo' : 'stock-normal') ?>">
                                <i class="fas fa-box"></i> Stock: <?= $producto['stock'] ?>
                            </div>
                            <div class="producto-actions">
                                <button class="btn-cantidad" onclick="cambiarCantidad(this, -1)" <?= $producto['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="cantidad-input" value="0" min="0" max="<?= $producto['stock'] ?>" readonly>
                                <button class="btn-cantidad" onclick="cambiarCantidad(this, 1)" <?= $producto['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="categoria" id="porciones">
                    <h3>Porciones</h3>
                    <div class="productos-grid">
                        <?php foreach ($productos_porciones as $producto): ?>
                        <div class="producto-card" data-id="<?= $producto['id'] ?>" 
                             data-nombre="<?= $producto['nombre'] ?>" 
                             data-precio-ch="<?= $producto['precio_pequeno'] ?>" 
                             data-precio-gr="<?= $producto['precio_grande'] ?>" 
                             data-stock="<?= $producto['stock'] ?>">
                            <h4 class="producto-nombre"><?= $producto['nombre'] ?></h4>
                            <p class="producto-descripcion"><?= $producto['descripcion'] ?: 'Porción deliciosa' ?></p>
                            <div class="producto-precio">
                                <small>CH:</small> $. <?= number_format($producto['precio_pequeno'], 2, ',', '.') ?> 
                                <small>GR:</small> $. <?= number_format($producto['precio_grande'], 2, ',', '.') ?>
                            </div>
                            <div class="producto-stock <?= $producto['stock'] <= 0 ? 'stock-agotado' : ($producto['stock'] <= $producto['stock_minimo'] ? 'stock-bajo' : 'stock-normal') ?>">
                                <i class="fas fa-box"></i> Stock: <?= $producto['stock'] ?>
                            </div>
                            <select class="tipo-porcion" style="margin-bottom: 10px; padding: 5px; border-radius: 5px;">
                                <option value="chica">Chica (CH)</option>
                                <option value="grande">Grande (GR)</option>
                            </select>
                            <div class="producto-actions">
                                <button class="btn-cantidad" onclick="cambiarCantidad(this, -1)" <?= $producto['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="cantidad-input" value="0" min="0" max="<?= $producto['stock'] ?>" readonly>
                                <button class="btn-cantidad" onclick="cambiarCantidad(this, 1)" <?= $producto['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="categoria" id="bebidas">
                    <h3>Bebidas</h3>
                    <div class="productos-grid">
                        <?php foreach ($productos_bebidas as $producto): ?>
                        <div class="producto-card" data-id="<?= $producto['id'] ?>" 
                             data-nombre="<?= $producto['nombre'] ?>" 
                             data-precio="<?= $producto['precio_pequeno'] ?>" 
                             data-precio-gr="<?= $producto['precio_grande'] ?>" 
                             data-stock="<?= $producto['stock'] ?>">
                            <h4 class="producto-nombre"><?= $producto['nombre'] ?></h4>
                            <p class="producto-descripcion"><?= $producto['descripcion'] ?: 'Bebida refrescante' ?></p>
                            <div class="producto-precio">
                                <?php if ($producto['precio_pequeno']): ?>
                                    $. <?= number_format($producto['precio_pequeno'], 2, ',', '.') ?>
                                <?php endif; ?>
                                <?php if ($producto['precio_grande']): ?>
                                    <small>GR:</small> $. <?= number_format($producto['precio_grande'], 2, ',', '.') ?>
                                <?php endif; ?>
                            </div>
                            <div class="producto-stock <?= $producto['stock'] <= 0 ? 'stock-agotado' : ($producto['stock'] <= $producto['stock_minimo'] ? 'stock-bajo' : 'stock-normal') ?>">
                                <i class="fas fa-box"></i> Stock: <?= $producto['stock'] ?>
                            </div>
                            <div class="producto-actions">
                                <button class="btn-cantidad" onclick="cambiarCantidad(this, -1)" <?= $producto['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="cantidad-input" value="0" min="0" max="<?= $producto['stock'] ?>" readonly>
                                <button class="btn-cantidad" onclick="cambiarCantidad(this, 1)" <?= $producto['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="carrito-section">
                <div class="carrito-header">
                    <h2><i class="fas fa-shopping-cart"></i> Tu Pedido</h2>
                       <span id="carrito-count">0</span>
                </div>

                <div id="carrito-items" class="carrito-items">
                    <div class="carrito-vacio">
                        <i class="fas fa-shopping-basket"></i>
                        <p>No hay productos seleccionados</p>
                    </div>
                </div>

                <div class="carrito-totales">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">$. 0.00</span>
                    </div>
                    <div class="gran-total">
                        <span>Total:</span>
                        <span id="total">$. 0.00</span>
                    </div>
                </div>

                <div class="metodo-pago" style="margin: 15px 0; padding: 15px; background: #f5f5f5; border-radius: 10px;">
                    <h4 style="color: #c41e1e; margin-bottom: 10px; font-size: 14px;">
                        <i class="fas fa-credit-card"></i> Método de Pago
                    </h4>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                            <input type="radio" name="metodo_pago" value="efectivo" checked>
                            <i class="fas fa-money-bill" style="color: #27ae60;"></i>
                            <span>Efectivo</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                            <input type="radio" name="metodo_pago" value="tarjeta">
                            <i class="fas fa-credit-card" style="color: #2980b9;"></i>
                            <span>Tarjeta</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                            <input type="radio" name="metodo_pago" value="qr">
                            <i class="fas fa-qrcode" style="color: #c41e1e;"></i>
                            <span>QR</span>
                        </label>
                    </div>
                </div>

                <form action="pedido.php" method="POST" id="form-pedido">
                    <input type="hidden" name="tipo_pedido" id="tipo-pedido-input" value="local">
                    <input type="hidden" name="productos_json" id="productos-json">
                    <input type="hidden" name="total" id="total-input">
                    <input type="hidden" name="metodo_pago" id="metodo-pago-input" value="efectivo">
        
                    <div class="cliente-info">
                        <input type="text" name="nombre" id="nombre" placeholder="Nombre (opcional)">
                        <input type="text" name="nit" id="nit" placeholder="NIT (opcional)">
                    </div>

                    <button type="submit" class="btn-confirmar" id="btn-confirmar" disabled>
                        Confirmar Pedido
                    </button>
                </form>

                <div id="stock-messages" class="stock-message" style="display: none;"></div>
            </div>

    <button class="btn-volver-arriba" id="btnVolverArriba" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="assets/js/script.js"></script>
    <script>
        window.onscroll = function() {
            const btn = document.getElementById('btnVolverArriba');
            if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
                btn.classList.add('show');
            } else {
                btn.classList.remove('show');
            }
        };

        document.querySelectorAll('.nav-item[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        document.querySelectorAll('input[name="metodo_pago"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('metodo-pago-input').value = this.value;
            });
        });
    </script>
</body>
</html>