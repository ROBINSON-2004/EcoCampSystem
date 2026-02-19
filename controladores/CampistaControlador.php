<?php
/**
 * ============================================
 * CONTROLADOR: CAMPISTAS (VERSIÓN MAESTRA 2026)
 * Proyecto: EcoCampSystem
 * Desarrollador: Robinson Moya
 * ============================================
 */
require_once __DIR__ . '/../config/constantes.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_MODELOS . '/Campista.php';
require_once RUTA_CONFIG . '/conexion.php';

class CampistaControlador {
    
    private $db;

    public function __construct() {
        $database = new Conexion();
        $this->db = $database->obtenerConexion();
    }

    /**
     * ACTUALIZAR EXPEDIENTE INTEGRAL
     * Maneja: Datos Personales, Salud y Contactos de Emergencia (Arrays)
     */
    public function actualizar($datos) {
        if (empty($datos['id_campista'])) return ['exito' => false, 'mensaje' => 'ID de campista no recibido.'];

        try {
            $this->db->beginTransaction();

            // 1. ACTUALIZAR TABLA PRINCIPAL (campistas)
            $campista = new Campista();
            $campista->id_campista = (int)$datos['id_campista'];
            $campista->nombre = limpiar_cadena($datos['nombre'] ?? '');
            $campista->apellido = limpiar_cadena($datos['apellido'] ?? '');
            $campista->fecha_nacimiento = $datos['fecha_nacimiento'] ?? '';
            $campista->genero = $datos['genero'] ?? '';
            $campista->notas_especiales = limpiar_cadena($datos['notas_especiales'] ?? '');
            
            // Calculamos la edad antes de enviar al modelo
            $campista->edad = calcular_edad($campista->fecha_nacimiento);
            
            if (!$campista->actualizar()) {
                throw new Exception("No se pudo actualizar la información personal.");
            }

            // 2. ACTUALIZAR INFORMACIÓN MÉDICA (informacion_medica)
            $sql_m = "UPDATE informacion_medica SET 
                      tipo_sangre = :sangre, 
                      alergias = :alergia, 
                      medicamentos = :med, 
                      condiciones_especiales = :cond 
                      WHERE id_campista = :id";
            
            $stmt_m = $this->db->prepare($sql_m);
            $stmt_m->execute([
                ':sangre'  => $datos['tipo_sangre'] ?? 'S/N',
                ':alergia' => limpiar_cadena($datos['alergias'] ?? 'Ninguna'),
                ':med'     => limpiar_cadena($datos['medicamentos'] ?? 'Ninguno'),
                ':cond'    => limpiar_cadena($datos['condiciones_especiales'] ?? 'Ninguna'),
                ':id'      => $datos['id_campista']
            ]);

            // 3. ACTUALIZAR CONTACTOS DE EMERGENCIA (informacion_emergencia)
            if (isset($datos['emergencia_nombre']) && is_array($datos['emergencia_nombre'])) {
                // Limpiamos contactos previos para evitar duplicados
                $this->db->prepare("DELETE FROM informacion_emergencia WHERE id_campista = ?")
                         ->execute([$datos['id_campista']]);

                $sql_e = "INSERT INTO informacion_emergencia (id_campista, nombre_contacto, parentesco, telefono) VALUES (?, ?, ?, ?)";
                $stmt_e = $this->db->prepare($sql_e);

                foreach ($datos['emergencia_nombre'] as $i => $nombre) {
                    if (!empty(trim($nombre))) {
                        $stmt_e->execute([
                            $datos['id_campista'],
                            limpiar_cadena($nombre),
                            limpiar_cadena($datos['emergencia_parentesco'][$i] ?? 'Familiar'),
                            limpiar_cadena($datos['emergencia_telefono'][$i] ?? '000')
                        ]);
                    }
                }
            }

            $this->db->commit();
            registrar_log("Expediente ID " . $datos['id_campista'] . " actualizado correctamente.", 'INFO');
            return ['exito' => true, 'mensaje' => 'Expediente actualizado con éxito.'];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            registrar_log("Error en CampistaControlador->actualizar: " . $e->getMessage(), 'ERROR');
            return ['exito' => false, 'mensaje' => "Error: " . $e->getMessage()];
        }
    }

    /**
     * OBTENER POR ID (Datos integrales con JOINs)
     */
    public function obtenerPorId($id) {
        $modelo = new Campista();
        $modelo->id_campista = (int)$id;
        return $modelo->obtenerInformacionCompleta();
    }

    /**
     * LISTAR TODOS CON FILTROS
     */
    public function listarTodos($estado = null, $anio = null) {
        $modelo = new Campista();
        return $modelo->leerTodos($estado, $anio);
    }

    /**
     * BUSCAR CAMPISTAS (Filtro dinámico)
     */
    public function buscar($termino) {
        if (empty($termino)) return $this->listarTodos();
        $modelo = new Campista();
        return $modelo->buscar($termino);
    }

    /**
     * CAMBIAR ESTADO (Aprobación, Rechazo, etc.)
     */
    public function cambiarEstado($id, $estado) {
        try {
            $sql = "UPDATE campistas SET estado_inscripcion = :estado WHERE id_campista = :id";
            $stmt = $this->db->prepare($sql);
            $res = $stmt->execute([
                ':estado' => $estado,
                ':id'     => (int)$id
            ]);
            return $res ? ['exito' => true, 'mensaje' => 'Estado actualizado.'] : ['exito' => false];
        } catch (PDOException $e) {
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }

    /**
     * ESTADÍSTICAS PARA EL DASHBOARD
     */
    public function obtenerEstadisticas() {
        $modelo = new Campista();
        $por_estado = $modelo->contarPorEstado();
        
        $stats = ['total' => 0, 'aprobados' => 0, 'pendientes' => 0, 'rechazados' => 0];
        foreach ($por_estado as $e) {
            $count = (int)$e['total'];
            $stats['total'] += $count;
            
            if ($e['estado_inscripcion'] == INSCRIPCION_APROBADO) $stats['aprobados'] = $count;
            if ($e['estado_inscripcion'] == INSCRIPCION_PENDIENTE) $stats['pendientes'] = $count;
            if ($e['estado_inscripcion'] == INSCRIPCION_RECHAZADO) $stats['rechazados'] = $count;
        }
        return $stats;
    }

    /**
     * ELIMINAR REGISTRO
     */
    public function eliminar($id) {
        $modelo = new Campista();
        $modelo->id_campista = (int)$id;
        return $modelo->eliminar() ? ['exito' => true] : ['exito' => false];
    }
}