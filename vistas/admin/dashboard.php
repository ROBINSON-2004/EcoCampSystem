<?php
// Verificar que sea administrador
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// Obtener datos del usuario
$datos_usuario = Sesion::obtenerDatosUsuario();

// Aquí irían consultas para obtener estadísticas
// Por ahora usaremos datos de ejemplo
$total_campistas = 0;
$total_grupos = 0;
$total_actividades = 0;
$campistas_hoy = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 1.8rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-name {
            font-size: 1rem;
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
        
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
        }
        
        /* Contenedor principal */
        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 40px;
        }
        
        /* Bienvenida */
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
        
        .welcome-section p {
            color: #666;
            font-size: 1.1rem;
        }
        
        /* Tarjetas de estadísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .stat-card .number {
            color: #333;
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .stat-card.blue { border-left: 4px solid #667eea; }
        .stat-card.green { border-left: 4px solid #48bb78; }
        .stat-card.orange { border-left: 4px solid #f6ad55; }
        .stat-card.purple { border-left: 4px solid #9f7aea; }
        
        /* Menú de acceso rápido */
        .quick-actions {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        
        .quick-actions h3 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            font-weight: 500;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .action-btn .icon {
            font-size: 2rem;
        }
        
        /* Actividades recientes */
        .recent-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .recent-section h3 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 15px 20px;
            }
            
            .container {
                padding: 0 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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