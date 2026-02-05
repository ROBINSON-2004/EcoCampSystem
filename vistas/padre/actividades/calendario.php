<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_CONTROLADORES . '/ActividadControlador.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_PADRE);

$controlador = new ActividadControlador();
// Obtenemos las actividades programadas para el campamento
$actividades_hoy = $controlador->obtenerProgramadas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actividades del Campamento - EcoCamp</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .header { background: #38a169; color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .timeline { margin-top: 20px; }
        .activity-item { background: white; border-radius: 12px; padding: 20px; margin-bottom: 15px; display: flex; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid #38a169; }
        .time-box { min-width: 120px; text-align: center; border-right: 1px solid #eee; margin-right: 20px; }
        .time-box span { display: block; font-weight: bold; color: #2d3748; }
        .details h3 { color: #2d3748; margin-bottom: 5px; }
        .details p { color: #718096; font-size: 0.9rem; }
        .badge-group { background: #ebf8ff; color: #3182ce; padding: 2px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: bold; }
        .btn-back { background: rgba(255,255,255,0.2); color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; border: 1px solid white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎯 Cronograma de Actividades</h1>
        <a href="../dashboard.php" class="btn-back">← Volver al Panel</a>
    </div>

    <div class="container">
        <h2>Próximas Actividades</h2>
        <p style="color: #666; margin-bottom: 25px;">Consulte el horario de las actividades programadas para los grupos del campamento.</p>

        <div class="timeline">
            <?php if (!empty($actividades_hoy)): ?>
                <?php foreach ($actividades_hoy as $act): ?>
                    <div class="activity-item">
                        <div class="time-box">
                            <span><?php echo date('H:i', strtotime($act['hora_inicio'])); ?></span>
                            <small style="color: #a0aec0;">a <?php echo date('H:i', strtotime($act['hora_fin'])); ?></small>
                        </div>
                        <div class="details">
                            <span class="badge-group"><?php echo htmlspecialchars($act['nombre_grupo']); ?></span>
                            <h3><?php echo htmlspecialchars($act['nombre_actividad']); ?></h3>
                            <p>📍 <strong>Lugar:</strong> <?php echo htmlspecialchars($act['ubicacion'] ?? 'Por confirmar'); ?></p>
                            <p>👤 <strong>Responsable:</strong> <?php echo htmlspecialchars(($act['responsable_nombre'] ?? 'Staff') . ' ' . ($act['responsable_apellido'] ?? 'EcoCamp')); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 50px; background: white; border-radius: 12px; color: #a0aec0;">
                    <p style="font-size: 3rem;">📅</p>
                    <p>No hay actividades programadas para mostrar en este momento.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>