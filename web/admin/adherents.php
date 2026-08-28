<?php
session_start();
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/i18n.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (($_POST['action'] ?? '') === 'creer') {
            $result = apiRequest('POST', '/adherents', [
                'email'       => $_POST['email'] ?? '',
                'password'    => $_POST['password'] ?? '',
                'nom'         => $_POST['nom'] ?? '',
                'siret'       => $_POST['siret'] ?? '',
                'adresse'     => $_POST['adresse'] ?? '',
                'code_postal' => $_POST['code_postal'] ?? '',
                'ville'       => $_POST['ville'] ?? '',
                'telephone'   => $_POST['telephone'] ?? '',
            ], $_SESSION['token']);
            if ($result['statusCode'] >= 400) {
                $error = $result['body']['error'] ?? t('create_adherent_error_fallback');
            }
        } else {
            apiRequest('DELETE', '/adherents', ['id' => (int) ($_POST['id'] ?? 0)], $_SESSION['token']);
        }
        if (!$error) {
            header('Location: adherents.php');
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$adherents = [];
try {
    $result = apiRequest('GET', '/adherents', null, $_SESSION['token']);
    $adherents = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

require __DIR__ . '/../includes/header_admin.php';
?>

<h2><?= t('adherents_page_heading') ?></h2>

<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3><?= t('create_adherent_heading') ?></h3>
<form method="post">
    <input type="hidden" name="action" value="creer">
    <label><?= t('email_label') ?> : <input type="email" name="email" required></label><br>
    <label><?= t('password_label') ?> : <input type="password" name="password" minlength="6" required></label><br>
    <label><?= t('nom_commerce_label') ?> : <input type="text" name="nom" required></label><br>
    <label><?= t('siret_label') ?> : <input type="text" name="siret" pattern="\d{14}" maxlength="14" inputmode="numeric" title="<?= t('siret_pattern_hint') ?>" required></label><br>
    <label><?= t('adresse_label') ?> : <input type="text" name="adresse"></label><br>
    <label><?= t('code_postal_label') ?> : <input type="text" name="code_postal"></label><br>
    <label><?= t('ville_label') ?> : <input type="text" name="ville"></label><br>
    <label><?= t('telephone_label') ?> : <input type="tel" name="telephone"></label><br>
    <button type="submit"><?= t('action_create') ?></button>
</form>

<h3><?= t('list_heading') ?></h3>
<table border="1" cellpadding="6">
    <tr>
        <th><?= t('id_column') ?></th>
        <th><?= t('email_label') ?></th>
        <th><?= t('nom_commerce_label') ?></th>
        <th><?= t('siret_label') ?></th>
        <th><?= t('adresse_label') ?></th>
        <th><?= t('code_postal_label') ?></th>
        <th><?= t('ville_label') ?></th>
        <th><?= t('telephone_label') ?></th>
        <th><?= t('adhesion_column') ?></th>
        <th><?= t('expiration_column') ?></th>
        <th><?= t('actions_column') ?></th>
    </tr>
    <?php foreach ($adherents as $a): ?>
    <tr>
        <td><?= htmlspecialchars($a['id']) ?></td>
        <td><?= htmlspecialchars($a['email']) ?></td>
        <td><?= htmlspecialchars($a['nom']) ?></td>
        <td><?= htmlspecialchars($a['siret']) ?></td>
        <td><?= htmlspecialchars($a['adresse']) ?></td>
        <td><?= htmlspecialchars($a['code_postal']) ?></td>
        <td><?= htmlspecialchars($a['ville']) ?></td>
        <td><?= htmlspecialchars($a['telephone']) ?></td>
        <td><?= htmlspecialchars($a['date_adhesion']) ?></td>
        <td><?= htmlspecialchars($a['date_expiration']) ?></td>
        <td>
            <form method="post" onsubmit="return confirm(<?= json_encode(t('confirm_delete_adherent')) ?>);">
                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                <button type="submit"><?= t('action_delete') ?></button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
