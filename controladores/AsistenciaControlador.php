<?php
require_once __DIR__ . '/../config/constantes.php';
require_once RUTA_UTILIDADES . '/funciones.php';

/**
 * Controlador de Asistencia
 * Gestiona el registro y consulta de asistencia
 */
class AsistenciaControlador {
    private $conexion;
    
    public function __construct() {
        $database = new Conexion();
        $this->conexion = $database->obtenerConexion();
    }
    
    /**
     * Registra o actualiza la asistencia de un campista
     * @param array $datos Datos de asistencia
     * @return array Respuesta
     */
    public function registrar($datos) {
        if (empty($datos['id_campista']) || empty($datos['fecha_asistencia']) || empty($datos['estado_asistencia'])) {
            return [
                'exito' => false,
                'mensaje' => 'Datos incompletos.'
            ];
        }
        
        try {
            // Verificar si ya existe registro para ese día
            $query_check = "SELECT id_asistencia FROM asistencia 
                           WHERE id_campista = :campista AND fecha_asistencia = :fecha";
            $stmt_check = $this->conexion->prepare($query_check);
            $stmt_check->bindParam(':campista', $datos['id_campista']);
            $stmt_check->bindParam(':fecha', $datos['fecha_asistencia']);
            $stmt_check->execute();
            
            if ($stmt_check->rowCount() > 0) {
                // Actualizar registro existente
                $row = $stmt_check->fetch(PDO::FETCH_ASSOC);
                $query_update = "UPDATE asistencia 
                               SET estado_asistencia = :estado,
                                   hora_entrada = :hora_entrada,
                                   hora_salida = :hora_salida,
                                   observaciones = :observaciones,
                                   registrado_por = :registrado_por
                               WHERE id_asistencia = :id";
                
                $stmt_update = $this->conexion->prepare($query_update);
                $stmt_update->bindParam(':estado', $datos['estado_asistencia']);
                $stmt_update->bindParam(':hora_entrada', $datos['hora_entrada']);
                $stmt_update->bindParam(':hora_salida', $datos['hora_salida']);
                $stmt_update->bindParam(':observaciones', $datos['observaciones']);
                $stmt_update->bindParam(':registrado_por', $datos['registrado_por']);
                $stmt_update->bindParam(':id', $row['id_asistencia']);
                
                if ($stmt_update->execute()) {
                    return [
                        'exito' => true,
                        'mensaje' => 'Asistencia actualizada correctamente.'
                    ];
                }
            } else {
                // Insertar nuevo registro
                $query_insert = "INSERT INTO asistencia 
                               (id_campista, fecha_asistencia, estado_asistencia, hora_entrada, 
                                hora_salida, observaciones, registrado_por)
                               VALUES (:campista, :fecha, :estado, :hora_entrada, 
                                       :hora_salida, :observaciones, :registrado_por)";
                
                $stmt_insert = $this->conexion->prepare($query_insert);
                $stmt_insert->bindParam(':campista', $datos['id_campista']);
                $stmt_insert->bindParam(':fecha', $datos['fecha_asistencia']);
                $stmt_insert->bindParam(':estado', $datos['estado_asistencia']);
                $stmt_insert->bindParam(':hora_entrada', $datos['hora_entrada']);
                $stmt_insert->bindParam(':hora_salida', $datos['hora_salida']);
                $stmt_insert->bindParam(':observaciones', $datos['observaciones']);
                $stmt_insert->bindParam(':registrado_por', $datos['registrado_por']);
                
                if ($stmt_insert->execute()) {
                    registrar_log("Asistencia registrada: Campista {$datos['id_campista']} - {$datos['fecha_asistencia']}", 'INFO');
                    return [
                        'exito' => true,
                        'mensaje' => 'Asistencia registrada correctamente.'
                    ];
                }
            }
            
        } catch (Exception $e) {
            registrar_log("Error al registrar asistencia: " . $e->getMessage(), 'ERROR');
        }
        
        return [
            'exito' => false,
            'mensaje' => MSG_ERROR_GENERAL
        ];
    }
    
    /**
     * Obtiene la asistencia de una fecha específica
     * @param string $fecha Fecha en formato Y-m-d
     * @param int $id_grupo Opcional: filtrar por grupo
     * @return array Lista de asistencias
     */
    public function obtenerPorFecha($fecha, $id_grupo = null) {
        try {
            $query = "SELECT a.*, 
                      c.nombre, c.apellido, c.edad, c.genero,
                      u.nombre as registrado_por_nombre,
                      u.apellido as registrado_por_apellido
                      FROM asistencia a
                      INNER JOIN campistas c ON a.id_campista = c.id_campista
                      LEFT JOIN usuarios u ON a.registrado_por = u.id_usuario
                      WHERE a.fecha_asistencia = :fecha";
            
            if ($id_grupo) {
                $query .= " AND c.id_campista IN (
                    SELECT id_campista FROM campistas_grupos 
                    WHERE id_grupo = :grupo AND estado = 'activo'
                )";
            }
            
            $query .= " ORDER BY c.nombre, c.apellido";
            
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':fecha', $fecha);
            
