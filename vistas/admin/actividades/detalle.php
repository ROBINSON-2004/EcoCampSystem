<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_MODELOS . '/Actividad.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: lista.php');
    exit;
}

$actividad = new Actividad();
$actividad->id_actividad = $id;

if (!$actividad->leerPorId()) {
    Sesion::establecerMensaje('error', 'Actividad no encontrada');
    header('Location: lista.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Actividad - EcoCamp</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; color: #333; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 30px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .info-item { margin-bottom: 15px; }
        .info-item label { display: block; font-weight: bold; color: #667eea; font-size: 0.9rem; text-transform: uppercase; }
        .info-item p { font-size: 1.1rem; margin-top: 5px; }
        .full-width { grid-column: 1 / -1; border-top: 1px solid #eee; pt: 15px; }
        .badge { display: inline-block; padding: 5px 15px; border-radius: 20px; background: #e3f2fd; color: #1565c0; font-weight: 600; }
        .btn { padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block; background: #e2e8f0; color: #475569; }
    </style>
</head>
<body>

<div class="header">
    <h1>👁️ Detalle de Actividad</h1>
    <a href="lista.php" class="btn">← Volver a la Lista</a>
</div>

<div class="container">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f0f0f0; pb: 15px;">
            <h2><?php echo htmlspecialchars($actividad->nombre_actividad ?? ''); ?></h2>
            <span class="badge"><?php echo ucfirst($actividad->tipo_actividad ?? ''); ?></span>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>📍 Ubicación</label>
                <p><?php echo htmlspecialchars($actividad->ubicacion ?? 'No especificada'); ?></p>
            </div>
            <div class="info-item">
                <label>⏱️ Duración estimada</label>
                <p><?php echo $actividad->duracion_minutos ?? 0; ?> minutos</p>
            </div>
            <div class="info-item">
                <label>👥 Capacidad Máxima</label>
                <p><?php echo $actividad->capacidad_maxima ?? 'Ilimitada'; ?> personas</p>
            </div>
            <div class="info-item">
                <label>🎂 Rango de Edad</label>
                <p><?php echo ($actividad->edad_minima ?? 0) . ' a ' . ($actividad->edad_maxima ?? 0); ?> años</p>
            </div>
            
            <div class="info-item full-width">
                <label>📝 Descripción</label>
                <p><?php echo nl2br(htmlspecialchars($actividad->descripcion ?? 'Sin descripción')); ?></p>
            </div>

            <div class="info-item full-width">
                <label>🎒 Materiales Necesarios</label>
                <p><?php echo nl2br(htmlspecialchars($actividad->materiales_necesarios ?? 'Ninguno')); ?></p>
            </div>
            
            <div class="info-item full-width">
                <label>📋 Instrucciones</label>
                <p><?php echo nl2br(htmlspecialchars($actividad->instrucciones ?? 'Sin instrucciones específicas')); ?></p>
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <a href="editar.php?id=<?php echo $id; ?>" class="btn" style="background: #667eea; color: white;">✏️ Editar Actividad</a>
        </div>
    </div>
</div>

</body>
</html>