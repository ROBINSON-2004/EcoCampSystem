<?php
require_once RUTA_UTILIDADES . 'sesion.php';
require_once RUTA_UTILIDADES . 'permisos.php';
require_once RUTA_CONTROLADORES . '/FormularioControlador.php';
require_once RUTA_CONTROLADORES . '/CampistaControlador.php';

verificarSesion();
verificarPermiso('admin');

$controlador = new FormularioControlador();
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $archivo = isset($_FILES['archivo']) ? $_FILES['archivo'] : null;
    $resultado = $controlador->crear($_POST, $archivo);
    
    if ($resultado['success']) {
        $_SESSION['mensaje'] = $resultado['mensaje'];
        $_SESSION['tipo_mensaje'] = 'success';
        header('Location: lista.php');
        exit;
    } else {
        $mensaje = $resultado['mensaje'];
        $tipo_mensaje = 'danger';
    }
}

require_once RUTA_VISTAS . '/header.php';
require_once RUTA_VISTAS . '/menu-admin.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-upload"></i> Subir Nuevo Formulario</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="lista.php">Formularios</a></li>
                        <li class="breadcrumb-item active">Nuevo</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show">
            <?php echo $mensaje; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Formulario principal -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Formulario</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="formFormulario">
                        
                        <!-- Título -->
                        <div class="form-group">
                            <label for="titulo">Título del Formulario <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="titulo" 
                                   name="titulo" 
                                   placeholder="Ej: Consentimiento Informado 2024"
                                   required>
                            <small class="form-text text-muted">
                                Nombre descriptivo que verán los padres
                            </small>
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3"
                                      placeholder="Describe de qué trata este formulario..."></textarea>
                            <small class="form-text text-muted">
                                Explica brevemente qué debe hacer el padre con este formulario
                            </small>
                        </div>

                        <!-- Tipo -->
                        <div class="form-group">
                            <label for="tipo">Tipo de Formulario <span class="text-danger">*</span></label>
                            <select class="form-control" id="tipo" name="tipo" required>
                                <option value="">Seleccione...</option>
                                <option value="consentimiento">Consentimiento Informado</option>
                                <option value="medico">Información Médica</option>
                                <option value="fotografico">Autorización Fotográfica</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>

                        <!-- Archivo -->
                        <div class="form-group">
                            <label for="archivo">Archivo del Formulario <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" 
                                       class="custom-file-input" 
                                       id="archivo" 
                                       name="archivo" 
                                       accept=".pdf,.doc,.docx"
                                       required>
                                <label class="custom-file-label" for="archivo">Seleccionar archivo...</label>
                            </div>
                            <small class="form-text text-muted">
                                Formatos permitidos: PDF, DOC, DOCX (Máximo 5MB)
                            </small>
                        </div>

                        <!-- Fecha límite -->
                        <div class="form-group">
                            <label for="fecha_limite">Fecha Límite de Firma</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_limite" 
                                   name="fecha_limite"
                                   min="<?php echo date('Y-m-d'); ?>">
                            <small class="form-text text-muted">
                                Opcional. Fecha hasta la cual debe estar firmado el formulario
                            </small>
                        </div>

                        <!-- Opciones -->
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="obligatorio" 
                                       name="obligatorio"
                                       checked>
                                <label class="custom-control-label" for="obligatorio">
                                    <strong>Formulario Obligatorio</strong>
                                </label>
                                <br>
                                <small class="form-text text-muted">
                                    Los formularios obligatorios deben ser firmados antes de que el campista pueda participar
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="activo" 
                                       name="activo"
                                       checked>
                                <label class="custom-control-label" for="activo">
                                    <strong>Activar formulario inmediatamente</strong>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="asignar_todos" 
                                       name="asignar_todos">
                                <label class="custom-control-label" for="asignar_todos">
                                    <strong>Asignar a todos los campistas activos</strong>
                                </label>
                                <br>
                                <small class="form-text text-muted">
                                    Si no marca esta opción, podrá asignar el formulario manualmente después
                                </small>
                            </div>
                        </div>

                        <hr>

                        <!-- Botones -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Crear Formulario
                            </button>
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel de ayuda -->
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header">
                    <h4 class="card-title"><i class="fas fa-info-circle"></i> Ayuda</h4>
                </div>
                <div class="card-body">
                    <h5>Tipos de Formularios:</h5>
                    <ul>
                        <li><strong>Consentimiento:</strong> Autorizaciones generales para participar en el campamento</li>
                        <li><strong>Médico:</strong> Información de salud y autorizaciones médicas</li>
                        <li><strong>Fotográfico:</strong> Permiso para tomar y usar fotografías</li>
                        <li><strong>Otro:</strong> Cualquier otro tipo de formulario</li>
                    </ul>

                    <hr>

                    <h5>Consejos:</h5>
                    <ul>
                        <li>Usa títulos descriptivos y claros</li>
                        <li>Marca como obligatorios solo los esenciales</li>
                        <li>Establece fechas límite realistas</li>
                        <li>Revisa el archivo antes de subirlo</li>
                    </ul>

                    <hr>

                    <div class="alert alert-warning">
                        <strong><i class="fas fa-exclamation-triangle"></i> Importante:</strong>
                        <p class="mb-0">Los formularios obligatorios deben ser firmados por los padres antes de que el campista pueda participar en actividades.</p>
                    </div>
                </div>
            </div>

            <!-- Vista previa -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title"><i class="fas fa-file-pdf"></i> Vista Previa</h4>
                </div>
                <div class="card-body">
                    <div id="preview-container" class="text-center text-muted">
                        <i class="fas fa-file fa-4x mb-3"></i>
                        <p>Selecciona un archivo para ver su información</p>
                    </div>
                    <div id="file-info" style="display: none;">
                        <p><strong>Nombre:</strong> <span id="file-name"></span></p>
                        <p><strong>Tamaño:</strong> <span id="file-size"></span></p>
                        <p><strong>Tipo:</strong> <span id="file-type"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Actualizar label del archivo
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Seleccionar archivo...';
    const label = e.target.nextElementSibling;
    label.textContent = fileName;
    
    // Mostrar información del archivo
    if (e.target.files[0]) {
        const file = e.target.files[0];
        document.getElementById('file-name').textContent = file.name;
        document.getElementById('file-size').textContent = formatBytes(file.size);
        document.getElementById('file-type').textContent = file.type || 'Desconocido';
        document.getElementById('preview-container').style.display = 'none';
        document.getElementById('file-info').style.display = 'block';
    }
});

// Formatear bytes
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// Validación del formulario
document.getElementById('formFormulario').addEventListener('submit', function(e) {
    const archivo = document.getElementById('archivo').files[0];
    
    if (archivo) {
        // Validar tamaño (5MB)
        if (archivo.size > 5 * 1024 * 1024) {
            e.preventDefault();
            alert('El archivo es muy grande. Máximo 5MB permitido.');
            return false;
        }
        
        // Validar extensión
        const extension = archivo.name.split('.').pop().toLowerCase();
        if (!['pdf', 'doc', 'docx'].includes(extension)) {
            e.preventDefault();
            alert('Formato de archivo no permitido. Use PDF, DOC o DOCX.');
            return false;
        }
    }
});
</script>

<?php require_once RUTA_VISTAS . '/footer.php'; ?>