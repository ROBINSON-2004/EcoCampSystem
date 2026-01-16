<?php
require_once __DIR__ . '/../utilidades/sesion.php';
require_once __DIR__ . '/../utilidades/funciones.php';
require_once __DIR__ . '/../controladores/AsistenciaControlador.php';

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