<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/permisos.php';
require_once RUTA_CONTROLADORES . '/FormularioControlador.php';
require_once RUTA_MODELOS . '/Padre.php';


Sesion::iniciar();
Permisos::requerirRol(TIPO_ADMINISTRADOR);


$controlador = new FormularioControlador();

// Obtener filtros
$filtros = [];
if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
    $filtros['tipo'] = $_GET['tipo'];
}
if (isset($_GET['activo'])) {
    $filtros['activo'] = $_GET['activo'];
}

$resultado = $controlador->listar($filtros);
$formularios = $resultado['success'] ? $resultado['formularios'] : [];

require_once RUTA_VISTAS . '/plantillas/header.php';

require_once RUTA_VISTAS . '/plantillas/menu-admin.php';

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-file-alt"></i> Gestión de Formularios</h1>
                <p>Administra los formularios que deben completar los padres de los campistas</p>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tipo_mensaje']; ?> alert-dismissible fade show">
            <?php 
                echo $_SESSION['mensaje']; 
                unset($_SESSION['mensaje']);
                unset($_SESSION['tipo_mensaje']);
            ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Filtros y acciones -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <label class="mr-2">Filtrar:</label>
                        
                        <select name="tipo" class="form-control mr-2">
                            <option value="">Todos los tipos</option>
                            <option value="consentimiento" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'consentimiento') ? 'selected' : ''; ?>>Consentimiento</option>
                            <option value="medico" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'medico') ? 'selected' : ''; ?>>Médico</option>
                            <option value="fotografico" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'fotografico') ? 'selected' : ''; ?>>Fotográfico</option>
                            <option value="otro" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'otro') ? 'selected' : ''; ?>>Otro</option>
                        </select>
                        
                        <select name="activo" class="form-control mr-2">
                            <option value="">Todos los estados</option>
                            <option value="1" <?php echo (isset($_GET['activo']) && $_GET['activo'] == '1') ? 'selected' : ''; ?>>Activos</option>
                            <option value="0" <?php echo (isset($_GET['activo']) && $_GET['activo'] == '0') ? 'selected' : ''; ?>>Inactivos</option>
                        </select>
                        
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        
                        <a href="lista.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 text-right">
            <a href="subir.php" class="btn btn-success btn-lg">
                <i class="fas fa-plus"></i> Nuevo Formulario
            </a>
        </div>
    </div>

    <!-- Tabla de formularios -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Listado de Formularios</h3>
                    <span class="badge badge-info float-right"><?php echo count($formularios); ?> formularios</span>
                </div>
                <div class="card-body">
                    <?php if (empty($formularios)): ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <h4>No hay formularios registrados</h4>
                            <p>Comienza creando un nuevo formulario desde el botón superior.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Obligatorio</th>
                                        <th>Fecha Límite</th>
                                        <th>Estadísticas</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($formularios as $form): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($form['titulo']); ?></strong>
                                                <?php if (!empty($form['descripcion'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($form['descripcion'], 0, 60)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $badges = [
                                                    'consentimiento' => 'primary',
                                                    'medico' => 'danger',
                                                    'fotografico' => 'warning',
                                                    'otro' => 'secondary'
                                                ];
                                                $color = $badges[$form['tipo']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?php echo $color; ?>">
                                                    <?php echo ucfirst($form['tipo']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($form['activo']): ?>
                                                    <span class="badge badge-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($form['obligatorio']): ?>
                                                    <i class="fas fa-exclamation-circle text-danger" title="Obligatorio"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-minus-circle text-muted" title="Opcional"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($form['fecha_limite']): ?>
                                                    <?php 
                                                    $fecha = new DateTime($form['fecha_limite']);
                                                    $hoy = new DateTime();
                                                    $dias = $hoy->diff($fecha)->days;
                                                    $vencido = $fecha < $hoy;
                                                    ?>
                                                    <span class="<?php echo $vencido ? 'text-danger' : ($dias <= 3 ? 'text-warning' : ''); ?>">
                                                        <?php echo $fecha->format('d/m/Y'); ?>
                                                        <?php if ($vencido): ?>
                                                            <br><small>(Vencido)</small>
                                                        <?php elseif ($dias <= 3): ?>
                                                            <br><small>(<?php echo $dias; ?> días)</small>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin límite</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small>
                                                    <strong><?php echo $form['total_firmados']; ?></strong> / <?php echo $form['total_envios']; ?> firmados
                                                    <?php 
                                                    $porcentaje = $form['total_envios'] > 0 ? 
                                                        round(($form['total_firmados'] / $form['total_envios']) * 100) : 0;
                                                    ?>
                                                    <div class="progress mt-1" style="height: 5px;">
                                                        <div class="progress-bar bg-success" style="width: <?php echo $porcentaje; ?>%"></div>
                                                    </div>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="seguimiento.php?id=<?php echo $form['id']; ?>" 
                                                       class="btn btn-info" 
                                                       title="Ver seguimiento">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                    
                                                    <a href="editar.php?id=<?php echo $form['id']; ?>" 
                                                       class="btn btn-warning" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <?php if ($form['archivo_url']): ?>
                                                        <a href="<?php echo $form['archivo_url']; ?>" 
                                                           class="btn btn-secondary" 
                                                           title="Descargar"
                                                           target="_blank">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <button type="button" 
                                                            class="btn btn-danger" 
                                                            onclick="confirmarEliminacion(<?php echo $form['id']; ?>)"
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
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

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de eliminar este formulario?</p>
                <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="formEliminar" method="POST" action="eliminar.php" style="display:inline;">
                    <input type="hidden" name="id" id="idEliminar">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminacion(id) {
    document.getElementById('idEliminar').value = id;
    $('#modalEliminar').modal('show');
}
</script>

<?php require_once RUTA_VISTAS . '/plantillas/footer.php';?>