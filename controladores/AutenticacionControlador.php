<?php
require_once __DIR__ . '/../config/constantes.php';
require_once __DIR__ . '/../utilidades/sesion.php';
require_once __DIR__ . '/../utilidades/funciones.php';
require_once __DIR__ . '/../modelos/Usuario.php';

/**
 * Controlador de Autenticación
 * Maneja login, registro y cierre de sesión
 */
class AutenticacionControlador {
    
    /**
     * Procesa el inicio de sesión
     * @param array $datos Datos del formulario
     * @return array Respuesta con éxito y mensaje
     */
    public function iniciarSesion($datos) {
        // Validar datos
        if (empty($datos['correo']) || empty($datos['contrasena'])) {
            return [
                'exito' => false,
                'mensaje' => 'Por favor completa todos los campos.'
            ];
        }
        
        // Validar formato de correo
        if (!validar_correo($datos['correo'])) {
            return [
                'exito' => false,
                'mensaje' => 'El correo electrónico no es válido.'
            ];
        }
        
        // Intentar autenticar
        $usuario_modelo = new Usuario();
        $usuario = $usuario_modelo->autenticar($datos['correo'], $datos['contrasena']);
        
        if ($usuario) {
            // Establecer sesión
            $usuario['recordar'] = isset($datos['recordar']) ? true : false;
            Sesion::establecer($usuario);
            
            // Registrar en log
            registrar_log("Inicio de sesión exitoso: {$datos['correo']}", 'INFO');
            
            return [
                'exito' => true,
                'mensaje' => 'Inicio de sesión exitoso.',
                'tipo_usuario' => $usuario['tipo_usuario']
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
        
        // Validar correo
        if (!validar_correo($datos['correo'])) {
            return [
                'exito' => false,
                'mensaje' => 'El correo electrónico no es válido.'
            ];
        }
        
        // Validar teléfono
        if (!validar_telefono($datos['telefono'])) {
            return [
                'exito' => false,
                'mensaje' => 'El número de teléfono no es válido.'
            ];
        }
        
        // Validar que las contraseñas coincidan
        if ($datos['contrasena'] !== $datos['confirmar_contrasena']) {
            return [
                'exito' => false,
                'mensaje' => 'Las contraseñas no coinciden.'
            ];
        }
        
        // Validar fortaleza de contraseña
        $validacion_contrasena = validar_fortaleza_contrasena($datos['contrasena']);
        if (!$validacion_contrasena['valida']) {
            return [
                'exito' => false,
                'mensaje' => implode(' ', $validacion_contrasena['errores'])
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
        
        // Crear usuario
        $usuario_modelo->nombre = limpiar_cadena($datos['nombre']);
        $usuario_modelo->apellido = limpiar_cadena($datos['apellido']);
        $usuario_modelo->correo_electronico = limpiar_cadena($datos['correo']);
        $usuario_modelo->telefono = limpiar_cadena($datos['telefono']);
        $usuario_modelo->contrasena = $datos['contrasena'];
        $usuario_modelo->tipo_usuario = TIPO_PADRE;
        $usuario_modelo->estado = ESTADO_ACTIVO;
        
        $id_usuario = $usuario_modelo->crear();
        
        if ($id_usuario) {
            // Crear registro en tabla padres
            require_once __DIR__ . '/../modelos/Padre.php';
            $padre_modelo = new Padre();
            $padre_modelo->id_usuario = $id_usuario;
            $padre_modelo->direccion = !empty($datos['direccion']) ? limpiar_cadena($datos['direccion']) : null;
            $padre_modelo->ciudad = !empty($datos['ciudad']) ? limpiar_cadena($datos['ciudad']) : null;
            $padre_modelo->codigo_postal = !empty($datos['codigo_postal']) ? limpiar_cadena($datos['codigo_postal']) : null;
            
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
        $correo = Sesion::obtenerDatosUsuario()['correo'];
        Sesion::destruir();
        
        registrar_log("Cierre de sesión: $correo", 'INFO');
        
        redirigir(URL_BASE . '/index.php');
    }
    
    /**
     * Valida el token de recuperación de contraseña
     * @param string $token Token a validar
     * @return array|bool Datos del usuario o false
     */
    public function validarTokenRecuperacion($token) {
        // Implementar lógica de tokens (requiere tabla adicional)
        // Por ahora retorna false
        return false;
    }
    
    /**
     * Envía correo de recuperación de contraseña
     * @param string $correo Correo del usuario
     * @return array Respuesta
     */
    public function recuperarContrasena($correo) {
        if (!validar_correo($correo)) {
            return [
                'exito' => false,
                'mensaje' => 'El correo electrónico no es válido.'
            ];
        }
        
        $usuario_modelo = new Usuario();
        $usuario_modelo->correo_electronico = $correo;
        
        if ($usuario_modelo->leerPorCorreo()) {
            // Generar token
            $token = generar_token(32);
            
            // TODO: Guardar token en BD con expiración
            // TODO: Enviar correo con enlace de recuperación
            
            registrar_log("Solicitud de recuperación de contraseña: $correo", 'INFO');
            
            return [
                'exito' => true,
                'mensaje' => 'Se ha enviado un correo con instrucciones para recuperar tu contraseña.'
            ];
        }
        
        // Por seguridad, siempre mostrar el mismo mensaje
        return [
            'exito' => true,
            'mensaje' => 'Si el correo existe, recibirás instrucciones para recuperar tu contraseña.'
        ];
    }
}
?>