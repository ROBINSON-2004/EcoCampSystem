<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once __DIR__ . '/../../../utilidades/sesion.php';
require_once __DIR__ . '/../../../utilidades/funciones.php';
require_once __DIR__ . '/../../../controladores/GrupoControlador.php';
require_once __DIR__ . '/../../../modelos/Campista.php';

Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

if (!isset($_GET['id']) || !es_numero_valido($_GET['id'])) {
    Sesion::establecerMensaje('error', 'ID de grupo no válido.');
    header('Location: lista.php');
    exit();
}

$id_grupo = (int)$_GET['id'];
$controlador = new GrupoControlador();
$grupo = $controlador->obtenerPorId($id_grupo);

if (!$grupo) {
    Sesion::establecerMensaje('error', 'Grupo no encontrado.');
    header('Location: lista.php');
    exit();
}

// Procesar asignación/remoción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        if ($_POST['accion'] === 'asignar' && isset($_POST['id_campista'])) {
            $resultado = $controlador->asignarCampista($_POST['id_campista'], $id_grupo);
            Sesion::establecerMensaje($resultado['exito'] ? 'exito' : 'error', $resultado['mensaje']);
        } elseif ($_POST['accion'] === 'remover' && isset($_POST['id_campista'])) {
            $resultado = $controlador->removerCampista($_POST['id_campista'], $id_grupo);
            Sesion::establecerMensaje($resultado['exito'] ? 'exito' : 'error', $resultado['mensaje']);
        }
        header('Location: asignar-campistas.php?id=' . $id_grupo);
        exit();
    }
}

// Obtener campistas del grupo
$campistas_grupo = $controlador->obtenerCampistas($id_grupo);
$ids_en_grupo = array_column($campistas_grupo, 'id_campista');

// Obtener campistas disponibles (aprobados y no en este grupo)
$campista_modelo = new Campista();
$todos_campistas = $campista_modelo->leerTodos(INSCRIPCION_APROBADO, ANIO_CAMPAMENTO_ACTUAL);
$campistas_disponibles = array_filter($todos_campistas, function($c) use ($ids_en_grupo, $grupo) {
    $en_rango = true;
    if ($grupo['edad_minima'] && $grupo['edad_maxima']) {
        $en_rango = ($c['edad'] >= $grupo['edad_minima'] && $c['edad'] <= $grupo['edad_maxima']);
    }
    return !in_array($c['id_campista'], $ids_en_grupo) && $en_rango;
});

$mensaje = Sesion::obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Campistas - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.5rem; }
        .header a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; transition: 0.3s; }
        .header a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 1400px; margin: 30px auto; padding: 0 30px; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #efe; color: #3c3; border-left: 4px solid #3c3; }
        .alert-error { background: #fee; color: #c33; border-left: 4px solid #c33; }
        .info-box { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .info-box h2 { color: #333; margin-bottom: 10px; }
        .info-box p { color: #666; }
        .columns { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .column { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .column h3 { color: #333; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; }
        .campista-card { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .campista-info h4 { color: #333; margin-bottom: 5px; }
        .campista-info p { color: #666; font-size: 0.9rem; }
        .btn { padding: 8px 15px; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: 0.3s; }
        .btn-success { background: #48bb78; color: white; }
        .btn-danger { background: #f56565; color: white; }
        .btn:hover { transform: translateY(-2px); }
        .empty { text-align: center; padding: 40px; color: #999; }
        @media (max-width: 768px) { .columns { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>👦 Asignar Campistas al Grupo</h1>
        <a href="detalle.php?id=<?php echo $id_grupo; ?>">← Volver</a>
    </div>
    
    <div class="container">
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $mensaje['tipo'] === 'exito' ? 'success' : 'error'; ?>">
                <?php echo $mensaje['contenido']; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <h2><?php echo htmlspecialchars($grupo['nombre_grupo']); ?></h2>
            <p><strong>Capacidad:</strong> <?php echo $grupo['total_campistas']; ?> / <?php echo $grupo['capacidad_maxima']; ?> campistas</p>
            <?php if ($grupo['edad_minima'] && $grupo['edad_maxima']): ?>
                <p><strong>Rango de edades:</strong> <?php echo $grupo['edad_minima']; ?> - <?php echo $grupo['edad_maxima']; ?> años</p>
            <?php endif; ?>
        </div>
        
        <div class="columns">
            <div class="column">
                <h3>Campistas Disponibles (<?php echo count($campistas_disponibles); ?>)</h3>
                <?php if (count($campistas_disponibles) > 0): ?>
                    <?php foreach ($campistas_disponibles as $campista): ?>
                        <div class="campista-card">
                            <div class="campista-info">
                                <h4><?php echo htmlspecialchars($campista['nombre'] . ' ' . $campista['apellido']); ?></h4>
                                <p><?php echo $campista['edad']; ?> años - <?php echo ucfirst($campista['genero']); ?></p>
                            </div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="accion" value="asignar">
                                <input type="hidden" name="id_campista" value="<?php echo $campista['id_campista']; ?>">
                                <button type="submit" class="btn btn-success">➕ Asignar</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty">No hay campistas disponibles</div>
                <?php endif; ?>
            </div>
            
            <div class="column">
                <h3>Campistas en el Grupo (<?php echo count($campistas_grupo); ?>)</h3>
                <?php if (count($campistas_grupo) > 0): ?>
                    <?php foreach ($campistas_grupo as $campista): ?>
                        <div class="campista-card">
                            <div class="campista-info">
                                <h4><?php echo htmlspecialchars($campista['nombre'] . ' ' . $campista['apellido']); ?></h4>
                                <p><?php echo $campista['edad']; ?> años - <?php echo ucfirst($campista['genero']); ?></p>
                            </div>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('¿Remover este campista del grupo?');">
                                <input type="hidden" name="accion" value="remover">
                                <input type="hidden" name="id_campista" value="<?php echo $campista['id_campista']; ?>">
                                <button type="submit" class="btn btn-danger">✖ Remover</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty">No hay campistas asignados</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>