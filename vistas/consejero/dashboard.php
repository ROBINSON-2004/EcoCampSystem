<?php
require_once __DIR__ . '/../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_CONTROLADORES . '/NotificacionControlador.php';
require_once RUTA_CONFIG . '/conexion.php';

Sesion::iniciar();
// Asumimos que TIPO_CONSEJERO está definido en constantes [cite: 146]
Sesion::requerirTipoUsuario(TIPO_CONSEJERO);

$id_usuario = $_SESSION['usuario_id'];
$db = (new Conexion())->obtenerConexion();

// 1. Obtener el grupo asignado al consejero [cite: 35]
$stmt_g = $db->prepare("SELECT id_grupo, nombre_grupo FROM grupos WHERE id_consejero = :id_u AND estado = 'activo' LIMIT 1");
$stmt_g->execute([':id_u' => $id_usuario]);
$grupo = $stmt_g->fetch(PDO::FETCH_ASSOC);

// 2. Obtener agenda del día si tiene grupo [cite: 14]
$agenda = [];
if ($grupo) {
    $stmt_a = $db->prepare("SELECT ap.*, a.nombre_actividad, a.ubicacion 
                            FROM actividades_programadas ap
                            JOIN actividades a ON ap.id_actividad = a.id_actividad
                            WHERE ap.id_grupo = :id_g AND ap.fecha_actividad = CURDATE()
                            ORDER BY ap.hora_inicio ASC");
    $stmt_a->execute([':id_g' => $grupo['id_grupo']]);
    $agenda = $stmt_a->fetchAll(PDO::FETCH_ASSOC);
}

// 3. Obtener notificaciones sin leer 
$modelo_n = new Notificacion();
$alertas = $modelo_n->obtenerPorUsuario($id_usuario, true);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Consejero | <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/admin.css?v=<?php echo time(); ?>">
    <script>
        setTimeout(function(){ location.reload(); }, 120000);
    </script>
</head>
<body style="background: #f0f4f8;">

<div class="container">
    <header class="header" style="background: #2c5282; padding: 25px; border-radius: 0 0 15px 15px;">
        <h1 style="color: white;">🏃 Panel del Consejero</h1>
        <div class="user-info" style="color: #ebf8ff;">
            Bienvenido, <strong><?php echo $_SESSION['usuario_nombre']; ?></strong> 
            <?php if($grupo): ?> | Grupo: <span class="badge"><?php echo $grupo['nombre_grupo']; ?></span><?php endif; ?>
        </div>
    </header>

    <?php if (!empty($alertas)): ?>
        <div class="alerts-section" style="margin-top: 20px;">
            <?php foreach ($alertas as $a): ?>
                <div class="card shadow" style="border-left: 5px solid #e53e3e; margin-bottom: 15px; background: #fff5f5;">
                    <div style="display: flex; justify-content: space-between;">
                        <strong>⚠️ <?php echo htmlspecialchars($a['titulo']); ?></strong>
                        <small><?php echo date('H:i', strtotime($a['fecha_envio'])); ?></small>
                    </div>
                    <p style="font-size: 0.9rem; margin: 10px 0;"><?php echo htmlspecialchars($a['mensaje']); ?></p>
                    <button onclick="marcarLeida(<?php echo $a['id_notificacion_usuario']; ?>)" class="btn-small">Entendido</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow" style="margin-top: 30px;">
        <div class="card-header">
            <h2>📅 Mi Agenda de Hoy</h2>
        </div>
        <div class="card-body">
            <?php if ($agenda): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Actividad</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agenda as $ev): ?>
                            <tr>
                                <td><?php echo date('H:i', strtotime($ev['hora_inicio'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($ev['nombre_actividad']); ?></strong></td>
                                <td>📍 <?php echo htmlspecialchars($ev['ubicacion']); ?></td>
                                <td><span class="badge"><?php echo ucfirst($ev['estado']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #718096; padding: 20px;">No tienes actividades programadas para hoy.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function marcarLeida(id) {
    // Aquí podrías usar una llamada AJAX para marcar sin recargar
    fetch('marcar_leida.php?id=' + id).then(() => location.reload());
}
</script>

</body>
</html>