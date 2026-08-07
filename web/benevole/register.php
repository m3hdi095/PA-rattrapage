<?php
session_start();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $capacites = $_POST['capacites'] ?? [];

    $payload = json_encode([
        'email'     => $email,
        'password'  => $password,
        'nom'       => $nom,
        'prenom'    => $prenom,
        'telephone' => $telephone,
        'capacites' => $capacites,
    ]);

    $ch = curl_init('http://localhost:8081/benevoles');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        $error = "Impossible de contacter l'API (" . $curlError . "). Vérifie que l'API Go tourne bien sur le port 8081.";
    } elseif ($statusCode === 201) {
        header('Location: index.php?created=1');
        exit;
    } else {
        $body = json_decode($response, true);
        $error = $body['error'] ?? "Erreur lors de la création du compte (code $statusCode)";
    }
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
