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
        if (($_POST['action'] ?? '') === 'supprimer') {
            apiRequest('DELETE', '/destinataires', ['id' => (int) ($_POST['id'] ?? 0)], $_SESSION['token']);
        } else {
            apiRequest('POST', '/destinataires', [
                'type'        => $_POST['type'] ?? '',
                'nom'         => $_POST['nom'] ?? '',
                'adresse'     => $_POST['adresse'] ?? '',
                'code_postal' => $_POST['code_postal'] ?? '',
                'ville'       => $_POST['ville'] ?? '',
                'telephone'   => $_POST['telephone'] ?? '',
            ], $_SESSION['token']);
        }
        header('Location: destinataires.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$destinataires = [];
try {
    $result = apiRequest('GET', '/destinataires', null, $_SESSION['token']);
    $destinataires = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

require __DIR__ . '/../includes/header_admin.php';
?>

<h2><?= t('destinataires_page_heading') ?></h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3><?= t('add_destinataire_heading') ?></h3>
<form method="post">
    <label><?= t('type_label') ?> :
        <select name="type" required>
            <option value="association"><?= t('association_option') ?></option>
            <option value="particulier"><?= t('particulier_option') ?></option>
        </select>
    </label><br>
    <label><?= t('nom_label') ?> : <input type="text" name="nom" required></label><br>
    <label><?= t('adresse_label') ?> : <input type="text" name="adresse" required></label><br>
    <label><?= t('code_postal_label') ?> : <input type="text" name="code_postal" required></label><br>
    <label><?= t('ville_label') ?> : <input type="text" name="ville" required></label><br>
    <label><?= t('telephone_label') ?> : <input type="tel" name="telephone"></label><br>
    <button type="submit"><?= t('action_add') ?></button>
</form>

<h3><?= t('list_heading') ?></h3>
<table border="1" cellpadding="6">
    <tr>
        <th><?= t('id_column') ?></th>
        <th><?= t('type_label') ?></th>
        <th><?= t('nom_label') ?></th>
        <th><?= t('adresse_label') ?></th>
        <th><?= t('code_postal_label') ?></th>
        <th><?= t('ville_label') ?></th>
        <th><?= t('telephone_label') ?></th>
        <th><?= t('actions_column') ?></th>
    </tr>
    <?php foreach ($destinataires as $d): ?>
    <tr>
        <td><?= htmlspecialchars($d['id']) ?></td>
        <td><?= htmlspecialchars($d['type']) ?></td>
        <td><?= htmlspecialchars($d['nom']) ?></td>
        <td><?= htmlspecialchars($d['adresse']) ?></td>
        <td><?= htmlspecialchars($d['code_postal']) ?></td>
        <td><?= htmlspecialchars($d['ville']) ?></td>
        <td><?= htmlspecialchars($d['telephone']) ?></td>
        <td>
            <form method="post" onsubmit="return confirm(<?= json_encode(t('confirm_delete_destinataire')) ?>);">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                <button type="submit"><?= t('action_delete') ?></button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
