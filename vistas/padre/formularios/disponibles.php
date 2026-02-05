<?php
require_once ROOT_PATH . '/config/constantes.php';
require_once ROOT_PATH . '/utilidades/sesion.php';
require_once ROOT_PATH . '/utilidades/permisos.php';
require_once ROOT_PATH . '/controladores/FormularioControlador.php';
require_once ROOT_PATH . '/modelos/Padre.php';


Permisos::requerirRol(TIPO_PADRE);

$controlador = new FormularioControlador();

$id_padre = $_SESSION['usuario_id'];

// Filtros
$filtros = [];
if (isset($_GET['firmado'])) {
    $filtros['firmado'] = $_GET['firmado'];
}
if (!empty($_GET['campista_id'])) {
    $filtros['campista_id'] = $_GET['campista_id'];
}

$resultado = $controlador->obtenerFormulariosPadre($id_padre, $filtros);

$formularios = $resultado['success'] ? $resultado['formularios'] : [];
$pendientes  = $resultado['success']
    ? $resultado['pendientes']
    : ['total_pendientes' => 0, 'campistas_con_pendientes' => 0];

// Conexión
$conexion = new Conexion();
$db = $conexion->obtenerConexion();

// Modelo Padre
$padreModelo = new Padre($db);
$hijos = $padreModelo->obtenerHijos($id_padre);

