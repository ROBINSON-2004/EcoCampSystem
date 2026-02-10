<?php
/**
 * Clase Sesion - EcoCampSystem
 * Centraliza la seguridad, el control de roles y la persistencia de datos.
 */
class Sesion {

    /**
     * Inicializa la sesión con el nombre configurado en constantes.php.
     * Se asegura de establecer el nombre antes de iniciar la sesión para evitar conflictos.
     */
    public static function iniciar() {
        if (session_status() == PHP_SESSION_NONE) {
            // Establecer el nombre de la sesión antes de iniciarla es vital para evitar el bucle
            if (defined('NOMBRE_SESION')) {
                session_name(NOMBRE_SESION);
            }
            session_start();
        }
    }

    /**
     * MÉTODO POLIMÓRFICO: Guarda datos del usuario o mensajes flash.
     * - 1 argumento: Guarda el arreglo de datos del usuario.
     * - 2 argumentos: Guarda un mensaje de retroalimentación (tipo, contenido).
     */
    public static function establecer($arg1, $arg2 = null) {
        self::iniciar();
        
        if ($arg2 === null) {
            // Guarda los datos del usuario bajo la llave principal de la sesión
            $_SESSION[NOMBRE_SESION] = $arg1;
            
            // AGREGADO: Normalizar para compatibilidad con módulo de formularios
            if (isset($arg1['id_usuario'])) {
                $_SESSION['id_usuario'] = $arg1['id_usuario'];
                $_SESSION['usuario_id'] = $arg1['id_usuario']; // Alias
            }
            if (isset($arg1['tipo_usuario'])) {
                $_SESSION['tipo_usuario'] = $arg1['tipo_usuario'];
            }
            if (isset($arg1['nombre'])) {
                $_SESSION['nombre'] = $arg1['nombre'];
            }
            if (isset($arg1['apellido'])) {
                $_SESSION['apellido'] = $arg1['apellido'];
            }
            if (isset($arg1['correo_electronico'])) {
                $_SESSION['correo_electronico'] = $arg1['correo_electronico'];
            }
        } else {
            // Llama al método interno para mensajes flash
            self::establecerMensaje($arg1, $arg2);
        }
    }

    /**
     * Verifica si el usuario ha iniciado sesión comprobando la existencia de la llave principal.
     */
    public static function estaAutenticado() {
        self::iniciar();
        return isset($_SESSION[NOMBRE_SESION]) && !empty($_SESSION[NOMBRE_SESION]);
    }

    /**
     * ALIAS: Mantiene compatibilidad con la lógica de index.php.
     */
    public static function estaActiva() {
        return self::estaAutenticado();
    }

    /**
     * Protege una página. Redirige al login si no detecta una sesión válida.
     */
    public static function requerirAutenticacion() {
        if (!self::estaAutenticado()) {
            header('Location: ' . URL_BASE . '/index.php');
            exit();
        }
    }

    /**
     * Retorna el arreglo completo de datos del usuario logueado.
     */
    public static function obtenerDatosUsuario() {
        self::iniciar();
        return $_SESSION[NOMBRE_SESION] ?? null;
    }

    /**
     * Verifica el rol del usuario. 
     * Corregido para usar 'tipo_usuario' en lugar de 'tipo'.
     */
    public static function requerirTipoUsuario($tipo_esperado) {
        self::requerirAutenticacion();
        $datos = self::obtenerDatosUsuario();
        
        // Validación de la llave correcta según tu estructura de base de datos
        $rol_actual = $datos['tipo_usuario'] ?? '';
        
        if ($rol_actual !== $tipo_esperado) {
            self::establecer('error', MSG_ERROR_PERMISOS);
            header('Location: ' . URL_BASE . '/index.php');
            exit();
        }
    }

    /**
     * Guarda un mensaje temporal (flash) que se borrará tras ser leído.
     */
    public static function establecerMensaje($tipo, $contenido) {
        self::iniciar();
        $_SESSION['mensaje'] = [
            'tipo' => $tipo, 
            'contenido' => $contenido
        ];
        
        // AGREGADO: También guardar en formato compatible con módulo de formularios
        $_SESSION['tipo_mensaje'] = $tipo;
        $_SESSION['mensaje_texto'] = $contenido;
    }

    /**
     * Recupera el mensaje flash y lo elimina de la sesión.
     */
    public static function obtenerMensaje() {
        self::iniciar();
        if (isset($_SESSION['mensaje'])) {
            $mensaje = $_SESSION['mensaje'];
            unset($_SESSION['mensaje']);
            // Limpiar también las variables compatibles
            unset($_SESSION['tipo_mensaje']);
            unset($_SESSION['mensaje_texto']);
            return $mensaje;
        }
        return null;
    }

