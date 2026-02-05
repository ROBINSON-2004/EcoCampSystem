<?php
require_once __DIR__ . '/../../../config/constantes.php';
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
$id_formulario = $_GET['id'];

// Obtener filtros
$filtros = [];
if (isset($_GET['firmado'])) {
    $filtros['firmado'] = $_GET['firmado'];
}

$resultado = $controlador->obtenerSeguimiento($id_formulario, $filtros);

if (!$resultado['success']) {
    $_SESSION['mensaje'] = $resultado['mensaje'];
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: lista.php');
    exit;
}

$formulario = $resultado['formulario'];
$campistas = $resultado['campistas'];
$estadisticas = $resultado['estadisticas'];

// Calcular porcentaje
$porcentaje = $estadisticas['total_asignados'] > 0 ? 
    round(($estadisticas['total_firmados'] / $estadisticas['total_asignados']) * 100, 1) : 0;

require_once RUTA_VISTAS . '/header.php';
require_once RUTA_VISTAS . '/menu-admin.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-chart-bar"></i> Seguimiento de Formulario</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="lista.php">Formularios</a></li>
                        <li class="breadcrumb-item active">Seguimiento</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Información del formulario -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title"><?php echo htmlspecialchars($formulario['titulo']); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($formulario['descripcion'])): ?>
                        <p class="mb-2"><?php echo htmlspecialchars($formulario['descripcion']); ?></p>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <strong>Tipo:</strong> 
                                <span class="badge badge-info"><?php echo ucfirst($formulario['tipo']); ?></span>
                            </p>
                            <p class="mb-1">
                                <strong>Estado:</strong> 
                                <?php if ($formulario['activo']): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactivo</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1">
                                <strong>Obligatorio:</strong> 
                                <?php echo $formulario['obligatorio'] ? '<span class="text-danger">Sí</span>' : 'No'; ?>
                            </p>
                            <p class="mb-1">
                                <strong>Fecha Límite:</strong> 
                                <?php if ($formulario['fecha_limite']): ?>
                                    <?php 
                                    $fecha = new DateTime($formulario['fecha_limite']);
                                    echo $fecha->format('d/m/Y'); 
                                    ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin límite</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($formulario['archivo_url']): ?>
                        <hr>
                        <a href="<?php echo $formulario['archivo_url']; ?>" 
                           class="btn btn-secondary" 
                           target="_blank">
                            <i class="fas fa-download"></i> Descargar Formulario Original
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Estadísticas</h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <h1 class="display-4 text-primary"><?php echo $porcentaje; ?>%</h1>
                        <p class="text-muted">Completado</p>
                    </div>
                    
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: <?php echo $porcentaje; ?>%"
                             aria-valuenow="<?php echo $porcentaje; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <?php echo $porcentaje; ?>%
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6 border-right">
                            <h3 class="text-success"><?php echo $estadisticas['total_firmados']; ?></h3>
                            <p class="text-muted small">Firmados</p>
                        </div>
                        <div class="col-6">
                            <h3 class="text-warning"><?php echo $estadisticas['pendientes']; ?></h3>
                            <p class="text-muted small">Pendientes</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <p class="mb-0">
                        <strong>Total Asignados:</strong><br>
                        <span class="h4"><?php echo $estadisticas['total_asignados']; ?></span> campistas
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-md-6">
            <form method="GET" class="form-inline">
                <input type="hidden" name="id" value="<?php echo $id_formulario; ?>">
                
                <label class="mr-2">Mostrar:</label>
                <select name="firmado" class="form-control mr-2">
                    <option value="">Todos</option>
                    <option value="1" <?php echo (isset($_GET['firmado']) && $_GET['firmado'] == '1') ? 'selected' : ''; ?>>
                        Solo Firmados
                    </option>
                    <option value="0" <?php echo (isset($_GET['firmado']) && $_GET['firmado'] == '0') ? 'selected' : ''; ?>>
                        Solo Pendientes
                    </option>
                </select>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </form>
        </div>
        
        <div class="col-md-6 text-right">
            <button type="button" class="btn btn-info" onclick="exportarExcel()">
                <i class="fas fa-file-excel"></i> Exportar a Excel
            </button>
            <a href="lista.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Lista de campistas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Campistas Asignados</h3>
                    <span class="badge badge-info float-right"><?php echo count($campistas); ?> registros</span>
                </div>
                <div class="card-body">
                    <?php if (empty($campistas)): ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <h4>No hay campistas asignados</h4>
                            <p>Este formulario aún no ha sido asignado a ningún campista.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaCampistas">
                                <thead>
                                    <tr>
                                        <th>Campista</th>
                                        <th>Grupo</th>
                                        <th>Padre/Tutor</th>
                                        <th>Contacto</th>
                                        <th>Estado</th>
                                        <th>Fecha Asignación</th>
                                        <th>Fecha Firma</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($campistas as $camp): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($camp['campista_nombre'] . ' ' . $camp['campista_apellidos']); ?></strong>
                                                <?php if ($camp['fecha_nacimiento']): ?>
                                                    <?php 
                                                    $nacimiento = new DateTime($camp['fecha_nacimiento']);
                                                    $hoy = new DateTime();
                                                    $edad = $hoy->diff($nacimiento)->y;
                                                    ?>
                                                    <br><small class="text-muted"><?php echo $edad; ?> años</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $camp['grupo_nombre'] ? htmlspecialchars($camp['grupo_nombre']) : '<span class="text-muted">Sin grupo</span>'; ?>
                                            </td>
                                            <td>
                                                <?php if ($camp['padre_nombre']): ?>
                                                    <?php echo htmlspecialchars($camp['padre_nombre'] . ' ' . $camp['padre_apellidos']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No asignado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($camp['padre_email']): ?>
                                                    <small>
                                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($camp['padre_email']); ?><br>
                                                        <?php if ($camp['padre_telefono']): ?>
                                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($camp['padre_telefono']); ?>
                                                        <?php endif; ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($camp['firmado']): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Firmado
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pendiente
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($camp['fecha_asignacion']) {
                                                    $fecha = new DateTime($camp['fecha_asignacion']);
                                                    echo $fecha->format('d/m/Y');
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($camp['fecha_firma']) {
                                                    $fecha = new DateTime($camp['fecha_firma']);
                                                    echo $fecha->format('d/m/Y H:i');
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($camp['documento_firmado_url']): ?>
                                                    <a href="<?php echo $camp['documento_firmado_url']; ?>" 
                                                       class="btn btn-sm btn-info" 
                                                       target="_blank"
                                                       title="Ver documento firmado">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($camp['padre_email']): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-secondary"
                                                            onclick="enviarRecordatorio('<?php echo $camp['padre_email']; ?>', '<?php echo $camp['campista_nombre']; ?>')"
                                                            title="Enviar recordatorio">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function enviarRecordatorio(email, nombreCampista) {
    if (confirm('¿Enviar recordatorio a ' + email + ' para el campista ' + nombreCampista + '?')) {
        // Aquí iría la llamada AJAX para enviar el correo
        alert('Funcionalidad de envío de recordatorios pendiente de implementación');
    }
}

function exportarExcel() {
    // Aquí iría la lógica para exportar a Excel
    alert('Funcionalidad de exportación pendiente de implementación');
}
</script>

<?php require_once RUTA_VISTAS . '/footer.php'; ?>