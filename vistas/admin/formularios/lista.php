<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_MODELOS . '/Formulario.php';

Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

$modelo = new Formulario();
$plantillas = $modelo->leerTodos(false); // Trae todos los años y estados
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Formularios | <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="<?php echo URL_PUBLIC; ?>/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <header class="header" style="display: flex; justify-content: space-between; align-items: center;">
            <h1>📑 Plantillas de Formularios</h1>
            <div class="actions">
                <a href="subir.php" class="btn-login" style="text-decoration: none;">+ Nuevo Formulario</a>
                <a href="seguimiento.php" class="btn-secondary">Ver Entregas</a>
                <a href="<?php echo URL_BASE; ?>/panel.php" class="btn-back">
                    <span>←</span> Inicio
                </a>
            </div>
        </header>

        <table class="table" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Vigencia</th>
                    <th>Obligatorio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plantillas as $f): ?>
                <tr>
                    <td><strong><?php echo $f['titulo']; ?></strong></td>
                    <td><span class="badge"><?php echo strtoupper($f['tipo_formulario']); ?></span></td>
                    <td><?php echo $f['anio_vigencia']; ?></td>
                    <td><?php echo $f['es_obligatorio'] ? '✅ Sí' : '❌ No'; ?></td>
                    <td>
                        <span class="badge" style="background: <?php echo ($f['estado']=='activo') ? '#e8f5e9' : '#ffebee'; ?>; color: #333;">
                            <?php echo strtoupper($f['estado']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?php echo URL_UPLOADS . '/formularios/' . $f['archivo_url']; ?>" target="_blank" style="color: #2196F3;">Descargar</a> |
                        <a href="editar.php?id=<?php echo $f['id_formulario']; ?>" style="color: #ff9800;">Editar</a>
                        </a>
                                <span>|</span>
                                <a href="eliminar.php?id=<?php echo $f['id_formulario']; ?>" 
                                   title="Eliminar plantilla" style="color: #d32f2f; font-weight: bold;">
                                   🗑️ Eliminar
                                </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>