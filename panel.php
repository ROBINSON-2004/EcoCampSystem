<?php
require_once __DIR__ . '/config/constantes.php';
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/utilidades/funciones.php';
require_once __DIR__ . '/utilidades/sesion.php';

// Requerir autenticación
Sesion::requerirAutenticacion();

// Obtener datos del usuario
$datos_usuario = Sesion::obtenerDatosUsuario();
$tipo_usuario = $datos_usuario['tipo'];

// Redirigir según tipo de usuario
switch ($tipo_usuario) {
    case TIPO_ADMINISTRADOR:
        require __DIR__ . '/vistas/admin/dashboard.php';
        break;
    
    case TIPO_PADRE:
        require __DIR__ . '/vistas/padre/dashboard.php';
        break;
    
    case TIPO_TRABAJADOR:
        require __DIR__ . '/vistas/trabajador/dashboard.php';
        break;
    
    case TIPO_CONSEJERO:
        require __DIR__ . '/vistas/consejero/dashboard.php';
        break;
    
    default:
        Sesion::establecerMensaje('error', 'Tipo de usuario no válido.');
        header('Location: index.php');
        exit();
}
?>