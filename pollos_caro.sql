CREATE DATABASE IF NOT EXISTS pollos_caro;
USE pollos_caro;

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio_pequeno DECIMAL(10,2),
    precio_grande DECIMAL(10,2),
    categoria_id INT,
    stock INT DEFAULT 0,
    stock_minimo INT DEFAULT 5,
    unidad_medida VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    imagen VARCHAR(255),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);


CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(20) UNIQUE,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    tipo_pedido ENUM('llevar', 'local') DEFAULT 'local',
    nombre_cliente VARCHAR(200),
    nit VARCHAR(50),
    total DECIMAL(10,2),
    metodo_pago ENUM('efectivo', 'tarjeta', 'qr') DEFAULT 'efectivo',  
    estado ENUM('pendiente', 'preparando', 'listo', 'entregado', 'cancelado') DEFAULT 'pendiente'
);

CREATE TABLE detalles_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT,
    producto_id INT,
    producto_nombre VARCHAR(200),
    cantidad INT,
    precio_unitario DECIMAL(10,2),
    subtotal DECIMAL(10,2),
    tipo_porcion VARCHAR(50), -- 'chica', 'grande', 'media', 'entera', etc.
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

INSERT INTO categorias (nombre, descripcion) VALUES
('Pollos', 'Pollos al spiedo enteros y porciones'),
('Porciones', 'Acompañamientos y porciones'),
('Bebidas', 'Bebidas sin alcohol y con alcohol');

INSERT INTO productos (nombre, descripcion, precio_pequeno, precio_grande, categoria_id, stock, unidad_medida) VALUES
('Económico', 'Pollo económico', 7000, NULL, 1, 20, 'unidad'),
('Cuarto', 'Cuarto de pollo', 12000, NULL, 1, 15, 'unidad'),
('Medio Pollo', 'Medio pollo al spiedo', 26000, NULL, 1, 10, 'unidad'),
('Pollo Entero', 'Pollo entero al spiedo', 40000, NULL, 1, 8, 'unidad'),
('Arroz', 'Porción de arroz', 6000, 7000, 2, 30, 'porcion'),
('Fideo', 'Porción de fideo', 6000, 7000, 2, 30, 'porcion'),
('Papas Fritas', 'Porción de papas fritas', 6000, 7000, 2, 25, 'porcion'),
('Coca-Cola vidrio', 'Gaseosa Coca-Cola', 5000, NULL, 3, 150, 'unidad'),
('Coca-Cola 3L', 'Gaseosa Coca-Cola', 7000, NULL, 3, 50, 'unidad'),
('Fanta vidrio', 'Gaseosa Fanta', 6000, NULL, 3, 50, 'unidad'),
('Fanta 3L', 'Gaseosa Fanta', 7000, NULL,3, 40, 'unidad'),
('Sprite', 'Gaseosa Sprite', 6000, NULL,3, 40, 'unidad'),
('Chola de Oro', 'Cerveza Chola de Oro', 7000, NULL, 3, 30, 'unidad'),
('Sol', 'Cerveza Sol', 7000, NULL, 3, 30, 'unidad'),
('Salvietti', 'Gaseosa Salvietti', 7000, NULL, 3, 20, 'unidad'),
('Oriental', 'Gaseosa Oriental', 7000, NULL, 3, 20, 'unidad'),
('Manaos', 'Gaseosa Manaos', 4000, NULL, 3, 25, 'unidad'),
('Ades', 'Jugo Ades', 4000, NULL, 3, 15, 'unidad'),
('Levite 1,5L', 'Bebida Levite', 4000, NULL, 3, 15, 'unidad'),
('Levite 3L', 'Bebida Levite', 5000, NULL, 3, 15, 'unidad'),
('Agua', 'Agua mineral', 3000, NULL, 3, 30, 'unidad'),
('Limonada Jarra', 'Limonada en jarra', 1500, NULL, 3, 10, 'jarra'),
('Limonada Vaso', 'Limonada en vaso', 1500, NULL, 3, 20, 'vaso');

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'vendedor') DEFAULT 'vendedor',
    activo BOOLEAN DEFAULT TRUE,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insertar usuario admin por defecto (password: admin123)
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Administrador', 'admin@polloscaro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

