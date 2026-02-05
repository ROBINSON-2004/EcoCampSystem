<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_MODELOS . '/Actividad.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// Solo procesar si viene por POST por seguridad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_actividad'] ?? null;

    if ($id) {
        $actividad = new Actividad();
        $actividad->id_actividad = $id;

        if ($actividad->eliminar()) {
            Sesion::establecerMensaje('exito', 'La actividad ha sido desactivada correctamente.');
        } else {
            Sesion::establecerMensaje('error', 'No se pudo eliminar la actividad.');
        }
    }
}

// Redirigir siempre de vuelta a la lista
header('Location: lista.php');
exit;