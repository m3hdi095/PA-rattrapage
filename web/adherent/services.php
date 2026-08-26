<?php
session_start();
require_once __DIR__ . '/../includes/api.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'adherent') {
    header('Location: index.php');
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = apiRequest('POST', '/inscriptions', [
            'planning_id' => (int) ($_POST['planning_id'] ?? 0),
        ], $_SESSION['token']);

        if ($result['statusCode'] >= 400) {
            $error = $result['body']['error'] ?? "Impossible de s'inscrire à ce créneau (peut-être déjà inscrit ?).";
        } else {
            header('Location: services.php?inscrit=1');
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['inscrit'])) {
    $success = "Inscription confirmée !";
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
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
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
            <form method="post" action="services.php">
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
