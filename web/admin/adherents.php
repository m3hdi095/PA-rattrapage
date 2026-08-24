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
        apiRequest('DELETE', '/adherents', ['id' => (int) ($_POST['id'] ?? 0)], $_SESSION['token']);
        header('Location: adherents.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$adherents = [];
try {
    $result = apiRequest('GET', '/adherents', null, $_SESSION['token']);
    $adherents = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

require __DIR__ . '/../includes/header_admin.php';
?>

<h2>Adhérents (commerçants)</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Nom</th>
        <th>SIRET</th>
        <th>Adresse</th>
        <th>Code postal</th>
        <th>Ville</th>
        <th>Téléphone</th>
        <th>Adhésion</th>
        <th>Expiration</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($adherents as $a): ?>
    <tr>
        <td><?= htmlspecialchars($a['id']) ?></td>
        <td><?= htmlspecialchars($a['email']) ?></td>
        <td><?= htmlspecialchars($a['nom']) ?></td>
        <td><?= htmlspecialchars($a['siret']) ?></td>
        <td><?= htmlspecialchars($a['adresse']) ?></td>
        <td><?= htmlspecialchars($a['code_postal']) ?></td>
        <td><?= htmlspecialchars($a['ville']) ?></td>
        <td><?= htmlspecialchars($a['telephone']) ?></td>
        <td><?= htmlspecialchars($a['date_adhesion']) ?></td>
        <td><?= htmlspecialchars($a['date_expiration']) ?></td>
        <td>
            <form method="post" onsubmit="return confirm('Supprimer cet adhérent ?');">
                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                <button type="submit">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
