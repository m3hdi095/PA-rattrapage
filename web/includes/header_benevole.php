<?php
// Header commun aux pages bénévole.
$currentPage = basename($_SERVER['PHP_SELF']);
function navClass($page, $currentPage) {
    return $page === $currentPage ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NO MORE WASTE - Espace Bénévole</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1>Espace Bénévole</h1>
    </div>

    <nav class="navbar">
        <a href="plannings.php" class="<?= navClass('plannings.php', $currentPage) ?>">Mon planning</a>
        <a href="profil.php" class="<?= navClass('profil.php', $currentPage) ?>">Mon profil</a>
        <a href="../logout.php">Déconnexion</a>
    </nav>

    <div class="container">