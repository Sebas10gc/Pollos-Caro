<?php
// reset_admin.php
require_once __DIR__ . '/includes/functions.php';

echo "<h2>🔄 Resetear Usuario Admin</h2>";

$email = "admin@polloscaro.com";
$password = "admin123";
$nombre = "Administrador";
$rol = "admin";

$password_hash = password_hash($password, PASSWORD_DEFAULT);

echo "Contraseña original: <strong>$password</strong><br>";
echo "Hash generado: <strong>$password_hash</strong><br>";
echo "Longitud del hash: " . strlen($password_hash) . " caracteres<br>";

if (password_verify($password, $password_hash)) {
    echo "<span style='color: green;'>✅ El hash funciona correctamente</span><br>";
} else {
    echo "<span style='color: red;'>❌ El hash NO funciona (error interno)</span><br>";
}

$stmt = $pdo->prepare("DELETE FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
echo "🗑️ Usuario existente eliminado<br>";

$stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
if ($stmt->execute([$nombre, $email, $password_hash, $rol])) {
    echo "✅ Usuario creado correctamente<br>";

    $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $guardado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Hash guardado en DB: " . $guardado['password'] . "<br>";
    
    if (password_verify($password, $guardado['password'])) {
        echo "<span style='color: green; font-weight: bold;'>✅ TODO OK: La contraseña funciona con el hash guardado</span><br>";
    } else {
        echo "<span style='color: red; font-weight: bold;'>❌ ERROR: La contraseña NO funciona con el hash guardado</span><br>";
    }
} else {
    echo "❌ Error al crear usuario<br>";
}

echo "<h3>Usuarios en la base de datos:</h3>";
$usuarios = $pdo->query("SELECT id, nombre, email, rol FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($usuarios);
echo "</pre>";
?>