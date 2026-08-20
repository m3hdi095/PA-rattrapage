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
        if (($_POST['action'] ?? '') === 'update_statut') {
            apiRequest('PATCH', '/produits/statut', [
                'id'     => (int) $_POST['id'],
                'statut' => $_POST['statut'],
            ], $_SESSION['token']);
        } elseif (($_POST['action'] ?? '') === 'supprimer') {
            apiRequest('DELETE', '/produits', ['id' => (int) $_POST['id']], $_SESSION['token']);
        } else {
            apiRequest('POST', '/produits', [
                'collecte_id'       => (int) ($_POST['collecte_id'] ?? 0),
                'code_barre'        => $_POST['code_barre'] ?? '',
                'nom'               => $_POST['nom'] ?? '',
                'quantite'          => (int) ($_POST['quantite'] ?? 1),
                'date_limite_conso' => $_POST['date_limite_conso'] ?? '',
                'emplacement_stock' => $_POST['emplacement_stock'] ?? '',
            ], $_SESSION['token']);
        }
        header('Location: produits.php');
        exit;
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

require __DIR__ . '/../includes/header_admin.php';
?>

<h2>Gestion des stocks (produits)</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<h3>Ajouter un produit</h3>
<form method="post">
    <label>ID de la collecte : <input type="number" name="collecte_id" required></label><br>
    <label>Code-barre : <input type="text" name="code_barre" required></label><br>
    <label>Nom : <input type="text" name="nom" required></label><br>
    <label>Quantité : <input type="number" name="quantite" value="1" required></label><br>
    <label>Date limite de conso : <input type="date" name="date_limite_conso"></label><br>
    <label>Emplacement stock : <input type="text" name="emplacement_stock"></label><br>
    <button type="submit">Ajouter au stock</button>
</form>

<h3>Produits</h3>
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Collecte</th>
        <th>Code-barre</th>
        <th>Nom</th>
        <th>Quantité</th>
        <th>DLC</th>
        <th>Emplacement</th>
        <th>Statut</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($produits as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['id']) ?></td>
        <td><?= htmlspecialchars($p['collecte_id']) ?></td>
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
            <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce produit ?');">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <button type="submit">Supprimer</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
