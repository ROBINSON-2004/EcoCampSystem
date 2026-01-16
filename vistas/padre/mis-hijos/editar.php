<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once __DIR__ . '/../../../utilidades/sesion.php';
require_once __DIR__ . '/../../../utilidades/funciones.php';
require_once __DIR__ . '/../../../controladores/CampistaControlador.php';
require_once __DIR__ . '/../../../modelos/Padre.php';

// 1. Seguridad: Solo padres
Sesion::requerirTipoUsuario(TIPO_PADRE);

$datos_usuario = Sesion::obtenerDatosUsuario();
$controlador = new CampistaControlador();

// 2. Obtener ID del padre logueado
$padre_modelo = new Padre();
$padre_modelo->id_usuario = $datos_usuario['id'];
$padre_modelo->leerPorIdUsuario();
$id_padre_sesion = $padre_modelo->id_padre;

// 3. Validar ID del campista enviado por URL
if (!isset($_GET['id']) || !es_numero_valido($_GET['id'])) {
    Sesion::establecerMensaje('error', 'ID de campista no válido.');
    header('Location: ../panel.php');
    exit();
}

$id_campista = (int)$_GET['id'];
$campista = $controlador->obtenerPorId($id_campista);

// 4. VALIDACIÓN CRÍTICA: ¿Es este niño hijo del padre logueado?
if (!$campista || $campista['id_padre'] != $id_padre_sesion) {
    Sesion::establecerMensaje('error', 'No tienes permiso para editar este perfil o el registro no existe.');
    header('Location: ../panel.php');
    exit();
}

$error = '';

// 5. Procesar Actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Solo permitimos editar ciertos campos (seguridad)
    $datos_actualizar = [
        'id_campista' => $id_campista,
        'nombre' => $_POST['nombre'],
        'apellido' => $_POST['apellido'],
        'fecha_nacimiento' => $_POST['fecha_nacimiento'],
        'genero' => $_POST['genero'],
        'notas_especiales' => $_POST['notas_especiales']
    ];
    
    $resultado = $controlador->actualizar($datos_actualizar);
    
    if ($resultado['exito']) {
        Sesion::establecerMensaje('exito', 'Información de tu hijo actualizada correctamente.');
        header('Location: ../panel.php');
        exit();
    } else {
        $error = $resultado['mensaje'];
        $campista = array_merge($campista, $_POST); // Mantener datos del formulario si falla
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Información - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; min-height: 100vh; }
        .header { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: #f8f9fa; padding: 25px 30px; border-bottom: 1px solid #eee; }
        .card-body { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-control { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1rem; }
        .form-control:focus { outline: none; border-color: #48bb78; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; background: #fee; color: #c33; border-left: 4px solid #c33; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        .btn { padding: 12px 25px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .btn-save { background: #38a169; color: white; }
        .btn-cancel { background: #e2e8f0; color: #4a5568; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>✏️ Editar Perfil</h1>
        <a href="../panel.php" style="color: white; text-decoration: none;">← Volver</a>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Información de <?php echo htmlspecialchars($campista['nombre']); ?></h2>
                <p style="color: #666;">Actualiza los datos personales de tu hijo.</p>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" 
                                   value="<?php echo htmlspecialchars($campista['nombre']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="apellido">Apellido</label>
                            <input type="text" id="apellido" name="apellido" class="form-control" 
                                   value="<?php echo htmlspecialchars($campista['apellido']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" 
                                   value="<?php echo $campista['fecha_nacimiento']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="genero">Género</label>
                            <select id="genero" name="genero" class="form-control" required>
                                <option value="masculino" <?php echo $campista['genero'] == 'masculino' ? 'selected' : ''; ?>>Masculino</option>
                                <option value="femenino" <?php echo $campista['genero'] == 'femenino' ? 'selected' : ''; ?>>Femenino</option>
                                <option value="otro" <?php echo $campista['genero'] == 'otro' ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notas_especiales">Notas Médicas / Alergias / Observaciones</label>
                        <textarea id="notas_especiales" name="notas_especiales" class="form-control" rows="4" 
                                  placeholder="Indica cualquier condición importante..."><?php echo htmlspecialchars($campista['notas_especiales'] ?? ''); ?></textarea>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-save">💾 Guardar Cambios</button>
                        <a href="../panel.php" class="btn btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>