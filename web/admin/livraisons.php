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
    $action = $_POST['action'] ?? 'create';
    try {
        if ($action === 'update_statut') {
            $result = apiRequest('PATCH', '/livraisons/statut', [
                'id'     => (int) $_POST['id'],
                'statut' => $_POST['statut'],
            ], $_SESSION['token']);
        } elseif ($action === 'add_produit') {
            $result = apiRequest('POST', '/livraisons/produits', [
                'livraison_id' => (int) $_POST['livraison_id'],
                'produit_id'   => (int) $_POST['produit_id'],
                'quantite'     => (int) $_POST['quantite'],
            ], $_SESSION['token']);
        } elseif ($action === 'supprimer') {
            $result = apiRequest('DELETE', '/livraisons', ['id' => (int) $_POST['id']], $_SESSION['token']);
        } else {
            $result = apiRequest('POST', '/livraisons', [
                'tournee_id'      => (int) ($_POST['tournee_id'] ?? 0),
                'destinataire_id' => (int) ($_POST['destinataire_id'] ?? 0),
            ], $_SESSION['token']);
        }
        if ($result['statusCode'] >= 400) {
            $error = $result['body']['error'] ?? t('livraison_action_error');
        } else {
            header('Location: livraisons.php');
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$livraisons = [];
try {
    $result = apiRequest('GET', '/livraisons', null, $_SESSION['token']);
    $livraisons = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$tournees = [];
try {
    $result = apiRequest('GET', '/tournees', null, $_SESSION['token']);
    $tournees = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$destinataires = [];
try {
    $result = apiRequest('GET', '/destinataires', null, $_SESSION['token']);
    $destinataires = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$produits = [];
try {
    $result = apiRequest('GET', '/produits', null, $_SESSION['token']);
    $produits = $result['body'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$destinatairesById = [];
foreach ($destinataires as $d) {
    $destinatairesById[$d['id']] = $d['nom'];
}

$tourneesById = [];
foreach ($tournees as $t) {
    $tourneesById[$t['id']] = 'Tournée #' . $t['id'] . ' — ' . $t['date_tournee'];
}

$token = $_SESSION['token'];
$apiPublicUrl = getenv('API_PUBLIC_URL') ?: 'http://localhost:8081';

require __DIR__ . '/../includes/header_admin.php';
?>

<h2><?= t('livraisons_page_heading') ?></h2>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3><?= t('create_livraison_heading') ?></h3>
<form method="post">
    <input type="hidden" name="action" value="create">
    <label><?= t('tournee_label') ?> :
        <select name="tournee_id" required>
            <option value=""><?= t('choose_placeholder') ?></option>
            <?php foreach ($tournees as $t): ?>
                <option value="<?= (int) $t['id'] ?>">Tournée #<?= (int) $t['id'] ?> — <?= htmlspecialchars($t['date_tournee']) ?> (<?= htmlspecialchars($t['statut']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label><?= t('destinataire_label') ?> :
        <select name="destinataire_id" required>
            <option value=""><?= t('choose_placeholder') ?></option>
            <?php foreach ($destinataires as $d): ?>
                <option value="<?= (int) $d['id'] ?>"><?= htmlspecialchars($d['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <button type="submit"><?= t('action_create') ?></button>
</form>

<h3><?= t('add_produit_to_livraison_heading') ?></h3>
<form method="post">
    <input type="hidden" name="action" value="add_produit">
    <label><?= t('livraison_label') ?> :
        <select name="livraison_id" required>
            <option value=""><?= t('choose_placeholder') ?></option>
            <?php foreach ($livraisons as $l): ?>
                <option value="<?= (int) $l['id'] ?>">Livraison #<?= (int) $l['id'] ?> — <?= htmlspecialchars($destinatairesById[$l['destinataire_id']] ?? ('destinataire #' . $l['destinataire_id'])) ?> (<?= htmlspecialchars($l['statut']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label><?= t('produit_label') ?> :
        <select name="produit_id" required>
            <option value=""><?= t('choose_placeholder') ?></option>
            <?php foreach ($produits as $p): ?>
                <option value="<?= (int) $p['id'] ?>"><?= htmlspecialchars($p['nom'] . ' (' . $p['code_barre'] . ')') ?></option>
            <?php endforeach; ?>
        </select>
    </label><br>
    <label><?= t('quantite_label') ?> : <input type="number" name="quantite" value="1" min="1" required></label><br>
    <button type="submit"><?= t('action_add') ?></button>
</form>

<h3><?= t('list_heading') ?></h3>
<table border="1" cellpadding="6">
    <tr>
        <th><?= t('id_column') ?></th>
        <th><?= t('tournee_label') ?></th>
        <th><?= t('destinataire_label') ?></th>
        <th><?= t('statut_column') ?></th>
        <th><?= t('actions_column') ?></th>
    </tr>
    <?php foreach ($livraisons as $l): ?>
    <tr>
        <td><?= htmlspecialchars($l['id']) ?></td>
        <td><?= htmlspecialchars($tourneesById[$l['tournee_id']] ?? ('tournée #' . $l['tournee_id'])) ?></td>
        <td><?= htmlspecialchars($destinatairesById[$l['destinataire_id']] ?? ('destinataire #' . $l['destinataire_id'])) ?></td>
        <td><?= htmlspecialchars($l['statut']) ?></td>
        <td>
            <?php foreach (['prevue', 'livree', 'annulee'] as $statut): ?>
                <?php if ($statut !== $l['statut']): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="update_statut">
                    <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                    <input type="hidden" name="statut" value="<?= $statut ?>">
                    <button type="submit">&rarr; <?= $statut ?></button>
                </form>
                <?php endif; ?>
            <?php endforeach; ?>
            <button type="button" onclick="genererPDF(<?= (int) $l['id'] ?>)"><?= t('generate_pdf_button') ?></button>
            <form method="post" style="display:inline;" onsubmit="return confirm(<?= json_encode(t('confirm_delete_livraison')) ?>);">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                <button type="submit"><?= t('action_delete') ?></button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<script>
    const API_TOKEN = <?= json_encode($token) ?>;
    const API_PUBLIC_URL = <?= json_encode($apiPublicUrl) ?>;
    const I18N_PDF_FETCH_ERROR = <?= json_encode(t('pdf_fetch_error')) ?>;
    const I18N_PDF_RECAP_TITLE_PREFIX = <?= json_encode(t('pdf_recap_title_prefix')) ?>;
    const I18N_PDF_COL_NOM = <?= json_encode(t('pdf_col_nom')) ?>;
    const I18N_PDF_COL_CODE_BARRE = <?= json_encode(t('pdf_col_code_barre')) ?>;
    const I18N_PDF_COL_QUANTITE = <?= json_encode(t('pdf_col_quantite')) ?>;
    const I18N_PDF_NO_PRODUITS = <?= json_encode(t('pdf_no_produits')) ?>;

    async function genererPDF(livraisonId) {
        const res = await fetch(API_PUBLIC_URL + '/livraisons/recap?livraison_id=' + livraisonId, {
            headers: { 'Authorization': API_TOKEN }
        });
        if (!res.ok) {
            alert(I18N_PDF_FETCH_ERROR);
            return;
        }
        const produits = await res.json();

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(16);
        doc.text(I18N_PDF_RECAP_TITLE_PREFIX + livraisonId, 10, 15);

        doc.setFontSize(11);
        let y = 30;
        doc.text(I18N_PDF_COL_NOM, 10, y);
        doc.text(I18N_PDF_COL_CODE_BARRE, 90, y);
        doc.text(I18N_PDF_COL_QUANTITE, 160, y);
        y += 8;

        if (produits.length === 0) {
            doc.text(I18N_PDF_NO_PRODUITS, 10, y);
        } else {
            produits.forEach(p => {
                doc.text(String(p.nom), 10, y);
                doc.text(String(p.code_barre), 90, y);
                doc.text(String(p.quantite), 160, y);
                y += 8;
            });
        }

        doc.save('livraison_' + livraisonId + '.pdf');
    }
</script>

</div>
</body>
</html>
