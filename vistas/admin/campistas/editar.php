<?php
/**
 * ============================================
 * VISTA: EDICIÓN INTEGRAL DEL CAMPISTA
 * EcoCampSystem 2026 - Panel Administrativo
 * ============================================
 */
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONTROLADORES . '/CampistaControlador.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

$id_campista = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$controlador = new CampistaControlador();

// 1. PROCESAR ACTUALIZACIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregamos el ID al array de datos para el controlador
    $_POST['id_campista'] = $id_campista;
    $resultado = $controlador->actualizar($_POST);
    
    if ($resultado['exito']) {
        Sesion::establecerMensaje('exito', 'Perfil actualizado correctamente.');
        header("Location: detalle.php?id=$id_campista");
        exit();
    } else {
        $error_msj = $resultado['mensaje'];
    }
}

// 2. OBTENER DATOS ACTUALES (Incluye Salud y Emergencia)
$campista = $controlador->obtenerPorId($id_campista);

if (!$campista) {
    header('Location: lista.php');
    exit();
}

$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Campista | <?php echo NOMBRE_SITIO; ?></title>
    <style>
        :root { --primary: #4a69bd; --bg: #f0f2f5; --white: #ffffff; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; color: #333; }
        .header { background: #2c3e50; color: white; padding: 20px 40px; display: flex; justify-content: space-between; }
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        
        .form-section { background: var(--white); border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .form-section h3 { margin-top: 0; color: var(--primary); border-bottom: 2px solid #edf2f7; padding-bottom: 10px; margin-bottom: 20px; }
        
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.9rem; color: #4a5568; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 1rem; }
        textarea { height: 80px; resize: none; }
        
        .btn-container { display: flex; justify-content: flex-end; gap: 10px; }
        .btn { padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; border: none; }
        .btn-save { background: var(--primary); color: white; }
        .btn-cancel { background: #e2e8f0; color: #4a5568; }
        
        /* Estilo para contactos dinámicos */
        .contacto-item { background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>

<div class="header">
    <h1>✏️ Editar Expediente</h1>
    <a href="detalle.php?id=<?php echo $id_campista; ?>" style="color: white; text-decoration: none;">Volver</a>
</div>

<div class="container">

    <?php if (isset($error_msj)): ?>
        <div style="background: #fed7d7; color: #822727; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            ⚠️ <?php echo $error_msj; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        
        <div class="form-section">
            <h3>👤 Información Personal</h3>
            <div class="grid">
                <div class="form-group">
                    <label>Nombre(s)</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($campista['nombre'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Apellido(s)</label>
                    <input type="text" name="apellido" value="<?php echo htmlspecialchars($campista['apellido'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" value="<?php echo $campista['fecha_nacimiento'] ?? ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Género</label>
                    <select name="genero">
                        <option value="masculino" <?php echo ($campista['genero'] == 'masculino') ? 'selected' : ''; ?>>Masculino</option>
                        <option value="femenino" <?php echo ($campista['genero'] == 'femenino') ? 'selected' : ''; ?>>Femenino</option>
                        <option value="otro" <?php echo ($campista['genero'] == 'otro') ? 'selected' : ''; ?>>Otro</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Notas Especiales (Observaciones Generales)</label>
                <textarea name="notas_especiales"><?php echo htmlspecialchars($campista['notas_especiales'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="form-section" style="border-left: 5px solid #e53e3e;">
            <h3>🏥 Ficha Médica</h3>
            <div class="grid">
                <div class="form-group">
                    <label>Tipo de Sangre</label>
                    <select name="tipo_sangre">
                        <?php $tipos = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'S/N']; 
                        foreach($tipos as $t): ?>
                            <option value="<?php echo $t; ?>" <?php echo (($campista['tipo_sangre'] ?? '') == $t) ? 'selected' : ''; ?>><?php echo $t; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Alergias</label>
                    <input type="text" name="alergias" value="<?php echo htmlspecialchars($campista['alergias'] ?? 'Ninguna'); ?>" placeholder="Ej: Penicilina, Maní...">
                </div>
            </div>
            <div class="form-group">
                <label>Medicamentos Actuales</label>
                <input type="text" name="medicamentos" value="<?php echo htmlspecialchars($campista['medicamentos'] ?? 'Ninguno'); ?>">
            </div>
            <div class="form-group">
                <label>Condiciones Especiales / Discapacidades</label>
                <textarea name="condiciones_especiales"><?php echo htmlspecialchars($campista['condiciones_especiales'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3>📞 Contactos de Emergencia</h3>
            <div id="contenedor-contactos">
                <?php 
                // Cargamos los contactos actuales
                $db = (new Conexion())->obtenerConexion();
                $stmt = $db->prepare("SELECT * FROM informacion_emergencia WHERE id_campista = ?");
                $stmt->execute([$id_campista]);
                $contactos = $stmt->fetchAll();

                foreach ($contactos as $i => $con): ?>
                    <div class="contacto-item">
                        <div class="grid">
                            <div class="form-group">
                                <label>Nombre del Contacto</label>
                                <input type="text" name="emergencia_nombre[]" value="<?php echo htmlspecialchars($con['nombre_contacto']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Parentesco</label>
                                <input type="text" name="emergencia_parentesco[]" value="<?php echo htmlspecialchars($con['parentesco']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Teléfono de Emergencia</label>
                                <input type="text" name="emergencia_telefono[]" value="<?php echo htmlspecialchars($con['telefono']); ?>" required>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="btn-container">
            <a href="detalle.php?id=<?php echo $id_campista; ?>" class="btn btn-cancel">Descartar Cambios</a>
            <button type="submit" class="btn btn-save">💾 Guardar Cambios en el Expediente</button>
        </div>

    </form>
</div>

</body>
</html>