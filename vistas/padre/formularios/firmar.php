<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/permisos.php';
require_once RUTA_CONTROLADORES . '/FormularioControlador.php';
require_once RUTA_CONTROLADORES . '/FormularioCampista.php';

verificarSesion();
verificarPermiso('padre');

if (!isset($_GET['id_formulario']) || !isset($_GET['id_campista'])) {
    header('Location: disponibles.php');
    exit;
}

$controlador = new FormularioControlador();
$database = new Database();
$db = $database->getConnection();
$formularioCampista = new FormularioCampista($db);

$id_formulario = $_GET['id_formulario'];
$id_campista = $_GET['id_campista'];
$id_padre = $_SESSION['usuario_id'];

// Verificar que el padre tiene acceso a este campista
require_once RUTA_MODELOS . '/Padre.php';
$padreModelo = new Padre($db);
$hijos = $padreModelo->obtenerHijos($id_padre);
$hijo_encontrado = false;

foreach ($hijos as $hijo) {
    if ($hijo['id'] == $id_campista) {
        $hijo_encontrado = true;
        $datos_hijo = $hijo;
        break;
    }
}

if (!$hijo_encontrado) {
    $_SESSION['mensaje'] = 'No tienes permiso para acceder a este formulario';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: disponibles.php');
    exit;
}

// Obtener datos del formulario
$asignacion = $formularioCampista->obtenerFormularioCampista($id_formulario, $id_campista);

if (!$asignacion) {
    $_SESSION['mensaje'] = 'Formulario no encontrado';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: disponibles.php');
    exit;
}

// Si ya está firmado, redirigir
if ($asignacion['firmado']) {
    $_SESSION['mensaje'] = 'Este formulario ya ha sido firmado';
    $_SESSION['tipo_mensaje'] = 'info';
    header('Location: disponibles.php');
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar firma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_firma'])) {
    $archivo_firmado = isset($_FILES['documento_firmado']) ? $_FILES['documento_firmado'] : null;
    $resultado = $controlador->firmarFormulario($id_formulario, $id_campista, $archivo_firmado);
    
    if ($resultado['success']) {
        $_SESSION['mensaje'] = $resultado['mensaje'];
        $_SESSION['tipo_mensaje'] = 'success';
        header('Location: disponibles.php');
        exit;
    } else {
        $mensaje = $resultado['mensaje'];
        $tipo_mensaje = 'danger';
    }
}

