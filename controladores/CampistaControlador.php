<?php
require_once __DIR__ . '/../config/constantes.php';
require_once __DIR__ . '/../utilidades/funciones.php';
require_once __DIR__ . '/../modelos/Campista.php';

/**
 * Controlador de Campistas
 * Gestiona todas las operaciones CRUD de campistas
 */
class CampistaControlador {
    
    /**
     * Obtiene todos los campistas
     * @param string $estado Filtrar por estado
     * @param int $anio Filtrar por año
     * @return array Lista de campistas
     */
    public function listarTodos($estado = null, $anio = null) {
        $campista_modelo = new Campista();
        return $campista_modelo->leerTodos($estado, $anio);
    }
    
    /**
     * Obtiene un campista por ID
     * @param int $id_campista ID del campista
     * @return array|bool Datos del campista o false
     */
    public function obtenerPorId($id_campista) {
        $campista_modelo = new Campista();
        $campista_modelo->id_campista = $id_campista;
        
        if ($campista_modelo->leerPorId()) {
            return $campista_modelo->obtenerInformacionCompleta();
        }
        
        return false;
    }
    
    /**
     * Crea un nuevo campista
     * @param array $datos Datos del formulario
     * @return array Respuesta con éxito y mensaje
     */
    public function crear($datos) {
        // Validar datos requeridos
        $campos_requeridos = ['nombre', 'apellido', 'fecha_nacimiento', 'genero', 'id_padre'];
        
        foreach ($campos_requeridos as $campo) {
            if (empty($datos[$campo])) {
                return [
                    'exito' => false,
                    'mensaje' => 'Por favor completa todos los campos obligatorios.'
                ];
            }
        }
        
        // Validar fecha de nacimiento
        if (!validar_fecha($datos['fecha_nacimiento'])) {
            return [
                'exito' => false,
                'mensaje' => 'La fecha de nacimiento no es válida.'
            ];
        }
        
        // Calcular edad
        $edad = calcular_edad($datos['fecha_nacimiento']);
        
        try {
            $campista_modelo = new Campista();
            $campista_modelo->nombre = limpiar_cadena($datos['nombre']);
            $campista_modelo->apellido = limpiar_cadena($datos['apellido']);
            $campista_modelo->fecha_nacimiento = $datos['fecha_nacimiento'];
            $campista_modelo->edad = $edad;
            $campista_modelo->genero = $datos['genero'];
            $campista_modelo->id_padre = $datos['id_padre'];
            $campista_modelo->foto_perfil = $datos['foto_perfil'] ?? null;
            $campista_modelo->notas_especiales = !empty($datos['notas_especiales']) ? limpiar_cadena($datos['notas_especiales']) : null;
            $campista_modelo->estado_inscripcion = $datos['estado_inscripcion'] ?? INSCRIPCION_PENDIENTE;
            $campista_modelo->anio_inscripcion = $datos['anio_inscripcion'] ?? ANIO_CAMPAMENTO_ACTUAL;
            
            $id_campista = $campista_modelo->crear();
            
            if ($id_campista) {
                registrar_log("Campista creado: ID $id_campista", 'INFO');
                
                return [
                    'exito' => true,
                    'mensaje' => 'Campista registrado correctamente.',
                    'id_campista' => $id_campista
                ];
            }
            
        } catch (Exception $e) {
            registrar_log("Error al crear campista: " . $e->getMessage(), 'ERROR');
        }
        
        return [
            'exito' => false,
            'mensaje' => MSG_ERROR_GENERAL
        ];
    }
    
    /**
     * Actualiza un campista
     * @param array $datos Datos del formulario
     * @return array Respuesta con éxito y mensaje
     */
    public function actualizar($datos) {
        // Validar datos requeridos
        if (empty($datos['id_campista'])) {
            return [
                'exito' => false,
                'mensaje' => 'ID de campista no válido.'
            ];
        }
        
        // Validar campos obligatorios
        if (empty($datos['nombre']) || empty($datos['apellido']) || empty($datos['fecha_nacimiento'])) {
            return [
                'exito' => false,
                'mensaje' => 'El nombre, apellido y fecha de nacimiento son obligatorios.'
            ];
        }
        
        // Validar fecha de nacimiento
        if (!validar_fecha($datos['fecha_nacimiento'])) {
            return [
                'exito' => false,
                'mensaje' => 'La fecha de nacimiento no es válida.'
            ];
        }
        
        // Calcular edad
        $edad = calcular_edad($datos['fecha_nacimiento']);
        
        try {
            $campista_modelo = new Campista();
            $campista_modelo->id_campista = $datos['id_campista'];
            $campista_modelo->nombre = limpiar_cadena($datos['nombre']);
            $campista_modelo->apellido = limpiar_cadena($datos['apellido']);
            $campista_modelo->fecha_nacimiento = $datos['fecha_nacimiento'];
            $campista_modelo->edad = $edad;
            $campista_modelo->genero = $datos['genero'];
            $campista_modelo->foto_perfil = $datos['foto_perfil'] ?? null;
            $campista_modelo->notas_especiales = !empty($datos['notas_especiales']) ? limpiar_cadena($datos['notas_especiales']) : null;
            $campista_modelo->estado_inscripcion = $datos['estado_inscripcion'] ?? INSCRIPCION_PENDIENTE;
            
            if ($campista_modelo->actualizar()) {
                registrar_log("Campista actualizado: ID {$datos['id_campista']}", 'INFO');
                
                return [
                    'exito' => true,
                    'mensaje' => MSG_EXITO_ACTUALIZAR
                ];
            }
            
        } catch (Exception $e) {
            registrar_log("Error al actualizar campista: " . $e->getMessage(), 'ERROR');
        }
        
        return [
            'exito' => false,
            'mensaje' => MSG_ERROR_GENERAL
        ];
    }
    
