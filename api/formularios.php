<?php
/**
 * API para Formularios
 * Endpoints AJAX para operaciones con formularios
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/constantes.php';

require_once RUTA_CONFIG . '/conexion.php';
require_once RUTA_CONTROLADORES . '/FormularioControlador.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'mensaje' => 'No autenticado']);
    exit;
}

$controlador = new FormularioControlador();
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

switch ($accion) {
    
    case 'listar':
        // Obtener filtros
        $filtros = [];
        if (isset($_GET['tipo'])) $filtros['tipo'] = $_GET['tipo'];
        if (isset($_GET['activo'])) $filtros['activo'] = $_GET['activo'];
        if (isset($_GET['obligatorio'])) $filtros['obligatorio'] = $_GET['obligatorio'];
        
        $resultado = $controlador->listar($filtros);
        echo json_encode($resultado);
        break;
    
    case 'obtener':
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'mensaje' => 'ID requerido']);
            exit;
        }
        
        $resultado = $controlador->obtener($_GET['id']);
        echo json_encode($resultado);
        break;
    
    case 'cambiar_estado':
        if (!isset($_POST['id']) || !isset($_POST['estado'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos']);
            exit;
        }
        
        // Verificar permiso de admin
        if ($_SESSION['rol'] !== 'admin') {
            echo json_encode(['success' => false, 'mensaje' => 'Sin permisos']);
            exit;
        }
        
        $resultado = $controlador->cambiarEstado($_POST['id'], $_POST['estado']);
        echo json_encode($resultado);
        break;
    
    case 'asignar_campistas':
        if (!isset($_POST['id_formulario']) || !isset($_POST['campistas'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos']);
            exit;
        }
        
        // Verificar permiso de admin
        if ($_SESSION['rol'] !== 'admin') {
            echo json_encode(['success' => false, 'mensaje' => 'Sin permisos']);
            exit;
        }
        
        $campistas_ids = json_decode($_POST['campistas'], true);
        $resultado = $controlador->asignarACampistas($_POST['id_formulario'], $campistas_ids);
        echo json_encode($resultado);
        break;
    
    case 'asignar_todos':
        if (!isset($_POST['id_formulario'])) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de formulario requerido']);
            exit;
        }
        
        // Verificar permiso de admin
        if ($_SESSION['rol'] !== 'admin') {
            echo json_encode(['success' => false, 'mensaje' => 'Sin permisos']);
            exit;
        }
        
        $resultado = $controlador->asignarATodos($_POST['id_formulario']);
        echo json_encode($resultado);
        break;
    
    case 'seguimiento':
        if (!isset($_GET['id_formulario'])) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de formulario requerido']);
            exit;
        }
        
        $filtros = [];
        if (isset($_GET['firmado'])) $filtros['firmado'] = $_GET['firmado'];
        
        $resultado = $controlador->obtenerSeguimiento($_GET['id_formulario'], $filtros);
        echo json_encode($resultado);
        break;
    
    case 'formularios_padre':
        // Para padres: obtener sus formularios
        if ($_SESSION['rol'] !== 'padre') {
            echo json_encode(['success' => false, 'mensaje' => 'Solo para padres']);
            exit;
        }
        
        $filtros = [];
        if (isset($_GET['firmado'])) $filtros['firmado'] = $_GET['firmado'];
        if (isset($_GET['campista_id'])) $filtros['campista_id'] = $_GET['campista_id'];
        
        $resultado = $controlador->obtenerFormulariosPadre($_SESSION['usuario_id'], $filtros);
        echo json_encode($resultado);
        break;
    
    case 'proximos_vencer':
        $dias = isset($_GET['dias']) ? intval($_GET['dias']) : 3;
        $resultado = $controlador->obtenerProximosVencer($dias);
        echo json_encode($resultado);
        break;
    
    case 'eliminar_asignacion':
        if (!isset($_POST['id_formulario']) || !isset($_POST['id_campista'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos']);
            exit;
        }
        
        // Verificar permiso de admin
        if ($_SESSION['rol'] !== 'admin') {
            echo json_encode(['success' => false, 'mensaje' => 'Sin permisos']);
            exit;
        }
        
        $resultado = $controlador->eliminarAsignacion($_POST['id_formulario'], $_POST['id_campista']);
        echo json_encode($resultado);
        break;
    
    case 'estadisticas_dashboard':
        // Estadísticas generales para el dashboard
        require_once RUTA_MODELOS . '/Formulario.php';
        require_once RUTA_MODELOS . '/FormularioCampista.php';
        
        $database = new Database();
        $db = $database->getConnection();
        
        $formulario = new Formulario($db);
        $formularioCampista = new FormularioCampista($db);
        
        // Obtener estadísticas
        $todos = $formulario->obtenerTodos();
        $total_formularios = count($todos);
        $formularios_activos = count(array_filter($todos, function($f) { return $f['activo'] == 1; }));
        
        // Pendientes del usuario
        $pendientes = 0;
        if ($_SESSION['rol'] === 'padre') {
            $resultado_pendientes = $formularioCampista->obtenerPendientesPadre($_SESSION['usuario_id']);
            $pendientes = $resultado_pendientes['total_pendientes'];
        }
        
        // Próximos a vencer
        $proximos_vencer = $formularioCampista->obtenerProximosVencer(7);
        
        echo json_encode([
            'success' => true,
            'total_formularios' => $total_formularios,
            'formularios_activos' => $formularios_activos,
            'pendientes_usuario' => $pendientes,
            'proximos_vencer' => count($proximos_vencer)
        ]);
        break;
    
    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
        break;
}
?>