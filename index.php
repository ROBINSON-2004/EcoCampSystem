<?php
// ============================================
// CARGA DE CONFIGURACIÓN BASE
// ============================================
require_once __DIR__ . '/config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_CONTROLADORES . '/AutenticacionControlador.php';

// ============================================
// INICIAR MOTOR DE SESIONES
// ============================================
Sesion::iniciar();

// ============================================
// VARIABLES DE MENSAJES
// ============================================
$error = '';
$exito = '';

/**
 * Función auxiliar para obtener la URL de redirección según el rol
 * Mapea el tipo de usuario a la carpeta física correspondiente en vistas/
 */
function obtenerUrlDashboard($tipo_usuario) {
    /**
     * Según tu estructura, el tipo 'administrador' (TIPO_ADMINISTRADOR) 
     * corresponde a la carpeta 'admin'.
     */
    $carpeta = ($tipo_usuario === TIPO_ADMINISTRADOR) ? 'admin' : $tipo_usuario;
    return URL_BASE . "/vistas/{$carpeta}/dashboard.php";
}

// ============================================
// VERIFICAR SI YA HAY SESIÓN ACTIVA
// Si el usuario ya está logueado, lo enviamos a su dashboard
// ============================================
if (isset($_SESSION['usuario'])) {
    $url = obtenerUrlDashboard($_SESSION['usuario']['tipo_usuario']);
    header('Location: ' . $url);
    exit;
}

// ============================================
// PROCESAR INICIO DE SESIÓN (POST)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new AutenticacionControlador();
    $resultado = $controlador->iniciarSesion($_POST);

    if ($resultado['exito']) {
        /**
         * Redirigir usando la URL generada por el controlador 
         * o la función auxiliar basada en URL_BASE.
         */
        $url_destino = isset($resultado['url_redireccion']) ? $resultado['url_redireccion'] : obtenerUrlDashboard($resultado['tipo_usuario']);
        header('Location: ' . $url_destino);
        exit;
    } else {
        $error = $resultado['mensaje'];
    }
}

// ============================================
// GESTIÓN DE MENSAJES FLASH
// ============================================
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
    <title>Iniciar Sesión | <?php echo NOMBRE_SITIO; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/estilos.css">
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <h1>🏕️ Bienvenido</h1>
            <p><?php echo NOMBRE_SITIO; ?> - Gestiona campistas, actividades y reportes de forma centralizada y eficiente.</p>
        </div>
        
        <div class="login-right">
            <div class="login-header">
                <h2>Iniciar Sesión</h2>
                <p>Ingresa tus credenciales para acceder al sistema</p>
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
                    <input type="email" id="correo" name="correo" required placeholder="ejemplo@ecocamp.com">
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