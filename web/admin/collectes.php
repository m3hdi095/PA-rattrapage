<?php
session_start();
require_once __DIR__ . '/../includes/api.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (($_POST['action'] ?? '') === 'supprimer') {
            apiRequest('DELETE', '/collectes', ['id' => (int) ($_POST['id'] ?? 0)], $_SESSION['token']);
        } else {
            apiRequest('PATCH', '/collectes/statut', [
                'id'     => (int) ($_POST['id'] ?? 0),
                'statut' => $_POST['statut'] ?? '',
            ], $_SESSION['token']);
        }
        header('Location: collectes.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$collectes = [];
try {
    $result = apiRequest('GET', '/collectes', null, $_SESSION['token']);
    $collectes = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$adherents = [];
try {
    $result = apiRequest('GET', '/adherents', null, $_SESSION['token']);
    $adherents = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$adherentsById = [];
foreach ($adherents as $a) {
    $adherentsById[$a['id']] = $a['nom'];
}

require __DIR__ . '/../includes/header_admin.php';
?>

<h2>Gestion des collectes</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Adhérent</th>
        <th>Date collecte</th>
        <th>Statut</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($collectes as $c): ?>
    <tr>
        <td><?= htmlspecialchars($c['id']) ?></td>
        <td><?= htmlspecialchars($adherentsById[$c['adherent_id']] ?? ('adhérent #' . $c['adherent_id'])) ?></td>
        <td><?= htmlspecialchars($c['date_collecte']) ?></td>
        <td><?= htmlspecialchars($c['statut']) ?></td>
        <td>
            <?php foreach (['planifiee', 'effectuee', 'annulee'] as $statut): ?>
                <?php if ($statut !== $c['statut']): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                    <input type="hidden" name="statut" value="<?= $statut ?>">
                    <button type="submit">&rarr; <?= $statut ?></button>
                </form>
                <?php endif; ?>
            <?php endforeach; ?>
            <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer cette collecte ?');">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                <button type="submit">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
