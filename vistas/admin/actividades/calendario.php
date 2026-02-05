<?php
require_once __DIR__ . '/../../../config/constantes.php';

require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/ActividadControlador.php';
require_once RUTA_MODELOS . '/Actividad.php';
require_once RUTA_MODELOS . '/Grupo.php';

Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

$controlador = new ActividadControlador();
$datos_usuario = Sesion::obtenerDatosUsuario();

// Mes y año actual o seleccionado
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
$grupo_filtro = $_GET['grupo'] ?? null;

// Procesar programación de actividad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'programar') {
        $_POST['id_responsable'] = $datos_usuario['id'];
        $resultado = $controlador->programar($_POST);
        Sesion::establecerMensaje($resultado['exito'] ? 'exito' : 'error', $resultado['mensaje']);
        header("Location: calendario.php?mes=$mes&anio=$anio");
        exit();
    } elseif ($_POST['accion'] === 'cancelar' && isset($_POST['id_programada'])) {
        $resultado = $controlador->cancelarProgramada($_POST['id_programada']);
        Sesion::establecerMensaje($resultado['exito'] ? 'exito' : 'error', $resultado['mensaje']);
        header("Location: calendario.php?mes=$mes&anio=$anio");
        exit();
    }
}

// Obtener actividades y grupos
$actividad_modelo = new Actividad();
$grupo_modelo = new Grupo();
$actividades = $actividad_modelo->leerTodas(null, 'activo');
$grupos = $grupo_modelo->leerTodos('activo', ANIO_CAMPAMENTO_ACTUAL);

// Obtener actividades programadas del mes
$fecha_inicio = "$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
$fecha_fin = date('Y-m-t', strtotime($fecha_inicio));
$programadas = $controlador->obtenerProgramadas($fecha_inicio, $fecha_fin, $grupo_filtro);

// Organizar por fecha
$actividades_por_fecha = [];
foreach ($programadas as $prog) {
    $fecha = $prog['fecha_actividad'];
    if (!isset($actividades_por_fecha[$fecha])) {
        $actividades_por_fecha[$fecha] = [];
    }
    $actividades_por_fecha[$fecha][] = $prog;
}

// Generar calendario
$primer_dia = date('N', strtotime($fecha_inicio)); // 1=Lunes, 7=Domingo
$total_dias = date('t', strtotime($fecha_inicio));

$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Actividades - <?php echo NOMBRE_SITIO; ?></title>
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
        .controls { background: white; padding: 20px 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .nav-mes { display: flex; align-items: center; gap: 15px; }
        .nav-mes a { text-decoration: none; color: #667eea; font-size: 1.5rem; padding: 5px 10px; }
        .nav-mes h2 { color: #333; margin: 0; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; transition: 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn:hover { transform: translateY(-2px); }
        .calendario { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
        .calendario-header { display: grid; grid-template-columns: repeat(7, 1fr); background: #667eea; color: white; }
        .dia-semana { padding: 15px; text-align: center; font-weight: 600; }
        .calendario-body { display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: #e0e0e0; }
        .dia { background: white; min-height: 120px; padding: 10px; position: relative; }
        .dia.vacio { background: #f8f9fa; }
        .dia.hoy { background: #e3f2fd; }
        .dia-numero { font-weight: bold; color: #333; margin-bottom: 5px; }
        .actividad-item { background: #667eea; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; margin: 3px 0; cursor: pointer; }
        .actividad-item:hover { background: #5568d3; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; max-width: 600px; margin: 50px auto; border-radius: 12px; padding: 30px; max-height: 80vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-close { font-size: 2rem; cursor: pointer; color: #999; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px; }
        @media (max-width: 768px) { .calendario-body { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>📅 Calendario de Actividades</h1>
        <div class="header-links">
            <a href="lista.php">← Actividades</a>
            <a href="<?php echo URL_BASE; ?>/panel.php">Dashboard</a>
        </div>
    </div>
    
    <div class="container">
        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?php echo $mensaje['contenido']; ?></div>
        <?php endif; ?>
        
        <div class="controls">
            <div class="nav-mes">
                <a href="?mes=<?php echo $mes == 1 ? 12 : $mes-1; ?>&anio=<?php echo $mes == 1 ? $anio-1 : $anio; ?>">◄</a>
                <h2><?php 
                    $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                    echo $meses[$mes] . ' ' . $anio; 
                ?></h2>
                <a href="?mes=<?php echo $mes == 12 ? 1 : $mes+1; ?>&anio=<?php echo $mes == 12 ? $anio+1 : $anio; ?>">►</a>
            </div>
            <button class="btn btn-primary" onclick="abrirModal()">➕ Programar Actividad</button>
        </div>
        
        <div class="calendario">
            <div class="calendario-header">
                <div class="dia-semana">Lun</div>
                <div class="dia-semana">Mar</div>
                <div class="dia-semana">Mié</div>
                <div class="dia-semana">Jue</div>
                <div class="dia-semana">Vie</div>
                <div class="dia-semana">Sáb</div>
                <div class="dia-semana">Dom</div>
            </div>
            
            <div class="calendario-body">
                <?php 
                // Días vacíos antes del primer día
                for ($i = 1; $i < $primer_dia; $i++) {
                    echo '<div class="dia vacio"></div>';
                }
                
                // Días del mes
                for ($dia = 1; $dia <= $total_dias; $dia++) {
                    $fecha_actual = "$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-" . str_pad($dia, 2, '0', STR_PAD_LEFT);
                    $es_hoy = $fecha_actual == date('Y-m-d');
                    echo '<div class="dia ' . ($es_hoy ? 'hoy' : '') . '">';
                    echo '<div class="dia-numero">' . $dia . '</div>';
                    
                    if (isset($actividades_por_fecha[$fecha_actual])) {
                        foreach ($actividades_por_fecha[$fecha_actual] as $act) {
                            echo '<div class="actividad-item" title="' . htmlspecialchars($act['nombre_actividad']) . '">';
                            echo date('H:i', strtotime($act['hora_inicio'])) . ' ' . htmlspecialchars(truncar_texto($act['nombre_actividad'], 15));
                            echo '</div>';
                        }
                    }
                    
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- Modal Programar -->
    <div id="modalProgramar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Programar Actividad</h2>
                <span class="modal-close" onclick="cerrarModal()">×</span>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="programar">
                
                <div class="form-group">
                    <label>Actividad *</label>
                    <select name="id_actividad" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($actividades as $act): ?>
                            <option value="<?php echo $act['id_actividad']; ?>">
                                <?php echo htmlspecialchars($act['nombre_actividad']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Grupo *</label>
                    <select name="id_grupo" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($grupos as $g): ?>
                            <option value="<?php echo $g['id_grupo']; ?>">
                                <?php echo htmlspecialchars($g['nombre_grupo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Fecha *</label>
                    <input type="date" name="fecha_actividad" required 
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Hora Inicio *</label>
                        <input type="time" name="hora_inicio" required>
                    </div>
                    <div class="form-group">
                        <label>Hora Fin *</label>
                        <input type="time" name="hora_fin" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea name="observaciones" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Programar</button>
            </form>
        </div>
    </div>
    
    <script>
        function abrirModal() {
            document.getElementById('modalProgramar').style.display = 'block';
        }
        function cerrarModal() {
            document.getElementById('modalProgramar').style.display = 'none';
        }
        window.onclick = function(e) {
            const modal = document.getElementById('modalProgramar');
            if (e.target == modal) cerrarModal();
        }
    </script>
</body>
</html>