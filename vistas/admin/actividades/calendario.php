<?php
/**
 * ============================================
 * VISTA: CALENDARIO INTERACTIVO (AUTO-VACIADO)
 * EcoCampSystem 2026 - Robinson Moya
 * ============================================
 */
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/ActividadControlador.php';
require_once RUTA_MODELOS . '/Actividad.php';
require_once RUTA_MODELOS . '/Grupo.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

$controlador = new ActividadControlador();
$datos_usuario = Sesion::obtenerDatosUsuario();

// 1. CONFIGURACIÓN DE TIEMPO
$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
$hoy_str = date('Y-m-d'); // 2026-02-18

// 2. PROCESAR ACCIONES (Programar, Completar, Cancelar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $id_p = isset($_POST['id_programada']) ? (int)$_POST['id_programada'] : null;
    
    if ($_POST['accion'] === 'programar') {
        $_POST['id_responsable'] = $datos_usuario['id'];
        $resultado = $controlador->programar($_POST);
    } elseif ($_POST['accion'] === 'completar') {
        $resultado = $controlador->cambiarEstadoProgramada($id_p, 'completada');
    } elseif ($_POST['accion'] === 'cancelar') {
        $resultado = $controlador->cambiarEstadoProgramada($id_p, 'cancelada');
    }

    if (isset($resultado)) {
        Sesion::establecerMensaje($resultado['exito'] ? 'exito' : 'error', $resultado['mensaje']);
        header("Location: calendario.php?mes=$mes&anio=$anio");
        exit();
    }
}

// 3. OBTENER DATOS
$actividad_modelo = new Actividad();
$grupo_modelo = new Grupo();
$actividades_catalogo = $actividad_modelo->leerTodas(null, 'activo');
$grupos = $grupo_modelo->leerTodos('activo', ANIO_CAMPAMENTO_ACTUAL);

$fecha_inicio = "$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
$fecha_fin = date('Y-m-t', strtotime($fecha_inicio));
$programadas = $controlador->obtenerProgramadas($fecha_inicio, $fecha_fin);

// Organizar por fecha (Solo las que sigan 'programadas')
$actividades_por_fecha = [];
foreach ($programadas as $prog) {
    if ($prog['estado'] === 'programada') {
        $actividades_por_fecha[$prog['fecha_actividad']][] = $prog;
    }
}

