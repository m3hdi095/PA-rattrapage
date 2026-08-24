<?php
// Header commun aux pages adhérent. A inclure après session_start() et le
// check du rôle. Suppose que la page courante est dans web/adherent/.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NO MORE WASTE - Espace Commerçant</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1>Espace Commerçant</h1>
    </div>

    <nav class="navbar">
        <a href="collectes.php">Mes collectes</a>
        <a href="services.php">Services et inscriptions</a>
        <a href="profil.php">Mon profil</a>
    </nav>

    <div class="container">