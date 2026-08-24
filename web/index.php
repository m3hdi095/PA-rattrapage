<?php
require_once __DIR__ . '/includes/i18n.php';
?>
<!DOCTYPE html>
<html lang="<?= currentLang() ?>">
<head>
    <meta charset="UTF-8">
    <title><?= t('site_title') ?></title>
</head>
<body>
    <h1><?= t('site_title') ?></h1>
    <p><?= t('home_choose_space') ?></p>
    <ul>
        <li><a href="admin/index.php"><?= t('home_admin_space') ?></a></li>
        <li><a href="adherent/index.php"><?= t('home_adherent_space') ?></a></li>
        <li><a href="benevole/index.php"><?= t('home_benevole_space') ?></a></li>
    </ul>
    <p><a href="?lang=fr">Français</a> | <a href="?lang=en">English</a></p>
</body>
</html>