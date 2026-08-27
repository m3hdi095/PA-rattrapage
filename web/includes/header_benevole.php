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
    <title><?= t('benevole_page_title') ?></title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1><?= t('benevole_space_heading') ?></h1>
    </div>

    <nav class="navbar">
        <a href="plannings.php" class="<?= navClass('plannings.php', $currentPage) ?>"><?= t('nav_mon_planning') ?></a>
        <a href="profil.php" class="<?= navClass('profil.php', $currentPage) ?>"><?= t('nav_mon_profil') ?></a>
        <a href="../logout.php"><?= t('nav_logout') ?></a>
        <span class="lang-switch"><a href="<?= $currentPage ?>?lang=<?= otherLang() ?>"><?= otherLangLabel() ?></a></span>
    </nav>

    <div class="container">