<?php
require_once __DIR__ . '/../includes/i18n.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $capacites = $_POST['capacites'] ?? [];

    $payload = json_encode([
        'email'     => $email,
        'password'  => $password,
        'nom'       => $nom,
        'prenom'    => $prenom,
        'telephone' => $telephone,
        'capacites' => $capacites,
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
</head>
<body>
    <h1><?= t('benevole_register_heading') ?></h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label><?= t('nom_label') ?> : <input type="text" name="nom" required></label><br>
        <label><?= t('prenom_label') ?> : <input type="text" name="prenom" required></label><br>
        <label><?= t('telephone_label') ?> : <input type="tel" name="telephone"></label><br>
        <label><?= t('email_label') ?> : <input type="email" name="email" required></label><br>
        <label><?= t('password_label') ?> : <input type="password" name="password" required></label><br>

        <fieldset>
            <legend><?= t('competences_legend') ?></legend>
            <label><input type="checkbox" name="capacites[]" value="chauffeur"> <?= t('chauffeur_label') ?></label><br>
            <label><input type="checkbox" name="capacites[]" value="cuisinier"> <?= t('cuisinier_label') ?></label><br>
            <label><input type="checkbox" name="capacites[]" value="plombier"> <?= t('plombier_label') ?></label><br>
            <label><input type="checkbox" name="capacites[]" value="electricien"> <?= t('electricien_label') ?></label><br>
        </fieldset>

        <button type="submit"><?= t('send_candidature_button') ?></button>
    </form>

    <p><a href="index.php"><?= t("back_to_login_link") ?></a></p>
    <p><a href="?lang=fr">Français</a> | <a href="?lang=en">English</a></p>
</body>
</html>
