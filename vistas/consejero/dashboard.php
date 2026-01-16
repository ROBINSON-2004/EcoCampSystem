<?php
Sesion::requerirTipoUsuario(TIPO_CONSEJERO);
$datos_usuario = Sesion::obtenerDatosUsuario();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Consejero - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; min-height: 100vh; }
        .header { background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%); color: white; padding: 20px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.8rem; }
        .btn-logout { background: rgba(255,255,255,0.2); color: white; padding: 8px 20px; border: 1px solid rgba(255,255,255,0.3); border-radius: 6px; text-decoration: none; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 40px; }
        .welcome { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
        .welcome h2 { color: #333; font-size: 2rem; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏕️ Panel Consejero</h1>
        <a href="<?php echo URL_BASE; ?>/logout.php" class="btn-logout">Cerrar Sesión</a>
    </div>
    <div class="container">
        <div class="welcome">
            <h2>Bienvenido, <?php echo $datos_usuario['nombre']; ?>! 🎯</h2>
            <p>Este es tu panel de consejero</p>
        </div>
    </div>
</body>
</html>