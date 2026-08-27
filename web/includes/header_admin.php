<?php
require_once __DIR__ . '/i18n.php';
$currentPage = basename($_SERVER['PHP_SELF']);
function navClass($page, $currentPage) {
    return $page === $currentPage ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="<?= currentLang() ?>">
<head>
    <meta charset="UTF-8">
    <title><?= t('admin_page_title') ?></title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1><?= t('admin_space_heading') ?></h1>
    </div>

    <nav class="navbar">
        <a href="adherents.php" class="<?= navClass('adherents.php', $currentPage) ?>"><?= t('nav_adherents') ?></a>
        <a href="benevoles.php" class="<?= navClass('benevoles.php', $currentPage) ?>"><?= t('nav_benevoles') ?></a>
        <a href="collectes.php" class="<?= navClass('collectes.php', $currentPage) ?>"><?= t('nav_collectes') ?></a>
        <a href="produits.php" class="<?= navClass('produits.php', $currentPage) ?>"><?= t('nav_produits') ?></a>
        <a href="destinataires.php" class="<?= navClass('destinataires.php', $currentPage) ?>"><?= t('nav_destinataires') ?></a>
        <a href="tournees.php" class="<?= navClass('tournees.php', $currentPage) ?>"><?= t('nav_tournees') ?></a>
        <a href="livraisons.php" class="<?= navClass('livraisons.php', $currentPage) ?>"><?= t('nav_livraisons') ?></a>
        <a href="services.php" class="<?= navClass('services.php', $currentPage) ?>"><?= t('nav_services') ?></a>
        <a href="plannings.php" class="<?= navClass('plannings.php', $currentPage) ?>"><?= t('nav_plannings') ?></a>
        <a href="inscriptions.php" class="<?= navClass('inscriptions.php', $currentPage) ?>"><?= t('nav_inscriptions') ?></a>
        <a href="competences.php" class="<?= navClass('competences.php', $currentPage) ?>"><?= t('nav_competences') ?></a>
        <?php if (($_SESSION['admin_role'] ?? '') === 'super_admin'): ?>
        <a href="admins.php" class="<?= navClass('admins.php', $currentPage) ?>"><?= t('nav_admins') ?></a>
        <?php endif; ?>
        <a href="../logout.php"><?= t('nav_logout') ?></a>
        <span class="lang-switch"><a href="<?= $currentPage ?>?lang=fr">Français</a> | <a href="<?= $currentPage ?>?lang=en">English</a></span>
    </nav>

    <div class="container">