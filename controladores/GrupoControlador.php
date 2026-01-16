<?php
require_once __DIR__ . '/../config/constantes.php';
require_once __DIR__ . '/../utilidades/funciones.php';
require_once __DIR__ . '/../modelos/Grupo.php';

/**
 * Controlador de Grupos
 * Gestiona todas las operaciones CRUD de grupos
 */
class GrupoControlador {
    
    /**
     * Obtiene todos los grupos
     * @param string $estado Filtrar por estado
     * @param int $anio Filtrar por año
     * @return array Lista de grupos
     */
    public function listarTodos($estado = null, $anio = null) {
        $grupo_modelo = new Grupo();
        return $grupo_modelo->leerTodos($estado, $anio);
    }
    
    /**
     * Obtiene un grupo por ID
     * @param int $id_grupo ID del grupo
     * @return array|bool Datos del grupo o false
     */
    public function obtenerPorId($id_grupo) {
        $grupo_modelo = new Grupo();
        $grupo_modelo->id_grupo = $id_grupo;
        
        if ($grupo_modelo->leerPorId()) {
            // Obtener información completa
            $conexion = new Conexion();
            $db = $conexion->obtenerConexion();
            
            $query = "SELECT g.*,
                      u.nombre as nombre_consejero,
                      u.apellido as apellido_consejero,
                      u.correo_electronico as correo_consejero,
                      (SELECT COUNT(*) FROM campistas_grupos cg 
                       WHERE cg.id_grupo = g.id_grupo AND cg.estado = 'activo') as total_campistas
                      FROM grupos g
                      LEFT JOIN usuarios u ON g.id_consejero = u.id_usuario
                      WHERE g.id_grupo = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id_grupo);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return false;
    }
    
    /**
     * Crea un nuevo grupo
     * @param array $datos Datos del formulario
     * @return array Respuesta con éxito y mensaje
     */
    public function crear($datos) {
        // Validar datos requeridos
        if (empty($datos['nombre_grupo'])) {
            return [
                'exito' => false,
                'mensaje' => 'El nombre del grupo es obligatorio.'
            ];
        }
        
        // Validar edades
        if (!empty($datos['edad_minima']) && !empty($datos['edad_maxima'])) {
            if ($datos['edad_minima'] > $datos['edad_maxima']) {
                return [
                    'exito' => false,
                    'mensaje' => 'La edad mínima no puede ser mayor que la edad máxima.'
                ];
            }
        }
        
        try {
            $grupo_modelo = new Grupo();
            $grupo_modelo->nombre_grupo = limpiar_cadena($datos['nombre_grupo']);
            $grupo_modelo->descripcion = !empty($datos['descripcion']) ? limpiar_cadena($datos['descripcion']) : null;
            $grupo_modelo->edad_minima = !empty($datos['edad_minima']) ? (int)$datos['edad_minima'] : null;
            $grupo_modelo->edad_maxima = !empty($datos['edad_maxima']) ? (int)$datos['edad_maxima'] : null;
            $grupo_modelo->capacidad_maxima = !empty($datos['capacidad_maxima']) ? (int)$datos['capacidad_maxima'] : 20;
            $grupo_modelo->id_consejero = !empty($datos['id_consejero']) ? (int)$datos['id_consejero'] : null;
            $grupo_modelo->anio_campamento = $datos['anio_campamento'] ?? ANIO_CAMPAMENTO_ACTUAL;
            $grupo_modelo->estado = $datos['estado'] ?? 'activo';
            
            $id_grupo = $grupo_modelo->crear();
            
            if ($id_grupo) {
                registrar_log("Grupo creado: ID $id_grupo", 'INFO');
                
                return [
                    'exito' => true,
                    'mensaje' => 'Grupo creado correctamente.',
                    'id_grupo' => $id_grupo
                ];
            }
            
        } catch (Exception $e) {
            registrar_log("Error al crear grupo: " . $e->getMessage(), 'ERROR');
        }
        
        return [
            'exito' => false,
            'mensaje' => MSG_ERROR_GENERAL
        ];
    }
    
