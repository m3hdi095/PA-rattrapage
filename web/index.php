<?php
require_once __DIR__ . '/includes/i18n.php';
?>
<!DOCTYPE html>
<html lang="<?= currentLang() ?>">
<head>
    <meta charset="UTF-8">
    <title><?= t('site_title') ?></title>
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1><?= t('site_title') ?></h1>
    </div>

    <div class="container">
        <div class="card">
            <p><?= t('home_choose_space') ?></p>
            <p>
                <a class="button" href="admin/index.php"><?= t('home_admin_space') ?></a>
                <a class="button" href="adherent/index.php"><?= t('home_adherent_space') ?></a>
                <a class="button" href="benevole/index.php"><?= t('home_benevole_space') ?></a>
            </p>
        </div>
        <p><a href="?lang=<?= otherLang() ?>"><?= otherLangLabel() ?></a></p>
    </div>
</body>
</html>
