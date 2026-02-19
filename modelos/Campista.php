<?php
/**
 * ============================================
 * MODELO: CAMPISTA (Versión Final Sincronizada)
 * EcoCampSystem 2026 - Robinson Moya
 * ============================================
 */
class Campista {
    
    private $db;

    // 1. PROPIEDADES DE LA TABLA 'campistas'
    public $id_campista;
    public $nombre;
    public $apellido;
    public $fecha_nacimiento;
    public $edad;
    public $genero;
    public $id_padre;
    public $notas_especiales;
    public $estado_inscripcion;
    public $anio_inscripcion;
    public $fecha_inscripcion;
    
    // 2. PROPIEDADES DE RELACIÓN (JOINs)
    // Declaradas para evitar "Deprecated: Creation of dynamic property"
    public $nombre_padre;
    public $apellido_padre;
    public $tipo_sangre;
    public $alergias;
    public $condiciones_especiales;
    public $medicamentos;
    public $foto_perfil;

    /**
     * CONSTRUCTOR: Inicializa la conexión PDO
     */
    public function __construct() {
        $database = new Conexion();
        $this->db = $database->obtenerConexion();
    }

    /**
     * LISTAR TODOS
     * Resuelve el error "Unknown column 'p.nombre'" uniendo la tabla usuarios
     */
    public function leerTodos($estado = null, $anio = null) {
        $sql = "SELECT c.*, 
                u.nombre AS nombre_padre, 
                u.apellido AS apellido_padre 
                FROM campistas c
                LEFT JOIN padres p ON c.id_padre = p.id_padre
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE 1=1";
        
        $params = [];
        if ($estado) {
            $sql .= " AND c.estado_inscripcion = :estado";
            $params[':estado'] = $estado;
        }
        if ($anio) {
            $sql .= " AND c.anio_inscripcion = :anio";
            $params[':anio'] = $anio;
        }

        $sql .= " ORDER BY c.id_campista DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            registrar_log("Error en leerTodos: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }

    /**
     * OBTENER INFORMACIÓN COMPLETA (JOIN Triple: Campista + Salud + Usuarios)
     */
    public function obtenerInformacionCompleta() {
        $sql = "SELECT c.*, 
                m.tipo_sangre, m.alergias, m.condiciones_especiales, m.medicamentos,
                u.nombre AS nombre_padre, u.apellido AS apellido_padre
                FROM campistas c
                LEFT JOIN informacion_medica m ON c.id_campista = m.id_campista
                LEFT JOIN padres p ON c.id_padre = p.id_padre
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE c.id_campista = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $this->id_campista]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            registrar_log("Error en obtenerInformacionCompleta: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * LEER POR ID (Carga de datos básicos en el objeto)
     */
    public function leerPorId() {
        $sql = "SELECT * FROM campistas WHERE id_campista = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $this->id_campista]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            foreach ($row as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * ACTUALIZAR DATOS EN TABLA 'campistas'
     */
    public function actualizar() {
        $sql = "UPDATE campistas SET 
                nombre = :nom, 
                apellido = :ape, 
                fecha_nacimiento = :fec, 
                genero = :gen, 
                notas_especiales = :not 
                WHERE id_campista = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nom' => $this->nombre,
                ':ape' => $this->apellido,
                ':fec' => $this->fecha_nacimiento,
                ':gen' => $this->genero,
                ':not' => $this->notas_especiales,
                ':id'  => $this->id_campista
            ]);
        } catch (PDOException $e) {
            registrar_log("Error SQL en actualizar: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * CREAR NUEVO REGISTRO
     */
    public function crear() {
        $sql = "INSERT INTO campistas (nombre, apellido, fecha_nacimiento, edad, genero, id_padre, notas_especiales, estado_inscripcion, anio_inscripcion) 
                VALUES (:nom, :ape, :fec, :eda, :gen, :idp, :not, :est, :ani)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $res = $stmt->execute([
                ':nom' => $this->nombre,
                ':ape' => $this->apellido,
                ':fec' => $this->fecha_nacimiento,
                ':eda' => $this->edad,
                ':gen' => $this->genero,
                ':idp' => $this->id_padre,
                ':not' => $this->notas_especiales,
                ':est' => $this->estado_inscripcion,
                ':ani' => $this->anio_inscripcion
            ]);

            return $res ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            registrar_log("Error al crear campista: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * CONTAR POR ESTADO (Para Estadísticas)
     */
    public function contarPorEstado() {
        $sql = "SELECT estado_inscripcion, COUNT(*) as total 
                FROM campistas 
                GROUP BY estado_inscripcion";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * BUSCADOR INTEGRAL
     */
    public function buscar($termino) {
        $sql = "SELECT c.*, u.nombre AS nombre_padre, u.apellido AS apellido_padre 
                FROM campistas c
                LEFT JOIN padres p ON c.id_padre = p.id_padre
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE c.nombre LIKE :term 
                   OR c.apellido LIKE :term 
                   OR u.nombre LIKE :term 
                   OR c.id_campista = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':term' => "%$termino%",
            ':id'   => (int)$termino
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ELIMINAR REGISTRO
     */
    public function eliminar() {
        $sql = "DELETE FROM campistas WHERE id_campista = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $this->id_campista]);
    }
}