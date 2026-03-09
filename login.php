<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor complete todos los campos';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "<!-- DEBUG: Email ingresado: $email -->\n";
        echo "<!-- DEBUG: Password ingresado: $password -->\n";
        if ($usuario) {
            echo "<!-- DEBUG: Usuario encontrado: " . $usuario['nombre'] . " -->\n";
            echo "<!-- DEBUG: Hash en DB: " . $usuario['password'] . " -->\n";
            $verify_result = password_verify($password, $usuario['password']) ? 'true' : 'false';
            echo "<!-- DEBUG: password_verify result: $verify_result -->\n";
        } else {
            echo "<!-- DEBUG: Usuario NO encontrado -->\n";
        }
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            $_SESSION['logged_in'] = true;
            
            if ($usuario['rol'] == 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Email o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pollos Caro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .login-container {
            background: #2d2d2d;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            border: 1px solid #404040;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--black);
            font-size: 40px;
            font-weight: bold;
            box-shadow: 0 10px 20px rgba(243, 156, 18, 0.2);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #b0b0b0;
            font-size: 14px;
            font-weight: 500;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #808080;
            font-size: 18px;
        }

        .input-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: #363636;
            border: 1px solid #404040;
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #f39c12;
            box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.1);
        }

        .input-group input::placeholder {
            color: #606060;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: #000;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(243, 156, 18, 0.3);
        }

        .error-message {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border: 1px solid #e74c3c;
        }

        .info-box {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            border: 1px solid #3498db;
        }

        .info-box i {
            margin-right: 5px;
        }

        .info-box p {
            margin: 5px 0;
        }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #808080;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #f39c12;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #606060;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="../assets/img/logo.png" alt="Pollos Caro" class="logo">
        </div>
        <div>
            <h1>Pollos Caro</h1>
            <p>Sistema de Gestión y Ventas</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="admin@polloscaro.com" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Contraseña
                </label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>

        <div class="links">
            <a href="index.php"><i class="fas fa-store"></i> Volver a la tienda</a>
        </div>

        <div class="footer">
            &copy; 2026 Pollos Caro. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>