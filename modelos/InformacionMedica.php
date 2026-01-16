<?php
require_once __DIR__ . '/../config/conexion.php';

/**
 * Clase InformacionMedica
 * Gestiona alergias, medicamentos y condiciones de los campistas
 */
class InformacionMedica {
    private $conexion;
    private $tabla = 'informacion_medica';
    
    public $id_informacion_medica;
    public $id_campista;
    public $alergias;
    public $medicamentos_actuales;
    public $condiciones_medicas;
    public $tipo_sangre;
    public $seguro_medico;
    public $numero_poliza;
    public $nombre_medico;
    public $telefono_medico;
    public $observaciones;

    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }

    /**
     * Guarda o actualiza la ficha médica
     */
    public function guardar() {
        $consulta = "INSERT INTO " . $this->tabla . " 
                    (id_campista, alergias, medicamentos_actuales, condiciones_medicas, tipo_sangre, 
                     seguro_medico, numero_poliza, nombre_medico, telefono_medico, observaciones)
                    VALUES (:id_campista, :alergias, :medicamentos, :condiciones, :sangre, 
                            :seguro, :poliza, :n_medico, :t_medico, :obs)
                    ON DUPLICATE KEY UPDATE 
                    alergias = :alergias, medicamentos_actuales = :medicamentos, 
                    condiciones_medicas = :condiciones, seguro_medico = :seguro, 
                    observaciones = :obs";
        
        $stmt = $this->conexion->prepare($consulta);

        $stmt->bindParam(':id_campista', $this->id_campista);
        $stmt->bindParam(':alergias', $this->alergias);
        $stmt->bindParam(':medicamentos', $this->medicamentos_actuales);
        $stmt->bindParam(':condiciones', $this->condiciones_medicas);
        $stmt->bindParam(':sangre', $this->tipo_sangre);
        $stmt->bindParam(':seguro', $this->seguro_medico);
        $stmt->bindParam(':poliza', $this->numero_poliza);
        $stmt->bindParam(':n_medico', $this->nombre_medico);
        $stmt->bindParam(':t_medico', $this->telefono_medico);
        $stmt->bindParam(':obs', $this->observaciones);

        return $stmt->execute();
    }

    /**
     * Obtiene la información médica de un campista por su ID
     */
    public function leerPorCampista($id_campista) {
        $consulta = "SELECT * FROM " . $this->tabla . " WHERE id_campista = :id LIMIT 1";
        $stmt = $this->conexion->prepare($consulta);
        $stmt->bindParam(':id', $id_campista);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}