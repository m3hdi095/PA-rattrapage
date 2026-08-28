<?php
require_once __DIR__ . '/includes/i18n.php';
?>
<!DOCTYPE html>
<html lang="<?= currentLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= t('site_title') ?></title>
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1><?= t('site_title') ?></h1>
    </div>

    <div class="container">
        <div class="hero">
            <p class="hero-tagline"><?= t('home_tagline') ?></p>
        </div>

        <p class="home-choose"><?= t('home_choose_space') ?></p>

        <div class="space-grid">
            <a class="space-card" href="admin/index.php">
                <span class="space-icon">🗂️</span>
                <h2><?= t('home_admin_space') ?></h2>
                <p><?= t('home_admin_desc') ?></p>
            </a>
            <a class="space-card" href="adherent/index.php">
                <span class="space-icon">🏪</span>
                <h2><?= t('home_adherent_space') ?></h2>
                <p><?= t('home_adherent_desc') ?></p>
            </a>
            <a class="space-card" href="benevole/index.php">
                <span class="space-icon">🤝</span>
                <h2><?= t('home_benevole_space') ?></h2>
                <p><?= t('home_benevole_desc') ?></p>
            </a>
        </div>

        <p class="lang-switch-home"><a href="?lang=<?= otherLang() ?>"><?= otherLangLabel() ?></a></p>
    </div>
</body>
</html>
