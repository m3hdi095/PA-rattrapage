<?php
session_start();
require_once __DIR__ . '/../includes/api.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'adherent') {
    header('Location: index.php');
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST['form'] ?? '';

    try {
        if ($form === 'profil') {
            apiRequest('PATCH', '/adherents/profil', [
                'nom'         => $_POST['nom'] ?? '',
                'adresse'     => $_POST['adresse'] ?? '',
                'code_postal' => $_POST['code_postal'] ?? '',
                'ville'       => $_POST['ville'] ?? '',
                'telephone'   => $_POST['telephone'] ?? '',
            ], $_SESSION['token']);
            $success = "Profil mis à jour.";
        } elseif ($form === 'mot_de_passe') {
            $result = apiRequest('PATCH', '/adherents/mot-de-passe', [
                'old_password' => $_POST['old_password'] ?? '',
                'new_password' => $_POST['new_password'] ?? '',
            ], $_SESSION['token']);

            if ($result['statusCode'] === 200) {
                $success = "Mot de passe mis à jour.";
            } else {
                $error = "Mot de passe actuel incorrect.";
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$moi = [];
try {
    $result = apiRequest('GET', '/adherents/me', null, $_SESSION['token']);
    $moi = $result['body'] ?? [];
} catch (Exception $e) {
}

require __DIR__ . '/../includes/header_adherent.php';
?>

<h2>Mon profil</h2>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<h3>Informations du commerce</h3>
<form method="post">
    <input type="hidden" name="form" value="profil">
    <label>Nom du commerce : <input type="text" name="nom" value="<?= htmlspecialchars($moi['nom'] ?? '') ?>" required></label><br>
    <label>Adresse : <input type="text" name="adresse" value="<?= htmlspecialchars($moi['adresse'] ?? '') ?>" required></label><br>
    <label>Code postal : <input type="text" name="code_postal" value="<?= htmlspecialchars($moi['code_postal'] ?? '') ?>" required></label><br>
    <label>Ville : <input type="text" name="ville" value="<?= htmlspecialchars($moi['ville'] ?? '') ?>" required></label><br>
    <label>Téléphone : <input type="tel" name="telephone" value="<?= htmlspecialchars($moi['telephone'] ?? '') ?>"></label><br>
    <button type="submit">Enregistrer</button>
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
