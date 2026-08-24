<?php
require_once __DIR__ . '/includes/i18n.php';
http_response_code(404);
// dirname(SCRIPT_NAME) = chemin de ce fichier, quel que soit le sous-dossier
// où le site est déployé (/, /PA-rattrapage/web, etc.) — indispensable ici
// car une page d'erreur Apache garde l'URL d'origine dans la barre
// d'adresse, donc un lien relatif pointerait au mauvais endroit.
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
