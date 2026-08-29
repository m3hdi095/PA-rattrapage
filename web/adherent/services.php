<?php
session_start();
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/i18n.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'adherent') {
    header('Location: index.php');
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (($_POST['action'] ?? '') === 'annuler') {
            $result = apiRequest('PATCH', '/inscriptions/annuler', [
                'id' => (int) ($_POST['id'] ?? 0),
            ], $_SESSION['token']);

            if ($result['statusCode'] >= 400) {
                $error = $result['body']['error'] ?? t('desinscription_error_fallback');
            } else {
                header('Location: services.php?desinscrit=1');
                exit;
            }
        } else {
            $result = apiRequest('POST', '/inscriptions', [
                'planning_id' => (int) ($_POST['planning_id'] ?? 0),
            ], $_SESSION['token']);

            if ($result['statusCode'] >= 400) {
                $error = $result['body']['error'] ?? t('inscription_error_fallback');
            } else {
                header('Location: services.php?inscrit=1');
                exit;
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['inscrit'])) {
    $success = t('inscription_confirmee_msg');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['desinscrit'])) {
    $success = t('desinscription_confirmee_msg');
}

$services = [];
$plannings = [];
$mesInscriptions = [];
try {
    $servicesResult = apiRequest('GET', '/services', null, $_SESSION['token']);
    $services = $servicesResult['body'] ?? [];

    $planningsResult = apiRequest('GET', '/plannings', null, $_SESSION['token']);
    $plannings = $planningsResult['body'] ?? [];

    $mesInscriptionsResult = apiRequest('GET', '/inscriptions/mes', null, $_SESSION['token']);
    $mesInscriptions = $mesInscriptionsResult['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$serviceNoms = [];
foreach ($services as $s) {
    $serviceNoms[$s['id']] = $s['nom'];
}

$planningIdsInscrits = [];
foreach ($mesInscriptions as $mi) {
    $planningIdsInscrits[$mi['planning_id']] = true;
}

require __DIR__ . '/../includes/header_adherent.php';
?>

<h2><?= t('services_proposes_heading') ?></h2>

<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<h3><?= t('mes_inscriptions_heading') ?></h3>
<?php if (empty($mesInscriptions)): ?>
    <p><?= t('aucune_inscription_msg') ?></p>
<?php else: ?>
    <table border="1" cellpadding="6">
        <tr>
            <th><?= t('service_column') ?></th>
            <th><?= t('debut_column') ?></th>
            <th><?= t('fin_column') ?></th>
            <th><?= t('lieu_column') ?></th>
            <th><?= t('action_column') ?></th>
        </tr>
        <?php foreach ($mesInscriptions as $mi): ?>
        <tr>
            <td><?= htmlspecialchars($serviceNoms[$mi['service_id']] ?? ('Service #' . $mi['service_id'])) ?></td>
            <td><?= htmlspecialchars($mi['date_debut']) ?></td>
            <td><?= htmlspecialchars($mi['date_fin']) ?></td>
            <td><?= htmlspecialchars($mi['lieu']) ?></td>
            <td>
                <form method="post" action="services.php" onsubmit="return confirm(<?= json_encode(t('confirm_desinscription')) ?>);">
                    <input type="hidden" name="action" value="annuler">
                    <input type="hidden" name="id" value="<?= (int) $mi['id'] ?>">
                    <button type="submit"><?= t('unsubscribe_button') ?></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<h3><?= t('nos_services_heading') ?></h3>
<ul>
    <?php foreach ($services as $s): ?>
        <li><strong><?= htmlspecialchars($s['nom']) ?></strong> — <?= htmlspecialchars($s['description']) ?></li>
    <?php endforeach; ?>
</ul>

<h3><?= t('creneaux_dispo_heading') ?></h3>
<table border="1" cellpadding="6">
    <tr>
        <th><?= t('service_column') ?></th>
        <th><?= t('debut_column') ?></th>
        <th><?= t('fin_column') ?></th>
        <th><?= t('lieu_column') ?></th>
        <th><?= t('places_restantes_column') ?></th>
        <th><?= t('action_column') ?></th>
    </tr>
    <?php foreach ($plannings as $p): ?>
        <?php if ($p['date_debut'] < date('Y-m-d H:i:s')) continue; ?>
    <tr>
        <td><?= htmlspecialchars($serviceNoms[$p['service_id']] ?? ('Service #' . $p['service_id'])) ?></td>
        <td><?= htmlspecialchars($p['date_debut']) ?></td>
        <td><?= htmlspecialchars($p['date_fin']) ?></td>
        <td><?= htmlspecialchars($p['lieu']) ?></td>
        <td><?= (int) $p['places_restantes'] ?> / <?= (int) $p['places_max'] ?></td>
        <td>
            <?php if (isset($planningIdsInscrits[$p['id']])): ?>
                <em><?= t('deja_inscrit_label') ?></em>
            <?php elseif ($p['places_restantes'] <= 0): ?>
                <em><?= t('complet_label') ?></em>
            <?php else: ?>
                <form method="post" action="services.php">
                    <input type="hidden" name="planning_id" value="<?= (int) $p['id'] ?>">
                    <button type="submit"><?= t('signup_button') ?></button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
