<?php
session_start();
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/i18n.php';

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

<h2><?= t('mes_collectes_page_heading') ?></h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3><?= t('demander_collecte_heading') ?></h3>
<form method="post">
    <label><?= t('date_collecte_souhaitee_label') ?> : <input type="datetime-local" name="date_collecte" required></label><br>
    <button type="submit"><?= t('demander_button') ?></button>
</form>

<h3><?= t('mes_collectes_list_heading') ?></h3>
<table border="1" cellpadding="6">
    <tr>
        <th><?= t('id_column') ?></th>
        <th><?= t('date_label') ?></th>
        <th><?= t('statut_column') ?></th>
    </tr>
    <?php foreach ($collectes as $c): ?>
    <tr>
        <td><?= htmlspecialchars($c['id']) ?></td>
        <td><?= htmlspecialchars($c['date_collecte']) ?></td>
        <td><?= htmlspecialchars($c['statut']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</div>
</body>
</html>
