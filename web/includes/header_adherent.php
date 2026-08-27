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
    <title><?= t('adherent_page_title') ?></title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1><?= t('adherent_space_heading') ?></h1>
    </div>

    <nav class="navbar">
        <a href="collectes.php" class="<?= navClass('collectes.php', $currentPage) ?>"><?= t('nav_mes_collectes') ?></a>
        <a href="services.php" class="<?= navClass('services.php', $currentPage) ?>"><?= t('nav_services_inscriptions') ?></a>
        <a href="profil.php" class="<?= navClass('profil.php', $currentPage) ?>"><?= t('nav_mon_profil') ?></a>
        <a href="../logout.php"><?= t('nav_logout') ?></a>
    </nav>

    <div class="container">