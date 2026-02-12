<?php
/**
 * ============================================
 * FORMULARIOS DISPONIBLES PARA PADRES
 * ============================================
 */

// 1. CARGA DE DEPENDENCIAS (Subimos 3 niveles para llegar a la raíz)
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_CONFIG . '/conexion.php';
require_once RUTA_MODELOS . '/Padre.php';
require_once RUTA_MODELOS . '/Campista.php';

// 2. CONTROL DE SESIÓN - Si falla, te enviará al login automáticamente
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_PADRE);

$datos_usuario = Sesion::obtenerDatosUsuario();
$db = (new Conexion())->obtenerConexion();

// 3. OBTENER ID DEL PADRE
$padre_modelo = new Padre();
$padre_modelo->id_usuario = $datos_usuario['id'];
$padre_modelo->leerPorIdUsuario();

// 4. CONSULTA DE DOCUMENTOS PENDIENTES (SOLO HIJOS APROBADOS)
$sql = "SELECT c.id_campista, c.nombre AS hijo_nombre, c.apellido AS hijo_apellido,
               f.id_formulario, f.titulo AS form_titulo, f.archivo_url AS plantilla_pdf
        FROM campistas c
        CROSS JOIN formularios f
        LEFT JOIN formularios_campistas fc ON f.id_formulario = fc.id_formulario 
             AND c.id_campista = fc.id_campista
        WHERE c.id_padre = :id_p 
          AND c.estado_inscripcion = 'aprobado' -- SOLO HIJOS APROBADOS
          AND f.estado = 'activo' 
          AND fc.id_formulario IS NULL";

$stmt = $db->prepare($sql);
$stmt->execute([':id_p' => $padre_modelo->id_padre]);
$pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formularios Pendientes | <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/padre.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="header">
        <h1>📑 Gestión de Formularios</h1>
        <div class="user-info">
            <span><?php echo htmlspecialchars($datos_usuario['nombre']); ?></span>
            <a href="../dashboard.php" class="btn-logout" style="background: #4a5568;">Volver al Panel</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome-section">
            <h2>Documentos de Consentimiento ✍️</h2>
            <p>Para que tus hijos participen, debes descargar, firmar y subir estos documentos.</p>
        </div>

        <?php if (count($pendientes) > 0): ?>
            <div class="hijos-grid">
                <?php foreach ($pendientes as $doc): ?>
                    <div class="hijo-card" style="border-left: 5px solid #ed8936;">
                        <span style="font-size: 0.8rem; color: #718096; text-transform: uppercase; font-weight: bold;">
                            Hijo(a): <?php echo htmlspecialchars($doc['hijo_nombre']); ?>
                        </span>
                        <h4 style="margin: 10px 0;"><?php echo htmlspecialchars($doc['form_titulo']); ?></h4>
                        
                        <div style="margin-top: 15px; display: flex; flex-direction: column; gap: 10px;">
                            <a href="<?php echo URL_UPLOADS; ?>/formularios/<?php echo $doc['plantilla_pdf']; ?>" 
                               target="_blank" class="btn-small" style="background: #edf2f7; color: #2d3748; text-align: center; text-decoration: none; padding: 8px; border-radius: 4px;">
                                📥 1. Descargar Plantilla
                            </a>

                            <a href="firmar.php?id_f=<?php echo $doc['id_formulario']; ?>&id_c=<?php echo $doc['id_campista']; ?>" 
                               class="btn btn-primary" style="text-align: center; text-decoration: none; background: #3182ce; padding: 10px; border-radius: 4px; color: white; font-weight: bold;">
                                ✍️ 2. Subir Firmado
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state" style="background: white; padding: 50px; border-radius: 12px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <div style="font-size: 4rem; margin-bottom: 20px;">✅</div>
                <h3>¡Todo al día!</h3>
                <p>No tienes formularios pendientes de firma para tus hijos.</p>
                <a href="../dashboard.php" class="btn-primary" style="display: inline-block; margin-top: 20px; text-decoration: none;">Regresar al Inicio</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>