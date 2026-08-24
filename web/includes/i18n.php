<?php
session_start();

if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

function currentLang() {
    return $_SESSION['lang'] ?? 'fr';
}

function t($key) {
    static $strings = null;
    if ($strings === null) {
        $path = __DIR__ . '/../lang/' . currentLang() . '/messages.json';
        $strings = json_decode(file_get_contents($path), true);
    }
    return $strings[$key] ?? $key;
}