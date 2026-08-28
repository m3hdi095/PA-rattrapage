<?php
require_once __DIR__ . '/includes/i18n.php';
http_response_code(500);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="<?= currentLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= t('error_500_title') ?></title>
    <link rel="stylesheet" href="<?= $base ?>/public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1><?= t('error_500_heading') ?></h1>
    </div>

    <div class="container">
        <div class="card">
            <p><?= t('error_500_text') ?></p>
            <p><a class="button" href="<?= $base ?>/index.php"><?= t('back_home_button') ?></a></p>
        </div>
    </div>
</body>
</html>
