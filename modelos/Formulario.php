<?php
/**
 * Modelo Formulario - Adaptado para campamento_db
 * Gestiona los formularios que deben ser completados/firmados por los padres
 */

class Formulario {
    private $conn;
    private $tabla = 'formularios';
    
    // Propiedades mapeadas a la BD
    public $id_formulario;
    public $titulo;
    public $descripcion;
    public $tipo_formulario; // consentimiento, medico, liberacion, otro
    public $archivo_url;
    public $es_obligatorio; // BOOLEAN
    public $anio_vigencia;
    public $fecha_subida;
    public $subido_por; // id_usuario
    public $estado; // activo, inactivo, archivado
    public $activo; // BOOLEAN (nuevo campo)
    public $fecha_limite; // DATE (nuevo campo)
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Obtener todos los formularios
     */
    public function obtenerTodos($filtros = []) {
        $query = "SELECT f.*, 
                         CONCAT(u.nombre, ' ', u.apellido) as creador_nombre,
                         (SELECT COUNT(*) FROM formularios_campistas 
                          WHERE id_formulario = f.id_formulario) as total_envios,
                         (SELECT COUNT(*) FROM formularios_campistas 
                          WHERE id_formulario = f.id_formulario AND estado = 'completado') as total_firmados
                  FROM " . $this->tabla . " f
                  LEFT JOIN usuarios u ON f.subido_por = u.id_usuario
                  WHERE 1=1";
        
        $params = [];
        
        // Filtros opcionales
        if (!empty($filtros['tipo_formulario'])) {
            $query .= " AND f.tipo_formulario = :tipo_formulario";
            $params[':tipo_formulario'] = $filtros['tipo_formulario'];
        }
        
        if (isset($filtros['estado'])) {
            $query .= " AND f.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        
        if (isset($filtros['activo'])) {
            $query .= " AND f.activo = :activo";
            $params[':activo'] = $filtros['activo'];
        }
        
        if (isset($filtros['es_obligatorio'])) {
            $query .= " AND f.es_obligatorio = :es_obligatorio";
            $params[':es_obligatorio'] = $filtros['es_obligatorio'];
        }
        
        if (isset($filtros['anio_vigencia'])) {
            $query .= " AND f.anio_vigencia = :anio_vigencia";
            $params[':anio_vigencia'] = $filtros['anio_vigencia'];
        } else {
            // Por defecto mostrar del año actual
            $query .= " AND f.anio_vigencia = YEAR(CURDATE())";
        }
        
        $query .= " ORDER BY f.fecha_subida DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener formulario por ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT f.*, 
                         CONCAT(u.nombre, ' ', u.apellido) as creador_nombre
                  FROM " . $this->tabla . " f
                  LEFT JOIN usuarios u ON f.subido_por = u.id_usuario
                  WHERE f.id_formulario = :id";
        
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
                      tipo_formulario = :tipo_formulario,
                      archivo_url = :archivo_url,
                      es_obligatorio = :es_obligatorio,
                      anio_vigencia = :anio_vigencia,
                      fecha_limite = :fecha_limite,
                      subido_por = :subido_por,
                      estado = :estado,
                      activo = :activo,
                      fecha_subida = NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->tipo_formulario = htmlspecialchars(strip_tags($this->tipo_formulario));
        $this->archivo_url = htmlspecialchars(strip_tags($this->archivo_url));
        
        // Bind datos
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':tipo_formulario', $this->tipo_formulario);
        $stmt->bindParam(':archivo_url', $this->archivo_url);
        $stmt->bindParam(':es_obligatorio', $this->es_obligatorio);
        $stmt->bindParam(':anio_vigencia', $this->anio_vigencia);
        $stmt->bindParam(':fecha_limite', $this->fecha_limite);
        $stmt->bindParam(':subido_por', $this->subido_por);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':activo', $this->activo);
        
