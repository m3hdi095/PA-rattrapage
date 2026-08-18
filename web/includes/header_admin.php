<?php
// Header commun aux pages admin. A inclure après session_start() et le check
// du role admin. Suppose que la page courante est dans web/admin/.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NO MORE WASTE - Espace Admin</title>
</head>
<body>
    <h1>Espace Admin</h1>

    <nav>
        <a href="adherents.php">Adhérents</a> |
        <a href="benevoles.php">Bénévoles</a> |
        <a href="collectes.php">Collectes</a> |
        <a href="produits.php">Produits</a> |
        <a href="destinataires.php">Destinataires</a> |
        <a href="tournees.php">Tournées</a> |
        <a href="livraisons.php">Livraisons</a> |
        <a href="services.php">Services</a> |
        <a href="plannings.php">Plannings</a> |
        <a href="inscriptions.php">Inscriptions</a>
    </nav>

    <hr>
