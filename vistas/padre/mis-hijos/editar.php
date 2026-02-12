<?php
/**
 * ============================================
 * VISTA: EDITAR HIJO (CAMPISTA) - EcoCampSystem
 * ============================================
 */

require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/CampistaControlador.php';
require_once RUTA_MODELOS . '/Padre.php';

// 1. SEGURIDAD: Solo padres
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_PADRE);

// CORRECCIÓN: Usamos 'usuario_id' directamente de la sesión plana detectada
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL_BASE . '/login.php');
    exit();
}

$controlador = new CampistaControlador();

// 2. OBTENER ID DEL PADRE LOGUEADO
$padre_modelo = new Padre();
$padre_modelo->id_usuario = $_SESSION['usuario_id']; 
$padre_modelo->leerPorIdUsuario(); // Esto carga el id_padre basado en el usuario_id
$id_padre_sesion = $padre_modelo->id_padre;

// 3. VALIDAR ID DEL CAMPISTA
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    Sesion::establecerMensaje('error', 'ID de campista no válido.');
    header('Location: ../dashboard.php');
    exit();
}

$id_campista = (int)$_GET['id'];
$campista = $controlador->obtenerPorId($id_campista);

// 4. VALIDACIÓN DE PROPIEDAD: ¿Es realmente su hijo?
if (!$campista || $campista['id_padre'] != $id_padre_sesion) {
    Sesion::establecerMensaje('error', 'No tienes permiso para editar este perfil.');
    header('Location: ../dashboard.php');
    exit();
}

$error = '';

// 5. PROCESAR ACTUALIZACIÓN (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos_actualizar = [
        'id_campista'      => $id_campista,
        'nombre'           => limpiar_cadena($_POST['nombre']),
        'apellido'         => limpiar_cadena($_POST['apellido']),
        'fecha_nacimiento' => $_POST['fecha_nacimiento'],
        'genero'           => $_POST['genero'],
        'notas_especiales' => limpiar_cadena($_POST['notas_especiales'])
    ];
    
    $resultado = $controlador->actualizar($datos_actualizar);
    
    if ($resultado['exito']) {
        Sesion::establecerMensaje('exito', 'Información actualizada correctamente.');
        header('Location: ../dashboard.php'); // CORRECCIÓN: Redirigir al dashboard real
        exit();
    } else {
        $error = $resultado['mensaje'];
        $campista = array_merge($campista, $_POST); // Mantiene los datos si hay error
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Información | <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/padre.css?v=<?php echo time(); ?>">
    <style>
        .container { max-width: 700px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; margin-bottom: 15px; }
        .btn-save { background: #38a169; color: white; padding: 12px 25px; border-radius: 8px; border: none; cursor: pointer; font-weight: bold; }
        .btn-cancel { background: #e2e8f0; color: #4a5568; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header" style="background: #38a169; color: white; padding: 20px; display: flex; justify-content: space-between;">
        <h1>✏️ Editar Perfil de Hijo</h1>
        <a href="../dashboard.php" style="color: white; text-decoration: none; font-weight: bold;">← Volver al Panel</a>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div style="padding: 25px; border-bottom: 1px solid #edf2f7;">
                <h2>Datos de <?php echo htmlspecialchars($campista['nombre']); ?></h2>
            </div>
            <div style="padding: 30px;">
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($campista['nombre']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Apellido</label>
                            <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($campista['apellido']); ?>" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control" value="<?php echo $campista['fecha_nacimiento']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Género</label>
                            <select name="genero" class="form-control" required>
                                <option value="masculino" <?php echo ($campista['genero'] == 'masculino') ? 'selected' : ''; ?>>Masculino</option>
                                <option value="femenino" <?php echo ($campista['genero'] == 'femenino') ? 'selected' : ''; ?>>Femenino</option>
                                <option value="otro" <?php echo ($campista['genero'] == 'otro') ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notas Médicas / Observaciones</label>
                        <textarea name="notas_especiales" class="form-control" rows="4"><?php echo htmlspecialchars($campista['notas_especiales'] ?? ''); ?></textarea>
                    </div>

                    <div style="display: flex; gap: 15px; margin-top: 10px;">
                        <button type="submit" class="btn-save">💾 Guardar Cambios</button>
                        <a href="../dashboard.php" class="btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>