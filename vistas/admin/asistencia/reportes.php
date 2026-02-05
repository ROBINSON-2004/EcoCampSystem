<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/AsistenciaControlador.php';
require_once RUTA_MODELOS . '/Campista.php';
require_once RUTA_MODELOS . '/Grupo.php';

Sesion::requerirTipoUsuario([TIPO_ADMINISTRADOR, TIPO_TRABAJADOR, TIPO_CONSEJERO]);

$controlador = new AsistenciaControlador();

// Parámetros de filtro
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d'); // Hoy
$id_campista = $_GET['campista'] ?? null;
$id_grupo = $_GET['grupo'] ?? null;

// Obtener estadísticas generales
$estadisticas = $controlador->obtenerEstadisticas($fecha_inicio, $fecha_fin);

// Calcular porcentajes
$total = $estadisticas['total_registros'];
$porcentaje_presentes = $total > 0 ? round(($estadisticas['presentes'] / $total) * 100, 1) : 0;
$porcentaje_ausentes = $total > 0 ? round(($estadisticas['ausentes'] / $total) * 100, 1) : 0;
$porcentaje_tardanzas = $total > 0 ? round(($estadisticas['tardanzas'] / $total) * 100, 1) : 0;

// Obtener listas para filtros
$campista_modelo = new Campista();
$grupo_modelo = new Grupo();
$campistas = $campista_modelo->leerTodos(INSCRIPCION_APROBADO, ANIO_CAMPAMENTO_ACTUAL);
$grupos = $grupo_modelo->leerTodos('activo', ANIO_CAMPAMENTO_ACTUAL);

// Historial si se seleccionó un campista
$historial = [];
if ($id_campista) {
    $historial = $controlador->obtenerHistorial($id_campista, $fecha_inicio, $fecha_fin);
}

// Obtener asistencias detalladas por fecha
$conexion = new Conexion();
$db = $conexion->obtenerConexion();

$query = "
    SELECT 
        DATE(a.fecha_asistencia) AS fecha,
        COUNT(*) AS total,
        SUM(CASE WHEN a.estado_asistencia = 'presente' THEN 1 ELSE 0 END) AS presentes,
        SUM(CASE WHEN a.estado_asistencia = 'ausente' THEN 1 ELSE 0 END) AS ausentes,
        SUM(CASE WHEN a.estado_asistencia = 'tardanza' THEN 1 ELSE 0 END) AS tardanzas
    FROM asistencia a
    INNER JOIN campistas_grupos cg 
        ON a.id_campista = cg.id_campista
    WHERE a.fecha_asistencia BETWEEN :inicio AND :fin
";

// 👉 Filtro opcional por grupo
if (!empty($id_grupo)) {
    $query .= " AND cg.id_grupo = :grupo AND cg.estado = 'activo'";
}

// 👉 Agrupación y orden correctos
$query .= "
    GROUP BY fecha
    ORDER BY fecha DESC
    LIMIT 30
";

