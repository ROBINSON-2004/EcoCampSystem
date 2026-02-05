<?php
// Opcional: proteger sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si quieres validar que sea padre
if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] !== 'padre') {
    header('Location: /EcoCampSystem/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>EcoCamp System</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Estilos propios -->
    <link rel="stylesheet" href="/EcoCampSystem/public/css/estilos.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">EcoCamp</a>
        <span class="navbar-text text-white">
            Bienvenido, <?php echo $_SESSION['usuario']['nombre'] ?? ''; ?>
        </span>
    </div>
</nav>

<div class="container mt-4">
