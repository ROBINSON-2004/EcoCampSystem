<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="<?php echo URL_BASE; ?>/panel.php">
        EcoCamp Admin
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#menuAdmin">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuAdmin">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo URL_BASE; ?>/vistas/admin/dashboard.php">
                    Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo URL_BASE; ?>/vistas/admin/formularios/lista.php">
                    Formularios
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo URL_BASE; ?>/vistas/admin/campistas/lista.php">
                    Campistas
                </a>
            </li>
        </ul>

        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-danger" href="<?php echo URL_BASE; ?>/logout.php">
                    Cerrar sesión
                </a>
            </li>
        </ul>
    </div>
</nav>
