<?php
require_once __DIR__ . '/../../utilidades/sesion.php';
require_once __DIR__ . '/../../utilidades/permisos.php';
require_once __DIR__ . '/../../controladores/FormularioControlador.php';

verificarSesion();
verificarPermiso('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $controlador = new FormularioControlador();
    $resultado = $controlador->eliminar($_POST['id']);
    
    $_SESSION['mensaje'] = $resultado['mensaje'];
    $_SESSION['tipo_mensaje'] = $resultado['success'] ? 'success' : 'danger';
}

header('Location: lista.php');
exit;
?>