<?php
class Permisos {

    public static function requerirRol($roles_permitidos) {

        // 1️⃣ Verificar que el usuario esté autenticado
        Sesion::requerirAutenticacion();

        // 2️⃣ Obtener rol actual
        $rol_actual = Sesion::obtenerTipoUsuario();

        // 3️⃣ Normalizar roles permitidos
        $roles = is_array($roles_permitidos)
            ? $roles_permitidos
            : [$roles_permitidos];

        // 4️⃣ Validar permiso
        if (!in_array($rol_actual, $roles)) {

            if ($rol_actual === TIPO_PADRE) {
                header("Location: " . URL_BASE . "/vistas/padre/dashboard.php");
            } else {
                header("Location: " . URL_BASE . "/panel.php");
            }
            exit();
        }
    }
}
