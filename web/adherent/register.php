<?php
require_once __DIR__ . '/../includes/i18n.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomCommerce = $_POST['nom_commerce'] ?? '';
    $siret = $_POST['siret'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $codePostal = $_POST['code_postal'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $telephone = $_POST['telephone'] ?? '';

    $payload = json_encode([
        'email'       => $email,
        'password'    => $password,
        'nom'         => $nomCommerce,
        'siret'       => $siret,
        'adresse'     => $adresse,
        'code_postal' => $codePostal,
        'ville'       => $ville,
        'telephone'   => $telephone,
    ]);

    $ch = curl_init('http://localhost:8081/adherents');
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
    <title><?= t('adherent_register_title') ?></title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1><?= t('adherent_register_heading') ?></h1>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <label><?= t('nom_commerce_label') ?> : <input type="text" name="nom_commerce" required></label>
            <label><?= t('siret_label') ?> : <input type="text" name="siret" required></label>
            <label><?= t('adresse_label') ?> : <input type="text" name="adresse" required></label>
            <label><?= t('code_postal_label') ?> : <input type="text" name="code_postal" required></label>
            <label><?= t('ville_label') ?> : <input type="text" name="ville" required></label>
            <label><?= t('telephone_label') ?> : <input type="tel" name="telephone"></label>
            <label><?= t('email_label') ?> : <input type="email" name="email" required></label>
            <label><?= t('password_label') ?> : <input type="password" name="password" required></label>
            <button type="submit"><?= t('create_account_button') ?></button>
        </form>

        <p><a href="index.php"><?= t("back_to_login_link") ?></a></p>
        <p><a href="?lang=fr">Français</a> | <a href="?lang=en">English</a></p>
    </div>
</body>
</html>
