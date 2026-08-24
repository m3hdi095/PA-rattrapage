<?php
require_once __DIR__ . '/../includes/i18n.php';
require_once __DIR__ . '/../includes/api.php';

$error = null;

$capacitesDisponibles = [];
try {
    $result = apiRequest('GET', '/capacites');
    $capacitesDisponibles = $result['body'] ?? [];
} catch (Exception $e) {
    // Pas bloquant : le select sera juste vide si l'API est indisponible.
}

$selectedCapacites = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $selectedCapacites = $_POST['capacites'] ?? [];

    $payload = json_encode([
        'email'     => $email,
        'password'  => $password,
        'nom'       => $nom,
        'prenom'    => $prenom,
        'telephone' => $telephone,
        'capacites' => $selectedCapacites,
    ]);

    $ch = curl_init('http://localhost:8081/benevoles');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        $error = t('api_unreachable_error') . ' (' . $curlError . ')';
    } elseif ($statusCode === 201) {
        header('Location: index.php?created=1');
        exit;
    } else {
        $body = json_decode($response, true);
        $error = $body['error'] ?? t('account_create_error') . " (code $statusCode)";
    }
}
?>
<!DOCTYPE html>
<html lang="<?= currentLang() ?>">
<head>
    <meta charset="UTF-8">
    <title><?= t('benevole_register_title') ?></title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1><?= t('benevole_register_heading') ?></h1>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <label><?= t('nom_label') ?> : <input type="text" name="nom" required></label>
            <label><?= t('prenom_label') ?> : <input type="text" name="prenom" required></label>
            <label><?= t('telephone_label') ?> : <input type="tel" name="telephone"></label>
            <label><?= t('email_label') ?> : <input type="email" name="email" required></label>
            <label><?= t('password_label') ?> : <input type="password" name="password" required></label>

            <label><?= t('competences_legend') ?> :
                <select name="capacites[]" multiple size="4">
                    <?php foreach ($capacitesDisponibles as $c): ?>
                        <option value="<?= htmlspecialchars($c['libelle']) ?>" <?= in_array($c['libelle'], $selectedCapacites, true) ? 'selected' : '' ?>><?= htmlspecialchars($c['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
                <br><small><?= t('multiselect_hint') ?></small>
            </label>

            <button type="submit"><?= t('send_candidature_button') ?></button>
        </form>

        <p><a href="index.php"><?= t("back_to_login_link") ?></a></p>
        <p><a href="?lang=fr">Français</a> | <a href="?lang=en">English</a></p>
    </div>
</body>
</html>
