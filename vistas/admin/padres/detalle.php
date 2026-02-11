<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/PadreControlador.php';

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
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/padre.css">
    
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