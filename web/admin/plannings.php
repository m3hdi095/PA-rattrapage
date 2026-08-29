<?php
session_start();
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/i18n.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'supprimer') {
            $result = apiRequest('DELETE', '/plannings', ['id' => (int) ($_POST['id'] ?? 0)], $_SESSION['token']);
        } elseif ($action === 'modifier') {
            $result = apiRequest('PATCH', '/plannings', [
                'id'          => (int) ($_POST['id'] ?? 0),
                'service_id'  => (int) ($_POST['service_id'] ?? 0),
                'benevole_id' => (int) ($_POST['benevole_id'] ?? 0),
                'date_debut'  => $_POST['date_debut'] ?? '',
                'date_fin'    => $_POST['date_fin'] ?? '',
                'lieu'        => $_POST['lieu'] ?? '',
                'places_max'  => (int) ($_POST['places_max'] ?? 1),
            ], $_SESSION['token']);
        } else {
            $result = apiRequest('POST', '/plannings', [
                'service_id'  => (int) ($_POST['service_id'] ?? 0),
                'benevole_id' => (int) ($_POST['benevole_id'] ?? 0),
                'date_debut'  => $_POST['date_debut'] ?? '',
                'date_fin'    => $_POST['date_fin'] ?? '',
                'lieu'        => $_POST['lieu'] ?? '',
                'places_max'  => (int) ($_POST['places_max'] ?? 1),
            ], $_SESSION['token']);
        }
        if ($result['statusCode'] >= 400) {
            $error = $result['body']['error'] ?? t('planning_create_error');
        } else {
            header('Location: plannings.php');
            exit;
        }
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