    /**
     * Limpia todos los datos y destruye la sesión y su cookie.
     */
    public static function cerrar() {
        self::iniciar();
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    // ========================================
    // MÉTODOS ADICIONALES PARA COMPATIBILIDAD
    // ========================================
    
    /**
     * Obtener ID del usuario actual
     * NUEVO: Para compatibilidad con módulo de formularios
     */
    public static function obtenerIdUsuario() {
        self::iniciar();
        $datos = self::obtenerDatosUsuario();
        return $datos['id_usuario'] ?? ($_SESSION['id_usuario'] ?? null);
    }
    
    /**
     * Obtener tipo de usuario actual
     * NUEVO: Para compatibilidad con módulo de formularios
     */
    public static function obtenerTipoUsuario() {
        self::iniciar();
        $datos = self::obtenerDatosUsuario();
        return $datos['tipo_usuario'] ?? ($_SESSION['tipo_usuario'] ?? null);
    }
    
    /**
     * Verificar si es administrador
     * NUEVO: Para compatibilidad
     */
    public static function esAdministrador() {
        return self::obtenerTipoUsuario() === TIPO_ADMINISTRADOR;
    }
    
    /**
     * Verificar si es padre
     * NUEVO: Para compatibilidad
     */
    public static function esPadre() {
        return self::obtenerTipoUsuario() === TIPO_PADRE;
    }
    
    /**
     * Verificar si es trabajador
     * NUEVO: Para compatibilidad
     */
    public static function esTrabajador() {
        return self::obtenerTipoUsuario() === TIPO_TRABAJADOR;
    }
    
    /**
     * Verificar si es consejero
     * NUEVO: Para compatibilidad
     */
    public static function esConsejero() {
        return self::obtenerTipoUsuario() === TIPO_CONSEJERO;
    }
}

// ============================================
// FUNCIONES DE COMPATIBILIDAD PARA FORMULARIOS
// ============================================

/**
 * Verificar que hay una sesión activa
 * Función wrapper para compatibilidad con módulo de formularios
 */
function verificarSesion() {
    Sesion::requerirAutenticacion();
    
    // Normalizar variables de sesión para compatibilidad
    Sesion::iniciar();
    $datos = Sesion::obtenerDatosUsuario();
    if ($datos) {
        if (isset($datos['id_usuario']) && !isset($_SESSION['usuario_id'])) {
            $_SESSION['usuario_id'] = $datos['id_usuario'];
        }
        if (isset($datos['tipo_usuario']) && !isset($_SESSION['tipo_usuario'])) {
            $_SESSION['tipo_usuario'] = $datos['tipo_usuario'];
        }
    }
}

/**
 * Verificar que el usuario tiene el permiso requerido
 * Función wrapper para compatibilidad con módulo de formularios
 * 
 * @param string $tipo_requerido Tipo de usuario requerido
 */
function verificarPermiso($tipo_requerido) {
    Sesion::requerirTipoUsuario($tipo_requerido);
}

/**
 * Obtener ID del usuario actual
 * @return int|null
 */
function obtenerIdUsuarioActual() {
    return Sesion::obtenerIdUsuario();
}

/**
 * Obtener tipo de usuario actual
 * @return string|null
 */
function obtenerTipoUsuarioActual() {
    return Sesion::obtenerTipoUsuario();
}

/**
 * Verificar si el usuario está autenticado
 * @return bool
 */
function estaAutenticado() {
    return Sesion::estaAutenticado();
}

/**
 * Verificar si el usuario es administrador
 * @return bool
 */
function esAdministrador() {
    return Sesion::esAdministrador();
}

/**
 * Verificar si el usuario es padre
 * @return bool
 */
function esPadre() {
    return Sesion::esPadre();
}

/**
 * Verificar si el usuario es trabajador
 * @return bool
 */
function esTrabajador() {
    return Sesion::esTrabajador();
}

/**
 * Verificar si el usuario es consejero
 * @return bool
 */
function esConsejero() {
    return Sesion::esConsejero();
}

/**
 * Obtener datos completos del usuario actual
 * @return array|null
 */
function obtenerDatosUsuario() {
    return Sesion::obtenerDatosUsuario();
}

/**
 * Establecer mensaje de sesión
 * @param string $tipo Tipo de mensaje (success, error, warning, info)
 * @param string $mensaje Texto del mensaje
 */
function establecerMensaje($tipo, $mensaje) {
    Sesion::establecerMensaje($tipo, $mensaje);
}

/**
 * Obtener y limpiar mensaje de sesión
 * @return array|null ['tipo' => string, 'contenido' => string]
 */
function obtenerMensaje() {
    return Sesion::obtenerMensaje();
}

/**
 * Cerrar sesión del usuario
 */
function cerrarSesion() {
    Sesion::cerrar();
    header('Location: ' . URL_BASE . '/index.php');
    exit();
}
?>