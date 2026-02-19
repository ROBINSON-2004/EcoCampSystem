<?php
/**
 * ============================================
 * VISTA: DETALLE Y ACCIONES DEL CAMPISTA
 * EcoCampSystem 2026 - Panel Administrativo
 * ============================================
 */
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/CampistaControlador.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

$id_campista = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$controlador = new CampistaControlador();

// 1. PROCESAR CAMBIO DE ESTADO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado') {
    $nuevo_estado = $_POST['nuevo_estado'] ?? '';
    $resultado = $controlador->cambiarEstado($id_campista, $nuevo_estado);
    
    if ($resultado['exito']) {
        Sesion::establecerMensaje('exito', $resultado['mensaje']);
    } else {
        Sesion::establecerMensaje('error', $resultado['mensaje']);
    }
    header("Location: detalle.php?id=$id_campista");
    exit();
}

// 2. OBTENER DATOS ACTUALIZADOS
$campista = $controlador->obtenerPorId($id_campista);

if (!$campista) {
    Sesion::establecerMensaje('error', 'Campista no encontrado.');
    header('Location: lista.php');
    exit();
}

$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente: <?php echo htmlspecialchars(($campista['nombre'] ?? '') . ' ' . ($campista['apellido'] ?? '')); ?></title>
    <style>
        :root { --primary: #4a69bd; --success: #48bb78; --warning: #f6ad55; --danger: #f56565; --dark: #2c3e50; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f7fafc; margin: 0; color: #2d3748; }
        .header { background: var(--dark); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; overflow: hidden; border: 1px solid #e2e8f0; }
        .card-header { padding: 15px 25px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .card-body { padding: 25px; }
        .badge { padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; }
        .estado-aprobado { background: #c6f6d5; color: #22543d; }
        .estado-pendiente { background: #feebc8; color: #744210; }
        .estado-rechazado { background: #fed7d7; color: #822727; }
        .status-form { display: flex; gap: 10px; align-items: center; background: #ebf4ff; padding: 15px; border-radius: 8px; border: 1px solid #bee3f8; }
        select { padding: 8px; border-radius: 6px; border: 1px solid #a0aec0; outline: none; }
        .btn { padding: 9px 18px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn-save { background: var(--primary); color: white; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .info-label { font-size: 0.75rem; color: #718096; text-transform: uppercase; font-weight: bold; margin-bottom: 4px; display: block; }
        .info-value { font-size: 1.1rem; color: #1a202c; font-weight: 500; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #edf2f7; color: #4a5568; }
        td { padding: 12px; border-bottom: 1px solid #edf2f7; }
    </style>
</head>
<body>

<div class="header">
    <h1>📋 Expediente del Campista</h1>
    <a href="lista.php" style="color: white; text-decoration: none;">← Volver al Listado</a>
</div>

<div class="container">

    <?php if ($mensaje): ?>
        <div style="padding: 15px; background: <?php echo $mensaje['tipo'] === 'exito' ? '#c6f6d5' : '#fed7d7'; ?>; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(0,0,0,0.1);">
            <?php echo $mensaje['contenido']; ?>
        </div>
    <?php endif; ?>

    <div class="card" style="border-top: 4px solid var(--primary);">
        <div class="card-header">
            <h3>⚙️ Gestión de Inscripción</h3>
            <span class="badge estado-<?php echo $campista['estado_inscripcion'] ?? 'pendiente'; ?>">
                <?php echo strtoupper($campista['estado_inscripcion'] ?? 'pendiente'); ?>
            </span>
        </div>
        <div class="card-body">
            <form method="POST" class="status-form">
                <input type="hidden" name="accion" value="cambiar_estado">
                <label><strong>Cambiar estado:</strong></label>
                <select name="nuevo_estado">
                    <option value="pendiente" <?php echo ($campista['estado_inscripcion'] === 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="aprobado" <?php echo ($campista['estado_inscripcion'] === 'aprobado') ? 'selected' : ''; ?>>Aprobado</option>
                    <option value="rechazado" <?php echo ($campista['estado_inscripcion'] === 'rechazado') ? 'selected' : ''; ?>>Rechazado</option>
                </select>
                <button type="submit" class="btn btn-save">Actualizar</button>
            </form>
        </div>
    </div>

    <div class="info-grid">
        <div class="card">
            <div class="card-header"><h3>👤 Datos Personales</h3></div>
            <div class="card-body">
                <div style="margin-bottom: 15px;">
                    <span class="info-label">Nombre</span>
                    <span class="info-value"><?php echo htmlspecialchars(($campista['nombre'] ?? '') . ' ' . ($campista['apellido'] ?? '')); ?></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <span class="info-label">Edad / Género</span>
                    <span class="info-value">
                        <?php 
                            $edad_mostrar = ($campista['edad'] > 0) ? $campista['edad'] : calcular_edad($campista['fecha_nacimiento'] ?? '');
                            echo $edad_mostrar . ' años / ' . ucfirst($campista['genero'] ?? 'N/A'); 
                        ?>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                        <span class="info-label">Padre Responsable</span>
                        <span class="info-value"><?php echo htmlspecialchars(($campista['nombre_padre'] ?? 'No asignado') . ' ' . ($campista['apellido_padre'] ?? '')); ?></span>
                    </div>
                    <a href="../padre/dashboard.php?id=<?php echo $campista['id_padre']; ?>" class="btn" style="background: #edf2f7; color: #4a5568; font-size: 0.75rem;">Ver Perfil Padre</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>🏥 Información Médica</h3></div>
            <div class="card-body">
                <div style="margin-bottom: 15px;">
                    <span class="info-label">Tipo de Sangre</span>
                    <span class="info-value" style="color: var(--danger); font-size: 1.3rem;"><?php echo strtoupper($campista['tipo_sangre'] ?? 'S/N'); ?></span>
                </div>
                <div style="margin-bottom: 15px;">
                    <span class="info-label">Alergias</span>
                    <span class="info-value"><?php echo htmlspecialchars($campista['alergias'] ?? 'Ninguna'); ?></span>
                </div>
                <div>
                    <span class="info-label">Observaciones</span>
                    <span class="info-value"><?php echo htmlspecialchars($campista['notas_especiales'] ?? 'Sin notas'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>📞 Contactos de Emergencia</h3></div>
        <div class="card-body" style="padding: 0;">
            <table>
                <thead>
                    <tr><th>Nombre</th><th>Relación</th><th>Teléfono</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $db = (new Conexion())->obtenerConexion();
                    $stmt = $db->prepare("SELECT * FROM informacion_emergencia WHERE id_campista = ?");
                    $stmt->execute([$id_campista]);
                    $contactos = $stmt->fetchAll();
                    if ($contactos): 
                        foreach ($contactos as $con): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($con['nombre_contacto']); ?></strong></td>
                            <td><?php echo htmlspecialchars($con['parentesco']); ?></td>
                            <td><a href="tel:<?php echo $con['telefono']; ?>" style="color: var(--primary); font-weight: bold;"><?php echo $con['telefono']; ?></a></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="3" style="text-align: center; padding: 20px;">Sin contactos registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 20px; display: flex; gap: 15px; justify-content: flex-end;">
        <a href="generar_pdf.php?id=<?php echo $id_campista; ?>" target="_blank" class="btn" style="background: #718096; color: white;">🖨️ Imprimir Ficha</a>
        <a href="editar.php?id=<?php echo $id_campista; ?>" class="btn" style="background: var(--warning); color: white;">✏️ Editar Información</a>
    </div>

</div>
</body>
</html>