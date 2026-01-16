<?php
require_once __DIR__ . '/config/constantes.php';
require_once __DIR__ . '/utilidades/sesion.php';
require_once __DIR__ . '/controladores/AutenticacionControlador.php';

// Si ya hay sesión activa, redirigir al panel
Sesion::iniciar();
if (Sesion::estaActiva()) {
    header('Location: panel.php');
    exit();
}

// Procesar formulario de login
$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new AutenticacionControlador();
    $resultado = $controlador->iniciarSesion($_POST);
    
    if ($resultado['exito']) {
        header('Location: panel.php');
        exit();
    } else {
        $error = $resultado['mensaje'];
    }
}

// Obtener mensaje flash si existe
$mensaje_flash = Sesion::obtenerMensaje();
if ($mensaje_flash) {
    if ($mensaje_flash['tipo'] === 'exito') {
        $exito = $mensaje_flash['contenido'];
    } else {
        $error = $mensaje_flash['contenido'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/estilos.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        
        .login-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-left h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .login-left p {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.9;
        }
        
        .login-right {
            padding: 60px 40px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-header h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        
        .checkbox-group input {
            margin-right: 8px;
        }
        
        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .register-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }
        
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
            }
            
            .login-left {
                padding: 40px 30px;
            }
            
            .login-left h1 {
                font-size: 2rem;
            }
            
            .login-right {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <h1>🏕️ Bienvenido</h1>
            <p>Sistema de Gestión de Campamento - Administra campistas, actividades, asistencia y más de forma fácil y eficiente.</p>
        </div>
        
        <div class="login-right">
            <div class="login-header">
                <h2>Iniciar Sesión</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($exito): ?>
                <div class="alert alert-success"><?php echo $exito; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" required placeholder="tu@email.com">
                </div>
                
                <div class="form-group">
                    <label for="contrasena">Contraseña</label>
                    <input type="password" id="contrasena" name="contrasena" required placeholder="••••••••">
                </div>
                
                <div class="form-options">
                    <div class="checkbox-group">
                        <input type="checkbox" id="recordar" name="recordar">
                        <label for="recordar" style="margin: 0;">Recordarme</label>
                    </div>
                    <a href="vistas/auth/recuperar-contrasena.php" class="forgot-password">¿Olvidaste tu contraseña?</a>
                </div>
                
                <button type="submit" class="btn-login">Iniciar Sesión</button>
                
                <div class="register-link">
                    ¿No tienes cuenta? <a href="vistas/auth/registro-padre.php">Regístrate aquí</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>