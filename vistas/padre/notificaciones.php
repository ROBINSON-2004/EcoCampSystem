<?php
/**
 * ============================================
 * VISTA: NOTIFICACIONES DEL PADRE - EcoCampSystem
 * ============================================
 */
require_once __DIR__ . '/../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_MODELOS . '/Notificacion.php';

Sesion::iniciar();
// Seguridad: Solo los padres pueden acceder a esta vista
Sesion::requerirTipoUsuario(TIPO_PADRE);

$id_usuario = $_SESSION['usuario_id'];
$modelo = new Notificacion();

/**
 * Obtenemos las notificaciones. 
 * El modelo ya usa 'u.nombre' para evitar el error de SQL
 */
$notificaciones = $modelo->obtenerPorUsuario($id_usuario);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones | <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="container">
    <a href="<?php echo URL_BASE; ?>/panel.php" class="btn-back"><span>←</span> Volver</a>
    
    <div class="header-section">
        <h1>🔔 Mis Notificaciones</h1>
        <p>Centro de avisos para padres de familia de **EcoCampSystem**.</p>
    </div>

    <?php if (empty($notificaciones)): ?>
        <div class="notif-card" style="text-align: center; border-left: none;">
            <p style="color: #a0aec0;">No tienes avisos nuevos por ahora.</p>
        </div>
    <?php else: ?>
        <?php foreach ($notificaciones as $n): ?>
            <div class="notif-card <?php echo $n['leida'] == 0 ? 'notif-unread' : ''; ?>">
                <div class="notif-header">
                    <span class="notif-date">📅 <?php echo date('d/m/Y H:i', strtotime($n['fecha_envio'])); ?></span>
                    <?php if ($n['leida'] == 0): ?>
                        <span class="badge-new">NUEVO</span>
                    <?php endif; ?>
                </div>

                <h3 class="notif-title"><?php echo htmlspecialchars($n['titulo']); ?></h3>
                <p class="notif-body"><?php echo nl2br(htmlspecialchars($n['mensaje'])); ?></p>

                <div class="notif-footer">
                    <span class="notif-sender">Remitente: <?php echo htmlspecialchars($n['remitente_nombre']); ?></span>
                    <?php if ($n['leida'] == 0): ?>
                        <a href="marcar_leida.php?id=<?php echo $n['id_notificacion_usuario']; ?>" class="btn-leer">Marcar como leída</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>