<?php
session_start();

// TODO : si déjà connecté (session bénévole valide), rediriger vers l'espace bénévole

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // TODO : appeler la route de login de l'API Go, ex POST http://localhost:8080/login
    // avec email, password, et role attendu = "benevole"
    // si succès : stocker $_SESSION['user_id'], $_SESSION['role'] = 'benevole',
    // $_SESSION['benevole_id'] = ...
    // puis rediriger vers l'espace bénévole
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>NO MORE WASTE - Espace Bénévole</title>
</head>
<body>
    <h1>Connexion - Espace Bénévole</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['created'])): ?>
        <p style="color:green;">Candidature envoyée ! Ton compte doit être validé par un administrateur avant de pouvoir te connecter.</p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Email : <input type="email" name="email" required></label><br>
        <label>Mot de passe : <input type="password" name="password" required></label><br>
        <button type="submit">Se connecter</button>
    </form>

    <p><a href="register.php">Pas encore de compte ? Devenir bénévole</a></p>
    <p><a href="../index.php">&larr; Retour</a></p>
</body>
</html>
