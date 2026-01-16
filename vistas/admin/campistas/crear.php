<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once __DIR__ . '/../../../utilidades/sesion.php';
require_once __DIR__ . '/../../../utilidades/funciones.php';
require_once __DIR__ . '/../../../controladores/CampistaControlador.php';
require_once __DIR__ . '/../../../controladores/PadreControlador.php';

// Requerir autenticación de administrador
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// Obtener lista de padres para el select
$padre_controlador = new PadreControlador();
$padres = $padre_controlador->listarTodos();

$error = '';
$datos_formulario = [];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new CampistaControlador();
    $resultado = $controlador->crear($_POST);
    
    if ($resultado['exito']) {
        Sesion::establecerMensaje('exito', $resultado['mensaje']);
        header('Location: detalle.php?id=' . $resultado['id_campista']);
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
    <title>Agregar Campista - <?php echo NOMBRE_SITIO; ?></title>
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
        .header a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; transition: all 0.3s; }
        .header a:hover { background: rgba(255,255,255,0.2); }
        
        .container { max-width: 1000px; margin: 30px auto; padding: 0 30px; }
        
        .breadcrumb { color: #666; margin-bottom: 20px; font-size: 0.9rem; }
        .breadcrumb a { color: #667eea; text-decoration: none; }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-error { background: #fee; color: #c33; border-left: 4px solid #c33; }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .card-header {
            padding: 25px 30px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .card-header h2 {
            color: #333;
            font-size: 1.8rem;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group label .required {
            color: #e53e3e;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 0.85rem;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .info-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
            margin-bottom: 20px;
        }
        
        .info-box p {
            color: #1565c0;
            margin: 5px 0;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>➕ Agregar Nuevo Campista</h1>
        <a href="lista.php">← Volver a la lista</a>
    </div>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo URL_BASE; ?>/panel.php">Inicio</a> / 
            <a href="lista.php">Campistas</a> / 
            <span>Agregar</span>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="info-box">
            <p><strong>📋 Información:</strong></p>
            <p>• Todos los campos marcados con (*) son obligatorios</p>
            <p>• La edad se calculará automáticamente según la fecha de nacimiento</p>
            <p>• El estado inicial será "Pendiente" hasta que apruebes la inscripción</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>Información del Campista</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    
                    <!-- Información Básica -->
                    <div class="form-section">
                        <h3>👤 Información Básica</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre">
                                    Nombre <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="nombre" 
                                    name="nombre" 
                                    required
                                    value="<?php echo htmlspecialchars($datos_formulario['nombre'] ?? ''); ?>"
                                    placeholder="Ej: Juan">
                            </div>
                            
                            <div class="form-group">
                                <label for="apellido">
                                    Apellido <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="apellido" 
                                    name="apellido" 
                                    required
                                    value="<?php echo htmlspecialchars($datos_formulario['apellido'] ?? ''); ?>"
                                    placeholder="Ej: Pérez">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha_nacimiento">
                                    Fecha de Nacimiento <span class="required">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="fecha_nacimiento" 
                                    name="fecha_nacimiento" 
                                    required
                                    max="<?php echo date('Y-m-d'); ?>"
                                    value="<?php echo htmlspecialchars($datos_formulario['fecha_nacimiento'] ?? ''); ?>">
                                <small>La edad se calculará automáticamente</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="genero">
                                    Género <span class="required">*</span>
                                </label>
                                <select id="genero" name="genero" required>
                                    <option value="">Seleccione...</option>
                                    <option value="masculino" <?php echo (isset($datos_formulario['genero']) && $datos_formulario['genero'] === 'masculino') ? 'selected' : ''; ?>>
                                        Masculino
                                    </option>
                                    <option value="femenino" <?php echo (isset($datos_formulario['genero']) && $datos_formulario['genero'] === 'femenino') ? 'selected' : ''; ?>>
                                        Femenino
                                    </option>
                                    <option value="otro" <?php echo (isset($datos_formulario['genero']) && $datos_formulario['genero'] === 'otro') ? 'selected' : ''; ?>>
                                        Otro
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="id_padre">
                                    Padre/Tutor <span class="required">*</span>
                                </label>
                                <select id="id_padre" name="id_padre" required>
                                    <option value="">Seleccione un padre/tutor...</option>
                                    <?php foreach ($padres as $padre): ?>
                                        <option value="<?php echo $padre['id_padre']; ?>" 
                                                <?php echo (isset($datos_formulario['id_padre']) && $datos_formulario['id_padre'] == $padre['id_padre']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($padre['nombre'] . ' ' . $padre['apellido'] . ' - ' . $padre['correo_electronico']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small>Selecciona el padre o tutor responsable del campista</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="estado_inscripcion">
                                    Estado de Inscripción <span class="required">*</span>
                                </label>
                                <select id="estado_inscripcion" name="estado_inscripcion" required>
                                    <option value="<?php echo INSCRIPCION_PENDIENTE; ?>" 
                                            <?php echo (!isset($datos_formulario['estado_inscripcion']) || $datos_formulario['estado_inscripcion'] === INSCRIPCION_PENDIENTE) ? 'selected' : ''; ?>>
                                        Pendiente
                                    </option>
                                    <option value="<?php echo INSCRIPCION_APROBADO; ?>"
                                            <?php echo (isset($datos_formulario['estado_inscripcion']) && $datos_formulario['estado_inscripcion'] === INSCRIPCION_APROBADO) ? 'selected' : ''; ?>>
                                        Aprobado
                                    </option>
                                    <option value="<?php echo INSCRIPCION_RECHAZADO; ?>"
                                            <?php echo (isset($datos_formulario['estado_inscripcion']) && $datos_formulario['estado_inscripcion'] === INSCRIPCION_RECHAZADO) ? 'selected' : ''; ?>>
                                        Rechazado
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información Adicional -->
                    <div class="form-section">
                        <h3>📝 Información Adicional</h3>
                        
                        <div class="form-group full-width">
                            <label for="notas_especiales">Notas Especiales</label>
                            <textarea 
                                id="notas_especiales" 
                                name="notas_especiales"
                                placeholder="Ej: Requiere atención especial, alergias conocidas, necesidades específicas..."><?php echo htmlspecialchars($datos_formulario['notas_especiales'] ?? ''); ?></textarea>
                            <small>Cualquier información relevante sobre el campista (opcional)</small>
                        </div>
                        
                        <input type="hidden" name="anio_inscripcion" value="<?php echo ANIO_CAMPAMENTO_ACTUAL; ?>">
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            💾 Guardar Campista
                        </button>
                        <a href="lista.php" class="btn btn-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>