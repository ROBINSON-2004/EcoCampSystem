<?php
/**
 * ============================================
 * PORTAL PARA PADRES - EcoCampSystem
 * ============================================
 */

require_once __DIR__ . '/../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Padre.php';
require_once RUTA_MODELOS . '/Campista.php';
require_once RUTA_CONFIG . '/conexion.php';

// 1. SEGURIDAD Y SESIÓN
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_PADRE);

$datos_usuario = Sesion::obtenerDatosUsuario();
$db = (new Conexion())->obtenerConexion();

// 2. OBTENER PERFIL DEL PADRE
$padre_modelo = new Padre();
$padre_modelo->id_usuario = $datos_usuario['id'];
$padre_modelo->leerPorIdUsuario(); 

// 3. LÓGICA DE FORMULARIOS PENDIENTES (CORREGIDA)
/**
 * Se cambió 'fc.id_registro' por 'fc.id_formulario' para la validación del LEFT JOIN.
 * Si no hay un registro en la tabla formularios_campistas, esa columna será NULL.
 */
// --- LÓGICA DE FORMULARIOS PENDIENTES FILTRADA POR ESTADO ---
$sql_pendientes = "SELECT COUNT(*) 
                   FROM campistas c
                   CROSS JOIN formularios f
                   LEFT JOIN formularios_campistas fc ON f.id_formulario = fc.id_formulario 
                        AND c.id_campista = fc.id_campista
                   WHERE c.id_padre = :id_p 
                     AND c.estado_inscripcion = 'aprobado' -- FILTRO POR HIJO APROBADO
                     AND f.estado = 'activo' 
                     AND fc.id_formulario IS NULL";

$stmt_p = $db->prepare($sql_pendientes);
$stmt_p->execute([':id_p' => $padre_modelo->id_padre]);
$total_pendientes = $stmt_p->fetchColumn();
// 4. ESTADÍSTICAS DE HIJOS
$campista_modelo = new Campista();
$mis_hijos = $campista_modelo->leerPorPadre($padre_modelo->id_padre);

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

$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel | <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/padre.css?v=<?php echo time(); ?>">
    <style>
        .action-card { position: relative; }
        .badge-danger {
            background: #ef4444;
            color: white;
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 0.8rem;
            position: absolute;
            top: 10px;
            right: 10px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            border: 2px solid white;
        }
        .text-alert { color: #dc2626; font-weight: bold; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏕️ Portal para Padres</h1>
        <div class="user-info">
            <span><?php echo htmlspecialchars($datos_usuario['nombre'] . ' ' . $datos_usuario['apellido']); ?></span>
            <a href="<?php echo URL_BASE; ?>/logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-section">
            <h2>¡Bienvenido, <?php echo htmlspecialchars($datos_usuario['nombre']); ?>! 👋</h2>
            <p>Gestiona la inscripción de tus hijos y documentos del campamento.</p>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo ($mensaje['tipo'] === 'exito') ? 'success' : 'error'; ?>">
                <?php echo $mensaje['contenido']; ?>
            </div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">👶</div>
                <h3>Mis Hijos</h3>
                <div class="number"><?php echo $total_hijos; ?></div>
            </div>
            
            <div class="stat-card" style="border-left: 5px solid #48bb78;">
                <div class="icon">✅</div>
                <h3>Aprobados</h3>
                <div class="number" style="color: #48bb78;"><?php echo $hijos_aprobados; ?></div>
            </div>
            
            <div class="stat-card" style="border-left: 5px solid #f6ad55;">
                <div class="icon">⏳</div>
                <h3>Pendientes</h3>
                <div class="number" style="color: #f6ad55;"><?php echo $hijos_pendientes; ?></div>
            </div>
        </div>
        
        <div class="actions-grid">
            <a href="<?php echo URL_BASE; ?>/vistas/padre/mis-hijos/lista.php" class="action-card">
                <div class="icon">👶</div>
                <h3>Mis Hijos</h3>
                <p>Administrar inscripciones</p>
            </a>
            
            <a href="<?php echo URL_BASE; ?>/vistas/padre/mis-hijos/inscribir.php" class="action-card">
                <div class="icon">➕</div>
                <h3>Inscribir</h3>
                <p>Nuevo hijo al campamento</p>
            </a>
            
            <a href="<?php echo URL_BASE; ?>/vistas/padre/formularios/disponibles.php" class="action-card">
                <div class="icon">📄</div>
                <?php if ($total_pendientes > 0): ?>
                    <span class="badge-danger"><?php echo $total_pendientes; ?></span>
                <?php endif; ?>
                <h3>Formularios</h3>
                <?php if ($total_pendientes > 0): ?>
                    <p class="text-alert">⚠️ Faltan firmas obligatorias</p>
                <?php else: ?>
                    <p>Documentos de consentimiento</p>
                <?php endif; ?>
            </a>
            
            <a href="<?php echo URL_BASE; ?>/vistas/padre/notificaciones.php" class="action-card">
                <div class="icon">📧</div>
                <h3>Avisos</h3>
                <p>Mensajes del campamento</p>
            </a>
        </div>
        
        <div class="mis-hijos-section" style="margin-top: 40px;">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2>👨‍👩‍👧‍👦 Tus Hijos Registrados</h2>
                <a href="mis-hijos/inscribir.php" class="btn-primary" style="background: #3182ce; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold;">+ Inscribir Nuevo</a>
            </div>
            
            <?php if ($total_hijos > 0): ?>
                <div class="hijos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                    <?php foreach ($mis_hijos as $hijo): ?>
                        <div class="hijo-card" style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                            <h4 style="margin-top: 0; font-size: 1.2rem;"><?php echo htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']); ?></h4>
                            <p style="color: #4a5568; margin: 10px 0;"><strong>Edad:</strong> <?php echo $hijo['edad']; ?> años</p>
                            <p style="margin: 10px 0;"><strong>Inscripción:</strong> 
                                <span class="estado-badge estado-<?php echo $hijo['estado_inscripcion']; ?>" style="padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: bold;">
                                    <?php echo strtoupper($hijo['estado_inscripcion']); ?>
                                </span>
                            </p>
                            <div class="hijo-actions" style="margin-top: 20px; display: flex; gap: 15px; border-top: 1px solid #edf2f7; padding-top: 15px;">
                                <a href="mis-hijos/detalle.php?id=<?php echo $hijo['id_campista']; ?>" style="color: #3182ce; text-decoration: none; font-size: 0.9rem; font-weight: 600;">Ver Detalle</a>
                                <a href="mis-hijos/editar.php?id=<?php echo $hijo['id_campista']; ?>" style="color: #718096; text-decoration: none; font-size: 0.9rem; font-weight: 600;">Editar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" style="text-align: center; padding: 60px 20px; background: #f7fafc; border: 2px dashed #cbd5e0; border-radius: 15px;">
                    <div style="font-size: 3rem; margin-bottom: 20px;">👶</div>
                    <h3 style="color: #4a5568;">No tienes hijos inscritos todavía</h3>
                    <p style="color: #718096; margin-bottom: 25px;">Registra a tus hijos para que puedan participar en el campamento.</p>
                    <a href="mis-hijos/inscribir.php" class="btn-primary" style="background: #3182ce; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold;">Registrar Mi Primer Hijo</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>