    /**
     * Actualiza un grupo
     * @param array $datos Datos del formulario
     * @return array Respuesta con éxito y mensaje
     */
    public function actualizar($datos) {
        if (empty($datos['id_grupo'])) {
            return [
                'exito' => false,
                'mensaje' => 'ID de grupo no válido.'
            ];
        }
        
        if (empty($datos['nombre_grupo'])) {
            return [
                'exito' => false,
                'mensaje' => 'El nombre del grupo es obligatorio.'
            ];
        }
        
        // Validar edades
        if (!empty($datos['edad_minima']) && !empty($datos['edad_maxima'])) {
            if ($datos['edad_minima'] > $datos['edad_maxima']) {
                return [
                    'exito' => false,
                    'mensaje' => 'La edad mínima no puede ser mayor que la edad máxima.'
                ];
            }
        }
        
        try {
            $grupo_modelo = new Grupo();
            $grupo_modelo->id_grupo = $datos['id_grupo'];
            $grupo_modelo->nombre_grupo = limpiar_cadena($datos['nombre_grupo']);
            $grupo_modelo->descripcion = !empty($datos['descripcion']) ? limpiar_cadena($datos['descripcion']) : null;
            $grupo_modelo->edad_minima = !empty($datos['edad_minima']) ? (int)$datos['edad_minima'] : null;
            $grupo_modelo->edad_maxima = !empty($datos['edad_maxima']) ? (int)$datos['edad_maxima'] : null;
            $grupo_modelo->capacidad_maxima = !empty($datos['capacidad_maxima']) ? (int)$datos['capacidad_maxima'] : 20;
            $grupo_modelo->id_consejero = !empty($datos['id_consejero']) ? (int)$datos['id_consejero'] : null;
            $grupo_modelo->estado = $datos['estado'] ?? 'activo';
            
            if ($grupo_modelo->actualizar()) {
                registrar_log("Grupo actualizado: ID {$datos['id_grupo']}", 'INFO');
                
                return [
                    'exito' => true,
                    'mensaje' => MSG_EXITO_ACTUALIZAR
                ];
            }
            
        } catch (Exception $e) {
            registrar_log("Error al actualizar grupo: " . $e->getMessage(), 'ERROR');
        }
        
        return [
            'exito' => false,
            'mensaje' => MSG_ERROR_GENERAL
        ];
    }
    
    /**
     * Elimina un grupo (soft delete)
     * @param int $id_grupo ID del grupo
     * @return array Respuesta con éxito y mensaje
     */
    public function eliminar($id_grupo) {
        if (empty($id_grupo)) {
            return [
                'exito' => false,
                'mensaje' => 'ID de grupo no válido.'
            ];
        }
        
        try {
            $grupo_modelo = new Grupo();
            $grupo_modelo->id_grupo = $id_grupo;
            
            if ($grupo_modelo->eliminar()) {
                registrar_log("Grupo eliminado: ID $id_grupo", 'INFO');
                
                return [
                    'exito' => true,
                    'mensaje' => 'El grupo ha sido desactivado correctamente.'
                ];
            }
            
        } catch (Exception $e) {
            registrar_log("Error al eliminar grupo: " . $e->getMessage(), 'ERROR');
        }
        
        return [
            'exito' => false,
            'mensaje' => MSG_ERROR_GENERAL
        ];
    }
    
    /**
     * Obtiene estadísticas de grupos
     * @return array Estadísticas
     */
    public function obtenerEstadisticas() {
        $grupo_modelo = new Grupo();
        $por_estado = $grupo_modelo->contarPorEstado(ANIO_CAMPAMENTO_ACTUAL);
        
        $stats = [
            'total' => 0,
            'activos' => 0,
            'inactivos' => 0,
            'completos' => 0
        ];
        
        foreach ($por_estado as $estado) {
            $stats['total'] += $estado['total'];
            
            switch ($estado['estado']) {
                case 'activo':
                    $stats['activos'] = $estado['total'];
                    break;
                case 'inactivo':
                    $stats['inactivos'] = $estado['total'];
                    break;
                case 'completo':
                    $stats['completos'] = $estado['total'];
                    break;
            }
        }
        
        return $stats;
    }
    
