<?php
session_start();

$role = $_SESSION['role'] ?? null;
session_unset();
session_destroy();

if ($role === 'admin') {
    $redirect = 'admin/index.php';
} elseif ($role === 'adherent') {
    $redirect = 'adherent/index.php';
} elseif ($role === 'benevole') {
    $redirect = 'benevole/index.php';
} else {
    $redirect = 'index.php';
}

header('Location: ' . $redirect);
exit;
