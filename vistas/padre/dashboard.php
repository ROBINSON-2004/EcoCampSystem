<?php
/**
 * ============================================
 * PORTAL PARA PADRES - EcoCampSystem (V. 2026)
 * Rutas Corregidas con URL_BASE
 * ============================================
 */
require_once __DIR__ . '/../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Padre.php';
require_once RUTA_MODELOS . '/Campista.php';
require_once RUTA_CONFIG . '/conexion.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_PADRE);

$datos_usuario = Sesion::obtenerDatosUsuario();
$db = (new Conexion())->obtenerConexion();

// 1. OBTENER PERFIL DEL PADRE
$padre_modelo = new Padre();
$padre_modelo->id_usuario = $datos_usuario['id'];
$padre_modelo->leerPorIdUsuario(); 

// 2. LÓGICA DE FORMULARIOS PENDIENTES
$sql_pendientes = "SELECT COUNT(*) 
                    FROM campistas c
                    CROSS JOIN formularios f
                    LEFT JOIN formularios_campistas fc ON f.id_formulario = fc.id_formulario 
                        AND c.id_campista = fc.id_campista
                    WHERE c.id_padre = :id_p 
                      AND c.estado_inscripcion = 'aprobado' 
                      AND f.estado = 'activo' 
                      AND fc.id_formulario IS NULL";

$stmt_p = $db->prepare($sql_pendientes);
$stmt_p->execute([':id_p' => $padre_modelo->id_padre]);
$total_pendientes = $stmt_p->fetchColumn();

// 3. OBTENER HIJOS Y ESTADO MÉDICO
$sql_hijos = "SELECT c.*, 
              (SELECT COUNT(*) FROM informacion_medica im WHERE im.id_campista = c.id_campista) as tiene_ficha
              FROM campistas c 
              WHERE c.id_padre = :id_p AND c.estado_inscripcion != 'retirado'";

$stmt_h = $db->prepare($sql_hijos);
$stmt_h->execute([':id_p' => $padre_modelo->id_padre]);
$mis_hijos = $stmt_h->fetchAll(PDO::FETCH_ASSOC);

$total_hijos = count($mis_hijos);
$fichas_pendientes = 0;
foreach ($mis_hijos as $h) { if($h['tiene_ficha'] == 0) $fichas_pendientes++; }

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
        .alert-health { background: #fff5f5; border-left: 5px solid #e53e3e; color: #c53030; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .badge-pending { background: #f6ad55; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; }
        .badge-success { background: #48bb78; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; }
        .btn-action-sm { font-size: 0.85rem; padding: 5px 12px; border-radius: 5px; text-decoration: none; font-weight: 600; display: inline-block; }
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
            <h2>¡Hola, <?php echo htmlspecialchars($datos_usuario['nombre']); ?>! 👋</h2>
            <p>Gestiona todo lo relacionado con el campamento 2026.</p>
        </div>
        
        <?php if ($fichas_pendientes > 0): ?>
            <div class="alert-health">
                <strong>⚠️ Pendiente:</strong> Tienes hijos sin ficha médica registrada.
            </div>
        <?php endif; ?>
        
        <div class="actions-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <a href="<?php echo URL_BASE; ?>/vistas/padre/mis-hijos/inscribir.php" class="action-card">
                <div class="icon">➕</div>
                <h3>Inscribir</h3>
                <p>Nuevo registro</p>
            </a>
            
            <a href="<?php echo URL_BASE; ?>/vistas/padre/formularios/disponibles.php" class="action-card">
                <div class="icon">📄</div>
                <?php if ($total_pendientes > 0): ?><span class="badge-danger"><?php echo $total_pendientes; ?></span><?php endif; ?>
                <h3>Formularios</h3>
                <p>Documentos legales</p>
            </a>

            <a href="<?php echo URL_BASE; ?>/vistas/padre/notificaciones.php" class="action-card">
                <div class="icon">📧</div>
                <h3>Avisos</h3>
                <p>Mensajería</p>
            </a>
        </div>
        
        <div class="mis-hijos-section">
            <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>👨‍👩‍👧‍👦 Mis Hijos Registrados</h2>
            </div>
            
            <div class="hijos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <?php foreach ($mis_hijos as $hijo): ?>
                    <div class="hijo-card" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        <h4><?php echo htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellido']); ?></h4>
                        
                        <div style="margin: 15px 0;">
                            <?php if ($hijo['tiene_ficha'] > 0): ?>
                                <span class="badge-success">✅ Salud Completa</span>
                            <?php else: ?>
                                <span class="badge-pending">⚠️ Salud Pendiente</span>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; gap: 10px; border-top: 1px solid #edf2f7; padding-top: 15px;">
                            <a href="<?php echo URL_BASE; ?>/vistas/padre/mis-hijos/editar.php?id=<?php echo $hijo['id_campista']; ?>" class="btn-action-sm" style="background: #edf2f7; color: #4a5568;">Editar</a>
                            
                            <?php if ($hijo['tiene_ficha'] == 0): ?>
                                <a href="<?php echo URL_BASE; ?>/vistas/padre/mis-hijos/completar_ficha.php?id=<?php echo $hijo['id_campista']; ?>" class="btn-action-sm" style="background: #f6ad55; color: white;">Llenar Ficha</a>
                            <?php else: ?>
                                <a href="<?php echo URL_BASE; ?>/vistas/padre/mis-hijos/detalle.php?id=<?php echo $hijo['id_campista']; ?>" class="btn-action-sm" style="background: #3182ce; color: white;">Ver Detalle</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>