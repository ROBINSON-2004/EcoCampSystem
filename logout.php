<?php
require_once __DIR__ . '/config/constantes.php';
require_once __DIR__ . '/utilidades/sesion.php';
require_once __DIR__ . '/utilidades/funciones.php';

// Iniciar sesión para poder destruirla
Sesion::iniciar();

// Obtener correo antes de destruir (para el log)
$datos_usuario = Sesion::obtenerDatosUsuario();
$correo = $datos_usuario['correo'] ?? 'Usuario desconocido';

// Destruir la sesión
Sesion::destruir();

// Registrar en log
registrar_log("Cierre de sesión: $correo", 'INFO');

// Establecer mensaje de éxito
session_start();
$_SESSION['mensaje_flash'] = [
    'tipo' => 'exito',
    'contenido' => 'Has cerrado sesión correctamente.'
];

// Redirigir al login
header('Location: ' . URL_BASE . '/index.php');
exit();
?>