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
            apiRequest('DELETE', '/capacites', ['id' => (int) ($_POST['id'] ?? 0)], $_SESSION['token']);
        } else {
            apiRequest('POST', '/capacites', [
                'libelle' => $_POST['libelle'] ?? '',
            ], $_SESSION['token']);
        }
        header('Location: competences.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
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

<h2><?= t('competences_page_heading') ?></h2>

<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3><?= t('add_competence_heading') ?></h3>
<form method="post">
    <label><?= t('libelle_label') ?> : <input type="text" name="libelle" required></label><br>
    <button type="submit"><?= t('action_add') ?></button>
</form>

<h3><?= t('list_heading') ?></h3>
<table border="1" cellpadding="6">
    <tr>
        <th><?= t('id_column') ?></th>
        <th><?= t('libelle_label') ?></th>
        <th><?= t('actions_column') ?></th>
    </tr>
    <?php foreach ($capacites as $c): ?>
    <tr>
        <td><?= htmlspecialchars($c['id']) ?></td>
        <td><?= htmlspecialchars($c['libelle']) ?></td>
        <td>
            <form method="post" onsubmit="return confirm(<?= json_encode(t('confirm_delete_competence')) ?>);">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                <button type="submit"><?= t('action_delete') ?></button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
