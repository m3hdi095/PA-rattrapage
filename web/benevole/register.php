<?php
session_start();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $capacites = $_POST['capacites'] ?? []; // ex: checkboxes chauffeur / cuisinier / plombier...

    // TODO : appeler la route de création de compte de l'API Go,
    // ex POST http://localhost:8080/benevoles
    // avec les champs ci-dessus (role = "benevole", statut_candidature = "en_attente")
    // si succès : rediriger vers index.php avec un message de confirmation
    // (le compte devra être validé par un admin avant utilisation complète)
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NO MORE WASTE - Devenir bénévole</title>
</head>
<body>
    <h1>Créer un compte - Bénévole</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Nom : <input type="text" name="nom" required></label><br>
        <label>Prénom : <input type="text" name="prenom" required></label><br>
        <label>Téléphone : <input type="tel" name="telephone"></label><br>
        <label>Email : <input type="email" name="email" required></label><br>
        <label>Mot de passe : <input type="password" name="password" required></label><br>

        <fieldset>
            <legend>Compétences proposées</legend>
            <label><input type="checkbox" name="capacites[]" value="chauffeur"> Chauffeur</label><br>
            <label><input type="checkbox" name="capacites[]" value="cuisinier"> Cuisinier</label><br>
            <label><input type="checkbox" name="capacites[]" value="plombier"> Plombier</label><br>
            <label><input type="checkbox" name="capacites[]" value="electricien"> Électricien</label><br>
        </fieldset>

        <button type="submit">Envoyer ma candidature</button>
    </form>

    <p><a href="index.php">&larr; Retour à la connexion</a></p>
</body>
</html>
