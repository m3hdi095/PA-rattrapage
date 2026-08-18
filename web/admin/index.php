<?php
session_start();
require_once __DIR__ . '/../includes/api.php';

if (isset($_SESSION['token']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: benevoles.php');
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
            'role'     => 'admin',
        ]);

        if ($result['statusCode'] === 200) {
            $_SESSION['token'] = $result['body']['token'];
            $_SESSION['role'] = 'admin';
            header('Location: benevoles.php');
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
