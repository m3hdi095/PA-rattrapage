<?php
session_start();
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/i18n.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'adherent') {
    header('Location: index.php');
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST['form'] ?? '';

    try {
        if ($form === 'profil') {
            apiRequest('PATCH', '/adherents/profil', [
                'nom'         => $_POST['nom'] ?? '',
                'adresse'     => $_POST['adresse'] ?? '',
                'code_postal' => $_POST['code_postal'] ?? '',
                'ville'       => $_POST['ville'] ?? '',
                'telephone'   => $_POST['telephone'] ?? '',
            ], $_SESSION['token']);
            $success = t('profil_updated_msg');
        } elseif ($form === 'mot_de_passe') {
            $result = apiRequest('PATCH', '/adherents/mot-de-passe', [
                'old_password' => $_POST['old_password'] ?? '',
                'new_password' => $_POST['new_password'] ?? '',
            ], $_SESSION['token']);

            if ($result['statusCode'] === 200) {
                $success = t('password_updated_msg');
            } else {
                $error = t('current_password_incorrect_msg');
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$moi = [];
try {
    $result = apiRequest('GET', '/adherents/me', null, $_SESSION['token']);
    $moi = $result['body'] ?? [];
} catch (Exception $e) {
}

require __DIR__ . '/../includes/header_adherent.php';
?>

<h2><?= t('mon_profil_heading') ?></h2>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<h3><?= t('infos_commerce_heading') ?></h3>
<form method="post">
    <input type="hidden" name="form" value="profil">
    <label><?= t('nom_commerce_label') ?> : <input type="text" name="nom" value="<?= htmlspecialchars($moi['nom'] ?? '') ?>" required></label><br>
    <label><?= t('adresse_label') ?> : <input type="text" name="adresse" value="<?= htmlspecialchars($moi['adresse'] ?? '') ?>" required></label><br>
    <label><?= t('code_postal_label') ?> : <input type="text" name="code_postal" value="<?= htmlspecialchars($moi['code_postal'] ?? '') ?>" required></label><br>
    <label><?= t('ville_label') ?> : <input type="text" name="ville" value="<?= htmlspecialchars($moi['ville'] ?? '') ?>" required></label><br>
    <label><?= t('telephone_label') ?> : <input type="tel" name="telephone" value="<?= htmlspecialchars($moi['telephone'] ?? '') ?>"></label><br>
    <button type="submit"><?= t('action_save') ?></button>
</form>

<h3><?= t('change_password_heading') ?></h3>
<form method="post">
    <input type="hidden" name="form" value="mot_de_passe">
    <label><?= t('current_password_label') ?> : <input type="password" name="old_password" required></label><br>
    <label><?= t('new_password_label') ?> : <input type="password" name="new_password" required></label><br>
    <button type="submit"><?= t('change_password_button') ?></button>
</form>

</div>
</body>
</html>
