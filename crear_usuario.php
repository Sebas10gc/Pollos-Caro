<?php
require_once __DIR__ . '/includes/functions.php';

$nombre = "Administrador";
$email = "admin@polloscaro.com";
$password = "admin123";
$rol = "admin";

$password_hash = password_hash($password, PASSWORD_DEFAULT);

echo "Contraseña original: " . $password . "<br>";
echo "Hash generado: " . $password_hash . "<br>";

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario_existente = $stmt->fetch();

if ($usuario_existente) {
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
    if ($stmt->execute([$password_hash, $email])) {
        echo "✅ Usuario actualizado correctamente<br>";
    } else {
        echo "❌ Error al actualizar usuario<br>";
    }
} else {
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$nombre, $email, $password_hash, $rol])) {
        echo "✅ Usuario creado correctamente<br>";
    } else {
        echo "❌ Error al crear usuario<br>";
    }
}

echo "<hr>";
echo "<h3>Prueba de verificación:</h3>";
$test_password = "admin123";
if (password_verify($test_password, $password_hash)) {
    echo "✅ La contraseña '$test_password' ES correcta<br>";
} else {
    echo "❌ La contraseña '$test_password' NO es correcta<br>";
}

echo "<hr>";
echo "<h3>Usuarios en la base de datos:</h3>";
$usuarios = $pdo->query("SELECT id, nombre, email, password, rol FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($usuarios);
echo "</pre>";
?>