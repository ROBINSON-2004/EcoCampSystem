<?php
/**
 * ============================================
 * PROCESADOR UNIFICADO: SALUD Y EMERGENCIA
 * EcoCampSystem - Pujilí, Ecuador
 * ============================================
 */
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONFIG . '/conexion.php';

Sesion::iniciar();

// 1. SEGURIDAD: Verificar identidad del padre
Sesion::requerirTipoUsuario(TIPO_PADRE);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit();
}

// 2. CONEXIÓN Y DATOS
$database = new Conexion();
$db = $database->obtenerConexion();

$id_campista = (int)$_POST['id_campista'];
$tipo_sangre = $_POST['tipo_sangre'] ?? '';
$alergias    = limpiar_cadena($_POST['alergias'] ?? 'Ninguna');
$condiciones = limpiar_cadena($_POST['condiciones_especiales'] ?? 'Ninguna');
$medicamentos = limpiar_cadena($_POST['medicamentos'] ?? 'No consume');

// Datos de contactos (Arreglos del formulario)
$nombres_emergencia     = $_POST['emergencia_nombre'] ?? [];
$parentescos_emergencia = $_POST['emergencia_parentesco'] ?? [];
$telefonos_emergencia   = $_POST['emergencia_telefono'] ?? [];

try {
    // 3. INICIO DE TRANSACCIÓN: Todo o nada
    $db->beginTransaction();

    // A. INSERTAR INFORMACIÓN MÉDICA
    $sql_medica = "INSERT INTO informacion_medica 
                   (id_campista, tipo_sangre, alergias, condiciones_especiales, medicamentos) 
                   VALUES 
                   (:id, :sangre, :aler, :cond, :med)";
    
    $stmt_m = $db->prepare($sql_medica);
    $stmt_m->execute([
        ':id'     => $id_campista,
        ':sangre' => $tipo_sangre,
        ':aler'   => $alergias,
        ':cond'   => $condiciones,
        ':med'    => $medicamentos
    ]);

    // B. INSERTAR CONTACTOS DE EMERGENCIA
    $sql_emergencia = "INSERT INTO informacion_emergencia 
                       (id_campista, nombre_contacto, parentesco, telefono) 
                       VALUES 
                       (:id, :nom, :par, :tel)";
    
    $stmt_e = $db->prepare($sql_emergencia);

    foreach ($nombres_emergencia as $index => $nombre) {
        $nombre_limpio = limpiar_cadena($nombre);
        
        // Solo guardamos si el nombre no está vacío
        if (!empty($nombre_limpio)) {
            $stmt_e->execute([
                ':id'  => $id_campista,
                ':nom' => $nombre_limpio,
                ':par' => limpiar_cadena($parentescos_emergencia[$index] ?? 'No especificado'),
                ':tel' => limpiar_cadena($telefonos_emergencia[$index] ?? '0000000000')
            ]);
        }
    }

    // 4. CONFIRMAR CAMBIOS
    $db->commit();

    Sesion::establecerMensaje('exito', '¡Excelente! La ficha médica y los contactos de emergencia se han guardado correctamente.');
    header('Location: ../dashboard.php');
    exit();

} catch (Exception $e) {
    // Si algo falla, deshacemos todo lo que se intentó guardar
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    registrar_log("Error crítico en Ficha Médica: " . $e->getMessage(), 'ERROR');
    Sesion::establecerMensaje('error', 'Hubo un error técnico al procesar la información. Por favor, intente de nuevo.');
    header('Location: completar_ficha.php?id=' . $id_campista);
    exit();
}