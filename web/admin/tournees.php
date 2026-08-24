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
        if (($_POST['action'] ?? '') === 'update_statut') {
            apiRequest('PATCH', '/tournees/statut', [
                'id'     => (int) $_POST['id'],
                'statut' => $_POST['statut'],
            ], $_SESSION['token']);
        } elseif (($_POST['action'] ?? '') === 'supprimer') {
            apiRequest('DELETE', '/tournees', ['id' => (int) $_POST['id']], $_SESSION['token']);
        } else {
            apiRequest('POST', '/tournees', [
                'benevole_id'  => (int) ($_POST['benevole_id'] ?? 0),
                'date_tournee' => $_POST['date_tournee'] ?? '',
            ], $_SESSION['token']);
        }
        header('Location: tournees.php');
        exit;
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

<h2>Tournées de distribution</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3>Créer une tournée</h3>
<form method="post">
    <label>Bénévole (chauffeur) :
        <select name="benevole_id" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($benevoles as $b): ?>
                <option value="<?= (int) $b['id'] ?>"><?= htmlspecialchars($b['nom'] . ' ' . $b['prenom']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label>Date : <input type="date" name="date_tournee" required></label><br>
    <button type="submit">Créer</button>
</form>

<h3>Liste</h3>
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Bénévole</th>
        <th>Date</th>
        <th>Statut</th>
        <th>Actions</th>
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
            <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer cette tournée ?');">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                <button type="submit">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
