<?php
session_start();
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/i18n.php';

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

<h2><?= t('mon_planning_heading') ?></h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<p><a href="download_planning.php"><?= t('download_excel_link') ?></a></p>

<h3><?= t('tous_creneaux_heading') ?></h3>
<table border="1" cellpadding="6">
    <tr>
        <th><?= t('id_column') ?></th>
        <th><?= t('service_column') ?></th>
        <th><?= t('benevole_column') ?></th>
        <th><?= t('debut_column') ?></th>
        <th><?= t('fin_column') ?></th>
        <th><?= t('lieu_column') ?></th>
    </tr>
    <?php foreach ($plannings as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['id']) ?></td>
        <td><?= htmlspecialchars($servicesById[$p['service_id']] ?? ('service #' . $p['service_id'])) ?></td>
        <td><?= $p['benevole_id'] === $ownBenevoleId ? t('moi_marker') : htmlspecialchars('bénévole #' . $p['benevole_id']) ?></td>
        <td><?= htmlspecialchars($p['date_debut']) ?></td>
        <td><?= htmlspecialchars($p['date_fin']) ?></td>
        <td><?= htmlspecialchars($p['lieu']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
