<?php
require_once __DIR__ . '/../../config/constantes.php';
require_once __DIR__ . '/../../utilidades/sesion.php';
require_once __DIR__ . '/../../utilidades/funciones.php';
require_once __DIR__ . '/../../controladores/AutenticacionControlador.php';

// Si ya hay sesión activa, redirigir al panel
Sesion::iniciar();
if (Sesion::estaActiva()) {
    header('Location: ' . URL_BASE . '/panel.php');
    exit();
}

// Procesar formulario de registro
$error = '';
$exito = '';
$datos_formulario = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new AutenticacionControlador();
    $resultado = $controlador->registrarPadre($_POST);
    
    if ($resultado['exito']) {
        $exito = $resultado['mensaje'];
        $datos_formulario = []; // Limpiar formulario
    } else {
        $error = $resultado['mensaje'];
        $datos_formulario = $_POST; // Mantener datos para no perderlos
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Padres - <?php echo NOMBRE_SITIO; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .registro-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            margin: 0 auto;
            overflow: hidden;
        }
        
        .registro-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .registro-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .registro-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .registro-body {
            padding: 40px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .form-group label .required {
            color: #e53e3e;
            margin-left: 3px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 0.85rem;
        }
        
        .password-requirements {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .password-requirements ul {
            margin: 10px 0 0 20px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .password-requirements li {
            margin: 5px 0;
        }
        
        .btn-register {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-register:active {
            transform: translateY(0);
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .registro-header h1 {
                font-size: 2rem;
            }
            
            .registro-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="registro-container">
        <div class="registro-header">
            <h1>🏕️ Registro de Padres</h1>
            <p>Crea tu cuenta para inscribir a tus hijos al campamento</p>
        </div>
        
        <div class="registro-body">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($exito): ?>
                <div class="alert alert-success">
                    <?php echo $exito; ?>
                    <br><br>
                    <a href="<?php echo URL_BASE; ?>/index.php" style="color: #2d7a2d; font-weight: bold;">
                        Click aquí para iniciar sesión
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if (!$exito): ?>
            <form method="POST" action="" id="formRegistro">
                
                <!-- Información Personal -->
                <div class="form-section">
                    <h3>📋 Información Personal</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">
                                Nombre <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="nombre" 
                                name="nombre" 
                                required
                                value="<?php echo htmlspecialchars($datos_formulario['nombre'] ?? ''); ?>"
                                placeholder="Juan">
                        </div>
                        
                        <div class="form-group">
                            <label for="apellido">
                                Apellido <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="apellido" 
                                name="apellido" 
                                required
                                value="<?php echo htmlspecialchars($datos_formulario['apellido'] ?? ''); ?>"
                                placeholder="Pérez">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="correo">
                                Correo Electrónico <span class="required">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="correo" 
                                name="correo" 
                                required
                                value="<?php echo htmlspecialchars($datos_formulario['correo'] ?? ''); ?>"
                                placeholder="juan.perez@email.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono">
                                Teléfono <span class="required">*</span>
                            </label>
                            <input 
                                type="tel" 
                                id="telefono" 
                                name="telefono" 
                                required
                                value="<?php echo htmlspecialchars($datos_formulario['telefono'] ?? ''); ?>"
                                placeholder="0999999999">
                            <small>Ingresa solo números, mínimo 7 dígitos</small>
                        </div>
                    </div>
                </div>
                
                <!-- Información de Contacto -->
                <div class="form-section">
                    <h3>📍 Información de Contacto (Opcional)</h3>
                    
                    <div class="form-group full-width">
                        <label for="direccion">Dirección</label>
                        <textarea 
                            id="direccion" 
                            name="direccion" 
                            placeholder="Calle principal #123, Sector Norte"><?php echo htmlspecialchars($datos_formulario['direccion'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ciudad">Ciudad</label>
                            <input 
                                type="text" 
                                id="ciudad" 
                                name="ciudad"
                                value="<?php echo htmlspecialchars($datos_formulario['ciudad'] ?? ''); ?>"
                                placeholder="Quito">
                        </div>
                        
                        <div class="form-group">
                            <label for="codigo_postal">Código Postal</label>
                            <input 
                                type="text" 
                                id="codigo_postal" 
                                name="codigo_postal"
                                value="<?php echo htmlspecialchars($datos_formulario['codigo_postal'] ?? ''); ?>"
                                placeholder="170101">
                        </div>
                    </div>
                </div>
                
                <!-- Seguridad -->
                <div class="form-section">
                    <h3>🔒 Seguridad</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contrasena">
                                Contraseña <span class="required">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="contrasena" 
                                name="contrasena" 
                                required
                                placeholder="••••••••">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmar_contrasena">
                                Confirmar Contraseña <span class="required">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="confirmar_contrasena" 
                                name="confirmar_contrasena" 
                                required
                                placeholder="••••••••">
                        </div>
                    </div>
                    
                    <div class="password-requirements">
                        <strong>Requisitos de contraseña:</strong>
                        <ul>
                            <li>Mínimo 8 caracteres</li>
                            <li>Al menos una letra mayúscula</li>
                            <li>Al menos una letra minúscula</li>
                            <li>Al menos un número</li>
                        </ul>
                    </div>
                </div>
                
                <button type="submit" class="btn-register">
                    Crear Mi Cuenta
                </button>
                
                <div class="login-link">
                    ¿Ya tienes cuenta? <a href="<?php echo URL_BASE; ?>/index.php">Inicia sesión aquí</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Validación adicional en el frontend
        document.getElementById('formRegistro')?.addEventListener('submit', function(e) {
            const contrasena = document.getElementById('contrasena').value;
            const confirmar = document.getElementById('confirmar_contrasena').value;
            
            if (contrasena !== confirmar) {
                e.preventDefault();
                alert('Las contraseñas no coinciden. Por favor verifica.');
                return false;
            }
            
            if (contrasena.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres.');
                return false;
            }
        });
    </script>
</body>
</html>