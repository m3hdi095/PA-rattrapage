<?php
$currentPage = basename($_SERVER['PHP_SELF']);
function navClass($page, $currentPage) {
    return $page === $currentPage ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NO MORE WASTE - Espace Admin</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1>Espace Admin</h1>
    </div>

    <nav class="navbar">
        <a href="adherents.php" class="<?= navClass('adherents.php', $currentPage) ?>">Adhérents</a>
        <a href="benevoles.php" class="<?= navClass('benevoles.php', $currentPage) ?>">Bénévoles</a>
        <a href="collectes.php" class="<?= navClass('collectes.php', $currentPage) ?>">Collectes</a>
        <a href="produits.php" class="<?= navClass('produits.php', $currentPage) ?>">Produits</a>
        <a href="destinataires.php" class="<?= navClass('destinataires.php', $currentPage) ?>">Destinataires</a>
        <a href="tournees.php" class="<?= navClass('tournees.php', $currentPage) ?>">Tournées</a>
        <a href="livraisons.php" class="<?= navClass('livraisons.php', $currentPage) ?>">Livraisons</a>
        <a href="services.php" class="<?= navClass('services.php', $currentPage) ?>">Services</a>
        <a href="plannings.php" class="<?= navClass('plannings.php', $currentPage) ?>">Plannings</a>
        <a href="inscriptions.php" class="<?= navClass('inscriptions.php', $currentPage) ?>">Inscriptions</a>
        <a href="competences.php" class="<?= navClass('competences.php', $currentPage) ?>">Compétences</a>
        <?php if (($_SESSION['admin_role'] ?? '') === 'super_admin'): ?>
        <a href="admins.php" class="<?= navClass('admins.php', $currentPage) ?>">Comptes admin</a>
        <?php endif; ?>
        <a href="../logout.php">Déconnexion</a>
    </nav>

    <div class="container">