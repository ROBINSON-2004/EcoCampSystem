<?php
/**
 * Utilidades de Permisos - EcoCampSystem
 * Control de acceso basado en tipo de usuario
 */

require_once __DIR__ . '/config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';

/**
 * Verificar permiso de acceso por tipo de usuario
 * Esta es la función principal usada por el módulo de formularios
 * 
 * @param string $tipo_requerido Tipo de usuario requerido (administrador, padre, trabajador, consejero)
 */
function verificarPermiso($tipo_requerido) {
    Sesion::iniciar();
    
    $tipo_usuario = Sesion::obtenerTipoUsuario();
    
    if (!$tipo_usuario || $tipo_usuario !== $tipo_requerido) {
        Sesion::establecerMensaje('error', MSG_ERROR_PERMISOS);
        header('Location: ' . URL_BASE . '/index.php');
        exit();
    }
}

/**
 * Verificar si el usuario tiene uno de varios permisos
 * 
 * @param array $tipos_permitidos Array de tipos de usuario permitidos
 * @return bool
 */
function tienePermiso($tipos_permitidos) {
    Sesion::iniciar();
    
    $tipo_usuario = Sesion::obtenerTipoUsuario();
    
    if (!$tipo_usuario) {
        return false;
    }
    
    return in_array($tipo_usuario, $tipos_permitidos);
}

/**
 * Verificar si puede acceder a datos de un campista
 * - Administradores: pueden acceder a todos
 * - Padres: solo sus hijos
 * - Trabajadores/Consejeros: campistas de sus grupos
 * 
 * @param int $id_campista
 * @return bool
 */
function puedeAccederCampista($id_campista) {
    $tipo_usuario = Sesion::obtenerTipoUsuario();
    $id_usuario = Sesion::obtenerIdUsuario();
    
    // Administrador puede acceder a todo
    if ($tipo_usuario === TIPO_ADMINISTRADOR) {
        return true;
    }
    
    // Padre solo puede acceder a sus hijos
    if ($tipo_usuario === TIPO_PADRE) {
        return verificarCampistaDelPadre($id_campista, $id_usuario);
    }
    
    // Trabajador/Consejero puede acceder a campistas de sus grupos
    if ($tipo_usuario === TIPO_TRABAJADOR || $tipo_usuario === TIPO_CONSEJERO) {
        return verificarCampistaDelGrupo($id_campista, $id_usuario);
    }
    
    return false;
}

/**
 * Verificar si un campista pertenece a un padre
 * 
 * @param int $id_campista
 * @param int $id_usuario Usuario padre
 * @return bool
 */
function verificarCampistaDelPadre($id_campista, $id_usuario) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT COUNT(*) as total
                  FROM campistas c
                  INNER JOIN padres p ON c.id_padre = p.id_padre
                  WHERE c.id_campista = :id_campista
                  AND p.id_usuario = :id_usuario";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_campista', $id_campista);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Verificar si un campista está en grupos del trabajador/consejero
 * 
 * @param int $id_campista
 * @param int $id_usuario Usuario trabajador/consejero
 * @return bool
 */
function verificarCampistaDelGrupo($id_campista, $id_usuario) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT COUNT(*) as total
                  FROM campistas_grupos cg
                  INNER JOIN grupos g ON cg.id_grupo = g.id_grupo
                  WHERE cg.id_campista = :id_campista
                  AND g.id_consejero = :id_usuario
                  AND cg.estado = 'activo'";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_campista', $id_campista);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Obtener ID del padre del usuario actual
 * (Si el usuario es tipo padre)
 * 
 * @return int|null
 */
function obtenerIdPadre() {
    $tipo_usuario = Sesion::obtenerTipoUsuario();
    $id_usuario = Sesion::obtenerIdUsuario();
    
    if ($tipo_usuario !== TIPO_PADRE || !$id_usuario) {
        return null;
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id_padre FROM padres WHERE id_usuario = :id_usuario";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['id_padre'] : null;
        
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Redirigir según tipo de usuario
 * Útil después de login
 */
function redirigirSegunTipo() {
    $tipo_usuario = Sesion::obtenerTipoUsuario();
    
    switch ($tipo_usuario) {
        case TIPO_ADMINISTRADOR:
            header('Location: ' . URL_BASE . '/vistas/admin/dashboard.php');
            break;
        case TIPO_PADRE:
            header('Location: ' . URL_BASE . '/vistas/padre/dashboard.php');
            break;
        case TIPO_TRABAJADOR:
            header('Location: ' . URL_BASE . '/vistas/trabajador/dashboard.php');
            break;
        case TIPO_CONSEJERO:
            header('Location: ' . URL_BASE . '/vistas/consejero/dashboard.php');
            break;
        default:
            header('Location: ' . URL_BASE . '/index.php');
    }
    exit();
}

/**
 * Verificar permisos múltiples (OR)
 * El usuario debe tener AL MENOS uno de los permisos
 * 
 * @param array $tipos_permitidos
 */
function verificarPermisosMultiples($tipos_permitidos) {
    Sesion::iniciar();
    
    $tipo_usuario = Sesion::obtenerTipoUsuario();
    
    if (!$tipo_usuario || !in_array($tipo_usuario, $tipos_permitidos)) {
        Sesion::establecerMensaje('error', MSG_ERROR_PERMISOS);
        header('Location: ' . URL_BASE . '/index.php');
        exit();
    }
}

/**
 * Obtener nombre del tipo de usuario en español
 * 
 * @param string $tipo
 * @return string
 */
function obtenerNombreTipo($tipo) {
    $nombres = [
        TIPO_ADMINISTRADOR => 'Administrador',
        TIPO_PADRE => 'Padre/Tutor',
        TIPO_TRABAJADOR => 'Trabajador',
        TIPO_CONSEJERO => 'Consejero'
    ];
    
    return $nombres[$tipo] ?? 'Usuario';
}
?>