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
        if (($_POST['action'] ?? '') === 'update_statut') {
            $result = apiRequest('PATCH', '/tournees/statut', [
                'id'     => (int) $_POST['id'],
                'statut' => $_POST['statut'],
            ], $_SESSION['token']);
        } elseif (($_POST['action'] ?? '') === 'supprimer') {
            $result = apiRequest('DELETE', '/tournees', ['id' => (int) $_POST['id']], $_SESSION['token']);
        } else {
            $result = apiRequest('POST', '/tournees', [
                'benevole_id'  => (int) ($_POST['benevole_id'] ?? 0),
                'date_tournee' => $_POST['date_tournee'] ?? '',
            ], $_SESSION['token']);
        }
        if ($result['statusCode'] >= 400) {
            $error = $result['body']['error'] ?? t('tournee_create_error');
        } else {
            header('Location: tournees.php');
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$tournees = [];
try {
    $result = apiRequest('GET', '/tournees', null, $_SESSION['token']);
    $tournees = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$benevoles = [];
try {
    $result = apiRequest('GET', '/benevoles', null, $_SESSION['token']);
    $benevoles = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$benevolesById = [];
foreach ($benevoles as $b) {
    $benevolesById[$b['id']] = $b['nom'] . ' ' . $b['prenom'];
}

require __DIR__ . '/../includes/header_admin.php';
?>

<h2><?= t('tournees_page_heading') ?></h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3><?= t('create_tournee_heading') ?></h3>
<form method="post">
    <label><?= t('benevole_chauffeur_label') ?> :
        <select name="benevole_id" required>
            <option value=""><?= t('choose_placeholder') ?></option>
            <?php foreach ($benevoles as $b): ?>
                <option value="<?= (int) $b['id'] ?>"><?= htmlspecialchars($b['nom'] . ' ' . $b['prenom']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label><?= t('date_label') ?> : <input type="date" name="date_tournee" min="<?= date('Y-m-d') ?>" required></label><br>
    <button type="submit"><?= t('action_create') ?></button>
</form>

<h3><?= t('list_heading') ?></h3>
<table border="1" cellpadding="6">
    <tr>
        <th><?= t('id_column') ?></th>
        <th><?= t('benevole_column') ?></th>
        <th><?= t('date_label') ?></th>
        <th><?= t('statut_column') ?></th>
        <th><?= t('actions_column') ?></th>
    </tr>
    <?php foreach ($tournees as $t): ?>
    <tr>
        <td><?= htmlspecialchars($t['id']) ?></td>
        <td><?= htmlspecialchars($benevolesById[$t['benevole_id']] ?? ('bénévole #' . $t['benevole_id'])) ?></td>
        <td><?= htmlspecialchars($t['date_tournee']) ?></td>
        <td><?= htmlspecialchars($t['statut']) ?></td>
        <td>
            <?php foreach (['planifiee', 'en_cours', 'terminee'] as $statut): ?>
                <?php if ($statut !== $t['statut']): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="update_statut">
                    <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                    <input type="hidden" name="statut" value="<?= $statut ?>">
                    <button type="submit">&rarr; <?= $statut ?></button>
                </form>
                <?php endif; ?>
            <?php endforeach; ?>
            <form method="post" style="display:inline;" onsubmit="return confirm(<?= json_encode(t('confirm_delete_tournee')) ?>);">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                <button type="submit"><?= t('action_delete') ?></button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
