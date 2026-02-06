<?php
require_once __DIR__ . '/../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_CONFIG . '/conexion.php';

// Verificar que sea administrador
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMIN);

// Obtener datos del usuario logueado
$datos_usuario = Sesion::obtenerDatosUsuario();

// Conexión a la base de datos
$conexion = new Conexion();
$db = $conexion->obtenerConexion();

// --- CONSULTAS PARA ESTADÍSTICAS ---
// Total de campistas registrados
$total_campistas = $db->query("SELECT COUNT(*) FROM campistas")->fetchColumn();

// Total de grupos actualmente activos
$total_grupos = $db->query("SELECT COUNT(*) FROM grupos WHERE estado = 'activo'")->fetchColumn();

// Total de actividades marcadas como activas
$total_actividades = $db->query("SELECT COUNT(*) FROM actividades WHERE estado = 'activo'")->fetchColumn();

// Campistas que marcaron asistencia como 'presente' el día de hoy
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM asistencia 
    WHERE fecha_asistencia = CURDATE() 
      AND estado_asistencia = 'presente'
");
$stmt->execute();
$campistas_hoy = $stmt->fetchColumn();

// --- CONSULTA PARA ACTIVIDADES RECIENTES (LOGS) ---
// Obtiene los últimos 5 registros de la tabla de auditoría unida con nombres de usuario
$stmt = $db->query("
    SELECT r.*, u.nombre, u.apellido 
    FROM registros_actividad r 
    LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
    ORDER BY r.fecha_hora DESC 
    LIMIT 5
");
$logs_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/public/css/dashboard.css">
    <style>
        /* Estilos específicos para la lista de actividades recientes */
        .log-list { list-style: none; padding: 0; }
        .log-item { padding: 12px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .log-item:last-child { border-bottom: none; }
        .log-meta { font-size: 0.85rem; color: #888; }
        .log-text { color: #444; font-weight: 500; }
        .log-badge { background: #e2e8f0; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏕️ <?php echo NOMBRE_SITIO; ?></h1>
        <div class="user-info">
            <span class="user-name">
                <?php echo $datos_usuario['nombre'] . ' ' . $datos_usuario['apellido']; ?>
            </span>
            <a href="<?php echo URL_BASE; ?>/logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-section">
            <h2>¡Bienvenido, <?php echo $datos_usuario['nombre']; ?>! 👋</h2>
            <p>Panel de administración - Gestión integral del campamento</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="icon">👥</div>
                <h3>Total Campistas</h3>
                <div class="number"><?php echo $total_campistas; ?></div>
            </div>
            
            <div class="stat-card green">
                <div class="icon">👨‍👩‍👧‍👦</div>
                <h3>Grupos Activos</h3>
                <div class="number"><?php echo $total_grupos; ?></div>
            </div>
            
            <div class="stat-card orange">
                <div class="icon">🎯</div>
                <h3>Actividades</h3>
                <div class="number"><?php echo $total_actividades; ?></div>
            </div>
            
            <div class="stat-card purple">
                <div class="icon">✅</div>
                <h3>Presentes Hoy</h3>
                <div class="number"><?php echo $campistas_hoy; ?></div>
            </div>
        </div>
        
        <div class="quick-actions">
            <h3>🚀 Acceso Rápido</h3>
            <div class="actions-grid">
                <a href="<?php echo URL_BASE; ?>/vistas/admin/padres/lista.php" class="action-btn">
                    <span class="icon">👨‍👩‍👧</span> Gestionar Padres
                </a>
                
                <a href="<?php echo URL_BASE; ?>/vistas/admin/campistas/lista.php" class="action-btn">
                    <span class="icon">👦</span> Gestionar Campistas
                </a>
                
                <a href="<?php echo URL_BASE; ?>/vistas/admin/grupos/lista.php" class="action-btn">
                    <span class="icon">👥</span> Gestionar Grupos
                </a>
                
                <a href="<?php echo URL_BASE; ?>/vistas/admin/actividades/lista.php" class="action-btn">
                    <span class="icon">📅</span> Actividades
                </a>
                
                <a href="<?php echo URL_BASE; ?>/vistas/admin/formularios/lista.php" class="action-btn">
                    <span class="icon">📄</span> Formularios
                </a>
            </div>
        </div>
        
        <div class="recent-section">
            <h3>📊 Actividad Reciente</h3>
            <div class="card" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <?php if (!empty($logs_recientes)): ?>
                    <ul class="log-list">
                        <?php foreach ($logs_recientes as $log): ?>
                            <li class="log-item">
                                <div>
                                    <span class="log-badge"><?php echo htmlspecialchars($log['tabla_afectada'] ?? 'SISTEMA'); ?></span>
                                    <span class="log-text"><?php echo htmlspecialchars($log['accion']); ?></span>
                                    <br>
                                    <small class="log-meta">
                                        Realizado por: <?php echo htmlspecialchars($log['nombre'] . ' ' . $log['apellido']); ?>
                                    </small>
                                </div>
                                <div class="log-meta">
                                    <?php echo date('H:i', strtotime($log['fecha_hora'])); ?> 
                                    <small><?php echo date('d/m/Y', strtotime($log['fecha_hora'])); ?></small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="no-data" style="text-align: center; color: #999; padding: 30px;">
                        <p>No se han registrado acciones recientes en el sistema.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>