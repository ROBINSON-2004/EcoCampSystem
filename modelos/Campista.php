<?php
require_once RUTA_CONFIG . '/conexion.php';

/**
 * Clase Campista
 * Modelo para gestionar toda la información de los niños inscritos.
 */
class Campista {
    private $conexion;
    private $tabla = 'campistas';
    
    // Propiedades del campista (compatibles con PHP 8.2+)
    public $id_campista;
    public $nombre;
    public $apellido;
    public $fecha_nacimiento;
    public $edad;
    public $genero;
    public $id_padre;
    public $foto_perfil;
    public $notas_especiales;
    public $estado_inscripcion;
    public $anio_inscripcion;
    public $fecha_inscripcion;
    
    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }
    
    /**
     * Crea un nuevo registro de campista
     */
    public function crear() {
        $consulta = "INSERT INTO " . $this->tabla . " 
                    (nombre, apellido, fecha_nacimiento, edad, genero, id_padre, 
                     foto_perfil, notas_especiales, estado_inscripcion, anio_inscripcion)
                    VALUES (:nombre, :apellido, :fecha_nac, :edad, :genero, :id_padre,
                            :foto, :notas, :estado, :anio)";
        
        $stmt = $this->conexion->prepare($consulta);
        
        // Limpieza de datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':fecha_nac', $this->fecha_nacimiento);
        $stmt->bindParam(':edad', $this->edad, PDO::PARAM_INT);
        $stmt->bindParam(':genero', $this->genero);
        $stmt->bindParam(':id_padre', $this->id_padre, PDO::PARAM_INT);
        $stmt->bindParam(':foto', $this->foto_perfil);
        $stmt->bindParam(':notas', $this->notas_especiales);
        $stmt->bindParam(':estado', $this->estado_inscripcion);
        $stmt->bindParam(':anio', $this->anio_inscripcion, PDO::PARAM_INT);
        
        if ($stmt->execute()) return $this->conexion->lastInsertId();
        return false;
    }
    
    /**
     * Lee un campista básico por su ID
     */
    public function leerPorId() {
        $consulta = "SELECT * FROM " . $this->tabla . " WHERE id_campista = :id LIMIT 1";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_campista, PDO::PARAM_INT);
        $stmt->execute();
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fila) {
            foreach ($fila as $key => $value) { 
                if (property_exists($this, $key)) $this->$key = $value; 
            }
            return true;
        }
        return false;
    }
    
    /**
     * Obtiene información completa del campista con datos del padre (JOIN)
     * Soluciona el error: Call to undefined method Campista::obtenerInformacionCompleta()
     */
    public function obtenerInformacionCompleta() {
        $consulta = "SELECT c.*, p.id_padre, u.nombre as nombre_padre, u.apellido as apellido_padre,
                            u.correo_electronico as correo_padre, u.telefono as telefono_padre
                     FROM " . $this->tabla . " c
                     INNER JOIN padres p ON c.id_padre = p.id_padre
                     INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                     WHERE c.id_campista = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_campista, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene la lista general para el Administrador
     * Soluciona el error: Call to undefined method Campista::leerTodos()
     */
    public function leerTodos($estado_inscripcion = null, $anio = null) {
        $consulta = "SELECT c.*, u.nombre as nombre_padre, u.apellido as apellido_padre, u.correo_electronico as correo_padre
                     FROM " . $this->tabla . " c
                     INNER JOIN padres p ON c.id_padre = p.id_padre
                     INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                     WHERE 1=1";
        
        if ($estado_inscripcion) $consulta .= " AND c.estado_inscripcion = :estado";
        if ($anio) $consulta .= " AND c.anio_inscripcion = :anio";
        
        $consulta .= " ORDER BY c.fecha_inscripcion DESC";
        
        $stmt = $this->conexion->prepare($consulta);
        if ($estado_inscripcion) $stmt->bindParam(':estado', $estado_inscripcion);
        if ($anio) $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualiza los datos de un campista
     */
    public function actualizar() {
        $consulta = "UPDATE " . $this->tabla . "
                    SET nombre = :nombre, apellido = :apellido, fecha_nacimiento = :fecha_nac,
                        edad = :edad, genero = :genero, foto_perfil = :foto,
                        notas_especiales = :notas, estado_inscripcion = :estado
                    WHERE id_campista = :id";
        
        $stmt = $this->conexion->prepare($consulta);
        
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':fecha_nac', $this->fecha_nacimiento);
        $stmt->bindParam(':edad', $this->edad, PDO::PARAM_INT);
        $stmt->bindParam(':genero', $this->genero);
        $stmt->bindParam(':foto', $this->foto_perfil);
        $stmt->bindParam(':notas', $this->notas_especiales);
        $stmt->bindParam(':estado', $this->estado_inscripcion);
        $stmt->bindParam(':id', $this->id_campista, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Obtiene hijos de un padre específico (excluye retirados)
     */
    public function leerPorPadre($id_padre) {
        $consulta = "SELECT * FROM " . $this->tabla . " 
                    WHERE id_padre = :id_padre AND estado_inscripcion != 'retirado' 
                    ORDER BY nombre ASC";
        
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id_padre', $id_padre, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Borrado lógico: cambia el estado a 'retirado'
     */
    public function eliminar() {
        $consulta = "UPDATE " . $this->tabla . " SET estado_inscripcion = 'retirado' WHERE id_campista = :id";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $this->id_campista, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Estadísticas agrupadas por estado de inscripción
     */
    public function contarPorEstado() {
        $consulta = "SELECT estado_inscripcion, COUNT(*) as total FROM " . $this->tabla . " GROUP BY estado_inscripcion";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Búsqueda por nombre de campista o padre
     */
    public function buscar($termino) {
        $consulta = "SELECT c.*, u.nombre as nombre_padre, u.apellido as apellido_padre
                     FROM " . $this->tabla . " c
                     INNER JOIN padres p ON c.id_padre = p.id_padre
                     INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                     WHERE CONCAT(c.nombre, ' ', c.apellido) LIKE :termino
                     OR CONCAT(u.nombre, ' ', u.apellido) LIKE :termino";
        
        $stmt = $this->conexion->prepare($consulta);
        $busqueda = "%$termino%";
        $stmt->bindParam(':termino', $busqueda);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>