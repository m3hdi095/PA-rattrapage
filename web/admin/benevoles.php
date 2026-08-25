<?php
session_start();
require_once __DIR__ . '/../includes/api.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'valider') {
            apiRequest('PATCH', '/benevoles/valider', ['id' => $id], $_SESSION['token']);
        } elseif ($action === 'rejeter') {
            apiRequest('PATCH', '/benevoles/rejeter', ['id' => $id], $_SESSION['token']);
        } elseif ($action === 'supprimer') {
            apiRequest('DELETE', '/benevoles', ['id' => $id], $_SESSION['token']);
        } elseif ($action === 'creer') {
            $result = apiRequest('POST', '/benevoles', [
                'email'     => $_POST['email'] ?? '',
                'password'  => $_POST['password'] ?? '',
                'nom'       => $_POST['nom'] ?? '',
                'prenom'    => $_POST['prenom'] ?? '',
                'telephone' => $_POST['telephone'] ?? '',
                'capacites' => $_POST['capacites'] ?? [],
            ], $_SESSION['token']);
            if ($result['statusCode'] >= 400) {
                $error = $result['body']['error'] ?? "Impossible de créer ce bénévole.";
            } else {
                apiRequest('PATCH', '/benevoles/valider', ['id' => $result['body']['id']], $_SESSION['token']);
            }
        }
        if (!$error) {
            header('Location: benevoles.php');
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$benevoles = [];
try {
    $result = apiRequest('GET', '/benevoles', null, $_SESSION['token']);
    $benevoles = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$capacitesDisponibles = [];
try {
    $result = apiRequest('GET', '/capacites');
    $capacitesDisponibles = $result['body'] ?? [];
} catch (Exception $e) {
}

require __DIR__ . '/../includes/header_admin.php';
?>

<h2>Gestion des bénévoles</h2>

<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3>Créer un bénévole</h3>
<form method="post">
    <input type="hidden" name="action" value="creer">
    <label>Email : <input type="email" name="email" required></label><br>
    <label>Mot de passe : <input type="password" name="password" required></label><br>
    <label>Nom : <input type="text" name="nom" required></label><br>
    <label>Prénom : <input type="text" name="prenom" required></label><br>
    <label>Téléphone : <input type="tel" name="telephone"></label><br>
    <label>Compétences :
        <select name="capacites[]" multiple size="4">
            <?php foreach ($capacitesDisponibles as $c): ?>
                <option value="<?= htmlspecialchars($c['libelle']) ?>"><?= htmlspecialchars($c['libelle']) ?></option>
            <?php endforeach; ?>
        </select>
        <br><small>Ctrl+clic pour sélectionner plusieurs compétences.</small>
    </label><br>
    <button type="submit">Créer (validé automatiquement)</button>
</form>

<h3>Liste</h3>
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Téléphone</th>
        <th>Statut</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($benevoles as $b): ?>
    <tr>
        <td><?= htmlspecialchars($b['id']) ?></td>
        <td><?= htmlspecialchars($b['email']) ?></td>
        <td><?= htmlspecialchars($b['nom']) ?></td>
        <td><?= htmlspecialchars($b['prenom']) ?></td>
        <td><?= htmlspecialchars($b['telephone']) ?></td>
        <td><?= htmlspecialchars($b['statut_candidature']) ?></td>
        <td>
            <?php if ($b['statut_candidature'] === 'en_attente'): ?>
            <form method="post" style="display:inline;">
                <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                <input type="hidden" name="action" value="valider">
                <button type="submit">Valider</button>
            </form>
            <form method="post" style="display:inline;">
                <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                <input type="hidden" name="action" value="rejeter">
                <button type="submit">Rejeter</button>
            </form>
            <?php endif; ?>
            <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce bénévole ?');">
                <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                <input type="hidden" name="action" value="supprimer">
                <button type="submit">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
