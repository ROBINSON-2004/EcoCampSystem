<?php
// 1. Carga de configuración y utilidades fundamentales
require_once __DIR__ . '/config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';

Sesion::iniciar();

/**
 * 2. Validación de Autenticación
 */
if (!Sesion::estaAutenticado()) {
    header('Location: index.php');
    exit();
}

$datos_usuario = Sesion::obtenerDatosUsuario();

// CORRECCIÓN AQUÍ: Usamos 'tipo_usuario' que es como viene en tu Array
$tipo_usuario = $datos_usuario['tipo_usuario'] ?? '';

/**
 * 3. Enrutador de Dashboards
 */
switch ($tipo_usuario) {

    case TIPO_ADMIN:
        require_once RUTA_VISTAS . '/admin/dashboard.php';
        break;

    case TIPO_PADRE:
        require_once RUTA_VISTAS . '/padre/dashboard.php';
        break;

    case TIPO_TRABAJADOR:
        require_once RUTA_VISTAS . '/trabajador/dashboard.php';
        break;

    case TIPO_CONSEJERO:
        require_once RUTA_VISTAS . '/consejero/dashboard.php';
        break;

    default:
        /**
         * 4. Manejo de errores de Rol
         */
        echo "<h2>Error de Configuración de Acceso</h2>";
        echo "El tipo de usuario detectado es: <strong>'$tipo_usuario'</strong>.<br>";
        echo "Este rol no tiene un panel de control asignado en el sistema.<br>";
        echo "Verifica que en <code>config/constantes.php</code> la constante TIPO_ADMIN sea igual a 'administrador'.";
        exit();
}