<?php
require_once __DIR__ . '/../includes/i18n.php';
require_once __DIR__ . '/../includes/api.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'email'       => $_POST['email'] ?? '',
        'password'    => $_POST['password'] ?? '',
        'nom'         => $_POST['nom_commerce'] ?? '',
        'siret'       => $_POST['siret'] ?? '',
        'adresse'     => $_POST['adresse'] ?? '',
        'code_postal' => $_POST['code_postal'] ?? '',
        'ville'       => $_POST['ville'] ?? '',
        'telephone'   => $_POST['telephone'] ?? '',
    ];

    try {
        $result = apiRequest('POST', '/adherents', $payload);

        if ($result['statusCode'] === 201) {
            header('Location: index.php?created=1');
            exit;
        } else {
            $error = $result['body']['error'] ?? t('account_create_error') . " (code {$result['statusCode']})";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="<?= currentLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            <label><?= t('siret_label') ?> : <input type="text" name="siret" pattern="\d{14}" maxlength="14" inputmode="numeric" title="<?= t('siret_pattern_hint') ?>" required></label>
            <label><?= t('adresse_label') ?> : <input type="text" name="adresse" required></label>
            <label><?= t('code_postal_label') ?> : <input type="text" name="code_postal" pattern="\d{5}" maxlength="5" inputmode="numeric" required></label>
            <label><?= t('ville_label') ?> : <input type="text" name="ville" required></label>
            <label><?= t('telephone_label') ?> : <input type="tel" name="telephone"></label>
            <label><?= t('email_label') ?> : <input type="email" name="email" required></label>
            <label><?= t('password_label') ?> : <input type="password" name="password" minlength="6" required></label>
            <button type="submit"><?= t('create_account_button') ?></button>
        </form>

        <p><a href="index.php"><?= t("back_to_login_link") ?></a></p>
        <p><a href="?lang=<?= otherLang() ?>"><?= otherLangLabel() ?></a></p>
    </div>
</body>
</html>
