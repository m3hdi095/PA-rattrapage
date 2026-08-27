<?php
session_start();
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/i18n.php';

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

<h2><?= t('inscriptions_page_heading') ?></h2>

<form method="get">
    <label><?= t('planning_label') ?> :
        <select name="planning_id" required>
            <option value=""><?= t('choose_placeholder') ?></option>
            <?php foreach ($plannings as $p): ?>
                <option value="<?= (int) $p['id'] ?>" <?= $p['id'] === $planningId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($servicesById[$p['service_id']] ?? ('service #' . $p['service_id'])) ?> — <?= htmlspecialchars($p['date_debut']) ?> (<?= htmlspecialchars($p['lieu']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit"><?= t('view_inscriptions_button') ?></button>
</form>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($planningId !== null && !$error): ?>
<table border="1" cellpadding="6">
    <tr>
        <th><?= t('id_column') ?></th>
        <th><?= t('adherent_column') ?></th>
        <th><?= t('statut_column') ?></th>
    </tr>
    <?php foreach ($inscriptions as $i): ?>
    <tr>
        <td><?= htmlspecialchars($i['id']) ?></td>
        <td><?= htmlspecialchars($adherentsById[$i['adherent_id']] ?? ('adhérent #' . $i['adherent_id'])) ?></td>
        <td><?= htmlspecialchars($i['statut']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

</div>
</body>
</html>
