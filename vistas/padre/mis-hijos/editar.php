<?php
/**
 * ============================================
 * VISTA: EDICIÓN INTEGRAL (Sincronizada con Controlador)
 * EcoCampSystem 2026 - Pujilí, Ecuador
 * ============================================
 */
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/CampistaControlador.php';
require_once RUTA_MODELOS . '/Padre.php';
require_once RUTA_CONFIG . '/conexion.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_PADRE);

// 1. VERIFICAR IDENTIDAD DEL PADRE
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL_BASE . '/login.php');
    exit();
}

$db = (new Conexion())->obtenerConexion();
$controlador = new CampistaControlador();

$padre_modelo = new Padre();
$padre_modelo->id_usuario = $_SESSION['usuario_id']; 
$padre_modelo->leerPorIdUsuario();
$id_padre_sesion = $padre_modelo->id_padre;

// 2. VALIDAR ID DEL CAMPISTA
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    Sesion::establecerMensaje('error', 'ID de campista no válido.');
    header('Location: ../dashboard.php');
    exit();
}

$id_campista = (int)$_GET['id'];
$campista = $controlador->obtenerPorId($id_campista);

// Validación de propiedad: Solo el padre puede editar a su hijo
if (!$campista || $campista['id_padre'] != $id_padre_sesion) {
    Sesion::establecerMensaje('error', 'No tienes permiso para editar este perfil.');
    header('Location: ../dashboard.php');
    exit();
}

/**
 * 3. CARGAR DATOS PARA PRE-RELLENAR EL FORMULARIO
 * Obtenemos los datos médicos y contactos directamente de la DB para asegurar que estén frescos.
 */
$info_medica_q = $db->prepare("SELECT * FROM informacion_medica WHERE id_campista = ?");
$info_medica_q->execute([$id_campista]);
$med = $info_medica_q->fetch(PDO::FETCH_ASSOC);

$contactos_q = $db->prepare("SELECT * FROM informacion_emergencia WHERE id_campista = ?");
$contactos_q->execute([$id_campista]);
$contactos = $contactos_q->fetchAll(PDO::FETCH_ASSOC);

$error = '';

// 4. PROCESAR ACTUALIZACIÓN (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Añadimos el ID al array de POST para que el controlador sepa a quién actualizar
    $_POST['id_campista'] = $id_campista;
    
    // El controlador ahora maneja la transacción completa (Básico + Médico + Emergencia)
    $resultado = $controlador->actualizar($_POST);
    
    if ($resultado['exito']) {
        Sesion::establecerMensaje('exito', 'Perfil actualizado correctamente.');
        header('Location: ../dashboard.php');
        exit();
    } else {
        $error = $resultado['mensaje'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil | <?php echo NOMBRE_SITIO; ?></title>
    <style>
        :root { --green: #38a169; --blue: #3182ce; --bg: #f7fafc; --text: #2d3748; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); }
        
        .header { background: var(--green); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 850px; margin: 30px auto; padding: 0 20px; }
        
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; overflow: hidden; }
        .section-title { background: #edf2f7; padding: 15px 25px; font-weight: bold; border-left: 5px solid var(--green); color: #4a5568; }
        .section-title.med { border-left-color: var(--blue); }
        
        .p-30 { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; }
        
        .form-control { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1rem; transition: 0.3s; }
        .form-control:focus { outline: none; border-color: var(--green); box-shadow: 0 0 0 3px rgba(56, 161, 105, 0.1); }
        
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn-save { background: var(--green); color: white; padding: 18px; border: none; border-radius: 10px; font-size: 1.1rem; font-weight: bold; cursor: pointer; width: 100%; transition: 0.3s; }
        .btn-save:hover { background: #2f855a; transform: translateY(-2px); }
        
        .contact-block { background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 15px; }

        @media (max-width: 600px) { .row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <div class="header">
        <h1>✏️ Editar Información de Campista</h1>
        <a href="../dashboard.php" style="color: white; text-decoration: none; font-weight: bold;">← Cancelar</a>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            
            <div class="card">
                <div class="section-title">👤 Datos Personales</div>
                <div class="p-30">
                    <div class="row">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($campista['nombre']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Apellido</label>
                            <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($campista['apellido']); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label>Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control" value="<?php echo $campista['fecha_nacimiento']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Género</label>
                            <select name="genero" class="form-control" required>
                                <option value="masculino" <?php echo ($campista['genero'] == 'masculino') ? 'selected' : ''; ?>>Masculino</option>
                                <option value="femenino" <?php echo ($campista['genero'] == 'femenino') ? 'selected' : ''; ?>>Femenino</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title med">🏥 Información Médica</div>
                <div class="p-30">
                    <div class="row">
                        <div class="form-group">
                            <label>Tipo de Sangre</label>
                            <select name="tipo_sangre" class="form-control" required>
                                <?php $ts = $med['tipo_sangre'] ?? ''; ?>
                                <option value="O+" <?php echo ($ts == 'O+') ? 'selected' : ''; ?>>O Positivo (O+)</option>
                                <option value="O-" <?php echo ($ts == 'O-') ? 'selected' : ''; ?>>O Negativo (O-)</option>
                                <option value="A+" <?php echo ($ts == 'A+') ? 'selected' : ''; ?>>A Positivo (A+)</option>
                                <option value="B+" <?php echo ($ts == 'B+') ? 'selected' : ''; ?>>B Positivo (B+)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Alergias</label>
                            <input type="text" name="alergias" class="form-control" value="<?php echo htmlspecialchars($med['alergias'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Condiciones Especiales / Discapacidades</label>
                        <textarea name="condiciones_especiales" class="form-control"><?php echo htmlspecialchars($med['condiciones_especiales'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Medicamentos Actuales</label>
                        <textarea name="medicamentos" class="form-control"><?php echo htmlspecialchars($med['medicamentos'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title med" style="border-left-color: var(--blue);">📞 Contactos de Emergencia</div>
                <div class="p-30">
                    <?php 
                    // Mostramos dos espacios de contacto (precargados si existen)
                    for ($i = 0; $i < 2; $i++): 
                        $con = $contactos[$i] ?? ['nombre_contacto' => '', 'parentesco' => '', 'telefono' => ''];
                    ?>
                    <div class="contact-block">
                        <p style="margin-bottom: 10px; font-weight: bold; color: var(--blue);">Contacto <?php echo $i+1; ?></p>
                        <div class="form-group">
                            <label>Nombre Completo:</label>
                            <input type="text" name="emergencia_nombre[]" class="form-control" value="<?php echo htmlspecialchars($con['nombre_contacto']); ?>" placeholder="Nombre del contacto">
                        </div>
                        <div class="row">
                            <div class="form-group">
                                <label>Parentesco:</label>
                                <input type="text" name="emergencia_parentesco[]" class="form-control" value="<?php echo htmlspecialchars($con['parentesco']); ?>" placeholder="Ej: Madre, Tío">
                            </div>
                            <div class="form-group">
                                <label>Teléfono:</label>
                                <input type="tel" name="emergencia_telefono[]" class="form-control" value="<?php echo htmlspecialchars($con['telefono']); ?>" placeholder="0987654321">
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Notas Adicionales / Observaciones</label>
                <textarea name="notas_especiales" class="form-control"><?php echo htmlspecialchars($campista['notas_especiales'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn-save">💾 Guardar Cambios del Perfil</button>
            <a href="../dashboard.php" class="btn-cancel" style="display: block; text-align: center; margin-top: 15px; color: #718096; text-decoration: none;">Cancelar y volver</a>
        </form>
    </div>

</body>
</html>