$benevoles = [];
try {
    $result = apiRequest('GET', '/benevoles', null, $_SESSION['token']);
    $benevoles = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$servicesById = [];
foreach ($services as $s) {
    $servicesById[$s['id']] = $s['nom'];
}

$benevolesById = [];
foreach ($benevoles as $b) {
    $benevolesById[$b['id']] = $b['nom'] . ' ' . $b['prenom'];
}

$serviceCapaciteId = [];
foreach ($services as $s) {
    $serviceCapaciteId[$s['id']] = $s['capacite_id'] ?? null;
}

$benevoleCapaciteIds = [];
foreach ($benevoles as $b) {
    $benevoleCapaciteIds[$b['id']] = array_map(fn($c) => $c['id'], $b['capacites'] ?? []);
}

$editPlanning = null;
if (isset($_GET['edit'])) {
    foreach ($plannings as $p) {
        if ($p['id'] === (int) $_GET['edit']) {
            $editPlanning = $p;
            break;
        }
    }
}

require __DIR__ . '/../includes/header_admin.php';
?>

<h2><?= t('plannings_page_heading') ?></h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3 id="creneau_form"><?= $editPlanning ? t('edit_creneau_heading') : t('create_creneau_heading') ?></h3>
<form method="post">
    <input type="hidden" name="action" value="<?= $editPlanning ? 'modifier' : 'creer' ?>">
    <?php if ($editPlanning): ?>
        <input type="hidden" name="id" value="<?= (int) $editPlanning['id'] ?>">
    <?php endif; ?>
    <label><?= t('service_label') ?> :
        <select name="service_id" id="service_id" required>
            <option value=""><?= t('choose_placeholder') ?></option>
            <?php foreach ($services as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= ($editPlanning && $editPlanning['service_id'] === $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['nom']) ?><?= (($s['capacite_libelle'] ?? '') !== '') ? ' (' . htmlspecialchars($s['capacite_libelle']) . ')' : '' ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label><?= t('benevole_affecte_label') ?> :
        <select name="benevole_id" id="benevole_id" required>
            <option value=""><?= t('choose_placeholder') ?></option>
            <?php foreach ($benevoles as $b): ?>
                <option value="<?= (int) $b['id'] ?>" <?= ($editPlanning && $editPlanning['benevole_id'] === $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['nom'] . ' ' . $b['prenom']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <p id="competence_warning" class="error" style="display:none;"><?= t('competence_warning_text') ?></p>
    <br>
    <label><?= t('date_debut_label') ?> : <input type="datetime-local" name="date_debut" value="<?= $editPlanning ? htmlspecialchars(str_replace(' ', 'T', substr($editPlanning['date_debut'], 0, 16))) : '' ?>" min="<?= date('Y-m-d\TH:i') ?>" required></label><br>
    <label><?= t('date_fin_label') ?> : <input type="datetime-local" name="date_fin" value="<?= $editPlanning ? htmlspecialchars(str_replace(' ', 'T', substr($editPlanning['date_fin'], 0, 16))) : '' ?>" min="<?= date('Y-m-d\TH:i') ?>" required></label><br>
    <label><?= t('lieu_label') ?> : <input type="text" name="lieu" value="<?= $editPlanning ? htmlspecialchars($editPlanning['lieu']) : '' ?>"></label><br>
    <label><?= t('places_max_label') ?> : <input type="number" name="places_max" value="<?= $editPlanning ? (int) $editPlanning['places_max'] : 1 ?>" min="1" required></label><br>
    <button type="submit"><?= $editPlanning ? t('action_save') : t('action_create') ?></button>
    <?php if ($editPlanning): ?>
        <a href="plannings.php"><?= t('cancel_edit_link') ?></a>
    <?php endif; ?>
</form>

<script>
    const SERVICE_CAPACITE_ID = <?= json_encode($serviceCapaciteId) ?>;
    const BENEVOLE_CAPACITE_IDS = <?= json_encode($benevoleCapaciteIds) ?>;

    const serviceSelect = document.getElementById('service_id');
    const benevoleSelect = document.getElementById('benevole_id');
    const competenceWarning = document.getElementById('competence_warning');
    const benevoleOptions = Array.from(benevoleSelect.options);

    function filtrerBenevoles() {
        const capaciteRequise = SERVICE_CAPACITE_ID[serviceSelect.value] ?? null;
        let visibleCount = 0;

        benevoleOptions.forEach(option => {
            if (option.value === '') {
                return;
            }
            const capacites = BENEVOLE_CAPACITE_IDS[option.value] || [];
            const correspond = capaciteRequise === null || capacites.includes(capaciteRequise);
            option.hidden = !correspond;
            option.disabled = !correspond;
            if (correspond) {
                visibleCount++;
            }
        });

        if (benevoleSelect.selectedOptions[0]?.hidden) {
            benevoleSelect.value = '';
        }

        competenceWarning.style.display = (capaciteRequise !== null && visibleCount === 0) ? 'block' : 'none';
    }

    serviceSelect.addEventListener('change', filtrerBenevoles);
    filtrerBenevoles();
</script>

<h3><?= t('list_heading') ?></h3>
<table border="1" cellpadding="6">
    <tr>
        <th><?= t('id_column') ?></th>
        <th><?= t('service_column') ?></th>
        <th><?= t('benevole_column') ?></th>
        <th><?= t('debut_column') ?></th>
        <th><?= t('fin_column') ?></th>
        <th><?= t('lieu_column') ?></th>
        <th><?= t('places_max_column') ?></th>
        <th><?= t('actions_column') ?></th>
    </tr>
    <?php foreach ($plannings as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['id']) ?></td>
        <td><?= htmlspecialchars($servicesById[$p['service_id']] ?? ('service #' . $p['service_id'])) ?></td>
        <td><?= htmlspecialchars($benevolesById[$p['benevole_id']] ?? ('bénévole #' . $p['benevole_id'])) ?></td>
        <td><?= htmlspecialchars($p['date_debut']) ?></td>
        <td><?= htmlspecialchars($p['date_fin']) ?></td>
        <td><?= htmlspecialchars($p['lieu']) ?></td>
        <td><?= htmlspecialchars($p['places_max']) ?></td>
        <td>
            <a href="plannings.php?edit=<?= (int) $p['id'] ?>#creneau_form"><?= t('action_edit') ?></a>
            <form method="post" style="display:inline;" onsubmit="return confirm(<?= json_encode(t('confirm_delete_planning')) ?>);">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <button type="submit"><?= t('action_delete') ?></button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
