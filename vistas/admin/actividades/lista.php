<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Actividad.php';

// 1. Seguridad
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// 2. Obtener datos
$actividad_obj = new Actividad();
// Sincronizamos el nombre de la variable con el resto del archivo
$actividades = $actividad_obj->leerTodas(); 
$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Actividades - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        /* Estilos base mantenidos y optimizados */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.5rem; }
        .header-links { display: flex; gap: 15px; }
        .header-links a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; transition: 0.3s; }
        .header-links a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 1400px; margin: 30px auto; padding: 0 30px; }
        .page-header { background: white; padding: 25px 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .page-header h2 { color: #333; font-size: 2rem; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; transition: 0.3s; vertical-align: middle; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn-danger { background: #f56565; color: white; }
        .btn-small { padding: 6px 12px; font-size: 0.85rem; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #efe; color: #3c3; border-left: 4px solid #3c3; }
        .actividades-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        .actividad-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #667eea; transition: 0.3s; display: flex; flex-direction: column; }
        .actividad-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2); }
        .actividad-header { margin-bottom: 15px; display: flex; justify-content: space-between; align-items: flex-start; }
        .actividad-header h3 { color: #333; font-size: 1.4rem; margin-right: 10px; }
        .tipo-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; background: #e3f2fd; color: #1565c0; white-space: nowrap; }
        .actividad-info { margin: 8px 0; color: #666; font-size: 0.95rem; }
        .actividad-info strong { color: #333; }
        .actividad-actions { margin-top: auto; padding-top: 20px; border-top: 1px solid #f0f0f0; display: flex; gap: 8px; flex-wrap: wrap; }
        .empty { background: white; padding: 60px 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎯 Gestión de Actividades</h1>
        <div class="header-links">
            <a href="<?php echo URL_BASE; ?>/panel.php">← Dashboard</a>
            <a href="calendario.php">📅 Calendario</a>
            <a href="<?php echo URL_BASE; ?>/logout.php">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <div>
                <h2>Catálogo de Actividades</h2>
                <p style="color: #888;">Gestiona las experiencias disponibles para los campistas</p>
            </div>
            <a href="crear.php" class="btn btn-primary">➕ Crear Actividad</a>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?php echo $mensaje['contenido']; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($actividades)): ?>
            <div class="actividades-grid">
                <?php foreach ($actividades as $act): ?>
                    <div class="actividad-card">
                        <div class="actividad-header">
                            <h3><?php echo htmlspecialchars($act['nombre_actividad']); ?></h3>
                            <span class="tipo-badge"><?php echo ucfirst($act['tipo_actividad']); ?></span>
                        </div>
                        
                        <?php if (!empty($act['descripcion'])): ?>
                            <p style="color: #666; font-size: 0.9rem; margin-bottom: 15px; line-height: 1.4;">
                                <?php echo htmlspecialchars(substr($act['descripcion'], 0, 100)) . (strlen($act['descripcion']) > 100 ? '...' : ''); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="actividad-info">
                            <strong>⏱️ Duración:</strong> 
                            <?php echo $act['duracion_minutos'] ? $act['duracion_minutos'] . ' min' : 'No definida'; ?>
                        </div>
                        
                        <div class="actividad-info">
                            <strong>📍 Ubicación:</strong> 
                            <?php echo htmlspecialchars($act['ubicacion'] ?? 'Por asignar'); ?>
                        </div>
                        
                        <div class="actividad-info">
                            <strong>👥 Capacidad:</strong> 
                            <?php echo $act['capacidad_maxima'] ? $act['capacidad_maxima'] . ' pers.' : 'Ilimitada'; ?>
                        </div>
                        
                        <?php if ($act['edad_minima'] || $act['edad_maxima']): ?>
                            <div class="actividad-info">
                                <strong>📅 Edades:</strong> 
                                <?php echo ($act['edad_minima'] ?? 'N/A') . ' a ' . ($act['edad_maxima'] ?? 'N/A') . ' años'; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="actividad-actions">
                            <a href="detalle.php?id=<?php echo $act['id_actividad']; ?>" class="btn btn-secondary btn-small">👁️ Ver</a>
                            <a href="editar.php?id=<?php echo $act['id_actividad']; ?>" class="btn btn-secondary btn-small">✏️ Editar</a>
                            
                            <form action="eliminar.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de desactivar esta actividad?');">
                                <input type="hidden" name="id_actividad" value="<?php echo $act['id_actividad']; ?>">
                                <button type="submit" class="btn btn-danger btn-small">🗑️ Eliminar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty">
                <div style="font-size: 5rem; margin-bottom: 20px;">🎯</div>
                <h3>No hay actividades registradas</h3>
                <p style="margin: 15px 0;">Comienza creando el catálogo de actividades para este campamento.</p>
                <a href="crear.php" class="btn btn-primary" style="margin-top: 20px;">➕ Crear Primera Actividad</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>