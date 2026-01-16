<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once __DIR__ . '/../../../utilidades/sesion.php';
require_once __DIR__ . '/../../../utilidades/funciones.php';
require_once __DIR__ . '/../../../controladores/GrupoControlador.php';

Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

if (!isset($_GET['id']) || !es_numero_valido($_GET['id'])) {
    Sesion::establecerMensaje('error', 'ID de grupo no válido.');
    header('Location: lista.php');
    exit();
}

$id_grupo = (int)$_GET['id'];
$controlador = new GrupoControlador();
$grupo = $controlador->obtenerPorId($id_grupo);

if (!$grupo) {
    Sesion::establecerMensaje('error', 'Grupo no encontrado.');
    header('Location: lista.php');
    exit();
}

// Obtener campistas del grupo
$campistas = $controlador->obtenerCampistas($id_grupo);

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'eliminar') {
        $resultado = $controlador->eliminar($id_grupo);
        if ($resultado['exito']) {
            Sesion::establecerMensaje('exito', $resultado['mensaje']);
            header('Location: lista.php');
            exit();
        }
    }
}

$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Grupo - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; min-height: 100vh; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.5rem; }
        .header a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; transition: 0.3s; }
        .header a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 30px; }
        .breadcrumb { color: #666; margin-bottom: 20px; font-size: 0.9rem; }
        .breadcrumb a { color: #667eea; text-decoration: none; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #efe; color: #3c3; border-left: 4px solid #3c3; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .card-header { padding: 25px 30px; border-bottom: 2px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { color: #333; font-size: 1.8rem; }
        .card-body { padding: 30px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; }
        .info-item { padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; }
        .info-label { color: #666; font-size: 0.9rem; font-weight: 500; margin-bottom: 5px; text-transform: uppercase; }
        .info-value { color: #333; font-size: 1.1rem; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; transition: 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn-danger { background: #f56565; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .actions-group { display: flex; gap: 10px; flex-wrap: wrap; }
        .campistas-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; }
        .campista-item { background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 3px solid #667eea; }
        .campista-item h4 { color: #333; margin-bottom: 5px; }
        .campista-item p { color: #666; font-size: 0.9rem; }
        .empty-state { text-align: center; padding: 40px; color: #999; }
        @media (max-width: 768px) { .info-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>👥 Detalle del Grupo</h1>
        <a href="lista.php">← Volver</a>
    </div>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo URL_BASE; ?>/panel.php">Inicio</a> / 
            <a href="lista.php">Grupos</a> / 
            <span>Detalle</span>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?php echo $mensaje['contenido']; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <div>
                    <h2><?php echo htmlspecialchars($grupo['nombre_grupo']); ?></h2>
                    <p style="color: #666; margin-top: 5px;">ID: #<?php echo $grupo['id_grupo']; ?> - Año <?php echo $grupo['anio_campamento']; ?></p>
                </div>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Descripción</div>
                        <div class="info-value">
                            <?php echo $grupo['descripcion'] ? nl2br(htmlspecialchars($grupo['descripcion'])) : 'Sin descripción'; ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Capacidad</div>
                        <div class="info-value">
                            <?php echo $grupo['total_campistas']; ?> / <?php echo $grupo['capacidad_maxima']; ?> campistas
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Rango de Edades</div>
                        <div class="info-value">
                            <?php 
                            if ($grupo['edad_minima'] && $grupo['edad_maxima']) {
                                echo $grupo['edad_minima'] . ' - ' . $grupo['edad_maxima'] . ' años';
                            } else {
                                echo 'Sin restricción';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Consejero Asignado</div>
                        <div class="info-value">
                            <?php 
                            if ($grupo['nombre_consejero']) {
                                echo htmlspecialchars($grupo['nombre_consejero'] . ' ' . $grupo['apellido_consejero']);
                            } else {
                                echo 'Sin asignar';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Estado</div>
                        <div class="info-value">
                            <?php echo ucfirst($grupo['estado']); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Fecha de Creación</div>
                        <div class="info-value">
                            <?php echo formatear_fecha($grupo['fecha_creacion'], true); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>👦 Campistas del Grupo (<?php echo count($campistas); ?>)</h2>
                <a href="asignar-campistas.php?id=<?php echo $id_grupo; ?>" class="btn btn-primary">
                    ➕ Asignar Campistas
                </a>
            </div>
            <div class="card-body">
                <?php if (count($campistas) > 0): ?>
                    <div class="campistas-list">
                        <?php foreach ($campistas as $campista): ?>
                            <div class="campista-item">
                                <h4><?php echo htmlspecialchars($campista['nombre'] . ' ' . $campista['apellido']); ?></h4>
                                <p>📅 <?php echo $campista['edad']; ?> años</p>
                                <p>👤 <?php echo ucfirst($campista['genero']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No hay campistas asignados a este grupo</p>
                        <a href="asignar-campistas.php?id=<?php echo $id_grupo; ?>" class="btn btn-primary" style="margin-top: 15px;">
                            Asignar Campistas
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header"><h2>⚙️ Acciones</h2></div>
            <div class="card-body">
                <div class="actions-group">
                    <a href="editar.php?id=<?php echo $id_grupo; ?>" class="btn btn-primary">✏️ Editar Grupo</a>
                    <a href="asignar-campistas.php?id=<?php echo $id_grupo; ?>" class="btn btn-secondary">👦 Gestionar Campistas</a>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('¿Desactivar este grupo?');">
                        <input type="hidden" name="accion" value="eliminar">
                        <button type="submit" class="btn btn-danger">🚫 Desactivar Grupo</button>
                    </form>
                    <a href="lista.php" class="btn btn-secondary">← Volver</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>