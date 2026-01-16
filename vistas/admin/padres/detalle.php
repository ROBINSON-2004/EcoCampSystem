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

// Procesar acciones (activar/desactivar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'desactivar') {
        $resultado = $controlador->desactivar($padre['id_usuario']);
        Sesion::establecerMensaje($resultado['exito'] ? 'exito' : 'error', $resultado['mensaje']);
        header('Location: detalle.php?id=' . $id_padre);
        exit();
    } elseif ($_POST['accion'] === 'activar') {
        $resultado = $controlador->activar($padre['id_usuario']);
        Sesion::establecerMensaje($resultado['exito'] ? 'exito' : 'error', $resultado['mensaje']);
        header('Location: detalle.php?id=' . $id_padre);
        exit();
    }
}

$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Padre - <?php echo NOMBRE_SITIO; ?></title>
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
        
        .container { max-width: 1200px; margin: 30px auto; padding: 0 30px; }
        
        .breadcrumb { color: #666; margin-bottom: 20px; font-size: 0.9rem; }
        .breadcrumb a { color: #667eea; text-decoration: none; }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #efe; color: #3c3; border-left: 4px solid #3c3; }
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            color: #333;
            font-size: 1.8rem;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .info-item {
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .info-value {
            color: #333;
            font-size: 1.1rem;
        }
        
        .estado-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .estado-activo { background: #d4edda; color: #155724; }
        .estado-inactivo { background: #f8d7da; color: #721c24; }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn-danger { background: #f56565; color: white; }
        .btn-success { background: #48bb78; color: white; }
        
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
        .actions-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .section-title {
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        @media (max-width: 768px) {
            .info-grid { grid-template-columns: 1fr; }
            .actions-group { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👤 Detalle del Padre</h1>
        <a href="lista.php">← Volver a la lista</a>
    </div>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo URL_BASE; ?>/panel.php">Inicio</a> / 
            <a href="lista.php">Padres</a> / 
            <span>Detalle</span>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje['tipo'] === 'exito' ? 'success' : 'error'; ?>">
                <?php echo $mensaje['contenido']; ?>
            </div>
        <?php endif; ?>
        
        <!-- Información Personal -->
        <div class="card">
            <div class="card-header">
                <h2>📋 Información Personal</h2>
                <span class="estado-badge estado-<?php echo $padre['estado']; ?>">
                    <?php echo ucfirst($padre['estado']); ?>
                </span>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nombre Completo</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($padre['nombre'] . ' ' . $padre['apellido']); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Correo Electrónico</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($padre['correo_electronico']); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Teléfono</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($padre['telefono'] ?? 'No registrado'); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Ocupación</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($padre['ocupacion'] ?? 'No registrado'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información de Contacto -->
        <div class="card">
            <div class="card-header">
                <h2>📍 Información de Contacto</h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Dirección</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($padre['direccion'] ?? 'No registrado'); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Ciudad</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($padre['ciudad'] ?? 'No registrado'); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Código Postal</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($padre['codigo_postal'] ?? 'No registrado'); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Fecha de Registro</div>
                        <div class="info-value">
                            <?php echo formatear_fecha($padre['fecha_registro'], true); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hijos Inscritos -->
        <div class="card">
            <div class="card-header">
                <h2>👶 Hijos Inscritos</h2>
            </div>
            <div class="card-body">
                <div class="empty-state">
                    <p>Este padre aún no tiene hijos inscritos en el campamento</p>
                    <p style="margin-top: 10px; color: #ccc;">La información aparecerá cuando se agreguen campistas</p>
                </div>
            </div>
        </div>
        
        <!-- Acciones -->
        <div class="card">
            <div class="card-header">
                <h2>⚙️ Acciones</h2>
            </div>
            <div class="card-body">
                <div class="actions-group">
                    <a href="editar.php?id=<?php echo $id_padre; ?>" class="btn btn-primary">
                        ✏️ Editar Información
                    </a>
                    
                    <?php if ($padre['estado'] === ESTADO_ACTIVO): ?>
                        <form method="POST" style="display: inline;" 
                              onsubmit="return confirm('¿Estás seguro de desactivar este padre?');">
                            <input type="hidden" name="accion" value="desactivar">
                            <button type="submit" class="btn btn-danger">
                                🚫 Desactivar Padre
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="accion" value="activar">
                            <button type="submit" class="btn btn-success">
                                ✅ Reactivar Padre
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="lista.php" class="btn btn-secondary">
                        ← Volver a la lista
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>