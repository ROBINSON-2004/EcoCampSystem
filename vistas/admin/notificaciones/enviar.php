<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_CONTROLADORES . '/NotificacionControlador.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// INICIALIZACIÓN CRÍTICA: Evita el Warning de la imagen 2
$mensaje_exito = ""; 
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new NotificacionControlador();
    // Validamos que los campos no estén vacíos antes de procesar
    if(!empty($_POST['titulo']) && !empty($_POST['mensaje'])) {
        $resultado = $controlador->enviarMasiva($_POST['titulo'], $_POST['mensaje']);
        if ($resultado['exito']) {
            $mensaje_exito = $resultado['mensaje'];
        } else {
            $error = $resultado['mensaje'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones Masivas | EcoCamp</title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <div class="toolbar" style="margin-top: 20px;">
            <a href="<?php echo URL_BASE; ?>/panel.php" class="btn-back"><span>←</span> Volver</a>
        </div>

        <div class="card shadow" style="max-width: 600px; margin: 20px auto; padding: 30px;">
            <h2>📢 Notificar a todos los Padres</h2>
            
            <?php if (!empty($mensaje_exito)): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    ✅ <?php echo $mensaje_exito; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    ⚠️ <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Título del Evento / Aviso</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ej: Fogata de Bienvenida" required>
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label>Mensaje Detallado</label>
                    <textarea name="mensaje" class="form-control" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn-save" style="width: 100%; margin-top: 20px; background: #4a90e2; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
                    Enviar a Todos los Padres
                </button>
            </form>
        </div>
    </div>
</body>
</html>