<?php
require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase Grupo
 * Modelo para gestionar grupos del campamento
 */
class Grupo {
    private $conexion;
    private $tabla = 'grupos';
    
    // Propiedades
    public $id_grupo;
    public $nombre_grupo;
    public $descripcion;
    public $edad_minima;
    public $edad_maxima;
    public $capacidad_maxima;
    public $id_consejero;
    public $anio_campamento;
    public $estado;
    public $fecha_creacion;
    
    /**
     * Constructor
     */
    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }
    
    /**
     * Crea un nuevo grupo
     * @return bool|int ID del grupo creado o false
     */
    public function crear() {
        $consulta = "INSERT INTO " . $this->tabla . " 
                    (nombre_grupo, descripcion, edad_minima, edad_maxima, capacidad_maxima,
                     id_consejero, anio_campamento, estado)
                    VALUES (:nombre, :descripcion, :edad_min, :edad_max, :capacidad,
                            :consejero, :anio, :estado)";
        
        $stmt = $this->conexion->prepare($consulta);
        
        // Limpiar datos
        $this->nombre_grupo = htmlspecialchars(strip_tags($this->nombre_grupo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        
        // Bind
        $stmt->bindParam(':nombre', $this->nombre_grupo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':edad_min', $this->edad_minima);
        $stmt->bindParam(':edad_max', $this->edad_maxima);
        $stmt->bindParam(':capacidad', $this->capacidad_maxima);
        $stmt->bindParam(':consejero', $this->id_consejero);
        $stmt->bindParam(':anio', $this->anio_campamento);
        $stmt->bindParam(':estado', $this->estado);
        
        if ($stmt->execute()) {
            return $this->conexion->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Lee un grupo por ID
     * @return bool
     */
    public function leerPorId() {
        $consulta = "SELECT * FROM " . $this->tabla . " WHERE id_grupo = :id LIMIT 1";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_grupo);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($fila) {
            $this->nombre_grupo = $fila['nombre_grupo'];
            $this->descripcion = $fila['descripcion'];
            $this->edad_minima = $fila['edad_minima'];
            $this->edad_maxima = $fila['edad_maxima'];
            $this->capacidad_maxima = $fila['capacidad_maxima'];
            $this->id_consejero = $fila['id_consejero'];
            $this->anio_campamento = $fila['anio_campamento'];
            $this->estado = $fila['estado'];
            $this->fecha_creacion = $fila['fecha_creacion'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtiene todos los grupos con información del consejero
     * @param string $estado Filtrar por estado
     * @param int $anio Filtrar por año
     * @return array
     */
    public function leerTodos($estado = null, $anio = null) {
        $consulta = "SELECT g.*,
                     u.nombre as nombre_consejero,
                     u.apellido as apellido_consejero,
                     (SELECT COUNT(*) FROM campistas_grupos cg 
                      WHERE cg.id_grupo = g.id_grupo AND cg.estado = 'activo') as total_campistas
                     FROM " . $this->tabla . " g
                     LEFT JOIN usuarios u ON g.id_consejero = u.id_usuario
                     WHERE 1=1";
        
        if ($estado) {
            $consulta .= " AND g.estado = :estado";
        }
        
        if ($anio) {
            $consulta .= " AND g.anio_campamento = :anio";
        }
        
        $consulta .= " ORDER BY g.nombre_grupo ASC";
        
        $stmt = $this->conexion->prepare($consulta);
        
        if ($estado) {
            $stmt->bindParam(':estado', $estado);
        }
        
        if ($anio) {
            $stmt->bindParam(':anio', $anio);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualiza un grupo
     * @return bool
     */
    public function actualizar() {
        $consulta = "UPDATE " . $this->tabla . "
                    SET nombre_grupo = :nombre,
                        descripcion = :descripcion,
                        edad_minima = :edad_min,
                        edad_maxima = :edad_max,
                        capacidad_maxima = :capacidad,
                        id_consejero = :consejero,
                        estado = :estado
                    WHERE id_grupo = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        
        // Limpiar datos
        $this->nombre_grupo = htmlspecialchars(strip_tags($this->nombre_grupo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        
        // Bind
        $stmt->bindParam(':nombre', $this->nombre_grupo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':edad_min', $this->edad_minima);
        $stmt->bindParam(':edad_max', $this->edad_maxima);
        $stmt->bindParam(':capacidad', $this->capacidad_maxima);
        $stmt->bindParam(':consejero', $this->id_consejero);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':id', $this->id_grupo);
        
        return $stmt->execute();
    }
    
    /**
     * Elimina un grupo (soft delete)
     * @return bool
     */
    public function eliminar() {
        $consulta = "UPDATE " . $this->tabla . "
                    SET estado = 'inactivo'
                    WHERE id_grupo = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_grupo);
        
        return $stmt->execute();
    }
    
    /**
     * Cuenta grupos por estado
     * @param int $anio Año del campamento
     * @return array
     */
    public function contarPorEstado($anio = null) {
        $consulta = "SELECT estado, COUNT(*) as total
                    FROM " . $this->tabla;
        
        if ($anio) {
            $consulta .= " WHERE anio_campamento = :anio";
        }
        
        $consulta .= " GROUP BY estado";
        
        $stmt = $this->conexion->prepare($consulta);
        
        if ($anio) {
            $stmt->bindParam(':anio', $anio);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>