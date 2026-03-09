<?php
require_once __DIR__ . '/includes/functions.php';

echo "<h2>🔍 Diagnóstico de Usuario</h2>";

$stmt = $pdo->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ?");
$stmt->execute(['admin@polloscaro.com']);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    echo "✅ Usuario encontrado:<br>";
    echo "ID: " . $usuario['id'] . "<br>";
    echo "Nombre: " . $usuario['nombre'] . "<br>";
    echo "Email: " . $usuario['email'] . "<br>";
    echo "Rol: " . $usuario['rol'] . "<br>";
    echo "Hash almacenado: " . $usuario['password'] . "<br>";
    echo "Longitud del hash: " . strlen($usuario['password']) . " caracteres<br>";
    
    $test_password = "admin123";
    if (password_verify($test_password, $usuario['password'])) {
        echo "<span style='color: green; font-weight: bold;'>✅ La contraseña 'admin123' ES correcta</span><br>";
    } else {
        echo "<span style='color: red; font-weight: bold;'>❌ La contraseña 'admin123' NO es correcta</span><br>";
    }
} else {
    echo "❌ No se encontró el usuario admin@polloscaro.com<br>";
}

echo "<h3>📋 Todos los usuarios:</h3>";
$usuarios = $pdo->query("SELECT id, nombre, email, rol FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($usuarios);
echo "</pre>";

echo "<h3>🗄️ Estructura de la tabla usuarios:</h3>";
$estructura = $pdo->query("DESCRIBE usuarios")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($estructura);
echo "</pre>";
?>