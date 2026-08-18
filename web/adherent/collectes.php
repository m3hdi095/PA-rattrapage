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
        apiRequest('POST', '/collectes', [
            'date_collecte' => $_POST['date_collecte'] ?? '',
        ], $_SESSION['token']);
        header('Location: collectes.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$collectes = [];
try {
    $result = apiRequest('GET', '/collectes', null, $_SESSION['token']);
    $collectes = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

require __DIR__ . '/../includes/header_adherent.php';
?>

<h2>Mes demandes de collecte</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3>Demander une collecte</h3>
<form method="post">
    <label>Date/heure souhaitée : <input type="datetime-local" name="date_collecte" required></label><br>
    <button type="submit">Demander</button>
</form>

<h3>Mes collectes</h3>
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Date</th>
        <th>Statut</th>
    </tr>
    <?php foreach ($collectes as $c): ?>
    <tr>
        <td><?= htmlspecialchars($c['id']) ?></td>
        <td><?= htmlspecialchars($c['date_collecte']) ?></td>
        <td><?= htmlspecialchars($c['statut']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
