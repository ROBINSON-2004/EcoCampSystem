<?php
/**
 * Modelo Formulario
 * Gestiona los formularios que deben ser completados/firmados por los padres
 */

class Formulario {
    private $conn;
    private $tabla = 'formularios';
    
    // Propiedades
    public $id;
    public $titulo;
    public $descripcion;
    public $archivo_url;
    public $tipo; // 'consentimiento', 'medico', 'fotografico', 'otro'
    public $obligatorio; // 1 o 0
    public $activo; // 1 o 0
    public $fecha_creacion;
    public $fecha_limite;
    public $creado_por; // ID del administrador
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Obtener todos los formularios
     */
    public function obtenerTodos($filtros = []) {
        $query = "SELECT f.*, 
                         u.nombre as creador_nombre,
                         (SELECT COUNT(*) FROM formularios_campistas 
                          WHERE id_formulario = f.id) as total_envios,
                         (SELECT COUNT(*) FROM formularios_campistas 
                          WHERE id_formulario = f.id AND firmado = 1) as total_firmados
                  FROM " . $this->tabla . " f
                  LEFT JOIN usuarios u ON f.creado_por = u.id
                  WHERE 1=1";
        
        $params = [];
        
        // Filtros opcionales
        if (!empty($filtros['tipo'])) {
            $query .= " AND f.tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }
        
        if (isset($filtros['activo'])) {
            $query .= " AND f.activo = :activo";
            $params[':activo'] = $filtros['activo'];
        }
        
        if (isset($filtros['obligatorio'])) {
            $query .= " AND f.obligatorio = :obligatorio";
            $params[':obligatorio'] = $filtros['obligatorio'];
        }
        
        $query .= " ORDER BY f.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener formulario por ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT f.*, 
                         u.nombre as creador_nombre
                  FROM " . $this->tabla . " f
                  LEFT JOIN usuarios u ON f.creado_por = u.id
                  WHERE f.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nuevo formulario
     */
    public function crear() {
        $query = "INSERT INTO " . $this->tabla . "
                  SET titulo = :titulo,
                      descripcion = :descripcion,
                      archivo_url = :archivo_url,
                      tipo = :tipo,
                      obligatorio = :obligatorio,
                      activo = :activo,
                      fecha_limite = :fecha_limite,
                      creado_por = :creado_por,
                      fecha_creacion = NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->archivo_url = htmlspecialchars(strip_tags($this->archivo_url));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        
        // Bind datos
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':archivo_url', $this->archivo_url);
        $stmt->bindParam(':tipo', $this->tipo);
        $stmt->bindParam(':obligatorio', $this->obligatorio);
        $stmt->bindParam(':activo', $this->activo);
        $stmt->bindParam(':fecha_limite', $this->fecha_limite);
        $stmt->bindParam(':creado_por', $this->creado_por);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Actualizar formulario
     */
    public function actualizar() {
        $query = "UPDATE " . $this->tabla . "
                  SET titulo = :titulo,
                      descripcion = :descripcion,
                      tipo = :tipo,
                      obligatorio = :obligatorio,
                      activo = :activo,
                      fecha_limite = :fecha_limite";
        
        // Si hay nuevo archivo
        if (!empty($this->archivo_url)) {
            $query .= ", archivo_url = :archivo_url";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        
        // Bind datos
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':tipo', $this->tipo);
        $stmt->bindParam(':obligatorio', $this->obligatorio);
        $stmt->bindParam(':activo', $this->activo);
        $stmt->bindParam(':fecha_limite', $this->fecha_limite);
        $stmt->bindParam(':id', $this->id);
        
        if (!empty($this->archivo_url)) {
            $this->archivo_url = htmlspecialchars(strip_tags($this->archivo_url));
            $stmt->bindParam(':archivo_url', $this->archivo_url);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar formulario
     */
    public function eliminar() {
        // Primero eliminar las relaciones
        $query = "DELETE FROM formularios_campistas WHERE id_formulario = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        // Eliminar archivo físico si existe
        if (!empty($this->archivo_url) && file_exists($this->archivo_url)) {
            unlink($this->archivo_url);
        }
        
        // Eliminar formulario
        $query = "DELETE FROM " . $this->tabla . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Cambiar estado activo/inactivo
     */
    public function cambiarEstado($id, $estado) {
        $query = "UPDATE " . $this->tabla . " SET activo = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener estadísticas de un formulario
     */
    public function obtenerEstadisticas($id_formulario) {
        $query = "SELECT 
                    COUNT(DISTINCT fc.id_campista) as total_asignados,
                    COUNT(CASE WHEN fc.firmado = 1 THEN 1 END) as total_firmados,
                    COUNT(CASE WHEN fc.firmado = 0 THEN 1 END) as pendientes
                  FROM formularios_campistas fc
                  WHERE fc.id_formulario = :id_formulario";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_formulario', $id_formulario);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Asignar formulario a campistas
     */
    public function asignarACampistas($id_formulario, $campistas_ids) {
        $query = "INSERT INTO formularios_campistas 
                  (id_formulario, id_campista, fecha_asignacion)
                  VALUES (:id_formulario, :id_campista, NOW())
                  ON DUPLICATE KEY UPDATE fecha_asignacion = NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($campistas_ids as $id_campista) {
            $stmt->bindParam(':id_formulario', $id_formulario);
            $stmt->bindParam(':id_campista', $id_campista);
            $stmt->execute();
        }
        
        return true;
    }
    
    /**
     * Asignar formulario a todos los campistas activos
     */
    public function asignarATodos($id_formulario) {
        $query = "INSERT INTO formularios_campistas 
                  (id_formulario, id_campista, fecha_asignacion)
                  SELECT :id_formulario, id, NOW()
                  FROM campistas
                  WHERE activo = 1
                  ON DUPLICATE KEY UPDATE fecha_asignacion = NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_formulario', $id_formulario);
        
        return $stmt->execute();
    }
}
?>