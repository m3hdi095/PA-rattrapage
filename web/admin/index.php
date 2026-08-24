<?php
require_once __DIR__ . '/../includes/i18n.php';
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
    <title><?= t('admin_page_title') ?></title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <div class="header">
        <h1><?= t('admin_login_title') ?></h1>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <label><?= t('email_label') ?> : <input type="email" name="email" required></label>
            <label><?= t('password_label') ?> : <input type="password" name="password" required></label>
            <button type="submit"><?= t('login_button') ?></button>
        </form>

        <p><a href="../index.php"><?= t("back_link") ?></a></p>
        <p><a href="?lang=fr">Français</a> | <a href="?lang=en">English</a></p>
    </div>
</body>
</html>
