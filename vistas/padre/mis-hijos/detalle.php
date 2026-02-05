<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/CampistaControlador.php';
require_once RUTA_MODELOS . '/Padre.php';

// 1. Seguridad: Solo padres con sesión activa
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_PADRE);

$datos_usuario = Sesion::obtenerDatosUsuario();
$controlador = new CampistaControlador();

// 2. Obtener ID del padre logueado para validación de seguridad
$padre_modelo = new Padre();
$padre_modelo->id_usuario = $datos_usuario['id'];
$padre_modelo->leerPorIdUsuario();
$id_padre_sesion = $padre_modelo->id_padre;

// 3. Validar ID del campista
if (!isset($_GET['id']) || !es_numero_valido($_GET['id'])) {
    header('Location: ' . URL_BASE . '/vistas/padre/dashboard.php');
    exit();
}

$id_campista = (int)$_GET['id'];
$hijo = $controlador->obtenerPorId($id_campista);

// 4. VALIDACIÓN DE PERMISOS: Solo el padre del niño puede ver este detalle
if (!$hijo || $hijo['id_padre'] != $id_padre_sesion) {
    Sesion::establecerMensaje('error', 'No tienes permiso para ver esta información.');
    header('Location: ' . URL_BASE . '/vistas/padre/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Campista - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; color: #333; }
        .header { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .profile-card { background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .profile-header { background: #f8f9fa; padding: 30px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 20px; }
        .profile-avatar { width: 80px; height: 80px; background: #48bb78; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; }
        .profile-info { padding: 30px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .info-group label { display: block; font-size: 0.85rem; color: #718096; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; }
        .info-group p { font-size: 1.1rem; color: #2d3748; font-weight: 500; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; font-weight: bold; }
        .status-aprobado { background: #c6f6d5; color: #22543d; }
        .status-pendiente { background: #feebc8; color: #744210; }
        .medical-notes { margin-top: 30px; padding: 20px; background: #fff5f5; border-left: 4px solid #f56565; border-radius: 8px; }
        .btn-group { padding: 20px 30px; background: #f8f9fa; display: flex; gap: 15px; }
        .btn { padding: 12px 25px; border-radius: 8px; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .btn-edit { background: #38a169; color: white; }
        .btn-back { background: #e2e8f0; color: #4a5568; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔍 Ficha de Información</h1>
        <a href="<?php echo URL_BASE; ?>/vistas/padre/dashboard.php" style="color: white; text-decoration: none;">← Volver</a>
    </div>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">👦</div>
                <div>
                    <h2 style="font-size: 1.8rem;"><?php echo htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']); ?></h2>
                    <span class="status-badge status-<?php echo $hijo['estado_inscripcion']; ?>">
                        <?php echo ucfirst($hijo['estado_inscripcion']); ?>
                    </span>
                </div>
            </div>

            <div class="profile-info">
                <div class="info-grid">
                    <div class="info-group">
                        <label>Fecha de Nacimiento</label>
                        <p><?php echo date('d/m/Y', strtotime($hijo['fecha_nacimiento'])); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Edad Actual</label>
                        <p><?php echo $hijo['edad']; ?> años</p>
                    </div>
                    <div class="info-group">
                        <label>Género</label>
                        <p><?php echo ucfirst($hijo['genero']); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Año de Inscripción</label>
                        <p><?php echo $hijo['anio_inscripcion']; ?></p>
                    </div>
                </div>

                <div class="medical-notes">
                    <label style="color: #c53030; font-weight: bold; display: block; margin-bottom: 10px;">📋 Notas Médicas y Observaciones</label>
                    <p style="line-height: 1.6;">
                        <?php echo !empty($hijo['notas_especiales']) ? nl2br(htmlspecialchars($hijo['notas_especiales'])) : 'No se han registrado observaciones médicas o notas especiales para este campista.'; ?>
                    </p>
                </div>
            </div>

            <div class="btn-group">
                <a href="editar.php?id=<?php echo $id_campista; ?>" class="btn btn-edit">✏️ Editar Información</a>
                <a href="<?php echo URL_BASE; ?>/vistas/padre/dashboard.php" class="btn btn-back">Volver al Panel</a>
            </div>
        </div>
    </div>
</body>
</html>