<?php
// Configuration des langues
$languages = [
    'fr' => [
        'name' => 'Français',
        'flag' => '🇫🇷',
        'code' => 'fr'
    ],
    'en' => [
        'name' => 'English',
        'flag' => '🇺🇸',
        'code' => 'en'
    ],
    'es' => [
        'name' => 'Español',
        'flag' => '🇪🇸',
        'code' => 'es'
    ],
    'de' => [
        'name' => 'Deutsch',
        'flag' => '🇩🇪',
        'code' => 'de'
    ]
];

// Langue par défaut
$default_language = 'fr';

// Détection de la langue
function getCurrentLanguage() {
    global $default_language;
    
    // 1. Vérifier la session
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }
    
    // 2. Vérifier les cookies
    if (isset($_COOKIE['language'])) {
        return $_COOKIE['language'];
    }
    
    // 3. Détection automatique du navigateur
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browser_languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($browser_languages as $lang) {
            $lang = substr(trim($lang), 0, 2);
            if (array_key_exists($lang, $GLOBALS['languages'])) {
                return $lang;
            }
        }
    }
    
    return $default_language;
}

// Fonction pour charger les traductions
function loadTranslations($language) {
    $file = __DIR__ . "/translations/{$language}.php";
    if (file_exists($file)) {
        return include $file;
    }
    return include __DIR__ . "/translations/fr.php"; // Fallback
}

// Fonction de traduction
function __($key, $params = []) {
    global $translations;
    
    if (!isset($translations)) {
        $current_lang = getCurrentLanguage();
        $translations = loadTranslations($current_lang);
    }
    
    $text = isset($translations[$key]) ? $translations[$key] : $key;
    
    // Remplacer les paramètres
    foreach ($params as $param => $value) {
        $text = str_replace(':' . $param, $value, $text);
    }
    
    return $text;
}

// Initialiser la langue actuelle
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = getCurrentLanguage();
}
?>