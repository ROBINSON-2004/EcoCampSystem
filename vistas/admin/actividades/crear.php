<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/ActividadControlador.php';

Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

$error = '';
$datos_formulario = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new ActividadControlador();
    $resultado = $controlador->crear($_POST);
    
    if ($resultado['exito']) {
        Sesion::establecerMensaje('exito', $resultado['mensaje']);
        header('Location: lista.php');
        exit();
    } else {
        $error = $resultado['mensaje'];
        $datos_formulario = $_POST;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Actividad - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.5rem; }
        .header a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; transition: 0.3s; }
        .header a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 900px; margin: 30px auto; padding: 0 30px; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #fee; color: #c33; border-left: 4px solid #c33; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .card-header { padding: 25px 30px; border-bottom: 2px solid #f0f0f0; }
        .card-header h2 { color: #333; font-size: 1.8rem; }
        .card-body { padding: 30px; }
        .form-section { margin-bottom: 30px; }
        .form-section h3 { color: #333; font-size: 1.2rem; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group label .required { color: #e53e3e; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; font-family: inherit; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: #667eea; }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-group small { display: block; margin-top: 5px; color: #666; font-size: 0.85rem; }
        .btn { padding: 12px 25px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; transition: 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn:hover { transform: translateY(-2px); }
        .form-actions { display: flex; gap: 15px; margin-top: 30px; }
        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>➕ Crear Nueva Actividad</h1>
        <a href="lista.php">← Volver</a>
    </div>
    
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Información de la Actividad</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    
                    <div class="form-section">
                        <h3>📋 Información Básica</h3>
                        
                        <div class="form-group full-width">
                            <label>Nombre de la Actividad <span class="required">*</span></label>
                            <input type="text" name="nombre_actividad" required
                                   value="<?php echo htmlspecialchars($datos_formulario['nombre_actividad'] ?? ''); ?>"
                                   placeholder="Ej: Natación, Manualidades, Senderismo">
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Descripción</label>
                            <textarea name="descripcion"
                                      placeholder="Describe brevemente la actividad..."><?php echo htmlspecialchars($datos_formulario['descripcion'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tipo de Actividad <span class="required">*</span></label>
                                <select name="tipo_actividad" required>
                                    <option value="">Seleccione...</option>
                                    <option value="deportiva" <?php echo (isset($datos_formulario['tipo_actividad']) && $datos_formulario['tipo_actividad'] === 'deportiva') ? 'selected' : ''; ?>>Deportiva</option>
                                    <option value="artistica" <?php echo (isset($datos_formulario['tipo_actividad']) && $datos_formulario['tipo_actividad'] === 'artistica') ? 'selected' : ''; ?>>Artística</option>
                                    <option value="educativa" <?php echo (isset($datos_formulario['tipo_actividad']) && $datos_formulario['tipo_actividad'] === 'educativa') ? 'selected' : ''; ?>>Educativa</option>
                                    <option value="recreativa" <?php echo (isset($datos_formulario['tipo_actividad']) && $datos_formulario['tipo_actividad'] === 'recreativa') ? 'selected' : ''; ?>>Recreativa</option>
                                    <option value="otra" <?php echo (isset($datos_formulario['tipo_actividad']) && $datos_formulario['tipo_actividad'] === 'otra') ? 'selected' : ''; ?>>Otra</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Ubicación</label>
                                <input type="text" name="ubicacion"
                                       value="<?php echo htmlspecialchars($datos_formulario['ubicacion'] ?? ''); ?>"
                                       placeholder="Ej: Piscina, Salón de arte">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>⚙️ Configuración</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Duración (minutos)</label>
                                <input type="number" name="duracion_minutos" min="5" max="480"
                                       value="<?php echo htmlspecialchars($datos_formulario['duracion_minutos'] ?? ''); ?>"
                                       placeholder="60">
                                <small>Duración estimada en minutos</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Capacidad Máxima</label>
                                <input type="number" name="capacidad_maxima" min="1" max="100"
                                       value="<?php echo htmlspecialchars($datos_formulario['capacidad_maxima'] ?? ''); ?>"
                                       placeholder="20">
                                <small>Número máximo de participantes</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Edad Mínima</label>
                                <input type="number" name="edad_minima" min="3" max="18"
                                       value="<?php echo htmlspecialchars($datos_formulario['edad_minima'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Edad Máxima</label>
                                <input type="number" name="edad_maxima" min="3" max="18"
                                       value="<?php echo htmlspecialchars($datos_formulario['edad_maxima'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>📝 Detalles Adicionales</h3>
                        
                        <div class="form-group full-width">
                            <label>Materiales Necesarios</label>
                            <textarea name="materiales_necesarios"
                                      placeholder="Lista los materiales o equipos necesarios..."><?php echo htmlspecialchars($datos_formulario['materiales_necesarios'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Instrucciones</label>
                            <textarea name="instrucciones"
                                      placeholder="Instrucciones especiales para realizar la actividad..."><?php echo htmlspecialchars($datos_formulario['instrucciones'] ?? ''); ?></textarea>
                        </div>
                        
                        <input type="hidden" name="estado" value="activo">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">💾 Crear Actividad</button>
                        <a href="lista.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>