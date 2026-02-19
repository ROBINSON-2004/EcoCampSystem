<?php
require_once RUTA_CONFIG . '/conexion.php';

/**
 * Clase CampistaGrupo
 * Gestiona la relación muchos a muchos entre campistas y grupos
 */
class CampistaGrupo {
    private $db;
    private $tabla = 'campistas_grupos';

    public function __construct() {
        $database = new Conexion();
        $this->db = $database->obtenerConexion();
    }

    /**
     * Asigna un campista a un grupo
     */
    public function asignar($id_campista, $id_grupo) {
        try {
            // Desactivamos asignaciones previas
            $sql_update = "UPDATE " . $this->tabla . " SET estado = 'inactivo' WHERE id_campista = :id_c";
            $stmt_up = $this->db->prepare($sql_update);
            $stmt_up->execute([':id_c' => $id_campista]);

            // Nueva asignación
            $sql = "INSERT INTO " . $this->tabla . " (id_campista, id_grupo, fecha_asignacion, estado) 
                    VALUES (:id_c, :id_g, NOW(), 'activo')";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id_c' => $id_campista,
                ':id_g' => $id_grupo
            ]);
        } catch (PDOException $e) {
            registrar_log("Error en asignar campista: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
}

/**
 * Clase Grupo
 * Modelo para gestionar grupos del campamento
 */
class Grupo {
    private $db;
    private $tabla = 'grupos';
    
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
    
    public function __construct() {
        $database = new Conexion();
        $this->db = $database->obtenerConexion();
    }
    
    /**
     * Crea un nuevo grupo
     * Soluciona: Deprecated strip_tags y Fatal Error anio_campamento
     */
    public function crear() {
        // 1. LIMPIEZA BLINDADA (PHP 8.2+) - Evita pasar null a funciones de cadena
        $this->nombre_grupo = htmlspecialchars(strip_tags($this->nombre_grupo ?? ''));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion ?? ''));
        
        // 2. ASEGURAR AÑO (Evita SQLSTATE[23000]: Column cannot be null)
        if (empty($this->anio_campamento)) {
            $this->anio_campamento = defined('ANIO_CAMPAMENTO_ACTUAL') ? ANIO_CAMPAMENTO_ACTUAL : 2026;
        }

        $consulta = "INSERT INTO " . $this->tabla . " 
                    (nombre_grupo, descripcion, edad_minima, edad_maxima, capacidad_maxima,
                     id_consejero, anio_campamento, estado)
                    VALUES (:nombre, :descripcion, :edad_min, :edad_max, :capacidad,
                            :consejero, :anio, :estado)";
        
        try {
            $stmt = $this->db->prepare($consulta);
            
            $stmt->bindValue(':nombre', $this->nombre_grupo);
            $stmt->bindValue(':descripcion', $this->descripcion);
            $stmt->bindValue(':edad_min', $this->edad_minima, PDO::PARAM_INT);
            $stmt->bindValue(':edad_max', $this->edad_maxima, PDO::PARAM_INT);
            $stmt->bindValue(':capacidad', $this->capacidad_maxima, PDO::PARAM_INT);
            $stmt->bindValue(':consejero', $this->id_consejero);
            $stmt->bindValue(':anio', $this->anio_campamento);
            $stmt->bindValue(':estado', $this->estado ?? 'activo');
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
        } catch (PDOException $e) {
            registrar_log("Error Fatal en Grupo->crear: " . $e->getMessage(), 'ERROR');
            return false;
        }
        return false;
    }
    
    public function leerPorId() {
        $consulta = "SELECT * FROM " . $this->tabla . " WHERE id_grupo = :id LIMIT 1";
        $stmt = $this->db->prepare($consulta);
        $stmt->bindParam(':id', $this->id_grupo);
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
    
    public function leerTodos($estado = null, $anio = null) {
        $consulta = "SELECT g.*, u.nombre as nombre_consejero, u.apellido as apellido_consejero,
                    (SELECT COUNT(*) FROM campistas_grupos cg 
                     WHERE cg.id_grupo = g.id_grupo AND cg.estado = 'activo') as total_campistas
                    FROM " . $this->tabla . " g
                    LEFT JOIN usuarios u ON g.id_consejero = u.id_usuario
                    WHERE 1=1";
        
        $params = [];
        if ($estado) {
            $consulta .= " AND g.estado = :estado";
            $params[':estado'] = $estado;
        }
        if ($anio) {
            $consulta .= " AND g.anio_campamento = :anio";
            $params[':anio'] = $anio;
        }
        
        $consulta .= " ORDER BY g.nombre_grupo ASC";
        $stmt = $this->db->prepare($consulta);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function actualizar() {
        // Limpieza blindada para PHP 8.2
        $this->nombre_grupo = htmlspecialchars(strip_tags($this->nombre_grupo ?? ''));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion ?? ''));

        $consulta = "UPDATE " . $this->tabla . "
                    SET nombre_grupo = :nombre, descripcion = :descripcion,
                        edad_minima = :edad_min, edad_maxima = :edad_max,
                        capacidad_maxima = :capacidad, id_consejero = :consejero,
                        estado = :estado
                    WHERE id_grupo = :id";
        
        $stmt = $this->db->prepare($consulta);
        return $stmt->execute([
            ':nombre' => $this->nombre_grupo,
            ':descripcion' => $this->descripcion,
            ':edad_min' => $this->edad_minima,
            ':edad_max' => $this->edad_maxima,
            ':capacidad' => $this->capacidad_maxima,
            ':consejero' => $this->id_consejero,
            ':estado' => $this->estado,
            ':id' => $this->id_grupo
        ]);
    }
    
    public function eliminar() {
        $consulta = "UPDATE " . $this->tabla . " SET estado = 'inactivo' WHERE id_grupo = :id";
        $stmt = $this->db->prepare($consulta);
        $stmt->bindParam(':id', $this->id_grupo);
        return $stmt->execute();
    }

    public function contarPorEstado($anio = null) {
        $consulta = "SELECT estado, COUNT(*) as total FROM " . $this->tabla;
        $params = [];
        if ($anio) {
            $consulta .= " WHERE anio_campamento = :anio";
            $params[':anio'] = $anio;
        }
        $consulta .= " GROUP BY estado";
        $stmt = $this->db->prepare($consulta);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}