        if ($stmt->execute()) {
            $this->id_formulario = $this->conn->lastInsertId();
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
                      tipo_formulario = :tipo_formulario,
                      es_obligatorio = :es_obligatorio,
                      fecha_limite = :fecha_limite,
                      estado = :estado,
                      activo = :activo";
        
        // Si hay nuevo archivo
        if (!empty($this->archivo_url)) {
            $query .= ", archivo_url = :archivo_url";
        }
        
        $query .= " WHERE id_formulario = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->tipo_formulario = htmlspecialchars(strip_tags($this->tipo_formulario));
        
        // Bind datos
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':tipo_formulario', $this->tipo_formulario);
        $stmt->bindParam(':es_obligatorio', $this->es_obligatorio);
        $stmt->bindParam(':fecha_limite', $this->fecha_limite);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':activo', $this->activo);
        $stmt->bindParam(':id', $this->id_formulario);
        
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
        // Primero eliminar las relaciones (CASCADE lo hace automáticamente)
        // Eliminar archivo físico si existe
        if (!empty($this->archivo_url) && file_exists(RUTA_RAIZ . '/' . $this->archivo_url)) {
            unlink(RUTA_RAIZ . '/' . $this->archivo_url);
        }
        
        // Eliminar formulario
        $query = "DELETE FROM " . $this->tabla . " WHERE id_formulario = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id_formulario);
        
        return $stmt->execute();
    }
    
    /**
     * Cambiar estado activo/inactivo
     */
    public function cambiarEstado($id, $activo) {
        $query = "UPDATE " . $this->tabla . " SET activo = :activo WHERE id_formulario = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':activo', $activo, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener estadísticas de un formulario
     */
    public function obtenerEstadisticas($id_formulario) {
        $query = "SELECT 
                    COUNT(DISTINCT fc.id_campista) as total_asignados,
                    COUNT(CASE WHEN fc.estado = 'completado' THEN 1 END) as total_firmados,
                    COUNT(CASE WHEN fc.estado = 'pendiente' THEN 1 END) as pendientes,
                    COUNT(CASE WHEN fc.estado = 'rechazado' THEN 1 END) as rechazados
                  FROM formularios_campistas fc
                  WHERE fc.id_formulario = :id_formulario";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_formulario', $id_formulario);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Asignar formulario a campistas específicos
     */
    public function asignarACampistas($id_formulario, $campistas_ids) {
        $query = "INSERT INTO formularios_campistas 
                  (id_formulario, id_campista, fecha_asignacion, estado)
                  VALUES (:id_formulario, :id_campista, NOW(), 'pendiente')
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
     * Asignar formulario a todos los campistas aprobados del año actual
     */
    public function asignarATodos($id_formulario) {
        $query = "INSERT INTO formularios_campistas 
                  (id_formulario, id_campista, fecha_asignacion, estado)
                  SELECT :id_formulario, id_campista, NOW(), 'pendiente'
                  FROM campistas
                  WHERE estado_inscripcion = 'aprobado'
                    AND anio_inscripcion = YEAR(CURDATE())
                  ON DUPLICATE KEY UPDATE fecha_asignacion = NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_formulario', $id_formulario);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener campistas que tienen formularios pendientes obligatorios
     */
    public function obtenerCampistasPendientesObligatorios() {
        $query = "SELECT DISTINCT
                    c.id_campista,
                    c.nombre,
                    c.apellido,
                    COUNT(*) as formularios_pendientes
                  FROM campistas c
                  INNER JOIN formularios_campistas fc ON c.id_campista = fc.id_campista
                  INNER JOIN formularios f ON fc.id_formulario = f.id_formulario
                  WHERE fc.estado = 'pendiente'
                    AND f.es_obligatorio = TRUE
                    AND f.estado = 'activo'
                    AND f.activo = TRUE
                    AND c.estado_inscripcion = 'aprobado'
                  GROUP BY c.id_campista
                  ORDER BY formularios_pendientes DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>