require_once RUTA_VISTAS . '/header.php';
require_once RUTA_VISTAS . '/menu-padre.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-signature"></i> Firmar Formulario</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../padre/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="disponibles.php">Formularios</a></li>
                        <li class="breadcrumb-item active">Firmar</li>
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
        <!-- Información del formulario -->
        <div class="col-md-8">
            <!-- Datos del campista -->
            <div class="card mb-3 border-primary">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user"></i> Formulario para: 
                        <?php echo htmlspecialchars($datos_hijo['nombre'] . ' ' . $datos_hijo['apellidos']); ?>
                    </h4>
                </div>
            </div>

            <!-- Información del formulario -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><?php echo htmlspecialchars($asignacion['titulo']); ?></h3>
                    <?php if ($asignacion['obligatorio']): ?>
                        <span class="badge badge-danger float-right">Obligatorio</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($asignacion['descripcion'])): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <?php echo nl2br(htmlspecialchars($asignacion['descripcion'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Tipo:</strong> 
                                <span class="badge badge-secondary"><?php echo ucfirst($asignacion['tipo']); ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <?php if ($asignacion['fecha_limite']): ?>
                                <p><strong>Fecha límite:</strong> 
                                    <?php 
                                    $fecha = new DateTime($asignacion['fecha_limite']);
                                    $hoy = new DateTime();
                                    $dias = $hoy->diff($fecha)->days;
                                    $vencido = $fecha < $hoy;
                                    ?>
                                    <span class="<?php echo $vencido ? 'text-danger' : ($dias <= 3 ? 'text-warning' : ''); ?>">
                                        <?php echo $fecha->format('d/m/Y'); ?>
                                        <?php if ($vencido): ?>
                                            (¡Vencido!)
                                        <?php elseif ($dias <= 3): ?>
                                            (Quedan <?php echo $dias; ?> días)
                                        <?php endif; ?>
                                    </span>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($asignacion['archivo_url']): ?>
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h5><i class="fas fa-file-pdf"></i> Documento a Revisar</h5>
                                <p class="mb-2">Descarga y revisa cuidadosamente este documento antes de firmar.</p>
                                <a href="<?php echo $asignacion['archivo_url']; ?>" 
                                   class="btn btn-primary" 
                                   target="_blank">
                                    <i class="fas fa-download"></i> Descargar y Revisar Documento
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de firma -->
                    <div class="card border-success mt-4">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0"><i class="fas fa-pen-fancy"></i> Proceso de Firma</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" id="formFirma">
                                
                                <!-- Opción 1: Subir documento firmado (opcional) -->
                                <div class="form-group">
                                    <label for="documento_firmado">
                                        <i class="fas fa-upload"></i> Subir Documento Firmado (Opcional)
                                    </label>
                                    <div class="custom-file">
                                        <input type="file" 
                                               class="custom-file-input" 
                                               id="documento_firmado" 
                                               name="documento_firmado" 
                                               accept=".pdf">
                                        <label class="custom-file-label" for="documento_firmado">
                                            Seleccionar archivo PDF...
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Si descargaste, imprimiste y firmaste el documento físicamente, 
                                        puedes escanear/fotografiar y subir el documento firmado aquí (solo PDF).
                                    </small>
                                </div>

                                <hr>

                                <!-- Checkbox de confirmación -->
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="acepto_terminos" 
                                               name="acepto_terminos"
                                               required>
                                        <label class="custom-control-label" for="acepto_terminos">
                                            <strong>He leído y acepto los términos de este formulario</strong>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="confirmo_identidad" 
                                               name="confirmo_identidad"
                                               required>
                                        <label class="custom-control-label" for="confirmo_identidad">
                                            <strong>Confirmo que soy el padre/tutor legal de 
                                            <?php echo htmlspecialchars($datos_hijo['nombre'] . ' ' . $datos_hijo['apellidos']); ?></strong>
                                        </label>
                                    </div>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Importante:</strong> Al hacer clic en "Confirmar Firma Digital", 
                                    estás firmando electrónicamente este documento. Esta acción quedará registrada 
                                    con tu usuario, fecha, hora e IP.
                                </div>

                                <div class="form-group mb-0">
                                    <button type="submit" 
                                            name="confirmar_firma" 
                                            class="btn btn-success btn-lg btn-block"
                                            id="btnFirmar">
                                        <i class="fas fa-check-circle"></i> Confirmar Firma Digital
                                    </button>
                                    <a href="disponibles.php" class="btn btn-secondary btn-block mt-2">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel lateral de ayuda -->
        <div class="col-md-4">
            <div class="card bg-light mb-3">
                <div class="card-header">
                    <h4 class="card-title"><i class="fas fa-question-circle"></i> ¿Cómo firmar?</h4>
                </div>
                <div class="card-body">
                    <h5>Opción 1: Firma Digital</h5>
                    <ol>
                        <li>Lee el documento completo</li>
                        <li>Marca las casillas de aceptación</li>
                        <li>Haz clic en "Confirmar Firma Digital"</li>
                    </ol>

                    <hr>

                    <h5>Opción 2: Firma Física + Digital</h5>
                    <ol>
                        <li>Descarga el documento</li>
                        <li>Imprímelo</li>
                        <li>Fírmalo físicamente</li>
                        <li>Escanea o fotografía el documento</li>
                        <li>Súbelo en formato PDF</li>
                        <li>Confirma la firma digital</li>
                    </ol>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información Legal
                    </h4>
                </div>
                <div class="card-body">
                    <p class="small">
                        La firma electrónica tiene la misma validez legal que una firma física 
                        según la legislación vigente. Todos los datos del proceso de firma quedan registrados:
                    </p>
                    <ul class="small">
                        <li>Usuario que firma</li>
                        <li>Fecha y hora exacta</li>
                        <li>Dirección IP</li>
                        <li>Documento firmado</li>
                    </ul>
                    <p class="small mb-0">
                        <strong>Esta información es confidencial y solo se usa con fines de verificación.</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Confirmar Firma</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>¿Estás seguro de firmar este formulario?</strong></p>
                <p>Esta acción no se puede deshacer. Al confirmar, declaras haber leído y aceptado 
                todos los términos del documento.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Revisar de nuevo</button>
                <button type="button" class="btn btn-success" id="btnConfirmarModal">
                    Sí, Firmar Ahora
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Actualizar label del archivo
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Seleccionar archivo PDF...';
    const label = e.target.nextElementSibling;
    label.textContent = fileName;
});

// Confirmación antes de enviar
document.getElementById('formFirma').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Verificar que ambos checkbox estén marcados
    const acepto = document.getElementById('acepto_terminos').checked;
    const confirmo = document.getElementById('confirmo_identidad').checked;
    
    if (!acepto || !confirmo) {
        alert('Debes aceptar ambas confirmaciones para continuar');
        return false;
    }
    
    // Mostrar modal de confirmación
    $('#modalConfirmacion').modal('show');
});

// Confirmar desde el modal
document.getElementById('btnConfirmarModal').addEventListener('click', function() {
    document.getElementById('formFirma').submit();
});

// Validar archivo
document.getElementById('documento_firmado').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validar que sea PDF
        if (file.type !== 'application/pdf') {
            alert('Solo se permiten archivos PDF');
            e.target.value = '';
            return false;
        }
        
        // Validar tamaño (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('El archivo es muy grande. Máximo 5MB permitido.');
            e.target.value = '';
            return false;
        }
    }
});
</script>

<?php require_once RUTA_VISTAS . '/footer.php'; ?>