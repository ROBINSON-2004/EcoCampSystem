<?php
/**
 * ============================================
 * VISTA: GESTIÓN DE CAMPISTAS - EcoCampSystem
 * Panel Administrativo 2026
 * ============================================
 */

require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/CampistaControlador.php';

// 1. SEGURIDAD: Solo administradores
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// 2. INICIALIZACIÓN
$controlador = new CampistaControlador();
$mensaje = Sesion::obtenerMensaje();

// 3. PROCESAR FILTROS Y BÚSQUEDA
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : null;
$termino_busqueda = isset($_GET['buscar']) ? limpiar_cadena($_GET['buscar']) : '';

// 4. OBTENER DATOS
if (!empty($termino_busqueda)) {
    $campistas = $controlador->buscar($termino_busqueda);
} else {
    // Listar por año actual definido en constantes
    $campistas = $controlador->listarTodos($filtro_estado, null);
}

// 5. OBTENER ESTADÍSTICAS (Para las tarjetas superiores)
$estadisticas = $controlador->obtenerEstadisticas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Campistas | <?php echo NOMBRE_SITIO; ?></title>
    <style>
        /* Estilos Base */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f2f5; color: #1a202c; }
        
        .header { background: linear-gradient(135deg, #2c3e50, #4a69bd); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header-links a { color: white; text-decoration: none; margin-left: 20px; font-weight: 500; font-size: 0.9rem; }

        .container { max-width: 1400px; margin: 30px auto; padding: 0 30px; }
        
        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid #4a69bd; }
        .stat-card h3 { font-size: 0.8rem; color: #718096; text-transform: uppercase; margin-bottom: 10px; }
        .stat-card .number { font-size: 1.8rem; font-weight: bold; color: #2d3748; }

        /* Actions Bar */
        .actions-bar { background: white; padding: 20px; border-radius: 12px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .search-box { display: flex; gap: 10px; flex-grow: 1; max-width: 500px; }
        .search-box input { flex-grow: 1; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; }

        .filter-tabs { display: flex; gap: 10px; }
        .filter-tab { padding: 8px 16px; background: #edf2f7; color: #4a5568; text-decoration: none; border-radius: 8px; font-size: 0.85rem; transition: 0.3s; }
        .filter-tab.active { background: #4a69bd; color: white; }

        /* Table */
        .table-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; padding: 15px; color: #64748b; font-size: 0.85rem; border-bottom: 2px solid #edf2f7; }
        td { padding: 15px; border-bottom: 1px solid #edf2f7; font-size: 0.9rem; }
        tr:hover { background: #f1f5f9; }

        /* Badges */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; }
        .estado-aprobado { background: #c6f6d5; color: #22543d; }
        .estado-pendiente { background: #feebc8; color: #744210; }
        .estado-rechazado { background: #fed7d7; color: #822727; }

        .btn-action { text-decoration: none; padding: 5px; margin: 0 2px; }
    </style>
</head>
<body>

<div class="header">
    <h1>🛡️ Administración EcoCamp</h1>
    <div class="header-links">
        <a href="<?php echo URL_BASE; ?>/panel.php">Dashboard</a>
        <a href="<?php echo URL_BASE; ?>/logout.php" style="color: #feb2b2;">Cerrar Sesión</a>
    </div>
</div>

<div class="container">
    
    <div class="stats-grid">
        <div class="stat-card"><h3>Total Inscritos</h3><div class="number"><?php echo $estadisticas['total']; ?></div></div>
        <div class="stat-card" style="border-left-color: #48bb78;"><h3>Aprobados</h3><div class="number"><?php echo $estadisticas['aprobados']; ?></div></div>
        <div class="stat-card" style="border-left-color: #f6ad55;"><h3>Pendientes</h3><div class="number"><?php echo $estadisticas['pendientes']; ?></div></div>
        <div class="stat-card" style="border-left-color: #f56565;"><h3>Rechazados</h3><div class="number"><?php echo $estadisticas['rechazados']; ?></div></div>
    </div>

    <div class="actions-bar">
        <form class="search-box" method="GET">
            <input type="text" name="buscar" placeholder="Buscar por nombre o ID..." value="<?php echo htmlspecialchars($termino_busqueda ?? ''); ?>">
            <button type="submit" style="padding: 10px 20px; background: #4a69bd; color:white; border:none; border-radius:8px; cursor:pointer;">🔍</button>
        </form>

        <div class="filter-tabs">
            <a href="lista.php" class="filter-tab <?php echo !$filtro_estado ? 'active' : ''; ?>">Todos</a>
            <a href="lista.php?estado=aprobado" class="filter-tab <?php echo $filtro_estado === 'aprobado' ? 'active' : ''; ?>">Aprobados</a>
            <a href="lista.php?estado=pendiente" class="filter-tab <?php echo $filtro_estado === 'pendiente' ? 'active' : ''; ?>">Pendientes</a>
        </div>
    </div>

    <div class="table-container">
        <?php if (!empty($campistas)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Campista</th>
                    <th>Género</th>
                    <th>Edad</th>
                    <th>Padre / Tutor</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campistas as $campista): ?>
                <tr>
                    <td><strong>#<?php echo $campista['id_campista']; ?></strong></td>
                    <td>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars(($campista['nombre'] ?? '') . ' ' . ($campista['apellido'] ?? '')); ?></div>
                        <small style="color: #a0aec0;"><?php echo formatear_fecha($campista['fecha_inscripcion']); ?></small>
                    </td>
                    <td><?php echo ucfirst($campista['genero'] ?? ''); ?></td>
                    <td><?php echo $campista['edad']; ?> años</td>
                    <td><?php echo htmlspecialchars(($campista['nombre_padre'] ?? '') . ' ' . ($campista['apellido_padre'] ?? '')); ?></td>
                    <td>
                        <span class="badge estado-<?php echo $campista['estado_inscripcion'] ?? 'pendiente'; ?>">
                            <?php echo ucfirst($campista['estado_inscripcion'] ?? 'pendiente'); ?>
                        </span>
                    </td>
                    <td>
                        <a href="detalle.php?id=<?php echo $campista['id_campista']; ?>" class="btn-action" title="Ver Detalle">👁️</a>
                        <a href="editar.php?id=<?php echo $campista['id_campista']; ?>" class="btn-action" title="Editar">✏️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div style="padding: 50px; text-align: center; color: #a0aec0;">
                <p style="font-size: 1.2rem;">No se encontraron campistas registrados.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>