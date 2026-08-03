<?php
session_start();

// TODO : si déjà connecté (session admin valide), rediriger vers le dashboard admin

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // TODO : appeler la route de login de l'API Go, ex POST http://localhost:8080/login
    // avec email, password, et role attendu = "admin"
    // si succès : stocker $_SESSION['user_id'], $_SESSION['role'] = 'admin', etc.
    // puis rediriger vers la page d'accueil du back-office
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NO MORE WASTE - Administration</title>
</head>
<body>
    <h1>Connexion - Espace Administration</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Email : <input type="email" name="email" required></label><br>
        <label>Mot de passe : <input type="password" name="password" required></label><br>
        <button type="submit">Se connecter</button>
    </form>

    <p><a href="../index.php">&larr; Retour</a></p>
</body>
</html>
