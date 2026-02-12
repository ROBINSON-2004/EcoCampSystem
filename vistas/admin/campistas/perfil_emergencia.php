<?php
// ... lógica de sesión y obtención de $id_campista ...
$ctrlCampista = new CampistaControlador();
$emergencia = $ctrlCampista->obtenerInfoEmergencia($id_campista);
$padre = $ctrlCampista->obtenerTutorLegal($id_campista);
?>

<div class="card shadow">
    <div class="card-header bg-danger text-white">
        <h2>🆘 Contactos de Urgencia</h2>
    </div>
    <div class="card-body">
        <div class="emergency-contact">
            <h3>Contacto de Emergencia</h3>
            <p><strong>Nombre:</strong> <?php echo $emergencia['nombre_contacto']; ?></p>
            <p><strong>Relación:</strong> <?php echo $emergencia['relacion']; ?></p>
            <p><strong>Teléfono:</strong> 
                <a href="tel:<?php echo $emergencia['telefono_principal']; ?>" class="btn-call">
                    📞 <?php echo $emergencia['telefono_principal']; ?>
                </a>
            </p>
        </div>
        <hr>
        <div class="parent-contact">
            <h3>Tutor Legal (Padre)</h3>
            <p><strong>Nombre:</strong> <?php echo $padre['nambre'] . " " . $padre['apellido']; ?></p>
            <p><strong>Teléfono:</strong> 
                <a href="tel:<?php echo $padre['telefono']; ?>" class="btn-call">
                    📱 <?php echo $padre['telefono']; ?>
                </a>
            </p>
        </div>
    </div>
</div>