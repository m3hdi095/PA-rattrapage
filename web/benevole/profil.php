<?php
session_start();
require_once __DIR__ . '/../includes/api.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'benevole') {
    header('Location: index.php');
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST['form'] ?? '';

    try {
        if ($form === 'profil') {
            apiRequest('PATCH', '/benevoles/profil', [
                'nom'       => $_POST['nom'] ?? '',
                'prenom'    => $_POST['prenom'] ?? '',
                'telephone' => $_POST['telephone'] ?? '',
            ], $_SESSION['token']);
            $success = "Profil mis à jour.";
        } elseif ($form === 'mot_de_passe') {
            $result = apiRequest('PATCH', '/benevoles/mot-de-passe', [
                'old_password' => $_POST['old_password'] ?? '',
                'new_password' => $_POST['new_password'] ?? '',
            ], $_SESSION['token']);

            if ($result['statusCode'] === 200) {
                $success = "Mot de passe mis à jour.";
            } else {
                $error = "Mot de passe actuel incorrect.";
            }
        } elseif ($form === 'competences') {
            apiRequest('PATCH', '/benevoles/capacites', [
                'capacites' => $_POST['capacites'] ?? [],
            ], $_SESSION['token']);
            $success = "Compétences mises à jour.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$capacitesDisponibles = [];
try {
    $result = apiRequest('GET', '/capacites');
    $capacitesDisponibles = $result['body'] ?? [];
} catch (Exception $e) {
}

$moi = [];
try {
    $result = apiRequest('GET', '/benevoles/me', null, $_SESSION['token']);
    $moi = $result['body'] ?? [];
} catch (Exception $e) {
}
$mesCapacites = array_map(fn($c) => $c['libelle'], $moi['capacites'] ?? []);

require __DIR__ . '/../includes/header_benevole.php';
?>

<h2>Mon profil</h2>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<h3>Mes informations</h3>
<form method="post">
    <input type="hidden" name="form" value="profil">
    <label>Nom : <input type="text" name="nom" value="<?= htmlspecialchars($moi['nom'] ?? '') ?>" required></label><br>
    <label>Prénom : <input type="text" name="prenom" value="<?= htmlspecialchars($moi['prenom'] ?? '') ?>" required></label><br>
    <label>Téléphone : <input type="tel" name="telephone" value="<?= htmlspecialchars($moi['telephone'] ?? '') ?>"></label><br>
    <button type="submit">Enregistrer</button>
</form>

<h3>Mes compétences</h3>
<form method="post">
    <input type="hidden" name="form" value="competences">
    <label>Compétences :
        <select name="capacites[]" multiple size="4">
            <?php foreach ($capacitesDisponibles as $c): ?>
                <option value="<?= htmlspecialchars($c['libelle']) ?>" <?= in_array($c['libelle'], $mesCapacites, true) ? 'selected' : '' ?>><?= htmlspecialchars($c['libelle']) ?></option>
            <?php endforeach; ?>
        </select>
        <br><small>Ctrl+clic pour sélectionner plusieurs compétences.</small>
    </label><br>
    <button type="submit">Enregistrer mes compétences</button>
</form>

<h3>Changer de mot de passe</h3>
<form method="post">
    <input type="hidden" name="form" value="mot_de_passe">
    <label>Mot de passe actuel : <input type="password" name="old_password" required></label><br>
    <label>Nouveau mot de passe : <input type="password" name="new_password" required></label><br>
    <button type="submit">Changer le mot de passe</button>
</form>

</div>
</body>
</html>
