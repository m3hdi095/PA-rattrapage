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
            apiRequest('DELETE', '/plannings', ['id' => (int) ($_POST['id'] ?? 0)], $_SESSION['token']);
        } else {
            apiRequest('POST', '/plannings', [
                'service_id'  => (int) ($_POST['service_id'] ?? 0),
                'benevole_id' => (int) ($_POST['benevole_id'] ?? 0),
                'date_debut'  => $_POST['date_debut'] ?? '',
                'date_fin'    => $_POST['date_fin'] ?? '',
                'lieu'        => $_POST['lieu'] ?? '',
                'places_max'  => (int) ($_POST['places_max'] ?? 1),
            ], $_SESSION['token']);
        }
        header('Location: plannings.php');
        exit;
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

// Pour le filtrage JS du select "Bénévole affecté" selon la compétence
// requise par le service choisi.
$serviceCapaciteId = [];
foreach ($services as $s) {
    $serviceCapaciteId[$s['id']] = $s['capacite_id'] ?? null;
}

$benevoleCapaciteIds = [];
foreach ($benevoles as $b) {
    $benevoleCapaciteIds[$b['id']] = array_map(fn($c) => $c['id'], $b['capacites'] ?? []);
}

require __DIR__ . '/../includes/header_admin.php';
?>

<h2>Plannings des services</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3>Créer un créneau</h3>
<form method="post">
    <label>Service :
        <select name="service_id" id="service_id" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($services as $s): ?>
                <option value="<?= (int) $s['id'] ?>"><?= htmlspecialchars($s['nom']) ?><?= (($s['capacite_libelle'] ?? '') !== '') ? ' (' . htmlspecialchars($s['capacite_libelle']) . ')' : '' ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label>Bénévole affecté :
        <select name="benevole_id" id="benevole_id" required>
            <option value="">-- Choisir --</option>
            <?php foreach ($benevoles as $b): ?>
                <option value="<?= (int) $b['id'] ?>"><?= htmlspecialchars($b['nom'] . ' ' . $b['prenom']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <p id="competence_warning" class="error" style="display:none;">Aucun bénévole ne possède la compétence requise par ce service.</p>
    <br>
    <label>Date/heure début : <input type="datetime-local" name="date_debut" required></label><br>
    <label>Date/heure fin : <input type="datetime-local" name="date_fin" required></label><br>
    <label>Lieu : <input type="text" name="lieu"></label><br>
    <label>Places max : <input type="number" name="places_max" value="1" required></label><br>
    <button type="submit">Créer</button>
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

<h3>Liste</h3>
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Service</th>
        <th>Bénévole</th>
        <th>Début</th>
        <th>Fin</th>
        <th>Lieu</th>
        <th>Places max</th>
        <th>Actions</th>
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
            <form method="post" onsubmit="return confirm('Supprimer ce planning ?');">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <button type="submit">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
