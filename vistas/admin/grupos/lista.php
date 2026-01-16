<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once __DIR__ . '/../../../utilidades/sesion.php';
require_once __DIR__ . '/../../../utilidades/funciones.php';
require_once __DIR__ . '/../../../controladores/GrupoControlador.php';

Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

$controlador = new GrupoControlador();
$mensaje = Sesion::obtenerMensaje();

// Obtener grupos del año actual
$grupos = $controlador->listarTodos('activo', ANIO_CAMPAMENTO_ACTUAL);
$estadisticas = $controlador->obtenerEstadisticas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Grupos - <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/vistas/admin/campistas/lista.php">
    <style>
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
        .btn { padding: 10px 20px; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; transition: 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn-small { padding: 6px 12px; font-size: 0.85rem; }
        .stats-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #667eea; }
        .stat-card h3 { color: #666; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 10px; }
        .stat-card .number { color: #333; font-size: 2rem; font-weight: bold; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #efe; color: #3c3; border-left: 4px solid #3c3; }
        .grupos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        .grupo-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #667eea; transition: 0.3s; }
        .grupo-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2); }
        .grupo-header { margin-bottom: 15px; }
        .grupo-header h3 { color: #333; font-size: 1.4rem; margin-bottom: 5px; }
        .grupo-info { margin: 10px 0; color: #666; font-size: 0.95rem; }
        .grupo-info strong { color: #333; }
        .grupo-actions { margin-top: 20px; display: flex; gap: 10px; padding-top: 20px; border-top: 1px solid #f0f0f0; }
        .empty-state { background: white; padding: 60px 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; color: #999; }
        .empty-state-icon { font-size: 5rem; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>👥 Gestión de Grupos</h1>
        <div class="header-links">
            <a href="<?php echo URL_BASE; ?>/panel.php">← Dashboard</a>
            <a href="<?php echo URL_BASE; ?>/logout.php">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <div>
                <h2>Grupos del Campamento</h2>
                <p style="color: #666; margin-top: 5px;">Año <?php echo ANIO_CAMPAMENTO_ACTUAL; ?></p>
            </div>
            <a href="crear.php" class="btn btn-primary">➕ Crear Nuevo Grupo</a>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?php echo $mensaje['contenido']; ?></div>
        <?php endif; ?>
        
        <div class="stats-cards">
            <div class="stat-card">
                <h3>Total Grupos</h3>
                <div class="number"><?php echo $estadisticas['total']; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #48bb78;">
                <h3>Grupos Activos</h3>
                <div class="number" style="color: #48bb78;"><?php echo $estadisticas['activos']; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #f6ad55;">
                <h3>Grupos Completos</h3>
                <div class="number" style="color: #f6ad55;"><?php echo $estadisticas['completos']; ?></div>
            </div>
        </div>
        
        <?php if (count($grupos) > 0): ?>
            <div class="grupos-grid">
                <?php foreach ($grupos as $grupo): ?>
                    <div class="grupo-card">
                        <div class="grupo-header">
                            <h3><?php echo htmlspecialchars($grupo['nombre_grupo']); ?></h3>
                            <?php if ($grupo['descripcion']): ?>
                                <p style="color: #999; font-size: 0.9rem;"><?php echo htmlspecialchars($grupo['descripcion']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="grupo-info">
                            <strong>👦 Campistas:</strong> <?php echo $grupo['total_campistas']; ?> / <?php echo $grupo['capacidad_maxima']; ?>
                        </div>
                        <div class="grupo-info">
                            <strong>📅 Edades:</strong> 
                            <?php echo $grupo['edad_minima'] ? $grupo['edad_minima'] . ' - ' . $grupo['edad_maxima'] . ' años' : 'Sin restricción'; ?>
                        </div>
                        <div class="grupo-info">
                            <strong>👨‍🏫 Consejero:</strong> 
                            <?php echo $grupo['nombre_consejero'] ? htmlspecialchars($grupo['nombre_consejero'] . ' ' . $grupo['apellido_consejero']) : 'No asignado'; ?>
                        </div>
                        
                        <div class="grupo-actions">
                            <a href="detalle.php?id=<?php echo $grupo['id_grupo']; ?>" class="btn btn-primary btn-small">👁️ Ver</a>
                            <a href="editar.php?id=<?php echo $grupo['id_grupo']; ?>" class="btn btn-secondary btn-small">✏️ Editar</a>
                            <a href="asignar-campistas.php?id=<?php echo $grupo['id_grupo']; ?>" class="btn btn-secondary btn-small">👦 Campistas</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">👥</div>
                <h3>No hay grupos creados</h3>
                <p style="margin: 15px 0;">Crea tu primer grupo para organizar a los campistas</p>
                <a href="crear.php" class="btn btn-primary" style="margin-top: 20px;">➕ Crear Primer Grupo</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>