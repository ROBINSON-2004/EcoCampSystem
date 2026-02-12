<?php
require_once __DIR__ . '/../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_MODELOS . '/Notificacion.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_PADRE);

$id_vinculo = $_GET['id'] ?? null;

if ($id_vinculo) {
    $modelo = new Notificacion();
    $modelo->marcarComoLeida($id_vinculo);
}

header('Location: notificaciones.php');
exit();