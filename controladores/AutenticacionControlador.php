<?php
/**
 * ============================================
 * CONTROLADOR DE AUTENTICACIÓN - EcoCampSystem
 * ============================================
 */

require_once __DIR__ . '/../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Usuario.php';

class AutenticacionControlador {
    
    /**
     * Procesa el inicio de sesión
     * @param array $datos Datos del formulario ($_POST)
     * @return array Respuesta con éxito, mensaje y URL de redirección
     */
    public function iniciarSesion($datos) {
        // 1. Validar campos obligatorios
        if (empty($datos['correo']) || empty($datos['contrasena'])) {
            return [
                'exito' => false,
                'mensaje' => 'Por favor completa todos los campos.'
            ];
        }
        
        // 2. Validar formato de correo electrónico
        if (!validar_correo($datos['correo'])) {
            return [
                'exito' => false,
                'mensaje' => 'El correo electrónico no es válido.'
            ];
        }
        
        // 3. Intentar autenticar con el modelo Usuario
        $usuario_modelo = new Usuario();
        $usuario = $usuario_modelo->autenticar($datos['correo'], $datos['contrasena']);
        
        if ($usuario) {
            // Establecer datos de sesión
            $usuario['recordar'] = isset($datos['recordar']) ? true : false;
            Sesion::establecer($usuario);
            
            // Registrar en log
            registrar_log("Inicio de sesión exitoso: {$datos['correo']}", 'INFO');
            
            /**
             * LÓGICA DE REDIRECCIÓN:
             * Mapeamos el tipo de usuario a su respectiva carpeta en /vistas/
             * Si es 'administrador', la carpeta es 'admin'.
             */
            $tipo = $usuario['tipo_usuario'];
            $carpeta = ($tipo === TIPO_ADMINISTRADOR) ? 'admin' : $tipo;
            
            $url_destino = URL_BASE . "/vistas/{$carpeta}/dashboard.php";
            
            return [
                'exito' => true,
                'mensaje' => 'Inicio de sesión exitoso.',
                'tipo_usuario' => $tipo,
                'url_redireccion' => $url_destino
            ];
        } else {
            // Registrar intento fallido
            registrar_log("Intento de inicio de sesión fallido: {$datos['correo']}", 'WARNING');
            
            return [
                'exito' => false,
                'mensaje' => 'Credenciales incorrectas o cuenta inactiva.'
            ];
        }
    }
    
    /**
     * Procesa el registro de un nuevo padre
     * @param array $datos Datos del formulario
     * @return array Respuesta con éxito y mensaje
     */
    public function registrarPadre($datos) {
        // Validar datos requeridos
        $campos_requeridos = ['nombre', 'apellido', 'correo', 'telefono', 'contrasena', 'confirmar_contrasena'];
        
        foreach ($campos_requeridos as $campo) {
            if (empty($datos[$campo])) {
                return [
                    'exito' => false,
                    'mensaje' => 'Por favor completa todos los campos obligatorios.'
                ];
            }
        }
        
        // Validar que las contraseñas coincidan
        if ($datos['contrasena'] !== $datos['confirmar_contrasena']) {
            return [
                'exito' => false,
                'mensaje' => 'Las contraseñas no coinciden.'
            ];
        }
        
        // Verificar si el correo ya existe
        $usuario_modelo = new Usuario();
        if ($usuario_modelo->correoExiste($datos['correo'])) {
            return [
                'exito' => false,
                'mensaje' => 'Este correo electrónico ya está registrado.'
            ];
        }
        
        // Configurar y crear el Usuario
        $usuario_modelo->nombre = limpiar_cadena($datos['nombre']);
        $usuario_modelo->apellido = limpiar_cadena($datos['apellido']);
        $usuario_modelo->correo_electronico = limpiar_cadena($datos['correo']);
        $usuario_modelo->telefono = limpiar_cadena($datos['telefono']);
        $usuario_modelo->contrasena = $datos['contrasena'];
        $usuario_modelo->tipo_usuario = TIPO_PADRE;
        $usuario_modelo->estado = ESTADO_ACTIVO;
        
        $id_usuario = $usuario_modelo->crear();
        
        if ($id_usuario) {
            // Crear el registro complementario en la tabla Padres
            require_once RUTA_MODELOS . '/Padre.php';
            $padre_modelo = new Padre();
            $padre_modelo->id_usuario = $id_usuario;
            $padre_modelo->direccion = !empty($datos['direccion']) ? limpiar_cadena($datos['direccion']) : null;
            
            if ($padre_modelo->crear()) {
                registrar_log("Nuevo padre registrado: {$datos['correo']}", 'INFO');
                return [
                    'exito' => true,
                    'mensaje' => 'Registro exitoso. Ya puedes iniciar sesión.'
                ];
            }
        }
        
        return [
            'exito' => false,
            'mensaje' => 'Error al crear la cuenta. Por favor intenta nuevamente.'
        ];
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function cerrarSesion() {
        $usuario_data = Sesion::obtenerDatosUsuario();
        $correo = $usuario_data['correo'] ?? 'Desconocido';
        
        Sesion::destruir();
        registrar_log("Cierre de sesión: $correo", 'INFO');
        
        header('Location: ' . URL_BASE . '/index.php');
        exit;
    }
    
    /**
     * Envía correo de recuperación de contraseña
     * @param string $correo Correo del usuario
     * @return array Respuesta
     */
    public function recuperarContrasena($correo) {
        if (!validar_correo($correo)) {
            return ['exito' => false, 'mensaje' => 'El correo electrónico no es válido.'];
        }
        
        $usuario_modelo = new Usuario();
        $usuario_modelo->correo_electronico = $correo;
        
        if ($usuario_modelo->leerPorCorreo()) {
            registrar_log("Solicitud de recuperación de contraseña: $correo", 'INFO');
            // Aquí iría la lógica de generación de token y envío de email
        }
        
        return [
            'exito' => true,
            'mensaje' => 'Si el correo existe, recibirás instrucciones para recuperar tu contraseña.'
        ];
    }
}
?>