    /**
     * Obtiene campistas de un grupo
     * @param int $id_grupo ID del grupo
     * @return array Lista de campistas
     */
    public function obtenerCampistas($id_grupo) {
        try {
            $conexion = new Conexion();
            $db = $conexion->obtenerConexion();
            
            $query = "SELECT c.*, cg.fecha_asignacion, cg.estado as estado_grupo
                      FROM campistas c
                      INNER JOIN campistas_grupos cg ON c.id_campista = cg.id_campista
                      WHERE cg.id_grupo = :id_grupo AND cg.estado = 'activo'
                      ORDER BY c.nombre, c.apellido";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_grupo', $id_grupo);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            registrar_log("Error al obtener campistas del grupo: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    /**
     * Asigna un campista a un grupo
     * @param int $id_campista ID del campista
     * @param int $id_grupo ID del grupo
     * @return array Respuesta
     */
    public function asignarCampista($id_campista, $id_grupo) {
        try {
            $conexion = new Conexion();
            $db = $conexion->obtenerConexion();
            
            // Verificar si ya está asignado
            $query_check = "SELECT id_campista_grupo FROM campistas_grupos 
                           WHERE id_campista = :campista AND id_grupo = :grupo AND estado = 'activo'";
            $stmt_check = $db->prepare($query_check);
            $stmt_check->bindParam(':campista', $id_campista);
            $stmt_check->bindParam(':grupo', $id_grupo);
            $stmt_check->execute();
            
            if ($stmt_check->rowCount() > 0) {
                return [
                    'exito' => false,
                    'mensaje' => 'El campista ya está asignado a este grupo.'
                ];
            }
            
            // Asignar campista
            $query_insert = "INSERT INTO campistas_grupos (id_campista, id_grupo, estado) 
                            VALUES (:campista, :grupo, 'activo')";
            $stmt_insert = $db->prepare($query_insert);
            $stmt_insert->bindParam(':campista', $id_campista);
            $stmt_insert->bindParam(':grupo', $id_grupo);
            
            if ($stmt_insert->execute()) {
                registrar_log("Campista $id_campista asignado al grupo $id_grupo", 'INFO');
                
                return [
                    'exito' => true,
                    'mensaje' => 'Campista asignado correctamente.'
                ];
            }
            
        } catch (Exception $e) {
            registrar_log("Error al asignar campista: " . $e->getMessage(), 'ERROR');
        }
        
        return [
            'exito' => false,
            'mensaje' => MSG_ERROR_GENERAL
        ];
    }
    
    /**
     * Remueve un campista de un grupo
     * @param int $id_campista ID del campista
     * @param int $id_grupo ID del grupo
     * @return array Respuesta
     */
    public function removerCampista($id_campista, $id_grupo) {
        try {
            $conexion = new Conexion();
            $db = $conexion->obtenerConexion();
            
            $query = "UPDATE campistas_grupos SET estado = 'retirado' 
                     WHERE id_campista = :campista AND id_grupo = :grupo";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':campista', $id_campista);
            $stmt->bindParam(':grupo', $id_grupo);
            
            if ($stmt->execute()) {
                registrar_log("Campista $id_campista removido del grupo $id_grupo", 'INFO');
                
                return [
                    'exito' => true,
                    'mensaje' => 'Campista removido del grupo.'
                ];
            }
            
        } catch (Exception $e) {
            registrar_log("Error al remover campista: " . $e->getMessage(), 'ERROR');
        }
        
        return [
            'exito' => false,
            'mensaje' => MSG_ERROR_GENERAL
        ];
    }
}
?>