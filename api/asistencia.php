<?php
require_once __DIR__ . '/../../../config/constantes.php';

require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/AsistenciaControlador.php';

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new AsistenciaControlador();
    
    // Procesar los datos recibidos del formulario
    $resultado = $controlador->guardarAsistenciaMasiva($_POST);
    
    if ($resultado['exito']) {
        // Redirigir con mensaje de éxito (puedes usar una variable de sesión para alertas)
        header("Location: ../vistas/admin/asistencia/registro-diario.php?id_grupo=" . $_POST['id_grupo'] . "&msj=success");
    } else {
        header("Location: ../vistas/admin/asistencia/registro-diario.php?id_grupo=" . $_POST['id_grupo'] . "&msj=error");
    }
    exit();
}