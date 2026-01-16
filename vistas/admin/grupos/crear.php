<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once __DIR__ . '/../../../utilidades/sesion.php';
require_once __DIR__ . '/../../../utilidades/funciones.php';
require_once __DIR__ . '/../../../controladores/GrupoControlador.php';

Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// Obtener lista de consejeros
$conexion = new Conexion();
$db = $conexion->obtenerConexion();
$query_consejeros = "SELECT id_usuario, nombre, apellido, correo_electronico 
                     FROM usuarios WHERE tipo_usuario = 'consejero' AND estado = 'activo'";
$stmt = $db->prepare($query_consejeros);
$stmt->execute();
$consejeros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$datos_formulario = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new GrupoControlador();
    $resultado = $controlador->crear($_POST);
    
    if ($resultado['exito']) {
        Sesion::establecerMensaje('exito', $resultado['mensaje']);
        header('Location: detalle.php?id=' . $resultado['id_grupo']);
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
    <title>Crear Grupo - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; min-height: 100vh; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.5rem; }
        .header a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; transition: 0.3s; }
        .header a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 900px; margin: 30px auto; padding: 0 30px; }
        .breadcrumb { color: #666; margin-bottom: 20px; font-size: 0.9rem; }
        .breadcrumb a { color: #667eea; text-decoration: none; }
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
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: 0.3s; font-family: inherit; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-group small { display: block; margin-top: 5px; color: #666; font-size: 0.85rem; }
        .btn { padding: 12px 25px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-block; transition: 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .form-actions { display: flex; gap: 15px; margin-top: 30px; }
        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>➕ Crear Nuevo Grupo</h1>
        <a href="lista.php">← Volver</a>
    </div>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo URL_BASE; ?>/panel.php">Inicio</a> / 
            <a href="lista.php">Grupos</a> / 
            <span>Crear</span>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Información del Grupo</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    
                    <div class="form-section">
                        <h3>📋 Información Básica</h3>
                        
                        <div class="form-group full-width">
                            <label for="nombre_grupo">
                                Nombre del Grupo <span class="required">*</span>
                            </label>
                            <input type="text" id="nombre_grupo" name="nombre_grupo" required
                                   value="<?php echo htmlspecialchars($datos_formulario['nombre_grupo'] ?? ''); ?>"
                                   placeholder="Ej: Grupo Águilas, Los Exploradores, etc.">
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion"
                                      placeholder="Breve descripción del grupo y sus características..."><?php echo htmlspecialchars($datos_formulario['descripcion'] ?? ''); ?></textarea>
                            <small>Opcional - Puedes describir el enfoque o características especiales del grupo</small>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>👦 Restricciones de Edad</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edad_minima">Edad Mínima</label>
                                <input type="number" id="edad_minima" name="edad_minima" min="3" max="18"
                                       value="<?php echo htmlspecialchars($datos_formulario['edad_minima'] ?? ''); ?>"
                                       placeholder="Ej: 6">
                                <small>Edad mínima permitida para unirse al grupo</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="edad_maxima">Edad Máxima</label>
                                <input type="number" id="edad_maxima" name="edad_maxima" min="3" max="18"
                                       value="<?php echo htmlspecialchars($datos_formulario['edad_maxima'] ?? ''); ?>"
                                       placeholder="Ej: 10">
                                <small>Edad máxima permitida para unirse al grupo</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>⚙️ Configuración</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="capacidad_maxima">Capacidad Máxima</label>
                                <input type="number" id="capacidad_maxima" name="capacidad_maxima" min="5" max="50"
                                       value="<?php echo htmlspecialchars($datos_formulario['capacidad_maxima'] ?? '20'); ?>"
                                       placeholder="20">
                                <small>Número máximo de campistas permitidos</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="id_consejero">Consejero Asignado</label>
                                <select id="id_consejero" name="id_consejero">
                                    <option value="">Sin asignar</option>
                                    <?php foreach ($consejeros as $consejero): ?>
                                        <option value="<?php echo $consejero['id_usuario']; ?>"
                                                <?php echo (isset($datos_formulario['id_consejero']) && $datos_formulario['id_consejero'] == $consejero['id_usuario']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($consejero['nombre'] . ' ' . $consejero['apellido']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small>Puedes asignar un consejero ahora o después</small>
                            </div>
                        </div>
                        
                        <input type="hidden" name="anio_campamento" value="<?php echo ANIO_CAMPAMENTO_ACTUAL; ?>">
                        <input type="hidden" name="estado" value="activo">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">💾 Crear Grupo</button>
                        <a href="lista.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>