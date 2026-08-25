<?php
http_response_code(500);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NO MORE WASTE - Erreur serveur</title>
    <link rel="stylesheet" href="<?= $base ?>/public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1>500 - Erreur serveur</h1>
    </div>

    <div class="container">
        <div class="card">
            <p>Une erreur inattendue s'est produite. Réessaie dans un instant.</p>
            <p><a class="button" href="<?= $base ?>/index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>
