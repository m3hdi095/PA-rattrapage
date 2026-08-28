<?php
require_once __DIR__ . '/../includes/i18n.php';
require_once __DIR__ . '/../includes/api.php';

$error = null;

$capacitesDisponibles = [];
try {
    $result = apiRequest('GET', '/capacites');
    $capacitesDisponibles = $result['body'] ?? [];
} catch (Exception $e) {
}

$selectedCapacites = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedCapacites = $_POST['capacites'] ?? [];

    $payload = [
        'email'     => $_POST['email'] ?? '',
        'password'  => $_POST['password'] ?? '',
        'nom'       => $_POST['nom'] ?? '',
        'prenom'    => $_POST['prenom'] ?? '',
        'telephone' => $_POST['telephone'] ?? '',
        'capacites' => $selectedCapacites,
    ];

    try {
        $result = apiRequest('POST', '/benevoles', $payload);

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
            <label><?= t('password_label') ?> : <input type="password" name="password" minlength="6" required></label>

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
        <p><a href="?lang=<?= otherLang() ?>"><?= otherLangLabel() ?></a></p>
    </div>
</body>
</html>
