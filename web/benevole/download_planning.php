<?php
session_start();
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/i18n.php';

if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'benevole') {
    header('Location: index.php');
    exit;
}

try {
    $result = apiRequestRaw('GET', '/plannings/excel', $_SESSION['token']);
} catch (Exception $e) {
    http_response_code(500);
    echo htmlspecialchars($e->getMessage());
    exit;
}

if ($result['statusCode'] !== 200) {
    http_response_code(500);
    echo htmlspecialchars(sprintf(t('excel_generation_error'), $result['statusCode']));
    exit;
}

header('Content-Type: ' . $result['contentType']);
header('Content-Disposition: attachment; filename="planning.xlsx"');
header('Content-Length: ' . strlen($result['body']));
echo $result['body'];
