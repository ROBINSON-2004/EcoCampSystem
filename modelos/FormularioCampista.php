<?php
/**
 * Modelo FormularioCampista - Adaptado para campamento_db
 * Gestiona la relación entre formularios y campistas (asignación, firma, seguimiento)
 */

class FormularioCampista {
    private $conn;
    private $tabla = 'formularios_campistas';
    
    // Propiedades
    public $id_formulario_campista;
    public $id_formulario;
    public $id_campista;
    public $fecha_firma;
    public $firmado_por; // id_usuario del padre
    public $archivo_firmado;
    public $estado; // pendiente, completado, rechazado
    public $observaciones;
    public $fecha_asignacion;
    public $ip_firma;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Obtener formularios asignados a un campista
     */
    public function obtenerPorCampista($id_campista, $filtros = []) {
        $query = "SELECT fc.*, 
                         f.titulo, 
                         f.descripcion, 
                         f.archivo_url,
                         f.tipo_formulario,
                         f.es_obligatorio,
                         f.fecha_limite,
                         c.nombre as campista_nombre,
                         c.apellido as campista_apellido
                  FROM " . $this->tabla . " fc
                  INNER JOIN formularios f ON fc.id_formulario = f.id_formulario
                  INNER JOIN campistas c ON fc.id_campista = c.id_campista
                  WHERE fc.id_campista = :id_campista
                  AND f.estado = 'activo'
                  AND f.activo = TRUE";
        
        $params = [':id_campista' => $id_campista];
        
        // Filtros opcionales
        if (isset($filtros['estado'])) {
            $query .= " AND fc.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['tipo_formulario'])) {
            $query .= " AND f.tipo_formulario = :tipo_formulario";
            $params[':tipo_formulario'] = $filtros['tipo_formulario'];
        }
        
        $query .= " ORDER BY f.es_obligatorio DESC, f.fecha_limite ASC, fc.fecha_asignacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener formularios de todos los hijos de un padre
     */
    public function obtenerPorPadre($id_padre, $filtros = []) {
        $query = "SELECT fc.*, 
                         f.titulo, 
                         f.descripcion, 
                         f.archivo_url,
                         f.tipo_formulario,
                         f.es_obligatorio,
                         f.fecha_limite,
                         c.nombre as campista_nombre,
                         c.apellido as campista_apellido,
                         c.id_campista
                  FROM " . $this->tabla . " fc
                  INNER JOIN formularios f ON fc.id_formulario = f.id_formulario
                  INNER JOIN campistas c ON fc.id_campista = c.id_campista
                  WHERE c.id_padre = :id_padre
                  AND f.estado = 'activo'
                  AND f.activo = TRUE";
        
        $params = [':id_padre' => $id_padre];
        
        // Filtros opcionales
        if (isset($filtros['estado'])) {
            $query .= " AND fc.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['campista_id'])) {
            $query .= " AND fc.id_campista = :campista_id";
            $params[':campista_id'] = $filtros['campista_id'];
        }
        
        $query .= " ORDER BY fc.estado ASC, f.es_obligatorio DESC, f.fecha_limite ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener campistas que tienen asignado un formulario
     */
    public function obtenerCampistasPorFormulario($id_formulario, $filtros = []) {
        $query = "SELECT fc.*, 
                         c.nombre as campista_nombre,
                         c.apellido as campista_apellido,
                         c.fecha_nacimiento,
                         g.nombre_grupo,
                         u.nombre as padre_nombre,
                         u.apellido as padre_apellido,
                         u.correo_electronico as padre_email,
                         u.telefono as padre_telefono
                  FROM " . $this->tabla . " fc
                  INNER JOIN campistas c ON fc.id_campista = c.id_campista
                  LEFT JOIN campistas_grupos cg ON c.id_campista = cg.id_campista AND cg.estado = 'activo'
                  LEFT JOIN grupos g ON cg.id_grupo = g.id_grupo
                  INNER JOIN padres p ON c.id_padre = p.id_padre
                  INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                  WHERE fc.id_formulario = :id_formulario";
        
        $params = [':id_formulario' => $id_formulario];
        
        // Filtros opcionales
        if (isset($filtros['estado'])) {
            $query .= " AND fc.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        
        $query .= " ORDER BY fc.estado ASC, c.apellido ASC, c.nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener un formulario específico de un campista
     */
    public function obtenerFormularioCampista($id_formulario, $id_campista) {
        $query = "SELECT fc.*, 
                         f.titulo, 
                         f.descripcion, 
                         f.archivo_url,
                         f.tipo_formulario,
                         f.es_obligatorio,
                         f.fecha_limite,
                         c.nombre as campista_nombre,
                         c.apellido as campista_apellido
                  FROM " . $this->tabla . " fc
                  INNER JOIN formularios f ON fc.id_formulario = f.id_formulario
                  INNER JOIN campistas c ON fc.id_campista = c.id_campista
                  WHERE fc.id_formulario = :id_formulario 
                  AND fc.id_campista = :id_campista";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_formulario', $id_formulario);
        $stmt->bindParam(':id_campista', $id_campista);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Firmar formulario
     */
    public function firmar($id_formulario, $id_campista, $archivo_firmado = null, $ip = null, $firmado_por = null) {
        $query = "UPDATE " . $this->tabla . "
                  SET estado = 'completado',
                      fecha_firma = NOW(),
                      archivo_firmado = :archivo_firmado,
                      ip_firma = :ip,
                      firmado_por = :firmado_por
                  WHERE id_formulario = :id_formulario 
                  AND id_campista = :id_campista";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':archivo_firmado', $archivo_firmado);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':firmado_por', $firmado_por);
        $stmt->bindParam(':id_formulario', $id_formulario);
        $stmt->bindParam(':id_campista', $id_campista);
        
        return $stmt->execute();
    }
    
    /**
     * Agregar observaciones a un formulario
     */
    public function agregarObservaciones($id, $observaciones) {
        $query = "UPDATE " . $this->tabla . "
                  SET observaciones = :observaciones
                  WHERE id_formulario_campista = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar si un campista tiene formularios pendientes obligatorios
     */
    public function tienePendientesObligatorios($id_campista) {
        $query = "SELECT COUNT(*) as total
                  FROM " . $this->tabla . " fc
                  INNER JOIN formularios f ON fc.id_formulario = f.id_formulario
                  WHERE fc.id_campista = :id_campista 
                  AND fc.estado = 'pendiente'
                  AND f.estado = 'activo'
                  AND f.activo = TRUE
                  AND f.es_obligatorio = TRUE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_campista', $id_campista);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }
    
    /**
     * Obtener formularios pendientes de un padre
     */
    public function obtenerPendientesPadre($id_padre) {
        $query = "SELECT COUNT(*) as total_pendientes,
                         COUNT(DISTINCT fc.id_campista) as campistas_con_pendientes
                  FROM " . $this->tabla . " fc
                  INNER JOIN formularios f ON fc.id_formulario = f.id_formulario
                  INNER JOIN campistas c ON fc.id_campista = c.id_campista
                  WHERE c.id_padre = :id_padre 
                  AND fc.estado = 'pendiente'
                  AND f.estado = 'activo'
                  AND f.activo = TRUE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_padre', $id_padre);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener formularios próximos a vencer
     */
    public function obtenerProximosVencer($dias = 3) {
        $query = "SELECT fc.*, 
                         f.titulo, 
                         f.fecha_limite,
                         c.nombre as campista_nombre,
                         c.apellido as campista_apellido,
                         u.correo_electronico as padre_email,
                         u.nombre as padre_nombre,
                         DATEDIFF(f.fecha_limite, CURRENT_DATE) as dias_restantes
                  FROM " . $this->tabla . " fc
                  INNER JOIN formularios f ON fc.id_formulario = f.id_formulario
                  INNER JOIN campistas c ON fc.id_campista = c.id_campista
                  INNER JOIN padres p ON c.id_padre = p.id_padre
                  INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                  WHERE fc.estado = 'pendiente'
                  AND f.estado = 'activo'
                  AND f.activo = TRUE
                  AND f.fecha_limite IS NOT NULL
                  AND f.fecha_limite BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL :dias DAY)
                  ORDER BY f.fecha_limite ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Eliminar asignación de formulario
     */
    public function eliminarAsignacion($id_formulario, $id_campista) {
        $query = "DELETE FROM " . $this->tabla . " 
                  WHERE id_formulario = :id_formulario 
                  AND id_campista = :id_campista";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_formulario', $id_formulario);
        $stmt->bindParam(':id_campista', $id_campista);
        
        return $stmt->execute();
    }
}
?>