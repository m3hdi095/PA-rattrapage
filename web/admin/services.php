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
            apiRequest('DELETE', '/services', ['id' => (int) ($_POST['id'] ?? 0)], $_SESSION['token']);
        } else {
            $capaciteId = ($_POST['capacite_id'] ?? '') !== '' ? (int) $_POST['capacite_id'] : null;
            apiRequest('POST', '/services', [
                'nom'         => $_POST['nom'] ?? '',
                'description' => $_POST['description'] ?? '',
                'capacite_id' => $capaciteId,
            ], $_SESSION['token']);
        }
        header('Location: services.php');
        exit;
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

<h2>Services proposés</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3>Créer un service</h3>
<form method="post">
    <label>Nom : <input type="text" name="nom" required></label><br>
    <label>Description : <textarea name="description"></textarea></label><br>
    <label>Compétence requise (optionnel) :
        <select name="capacite_id">
            <option value="">-- Aucune --</option>
            <?php foreach ($capacites as $c): ?>
                <option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['libelle']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <button type="submit">Créer</button>
</form>

<h3>Liste</h3>
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Description</th>
        <th>Compétence requise</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($services as $s): ?>
    <tr>
        <td><?= htmlspecialchars($s['id']) ?></td>
        <td><?= htmlspecialchars($s['nom']) ?></td>
        <td><?= htmlspecialchars($s['description']) ?></td>
        <td><?= htmlspecialchars($s['capacite_libelle'] ?? '—') ?></td>
        <td>
            <form method="post" onsubmit="return confirm('Supprimer ce service ?');">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                <button type="submit">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
