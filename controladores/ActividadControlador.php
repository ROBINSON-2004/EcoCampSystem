<?php
/**
 * ============================================
 * CONTROLADOR: ACTIVIDADES (Versión Final)
 * EcoCampSystem 2026 - Robinson Moya
 * ============================================
 */
require_once __DIR__ . '/../config/constantes.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Actividad.php';

class ActividadControlador {
    private $db;
    
    public function __construct() {
        $database = new Conexion();
        $this->db = $database->obtenerConexion();
    }
    
    /**
     * PROGRAMA UNA ACTIVIDAD PARA UN GRUPO
     * Valida que la fecha sea futura y asegura la integridad de la DB.
     */
    public function programar($datos) {
        // 1. Validación de campos obligatorios (sin usar empty para permitir IDs en 0)
        $requeridos = ['id_actividad', 'id_grupo', 'fecha_actividad', 'hora_inicio', 'hora_fin'];
        foreach ($requeridos as $campo) {
            if (!isset($datos[$campo]) || trim((string)$datos[$campo]) === '') {
                return ['exito' => false, 'mensaje' => '⚠️ Todos los campos marcados con asterisco (*) son obligatorios.'];
            }
        }

        // 2. VALIDACIÓN DE FECHA (Bloqueo de pasado)
        $hoy = date('Y-m-d');
        if ($datos['fecha_actividad'] < $hoy) {
            return [
                'exito' => false, 
                'mensaje' => "❌ No puedes programar actividades para el pasado ({$datos['fecha_actividad']})."
            ];
        }
        
        try {
            // 3. Consulta de inserción
            $query = "INSERT INTO actividades_programadas 
                      (id_actividad, id_grupo, fecha_actividad, hora_inicio, hora_fin, 
                       id_responsable, estado, observaciones)
                      VALUES (:act, :grupo, :fecha, :h_ini, :h_fin, :resp, 'programada', :obs)";
            
            $stmt = $this->db->prepare($query);
            
            // Si no llega responsable, usamos el de la sesión
            $id_res = !empty($datos['id_responsable']) ? (int)$datos['id_responsable'] : (isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : 0);

            $stmt->execute([
                ':act'    => (int)$datos['id_actividad'],
                ':grupo'  => (int)$datos['id_grupo'],
                ':fecha'  => $datos['fecha_actividad'],
                ':h_ini'  => $datos['hora_inicio'],
                ':h_fin'  => $datos['hora_fin'],
                ':resp'   => $id_res,
                ':obs'    => !empty($datos['observaciones']) ? limpiar_cadena($datos['observaciones']) : null
            ]);
            
            if ($stmt->rowCount() > 0) {
                registrar_log("Actividad programada con éxito para el grupo {$datos['id_grupo']}", 'INFO');
                return ['exito' => true, 'mensaje' => '✅ Actividad programada correctamente.'];
            }
        } catch (PDOException $e) {
            registrar_log("Error PDO al programar: " . $e->getMessage(), 'ERROR');
            return ['exito' => false, 'mensaje' => '🔥 Error técnico en Base de Datos.'];
        }
        
        return ['exito' => false, 'mensaje' => 'No se pudo completar la operación.'];
    }

    /**
     * CAMBIAR ESTADO DE ACTIVIDAD (Realizada o Cancelada)
     * Al cambiar el estado, la actividad dejará de aparecer en el calendario.
     */
    public function cambiarEstadoProgramada($id, $nuevo_estado) {
        if (empty($id)) return ['exito' => false, 'mensaje' => 'ID de programación no válido.'];
        
        try {
            $query = "UPDATE actividades_programadas SET estado = :estado WHERE id_actividad_programada = :id";
            $stmt = $this->db->prepare($query);
            $res = $stmt->execute([
                ':estado' => $nuevo_estado,
                ':id'     => (int)$id
            ]);
            
            if ($res) {
                $texto = ($nuevo_estado === 'completada') ? '¡Felicidades! Actividad realizada.' : 'Actividad cancelada.';
                return ['exito' => true, 'mensaje' => "✅ $texto"];
            }
        } catch (Exception $e) {
            registrar_log("Error al actualizar estado: " . $e->getMessage(), 'ERROR');
            return ['exito' => false, 'mensaje' => 'Error al actualizar el estado.'];
        }
        return ['exito' => false];
    }
    
    /**
     * OBTENER ACTIVIDADES PROGRAMADAS
     * Devuelve las actividades para el calendario dentro de un rango de fechas.
     */
    public function obtenerProgramadas($inicio, $fin, $id_grupo = null) {
        try {
            $query = "SELECT ap.*, a.nombre_actividad, a.tipo_actividad, a.ubicacion, g.nombre_grupo,
                             u.nombre as resp_nombre, u.apellido as resp_apellido
                      FROM actividades_programadas ap
                      INNER JOIN actividades a ON ap.id_actividad = a.id_actividad
                      INNER JOIN grupos g ON ap.id_grupo = g.id_grupo
                      LEFT JOIN usuarios u ON ap.id_responsable = u.id_usuario
                      WHERE ap.fecha_actividad BETWEEN :inicio AND :fin";
            
            if ($id_grupo) $query .= " AND ap.id_grupo = :grupo";
            
            $query .= " ORDER BY ap.fecha_actividad ASC, ap.hora_inicio ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':inicio', $inicio);
            $stmt->bindParam(':fin', $fin);
            if ($id_grupo) $stmt->bindParam(':grupo', $id_grupo);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            registrar_log("Error al recuperar programadas: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }

    /**
     * CREAR ACTIVIDAD MAESTRA (Catálogo)
     */
    public function crear($datos) {
        if (empty($datos['nombre_actividad'])) return ['exito' => false, 'mensaje' => 'El nombre es requerido.'];
        
        try {
            $actividad = new Actividad();
            $actividad->nombre_actividad = limpiar_cadena($datos['nombre_actividad']);
            $actividad->descripcion = $datos['descripcion'] ?? null;
            $actividad->tipo_actividad = $datos['tipo_actividad'] ?? 'recreativa';
            $actividad->estado = 'activo';
            
            $id = $actividad->crear();
            return $id ? ['exito' => true, 'mensaje' => 'Actividad guardada en catálogo.'] : ['exito' => false];
        } catch (Exception $e) {
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }
}