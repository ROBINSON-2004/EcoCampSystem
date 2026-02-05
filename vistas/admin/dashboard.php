<?php
// Verificar que sea administrador
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// Obtener datos del usuario
$datos_usuario = Sesion::obtenerDatosUsuario();

// Aquí irían consultas para obtener estadísticas
require_once RUTA_CONFIG . '/conexion.php';

$conexion = new Conexion();
$db = $conexion->obtenerConexion();

// Por ahora usaremos datos de ejemplo
$stmt = $db->query("
    SELECT COUNT(*) 
    FROM campistas
");
$total_campistas = $stmt->fetchColumn();
$stmt = $db->query("
    SELECT COUNT(*) 
    FROM grupos
    WHERE estado = 'activo'
");
$total_grupos = $stmt->fetchColumn();
$stmt = $db->query("
    SELECT COUNT(*) 
    FROM actividades
    WHERE estado = 'activo'
");
$total_actividades = $stmt->fetchColumn();
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM asistencia
    WHERE fecha_asistencia = CURDATE()
      AND estado_asistencia = 'presente'
");
$stmt->execute();
$campistas_hoy = $stmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/public/css/dashboard.css">

</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>🏕️ <?php echo NOMBRE_SITIO; ?></h1>
        <div class="user-info">
            <span class="user-name">
                <?php echo $datos_usuario['nombre'] . ' ' . $datos_usuario['apellido']; ?>
            </span>
            <a href="<?php echo URL_BASE; ?>/logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </div>
    
    <!-- Contenido principal -->
    <div class="container">
        
        <!-- Sección de bienvenida -->
        <div class="welcome-section">
            <h2>¡Bienvenido, <?php echo $datos_usuario['nombre']; ?>! 👋</h2>
            <p>Panel de administración - Aquí puedes gestionar todo el sistema del campamento</p>
        </div>
        
        <!-- Estadísticas -->
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
        
        <!-- Acceso rápido -->
        <div class="quick-actions">
            <h3>🚀 Acceso Rápido</h3>
            <div class="actions-grid">
                <a href="<?php echo URL_BASE; ?>/vistas/admin/padres/lista.php" class="action-btn">
                    <span class="icon">👨‍👩‍👧</span>
                    Gestionar Padres
                </a>
                
                <a href="<?php echo URL_BASE; ?>/vistas/admin/campistas/lista.php" class="action-btn">
                    <span class="icon">👦</span>
                    Gestionar Campistas
                </a>
                
                <a href="<?php echo URL_BASE; ?>/vistas/admin/grupos/lista.php" class="action-btn">
                    <span class="icon">👥</span>
                    Gestionar Grupos
                </a>
                
                <a href="<?php echo URL_BASE; ?>/vistas/admin/actividades/calendario.php" class="action-btn">
                    <span class="icon">📅</span>
                    Actividades
                </a>
                
                <a href="<?php echo URL_BASE; ?>/vistas/admin/asistencia/registro-diario.php" class="action-btn">
                    <span class="icon">✅</span>
                    Registrar Asistencia
                </a>
                
                <a href="<?php echo URL_BASE; ?>/vistas/admin/formularios/lista.php" class="action-btn">
                    <span class="icon">📄</span>
                    Formularios
                </a>
                
                <a href="<?php echo URL_BASE; ?>/vistas/admin/notificaciones/enviar.php" class="action-btn">
                    <span class="icon">📧</span>
                    Notificaciones
                </a>

            </div>
        </div>
        
        <!-- Actividades recientes -->
        <div class="recent-section">
            <h3>📊 Actividad Reciente</h3>
            <div class="no-data">
                <p>No hay actividad reciente para mostrar</p>
                <p style="margin-top: 10px; color: #ccc;">Las acciones del sistema aparecerán aquí</p>
            </div>
        </div>
        
    </div>
</body>
</html>