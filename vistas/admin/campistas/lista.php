<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once __DIR__ . '/../../../utilidades/sesion.php';
require_once __DIR__ . '/../../../utilidades/funciones.php';
require_once __DIR__ . '/../../../controladores/CampistaControlador.php';

// Requerir autenticación de administrador
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// Instanciar controlador
$controlador = new CampistaControlador();

// Procesar mensaje
$mensaje = Sesion::obtenerMensaje();

// Procesar filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : null;
$termino_busqueda = isset($_GET['buscar']) ? limpiar_cadena($_GET['buscar']) : '';

// Obtener lista de campistas
if (!empty($termino_busqueda)) {
    $campistas = $controlador->buscar($termino_busqueda);
} else {
    $campistas = $controlador->listarTodos($filtro_estado, ANIO_CAMPAMENTO_ACTUAL);
}

// Obtener estadísticas
$estadisticas = $controlador->obtenerEstadisticas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Campistas - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; min-height: 100vh; }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 { font-size: 1.5rem; }
        .header-links { display: flex; gap: 15px; align-items: center; }
        .header-links a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; transition: all 0.3s; }
        .header-links a:hover { background: rgba(255,255,255,0.2); }
        
        .container { max-width: 1400px; margin: 30px auto; padding: 0 30px; }
        
        .breadcrumb { color: #666; margin-bottom: 20px; font-size: 0.9rem; }
        .breadcrumb a { color: #667eea; text-decoration: none; }
        
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
        
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn-small { padding: 6px 12px; font-size: 0.85rem; }
        .btn-success { background: #48bb78; color: white; }
        .btn-danger { background: #f56565; color: white; }
        .btn-warning { background: #f6ad55; color: white; }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #efe; color: #3c3; border-left: 4px solid #3c3; }
        .alert-error { background: #fee; color: #c33; border-left: 4px solid #c33; }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 0.85rem;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            color: #333;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .actions-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 500px;
        }
        
        .search-box input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .search-box input:focus { outline: none; border-color: #667eea; }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 8px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .filter-tab:hover, .filter-tab.active {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }
        
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8f9fa; }
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #666;
        }
        
        tbody tr:hover { background: #f8f9fa; }
        
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
        .estado-retirado { background: #e2e3e5; color: #383d41; }
        
        .actions-cell { display: flex; gap: 8px; }
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .no-data-icon { font-size: 4rem; margin-bottom: 20px; }
        
        @media (max-width: 768px) {
            .container { padding: 0 15px; }
            .actions-bar { flex-direction: column; align-items: stretch; }
            .search-box { max-width: 100%; }
            table { font-size: 0.9rem; }
            th, td { padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👦 Gestión de Campistas</h1>
        <div class="header-links">
            <a href="<?php echo URL_BASE; ?>/panel.php">← Volver al Dashboard</a>
            <a href="<?php echo URL_BASE; ?>/logout.php">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo URL_BASE; ?>/panel.php">Inicio</a> / 
            <span>Gestión de Campistas</span>
        </div>
        
        <div class="page-header">
            <div>
                <h2>Campistas Registrados</h2>
                <p style="color: #666; margin-top: 5px;">Año <?php echo ANIO_CAMPAMENTO_ACTUAL; ?></p>
            </div>
            <a href="crear.php" class="btn btn-primary">➕ Agregar Campista</a>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje['tipo'] === 'exito' ? 'success' : 'error'; ?>">
                <?php echo $mensaje['contenido']; ?>
            </div>
        <?php endif; ?>
        
        <!-- Estadísticas -->
        <div class="stats-cards">
            <div class="stat-card">
                <h3>Total Campistas</h3>
                <div class="number"><?php echo $estadisticas['total']; ?></div>
            </div>
            
            <div class="stat-card" style="border-left-color: #48bb78;">
                <h3>Aprobados</h3>
                <div class="number" style="color: #48bb78;"><?php echo $estadisticas['aprobados']; ?></div>
            </div>
            
            <div class="stat-card" style="border-left-color: #f6ad55;">
                <h3>Pendientes</h3>
                <div class="number" style="color: #f6ad55;"><?php echo $estadisticas['pendientes']; ?></div>
            </div>
            
            <div class="stat-card" style="border-left-color: #f56565;">
                <h3>Rechazados</h3>
                <div class="number" style="color: #f56565;"><?php echo $estadisticas['rechazados']; ?></div>
            </div>
        </div>
        
        <!-- Barra de acciones y filtros -->
        <div class="actions-bar">
            <form class="search-box" method="GET" action="">
                <input 
                    type="text" 
                    name="buscar" 
                    placeholder="Buscar por nombre del campista o padre..."
                    value="<?php echo htmlspecialchars($termino_busqueda); ?>">
                <button type="submit" class="btn btn-primary">🔍 Buscar</button>
                <?php if ($termino_busqueda): ?>
                    <a href="lista.php" class="btn btn-secondary">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Filtros por estado -->
        <div class="actions-bar">
            <div class="filter-tabs">
                <a href="lista.php" class="filter-tab <?php echo !$filtro_estado ? 'active' : ''; ?>">
                    Todos
                </a>
                <a href="lista.php?estado=<?php echo INSCRIPCION_APROBADO; ?>" 
                   class="filter-tab <?php echo $filtro_estado === INSCRIPCION_APROBADO ? 'active' : ''; ?>">
                    Aprobados
                </a>
                <a href="lista.php?estado=<?php echo INSCRIPCION_PENDIENTE; ?>" 
                   class="filter-tab <?php echo $filtro_estado === INSCRIPCION_PENDIENTE ? 'active' : ''; ?>">
                    Pendientes
                </a>
                <a href="lista.php?estado=<?php echo INSCRIPCION_RECHAZADO; ?>" 
                   class="filter-tab <?php echo $filtro_estado === INSCRIPCION_RECHAZADO ? 'active' : ''; ?>">
                    Rechazados
                </a>
            </div>
        </div>
        
        <!-- Tabla de campistas -->
        <div class="table-container">
            <?php if (count($campistas) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Campista</th>
                        <th>Edad</th>
                        <th>Género</th>
                        <th>Padre/Tutor</th>
                        <th>Estado</th>
                        <th>Fecha Inscripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campistas as $campista): ?>
                    <tr>
                        <td><strong>#<?php echo $campista['id_campista']; ?></strong></td>
                        <td>
                            <strong><?php echo htmlspecialchars($campista['nombre'] . ' ' . $campista['apellido']); ?></strong>
                        </td>
                        <td><?php echo $campista['edad']; ?> años</td>
                        <td><?php echo ucfirst($campista['genero']); ?></td>
                        <td><?php echo htmlspecialchars($campista['nombre_padre'] . ' ' . $campista['apellido_padre']); ?></td>
                        <td>
                            <span class="estado-badge estado-<?php echo $campista['estado_inscripcion']; ?>">
                                <?php echo ucfirst($campista['estado_inscripcion']); ?>
                            </span>
                        </td>
                        <td><?php echo formatear_fecha($campista['fecha_inscripcion']); ?></td>
                        <td>
                            <div class="actions-cell">
                                <a href="detalle.php?id=<?php echo $campista['id_campista']; ?>" 
                                   class="btn btn-primary btn-small" title="Ver detalles">
                                    👁️
                                </a>
                                <a href="editar.php?id=<?php echo $campista['id_campista']; ?>" 
                                   class="btn btn-secondary btn-small" title="Editar">
                                    ✏️
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-data">
                <div class="no-data-icon">📭</div>
                <h3>No se encontraron campistas</h3>
                <p>
                    <?php if ($termino_busqueda): ?>
                        No hay resultados para "<?php echo htmlspecialchars($termino_busqueda); ?>"
                    <?php else: ?>
                        Aún no hay campistas registrados para este año
                    <?php endif; ?>
                </p>
                <br>
                <a href="crear.php" class="btn btn-primary">➕ Agregar Primer Campista</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>