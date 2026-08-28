<?php
session_start();
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/i18n.php';

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
            $success = t('profil_updated_msg');
        } elseif ($form === 'mot_de_passe') {
            $result = apiRequest('PATCH', '/benevoles/mot-de-passe', [
                'old_password' => $_POST['old_password'] ?? '',
                'new_password' => $_POST['new_password'] ?? '',
            ], $_SESSION['token']);

            if ($result['statusCode'] === 200) {
                $success = t('password_updated_msg');
            } else {
                $error = $result['body']['error'] ?? t('current_password_incorrect_msg');
            }
        } elseif ($form === 'competences') {
            apiRequest('PATCH', '/benevoles/capacites', [
                'capacites' => $_POST['capacites'] ?? [],
            ], $_SESSION['token']);
            $success = t('competences_updated_msg');
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

<h2><?= t('mon_profil_heading') ?></h2>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<h3><?= t('mes_infos_heading') ?></h3>
<form method="post">
    <input type="hidden" name="form" value="profil">
    <label><?= t('nom_label') ?> : <input type="text" name="nom" value="<?= htmlspecialchars($moi['nom'] ?? '') ?>" required></label><br>
    <label><?= t('prenom_label') ?> : <input type="text" name="prenom" value="<?= htmlspecialchars($moi['prenom'] ?? '') ?>" required></label><br>
    <label><?= t('telephone_label') ?> : <input type="tel" name="telephone" value="<?= htmlspecialchars($moi['telephone'] ?? '') ?>"></label><br>
    <button type="submit"><?= t('action_save') ?></button>
</form>

<h3><?= t('mes_competences_heading') ?></h3>
<form method="post">
    <input type="hidden" name="form" value="competences">
    <label><?= t('competences_legend') ?> :
        <select name="capacites[]" multiple size="4">
            <?php foreach ($capacitesDisponibles as $c): ?>
                <option value="<?= htmlspecialchars($c['libelle']) ?>" <?= in_array($c['libelle'], $mesCapacites, true) ? 'selected' : '' ?>><?= htmlspecialchars($c['libelle']) ?></option>
            <?php endforeach; ?>
        </select>
        <br><small><?= t('multiselect_hint') ?></small>
    </label><br>
    <button type="submit"><?= t('save_competences_button') ?></button>
</form>

<h3><?= t('change_password_heading') ?></h3>
<form method="post">
    <input type="hidden" name="form" value="mot_de_passe">
    <label><?= t('current_password_label') ?> : <input type="password" name="old_password" required></label><br>
    <label><?= t('new_password_label') ?> : <input type="password" name="new_password" minlength="6" required></label><br>
    <button type="submit"><?= t('change_password_button') ?></button>
</form>

</div>
</body>
</html>