            if ($id_grupo) {
                $stmt->bindParam(':grupo', $id_grupo);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            registrar_log("Error al obtener asistencia: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    /**
     * Obtiene campistas que aún no tienen asistencia registrada en una fecha
     * @param string $fecha Fecha
     * @param int $id_grupo Opcional: filtrar por grupo
     * @return array Lista de campistas
     */
    public function obtenerSinRegistro($fecha, $id_grupo = null) {
        try {
            $query = "SELECT c.* 
                      FROM campistas c
                      WHERE c.estado_inscripcion = 'aprobado'
                      AND c.anio_inscripcion = YEAR(:fecha)
                      AND c.id_campista NOT IN (
                          SELECT id_campista FROM asistencia 
                          WHERE fecha_asistencia = :fecha2
                      )";
            
            if ($id_grupo) {
                $query .= " AND c.id_campista IN (
                    SELECT id_campista FROM campistas_grupos 
                    WHERE id_grupo = :grupo AND estado = 'activo'
                )";
            }
            
            $query .= " ORDER BY c.nombre, c.apellido";
            
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':fecha2', $fecha);
            
            if ($id_grupo) {
                $stmt->bindParam(':grupo', $id_grupo);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            registrar_log("Error al obtener campistas sin registro: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    /**
     * Obtiene el historial de asistencia de un campista
     * @param int $id_campista ID del campista
     * @param string $fecha_inicio Fecha inicio
     * @param string $fecha_fin Fecha fin
     * @return array Historial
     */
    public function obtenerHistorial($id_campista, $fecha_inicio = null, $fecha_fin = null) {
        try {
            $query = "SELECT a.*, 
                      u.nombre as registrado_por_nombre,
                      u.apellido as registrado_por_apellido
                      FROM asistencia a
                      LEFT JOIN usuarios u ON a.registrado_por = u.id_usuario
                      WHERE a.id_campista = :campista";
            
            if ($fecha_inicio && $fecha_fin) {
                $query .= " AND a.fecha_asistencia BETWEEN :inicio AND :fin";
            }
            
            $query .= " ORDER BY a.fecha_asistencia DESC";
            
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':campista', $id_campista);
            
            if ($fecha_inicio && $fecha_fin) {
                $stmt->bindParam(':inicio', $fecha_inicio);
                $stmt->bindParam(':fin', $fecha_fin);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            registrar_log("Error al obtener historial: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    /**
     * Obtiene estadísticas de asistencia
     * @param string $fecha_inicio Fecha inicio
     * @param string $fecha_fin Fecha fin
     * @return array Estadísticas
     */
    public function obtenerEstadisticas($fecha_inicio = null, $fecha_fin = null) {
        try {
            if (!$fecha_inicio) {
                $fecha_inicio = date('Y-m-01'); // Primer día del mes actual
            }
            if (!$fecha_fin) {
                $fecha_fin = date('Y-m-d'); // Hoy
            }
            
            $query = "SELECT 
                      COUNT(*) as total_registros,
                      SUM(CASE WHEN estado_asistencia = 'presente' THEN 1 ELSE 0 END) as presentes,
                      SUM(CASE WHEN estado_asistencia = 'ausente' THEN 1 ELSE 0 END) as ausentes,
                      SUM(CASE WHEN estado_asistencia = 'tardanza' THEN 1 ELSE 0 END) as tardanzas,
                      SUM(CASE WHEN estado_asistencia = 'retiro_temprano' THEN 1 ELSE 0 END) as retiros_tempranos
                      FROM asistencia
                      WHERE fecha_asistencia BETWEEN :inicio AND :fin";
            
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':inicio', $fecha_inicio);
            $stmt->bindParam(':fin', $fecha_fin);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            registrar_log("Error al obtener estadísticas: " . $e->getMessage(), 'ERROR');
            return [
                'total_registros' => 0,
                'presentes' => 0,
                'ausentes' => 0,
                'tardanzas' => 0,
                'retiros_tempranos' => 0
            ];
        }
    }
    
    /**
     * Registra asistencia masiva (marcar todos como presentes)
     * @param array $ids_campistas Array de IDs de campistas
     * @param string $fecha Fecha
     * @param int $registrado_por ID del usuario que registra
     * @return array Respuesta
     */
    public function registrarMasivo($ids_campistas, $fecha, $registrado_por) {
        try {
            $exitosos = 0;
            
            foreach ($ids_campistas as $id_campista) {
                $datos = [
                    'id_campista' => $id_campista,
                    'fecha_asistencia' => $fecha,
                    'estado_asistencia' => ASISTENCIA_PRESENTE,
                    'hora_entrada' => date('H:i:s'),
                    'hora_salida' => null,
                    'observaciones' => null,
                    'registrado_por' => $registrado_por
                ];
                
                $resultado = $this->registrar($datos);
                if ($resultado['exito']) {
                    $exitosos++;
                }
            }
            
            return [
                'exito' => true,
                'mensaje' => "Se registró la asistencia de $exitosos campista(s).",
                'total' => $exitosos
            ];
            
        } catch (Exception $e) {
            registrar_log("Error en registro masivo: " . $e->getMessage(), 'ERROR');
            return [
                'exito' => false,
                'mensaje' => MSG_ERROR_GENERAL
            ];
        }
    }
}
?>