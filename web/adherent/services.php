<?php
session_start();
require_once __DIR__ . '/../includes/api.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'adherent') {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        apiRequest('POST', '/inscriptions', [
            'planning_id' => (int) ($_POST['planning_id'] ?? 0),
        ], $_SESSION['token']);
        header('Location: services.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$services = [];
$plannings = [];
try {
    $servicesResult = apiRequest('GET', '/services', null, $_SESSION['token']);
    $services = $servicesResult['body'] ?? [];

    $planningsResult = apiRequest('GET', '/plannings', null, $_SESSION['token']);
    $plannings = $planningsResult['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$serviceNoms = [];
foreach ($services as $s) {
    $serviceNoms[$s['id']] = $s['nom'];
}

require __DIR__ . '/../includes/header_adherent.php';
?>

<h2>Services proposés</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3>Nos services</h3>
<ul>
    <?php foreach ($services as $s): ?>
        <li><strong><?= htmlspecialchars($s['nom']) ?></strong> — <?= htmlspecialchars($s['description']) ?></li>
    <?php endforeach; ?>
</ul>

<h3>Créneaux disponibles</h3>
<table border="1" cellpadding="6">
    <tr>
        <th>Service</th>
        <th>Début</th>
        <th>Fin</th>
        <th>Lieu</th>
        <th>Places max</th>
        <th>Action</th>
    </tr>
    <?php foreach ($plannings as $p): ?>
    <tr>
        <td><?= htmlspecialchars($serviceNoms[$p['service_id']] ?? ('Service #' . $p['service_id'])) ?></td>
        <td><?= htmlspecialchars($p['date_debut']) ?></td>
        <td><?= htmlspecialchars($p['date_fin']) ?></td>
        <td><?= htmlspecialchars($p['lieu']) ?></td>
        <td><?= htmlspecialchars($p['places_max']) ?></td>
        <td>
            <form method="post">
                <input type="hidden" name="planning_id" value="<?= (int) $p['id'] ?>">
                <button type="submit">S'inscrire</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
