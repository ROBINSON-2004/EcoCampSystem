<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once __DIR__ . '/../../../utilidades/sesion.php';
require_once __DIR__ . '/../../../utilidades/funciones.php';
require_once __DIR__ . '/../../../controladores/PadreControlador.php';

// Requerir autenticación de administrador
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// Verificar ID
if (!isset($_GET['id']) || !es_numero_valido($_GET['id'])) {
    Sesion::establecerMensaje('error', 'ID de padre no válido.');
    header('Location: lista.php');
    exit();
}

$id_padre = (int)$_GET['id'];

// Obtener datos del padre
$controlador = new PadreControlador();
$padre = $controlador->obtenerPorId($id_padre);

if (!$padre) {
    Sesion::establecerMensaje('error', 'Padre no encontrado.');
    header('Location: lista.php');
    exit();
}

$error = '';
$exito = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['id_padre'] = $id_padre;
    $_POST['id_usuario'] = $padre['id_usuario'];
    
    $resultado = $controlador->actualizar($_POST);
    
    if ($resultado['exito']) {
        Sesion::establecerMensaje('exito', $resultado['mensaje']);
        header('Location: detalle.php?id=' . $id_padre);
        exit();
    } else {
        $error = $resultado['mensaje'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Padre - <?php echo NOMBRE_SITIO; ?></title>
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
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✏️ Editar Padre</h1>
        <a href="detalle.php?id=<?php echo $id_padre; ?>">← Volver al detalle</a>
    </div>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo URL_BASE; ?>/panel.php">Inicio</a> / 
            <a href="lista.php">Padres</a> / 
            <a href="detalle.php?id=<?php echo $id_padre; ?>">Detalle</a> /
            <span>Editar</span>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2>Editar Información del Padre</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    
                    <!-- Información Personal -->
                    <div class="form-section">
                        <h3>📋 Información Personal</h3>
                        
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
                                    value="<?php echo htmlspecialchars($padre['nombre']); ?>">
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
                                    value="<?php echo htmlspecialchars($padre['apellido']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono">
                                    Teléfono <span class="required">*</span>
                                </label>
                                <input 
                                    type="tel" 
                                    id="telefono" 
                                    name="telefono" 
                                    required
                                    value="<?php echo htmlspecialchars($padre['telefono']); ?>">
                                <small>Ingresa solo números, mínimo 7 dígitos</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="ocupacion">Ocupación</label>
                                <input 
                                    type="text" 
                                    id="ocupacion" 
                                    name="ocupacion"
                                    value="<?php echo htmlspecialchars($padre['ocupacion'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="estado">
                                    Estado <span class="required">*</span>
                                </label>
                                <select id="estado" name="estado" required>
                                    <option value="activo" <?php echo $padre['estado'] === 'activo' ? 'selected' : ''; ?>>
                                        Activo
                                    </option>
                                    <option value="inactivo" <?php echo $padre['estado'] === 'inactivo' ? 'selected' : ''; ?>>
                                        Inactivo
                                    </option>
                                    <option value="suspendido" <?php echo $padre['estado'] === 'suspendido' ? 'selected' : ''; ?>>
                                        Suspendido
                                    </option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Correo Electrónico</label>
                                <input 
                                    type="text" 
                                    value="<?php echo htmlspecialchars($padre['correo_electronico']); ?>"
                                    disabled
                                    style="background: #f0f0f0; cursor: not-allowed;">
                                <small>El correo no puede ser modificado</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información de Contacto -->
                    <div class="form-section">
                        <h3>📍 Información de Contacto</h3>
                        
                        <div class="form-group full-width">
                            <label for="direccion">Dirección</label>
                            <textarea 
                                id="direccion" 
                                name="direccion"><?php echo htmlspecialchars($padre['direccion'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="ciudad">Ciudad</label>
                                <input 
                                    type="text" 
                                    id="ciudad" 
                                    name="ciudad"
                                    value="<?php echo htmlspecialchars($padre['ciudad'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="codigo_postal">Código Postal</label>
                                <input 
                                    type="text" 
                                    id="codigo_postal" 
                                    name="codigo_postal"
                                    value="<?php echo htmlspecialchars($padre['codigo_postal'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            💾 Guardar Cambios
                        </button>
                        <a href="detalle.php?id=<?php echo $id_padre; ?>" class="btn btn-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>