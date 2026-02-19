<?php
/**
 * ============================================
 * VISTA: FICHA MÉDICA E IDENTIDAD DE EMERGENCIA
 * EcoCampSystem - Ciclo 2026
 * ============================================
 */
require_once __DIR__ . '/../../../config/constantes.php';
require_once RUTA_UTILIDADES . '/sesion.php';
require_once RUTA_UTILIDADES . '/funciones.php';
require_once RUTA_CONFIG . '/conexion.php';

Sesion::iniciar();

// 1. SEGURIDAD: Solo acceso para padres autenticados
Sesion::requerirTipoUsuario(TIPO_PADRE);

// 2. VALIDACIÓN DE ID: Obtenemos el ID del campista desde la URL
$id_campista = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_campista === 0) {
    header('Location: lista.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información de Seguridad | <?php echo NOMBRE_SITIO; ?></title>
    <style>
        /* Estilos optimizados para EcoCampSystem 2026 */
        :root { --primary: #3182ce; --success: #38a169; --danger: #e53e3e; --bg: #f7fafc; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: var(--bg); color: #2d3748; }
        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 25px; }
        .card-header { background: var(--success); color: white; padding: 20px; text-align: center; }
        .card-header.emergency { background: var(--primary); }
        .card-body { padding: 30px; }

        .alert-box { background: #ebf8ff; border-left: 4px solid #4299e1; padding: 15px; margin-bottom: 20px; font-size: 0.9rem; color: #2b6cb0; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #4a5568; }
        select, textarea, input { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1rem; font-family: inherit; }
        textarea { resize: vertical; min-height: 90px; }
        
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .contact-block { background: #f8fafc; padding: 20px; border-radius: 10px; border: 1px solid #edf2f7; margin-bottom: 15px; }
        
        .btn-save { 
            background: var(--success); color: white; border: none; padding: 18px; width: 100%; 
            border-radius: 10px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: 0.3s;
        }
        .btn-save:hover { background: #2f855a; transform: translateY(-2px); }

        @media (max-width: 600px) { .row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="container">
    <form action="procesar_ficha_medica.php" method="POST">
        <input type="hidden" name="id_campista" value="<?php echo $id_campista; ?>">

        <div class="card">
            <div class="card-header">
                <h2>🩺 Información Médica</h2>
                <p>Datos de salud para el cuidado preventivo</p>
            </div>
            <div class="card-body">
                <div class="alert-box">
                    <strong>Paso 1:</strong> Ingrese los datos de salud actuales de su hijo. Estos datos son confidenciales.
                </div>

                <div class="form-group">
                    <label for="tipo_sangre">Tipo de Sangre *</label>
                    <select name="tipo_sangre" id="tipo_sangre" required>
                        <option value="">-- Seleccione --</option>
                        <option value="O+">O Positivo (O+)</option>
                        <option value="O-">O Negativo (O-)</option>
                        <option value="A+">A Positivo (A+)</option>
                        <option value="A-">A Negativo (A-)</option>
                        <option value="B+">B Positivo (B+)</option>
                        <option value="B-">B Negativo (B-)</option>
                        <option value="AB+">AB Positivo (AB+)</option>
                        <option value="AB-">AB Negativo (AB-)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="alergias">Alergias (Alimentos, Medicinas, Polvo)</label>
                    <textarea name="alergias" id="alergias" placeholder="Si no tiene, escriba 'Ninguna'"></textarea>
                </div>

                <div class="form-group">
                    <label for="condiciones_especiales">Condiciones Especiales / Discapacidades</label>
                    <textarea name="condiciones_especiales" id="condiciones_especiales" placeholder="Ej: Asma, Diabetes, Autismo, etc."></textarea>
                </div>

                <div class="form-group">
                    <label for="medicamentos">Medicamentos Actuales</label>
                    <textarea name="medicamentos" id="medicamentos" placeholder="Indique dosis y horarios si el niño debe medicarse en el campamento"></textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header emergency">
                <h2>📞 Contactos de Emergencia</h2>
                <p>¿A quién llamamos en caso de necesidad?</p>
            </div>
            <div class="card-body">
                <div class="alert-box" style="border-left-color: #3182ce; color: #2c5282; background: #ebf8ff;">
                    <strong>Paso 2:</strong> Proporcione los datos de dos personas localizables durante el campamento.
                </div>

                <div class="contact-block">
                    <p style="margin-bottom: 10px; font-weight: bold; color: var(--primary);">Contacto Principal</p>
                    <div class="form-group">
                        <label>Nombre Completo:</label>
                        <input type="text" name="emergencia_nombre[]" required placeholder="Nombre del contacto">
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label>Parentesco:</label>
                            <input type="text" name="emergencia_parentesco[]" required placeholder="Ej: Madre, Tía">
                        </div>
                        <div class="form-group">
                            <label>Teléfono:</label>
                            <input type="tel" name="emergencia_telefono[]" required placeholder="Ej: 0987654321">
                        </div>
                    </div>
                </div>

                <div class="contact-block">
                    <p style="margin-bottom: 10px; font-weight: bold; color: var(--primary);">Contacto Secundario</p>
                    <div class="form-group">
                        <label>Nombre Completo:</label>
                        <input type="text" name="emergencia_nombre[]" placeholder="Nombre del contacto">
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label>Parentesco:</label>
                            <input type="text" name="emergencia_parentesco[]" placeholder="Ej: Padre, Hermano">
                        </div>
                        <div class="form-group">
                            <label>Teléfono:</label>
                            <input type="tel" name="emergencia_telefono[]" placeholder="Ej: 0912345678">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-save">
            💾 Guardar Ficha y Finalizar Inscripción
        </button>
    </form>
    
    <p style="text-align: center; margin-top: 20px; font-size: 0.8rem; color: #a0aec0;">
        EcoCampSystem - Latacunga, Cotopaxi, Ecuador.
    </p>
</div>

</body>
</html>