<?php
/**
 * ============================================
 * PROCESADOR DE NOTIFICACIONES - EcoCampSystem
 * ============================================
 */
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Notificacion.php';

// 1. SEGURIDAD: Solo el administrador Robinson puede ejecutar esto
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: centro.php');
    exit();
}

$notificacion = new Notificacion();
$db = (new Conexion())->obtenerConexion();

$accion = $_POST['accion'] ?? '';
$titulo = limpiar_cadena($_POST['titulo']);
$mensaje = limpiar_cadena($_POST['mensaje']);
$emisor_id = $_SESSION['usuario_id'];

try {
    switch ($accion) {
        // HISTORIA 2: Notificar a todos los padres a la vez
        case 'masiva_padres':
            $tipo = 'evento';
            $destinatarios_tipo = 'todos';
            
            // Obtenemos los id_usuario de todos los padres activos [cite: 146, 154]
            $sql = "SELECT id_usuario FROM usuarios WHERE tipo_usuario = 'padre' AND estado = 'activo'";
            $usuarios_ids = $db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            
            break;

        // HISTORIA 3: Notificar agenda al consejero
        case 'agenda_consejero':
            $tipo = 'informativa';
            $destinatarios_tipo = 'especifico';
            $id_consejero = (int)$_POST['id_consejero'];
            $usuarios_ids = [$id_consejero];
            
            break;

        default:
            throw new Exception("Acción de notificación no válida.");
    }

    // 2. CREAR EL REGISTRO BASE [cite: 161]
    $datos_base = [
        'titulo' => $titulo,
        'mensaje' => $mensaje,
        'tipo' => $tipo,
        'enviada_por' => $emisor_id,
        'destinatarios' => $destinatarios_tipo
    ];

    $id_notificacion = $notificacion->crear($datos_base);

    if ($id_notificacion && !empty($usuarios_ids)) {
        // 3. VINCULAR CON LOS USUARIOS [cite: 127]
        $notificacion->vincularUsuarios($id_notificacion, $usuarios_ids);
        
        Sesion::establecerMensaje('exito', 'La notificación ha sido enviada correctamente.');
    } else {
        throw new Exception("No se encontraron destinatarios válidos.");
    }

} catch (Exception $e) {
    Sesion::establecerMensaje('error', 'Error: ' . $e->getMessage());
}

// Redirección de vuelta al centro de control
header('Location: centro.php');
exit();