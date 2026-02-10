<?php
// 1. Carga de configuración y herramientas
require_once __DIR__ . '/config/constantes.php';
require_once RUTA_CONFIG . '/conexion.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';

// Inicializar la sesión
Sesion::iniciar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Limpiar entradas para evitar inyecciones
    $correo = limpiar_cadena($_POST['correo']);
    $password_ingresada = $_POST['password'];

    try {
        $db = Conexion::conectar();
        
        /**
         * 2. Consulta a la Base de Datos
         * Usamos 'contrasena' y 'tipo_usuario' tal como definiste en tu SQL.
         */
        $query = "SELECT id_usuario, nombre, apellido, contrasena, tipo_usuario 
                  FROM usuarios 
                  WHERE correo_electronico = :correo AND estado = 'activo' 
                  LIMIT 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        /**
         * 3. Verificación de Credenciales
         * Tu SQL usa un hash ($2y$10$...), por lo que usamos password_verify.
         */
        if ($usuario && password_verify($password_ingresada, $usuario['contrasena'])) {
            
            /**
             * 4. Creación de la Sesión
             * MAPEAMOS 'tipo_usuario' de la DB a la llave 'tipo' que busca el Panel.
             */
            $datos_sesion = [
                'id' => $usuario['id_usuario'],
                'nombre' => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'tipo' => $usuario['tipo_usuario'] // <-- Esto soluciona el error detectado: ''
            ];

            // Usamos el método polimórfico que creamos en Sesion::establecer()
            Sesion::establecer($datos_sesion);

            // Actualizar último acceso (Opcional)
            $update = $db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id");
            $update->execute([':id' => $usuario['id_usuario']]);

            // Redirigir al enrutador central
            header('Location: panel.php');
            exit();

        } else {
            // Error: Credenciales no coinciden
            Sesion::establecer('error', 'El correo o la contraseña son incorrectos.');
            header('Location: index.php');
            exit();
        }

    } catch (PDOException $e) {
        // Error de conexión o SQL
        Sesion::establecer('error', 'Error en el sistema. Por favor intenta más tarde.');
        header('Location: index.php');
        exit();
    }
} else {
    // Si se intenta entrar por URL sin enviar el formulario
    header('Location: index.php');
    exit();
}