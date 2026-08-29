<?php
session_start();
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/i18n.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (($_POST['action'] ?? '') === 'update_statut') {
            $result = apiRequest('PATCH', '/produits/statut', [
                'id'     => (int) $_POST['id'],
                'statut' => $_POST['statut'],
            ], $_SESSION['token']);
        } elseif (($_POST['action'] ?? '') === 'supprimer') {
            $result = apiRequest('DELETE', '/produits', ['id' => (int) $_POST['id']], $_SESSION['token']);
        } else {
            $result = apiRequest('POST', '/produits', [
                'collecte_id'       => (int) ($_POST['collecte_id'] ?? 0),
                'nom'               => $_POST['nom'] ?? '',
                'quantite'          => (int) ($_POST['quantite'] ?? 1),
                'date_limite_conso' => $_POST['date_limite_conso'] ?? '',
                'emplacement_stock' => $_POST['emplacement_stock'] ?? '',
            ], $_SESSION['token']);
        }
        if ($result['statusCode'] >= 400) {
            $error = $result['body']['error'] ?? t('produit_action_error');
        } else {
            header('Location: produits.php');
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$produits = [];
try {
    $result = apiRequest('GET', '/produits', null, $_SESSION['token']);
    $produits = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$collectes = [];
try {
    $result = apiRequest('GET', '/collectes', null, $_SESSION['token']);
    $collectes = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$adherents = [];
try {
    $result = apiRequest('GET', '/adherents', null, $_SESSION['token']);
    $adherents = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$adherentsById = [];
foreach ($adherents as $a) {
    $adherentsById[$a['id']] = $a['nom'];
}

$collectesById = [];
foreach ($collectes as $c) {
    $collectesById[$c['id']] = 'Collecte #' . $c['id'] . ' — ' . ($adherentsById[$c['adherent_id']] ?? ('adhérent #' . $c['adherent_id']));
}

require __DIR__ . '/../includes/header_admin.php';
?>

<h2><?= t('produits_page_heading') ?></h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3><?= t('add_produit_heading') ?></h3>
<form method="post">
    <label><?= t('collecte_label') ?> :
        <select name="collecte_id" required>
            <option value=""><?= t('choose_placeholder') ?></option>
            <?php foreach ($collectes as $c): ?>
                <option value="<?= (int) $c['id'] ?>">Collecte #<?= (int) $c['id'] ?> — <?= htmlspecialchars($adherentsById[$c['adherent_id']] ?? ('adhérent #' . $c['adherent_id'])) ?> — <?= htmlspecialchars($c['date_collecte']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label><?= t('nom_label') ?> : <input type="text" name="nom" required></label><br>
    <label><?= t('quantite_label') ?> : <input type="number" name="quantite" value="1" min="1" required></label><br>
    <label><?= t('date_limite_conso_label') ?> : <input type="date" name="date_limite_conso"></label><br>
    <label><?= t('emplacement_stock_label') ?> : <input type="text" name="emplacement_stock"></label><br>
    <button type="submit"><?= t('add_to_stock_button') ?></button>
</form>

<h3><?= t('produits_list_heading') ?></h3>
<label for="recherche_code_barre"><?= t('recherche_code_barre_label') ?></label>
<input type="text" id="recherche_code_barre" placeholder="<?= t('recherche_code_barre_placeholder') ?>" autocomplete="off">
<p id="recherche_aucun_resultat" class="error" style="display:none;"><?= t('recherche_aucun_resultat') ?></p>

<table border="1" cellpadding="6" id="produits_table">
    <tr>
        <th><?= t('id_column') ?></th>
        <th><?= t('collecte_label') ?></th>
        <th><?= t('code_barre_label') ?></th>
        <th><?= t('nom_label') ?></th>
        <th><?= t('quantite_label') ?></th>
        <th><?= t('dlc_column') ?></th>
        <th><?= t('emplacement_column') ?></th>
        <th><?= t('statut_column') ?></th>
        <th><?= t('actions_column') ?></th>
    </tr>
    <?php foreach ($produits as $p): ?>
    <tr class="produit_row" data-code-barre="<?= htmlspecialchars(strtolower($p['code_barre'])) ?>">
        <td><?= htmlspecialchars($p['id']) ?></td>
        <td><?= htmlspecialchars($collectesById[$p['collecte_id']] ?? ('collecte #' . $p['collecte_id'])) ?></td>
        <td><?= htmlspecialchars($p['code_barre']) ?></td>
        <td><?= htmlspecialchars($p['nom']) ?></td>
        <td><?= htmlspecialchars($p['quantite']) ?></td>
        <td><?= htmlspecialchars($p['date_limite_conso']) ?></td>
        <td><?= htmlspecialchars($p['emplacement_stock']) ?></td>
        <td><?= htmlspecialchars($p['statut']) ?></td>
        <td>
            <?php foreach (['en_stock', 'distribue', 'perime'] as $statut): ?>
                <?php if ($statut !== $p['statut']): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="update_statut">
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                    <input type="hidden" name="statut" value="<?= $statut ?>">
                    <button type="submit">&rarr; <?= $statut ?></button>
                </form>
                <?php endif; ?>
            <?php endforeach; ?>
            <form method="post" style="display:inline;" onsubmit="return confirm(<?= json_encode(t('confirm_delete_produit')) ?>);">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <button type="submit"><?= t('action_delete') ?></button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<script>
    const rechercheInput = document.getElementById('recherche_code_barre');
    const lignes = Array.from(document.querySelectorAll('.produit_row'));
    const aucunResultat = document.getElementById('recherche_aucun_resultat');

    rechercheInput.addEventListener('input', () => {
        const terme = rechercheInput.value.trim().toLowerCase();
        let visibleCount = 0;

        lignes.forEach(ligne => {
            const correspond = terme === '' || ligne.dataset.codeBarre.includes(terme);
            ligne.style.display = correspond ? '' : 'none';
            if (correspond) {
                visibleCount++;
            }
        });

        aucunResultat.style.display = (terme !== '' && visibleCount === 0) ? 'block' : 'none';
    });
</script>

</div>
</body>
</html>
