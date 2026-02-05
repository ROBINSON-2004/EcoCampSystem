<?php
require_once __DIR__ . '/../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Padre.php';
require_once RUTA_MODELOS . '/Campista.php';

// 1. Seguridad: Verificar sesión de padre
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_PADRE);

$datos_usuario = Sesion::obtenerDatosUsuario();

// 2. Obtener datos del padre y sus hijos
$padre_modelo = new Padre();
$padre_modelo->id_usuario = $datos_usuario['id'];
$padre_modelo->leerPorIdUsuario();

$campista_modelo = new Campista();
$mis_hijos = $campista_modelo->leerPorPadre($padre_modelo->id_padre);

// 3. Cálculo de estadísticas
$total_hijos = count($mis_hijos);
$hijos_aprobados = 0;
$hijos_pendientes = 0;

foreach ($mis_hijos as $hijo) {
    if ($hijo['estado_inscripcion'] === INSCRIPCION_APROBADO) {
        $hijos_aprobados++;
    } elseif ($hijo['estado_inscripcion'] === INSCRIPCION_PENDIENTE) {
        $hijos_pendientes++;
    }
}

$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        /* Estilos Originales del Dashboard */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; min-height: 100vh; }
        
        .header { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .user-info { display: flex; align-items: center; gap: 20px; }
        .btn-logout { background: rgba(255,255,255,0.2); color: white; padding: 8px 20px; border: 1px solid rgba(255,255,255,0.3); border-radius: 6px; text-decoration: none; transition: all 0.3s; }
        .btn-logout:hover { background: rgba(255,255,255,0.3); }
        
        .container { max-width: 1200px; margin: 40px auto; padding: 0 30px; }
        .welcome-section { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px; }
        
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #efe; color: #3c3; border-left: 4px solid #3c3; }
        .alert-error { background: #fee; color: #c33; border-left: 4px solid #c33; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #48bb78; }
        .stat-card .number { color: #333; font-size: 2.5rem; font-weight: bold; }
        
        .actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .action-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; text-decoration: none; transition: all 0.3s; border: 2px solid transparent; display: block; }
        .action-card:hover { border-color: #48bb78; transform: translateY(-5px); }
        
        .mis-hijos-section { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; }
        
        .hijos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .hijo-card { background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid #48bb78; }
        .hijo-actions { margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap; }
        
        .btn { padding: 10px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.3s; border: none; }
        .btn-primary { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; }
        .btn-small { padding: 6px 12px; font-size: 0.85rem; }
        .btn-danger { background: #f56565; color: white; }
        .badge-locked { color: #888; font-size: 0.85rem; font-style: italic; align-self: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏕️ Portal para Padres</h1>
        <div class="user-info">
            <span><?php echo $datos_usuario['nombre'] . ' ' . $datos_usuario['apellido']; ?></span>
            <a href="<?php echo URL_BASE; ?>/logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-section">
            <h2>¡Bienvenido, <?php echo $datos_usuario['nombre']; ?>! 👋</h2>
            <p>Gestiona las inscripciones y revisa el estado de tus hijos en el campamento.</p>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje['tipo'] === 'exito' ? 'success' : 'error'; ?>">
                <?php echo $mensaje['contenido']; ?>
            </div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Mis Hijos</h3>
                <div class="number"><?php echo $total_hijos; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #48bb78;">
                <h3>Aprobados</h3>
                <div class="number" style="color: #48bb78;"><?php echo $hijos_aprobados; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #f6ad55;">
                <h3>Pendientes</h3>
                <div class="number" style="color: #f6ad55;"><?php echo $hijos_pendientes; ?></div>
            </div>
        </div>
        
        <div class="actions-grid">
            <a href="mis-hijos/inscribir.php" class="action-card">
                <h3>➕ Inscribir Hijo</h3>
            </a>
            <a href="formularios/disponibles.php" class="action-card">
                <h3>📄 Formularios</h3>
            </a>
            <a href="actividades/calendario.php" class="action-card">
                <h3>🎯 Actividades</h3>
            </a>
        </div>
        
        <div class="mis-hijos-section">
            <div class="section-header">
                <h2>👨‍👩‍👧‍👦 Mis Hijos Inscritos</h2>
                <a href="mis-hijos/inscribir.php" class="btn btn-primary">➕ Nuevo Registro</a>
            </div>
            
            <?php if (!empty($mis_hijos)): ?>
                <div class="hijos-grid">
                    <?php foreach ($mis_hijos as $hijo): ?>
                        <div class="hijo-card">
                            <h4><?php echo htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']); ?></h4>
                            <p>Estado: <strong><?php echo ucfirst($hijo['estado_inscripcion']); ?></strong></p>
                            
                            <div class="hijo-actions">
                                <a href="mis-hijos/detalle.php?id=<?php echo $hijo['id_campista']; ?>" class="btn btn-primary btn-small">Ver Detalle</a>
                                <a href="mis-hijos/editar.php?id=<?php echo $hijo['id_campista']; ?>" class="btn btn-small" style="background: #e2e8f0; color: #333;">Editar</a>
                                
                                <?php if ($hijo['estado_inscripcion'] !== INSCRIPCION_APROBADO): ?>
                                    <form action="mis-hijos/eliminar.php" method="POST" onsubmit="return confirm('¿Retirar inscripción?');">
                                        <input type="hidden" name="id_campista" value="<?php echo $hijo['id_campista']; ?>">
                                        <button type="submit" class="btn btn-danger btn-small">Eliminar</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge-locked">🔒 Aprobado</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 20px;">No tienes hijos registrados.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>