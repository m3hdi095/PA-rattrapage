<?php
session_start();

if (isset($_SESSION['token']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: index.php?connected=1');
    exit;
}
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $payload = json_encode([
        'email'    => $email,
        'password' => $password,
        'role'     => 'admin',
    ]);

    $ch = curl_init('http://localhost:8081/login');
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
    } elseif ($statusCode === 200) {
        $body = json_decode($response, true);
        $_SESSION['token'] = $body['token'];
        $_SESSION['role'] = 'admin';
        header('Location: index.php?connected=1');
        exit;
    } else {
        $error = "Email ou mot de passe incorrect";
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
