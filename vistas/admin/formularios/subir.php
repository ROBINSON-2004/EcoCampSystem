<?php
/**
 * ============================================
 * VISTA: CARGAR FORMULARIO - EcoCampSystem
 * ============================================
 */

// 1. CARGA DE CONFIGURACIÓN Y SEGURIDAD
// Subimos tres niveles para llegar a la raíz: /vistas/admin/formularios/ -> /
require_once __DIR__ . '/../../../config/constantes.php'; //
require_once RUTA_UTILIDADES . '/sesion.php'; //
require_once RUTA_CONTROLADORES . '/FormularioControlador.php'; //

// Iniciar sesión y validar que el usuario tenga rol de Administrador
Sesion::iniciar(); //

Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR); //

$mensaje = "";

// 2. PROCESAR LA SUBIDA (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new FormularioControlador(); //
    
    // Llamada al método del controlador para procesar la plantilla y el archivo PDF
    if ($controlador->subirPlantilla($_POST, $_FILES['archivo'])) { //
        $mensaje = "<div class='alert alert-success'>¡Éxito! El formulario ha sido publicado y está disponible para los padres.</div>";
    } else {
        $mensaje = "<div class='alert alert-error'>Error: No se pudo subir el archivo. Verifica que sea un PDF y que las carpetas tengan permisos.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Formulario | <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/admin.css?v=<?php echo time(); ?>"> //
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>📤 Cargar Nueva Plantilla de Formulario</h1>
            <div class="actions">
                <a href="lista.php" class="btn-secondary">Volver al Listado</a>
            </div>
        </header>

        <?php echo $mensaje; ?>

        <div class="card shadow" style="max-width: 650px; margin: 20px auto; padding: 30px;">
            <form action="" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="titulo">Título del Formulario</label>
                    <input type="text" id="titulo" name="titulo" required placeholder="Ej: Consentimiento de Natación 2026">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción / Instrucciones</label>
                    <textarea id="descripcion" name="descripcion" rows="3" placeholder="Indica a los padres los requisitos de este documento..."></textarea>
                </div>

                <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="tipo">Tipo de Documento</label>
                        <select id="tipo" name="tipo" required>
                            <option value="consentimiento">Consentimiento General</option>
                            <option value="medico">Información Médica / Alergias</option>
                            <option value="liberacion">Liberación de Responsabilidad</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <div class="form-group" style="display: flex; flex-direction: column; justify-content: center;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 15px;">
                            <input type="checkbox" name="obligatorio" id="obligatorio" value="1" checked>
                            <label for="obligatorio" style="margin: 0; font-weight: normal; cursor: pointer;">Es obligatorio</label>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="background: #fcfcfc; border: 2px dashed #e0e0e0; padding: 20px; border-radius: 8px; text-align: center; margin-top: 10px;">
                    <label style="display: block; margin-bottom: 15px; color: #1976d2;">📄 Seleccionar Plantilla PDF</label>
                    <input type="file" name="archivo" accept=".pdf" required style="border: none; background: transparent;">
                    <p style="font-size: 12px; color: #888; margin-top: 10px;">
                        Tamaño máximo permitido: 5MB. Solo archivos formato .PDF.
                    </p>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-login" style="width: 100%;">Publicar Formulario</button>
                </div>
                
            </form>
        </div>
    </div>
</body>
</html>