require_once RUTA_VISTAS . '/header.php';
require_once RUTA_VISTAS . '/menu-padre.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-file-signature"></i> Formularios de Mis Hijos</h1>
                <p>Revisa y firma los formularios requeridos para tus hijos</p>
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

    <!-- Alerta de pendientes -->
    <?php if ($pendientes['total_pendientes'] > 0): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>¡Atención!</strong> Tienes <strong><?php echo $pendientes['total_pendientes']; ?></strong> 
            formulario(s) pendiente(s) de firma para 
            <strong><?php echo $pendientes['campistas_con_pendientes']; ?></strong> hijo(s).
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <label class="mr-2">Filtrar:</label>
                        
                        <!-- Filtro por hijo -->
                        <select name="campista_id" class="form-control mr-2">
                            <option value="">Todos mis hijos</option>
                            <?php foreach ($hijos as $hijo): ?>
                                <option value="<?php echo $hijo['id']; ?>" 
                                        <?php echo (isset($_GET['campista_id']) && $_GET['campista_id'] == $hijo['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($hijo['nombre'] . ' ' . $hijo['apellidos']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <!-- Filtro por estado -->
                        <select name="firmado" class="form-control mr-2">
                            <option value="">Todos los estados</option>
                            <option value="0" <?php echo (isset($_GET['firmado']) && $_GET['firmado'] == '0') ? 'selected' : ''; ?>>
                                Pendientes de Firma
                            </option>
                            <option value="1" <?php echo (isset($_GET['firmado']) && $_GET['firmado'] == '1') ? 'selected' : ''; ?>>
                                Ya Firmados
                            </option>
                        </select>
                        
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        
                        <a href="disponibles.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $pendientes['total_pendientes']; ?></h3>
                    <p class="mb-0">Formularios Pendientes</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de formularios -->
    <div class="row">
        <div class="col-12">
            <?php if (empty($formularios)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                        <h3>¡Todo al día!</h3>
                        <p class="text-muted">No tienes formularios pendientes en este momento.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php
                // Agrupar por campista
                $porCampista = [];
                foreach ($formularios as $form) {
                    $key = $form['campista_id'];
                    if (!isset($porCampista[$key])) {
                        $porCampista[$key] = [
                            'nombre' => $form['campista_nombre'] . ' ' . $form['campista_apellidos'],
                            'formularios' => []
                        ];
                    }
                    $porCampista[$key]['formularios'][] = $form;
                }
                ?>
                
                <?php foreach ($porCampista as $datos): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($datos['nombre']); ?>
                                <span class="badge badge-light float-right">
                                    <?php echo count($datos['formularios']); ?> formulario(s)
                                </span>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($datos['formularios'] as $form): ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card h-100 <?php echo $form['firmado'] ? 'border-success' : ($form['obligatorio'] ? 'border-danger' : 'border-warning'); ?>">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">
                                                    <?php echo htmlspecialchars($form['titulo']); ?>
                                                    <?php if ($form['firmado']): ?>
                                                        <i class="fas fa-check-circle text-success float-right"></i>
                                                    <?php elseif ($form['obligatorio']): ?>
                                                        <i class="fas fa-exclamation-circle text-danger float-right"></i>
                                                    <?php endif; ?>
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <?php if (!empty($form['descripcion'])): ?>
                                                    <p class="card-text small">
                                                        <?php echo htmlspecialchars($form['descripcion']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <ul class="list-unstyled small">
                                                    <li>
                                                        <strong>Tipo:</strong> 
                                                        <span class="badge badge-secondary"><?php echo ucfirst($form['tipo']); ?></span>
                                                    </li>
                                                    <li>
                                                        <strong>Estado:</strong>
                                                        <?php if ($form['firmado']): ?>
                                                            <span class="badge badge-success">Firmado</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-warning">Pendiente</span>
                                                        <?php endif; ?>
                                                    </li>
                                                    <li>
                                                        <strong>Prioridad:</strong>
                                                        <?php if ($form['obligatorio']): ?>
                                                            <span class="badge badge-danger">Obligatorio</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-info">Opcional</span>
                                                        <?php endif; ?>
                                                    </li>
                                                    <?php if ($form['fecha_limite']): ?>
                                                        <li>
                                                            <strong>Fecha límite:</strong><br>
                                                            <?php 
                                                            $fecha = new DateTime($form['fecha_limite']);
                                                            $hoy = new DateTime();
                                                            $dias = $hoy->diff($fecha)->days;
                                                            $vencido = $fecha < $hoy;
                                                            ?>
                                                            <span class="<?php echo $vencido ? 'text-danger' : ($dias <= 3 ? 'text-warning' : ''); ?>">
                                                                <?php echo $fecha->format('d/m/Y'); ?>
                                                                <?php if (!$form['firmado']): ?>
                                                                    <?php if ($vencido): ?>
                                                                        <br><small>(¡Vencido!)</small>
                                                                    <?php elseif ($dias <= 3): ?>
                                                                        <br><small>(Quedan <?php echo $dias; ?> días)</small>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </span>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if ($form['firmado'] && $form['fecha_firma']): ?>
                                                        <li class="text-success">
                                                            <strong>Firmado el:</strong><br>
                                                            <?php 
                                                            $fecha = new DateTime($form['fecha_firma']);
                                                            echo $fecha->format('d/m/Y H:i');
                                                            ?>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                            <div class="card-footer">
                                                <?php if ($form['firmado']): ?>
                                                    <?php if ($form['documento_firmado_url']): ?>
                                                        <a href="<?php echo $form['documento_firmado_url']; ?>" 
                                                           class="btn btn-sm btn-success btn-block"
                                                           target="_blank">
                                                            <i class="fas fa-file-pdf"></i> Ver Documento Firmado
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-success btn-block" disabled>
                                                            <i class="fas fa-check"></i> Firmado Correctamente
                                                        </button>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <a href="firmar.php?id_formulario=<?php echo $form['id_formulario']; ?>&id_campista=<?php echo $form['id_campista']; ?>" 
                                                       class="btn btn-sm btn-primary btn-block">
                                                        <i class="fas fa-signature"></i> Revisar y Firmar
                                                    </a>
                                                    <?php if ($form['archivo_url']): ?>
                                                        <a href="<?php echo $form['archivo_url']; ?>" 
                                                           class="btn btn-sm btn-secondary btn-block mt-1"
                                                           target="_blank">
                                                            <i class="fas fa-eye"></i> Vista Previa
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?require_once RUTA_VISTAS . '/footer.php';?>