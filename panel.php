<?php
require_once __DIR__ . '/config/constantes.php';
require_once RUTA_CONFIG . '/conexion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_UTILIDADES . '/sesion.php';

// Requerir autenticación
Sesion::requerirAutenticacion();

// Obtener datos del usuario desde sesión
$datos_usuario = Sesion::obtenerDatosUsuario();

// Validación defensiva
if (!$datos_usuario || !isset($datos_usuario['tipo'])) {
    Sesion::cerrar();
    header('Location: index.php');
    exit();
}

$tipo_usuario = $datos_usuario['tipo'];

// Redirigir según tipo
switch ($tipo_usuario) {

    case TIPO_ADMINISTRADOR:
        require RUTA_VISTAS . '/admin/dashboard.php';
        break;

    case TIPO_PADRE:
        require RUTA_VISTAS . '/padre/dashboard.php';
        break;

    case TIPO_TRABAJADOR:
        require RUTA_VISTAS . '/trabajador/dashboard.php';
        break;

    case TIPO_CONSEJERO:
        require RUTA_VISTAS . '/consejero/dashboard.php';
        break;

    default:
        Sesion::establecerMensaje('error', 'Tipo de usuario no válido.');
        header('Location: ' . URL_BASE . '/index.php');
        exit();
}
