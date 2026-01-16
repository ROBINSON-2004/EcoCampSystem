<?php
require_once __DIR__ . '/../config/constantes.php';
require_once __DIR__ . '/../utilidades/funciones.php';
require_once __DIR__ . '/../modelos/Usuario.php';
require_once __DIR__ . '/../modelos/Padre.php';

/**
 * Controlador de Padres
 * Gestiona todas las operaciones CRUD de padres
 */
class PadreControlador {
    
    /**
     * Obtiene todos los padres
     * @return array Lista de padres
     */
    public function listarTodos() {
        $padre_modelo = new Padre();
        return $padre_modelo->leerTodos();
    }
    
    /**
     * Obtiene un padre por ID
     * @param int $id_padre ID del padre
     * @return array|bool Datos del padre o false
     */
    public function obtenerPorId($id_padre) {
        $padre_modelo = new Padre();
        $padre_modelo->id_padre = $id_padre;
        
        if ($padre_modelo->leerPorId()) {
            // Obtener también datos del usuario
            return $padre_modelo->obtenerInformacionCompleta();
        }
        
        return false;
    }
    
    /**
     * Actualiza información de un padre
     * @param array $datos Datos del formulario
     * @return array Respuesta con éxito y mensaje
     */
    public function actualizar($datos) {
        // Validar datos requeridos
        if (empty($datos['id_padre']) || empty($datos['id_usuario'])) {
            return [
                'exito' => false,
                'mensaje' => 'Datos incompletos.'
            ];
        }
        
        // Validar nombre y apellido
        if (empty($datos['nombre']) || empty($datos['apellido'])) {
            return [
                'exito' => false,
                'mensaje' => 'El nombre y apellido son obligatorios.'
            ];
        }
        
        // Validar teléfono
        if (!empty($datos['telefono']) && !validar_telefono($datos['telefono'])) {
            return [
                'exito' => false,
                'mensaje' => 'El número de teléfono no es válido.'
            ];
        }
        
        try {
            // Actualizar datos de usuario
            $usuario_modelo = new Usuario();
            $usuario_modelo->id_usuario = $datos['id_usuario'];
            $usuario_modelo->nombre = limpiar_cadena($datos['nombre']);
            $usuario_modelo->apellido = limpiar_cadena($datos['apellido']);
            $usuario_modelo->telefono = limpiar_cadena($datos['telefono']);
            $usuario_modelo->estado = $datos['estado'] ?? ESTADO_ACTIVO;
            
            if (!$usuario_modelo->actualizar()) {
                return [
                    'exito' => false,
                    'mensaje' => 'Error al actualizar datos del usuario.'
                ];
            }
            
            // Actualizar datos específicos de padre
            $padre_modelo = new Padre();
            $padre_modelo->id_padre = $datos['id_padre'];
            $padre_modelo->direccion = !empty($datos['direccion']) ? limpiar_cadena($datos['direccion']) : null;
            $padre_modelo->ciudad = !empty($datos['ciudad']) ? limpiar_cadena($datos['ciudad']) : null;
            $padre_modelo->codigo_postal = !empty($datos['codigo_postal']) ? limpiar_cadena($datos['codigo_postal']) : null;
            $padre_modelo->ocupacion = !empty($datos['ocupacion']) ? limpiar_cadena($datos['ocupacion']) : null;
            
            if ($padre_modelo->actualizar()) {
                registrar_log("Padre actualizado: ID {$datos['id_padre']}", 'INFO');
                
                return [
                    'exito' => true,
                    'mensaje' => MSG_EXITO_ACTUALIZAR
                ];
            }
            
        } catch (Exception $e) {
            registrar_log("Error al actualizar padre: " . $e->getMessage(), 'ERROR');
        }
        
        return [
            'exito' => false,
            'mensaje' => MSG_ERROR_GENERAL
        ];
    }
    
    /**
     * Desactiva un padre (soft delete)
     * @param int $id_usuario ID del usuario padre
     * @return array Respuesta con éxito y mensaje
     */
    public function desactivar($id_usuario) {
        if (empty($id_usuario)) {
            return [
                'exito' => false,
                'mensaje' => 'ID de usuario no válido.'
            ];
        }
        
        try {
            $usuario_modelo = new Usuario();
            $usuario_modelo->id_usuario = $id_usuario;
            
            if ($usuario_modelo->eliminar()) {
                registrar_log("Padre desactivado: ID Usuario $id_usuario", 'INFO');
                
                return [
                    'exito' => true,
                    'mensaje' => 'El padre ha sido desactivado correctamente.'
                ];
            }
            
        } catch (Exception $e) {
            registrar_log("Error al desactivar padre: " . $e->getMessage(), 'ERROR');
        }
        
        return [
            'exito' => false,
            'mensaje' => MSG_ERROR_GENERAL
        ];
    }
    
    /**
     * Reactiva un padre
     * @param int $id_usuario ID del usuario padre
     * @return array Respuesta con éxito y mensaje
     */
    public function activar($id_usuario) {
        if (empty($id_usuario)) {
            return [
                'exito' => false,
                'mensaje' => 'ID de usuario no válido.'
            ];
        }
        
        try {
            $usuario_modelo = new Usuario();
            $usuario_modelo->id_usuario = $id_usuario;
            $usuario_modelo->estado = ESTADO_ACTIVO;
            
            if ($usuario_modelo->leerPorId() && $usuario_modelo->actualizar()) {
                registrar_log("Padre reactivado: ID Usuario $id_usuario", 'INFO');
                
                return [
                    'exito' => true,
                    'mensaje' => 'El padre ha sido reactivado correctamente.'
                ];
            }
            
        } catch (Exception $e) {
            registrar_log("Error al reactivar padre: " . $e->getMessage(), 'ERROR');
        }
        
        return [
            'exito' => false,
            'mensaje' => MSG_ERROR_GENERAL
        ];
    }
    
    /**
     * Busca padres por término
     * @param string $termino Término de búsqueda
     * @return array Lista de padres filtrados
     */
    public function buscar($termino) {
        $todos_padres = $this->listarTodos();
        
        if (empty($termino)) {
            return $todos_padres;
        }
        
        $termino = strtolower($termino);
        $resultados = [];
        
        foreach ($todos_padres as $padre) {
            $nombre_completo = strtolower($padre['nombre'] . ' ' . $padre['apellido']);
            $correo = strtolower($padre['correo_electronico']);
            
            if (strpos($nombre_completo, $termino) !== false || 
                strpos($correo, $termino) !== false) {
                $resultados[] = $padre;
            }
        }
        
        return $resultados;
    }
    
    /**
     * Obtiene estadísticas de padres
     * @return array Estadísticas
     */
    public function obtenerEstadisticas() {
        $todos_padres = $this->listarTodos();
        
        $activos = 0;
        $inactivos = 0;
        
        foreach ($todos_padres as $padre) {
            if ($padre['estado'] === ESTADO_ACTIVO) {
                $activos++;
            } else {
                $inactivos++;
            }
        }
        
        return [
            'total' => count($todos_padres),
            'activos' => $activos,
            'inactivos' => $inactivos
        ];
    }
}
?>