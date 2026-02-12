<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_CONFIG . '/conexion.php';
require_once RUTA_CONTROLADORES . '/FormularioControlador.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$db = (new Conexion())->obtenerConexion();

// Obtener datos para la vista
$stmt = $db->prepare("SELECT titulo, archivo_url FROM formularios WHERE id_formulario = :id");
$stmt->execute([':id' => $id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$form) { header("Location: lista.php"); exit; }

$mensaje_error = "";

// Lógica de eliminación corregida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new FormularioControlador();
    $resultado = $controlador->eliminarPlantilla($id);

    if ($resultado === true) {
        header("Location: lista.php?msg=eliminado");
        exit;
    } else {
        // Si devuelve un string, es el mensaje de error de la base de datos
        $mensaje_error = $resultado ?: "Error desconocido al intentar eliminar.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Eliminación | <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/admin.css?v=<?php echo time(); ?>">
</head>
<body class="bg-danger-light">
    <div class="container-center">
        <div class="delete-card shadow">
            <div class="warning-icon">⚠️</div>
            <h2>¿Confirmar Eliminación?</h2>
            
            <p class="text-muted">Estás a punto de eliminar permanentemente la plantilla:</p>
            <h3 class="item-title"><?php echo htmlspecialchars($form['titulo']); ?></h3>

            <?php if ($mensaje_error): ?>
                <div class="alert-danger-box">
                    <strong>¡Atención!</strong><br>
                    <?php echo $mensaje_error; ?>
                </div>
            <?php endif; ?>

            <div class="info-alert">
                <p><strong>Aviso:</strong> Esta acción borrará el archivo físico <br> 
                <code><?php echo $form['archivo_url']; ?></code> del servidor.</p>
            </div>

            <form action="" method="POST">
                <div class="button-group">
                    <button type="submit" class="btn-danger-confirm">ELIMINAR AHORA</button>
                    <a href="lista.php" class="btn-cancel">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>