<?php

// ================================================================
// .PHP QUE CONTROLA LA LÓGICA Y ESTRUCTURA DE LA PÁGINA DE LOGIN.
// ================================================================

session_start();

// Si ya hay sesión activa, redirigir al dashboard.
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halcon Order Hub - Acceso de Personal</title>
    <link rel="stylesheet" href="css/styles.css">

    <!-- Fuentes de Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Asap+Condensed:wght@400;800&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <h2 id="header-title"><a href="index.php" style="text-decoration:none;color:inherit;">🦅 Halcon Order Hub</a></h2>
</header>

    <div id="estado-title">
        <h1 id="estado-t1">ACCESO DE</h1>
        <h1 id="estado-t2">PERSONAL</h1>
    </div>
    <h2 id="estado-descripcion">Ingresa tus credenciales para acceder al panel de gestión.</h2>

    <form id="login-form">
        <div class="inputs-row">
            <div class="field floating-field">
                <input type="text" id="login-username" name="username" placeholder=" " required autocomplete="username">
                <label for="login-username" class="floating-label">Usuario</label>
            </div>
            <div class="field floating-field">
                <input type="password" id="login-password" name="password" placeholder=" " required autocomplete="current-password">
                <label for="login-password" class="floating-label">Contraseña</label>
            </div>
        </div>
        <button type="submit">Iniciar Sesión</button>
    </form>

    <div id="login-result"></div>

    <script src="js/main.js"></script>
</body>
</html>