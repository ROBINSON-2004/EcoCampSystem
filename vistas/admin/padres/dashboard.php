<?php
require_once __DIR__ . '/../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Padre.php';
require_once RUTA_MODELOS . '/Campista.php';

// Verificar que sea padre
Sesion::requerirTipoUsuario(TIPO_PADRE);

// Obtener datos del usuario
$datos_usuario = Sesion::obtenerDatosUsuario();

// Obtener ID del padre
$padre_modelo = new Padre();
$padre_modelo->id_usuario = $datos_usuario['id'];
$padre_modelo->leerPorIdUsuario();

// Obtener hijos del padre
$campista_modelo = new Campista();
$mis_hijos = $campista_modelo->leerPorPadre($padre_modelo->id_padre);

// Calcular estadísticas
$total_hijos = count($mis_hijos);
$hijos_aprobados = 0;
$hijos_pendientes = 0;

foreach ($mis_hijos as $hijo) {
    if ($hijo['estado_inscripcion'] === INSCRIPCION_APROBADO) {
        $hijos_aprobados++;
    } elseif ($hijo['estado_inscripcion'] === INSCRIPCION_PENDIENTE) {
        $hijos_pendientes++;
    }
}

// Obtener mensaje flash
$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/admin.css?v=<?php echo time(); ?>">
    
</head>
<body>
    <div class="header">
        <h1>🏕️ Portal para Padres</h1>
        <div class="user-info">
            <span><?php echo $datos_usuario['nombre'] . ' ' . $datos_usuario['apellido']; ?></span>
            <a href="<?php echo URL_BASE; ?>/logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-section">
            <h2>¡Bienvenido, <?php echo $datos_usuario['nombre']; ?>! 👋</h2>
            <p>Gestiona la inscripción de tus hijos y mantente al día con todas las actividades del campamento</p>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje['tipo'] === 'exito' ? 'success' : 'error'; ?>">
                <?php echo $mensaje['contenido']; ?>
            </div>
        <?php endif; ?>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">👶</div>
                <h3>Mis Hijos</h3>
                <div class="number"><?php echo $total_hijos; ?></div>
            </div>
            
            <div class="stat-card" style="border-left-color: #48bb78;">
                <div class="icon">✅</div>
                <h3>Aprobados</h3>
                <div class="number" style="color: #48bb78;"><?php echo $hijos_aprobados; ?></div>
            </div>
            
            <div class="stat-card" style="border-left-color: #f6ad55;">
                <div class="icon">⏳</div>
                <h3>Pendientes</h3>
                <div class="number" style="color: #f6ad55;"><?php echo $hijos_pendientes; ?></div>
            </div>
        </div>
        
        <!-- Acciones Rápidas -->
        <div class="actions-grid">
            <a href="mis-hijos/lista.php" class="action-card">
                <div class="icon">👶</div>
                <h3>Mis Hijos</h3>
                <p>Ver y administrar tus hijos inscritos</p>
            </a>
            
            <a href="mis-hijos/inscribir.php" class="action-card">
                <div class="icon">➕</div>
                <h3>Inscribir Hijo</h3>
                <p>Inscribe a un nuevo hijo al campamento</p>
            </a>
            
            <a href="formularios/disponibles.php" class="action-card">
                <div class="icon">📄</div>
                <h3>Formularios</h3>
                <p>Firma y envía formularios de consentimiento</p>
            </a>
            
            <a href="notificaciones.php" class="action-card">
                <div class="icon">📧</div>
                <h3>Notificaciones</h3>
                <p>Revisa mensajes y anuncios importantes</p>
            </a>
        </div>
        
        <!-- Mis Hijos -->
        <div class="mis-hijos-section">
            <div class="section-header">
                <h2>👨‍👩‍👧‍👦 Mis Hijos Inscritos</h2>
                <a href="mis-hijos/inscribir.php" class="btn btn-primary">➕ Inscribir Nuevo Hijo</a>
            </div>
            
            <?php if (count($mis_hijos) > 0): ?>
                <div class="hijos-grid">
                    <?php foreach ($mis_hijos as $hijo): ?>
                        <div class="hijo-card">
                            <h4><?php echo htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']); ?></h4>
                            <div class="hijo-info">
                                <strong>Edad:</strong> <?php echo $hijo['edad']; ?> años
                            </div>
                            <div class="hijo-info">
                                <strong>Género:</strong> <?php echo ucfirst($hijo['genero']); ?>
                            </div>
                            <div class="hijo-info">
                                <strong>Año:</strong> <?php echo $hijo['anio_inscripcion']; ?>
                            </div>
                            <span class="estado-badge estado-<?php echo $hijo['estado_inscripcion']; ?>">
                                <?php echo ucfirst($hijo['estado_inscripcion']); ?>
                            </span>
                            <div class="hijo-actions">
                                <a href="mis-hijos/detalle.php?id=<?php echo $hijo['id_campista']; ?>" 
                                   class="btn btn-primary btn-small">
                                    Ver Detalle
                                </a>
                                <a href="mis-hijos/editar.php?id=<?php echo $hijo['id_campista']; ?>" 
                                   class="btn btn-secondary btn-small">
                                    Editar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👶</div>
                    <h3>Aún no tienes hijos inscritos</h3>
                    <p style="margin: 15px 0;">Comienza inscribiendo a tu primer hijo al campamento</p>
                    <a href="mis-hijos/inscribir.php" class="btn btn-primary">
                        ➕ Inscribir Mi Primer Hijo
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>