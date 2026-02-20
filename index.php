<?php

// ================================================================
// .PHP QUE CONTROLA LA LÓGICA Y ESTRUCTURA DE LA PÁGINA DE INICIO.
// ================================================================

// Archivo json donde se guardaran los datos.
$jsonData = "db/db.json";

// En caso de que el archivo no haya sido creado, se crea vacio.
if(file_exists($jsonData) == false)    {
    file_put_contents($jsonData, "[]");
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halcon Order Hub - Home</title>
    <link rel="stylesheet" href="css/styles.css">
    
    <!-- Fuentes de Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Asap+Condensed:wght@400;800&display=swap" rel="stylesheet">
</head>
<header>
    <h2 id="header-title">🦅 Halcon Order Hub</h2>
    <button id="login-button">Acceso de Personal</button>
</header>
<body>
    <div id="estado-title">
        <h1 id="estado-t1">ESTADO DE</h1>
        <h1 id="estado-t2">TU PEDIDO</h1>
    </div>
    <h2 id="estado-descripcion">Ingresa tu número de cliente y el número de factura para consultar el estado actual de tu pedido.</h2>

</body>
</html>