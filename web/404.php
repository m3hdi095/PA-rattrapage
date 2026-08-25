<?php
require_once __DIR__ . '/includes/i18n.php';
http_response_code(404);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="<?= currentLang() ?>">
<head>
    <meta charset="UTF-8">
    <title>NO MORE WASTE - Page introuvable</title>
    <link rel="stylesheet" href="<?= $base ?>/public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1>404 - Page introuvable</h1>
    </div>

    <div class="container">
        <div class="card">
            <p>La page que tu cherches n'existe pas ou a été déplacée.</p>
            <p><a class="button" href="<?= $base ?>/index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>
