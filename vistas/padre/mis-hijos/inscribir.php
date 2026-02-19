<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Padre.php';
require_once RUTA_CONTROLADORES . '/CampistaControlador.php';

// Verificar que sea padre
Sesion::requerirTipoUsuario(TIPO_PADRE);

$datos_usuario = Sesion::obtenerDatosUsuario();

// Obtener ID del padre
$padre_modelo = new Padre();
$padre_modelo->id_usuario = $datos_usuario['id'];
$padre_modelo->leerPorIdUsuario();

$error = '';
$datos_formulario = [];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['id_padre'] = $padre_modelo->id_padre;
    $_POST['estado_inscripcion'] = INSCRIPCION_PENDIENTE; 
    
    $controlador = new CampistaControlador();
    $resultado = $controlador->crear($_POST);
    
    if ($resultado['exito']) {
        // CAMBIO: Mensaje orientado a la ficha médica
        Sesion::establecerMensaje('exito', '¡Hijo inscrito! Ahora, por favor completa la ficha médica para finalizar el proceso.');
        
        // REDIRECCIÓN: Ahora enviamos al usuario a completar_ficha.php
        header('Location: completar_ficha.php?id=' . $resultado['id_campista']);
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
    <title>Inscribir Hijo - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        /* (Se mantienen tus estilos originales) */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; min-height: 100vh; }
        .header { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.5rem; }
        .header a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; transition: all 0.3s; }
        .header a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 900px; margin: 30px auto; padding: 0 30px; }
        .breadcrumb { color: #666; margin-bottom: 20px; font-size: 0.9rem; }
        .breadcrumb a { color: #48bb78; text-decoration: none; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #fee; color: #c33; border-left: 4px solid #c33; }
        .info-box { background: #e3f2fd; padding: 20px; border-radius: 8px; border-left: 4px solid #2196f3; margin-bottom: 25px; }
        .info-box h3 { color: #1565c0; margin-bottom: 10px; }
        .info-box ul { margin-left: 20px; color: #1565c0; }
        .info-box li { margin: 5px 0; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .card-header { padding: 25px 30px; border-bottom: 2px solid #f0f0f0; }
        .card-header h2 { color: #333; font-size: 1.8rem; }
        .card-body { padding: 30px; }
        .form-section { margin-bottom: 30px; }
        .form-section h3 { color: #333; font-size: 1.2rem; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f0f0f0; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group label .required { color: #e53e3e; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; font-family: inherit; }
        .btn { padding: 12px 25px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .form-actions { display: flex; gap: 15px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>➕ Inscribir Nuevo Hijo</h1>
        <a href="lista.php">← Volver</a>
    </div>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo URL_BASE; ?>/vistas/padre/dashboard.php">Inicio</a> / 
            <a href="lista.php">Mis Hijos</a> / 
            <span>Inscribir</span>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="info-box">
            <h3>📋 Próximo Paso</h3>
            <p style="color: #1565c0; margin-bottom: 10px;">Para garantizar la seguridad de tu hijo durante el campamento, el sistema seguirá este orden:</p>
            <ul>
                <li><strong>Paso 1:</strong> Registro de datos básicos (este formulario).</li>
                <li><strong>Paso 2:</strong> Llenado de la <strong>Ficha Médica</strong> (se abrirá automáticamente al terminar).</li>
                <li><strong>Paso 3:</strong> Revisión y aprobación por parte del administrador.</li>
            </ul>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>Datos del Campista</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-section">
                        <h3>👤 Información Básica</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre">Nombre <span class="required">*</span></label>
                                <input type="text" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($datos_formulario['nombre'] ?? ''); ?>" placeholder="Ej: Juan">
                            </div>
                            <div class="form-group">
                                <label for="apellido">Apellido <span class="required">*</span></label>
                                <input type="text" id="apellido" name="apellido" required value="<?php echo htmlspecialchars($datos_formulario['apellido'] ?? ''); ?>" placeholder="Ej: Pérez">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha_nacimiento">Fecha de Nacimiento <span class="required">*</span></label>
                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($datos_formulario['fecha_nacimiento'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="genero">Género <span class="required">*</span></label>
                                <select id="genero" name="genero" required>
                                    <option value="">Seleccione...</option>
                                    <option value="masculino" <?php echo (isset($datos_formulario['genero']) && $datos_formulario['genero'] === 'masculino') ? 'selected' : ''; ?>>Masculino</option>
                                    <option value="femenino" <?php echo (isset($datos_formulario['genero']) && $datos_formulario['genero'] === 'femenino') ? 'selected' : ''; ?>>Femenino</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>📝 Información Adicional</h3>
                        <div class="form-group">
                            <label for="notas_especiales">Notas Especiales</label>
                            <textarea id="notas_especiales" name="notas_especiales" placeholder="Cualquier información relevante que el campamento deba conocer (opcional)"><?php echo htmlspecialchars($datos_formulario['notas_especiales'] ?? ''); ?></textarea>
                        </div>
                        <input type="hidden" name="anio_inscripcion" value="<?php echo date('Y'); ?>">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            💾 Continuar a Ficha Médica
                        </button>
                        <a href="lista.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>