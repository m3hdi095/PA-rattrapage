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
            $result = apiRequest('DELETE', '/services', ['id' => (int) ($_POST['id'] ?? 0)], $_SESSION['token']);
        } else {
            $capaciteId = ($_POST['capacite_id'] ?? '') !== '' ? (int) $_POST['capacite_id'] : null;
            $result = apiRequest('POST', '/services', [
                'nom'         => $_POST['nom'] ?? '',
                'description' => $_POST['description'] ?? '',
                'capacite_id' => $capaciteId,
            ], $_SESSION['token']);
        }
        if ($result['statusCode'] >= 400) {
            $error = $result['body']['error'] ?? t('service_action_error');
        } else {
            header('Location: services.php');
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$services = [];
try {
    $result = apiRequest('GET', '/services', null, $_SESSION['token']);
    $services = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$capacites = [];
try {
    $result = apiRequest('GET', '/capacites', null, $_SESSION['token']);
    $capacites = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

require __DIR__ . '/../includes/header_admin.php';
?>

<h2><?= t('services_page_heading') ?></h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3><?= t('create_service_heading') ?></h3>
<form method="post">
    <label><?= t('nom_label') ?> : <input type="text" name="nom" required></label><br>
    <label><?= t('description_label') ?> : <textarea name="description"></textarea></label><br>
    <label><?= t('competence_requise_label') ?> :
        <select name="capacite_id">
            <option value=""><?= t('none_placeholder') ?></option>
            <?php foreach ($capacites as $c): ?>
                <option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['libelle']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <button type="submit"><?= t('action_create') ?></button>
</form>

<h3><?= t('list_heading') ?></h3>
<table border="1" cellpadding="6">
    <tr>
        <th><?= t('id_column') ?></th>
        <th><?= t('nom_label') ?></th>
        <th><?= t('description_label') ?></th>
        <th><?= t('competence_requise_column') ?></th>
        <th><?= t('actions_column') ?></th>
    </tr>
    <?php foreach ($services as $s): ?>
    <tr>
        <td><?= htmlspecialchars($s['id']) ?></td>
        <td><?= htmlspecialchars($s['nom']) ?></td>
        <td><?= htmlspecialchars($s['description']) ?></td>
        <td><?= htmlspecialchars($s['capacite_libelle'] ?? '—') ?></td>
        <td>
            <form method="post" onsubmit="return confirm(<?= json_encode(t('confirm_delete_service')) ?>);">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                <button type="submit"><?= t('action_delete') ?></button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
