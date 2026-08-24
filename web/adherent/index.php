<?php
require_once __DIR__ . '/../includes/i18n.php';
require_once __DIR__ . '/../includes/api.php';

if (isset($_SESSION['token']) && isset($_SESSION['role']) && $_SESSION['role'] === 'adherent' && !isset($_GET['connected'])) {
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
            'role'     => 'adherent',
        ]);

        if ($result['statusCode'] === 200) {
            $_SESSION['token'] = $result['body']['token'];
            $_SESSION['role'] = 'adherent';
            header('Location: index.php?connected=1');
            exit;
        } else {
            $error = t('login_error');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="<?= currentLang() ?>">
<head>
    <meta charset="UTF-8">
    <title><?= t('adherent_page_title') ?></title>
</head>
<body>
    <h1><?= t('adherent_login_title') ?></h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['created'])): ?>
        <p style="color:green;"><?= t('account_created_msg') ?></p>
    <?php endif; ?>

     <?php if (isset($_GET['connected']) && isset($_SESSION['token'])): ?>
        <p style="color:green;"><?= t('adherent_connected_msg') ?></p>
        <p style="font-size:0.8em;word-break:break-all;"><?= t('token_label') ?> <?= htmlspecialchars($_SESSION['token']) ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label><?= t('email_label') ?> : <input type="email" name="email" required></label><br>
        <label><?= t('password_label') ?> : <input type="password" name="password" required></label><br>
        <button type="submit"><?= t('login_button') ?></button>
    </form>

    <p><a href="register.php"><?= t('adherent_no_account_link') ?></a></p>
    <p><a href="../index.php"><?= t("back_link") ?></a></p>
    <p><a href="?lang=fr">Français</a> | <a href="?lang=en">English</a></p>
</body>
</html>
