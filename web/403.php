<?php
require_once __DIR__ . '/includes/i18n.php';
http_response_code(403);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="<?= currentLang() ?>">
<head>
    <meta charset="UTF-8">
    <title>NO MORE WASTE - Accès refusé</title>
    <link rel="stylesheet" href="<?= $base ?>/public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1>403 - Accès refusé</h1>
    </div>

    <div class="container">
        <div class="card">
            <p>Tu n'as pas le droit d'accéder à cette page.</p>
            <p><a class="button" href="<?= $base ?>/index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>
