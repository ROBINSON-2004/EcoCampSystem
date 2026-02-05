<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/AsistenciaControlador.php';
require_once RUTA_MODELOS . '/Grupo.php';

Sesion::requerirTipoUsuario([TIPO_ADMINISTRADOR, TIPO_TRABAJADOR, TIPO_CONSEJERO]);

$controlador = new AsistenciaControlador();
$datos_usuario = Sesion::obtenerDatosUsuario();

// Obtener grupos disponibles
$grupo_modelo = new Grupo();
$grupos = $grupo_modelo->leerTodos('activo', ANIO_CAMPAMENTO_ACTUAL);

// Fecha y grupo seleccionados
$fecha_seleccionada = $_GET['fecha'] ?? date('Y-m-d');
$grupo_seleccionado = $_GET['grupo'] ?? null;

// Procesar registro individual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'registrar') {
        $_POST['registrado_por'] = $datos_usuario['id'];
        $resultado = $controlador->registrar($_POST);
        Sesion::establecerMensaje($resultado['exito'] ? 'exito' : 'error', $resultado['mensaje']);
        header("Location: registro-diario.php?fecha=$fecha_seleccionada" . ($grupo_seleccionado ? "&grupo=$grupo_seleccionado" : ""));
        exit();
    } elseif ($_POST['accion'] === 'marcar_todos') {
        $campistas_sin_registro = $controlador->obtenerSinRegistro($fecha_seleccionada, $grupo_seleccionado);
        $ids = array_column($campistas_sin_registro, 'id_campista');
        $resultado = $controlador->registrarMasivo($ids, $fecha_seleccionada, $datos_usuario['id']);
        Sesion::establecerMensaje($resultado['exito'] ? 'exito' : 'error', $resultado['mensaje']);
        header("Location: registro-diario.php?fecha=$fecha_seleccionada" . ($grupo_seleccionado ? "&grupo=$grupo_seleccionado" : ""));
        exit();
    }
}

// Obtener asistencias y campistas sin registro
$asistencias = $controlador->obtenerPorFecha($fecha_seleccionada, $grupo_seleccionado);
$sin_registro = $controlador->obtenerSinRegistro($fecha_seleccionada, $grupo_seleccionado);

$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencia - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.5rem; }
        .header-links { display: flex; gap: 15px; }
        .header-links a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; transition: 0.3s; }
        .header-links a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 1400px; margin: 30px auto; padding: 0 30px; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #efe; color: #3c3; border-left: 4px solid #3c3; }
        .filters { background: white; padding: 20px 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px; display: flex; gap: 20px; align-items: end; flex-wrap: wrap; }
        .filter-group { flex: 1; min-width: 200px; }
        .filter-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .filter-group input, .filter-group select { width: 100%; padding: 10px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; transition: 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-success { background: #48bb78; color: white; }
        .btn-warning { background: #f6ad55; color: white; }
        .btn-danger { background: #f56565; color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn:hover { transform: translateY(-2px); }
        .section { background: white; padding: 25px 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; }
        .section-header h2 { color: #333; font-size: 1.5rem; }
        .campista-item { background: #f8f9fa; padding: 15px 20px; border-radius: 8px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .campista-info h4 { color: #333; margin-bottom: 5px; }
        .campista-info p { color: #666; font-size: 0.9rem; }
        .estado-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .estado-presente { background: #d4edda; color: #155724; }
        .estado-ausente { background: #f8d7da; color: #721c24; }
        .estado-tardanza { background: #fff3cd; color: #856404; }
        .empty { text-align: center; padding: 40px; color: #999; }
        .quick-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        @media (max-width: 768px) { .filters { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Registro de Asistencia</h1>
        <div class="header-links">
            <a href="<?php echo URL_BASE; ?>/panel.php">← Dashboard</a>
            <a href="reportes.php">📊 Reportes</a>
            <a href="<?php echo URL_BASE; ?>/logout.php">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?php echo $mensaje['contenido']; ?></div>
        <?php endif; ?>
        
        <div class="filters">
            <div class="filter-group">
                <label>Fecha</label>
                <input type="date" id="fecha" value="<?php echo $fecha_seleccionada; ?>" 
                       onchange="filtrar()">
            </div>
            <div class="filter-group">
                <label>Grupo</label>
                <select id="grupo" onchange="filtrar()">
                    <option value="">Todos los grupos</option>
                    <?php foreach ($grupos as $grupo): ?>
                        <option value="<?php echo $grupo['id_grupo']; ?>" 
                                <?php echo $grupo_seleccionado == $grupo['id_grupo'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($grupo['nombre_grupo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-primary" onclick="filtrar()">🔍 Filtrar</button>
        </div>
        
        <?php if (count($sin_registro) > 0): ?>
        <div class="section">
            <div class="section-header">
                <h2>Campistas Sin Registro (<?php echo count($sin_registro); ?>)</h2>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="accion" value="marcar_todos">
                    <button type="submit" class="btn btn-success" 
                            onclick="return confirm('¿Marcar todos como presentes?');">
                        ✅ Marcar Todos Presentes
                    </button>
                </form>
            </div>
            
            <?php foreach ($sin_registro as $campista): ?>
                <div class="campista-item">
                    <div class="campista-info">
                        <h4><?php echo htmlspecialchars($campista['nombre'] . ' ' . $campista['apellido']); ?></h4>
                        <p><?php echo $campista['edad']; ?> años - <?php echo ucfirst($campista['genero']); ?></p>
                    </div>
                    
                    <form method="POST" class="quick-actions">
                        <input type="hidden" name="accion" value="registrar">
                        <input type="hidden" name="id_campista" value="<?php echo $campista['id_campista']; ?>">
                        <input type="hidden" name="fecha_asistencia" value="<?php echo $fecha_seleccionada; ?>">
                        <input type="hidden" name="hora_entrada" value="<?php echo date('H:i:s'); ?>">
                        
                        <button type="submit" name="estado_asistencia" value="presente" class="btn btn-success">✅ Presente</button>
                        <button type="submit" name="estado_asistencia" value="ausente" class="btn btn-danger">❌ Ausente</button>
                        <button type="submit" name="estado_asistencia" value="tardanza" class="btn btn-warning">⏰ Tardanza</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <div class="section-header">
                <h2>Asistencia Registrada (<?php echo count($asistencias); ?>)</h2>
            </div>
            
            <?php if (count($asistencias) > 0): ?>
                <?php foreach ($asistencias as $asistencia): ?>
                    <div class="campista-item">
                        <div class="campista-info">
                            <h4><?php echo htmlspecialchars($asistencia['nombre'] . ' ' . $asistencia['apellido']); ?></h4>
                            <p>
                                <?php echo $asistencia['edad']; ?> años - 
                                Entrada: <?php echo $asistencia['hora_entrada'] ? date('H:i', strtotime($asistencia['hora_entrada'])) : 'N/A'; ?>
                                <?php if ($asistencia['observaciones']): ?>
                                    - 📝 <?php echo htmlspecialchars($asistencia['observaciones']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <span class="estado-badge estado-<?php echo $asistencia['estado_asistencia']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $asistencia['estado_asistencia'])); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">
                    <p>No hay asistencia registrada para esta fecha</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function filtrar() {
            const fecha = document.getElementById('fecha').value;
            const grupo = document.getElementById('grupo').value;
            let url = 'registro-diario.php?fecha=' + fecha;
            if (grupo) url += '&grupo=' + grupo;
            window.location.href = url;
        }
    </script>
</body>
</html>