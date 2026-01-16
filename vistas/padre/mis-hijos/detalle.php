<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once __DIR__ . '/../../../utilidades/sesion.php';
require_once __DIR__ . '/../../../utilidades/funciones.php';
require_once __DIR__ . '/../../../controladores/CampistaControlador.php';
require_once __DIR__ . '/../../../modelos/Padre.php';

// 1. Seguridad: Solo padres
Sesion::requerirTipoUsuario(TIPO_PADRE);

$datos_usuario = Sesion::obtenerDatosUsuario();
$controlador = new CampistaControlador();

// 2. Obtener ID del padre vinculado al usuario actual
$padre_modelo = new Padre();
$padre_modelo->id_usuario = $datos_usuario['id'];
$padre_modelo->leerPorIdUsuario();
$id_padre_sesion = $padre_modelo->id_padre;

// 3. Verificar ID del campista
if (!isset($_GET['id']) || !es_numero_valido($_GET['id'])) {
    Sesion::establecerMensaje('error', 'ID de campista no válido.');
    header('Location: ../panel.php');
    exit();
}

$id_campista = (int)$_GET['id'];
$campista = $controlador->obtenerPorId($id_campista);

// 4. VALIDACIÓN DE SEGURIDAD: ¿Es este niño realmente hijo de quien consulta?
if (!$campista || $campista['id_padre'] != $id_padre_sesion) {
    Sesion::establecerMensaje('error', 'No tienes permiso para ver este perfil.');
    header('Location: ../panel.php');
    exit();
}

$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de mi Hijo - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; min-height: 100vh; }
        
        .header {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex; justify-content: space-between; align-items: center;
        }
        .header h1 { font-size: 1.5rem; }
        .header a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; transition: 0.3s; border: 1px solid rgba(255,255,255,0.3); }
        .header a:hover { background: rgba(255,255,255,0.2); }
        
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        
        .breadcrumb { color: #666; margin-bottom: 20px; font-size: 0.9rem; }
        .breadcrumb a { color: #38a169; text-decoration: none; }
        
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px; overflow: hidden; }
        
        .card-header {
            padding: 25px 30px; background: #fff; border-bottom: 2px solid #f8f9fa;
            display: flex; justify-content: space-between; align-items: center;
        }
        .card-header h2 { color: #333; font-size: 1.5rem; }
        
        .card-body { padding: 30px; }
        
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .info-item { margin-bottom: 10px; }
        .info-label { color: #888; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; margin-bottom: 5px; }
        .info-value { color: #333; font-size: 1.1rem; }

        .status-container {
            display: flex; align-items: center; gap: 10px; padding: 15px;
            background: #f0fff4; border-radius: 10px; border: 1px solid #c6f6d5; margin-bottom: 25px;
        }
        
        .estado-badge { padding: 5px 15px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .estado-aprobado { background: #c6f6d5; color: #22543d; }
        .estado-pendiente { background: #feebc8; color: #744210; }
        
        .btn { padding: 12px 25px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block; transition: 0.3s; }
        .btn-edit { background: #38a169; color: white; margin-right: 10px; }
        .btn-back { background: #edf2f7; color: #4a5568; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }

        .notes-box { background: #f8fafc; border-left: 4px solid #cbd5e0; padding: 15px; margin-top: 20px; border-radius: 0 8px 8px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔍 Ficha del Campista</h1>
        <a href="../panel.php">Volver al Panel</a>
    </div>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="../panel.php">Inicio</a> / 
            <span>Detalle de mi hijo</span>
        </div>
        
        <div class="status-container">
            <span style="font-size: 1.2rem;">📌</span>
            <div>
                <span style="color: #2f855a; font-weight: 600;">Estado de la Inscripción:</span>
                <span class="estado-badge estado-<?php echo $campista['estado_inscripcion']; ?>">
                    <?php echo strtoupper($campista['estado_inscripcion']); ?>
                </span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2><?php echo htmlspecialchars($campista['nombre'] . ' ' . $campista['apellido']); ?></h2>
                <span style="color: #cbd5e0; font-weight: bold;">ID #<?php echo $id_campista; ?></span>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Fecha de Nacimiento</div>
                        <div class="info-value"><?php echo formatear_fecha($campista['fecha_nacimiento']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Edad Actual</div>
                        <div class="info-value"><?php echo $campista['edad']; ?> años</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Género</div>
                        <div class="info-value"><?php echo ucfirst($campista['genero']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Año de Registro</div>
                        <div class="info-value"><?php echo $campista['anio_inscripcion']; ?></div>
                    </div>
                </div>

                <div class="notes-box">
                    <div class="info-label">Observaciones / Información Médica</div>
                    <div class="info-value" style="font-size: 1rem; line-height: 1.5;">
                        <?php echo !empty($campista['notas_especiales']) 
                                   ? nl2br(htmlspecialchars($campista['notas_especiales'])) 
                                   : '<i style="color:#a0aec0">No hay observaciones registradas.</i>'; ?>
                    </div>
                </div>

                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                <a href="editar.php?id=<?php echo $id_campista; ?>" class="btn btn-edit">✏️ Editar Información</a>
                
                <a href="../panel.php" class="btn btn-back">Volver al Inicio</a>
            </div>


            </div>

        </div>

        <?php if ($campista['estado_inscripcion'] === INSCRIPCION_APROBADO): ?>
        <div class="card" style="border-top: 4px solid #4299e1;">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="font-size: 2rem;">🏕️</span>
                    <div>
                        <h3 style="color: #2b6cb0;">Asignación de Campamento</h3>
                        <p style="color: #718096;">Tu hijo ya tiene un lugar asegurado para la temporada <?php echo $campista['anio_inscripcion']; ?>.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>