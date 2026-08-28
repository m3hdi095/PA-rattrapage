<?php
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/api.php';
$currentPage = basename($_SERVER['PHP_SELF']);
function navClass($page, $currentPage) {
    return $page === $currentPage ? 'active' : '';
}

$adhesionExpiree = false;
if (isset($_SESSION['token'])) {
    try {
        $result = apiRequest('GET', '/adherents/me', null, $_SESSION['token']);
        $dateExpiration = $result['body']['date_expiration'] ?? null;
        if ($dateExpiration && strtotime($dateExpiration) < strtotime('today')) {
            $adhesionExpiree = true;
        }
    } catch (Exception $e) {
    }
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
        <span class="lang-switch"><a href="<?= $currentPage ?>?lang=<?= otherLang() ?>"><?= otherLangLabel() ?></a></span>
    </nav>

    <?php if ($adhesionExpiree): ?>
        <p class="error"><?= t('adhesion_expired_banner') ?> <a href="profil.php"><?= t('renew_adhesion_button') ?></a></p>
    <?php endif; ?>

    <div class="container">