try {
    $stmt = $db->prepare($query);
    $stmt->bindParam(':inicio', $fecha_inicio);
    $stmt->bindParam(':fin', $fecha_fin);

    if (!empty($id_grupo)) {
        $stmt->bindParam(':grupo', $id_grupo, PDO::PARAM_INT);
    }

    $stmt->execute();
    $asistencias_por_fecha = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("❌ Error al generar el reporte de asistencia: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Asistencia - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.5rem; }
        .header-links { display: flex; gap: 15px; }
        .header-links a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; transition: 0.3s; }
        .header-links a:hover { background: rgba(255,255,255,0.2); }
        
        .container { max-width: 1400px; margin: 30px auto; padding: 0 30px; }
        
        .filters { background: white; padding: 20px 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .filters h3 { color: #333; margin-bottom: 20px; }
        .filter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .filter-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 0.9rem; }
        .filter-group input, .filter-group select { width: 100%; padding: 10px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; transition: 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn:hover { transform: translateY(-2px); }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #667eea; }
        .stat-card h3 { color: #666; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 10px; }
        .stat-card .number { color: #333; font-size: 2.5rem; font-weight: bold; margin-bottom: 5px; }
        .stat-card .percentage { color: #999; font-size: 1rem; }
        
        .chart-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .chart-container h3 { color: #333; margin-bottom: 20px; }
        
        .progress-bar { width: 100%; height: 30px; background: #f0f0f0; border-radius: 15px; overflow: hidden; margin: 15px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #48bb78, #38a169); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.9rem; transition: width 0.3s; }
        .progress-fill.ausente { background: linear-gradient(90deg, #f56565, #e53e3e); }
        .progress-fill.tardanza { background: linear-gradient(90deg, #f6ad55, #ed8936); }
        
        .table-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8f9fa; }
        th { padding: 15px; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; }
        td { padding: 15px; border-bottom: 1px solid #f0f0f0; color: #666; }
        tbody tr:hover { background: #f8f9fa; }
        
        .estado-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .estado-presente { background: #d4edda; color: #155724; }
        .estado-ausente { background: #f8d7da; color: #721c24; }
        .estado-tardanza { background: #fff3cd; color: #856404; }
        
        .empty { text-align: center; padding: 40px; color: #999; }
        
        @media print {
            .header-links, .filters, .btn { display: none; }
            .header { background: #667eea; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Reportes de Asistencia</h1>
        <div class="header-links">
            <a href="registro-diario.php">← Registro</a>
            <a href="<?php echo URL_BASE; ?>/panel.php">Dashboard</a>
            <a href="<?php echo URL_BASE; ?>/logout.php">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        
        <!-- Filtros -->
        <div class="filters">
            <h3>🔍 Filtros de Reporte</h3>
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Fecha Fin</label>
                        <input type="date" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Campista (Opcional)</label>
                        <select name="campista">
                            <option value="">Todos los campistas</option>
                            <?php foreach ($campistas as $c): ?>
                                <option value="<?php echo $c['id_campista']; ?>" 
                                        <?php echo $id_campista == $c['id_campista'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Grupo (Opcional)</label>
                        <select name="grupo">
                            <option value="">Todos los grupos</option>
                            <?php foreach ($grupos as $g): ?>
                                <option value="<?php echo $g['id_grupo']; ?>"
                                        <?php echo $id_grupo == $g['id_grupo'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($g['nombre_grupo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">📊 Generar Reporte</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Imprimir</button>
            </form>
        </div>
        
        <!-- Estadísticas Generales -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Registros</h3>
                <div class="number"><?php echo number_format($total); ?></div>
                <div class="percentage">Periodo: <?php echo formatear_fecha($fecha_inicio); ?> - <?php echo formatear_fecha($fecha_fin); ?></div>
            </div>
            
            <div class="stat-card" style="border-left-color: #48bb78;">
                <h3>Presentes</h3>
                <div class="number" style="color: #48bb78;"><?php echo number_format($estadisticas['presentes']); ?></div>
                <div class="percentage"><?php echo $porcentaje_presentes; ?>% del total</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #f56565;">
                <h3>Ausentes</h3>
                <div class="number" style="color: #f56565;"><?php echo number_format($estadisticas['ausentes']); ?></div>
                <div class="percentage"><?php echo $porcentaje_ausentes; ?>% del total</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #f6ad55;">
                <h3>Tardanzas</h3>
                <div class="number" style="color: #f6ad55;"><?php echo number_format($estadisticas['tardanzas']); ?></div>
                <div class="percentage"><?php echo $porcentaje_tardanzas; ?>% del total</div>
            </div>
        </div>
        
        <!-- Gráficos de Progreso -->
        <div class="chart-container">
            <h3>📈 Distribución de Asistencia</h3>
            
            <div>
                <strong>Presentes:</strong>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $porcentaje_presentes; ?>%;">
                        <?php echo $porcentaje_presentes; ?>%
                    </div>
                </div>
            </div>
            
            <div>
                <strong>Ausentes:</strong>
                <div class="progress-bar">
                    <div class="progress-fill ausente" style="width: <?php echo $porcentaje_ausentes; ?>%;">
                        <?php echo $porcentaje_ausentes; ?>%
                    </div>
                </div>
            </div>
            
            <div>
                <strong>Tardanzas:</strong>
                <div class="progress-bar">
                    <div class="progress-fill tardanza" style="width: <?php echo $porcentaje_tardanzas; ?>%;">
                        <?php echo $porcentaje_tardanzas; ?>%
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Historial por Campista -->
        <?php if ($id_campista && count($historial) > 0): ?>
        <div class="table-container">
            <h3>📋 Historial del Campista</h3>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Hora Entrada</th>
                        <th>Hora Salida</th>
                        <th>Observaciones</th>
                        <th>Registrado Por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $h): ?>
                    <tr>
                        <td><?php echo formatear_fecha($h['fecha_asistencia']); ?></td>
                        <td>
                            <span class="estado-badge estado-<?php echo $h['estado_asistencia']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $h['estado_asistencia'])); ?>
                            </span>
                        </td>
                        <td><?php echo $h['hora_entrada'] ? date('H:i', strtotime($h['hora_entrada'])) : '-'; ?></td>
                        <td><?php echo $h['hora_salida'] ? date('H:i', strtotime($h['hora_salida'])) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($h['observaciones'] ?? '-'); ?></td>
                        <td>
                            <?php 
                            if ($h['registrado_por_nombre']) {
                                echo htmlspecialchars($h['registrado_por_nombre'] . ' ' . $h['registrado_por_apellido']);
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Resumen por Fecha -->
        <?php if (count($asistencias_por_fecha) > 0): ?>
        <div class="table-container">
            <h3>📅 Resumen por Fecha</h3>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Presentes</th>
                        <th>Ausentes</th>
                        <th>Tardanzas</th>
                        <th>% Asistencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($asistencias_por_fecha as $a): ?>
                    <?php 
                    $porcentaje_dia = $a['total'] > 0 ? round(($a['presentes'] / $a['total']) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><strong><?php echo formatear_fecha($a['fecha']); ?></strong></td>
                        <td><?php echo $a['total']; ?></td>
                        <td style="color: #48bb78;"><strong><?php echo $a['presentes']; ?></strong></td>
                        <td style="color: #f56565;"><strong><?php echo $a['ausentes']; ?></strong></td>
                        <td style="color: #f6ad55;"><strong><?php echo $a['tardanzas']; ?></strong></td>
                        <td>
                            <div class="progress-bar" style="width: 100px; height: 20px;">
                                <div class="progress-fill" style="width: <?php echo $porcentaje_dia; ?>%; font-size: 0.75rem;">
                                    <?php echo $porcentaje_dia; ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="table-container">
            <div class="empty">
                <p>No hay datos de asistencia para el periodo seleccionado</p>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</body>
</html>