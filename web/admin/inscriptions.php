<?php
session_start();
require_once __DIR__ . '/../includes/api.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$planningId = isset($_GET['planning_id']) ? (int) $_GET['planning_id'] : null;
$inscriptions = [];
$error = null;

if ($planningId !== null) {
    try {
        $result = apiRequest('GET', '/inscriptions?planning_id=' . $planningId, null, $_SESSION['token']);
        $inscriptions = $result['body'] ?? [];
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require __DIR__ . '/../includes/header_admin.php';
?>

<h2>Inscriptions à un créneau</h2>

<form method="get">
    <label>ID du planning : <input type="number" name="planning_id" value="<?= htmlspecialchars($planningId ?? '') ?>" required></label>
    <button type="submit">Voir les inscriptions</button>
</form>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($planningId !== null && !$error): ?>
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Adhérent (id)</th>
        <th>Statut</th>
    </tr>
    <?php foreach ($inscriptions as $i): ?>
    <tr>
        <td><?= htmlspecialchars($i['id']) ?></td>
        <td><?= htmlspecialchars($i['adherent_id']) ?></td>
        <td><?= htmlspecialchars($i['statut']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>
