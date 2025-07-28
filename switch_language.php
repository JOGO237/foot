<?php
session_start();
require_once 'config/languages.php';

if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $languages)) {
    $_SESSION['language'] = $_GET['lang'];
    
    // Sauvegarder dans un cookie pour 30 jours
    setcookie('language', $_GET['lang'], time() + (30 * 24 * 60 * 60), '/');
}

// Rediriger vers la page précédente ou l'accueil
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header('Location: ' . $redirect);
exit;
?>