<?php
/**
 * PROCESADOR DE ELIMINACIÓN DE CAMPISTA (LÓGICA)
 * Restricción: No permite eliminar si el estado es 'aprobado'.
 */
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_CONTROLADORES . '/CampistaControlador.php';
require_once RUTA_MODELOS . '/Padre.php';

// 1. Seguridad: Solo usuarios con rol de padre pueden acceder
Sesion::iniciar();
Sesion::requerirTipoUsuario(TIPO_PADRE);

// 2. Solo procesar si la petición es POST (por seguridad)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_campista = $_POST['id_campista'] ?? null;
    $datos_usuario = Sesion::obtenerDatosUsuario();

    if ($id_campista) {
        $controlador = new CampistaControlador();
        
        // Obtener los datos del padre vinculado al usuario en sesión
        $padre = new Padre();
        $padre->id_usuario = $datos_usuario['id'];
        $padre->leerPorIdUsuario();

        // Obtener información del niño para validar propiedad y estado
        $info_nino = $controlador->obtenerPorId($id_campista);

        // 3. Validar que el niño pertenezca a este padre
        if ($info_nino && $info_nino['id_padre'] == $padre->id_padre) {
            
            // --- RESTRICCIÓN SOLICITADA ---
            // Si el hijo está inscrito y aprobado, no se puede eliminar
            if ($info_nino['estado_inscripcion'] === INSCRIPCION_APROBADO) {
                Sesion::establecerMensaje('error', 'No puedes eliminar a un hijo cuya inscripción ya ha sido aprobada. Por favor, contacta con la administración si necesitas realizar cambios.');
            } else {
                // Proceder con el borrado lógico (cambio a estado 'retirado')
                $resultado = $controlador->eliminar($id_campista);
                
                if ($resultado['exito']) {
                    Sesion::establecerMensaje('exito', 'La inscripción de tu hijo ha sido eliminada correctamente.');
                } else {
                    Sesion::establecerMensaje('error', 'Hubo un problema al intentar eliminar el registro.');
                }
            }
        } else {
            Sesion::establecerMensaje('error', 'No tienes permisos para eliminar este perfil o el registro no existe.');
        }
    }
}

// 4. Redirección final al Dashboard (Evita error 404 al usar URL_BASE)
header('Location: ' . URL_BASE . '/vistas/padre/dashboard.php');
exit();