$primer_dia = date('N', strtotime($fecha_inicio));
$total_dias = date('t', strtotime($fecha_inicio));
$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario EcoCampSystem</title>
    <style>
        :root { --primary: #667eea; --secondary: #764ba2; --success: #48bb78; --danger: #f56565; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; margin: 0; color: #2d3748; }
        .header { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
        
        /* Alertas */
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(0,0,0,0.1); font-weight: 500; }
        .alert-exito { background: #c6f6d5; color: #22543d; }
        .alert-error { background: #fed7d7; color: #822727; }

        .controls { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: bold; transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--primary); color: white; }

        /* Calendario */
        .calendario { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden; display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: #e2e8f0; border: 1px solid #e2e8f0; }
        .dia-semana { background: var(--primary); color: white; padding: 15px; text-align: center; font-weight: bold; font-size: 0.8rem; }
        .dia { background: white; min-height: 140px; padding: 10px; transition: 0.2s; }
        .dia-hoy { background: #ebf4ff; border: 2px solid var(--primary); }
        .dia-pasado { background: #f1f5f9; opacity: 0.6; }
        .dia-num { font-weight: bold; font-size: 1.1rem; color: #4a5568; margin-bottom: 8px; }
        
        /* Actividades */
        .act-tag { background: var(--primary); color: white; padding: 5px 8px; border-radius: 6px; font-size: 0.75rem; margin-bottom: 4px; border: none; width: 100%; text-align: left; cursor: pointer; display: block; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        .act-tag:hover { background: var(--secondary); transform: scale(1.02); }

        /* Modales */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1000; }
        .modal-card { background: white; max-width: 500px; margin: 50px auto; border-radius: 15px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .detail-row { margin-bottom: 15px; border-bottom: 1px solid #edf2f7; padding-bottom: 8px; }
        .detail-label { font-size: 0.7rem; color: #718096; text-transform: uppercase; font-weight: bold; }
        .detail-value { font-size: 1rem; font-weight: 500; }
    </style>
</head>
<body>

    <div class="header">
        <h1>📅 Planificación EcoCamp</h1>
        <a href="<?php echo URL_BASE; ?>/panel.php" style="color: white; text-decoration: none;">Dashboard</a>
    </div>

    <div class="container">
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje['tipo']; ?>">
                <?php echo $mensaje['contenido']; ?>
            </div>
        <?php endif; ?>

        <div class="controls">
            <div style="display: flex; align-items: center; gap: 20px;">
                <a href="?mes=<?php echo $mes==1?12:$mes-1; ?>&anio=<?php echo $mes==1?$anio-1:$anio; ?>" style="text-decoration:none; font-size:1.5rem;">◀</a>
                <h2 style="min-width: 200px; text-align: center;"><?php echo $meses[$mes] . " " . $anio; ?></h2>
                <a href="?mes=<?php echo $mes==12?1:$mes+1; ?>&anio=<?php echo $mes==12?$anio+1:$anio; ?>" style="text-decoration:none; font-size:1.5rem;">▶</a>
            </div>
            <button class="btn btn-primary" onclick="abrirProgramar()">➕ Programar Actividad</button>
        </div>

        <div class="calendario">
            <?php 
            $d_nombres = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
            foreach($d_nombres as $dn) echo "<div class='dia-semana'>$dn</div>";

            for ($i = 1; $i < $primer_dia; $i++) echo '<div class="dia"></div>';
            
            for ($dia = 1; $dia <= $total_dias; $dia++) {
                $fecha_f = "$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-" . str_pad($dia, 2, '0', STR_PAD_LEFT);
                $es_hoy = ($fecha_f == $hoy_str);
                $es_pasado = ($fecha_f < $hoy_str);
                
                echo '<div class="dia ' . ($es_hoy ? 'dia-hoy' : ($es_pasado ? 'dia-pasado' : '')) . '">';
                echo '<div class="dia-num">' . $dia . '</div>';
                
                // LÓGICA DE VACIADO: Solo mostrar si no es pasado y está programada
                if (isset($actividades_por_fecha[$fecha_f]) && !$es_pasado) {
                    foreach ($actividades_por_fecha[$fecha_f] as $act) {
                        echo '<button class="act-tag" onclick=\'verDetalle(' . json_encode($act) . ')\'>';
                        echo '<strong>' . date('H:i', strtotime($act['hora_inicio'])) . '</strong> ' . htmlspecialchars($act['nombre_actividad']);
                        echo '</button>';
                    }
                }
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <div id="modalDetalle" class="modal">
        <div class="modal-card">
            <h2 id="det-titulo" style="margin-top:0; color:var(--primary);"></h2>
            <div class="detail-row">
                <div class="detail-label">Grupo y Ubicación</div>
                <div id="det-info" class="detail-value"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Responsable</div>
                <div id="det-resp" class="detail-value"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Observaciones</div>
                <div id="det-obs" class="detail-value"></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 25px;">
                <form method="POST">
                    <input type="hidden" name="accion" value="completar">
                    <input type="hidden" name="id_programada" id="id-comp">
                    <button type="submit" class="btn" style="background:var(--success); color:white; width:100%;">✔️ Realizada</button>
                </form>
                <form method="POST">
                    <input type="hidden" name="accion" value="cancelar">
                    <input type="hidden" name="id_programada" id="id-canc">
                    <button type="submit" class="btn" style="background:var(--danger); color:white; width:100%;">❌ Cancelar</button>
                </form>
            </div>
            <button onclick="cerrarDetalle()" style="width:100%; margin-top:10px; padding:10px; border:none; background:#eee; border-radius:8px; cursor:pointer;">Cerrar</button>
        </div>
    </div>

    <div id="modalProgramar" class="modal">
        <div class="modal-card">
            <h3>Programar Nueva Actividad</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="programar">
                <div style="margin-bottom:10px;">
                    <label>Actividad</label>
                    <select name="id_actividad" required style="width:100%; padding:8px;">
                        <?php foreach($actividades_catalogo as $ac): ?>
                            <option value="<?php echo $ac['id_actividad']; ?>"><?php echo $ac['nombre_actividad']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-bottom:10px;">
                    <label>Grupo</label>
                    <select name="id_grupo" required style="width:100%; padding:8px;">
                        <?php foreach($grupos as $g): ?>
                            <option value="<?php echo $g['id_grupo']; ?>"><?php echo $g['nombre_grupo']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-bottom:10px;">
                    <label>Fecha</label>
                    <input type="date" name="fecha_actividad" min="<?php echo $hoy_str; ?>" required style="width:100%; padding:8px;">
                </div>
                <div style="display:flex; gap:10px; margin-bottom:10px;">
                    <input type="time" name="hora_inicio" required style="flex:1; padding:8px;">
                    <input type="time" name="hora_fin" required style="flex:1; padding:8px;">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Guardar Programación</button>
                <button type="button" onclick="cerrarProgramar()" style="width:100%; margin-top:5px; border:none; background:none; cursor:pointer; color:gray;">Cancelar</button>
            </form>
        </div>
    </div>

<script>
    // 1. Función para obtener la fecha de hoy en formato YYYY-MM-DD (Sincronizado a Feb 2026)
    function obtenerFechaHoy() {
        const hoy = new Date();
        const offset = hoy.getTimezoneOffset();
        const fechaLocal = new Date(hoy.getTime() - (offset * 60 * 1000));
        return fechaLocal.toISOString().split('T')[0];
    }

    // 2. Mostrar detalle de actividad existente
    function verDetalle(data) {
        document.getElementById('det-titulo').innerText = data.nombre_actividad;
        document.getElementById('det-info').innerText = data.nombre_grupo + " | " + (data.ubicacion || 'Sin ubicación');
        document.getElementById('det-resp').innerText = data.resp_nombre + " " + data.resp_apellido;
        document.getElementById('det-obs').innerText = data.observaciones || 'Sin notas.';
        document.getElementById('id-comp').value = data.id_actividad_programada;
        document.getElementById('id-canc').value = data.id_actividad_programada;
        document.getElementById('modalDetalle').style.display = 'block';
    }

    function cerrarDetalle() { document.getElementById('modalDetalle').style.display = 'none'; }

    // 3. Abrir programación con validación de fecha mínima
    function abrirProgramar() { 
        const inputFecha = document.querySelector('input[name="fecha_actividad"]');
        if (inputFecha) {
            inputFecha.setAttribute('min', obtenerFechaHoy()); // Bloqueo visual en el calendario del navegador
        }
        document.getElementById('modalProgramar').style.display = 'block'; 
    }

    function cerrarProgramar() { document.getElementById('modalProgramar').style.display = 'none'; }

    // 4. Validación de seguridad antes de enviar el formulario
    document.addEventListener('DOMContentLoaded', function() {
        const formProgramar = document.querySelector('#modalProgramar form');
        
        if (formProgramar) {
            formProgramar.onsubmit = function(e) {
                const inputFecha = document.querySelector('input[name="fecha_actividad"]').value;
                const hoy = obtenerFechaHoy();

                if (inputFecha < hoy) {
                    e.preventDefault(); // Detiene el envío
                    alert("⚠️ Error: No puedes programar actividades para días que ya pasaron.");
                    return false;
                }
            };
        }
    });

    // Cerrar modales al hacer clic fuera
    window.onclick = function(e) { 
        if(e.target.className == 'modal') { cerrarDetalle(); cerrarProgramar(); }
    }
</script>
</body>
</html>