<?php
/**
 * ============================================
 * VISTA: EDICIÓN INTEGRAL DEL CAMPISTA
 * EcoCampSystem 2026 - Robinson Moya
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
    $_POST['id_campista'] = $id_campista; // Aseguramos el ID
    $resultado = $controlador->actualizar($_POST);
    
    if ($resultado['exito']) {
        Sesion::establecerMensaje('exito', '¡Expediente actualizado correctamente!');
        header("Location: detalle.php?id=$id_campista");
        exit();
    } else {
        $error_msj = $resultado['mensaje'];
    }
}

// 2. OBTENER DATOS ACTUALES
$campista = $controlador->obtenerPorId($id_campista);

if (!$campista) {
    header('Location: lista.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Expediente | <?php echo htmlspecialchars($campista['nombre'] ?? 'Campista'); ?></title>
    <style>
        :root { --primary: #4a69bd; --secondary: #718096; --danger: #e53e3e; --bg: #f7fafc; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; color: #2d3748; }
        .header { background: #2c3e50; color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 950px; margin: 30px auto; padding: 0 20px; padding-bottom: 60px; }
        
        .form-section { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; border-top: 5px solid #cbd5e0; }
        .section-personal { border-top-color: var(--primary); }
        .section-medical { border-top-color: var(--danger); }
        
        h3 { margin-top: 0; margin-bottom: 20px; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; font-size: 0.85rem; color: #4a5568; text-transform: uppercase; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; background: #f8fafc; transition: 0.2s; box-sizing: border-box; }
        input:focus, textarea:focus { outline: none; border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(74, 105, 189, 0.1); }
        textarea { height: 100px; resize: none; }
        
        .contacto-item { background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #edf2f7; margin-bottom: 15px; position: relative; }
        .btn-add { background: #48bb78; color: white; padding: 8px 15px; border-radius: 6px; border: none; cursor: pointer; font-size: 0.9rem; margin-top: 10px; }
        
        .btn-container { display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; }
        .btn { padding: 15px 30px; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; border: none; transition: 0.2s; }
        .btn-save { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(74, 105, 189, 0.2); }
        .btn-save:hover { background: #3c55a5; transform: translateY(-1px); }
        .btn-cancel { background: white; color: var(--secondary); border: 1px solid #e2e8f0; }
    </style>
</head>
<body>

<div class="header">
    <h1>✏️ Editar Campista #<?php echo $id_campista; ?></h1>
    <a href="detalle.php?id=<?php echo $id_campista; ?>" style="color: white; text-decoration: none;">✕ Cancelar</a>
</div>

<div class="container">

    <?php if (isset($error_msj)): ?>
        <div style="background: #fff5f5; color: #c53030; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #feb2b2;">
            <strong>Error:</strong> <?php echo $error_msj; ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="formEditar">
        
        <div class="form-section section-personal">
            <h3>👤 Datos Personales</h3>
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
                        <option value="masculino" <?php echo (($campista['genero'] ?? '') == 'masculino') ? 'selected' : ''; ?>>Masculino</option>
                        <option value="femenino" <?php echo (($campista['genero'] ?? '') == 'femenino') ? 'selected' : ''; ?>>Femenino</option>
                        <option value="otro" <?php echo (($campista['genero'] ?? '') == 'otro') ? 'selected' : ''; ?>>Otro</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Notas Especiales / Observaciones</label>
                <textarea name="notas_especiales"><?php echo htmlspecialchars($campista['notas_especiales'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="form-section section-medical">
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
                    <label>Alergias Conocidas</label>
                    <input type="text" name="alergias" value="<?php echo htmlspecialchars($campista['alergias'] ?? 'Ninguna'); ?>">
                </div>
            </div>
            <div class="grid">
                <div class="form-group">
                    <label>Medicamentos Actuales</label>
                    <input type="text" name="medicamentos" value="<?php echo htmlspecialchars($campista['medicamentos'] ?? 'Ninguno'); ?>">
                </div>
                <div class="form-group">
                    <label>Condiciones Especiales</label>
                    <input type="text" name="condiciones_especiales" value="<?php echo htmlspecialchars($campista['condiciones_especiales'] ?? 'Ninguna'); ?>">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>📞 Contactos de Emergencia</h3>
            <div id="contenedor-contactos">
                <?php 
                $db = (new Conexion())->obtenerConexion();
                $stmt = $db->prepare("SELECT * FROM informacion_emergencia WHERE id_campista = ?");
                $stmt->execute([$id_campista]);
                $contactos = $stmt->fetchAll();

                if (empty($contactos)) $contactos = [['nombre_contacto'=>'','parentesco'=>'','telefono'=>'']];

                foreach ($contactos as $con): ?>
                    <div class="contacto-item">
                        <div class="grid">
                            <div class="form-group">
                                <label>Nombre</label>
                                <input type="text" name="emergencia_nombre[]" value="<?php echo htmlspecialchars($con['nombre_contacto']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Parentesco</label>
                                <input type="text" name="emergencia_parentesco[]" value="<?php echo htmlspecialchars($con['parentesco']); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="emergencia_telefono[]" value="<?php echo htmlspecialchars($con['telefono']); ?>" required>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn-add" onclick="agregarContacto()">+ Añadir otro contacto</button>
        </div>

        <div class="btn-container">
            <a href="detalle.php?id=<?php echo $id_campista; ?>" class="btn btn-cancel">Descartar Cambios</a>
            <button type="submit" class="btn btn-save">💾 Guardar Expediente Completo</button>
        </div>
    </form>
</div>

<script>
function agregarContacto() {
    const contenedor = document.getElementById('contenedor-contactos');
    const nuevoDiv = document.createElement('div');
    nuevoDiv.className = 'contacto-item';
    nuevoDiv.innerHTML = `
        <div class="grid">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="emergencia_nombre[]" required>
            </div>
            <div class="form-group">
                <label>Parentesco</label>
                <input type="text" name="emergencia_parentesco[]" required>
            </div>
        </div>
        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="emergencia_telefono[]" required>
        </div>
    `;
    contenedor.appendChild(nuevoDiv);
}
</script>

</body>
</html>