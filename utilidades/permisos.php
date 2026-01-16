<?php
/**
 * Control de acceso basado en roles
 */
class Permisos {
    /**
     * Verifica si el usuario tiene el rol necesario, si no, redirige
     * @param mixed $roles_permitidos String o Array de roles
     */
    public static function requerirRol($roles_permitidos) {
        // Verificar si la sesión existe 
        if (!Sesion::estaAutenticado()) {
            header("Location: " . URL_BASE . "/index.php");
            exit();
        }

        $rol_actual = Sesion::obtenerTipoUsuario();
        
        // Convertir a array si es un solo string para poder usar in_array 
        $roles = is_array($roles_permitidos) ? $roles_permitidos : [$roles_permitidos];
        
        if (!in_array($rol_actual, $roles)) {
            // Redirección según el rol para evitar bucles 
            if ($rol_actual === 'padre') {
                header("Location: " . URL_BASE . "/vistas/padre/dashboard.php");
            } else {
                header("Location: " . URL_BASE . "/panel.php");
            }
            exit();
        }
    }
}