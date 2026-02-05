<?php
// Usamos la ruta relativa basada en tu estructura de carpetas (3 niveles arriba)
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_MODELOS . '/Actividad.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// Obtenemos el ID de la URL
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: lista.php'); // Redirige a la lista si no hay ID
    exit;
}

$actividad = new Actividad();
$actividad->id_actividad = $id;

// Cargar datos actuales
if (!$actividad->leerPorId()) {
    header('Location: lista.php');
    exit;
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actividad->nombre_actividad = $_POST['nombre'];
    $actividad->descripcion      = $_POST['descripcion'];
    $actividad->tipo_actividad   = $_POST['tipo'];
    $actividad->ubicacion        = $_POST['ubicacion'];
    $actividad->duracion_minutos = (int)$_POST['duracion'];
    $actividad->capacidad_maxima = (int)$_POST['capacidad'];
    $actividad->edad_minima      = (int)$_POST['edad_min'];
    $actividad->edad_maxima      = (int)$_POST['edad_max'];
    $actividad->estado           = 'activo';

    if ($actividad->actualizar()) {
        header('Location: lista.php?mensaje=actualizado');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Actividad - EcoCamp</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; }
        .header-bar { background: #6f42c1; color: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 850px; margin: 30px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .btn { padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; text-decoration: none; font-weight: 600; display: inline-block; }
        .btn-primary { background: #6f42c1; color: white; }
        .btn-secondary { background: #e9ecef; color: #495057; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 8px; font-size: 1rem; }
        .row { display: flex; gap: 20px; margin-bottom: 10px; }
        .col { flex: 1; }
    </style>
</head>
<body>

<div class="header-bar">
    <h2>✏️ Editar Actividad</h2>
    <a href="lista.php" class="btn btn-secondary">← Volver</a>
</div>

<div class="container">
    <form method="POST">
        <div class="form-group">
            <label>Nombre de Actividad *</label>
            <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($actividad->nombre_actividad ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($actividad->descripcion ?? '') ?></textarea>
        </div>

        <div class="row">
            <div class="col form-group">
                <label>Tipo de Actividad</label>
                <select name="tipo" class="form-control">
                    <?php foreach(['deportiva','artistica','educativa','recreativa','otra'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($actividad->tipo_actividad == $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col form-group">
                <label>Duración (minutos)</label>
                <input type="number" name="duracion" class="form-control" value="<?= $actividad->duracion_minutos ?? 0 ?>">
            </div>
        </div>

        <div class="row">
            <div class="col form-group">
                <label>Ubicación / Lugar</label>
                <input type="text" name="ubicacion" class="form-control" value="<?= htmlspecialchars($actividad->ubicacion ?? '') ?>">
            </div>
            <div class="col form-group">
                <label>Capacidad Máxima</label>
                <input type="number" name="capacidad" class="form-control" value="<?= $actividad->capacidad_maxima ?? 0 ?>">
            </div>
        </div>

        <div class="row">
            <div class="col form-group">
                <label>Edad Mínima</label>
                <input type="number" name="edad_min" class="form-control" value="<?= $actividad->edad_minima ?? 0 ?>">
            </div>
            <div class="col form-group">
                <label>Edad Máxima</label>
                <input type="number" name="edad_max" class="form-control" value="<?= $actividad->edad_maxima ?? 0 ?>">
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
            <a href="lista.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

</body>
</html>