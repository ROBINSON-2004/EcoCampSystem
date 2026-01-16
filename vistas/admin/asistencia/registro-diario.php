<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once __DIR__ . '/../../../utilidades/sesion.php';
require_once __DIR__ . '/../../../utilidades/permisos.php';
require_once __DIR__ . '/../../../controladores/AsistenciaControlador.php';
require_once __DIR__ . '/../../../modelos/Grupo.php';
require_once __DIR__ . '/../../../modelos/Asistencia.php';

// Validar acceso
Permisos::requerirRol(['administrador', 'trabajador', 'consejero']);

$asistenciaCtrl = new AsistenciaControlador();
$grupoModelo = new Grupo();

// Obtener datos para filtros
$grupos = $grupoModelo->leerTodos();
$id_grupo_seleccionado = $_GET['id_grupo'] ?? null;
$fecha_consulta = $_GET['fecha'] ?? date('Y-m-d');
$campistas = [];

if ($id_grupo_seleccionado) {
    $asistencia_mod = new Asistencia();
    $campistas = $asistencia_mod->leerPorGrupoYFecha($id_grupo_seleccionado, $fecha_consulta);
}

include __DIR__ . '/../../plantillas/header.php';
?>

<div class="container-fluid">
    <h2 class="mt-4">Registro Diario de Asistencia</h2>
    
    <?php if (isset($_GET['msj'])): ?>
        <div class="alert alert-<?= ($_GET['msj'] == 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show">
            <?= ($_GET['msj'] == 'success') ? 'Guardado con éxito.' : 'Error al procesar.'; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Grupo</label>
                    <select name="id_grupo" class="form-select" required>
                        <option value="">-- Seleccione --</option>
                        <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['id_grupo']; ?>" <?= ($id_grupo_seleccionado == $g['id_grupo']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($g['nombre_grupo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($fecha_consulta); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($id_grupo_seleccionado): ?>
    <form action="../../../api/asistencia.php" method="POST">
        <input type="hidden" name="id_grupo" value="<?= htmlspecialchars($id_grupo_seleccionado); ?>">
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Campista</th>
                        <th>Estado</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campistas as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?></td>
                        <td>
                            <select name="asistencia[<?= $c['id_campista']; ?>]" class="form-select">
                                <option value="presente" <?= (($c['estado_asistencia'] ?? '') == 'presente') ? 'selected' : ''; ?>>Presente</option>
                                <option value="ausente" <?= (($c['estado_asistencia'] ?? '') == 'ausente') ? 'selected' : ''; ?>>Ausente</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="observaciones[<?= $c['id_campista']; ?>]" 
                                   class="form-control" value="<?= htmlspecialchars($c['observaciones'] ?? ''); ?>">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-success">Guardar Asistencia</button>
    </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../plantillas/footer.php'; ?>