    /**
     * Elimina un campista (soft delete)
     * @param int $id_campista ID del campista
     * @return array Respuesta con éxito y mensaje
     */
    public function eliminar($id_campista) {
        if (empty($id_campista)) {
            return [
                'exito' => false,
                'mensaje' => 'ID de campista no válido.'
            ];
        }
        
        try {
            $campista_modelo = new Campista();
            $campista_modelo->id_campista = $id_campista;
            
            if ($campista_modelo->eliminar()) {
                registrar_log("Campista eliminado: ID $id_campista", 'INFO');
                
                return [
                    'exito' => true,
                    'mensaje' => 'El campista ha sido retirado correctamente.'
                ];
            }
            
        } catch (Exception $e) {
            registrar_log("Error al eliminar campista: " . $e->getMessage(), 'ERROR');
        }
        
        return [
            'exito' => false,
            'mensaje' => MSG_ERROR_GENERAL
        ];
    }
    
    /**
     * Busca campistas por término
     * @param string $termino Término de búsqueda
     * @return array Lista de campistas filtrados
     */
    public function buscar($termino) {
        if (empty($termino)) {
            return $this->listarTodos();
        }
        
        $campista_modelo = new Campista();
        return $campista_modelo->buscar($termino);
    }
    
    /**
     * Obtiene estadísticas de campistas
     * @return array Estadísticas
     */
    public function obtenerEstadisticas() {
        $campista_modelo = new Campista();
        $por_estado = $campista_modelo->contarPorEstado();
        
        $stats = [
            'total' => 0,
            'aprobados' => 0,
            'pendientes' => 0,
            'rechazados' => 0,
            'retirados' => 0
        ];
        
        foreach ($por_estado as $estado) {
            $stats['total'] += $estado['total'];
            
            switch ($estado['estado_inscripcion']) {
                case INSCRIPCION_APROBADO:
                    $stats['aprobados'] = $estado['total'];
                    break;
                case INSCRIPCION_PENDIENTE:
                    $stats['pendientes'] = $estado['total'];
                    break;
                case INSCRIPCION_RECHAZADO:
                    $stats['rechazados'] = $estado['total'];
                    break;
                case INSCRIPCION_RETIRADO:
                    $stats['retirados'] = $estado['total'];
                    break;
            }
        }
        
        return $stats;
    }
    
    /**
     * Cambia el estado de inscripción de un campista
     * @param int $id_campista ID del campista
     * @param string $nuevo_estado Nuevo estado
     * @return array Respuesta
     */
    public function cambiarEstado($id_campista, $nuevo_estado) {
        $estados_validos = [INSCRIPCION_PENDIENTE, INSCRIPCION_APROBADO, INSCRIPCION_RECHAZADO, INSCRIPCION_RETIRADO];
        
        if (!in_array($nuevo_estado, $estados_validos)) {
            return [
                'exito' => false,
                'mensaje' => 'Estado no válido.'
            ];
        }
        
        try {
            $campista_modelo = new Campista();
            $campista_modelo->id_campista = $id_campista;
            
            if ($campista_modelo->leerPorId()) {
                $campista_modelo->estado_inscripcion = $nuevo_estado;
                
                if ($campista_modelo->actualizar()) {
                    registrar_log("Estado de campista cambiado: ID $id_campista a $nuevo_estado", 'INFO');
                    
                    return [
                        'exito' => true,
                        'mensaje' => 'Estado actualizado correctamente.'
                    ];
                }
            }
            
        } catch (Exception $e) {
            registrar_log("Error al cambiar estado: " . $e->getMessage(), 'ERROR');
        }
        
        return [
            'exito' => false,
            'mensaje' => MSG_ERROR_GENERAL
        ];
    }
}
?>