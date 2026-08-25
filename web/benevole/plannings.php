<?php
session_start();
require_once __DIR__ . '/../includes/api.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'benevole') {
    header('Location: index.php');
    exit;
}

$error = null;
$plannings = [];
try {
    $result = apiRequest('GET', '/plannings', null, $_SESSION['token']);
    $plannings = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$services = [];
try {
    $result = apiRequest('GET', '/services', null, $_SESSION['token']);
    $services = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$servicesById = [];
foreach ($services as $s) {
    $servicesById[$s['id']] = $s['nom'];
}

$ownBenevoleId = jwtClaims($_SESSION['token'])['id'] ?? null;

require __DIR__ . '/../includes/header_benevole.php';
?>

<h2>Mon planning</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<p><a href="download_planning.php">Télécharger mon planning en Excel (.xlsx)</a></p>

<h3>Tous les créneaux planifiés</h3>
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Service</th>
        <th>Bénévole</th>
        <th>Début</th>
        <th>Fin</th>
        <th>Lieu</th>
    </tr>
    <?php foreach ($plannings as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['id']) ?></td>
        <td><?= htmlspecialchars($servicesById[$p['service_id']] ?? ('service #' . $p['service_id'])) ?></td>
        <td><?= $p['benevole_id'] === $ownBenevoleId ? 'Moi' : htmlspecialchars('bénévole #' . $p['benevole_id']) ?></td>
        <td><?= htmlspecialchars($p['date_debut']) ?></td>
        <td><?= htmlspecialchars($p['date_fin']) ?></td>
        <td><?= htmlspecialchars($p['lieu']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
