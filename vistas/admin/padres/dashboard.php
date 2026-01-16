<?php
require_once __DIR__ . '/../../config/constantes.php';
require_once __DIR__ . '/../../utilidades/sesion.php';
require_once __DIR__ . '/../../utilidades/funciones.php';
require_once __DIR__ . '/../../modelos/Padre.php';
require_once __DIR__ . '/../../modelos/Campista.php';

// Verificar que sea padre
Sesion::requerirTipoUsuario(TIPO_PADRE);

// Obtener datos del usuario
$datos_usuario = Sesion::obtenerDatosUsuario();

// Obtener ID del padre
$padre_modelo = new Padre();
$padre_modelo->id_usuario = $datos_usuario['id'];
$padre_modelo->leerPorIdUsuario();

// Obtener hijos del padre
$campista_modelo = new Campista();
$mis_hijos = $campista_modelo->leerPorPadre($padre_modelo->id_padre);

// Calcular estadísticas
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

// Obtener mensaje flash
$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - <?php echo NOMBRE_SITIO; ?></title>
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
        
        .header h1 { font-size: 1.8rem; }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 20px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-logout:hover { background: rgba(255,255,255,0.3); }
        
        .container { max-width: 1200px; margin: 40px auto; padding: 0 30px; }
        
        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .welcome-section h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .welcome-section p { color: #666; font-size: 1.1rem; }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #efe; color: #3c3; border-left: 4px solid #3c3; }
        .alert-error { background: #fee; color: #c33; border-left: 4px solid #c33; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #48bb78;
        }
        
        .stat-card .icon { font-size: 2.5rem; margin-bottom: 10px; }
        .stat-card h3 {
            color: #666;
            font-size: 0.85rem;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .stat-card .number {
            color: #333;
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s;
            border: 2px solid transparent;
            text-decoration: none;
            display: block;
        }
        
        .action-card:hover {
            border-color: #48bb78;
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(72, 187, 120, 0.2);
        }
        
        .action-card .icon { font-size: 3rem; margin-bottom: 15px; }
        .action-card h3 { color: #333; margin-bottom: 10px; }
        .action-card p { color: #666; font-size: 0.9rem; }
        
        .mis-hijos-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .section-header h2 { color: #333; font-size: 1.8rem; }
        
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
        
        .btn-primary {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(72, 187, 120, 0.4);
        }
        
        .hijos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .hijo-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #48bb78;
        }
        
        .hijo-card h4 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        
        .hijo-info {
            color: #666;
            font-size: 0.9rem;
            margin: 5px 0;
        }
        
        .hijo-info strong { color: #333; }
        
        .estado-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 10px;
        }
        
        .estado-aprobado { background: #d4edda; color: #155724; }
        .estado-pendiente { background: #fff3cd; color: #856404; }
        .estado-rechazado { background: #f8d7da; color: #721c24; }
        
        .hijo-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 0.85rem;
        }
        
        .btn-secondary { background: #e0e0e0; color: #333; }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state-icon { font-size: 4rem; margin-bottom: 20px; }
        
        @media (max-width: 768px) {
            .container { padding: 0 15px; }
            .stats-grid { grid-template-columns: 1fr; }
            .actions-grid { grid-template-columns: 1fr; }
            .hijos-grid { grid-template-columns: 1fr; }
        }
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
            <p>Gestiona la inscripción de tus hijos y mantente al día con todas las actividades del campamento</p>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje['tipo'] === 'exito' ? 'success' : 'error'; ?>">
                <?php echo $mensaje['contenido']; ?>
            </div>
        <?php endif; ?>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">👶</div>
                <h3>Mis Hijos</h3>
                <div class="number"><?php echo $total_hijos; ?></div>
            </div>
            
            <div class="stat-card" style="border-left-color: #48bb78;">
                <div class="icon">✅</div>
                <h3>Aprobados</h3>
                <div class="number" style="color: #48bb78;"><?php echo $hijos_aprobados; ?></div>
            </div>
            
            <div class="stat-card" style="border-left-color: #f6ad55;">
                <div class="icon">⏳</div>
                <h3>Pendientes</h3>
                <div class="number" style="color: #f6ad55;"><?php echo $hijos_pendientes; ?></div>
            </div>
        </div>
        
        <!-- Acciones Rápidas -->
        <div class="actions-grid">
            <a href="mis-hijos/lista.php" class="action-card">
                <div class="icon">👶</div>
                <h3>Mis Hijos</h3>
                <p>Ver y administrar tus hijos inscritos</p>
            </a>
            
            <a href="mis-hijos/inscribir.php" class="action-card">
                <div class="icon">➕</div>
                <h3>Inscribir Hijo</h3>
                <p>Inscribe a un nuevo hijo al campamento</p>
            </a>
            
            <a href="formularios/disponibles.php" class="action-card">
                <div class="icon">📄</div>
                <h3>Formularios</h3>
                <p>Firma y envía formularios de consentimiento</p>
            </a>
            
            <a href="notificaciones.php" class="action-card">
                <div class="icon">📧</div>
                <h3>Notificaciones</h3>
                <p>Revisa mensajes y anuncios importantes</p>
            </a>
        </div>
        
        <!-- Mis Hijos -->
        <div class="mis-hijos-section">
            <div class="section-header">
                <h2>👨‍👩‍👧‍👦 Mis Hijos Inscritos</h2>
                <a href="mis-hijos/inscribir.php" class="btn btn-primary">➕ Inscribir Nuevo Hijo</a>
            </div>
            
            <?php if (count($mis_hijos) > 0): ?>
                <div class="hijos-grid">
                    <?php foreach ($mis_hijos as $hijo): ?>
                        <div class="hijo-card">
                            <h4><?php echo htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']); ?></h4>
                            <div class="hijo-info">
                                <strong>Edad:</strong> <?php echo $hijo['edad']; ?> años
                            </div>
                            <div class="hijo-info">
                                <strong>Género:</strong> <?php echo ucfirst($hijo['genero']); ?>
                            </div>
                            <div class="hijo-info">
                                <strong>Año:</strong> <?php echo $hijo['anio_inscripcion']; ?>
                            </div>
                            <span class="estado-badge estado-<?php echo $hijo['estado_inscripcion']; ?>">
                                <?php echo ucfirst($hijo['estado_inscripcion']); ?>
                            </span>
                            <div class="hijo-actions">
                                <a href="mis-hijos/detalle.php?id=<?php echo $hijo['id_campista']; ?>" 
                                   class="btn btn-primary btn-small">
                                    Ver Detalle
                                </a>
                                <a href="mis-hijos/editar.php?id=<?php echo $hijo['id_campista']; ?>" 
                                   class="btn btn-secondary btn-small">
                                    Editar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👶</div>
                    <h3>Aún no tienes hijos inscritos</h3>
                    <p style="margin: 15px 0;">Comienza inscribiendo a tu primer hijo al campamento</p>
                    <a href="mis-hijos/inscribir.php" class="btn btn-primary">
                        ➕ Inscribir Mi Primer Hijo
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>