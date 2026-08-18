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
        apiRequest('POST', '/plannings', [
            'service_id'  => (int) ($_POST['service_id'] ?? 0),
            'benevole_id' => (int) ($_POST['benevole_id'] ?? 0),
            'date_debut'  => $_POST['date_debut'] ?? '',
            'date_fin'    => $_POST['date_fin'] ?? '',
            'lieu'        => $_POST['lieu'] ?? '',
            'places_max'  => (int) ($_POST['places_max'] ?? 1),
        ], $_SESSION['token']);
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

require __DIR__ . '/../includes/header_admin.php';
?>

<h2>Plannings des services</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3>Créer un créneau</h3>
<form method="post">
    <label>ID du service : <input type="number" name="service_id" required></label><br>
    <label>ID du bénévole affecté : <input type="number" name="benevole_id" required></label><br>
    <label>Date/heure début : <input type="datetime-local" name="date_debut" required></label><br>
    <label>Date/heure fin : <input type="datetime-local" name="date_fin" required></label><br>
    <label>Lieu : <input type="text" name="lieu"></label><br>
    <label>Places max : <input type="number" name="places_max" value="1" required></label><br>
    <button type="submit">Créer</button>
</form>

<h3>Liste</h3>
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Service (id)</th>
        <th>Bénévole (id)</th>
        <th>Début</th>
        <th>Fin</th>
        <th>Lieu</th>
        <th>Places max</th>
    </tr>
    <?php foreach ($plannings as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['id']) ?></td>
        <td><?= htmlspecialchars($p['service_id']) ?></td>
        <td><?= htmlspecialchars($p['benevole_id']) ?></td>
        <td><?= htmlspecialchars($p['date_debut']) ?></td>
        <td><?= htmlspecialchars($p['date_fin']) ?></td>
        <td><?= htmlspecialchars($p['lieu']) ?></td>
        <td><?= htmlspecialchars($p['places_max']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
