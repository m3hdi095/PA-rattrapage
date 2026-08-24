<?php
// Header commun aux pages adhérent. A inclure après session_start() et le
// check du rôle. Suppose que la page courante est dans web/adherent/.
$currentPage = basename($_SERVER['PHP_SELF']);
function navClass($page, $currentPage) {
    return $page === $currentPage ? 'active' : '';
}
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
        <a href="collectes.php" class="<?= navClass('collectes.php', $currentPage) ?>">Mes collectes</a>
        <a href="services.php" class="<?= navClass('services.php', $currentPage) ?>">Services et inscriptions</a>
        <a href="profil.php" class="<?= navClass('profil.php', $currentPage) ?>">Mon profil</a>
        <a href="../logout.php">Déconnexion</a>
    </nav>

    <div class="container">