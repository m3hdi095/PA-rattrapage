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
        apiRequest('POST', '/destinataires', [
            'type'        => $_POST['type'] ?? '',
            'nom'         => $_POST['nom'] ?? '',
            'adresse'     => $_POST['adresse'] ?? '',
            'code_postal' => $_POST['code_postal'] ?? '',
            'ville'       => $_POST['ville'] ?? '',
            'telephone'   => $_POST['telephone'] ?? '',
        ], $_SESSION['token']);
        header('Location: destinataires.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$destinataires = [];
try {
    $result = apiRequest('GET', '/destinataires', null, $_SESSION['token']);
    $destinataires = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

require __DIR__ . '/../includes/header_admin.php';
?>

<h2>Destinataires (associations / particuliers en détresse)</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3>Ajouter un destinataire</h3>
<form method="post">
    <label>Type :
        <select name="type" required>
            <option value="association">Association</option>
            <option value="particulier">Particulier</option>
        </select>
    </label><br>
    <label>Nom : <input type="text" name="nom" required></label><br>
    <label>Adresse : <input type="text" name="adresse" required></label><br>
    <label>Code postal : <input type="text" name="code_postal" required></label><br>
    <label>Ville : <input type="text" name="ville" required></label><br>
    <label>Téléphone : <input type="tel" name="telephone"></label><br>
    <button type="submit">Ajouter</button>
</form>

<h3>Liste</h3>
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Type</th>
        <th>Nom</th>
        <th>Adresse</th>
        <th>Code postal</th>
        <th>Ville</th>
        <th>Téléphone</th>
    </tr>
    <?php foreach ($destinataires as $d): ?>
    <tr>
        <td><?= htmlspecialchars($d['id']) ?></td>
        <td><?= htmlspecialchars($d['type']) ?></td>
        <td><?= htmlspecialchars($d['nom']) ?></td>
        <td><?= htmlspecialchars($d['adresse']) ?></td>
        <td><?= htmlspecialchars($d['code_postal']) ?></td>
        <td><?= htmlspecialchars($d['ville']) ?></td>
        <td><?= htmlspecialchars($d['telephone']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
