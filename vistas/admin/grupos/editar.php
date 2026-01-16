<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once __DIR__ . '/../../../utilidades/sesion.php';
require_once __DIR__ . '/../../../utilidades/funciones.php';
require_once __DIR__ . '/../../../controladores/GrupoControlador.php';
require_once __DIR__ . '/../../../config/Conexion.php'; // Asegúrate de incluir la clase Conexion

Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

$controlador = new GrupoControlador();

// 1. Verificar ID del grupo
if (!isset($_GET['id']) || !es_numero_valido($_GET['id'])) {
    Sesion::establecerMensaje('error', 'ID de grupo no válido.');
    header('Location: lista.php');
    exit();
}

$id_grupo = (int)$_GET['id'];

// 2. Obtener datos actuales del grupo
$grupo = $controlador->obtenerPorId($id_grupo);

if (!$grupo) {
    Sesion::establecerMensaje('error', 'El grupo solicitado no existe.');
    header('Location: lista.php');
    exit();
}

// 3. Obtener lista de consejeros activos para el select
$conexion = new Conexion();
$db = $conexion->obtenerConexion();
$query_consejeros = "SELECT id_usuario, nombre, apellido FROM usuarios WHERE tipo_usuario = 'consejero' AND estado = 'activo'";
$stmt = $db->prepare($query_consejeros);
$stmt->execute();
$consejeros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
// Si hay error en el POST, mantenemos lo que el usuario escribió, si no, cargamos lo de la DB
$datos_viejos = $grupo; 

// 4. Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['id_grupo'] = $id_grupo; // Forzamos el ID de la URL
    $resultado = $controlador->actualizar($_POST);
    
    if ($resultado['exito']) {
        Sesion::establecerMensaje('exito', 'Grupo actualizado correctamente.');
        header('Location: detalle.php?id=' . $id_grupo);
        exit();
    } else {
        $error = $resultado['mensaje'];
        $datos_viejos = $_POST; // En caso de error, dejamos los datos que intentó guardar
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Grupo - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        /* Mantengo tus estilos originales que están muy bien */
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
        <h1>📝 Editar Grupo: <?php echo htmlspecialchars($grupo['nombre_grupo']); ?></h1>
        <a href="lista.php">← Volver al listado</a>
    </div>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo URL_BASE; ?>/panel.php">Inicio</a> / 
            <a href="lista.php">Grupos</a> / 
            <span>Editar</span>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Modificar Información</h2>
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
                                   value="<?php echo htmlspecialchars($datos_viejos['nombre_grupo'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($datos_viejos['descripcion'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>👦 Restricciones de Edad</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edad_minima">Edad Mínima</label>
                                <input type="number" id="edad_minima" name="edad_minima" min="3" max="18"
                                       value="<?php echo htmlspecialchars($datos_viejos['edad_minima'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="edad_maxima">Edad Máxima</label>
                                <input type="number" id="edad_maxima" name="edad_maxima" min="3" max="18"
                                       value="<?php echo htmlspecialchars($datos_viejos['edad_maxima'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>⚙️ Configuración y Asignación</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="capacidad_maxima">Capacidad Máxima</label>
                                <input type="number" id="capacidad_maxima" name="capacidad_maxima" min="5" max="50"
                                       value="<?php echo htmlspecialchars($datos_viejos['capacidad_maxima'] ?? '20'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="id_consejero">Consejero Responsable</label>
                                <select id="id_consejero" name="id_consejero">
                                    <option value="">Sin asignar</option>
                                    <?php foreach ($consejeros as $cons): ?>
                                        <option value="<?php echo $cons['id_usuario']; ?>"
                                            <?php echo (isset($datos_viejos['id_consejero']) && $datos_viejos['id_consejero'] == $cons['id_usuario']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cons['nombre'] . ' ' . $cons['apellido']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="estado">Estado del Grupo</label>
                            <select id="estado" name="estado">
                                <option value="activo" <?php echo ($datos_viejos['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                                <option value="inactivo" <?php echo ($datos_viejos['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo (Lleno / Cerrado)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
                        <a href="detalle.php?id=<?php echo $id_grupo; ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>