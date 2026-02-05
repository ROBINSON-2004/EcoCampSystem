<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/CampistaControlador.php';
require_once RUTA_MODELOS . '/Padre.php';

// 1. Seguridad: Solo padres
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_PADRE);

$datos_usuario = Sesion::obtenerDatosUsuario();
$controlador = new CampistaControlador();

// 2. Obtener ID del padre logueado
$padre_modelo = new Padre();
$padre_modelo->id_usuario = $datos_usuario['id'];
$padre_modelo->leerPorIdUsuario();
$id_padre_sesion = $padre_modelo->id_padre;

// 3. Validar ID del campista
if (!isset($_GET['id']) || !es_numero_valido($_GET['id'])) {
    Sesion::establecerMensaje('error', 'ID de campista no válido.');
    header('Location: ' . URL_BASE . '/vistas/padre/dashboard.php');
    exit();
}

$id_campista = (int)$_GET['id'];
$campista = $controlador->obtenerPorId($id_campista);

// 4. VALIDACIÓN DE PERMISOS
if (!$campista || $campista['id_padre'] != $id_padre_sesion) {
    Sesion::establecerMensaje('error', 'No tienes permiso para editar este perfil.');
    header('Location: ' . URL_BASE . '/vistas/padre/dashboard.php');
    exit();
}

$error = '';

// 5. Procesar Actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos_actualizar = [
        'id_campista' => $id_campista,
        'nombre' => $_POST['nombre'],
        'apellido' => $_POST['apellido'],
        'fecha_nacimiento' => $_POST['fecha_nacimiento'],
        'genero' => $_POST['genero'],
        'notas_especiales' => $_POST['notas_especiales'],
        // Mantenemos el estado actual para que no se resetee a "pendiente" al editar
        'estado_inscripcion' => $campista['estado_inscripcion'] 
    ];
    
    $resultado = $controlador->actualizar($datos_actualizar);
    
    if ($resultado['exito']) {
        Sesion::establecerMensaje('exito', 'Información actualizada correctamente.');
        header('Location: ' . URL_BASE . '/vistas/padre/dashboard.php');
        exit();
    } else {
        $error = $resultado['mensaje'];
        $campista = array_merge($campista, $_POST);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Información - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        /* Estilos mantenidos... */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .header { background: #38a169; color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; }
        .btn { padding: 12px 25px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block; cursor: pointer; border: none; }
        .btn-save { background: #38a169; color: white; }
        .btn-cancel { background: #e2e8f0; color: #4a5568; }
    </style>
</head>
<body>
    <div class="header">
        <h1>✏️ Editar Perfil de <?php echo htmlspecialchars($campista['nombre']); ?></h1>
        <a href="<?php echo URL_BASE; ?>/vistas/padre/dashboard.php" style="color: white; text-decoration: none; font-weight: bold;">← Volver al Panel</a>
    </div>

    <div class="container">
        <?php if ($error): ?> <div style="color: red; margin-bottom: 20px;"><?php echo $error; ?></div> <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($campista['nombre']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Apellido</label>
                    <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($campista['apellido']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" value="<?php echo $campista['fecha_nacimiento']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Género</label>
                    <select name="genero" class="form-control">
                        <option value="masculino" <?php echo $campista['genero'] == 'masculino' ? 'selected' : ''; ?>>Masculino</option>
                        <option value="femenino" <?php echo $campista['genero'] == 'femenino' ? 'selected' : ''; ?>>Femenino</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notas Médicas / Observaciones</label>
                    <textarea name="notas_especiales" class="form-control" rows="4"><?php echo htmlspecialchars($campista['notas_especiales'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-save">💾 Guardar Cambios</button>
                    <a href="<?php echo URL_BASE; ?>/vistas/padre/dashboard.php" class="btn btn-cancel">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>