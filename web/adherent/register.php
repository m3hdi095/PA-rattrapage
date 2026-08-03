<?php
session_start();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raisonSociale = $_POST['raison_sociale'] ?? '';
    $siret = $_POST['siret'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $codePostal = $_POST['code_postal'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $telephone = $_POST['telephone'] ?? '';

    // TODO : appeler la route de création de compte de l'API Go,
    // ex POST http://localhost:8080/commercants
    // avec les champs ci-dessus (role = "commercant")
    // si succès : rediriger vers index.php avec un message de confirmation
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NO MORE WASTE - Créer un compte commerçant</title>
</head>
<body>
    <h1>Créer un compte - Commerçant</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Raison sociale : <input type="text" name="raison_sociale" required></label><br>
        <label>SIRET : <input type="text" name="siret" required></label><br>
        <label>Adresse : <input type="text" name="adresse" required></label><br>
        <label>Code postal : <input type="text" name="code_postal" required></label><br>
        <label>Ville : <input type="text" name="ville" required></label><br>
        <label>Téléphone : <input type="tel" name="telephone"></label><br>
        <label>Email : <input type="email" name="email" required></label><br>
        <label>Mot de passe : <input type="password" name="password" required></label><br>
        <button type="submit">Créer mon compte</button>
    </form>

    <p><a href="index.php">&larr; Retour à la connexion</a></p>
</body>
</html>
