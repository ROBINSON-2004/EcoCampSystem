<?php
/**
 * Modelo FormularioCampista
 * Gestiona la relación entre formularios y campistas (asignación, firma, seguimiento)
 */

class FormularioCampista {
    private $conn;
    private $tabla = 'formularios_campistas';
    
    // Propiedades
    public $id;
    public $id_formulario;
    public $id_campista;
    public $firmado;
    public $fecha_firma;
    public $fecha_asignacion;
    public $documento_firmado_url;
    public $ip_firma;
    public $observaciones;
    
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
                         f.tipo,
                         f.obligatorio,
                         f.fecha_limite,
                         c.nombre as campista_nombre,
                         c.apellidos as campista_apellidos
                  FROM " . $this->tabla . " fc
                  INNER JOIN formularios f ON fc.id_formulario = f.id
                  INNER JOIN campistas c ON fc.id_campista = c.id
                  WHERE fc.id_campista = :id_campista
                  AND f.activo = 1";
        
        $params = [':id_campista' => $id_campista];
        
        // Filtros opcionales
        if (isset($filtros['firmado'])) {
            $query .= " AND fc.firmado = :firmado";
            $params[':firmado'] = $filtros['firmado'];
        }
        
        if (!empty($filtros['tipo'])) {
            $query .= " AND f.tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }
        
        $query .= " ORDER BY f.obligatorio DESC, f.fecha_limite ASC, fc.fecha_asignacion DESC";
        
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
                         f.tipo,
                         f.obligatorio,
                         f.fecha_limite,
                         c.nombre as campista_nombre,
                         c.apellidos as campista_apellidos,
                         c.id as campista_id
                  FROM " . $this->tabla . " fc
                  INNER JOIN formularios f ON fc.id_formulario = f.id
                  INNER JOIN campistas c ON fc.id_campista = c.id
                  INNER JOIN padres_campistas pc ON c.id = pc.id_campista
                  WHERE pc.id_padre = :id_padre
                  AND f.activo = 1";
        
        $params = [':id_padre' => $id_padre];
        
        // Filtros opcionales
        if (isset($filtros['firmado'])) {
            $query .= " AND fc.firmado = :firmado";
            $params[':firmado'] = $filtros['firmado'];
        }
        
        if (!empty($filtros['campista_id'])) {
            $query .= " AND fc.id_campista = :campista_id";
            $params[':campista_id'] = $filtros['campista_id'];
        }
        
        $query .= " ORDER BY fc.firmado ASC, f.obligatorio DESC, f.fecha_limite ASC";
        
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
                         c.apellidos as campista_apellidos,
                         c.fecha_nacimiento,
                         g.nombre as grupo_nombre,
                         p.nombre as padre_nombre,
                         p.apellidos as padre_apellidos,
                         p.email as padre_email,
                         p.telefono as padre_telefono
                  FROM " . $this->tabla . " fc
                  INNER JOIN campistas c ON fc.id_campista = c.id
                  LEFT JOIN grupos g ON c.id_grupo = g.id
                  LEFT JOIN padres_campistas pc ON c.id = pc.id_campista
                  LEFT JOIN padres p ON pc.id_padre = p.id
                  WHERE fc.id_formulario = :id_formulario";
        
        $params = [':id_formulario' => $id_formulario];
        
        // Filtros opcionales
        if (isset($filtros['firmado'])) {
            $query .= " AND fc.firmado = :firmado";
            $params[':firmado'] = $filtros['firmado'];
        }
        
        $query .= " ORDER BY fc.firmado ASC, c.apellidos ASC, c.nombre ASC";
        
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
                         f.tipo,
                         f.obligatorio,
                         f.fecha_limite,
                         c.nombre as campista_nombre,
                         c.apellidos as campista_apellidos
                  FROM " . $this->tabla . " fc
                  INNER JOIN formularios f ON fc.id_formulario = f.id
                  INNER JOIN campistas c ON fc.id_campista = c.id
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
    public function firmar($id_formulario, $id_campista, $documento_url = null, $ip = null) {
        $query = "UPDATE " . $this->tabla . "
                  SET firmado = 1,
                      fecha_firma = NOW(),
                      documento_firmado_url = :documento_url,
                      ip_firma = :ip
                  WHERE id_formulario = :id_formulario 
                  AND id_campista = :id_campista";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':documento_url', $documento_url);
        $stmt->bindParam(':ip', $ip);
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
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar si un campista tiene formularios pendientes
     */
    public function tienePendientes($id_campista) {
        $query = "SELECT COUNT(*) as total
                  FROM " . $this->tabla . " fc
                  INNER JOIN formularios f ON fc.id_formulario = f.id
                  WHERE fc.id_campista = :id_campista 
                  AND fc.firmado = 0
                  AND f.activo = 1
                  AND f.obligatorio = 1";
        
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
                  INNER JOIN formularios f ON fc.id_formulario = f.id
                  INNER JOIN campistas c ON fc.id_campista = c.id
                  INNER JOIN padres_campistas pc ON c.id = pc.id_campista
                  WHERE pc.id_padre = :id_padre 
                  AND fc.firmado = 0
                  AND f.activo = 1";
        
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
                         c.apellidos as campista_apellidos,
                         p.email as padre_email,
                         p.nombre as padre_nombre
                  FROM " . $this->tabla . " fc
                  INNER JOIN formularios f ON fc.id_formulario = f.id
                  INNER JOIN campistas c ON fc.id_campista = c.id
                  LEFT JOIN padres_campistas pc ON c.id = pc.id_campista
                  LEFT JOIN padres p ON pc.id_padre = p.id
                  WHERE fc.firmado = 0
                  AND f.activo = 1
                  AND f.fecha_limite IS NOT NULL
                  AND f.fecha_limite BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :dias DAY)
                  ORDER BY f.fecha_limite ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dias', $dias);
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