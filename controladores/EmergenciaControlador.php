<?php
require_once __DIR__ . '/../modelos/InformacionMedica.php';
require_once __DIR__ . '/../modelos/InformacionEmergencia.php';

class EmergenciaControlador {
    
    /**
     * Obtiene el perfil de seguridad completo de un campista
     */
    public function obtenerFichaSeguridad($id_campista) {
        $medica = new InformacionMedica();
        $emergencia = new InformacionEmergencia(); // Asumiendo que crearás este modelo similar al médico
        
        return [
            'medica' => $medica->leerPorCampista($id_campista),
            'contactos' => $emergencia->leerPorCampista($id_campista) 
        ];
    }
}