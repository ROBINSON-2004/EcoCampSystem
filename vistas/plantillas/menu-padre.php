<?php
// Seguridad básica: evitar acceso directo sin sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/EcoCampSystem/vistas/padre/dashboard.php">
            🌲 EcoCamp
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#menuPadre" aria-controls="menuPadre"
            aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPadre">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link" href="/EcoCampSystem/vistas/padre/dashboard.php">
                        🏠 Inicio
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/EcoCampSystem/vistas/padre/campistas/lista.php">
                        👦 Mis Campistas
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="/EcoCampSystem/vistas/padre/formularios/disponibles.php">
                        📄 Formularios
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/EcoCampSystem/vistas/padre/notificaciones.php">
                        🔔 Notificaciones
                    </a>
                </li>
            </ul>

            <span class="navbar-text me-3">
                👤 <?php echo $_SESSION['nombre'] ?? 'Padre'; ?>
            </span>

            <a href="/EcoCampSystem/logout.php" class="btn btn-outline-light btn-sm">
                Salir
            </a>
        </div>
    </div>
</nav>
