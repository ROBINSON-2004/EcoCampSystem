<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_CONFIG . '/conexion.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

$id_grupo = $_GET['id'] ?? null;
$db = (new Conexion())->obtenerConexion();

// 1. Obtener información del grupo actual
$stmt_g = $db->prepare("SELECT id_grupo, nombre_grupo, id_consejero FROM grupos WHERE id_grupo = :id");
$stmt_g->execute([':id' => $id_grupo]);
$grupo = $stmt_g->fetch(PDO::FETCH_ASSOC);

// 2. Obtener lista de todos los usuarios tipo 'consejero' activos
$stmt_c = $db->prepare("SELECT id_usuario, nombre, apellido FROM usuarios WHERE tipo_usuario = 'consejero' AND estado = 'activo'");
$stmt_c->execute();
$consejeros = $stmt_c->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container" style="max-width: 600px; margin: 40px auto;">
    <div class="card shadow p-4">
        <h2>👨‍🏫 Asignar Consejero</h2>
        <p>Grupo: <strong><?php echo htmlspecialchars($grupo['nombre_grupo']); ?></strong></p>
        
        <form action="procesar_asignacion_consejero.php" method="POST">
            <input type="hidden" name="id_grupo" value="<?php echo $id_grupo; ?>">
            
            <div class="form-group" style="margin-top: 20px;">
                <label for="id_consejero">Seleccionar Consejero Responsable:</label>
                <select name="id_consejero" id="id_consejero" class="form-control" required>
                    <option value="">-- Seleccione un trabajador --</option>
                    <?php foreach ($consejeros as $con): ?>
                        <option value="<?php echo $con['id_usuario']; ?>" 
                            <?php echo ($grupo['id_consejero'] == $con['id_usuario']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($con['nombre'] . " " . $con['apellido']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-top: 25px; display: flex; gap: 10px;">
                <button type="submit" class="btn-save" style="flex: 1; background: #2c5282; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
                    Guardar Asignación
                </button>
                <a href="detalle.php?id=<?php echo $id_grupo; ?>" class="btn-secondary" style="text-decoration: none; color: #666; padding: 10px;">Cancelar</a>
            </div>
        </form>
    </div>
</div>