<?php
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/permisos.php';
require_once RUTA_CONTROLADORES . '/FormularioControlador.php';

verificarSesion();
verificarPermiso('admin');

if (!isset($_GET['id'])) {
    header('Location: lista.php');
    exit;
}

$controlador = new FormularioControlador();
$id = $_GET['id'];

// Obtener formulario
$resultado = $controlador->obtener($id);

if (!$resultado['success']) {
    $_SESSION['mensaje'] = $resultado['mensaje'];
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: lista.php');
    exit;
}

$formulario = $resultado['formulario'];
$mensaje = '';
$tipo_mensaje = '';

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $archivo = isset($_FILES['archivo']) ? $_FILES['archivo'] : null;
    $resultado = $controlador->actualizar($id, $_POST, $archivo);
    
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
                <h1><i class="fas fa-edit"></i> Editar Formulario</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="lista.php">Formularios</a></li>
                        <li class="breadcrumb-item active">Editar</li>
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
                    <h3 class="card-title">Editar Información</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        
                        <!-- Título -->
                        <div class="form-group">
                            <label for="titulo">Título del Formulario <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="titulo" 
                                   name="titulo" 
                                   value="<?php echo htmlspecialchars($formulario['titulo']); ?>"
                                   required>
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3"><?php echo htmlspecialchars($formulario['descripcion'] ?? ''); ?></textarea>
                        </div>

                        <!-- Tipo -->
                        <div class="form-group">
                            <label for="tipo">Tipo de Formulario <span class="text-danger">*</span></label>
                            <select class="form-control" id="tipo" name="tipo" required>
                                <option value="consentimiento" <?php echo $formulario['tipo'] == 'consentimiento' ? 'selected' : ''; ?>>
                                    Consentimiento Informado
                                </option>
                                <option value="medico" <?php echo $formulario['tipo'] == 'medico' ? 'selected' : ''; ?>>
                                    Información Médica
                                </option>
                                <option value="fotografico" <?php echo $formulario['tipo'] == 'fotografico' ? 'selected' : ''; ?>>
                                    Autorización Fotográfica
                                </option>
                                <option value="otro" <?php echo $formulario['tipo'] == 'otro' ? 'selected' : ''; ?>>
                                    Otro
                                </option>
                            </select>
                        </div>

                        <!-- Archivo actual -->
                        <?php if (!empty($formulario['archivo_url'])): ?>
                            <div class="alert alert-info">
                                <strong>Archivo actual:</strong> 
                                <a href="<?php echo $formulario['archivo_url']; ?>" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Ver archivo actual
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Nuevo archivo (opcional) -->
                        <div class="form-group">
                            <label for="archivo">Reemplazar Archivo (Opcional)</label>
                            <div class="custom-file">
                                <input type="file" 
                                       class="custom-file-input" 
                                       id="archivo" 
                                       name="archivo" 
                                       accept=".pdf,.doc,.docx">
                                <label class="custom-file-label" for="archivo">Seleccionar nuevo archivo...</label>
                            </div>
                            <small class="form-text text-muted">
                                Solo si deseas cambiar el archivo. Formatos: PDF, DOC, DOCX (Max 5MB)
                            </small>
                        </div>

                        <!-- Fecha límite -->
                        <div class="form-group">
                            <label for="fecha_limite">Fecha Límite de Firma</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_limite" 
                                   name="fecha_limite"
                                   value="<?php echo $formulario['fecha_limite'] ?? ''; ?>"
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <!-- Opciones -->
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="obligatorio" 
                                       name="obligatorio"
                                       <?php echo $formulario['obligatorio'] ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="obligatorio">
                                    <strong>Formulario Obligatorio</strong>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="activo" 
                                       name="activo"
                                       <?php echo $formulario['activo'] ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="activo">
                                    <strong>Formulario Activo</strong>
                                </label>
                            </div>
                        </div>

                        <hr>

                        <!-- Botones -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel lateral con info -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><i class="fas fa-info-circle"></i> Información</h4>
                </div>
                <div class="card-body">
                    <p><strong>Creado:</strong><br>
                    <?php 
                    if ($formulario['fecha_creacion']) {
                        $fecha = new DateTime($formulario['fecha_creacion']);
                        echo $fecha->format('d/m/Y H:i');
                    }
                    ?>
                    </p>
                    
                    <?php if (!empty($formulario['creador_nombre'])): ?>
                        <p><strong>Creado por:</strong><br>
                        <?php echo htmlspecialchars($formulario['creador_nombre']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <?php if (isset($resultado['estadisticas'])): ?>
                        <h5>Estadísticas</h5>
                        <p class="mb-1">
                            <strong>Asignados:</strong> 
                            <?php echo $resultado['estadisticas']['total_asignados']; ?>
                        </p>
                        <p class="mb-1">
                            <strong>Firmados:</strong> 
                            <span class="text-success">
                                <?php echo $resultado['estadisticas']['total_firmados']; ?>
                            </span>
                        </p>
                        <p class="mb-1">
                            <strong>Pendientes:</strong> 
                            <span class="text-warning">
                                <?php echo $resultado['estadisticas']['pendientes']; ?>
                            </span>
                        </p>
                        
                        <a href="seguimiento.php?id=<?php echo $id; ?>" class="btn btn-info btn-block mt-3">
                            <i class="fas fa-chart-bar"></i> Ver Seguimiento Completo
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Actualizar label del archivo
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Seleccionar nuevo archivo...';
    const label = e.target.nextElementSibling;
    label.textContent = fileName;
});
</script>

<?php require_once RUTA_VISTAS . '/footer.php';?>