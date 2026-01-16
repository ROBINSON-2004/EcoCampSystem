<?php
/**
 * Clase Sesion
 * Maneja todas las operaciones relacionadas con sesiones de usuario
 */
class Sesion {
    
    /**
     * Inicia la sesión si no está iniciada
     */
    public static function iniciar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(NOMBRE_SESION);
            session_start();
            
            // Regenerar ID de sesión periódicamente para seguridad
            if (!isset($_SESSION['ultima_actividad'])) {
                $_SESSION['ultima_actividad'] = time();
            } else {
                // Regenerar cada 30 minutos
                if (time() - $_SESSION['ultima_actividad'] > 1800) {
                    session_regenerate_id(true);
                    $_SESSION['ultima_actividad'] = time();
                }
            }
        }
    }
    
    /**
     * Establece los datos del usuario en la sesión
     * @param array $datos_usuario Array con los datos del usuario
     */
    public static function establecer($datos_usuario) {
        self::iniciar();
        
        $_SESSION['usuario_id'] = $datos_usuario['id_usuario'];
        $_SESSION['usuario_nombre'] = $datos_usuario['nombre'];
        $_SESSION['usuario_apellido'] = $datos_usuario['apellido'];
        $_SESSION['usuario_correo'] = $datos_usuario['correo_electronico'];
        $_SESSION['usuario_tipo'] = $datos_usuario['tipo_usuario'];
        $_SESSION['sesion_iniciada'] = true;
        $_SESSION['tiempo_inicio'] = time();
        $_SESSION['ultima_actividad'] = time();
        
        // Establecer cookie de recordar sesión si se solicitó
        if (isset($datos_usuario['recordar']) && $datos_usuario['recordar']) {
            setcookie(
                'recordar_sesion',
                $datos_usuario['id_usuario'],
                time() + (86400 * 30), // 30 días
                '/'
            );
        }
    }
    
    /**
     * Verifica si hay una sesión activa
     * @return bool
     */
    public static function estaActiva() {
        self::iniciar();
        
        // Verificar si la sesión está iniciada
        if (!isset($_SESSION['sesion_iniciada']) || $_SESSION['sesion_iniciada'] !== true) {
            return false;
        }
        
        // Verificar tiempo de inactividad
        if (isset($_SESSION['ultima_actividad'])) {
            $tiempo_inactivo = time() - $_SESSION['ultima_actividad'];
            
            if ($tiempo_inactivo > TIEMPO_SESION) {
                self::destruir();
                return false;
            }
        }
        
        // Actualizar última actividad
        $_SESSION['ultima_actividad'] = time();
        
        return true;
    }
    
    /**
     * Obtiene el ID del usuario actual
     * @return int|null
     */
    public static function obtenerUsuarioId() {
        self::iniciar();
        return isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
    }
    
    /**
     * Obtiene el tipo de usuario actual
     * @return string|null
     */
    public static function obtenerTipoUsuario() {
        self::iniciar();
        return isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : null;
    }
    
    /**
     * Obtiene el nombre completo del usuario
     * @return string
     */
    public static function obtenerNombreCompleto() {
        self::iniciar();
        if (isset($_SESSION['usuario_nombre']) && isset($_SESSION['usuario_apellido'])) {
            return $_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellido'];
        }
        return 'Usuario';
    }
    
    /**
     * Obtiene todos los datos del usuario en sesión
     * @return array
     */
    public static function obtenerDatosUsuario() {
        self::iniciar();
        return [
            'id' => $_SESSION['usuario_id'] ?? null,
            'nombre' => $_SESSION['usuario_nombre'] ?? '',
            'apellido' => $_SESSION['usuario_apellido'] ?? '',
            'correo' => $_SESSION['usuario_correo'] ?? '',
            'tipo' => $_SESSION['usuario_tipo'] ?? ''
        ];
    }
    
    /**
     * Verifica si el usuario tiene un tipo específico
     * @param string $tipo Tipo de usuario a verificar
     * @return bool
     */
    public static function esTipoUsuario($tipo) {
        return self::obtenerTipoUsuario() === $tipo;
    }
    
    /**
     * Verifica si el usuario es administrador
     * @return bool
     */
    public static function esAdministrador() {
        return self::esTipoUsuario(TIPO_ADMINISTRADOR);
    }
    
    /**
     * Verifica si el usuario es padre
     * @return bool
     */
    public static function esPadre() {
        return self::esTipoUsuario(TIPO_PADRE);
    }
    
    /**
     * Verifica si el usuario es trabajador
     * @return bool
     */
    public static function esTrabajador() {
        return self::esTipoUsuario(TIPO_TRABAJADOR);
    }
    
    /**
     * Verifica si el usuario es consejero
     * @return bool
     */
    public static function esConsejero() {
        return self::esTipoUsuario(TIPO_CONSEJERO);
    }
    
    /**
     * Establece un mensaje flash en la sesión
     * @param string $tipo Tipo de mensaje (exito, error, advertencia, info)
     * @param string $mensaje Contenido del mensaje
     */
    public static function establecerMensaje($tipo, $mensaje) {
        self::iniciar();
        $_SESSION['mensaje_flash'] = [
            'tipo' => $tipo,
            'contenido' => $mensaje
        ];
    }
    
    /**
     * Obtiene y elimina el mensaje flash
     * @return array|null
     */
    public static function obtenerMensaje() {
        self::iniciar();
        if (isset($_SESSION['mensaje_flash'])) {
            $mensaje = $_SESSION['mensaje_flash'];
            unset($_SESSION['mensaje_flash']);
            return $mensaje;
        }
        return null;
    }
    
    /**
     * Destruye la sesión completamente
     */
    public static function destruir() {
        self::iniciar();
        
        // Eliminar cookie de recordar sesión
        if (isset($_COOKIE['recordar_sesion'])) {
            setcookie('recordar_sesion', '', time() - 3600, '/');
        }
        
        // Limpiar variables de sesión
        $_SESSION = array();
        
        // Destruir la cookie de sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destruir la sesión
        session_destroy();
    }
    
    /**
     * Requiere que el usuario esté autenticado
     * Redirige al login si no lo está
     */
    public static function requerirAutenticacion() {
        if (!self::estaActiva()) {
            header('Location: ' . URL_BASE . '/index.php');
            exit();
        }
    }
    
    /**
     * Requiere un tipo de usuario específico
     * @param string|array $tipos_permitidos Tipo(s) de usuario permitido(s)
     */
    public static function requerirTipoUsuario($tipos_permitidos) {
        self::requerirAutenticacion();
        
        if (!is_array($tipos_permitidos)) {
            $tipos_permitidos = [$tipos_permitidos];
        }
        
        if (!in_array(self::obtenerTipoUsuario(), $tipos_permitidos)) {
            self::establecerMensaje('error', MSG_ERROR_PERMISOS);
            header('Location: ' . URL_BASE . '/panel.php');
            exit();
        }
    }
    
    /**
     * Registra la última actividad del usuario
     */
    public static function registrarActividad() {
        self::iniciar();
        $_SESSION['ultima_actividad'] = time();
    }
}
?>