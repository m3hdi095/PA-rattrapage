<?php
session_start();
require_once __DIR__ . '/../includes/api.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    try {
        if ($action === 'update_statut') {
            apiRequest('PATCH', '/livraisons/statut', [
                'id'     => (int) $_POST['id'],
                'statut' => $_POST['statut'],
            ], $_SESSION['token']);
        } elseif ($action === 'add_produit') {
            apiRequest('POST', '/livraisons/produits', [
                'livraison_id' => (int) $_POST['livraison_id'],
                'produit_id'   => (int) $_POST['produit_id'],
                'quantite'     => (int) $_POST['quantite'],
            ], $_SESSION['token']);
        } elseif ($action === 'supprimer') {
            apiRequest('DELETE', '/livraisons', ['id' => (int) $_POST['id']], $_SESSION['token']);
        } else {
            apiRequest('POST', '/livraisons', [
                'tournee_id'      => (int) ($_POST['tournee_id'] ?? 0),
                'destinataire_id' => (int) ($_POST['destinataire_id'] ?? 0),
            ], $_SESSION['token']);
        }
        header('Location: livraisons.php');
        exit;
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

$token = $_SESSION['token'];

require __DIR__ . '/../includes/header_admin.php';
?>

<h2>Livraisons</h2>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3>Créer une livraison</h3>
<form method="post">
    <input type="hidden" name="action" value="create">
    <label>ID de la tournée : <input type="number" name="tournee_id" required></label><br>
    <label>ID du destinataire : <input type="number" name="destinataire_id" required></label><br>
    <button type="submit">Créer</button>
</form>

<h3>Ajouter un produit à une livraison</h3>
<form method="post">
    <input type="hidden" name="action" value="add_produit">
    <label>ID de la livraison : <input type="number" name="livraison_id" required></label><br>
    <label>ID du produit : <input type="number" name="produit_id" required></label><br>
    <label>Quantité : <input type="number" name="quantite" value="1" required></label><br>
    <button type="submit">Ajouter</button>
</form>

<h3>Liste</h3>
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Tournée</th>
        <th>Destinataire</th>
        <th>Statut</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($livraisons as $l): ?>
    <tr>
        <td><?= htmlspecialchars($l['id']) ?></td>
        <td><?= htmlspecialchars($l['tournee_id']) ?></td>
        <td><?= htmlspecialchars($l['destinataire_id']) ?></td>
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
            <button type="button" onclick="genererPDF(<?= (int) $l['id'] ?>)">Générer PDF</button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer cette livraison ?');">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                <button type="submit">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<script>
    const API_TOKEN = <?= json_encode($token) ?>;

    async function genererPDF(livraisonId) {
        const res = await fetch('http://localhost:8081/livraisons/recap?livraison_id=' + livraisonId, {
            headers: { 'Authorization': API_TOKEN }
        });
        if (!res.ok) {
            alert('Impossible de récupérer le récapitulatif de cette livraison.');
            return;
        }
        const produits = await res.json();

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(16);
        doc.text('NO MORE WASTE - Récapitulatif de livraison #' + livraisonId, 10, 15);

        doc.setFontSize(11);
        let y = 30;
        doc.text('Nom', 10, y);
        doc.text('Code-barre', 90, y);
        doc.text('Quantité', 160, y);
        y += 8;

        if (produits.length === 0) {
            doc.text('Aucun produit associé à cette livraison.', 10, y);
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

</body>
</html>
