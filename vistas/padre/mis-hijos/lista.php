<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once __DIR__ . '/../../../utilidades/sesion.php';
require_once __DIR__ . '/../../../utilidades/funciones.php';
require_once __DIR__ . '/../../../modelos/Padre.php';
require_once __DIR__ . '/../../../modelos/Campista.php';

// Verificar que sea padre
Sesion::requerirTipoUsuario(TIPO_PADRE);

$datos_usuario = Sesion::obtenerDatosUsuario();

// Obtener ID del padre
$padre_modelo = new Padre();
$padre_modelo->id_usuario = $datos_usuario['id'];
$padre_modelo->leerPorIdUsuario();

// Obtener hijos
$campista_modelo = new Campista();
$mis_hijos = $campista_modelo->leerPorPadre($padre_modelo->id_padre);

$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Hijos - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; min-height: 100vh; }
        
        .header {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 { font-size: 1.5rem; }
        .header-links { display: flex; gap: 15px; }
        .header-links a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; transition: all 0.3s; }
        .header-links a:hover { background: rgba(255,255,255,0.2); }
        
        .container { max-width: 1200px; margin: 30px auto; padding: 0 30px; }
        
        .breadcrumb { color: #666; margin-bottom: 20px; font-size: 0.9rem; }
        .breadcrumb a { color: #48bb78; text-decoration: none; }
        
        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-header h2 { color: #333; font-size: 2rem; }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #efe; color: #3c3; border-left: 4px solid #3c3; }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(72, 187, 120, 0.4); }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn-small { padding: 6px 12px; font-size: 0.85rem; }
        
        .hijos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .hijo-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #48bb78;
            transition: all 0.3s;
        }
        
        .hijo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(72, 187, 120, 0.2);
        }
        
        .hijo-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .hijo-header h3 { color: #333; font-size: 1.4rem; }
        
        .estado-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .estado-aprobado { background: #d4edda; color: #155724; }
        .estado-pendiente { background: #fff3cd; color: #856404; }
        .estado-rechazado { background: #f8d7da; color: #721c24; }
        
        .hijo-info {
            margin: 10px 0;
            color: #666;
            font-size: 0.95rem;
        }
        
        .hijo-info strong { color: #333; }
        
        .hijo-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }
        
        .empty-state {
            background: white;
            padding: 60px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            color: #999;
        }
        
        .empty-state-icon { font-size: 5rem; margin-bottom: 20px; }
        
        @media (max-width: 768px) {
            .container { padding: 0 15px; }
            .hijos-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👶 Mis Hijos</h1>
        <div class="header-links">
            <a href="<?php echo URL_BASE; ?>/vistas/padre/dashboard.php">← Dashboard</a>
            <a href="<?php echo URL_BASE; ?>/logout.php">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo URL_BASE; ?>/vistas/padre/dashboard.php">Inicio</a> / 
            <span>Mis Hijos</span>
        </div>
        
        <div class="page-header">
            <h2>Mis Hijos Inscritos</h2>
            <a href="inscribir.php" class="btn btn-primary">➕ Inscribir Nuevo Hijo</a>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-success">
                <?php echo $mensaje['contenido']; ?>
            </div>
        <?php endif; ?>
        
        <?php if (count($mis_hijos) > 0): ?>
            <div class="hijos-grid">
                <?php foreach ($mis_hijos as $hijo): ?>
                    <div class="hijo-card">
                        <div class="hijo-header">
                            <h3><?php echo htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']); ?></h3>
                            <span class="estado-badge estado-<?php echo $hijo['estado_inscripcion']; ?>">
                                <?php echo ucfirst($hijo['estado_inscripcion']); ?>
                            </span>
                        </div>
                        
                        <div class="hijo-info">
                            <strong>📅 Edad:</strong> <?php echo $hijo['edad']; ?> años
                        </div>
                        <div class="hijo-info">
                            <strong>🎂 Fecha de Nacimiento:</strong> <?php echo formatear_fecha($hijo['fecha_nacimiento']); ?>
                        </div>
                        <div class="hijo-info">
                            <strong>👤 Género:</strong> <?php echo ucfirst($hijo['genero']); ?>
                        </div>
                        <div class="hijo-info">
                            <strong>📆 Año Inscripción:</strong> <?php echo $hijo['anio_inscripcion']; ?>
                        </div>
                        <div class="hijo-info">
                            <strong>🗓️ Inscrito:</strong> <?php echo formatear_fecha($hijo['fecha_inscripcion']); ?>
                        </div>
                        
                        <?php if (!empty($hijo['notas_especiales'])): ?>
                        <div class="hijo-info" style="margin-top: 15px;">
                            <strong>📝 Notas:</strong><br>
                            <span style="color: #999; font-size: 0.9rem;">
                                <?php echo nl2br(htmlspecialchars(truncar_texto($hijo['notas_especiales'], 100))); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="hijo-actions">
                            <a href="detalle.php?id=<?php echo $hijo['id_campista']; ?>" 
                               class="btn btn-primary btn-small">
                                👁️ Ver Detalle
                            </a>
                            <a href="editar.php?id=<?php echo $hijo['id_campista']; ?>" 
                               class="btn btn-secondary btn-small">
                                ✏️ Editar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">👶</div>
                <h3>Aún no tienes hijos inscritos</h3>
                <p style="margin: 15px 0; font-size: 1.1rem;">Comienza inscribiendo a tu primer hijo al campamento</p>
                <a href="inscribir.php" class="btn btn-primary" style="margin-top: 20px;">
                    ➕ Inscribir Mi Primer Hijo
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>