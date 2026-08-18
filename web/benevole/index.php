<?php
session_start();
require_once __DIR__ . '/../includes/api.php';

if (isset($_SESSION['token']) && isset($_SESSION['role']) && $_SESSION['role'] === 'benevole' && !isset($_GET['connected'])) {
    header('Location: index.php?connected=1');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $result = apiRequest('POST', '/login', [
            'email'    => $email,
            'password' => $password,
            'role'     => 'benevole',
        ]);

        if ($result['statusCode'] === 200) {
            $_SESSION['token'] = $result['body']['token'];
            $_SESSION['role'] = 'benevole';
            header('Location: index.php?connected=1');
            exit;
        } else {
            $error = "Email ou mot de passe incorrect";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
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

    <?php if (isset($_GET['connected']) && isset($_SESSION['token'])): ?>
        <p style="color:green;">Connecté ! (espace bénévole pas encore construit, mais le token est bien en session)</p>
        <p style="font-size:0.8em;word-break:break-all;">Token : <?= htmlspecialchars($_SESSION['token']) ?></p>
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
