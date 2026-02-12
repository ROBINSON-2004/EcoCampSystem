<?php
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
Sesion::requerirTipoUsuario(TIPO_ADMINISTRADOR);

// Aquí procesarías el POST enviando los datos a tu UsuarioControlador
?>
<div class="container">
    <div class="card shadow" style="max-width: 500px; margin: auto;">
        <h2>👤 Registrar Nuevo Consejero</h2>
        <form action="procesar_registro.php" method="POST">
            <input type="hidden" name="tipo_usuario" value="consejero">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Correo Electrónico (Para Login)</label>
                <input type="email" name="correo" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Contraseña Temporal</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-save">Crear Cuenta de Consejero</button>
        </form>
    </div>
</div>