<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once __DIR__ . '/../../../utilidades/sesion.php';
require_once __DIR__ . '/../../../utilidades/funciones.php';
require_once __DIR__ . '/../../../controladores/CampistaControlador.php';

// Requerir autenticación de administrador
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// Verificar ID
if (!isset($_GET['id']) || !es_numero_valido($_GET['id'])) {
    Sesion::establecerMensaje('error', 'ID de campista no válido.');
    header('Location: lista.php');
    exit();
}

$id_campista = (int)$_GET['id'];

// Obtener datos del campista
$controlador = new CampistaControlador();
$campista = $controlador->obtenerPorId($id_campista);

if (!$campista) {
    Sesion::establecerMensaje('error', 'Campista no encontrado.');
    header('Location: lista.php');
    exit();
}

// Procesar acciones (cambiar estado, eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'cambiar_estado' && isset($_POST['nuevo_estado'])) {
        $resultado = $controlador->cambiarEstado($id_campista, $_POST['nuevo_estado']);
        Sesion::establecerMensaje($resultado['exito'] ? 'exito' : 'error', $resultado['mensaje']);
        header('Location: detalle.php?id=' . $id_campista);
        exit();
    } elseif ($_POST['accion'] === 'eliminar') {
        $resultado = $controlador->eliminar($id_campista);
        if ($resultado['exito']) {
            Sesion::establecerMensaje('exito', $resultado['mensaje']);
            header('Location: lista.php');
            exit();
        } else {
            Sesion::establecerMensaje('error', $resultado['mensaje']);
        }
    }
}

$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Campista - <?php echo NOMBRE_SITIO; ?></title>
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
        
        .estado-aprobado { background: #d4edda; color: #155724; }
        .estado-pendiente { background: #fff3cd; color: #856404; }
        .estado-rechazado { background: #f8d7da; color: #721c24; }
        .estado-retirado { background: #e2e3e5; color: #383d41; }
        
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
        .btn-warning { background: #f6ad55; color: white; }
        
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
        .actions-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .estado-selector {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 15px;
        }
        
        .estado-selector select {
            padding: 8px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
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
        <h1>👦 Detalle del Campista</h1>
        <a href="lista.php">← Volver a la lista</a>
    </div>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo URL_BASE; ?>/panel.php">Inicio</a> / 
            <a href="lista.php">Campistas</a> / 
            <span>Detalle</span>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje['tipo'] === 'exito' ? 'success' : 'error'; ?>">
                <?php echo $mensaje['contenido']; ?>
            </div>
        <?php endif; ?>
        
        <!-- Información del Campista -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h2><?php echo htmlspecialchars($campista['nombre'] . ' ' . $campista['apellido']); ?></h2>
                    <p style="color: #666; margin-top: 5px;">ID: #<?php echo $campista['id_campista']; ?></p>
                </div>
                <span class="estado-badge estado-<?php echo $campista['estado_inscripcion']; ?>">
                    <?php echo ucfirst($campista['estado_inscripcion']); ?>
                </span>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Fecha de Nacimiento</div>
                        <div class="info-value">
                            <?php echo formatear_fecha($campista['fecha_nacimiento']); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Edad</div>
                        <div class="info-value">
                            <?php echo $campista['edad']; ?> años
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Género</div>
                        <div class="info-value">
                            <?php echo ucfirst($campista['genero']); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Año de Inscripción</div>
                        <div class="info-value">
                            <?php echo $campista['anio_inscripcion']; ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Fecha de Inscripción</div>
                        <div class="info-value">
                            <?php echo formatear_fecha($campista['fecha_inscripcion'], true); ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($campista['notas_especiales'])): ?>
                <div style="margin-top: 25px;">
                    <div class="info-label">Notas Especiales</div>
                    <div class="info-value" style="margin-top: 10px; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($campista['notas_especiales'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Información del Padre/Tutor -->
        <div class="card">
            <div class="card-header">
                <h2>👨‍👩‍👧 Padre/Tutor Responsable</h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nombre Completo</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($campista['nombre_padre'] . ' ' . $campista['apellido_padre']); ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Correo Electrónico</div>
                        <div class="info-value">
                            <a href="mailto:<?php echo htmlspecialchars($campista['correo_padre']); ?>" style="color: #667eea;">
                                <?php echo htmlspecialchars($campista['correo_padre']); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Teléfono</div>
                        <div class="info-value">
                            <a href="tel:<?php echo htmlspecialchars($campista['telefono_padre']); ?>" style="color: #667eea;">
                                <?php echo htmlspecialchars($campista['telefono_padre'] ?? 'No registrado'); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Ver Perfil</div>
                        <div class="info-value">
                            <a href="../padres/detalle.php?id=<?php echo $campista['id_padre']; ?>" class="btn btn-secondary" style="padding: 6px 15px; font-size: 0.9rem;">
                                Ver Detalles del Padre
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información Médica -->
        <div class="card">
            <div class="card-header">
                <h2>🏥 Información Médica</h2>
            </div>
            <div class="card-body">
                <div class="empty-state">
                    <p>No se ha registrado información médica para este campista</p>
                    <p style="margin-top: 10px; color: #ccc;">Esta funcionalidad estará disponible próximamente</p>
                </div>
            </div>
        </div>
        
        <!-- Cambiar Estado -->
        <div class="card">
            <div class="card-header">
                <h2>🔄 Cambiar Estado de Inscripción</h2>
            </div>
            <div class="card-body">
                <form method="POST" class="estado-selector">
                    <input type="hidden" name="accion" value="cambiar_estado">
                    <label for="nuevo_estado" style="font-weight: 500;">Seleccionar nuevo estado:</label>
                    <select id="nuevo_estado" name="nuevo_estado">
                        <option value="<?php echo INSCRIPCION_PENDIENTE; ?>" <?php echo $campista['estado_inscripcion'] === INSCRIPCION_PENDIENTE ? 'selected' : ''; ?>>
                            Pendiente
                        </option>
                        <option value="<?php echo INSCRIPCION_APROBADO; ?>" <?php echo $campista['estado_inscripcion'] === INSCRIPCION_APROBADO ? 'selected' : ''; ?>>
                            Aprobado
                        </option>
                        <option value="<?php echo INSCRIPCION_RECHAZADO; ?>" <?php echo $campista['estado_inscripcion'] === INSCRIPCION_RECHAZADO ? 'selected' : ''; ?>>
                            Rechazado
                        </option>
                        <option value="<?php echo INSCRIPCION_RETIRADO; ?>" <?php echo $campista['estado_inscripcion'] === INSCRIPCION_RETIRADO ? 'selected' : ''; ?>>
                            Retirado
                        </option>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        Actualizar Estado
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Acciones -->
        <div class="card">
            <div class="card-header">
                <h2>⚙️ Acciones</h2>
            </div>
            <div class="card-body">
                <div class="actions-group">
                    <a href="editar.php?id=<?php echo $id_campista; ?>" class="btn btn-primary">
                        ✏️ Editar Información
                    </a>
                    
                    <form method="POST" style="display: inline;" 
                          onsubmit="return confirm('¿Estás seguro de retirar a este campista? Esta acción cambiará su estado a RETIRADO.');">
                        <input type="hidden" name="accion" value="eliminar">
                        <button type="submit" class="btn btn-danger">
                            🚫 Retirar Campista
                        </button>
                    </form>
                    
                    <a href="lista.php" class="btn btn-secondary">
                        ← Volver a la lista
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>