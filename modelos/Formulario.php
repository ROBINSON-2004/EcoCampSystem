<?php
require_once RUTA_CONFIG . '/conexion.php';

class Formulario {
    private $conexion;
    private $tabla = 'formularios';

    public $id_formulario;
    public $titulo;
    public $descripcion;
    public $tipo_formulario;
    public $archivo_url;
    public $es_obligatorio;
    public $anio_vigencia;
    public $subido_por;
    public $estado;

    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }

    public function crear() {
        $sql = "INSERT INTO " . $this->tabla . " 
                (titulo, descripcion, tipo_formulario, archivo_url, es_obligatorio, anio_vigencia, subido_por, estado)
                VALUES (:titulo, :descripcion, :tipo, :url, :obligatorio, :anio, :usuario, :estado)";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':tipo', $this->tipo_formulario);
        $stmt->bindParam(':url', $this->archivo_url);
        $stmt->bindParam(':obligatorio', $this->es_obligatorio, PDO::PARAM_BOOL);
        $stmt->bindParam(':anio', $this->anio_vigencia);
        $stmt->bindParam(':usuario', $this->subido_por);
        $stmt->bindParam(':estado', $this->estado);

        return $stmt->execute();
    }

    public function leerTodos($solo_activos = true) {
        $sql = "SELECT * FROM " . $this->tabla;
        if ($solo_activos) $sql .= " WHERE estado = 'activo' AND anio_vigencia = YEAR(CURDATE())";
        $sql .= " ORDER BY fecha_subida DESC";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>