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
        if (($_POST['action'] ?? '') === 'creer') {
            $result = apiRequest('POST', '/adherents', [
                'email'       => $_POST['email'] ?? '',
                'password'    => $_POST['password'] ?? '',
                'nom'         => $_POST['nom'] ?? '',
                'siret'       => $_POST['siret'] ?? '',
                'adresse'     => $_POST['adresse'] ?? '',
                'code_postal' => $_POST['code_postal'] ?? '',
                'ville'       => $_POST['ville'] ?? '',
                'telephone'   => $_POST['telephone'] ?? '',
            ], $_SESSION['token']);
            if ($result['statusCode'] >= 400) {
                $error = $result['body']['error'] ?? "Impossible de créer cet adhérent.";
            }
        } else {
            apiRequest('DELETE', '/adherents', ['id' => (int) ($_POST['id'] ?? 0)], $_SESSION['token']);
        }
        if (!$error) {
            header('Location: adherents.php');
            exit;
        }
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
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3>Créer un adhérent</h3>
<form method="post">
    <input type="hidden" name="action" value="creer">
    <label>Email : <input type="email" name="email" required></label><br>
    <label>Mot de passe : <input type="password" name="password" required></label><br>
    <label>Nom du commerce : <input type="text" name="nom" required></label><br>
    <label>SIRET : <input type="text" name="siret" required></label><br>
    <label>Adresse : <input type="text" name="adresse"></label><br>
    <label>Code postal : <input type="text" name="code_postal"></label><br>
    <label>Ville : <input type="text" name="ville"></label><br>
    <label>Téléphone : <input type="tel" name="telephone"></label><br>
    <button type="submit">Créer</button>
</form>

<h3>Liste</h3>
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
