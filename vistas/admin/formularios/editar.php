<?php
/**
 * ============================================
 * VISTA: EDITAR FORMULARIO - EcoCampSystem
 * ============================================
 */

// 1. CARGA DE CONFIGURACIÓN Y SEGURIDAD
// Subimos tres niveles para llegar a la raíz: /vistas/admin/formularios/ -> /
require_once __DIR__ . '/../../../config/constantes.php'; //
require_once RUTA_UTILIDADES . '/sesion.php'; //
require_once RUTA_CONFIG . '/conexion.php'; //
require_once RUTA_CONTROLADORES . '/FormularioControlador.php'; //

// Iniciar sesión y validar que el usuario sea administrador
Sesion::iniciar(); //
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR); //

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$database = new Conexion(); //
$db = $database->obtenerConexion();

// 2. OBTENER DATOS ACTUALES DEL FORMULARIO
if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM formularios WHERE id_formulario = :id"); //
    $stmt->execute([':id' => $id]);
    $form = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Redirigir si el formulario no existe
if (!$form) {
    header("Location: lista.php");
    exit;
}

$mensaje = "";

// ============================================
// PROCESAR ACTUALIZACIÓN (POST)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new FormularioControlador();
    
    // Verificamos si existe el archivo en $_FILES, de lo contrario enviamos null
    $archivo_nuevo = isset($_FILES['nuevo_pdf']) ? $_FILES['nuevo_pdf'] : null;

    if ($controlador->actualizarPlantilla($id, $_POST, $archivo_nuevo)) {
        $mensaje = "<div class='alert alert-success'>Cambios guardados correctamente.</div>";
        
        // Refrescar los datos para mostrarlos en el formulario
        $stmt->execute([':id' => $id]);
        $form = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $mensaje = "<div class='alert alert-error'>Error al intentar actualizar el formulario.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Formulario | <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/admin.css?v=<?php echo time(); ?>"> </head>
<body>
    <div class="container">
        <header class="header">
            <h1>⚙️ Editar Plantilla de Formulario</h1>
            <div class="actions">
                <a href="lista.php" class="btn-secondary">Volver al Listado</a>
            </div>
        </header>

        <?php echo $mensaje; ?>

        <div class="card shadow" style="max-width: 700px; margin: 20px auto; padding: 30px;">
            <form action="" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="titulo">Título del Formulario</label>
                    <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($form['titulo']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción / Instrucciones para los Padres</label>
                    <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($form['descripcion']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="estado">Estado de la Plantilla</label>
                    <select id="estado" name="estado" required>
                        <option value="activo" <?php echo ($form['estado'] == 'activo') ? 'selected' : ''; ?>>Activo (Visible para padres)</option>
                        <option value="inactivo" <?php echo ($form['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo (Oculto)</option>
                        <option value="archivado" <?php echo ($form['estado'] == 'archivado') ? 'selected' : ''; ?>>Archivado</option>
                    </select>
                </div>

                <div class="form-group" style="background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; margin-top: 25px;">
                    <label style="color: #0d6efd; display: block; margin-bottom: 10px;">📄 Archivo PDF (Opcional)</label>
                    
                    <p style="font-size: 13px; margin-bottom: 10px;">
                        <strong>Archivo actual:</strong> 
                        <a href="<?php echo URL_UPLOADS . '/formularios/' . $form['archivo_url']; ?>" target="_blank">
                            <?php echo $form['archivo_url']; ?>
                        </a>
                    </p>

                    <label for="nuevo_pdf" style="font-size: 14px; font-weight: normal;">Selecciona un nuevo PDF para reemplazar el anterior:</label>
                    <input type="file" id="nuevo_pdf" name="nuevo_pdf" accept=".pdf" style="border: none; padding: 10px 0;">
                    <p style="font-size: 12px; color: #6c757d; margin-top: 5px;">
                        * Si dejas este campo vacío, se mantendrá el archivo actual.
                    </p>
                </div>

                <div style="margin-top: 30px; display: flex; gap: 15px;">
                    <button type="submit" class="btn-login" style="flex: 2;">Actualizar Formulario</button>
                    <a href="lista.php" class="btn-secondary" style="flex: 1; text-align: center; padding-top: 12px; text-decoration: none;">Cancelar</a>
                </div>

            </form>
        </div>
    </div>
</body>
</html>