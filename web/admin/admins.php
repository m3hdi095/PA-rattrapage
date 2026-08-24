<?php
session_start();
require_once __DIR__ . '/../includes/api.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
    header('Location: benevoles.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (($_POST['action'] ?? '') === 'supprimer') {
            $result = apiRequest('DELETE', '/admins', ['id' => (int) ($_POST['id'] ?? 0)], $_SESSION['token']);
            if ($result['statusCode'] >= 400) {
                $error = $result['body']['error'] ?? "Impossible de supprimer cet admin.";
            }
        } else {
            $result = apiRequest('POST', '/admins', [
                'email'    => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'nom'      => $_POST['nom'] ?? '',
                'prenom'   => $_POST['prenom'] ?? '',
                'role'     => $_POST['role'] ?? 'admin',
            ], $_SESSION['token']);
            if ($result['statusCode'] >= 400) {
                $error = $result['body']['error'] ?? "Impossible de créer cet admin.";
            }
        }
        if (!$error) {
            header('Location: admins.php');
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$admins = [];
try {
    $result = apiRequest('GET', '/admins', null, $_SESSION['token']);
    $admins = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$ownId = jwtClaims($_SESSION['token'])['id'] ?? null;

require __DIR__ . '/../includes/header_admin.php';
?>

<h2>Comptes admin</h2>

<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3>Créer un admin</h3>
<form method="post">
    <label>Email : <input type="email" name="email" required></label><br>
    <label>Mot de passe : <input type="password" name="password" required></label><br>
    <label>Nom : <input type="text" name="nom" required></label><br>
    <label>Prénom : <input type="text" name="prenom" required></label><br>
    <label>Rôle :
        <select name="role">
            <option value="admin">admin</option>
            <option value="super_admin">super_admin</option>
        </select>
    </label><br>
    <button type="submit">Créer</button>
</form>

<h3>Liste</h3>
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Rôle</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($admins as $a): ?>
    <tr>
        <td><?= htmlspecialchars($a['id']) ?></td>
        <td><?= htmlspecialchars($a['email']) ?></td>
        <td><?= htmlspecialchars($a['nom']) ?></td>
        <td><?= htmlspecialchars($a['prenom']) ?></td>
        <td><?= htmlspecialchars($a['role']) ?></td>
        <td>
            <?php if ($a['id'] === $ownId): ?>
                <em>(vous)</em>
            <?php else: ?>
                <form method="post" onsubmit="return confirm('Supprimer ce compte admin ?');">
                    <input type="hidden" name="action" value="supprimer">
                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                    <button type="submit">Supprimer</button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
