<?php
// Initialiser le système de langue
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/languages.php';
$current_language = getCurrentLanguage();
$translations = loadTranslations($current_language);
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : __('football_youth_cameroon'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-green-600 via-yellow-500 to-red-600 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="text-white font-bold text-xl">
                        <i class="fas fa-futbol mr-2"></i>
                        <?php echo __('football_youth_cameroon'); ?>
                    </div>
                </div>
                
                <div class="hidden md:flex space-x-6">
                    <a href="index.php" class="text-white hover:text-yellow-200 transition-colors duration-200">
                        <i class="fas fa-home mr-1"></i><?php echo __('home'); ?>
                    </a>
                    <a href="clubs.php" class="text-white hover:text-yellow-200 transition-colors duration-200">
                        <i class="fas fa-shield-alt mr-1"></i><?php echo __('clubs'); ?>
                    </a>
                    
                    <a href="classements.php" class="text-white hover:text-yellow-200 transition-colors duration-200">
                        <i class="fas fa-trophy mr-1"></i><?php echo __('rankings'); ?>
                    </a>
                    <a href="calendrier.php" class="text-white hover:text-yellow-200 transition-colors duration-200">
                        <i class="fas fa-calendar mr-1"></i><?php echo __('calendar'); ?>
                    </a>
                    <a href="resultats.php" class="text-white hover:text-yellow-200 transition-colors duration-200">
                        <i class="fas fa-chart-bar mr-1"></i><?php echo __('results'); ?>
                    </a>
                    <a href="admin/index.php" class="text-white hover:text-yellow-200 transition-colors duration-200">
                        <i class="fas fa-cog mr-1"></i><?php echo __('admin'); ?>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Sélecteur de langue -->
                    <div class="relative">
                        <button id="languageToggle" class="text-white hover:text-yellow-200 transition-colors duration-200 flex items-center">
                            <span class="text-lg mr-1"><?php echo $languages[$current_language]['flag']; ?></span>
                            <span class="hidden lg:inline"><?php echo $languages[$current_language]['name']; ?></span>
                            <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                        <div id="languageMenu" class="hidden absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50">
                            <?php foreach($languages as $code => $lang): ?>
                            <a href="switch_language.php?lang=<?php echo $code; ?>" 
                               class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center <?php echo $current_language == $code ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                                <span class="mr-2"><?php echo $lang['flag']; ?></span>
                                <?php echo $lang['name']; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button id="darkModeToggle" class="text-white hover:text-yellow-200 transition-colors duration-200">
                        <i class="fas fa-moon"></i>
                    </button>
                    <button class="md:hidden text-white" id="mobileMenuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="md:hidden bg-green-700 hidden">
            <div class="px-4 py-2 space-y-2">
                <a href="index.php" class="block text-white hover:text-yellow-200"><?php echo __('home'); ?></a>
                <a href="clubs.php" class="block text-white hover:text-yellow-200"><?php echo __('clubs'); ?></a>
                <a href="joueurs.php" class="block text-white hover:text-yellow-200"><?php echo __('players'); ?></a>
                <a href="classements.php" class="block text-white hover:text-yellow-200"><?php echo __('rankings'); ?></a>
                <a href="calendrier.php" class="block text-white hover:text-yellow-200"><?php echo __('calendar'); ?></a>
                <a href="resultats.php" class="block text-white hover:text-yellow-200"><?php echo __('results'); ?></a>
                <a href="admin/index.php" class="block text-white hover:text-yellow-200"><?php echo __('admin'); ?></a>
                
                <!-- Sélecteur de langue mobile -->
                <div class="pt-2 border-t border-green-600">
                    <div class="text-white text-sm font-medium mb-2">
                        <i class="fas fa-globe mr-2"></i><?php echo __('language'); ?>
                    </div>
                    <?php foreach($languages as $code => $lang): ?>
                    <a href="switch_language.php?lang=<?php echo $code; ?>" 
                       class="block text-white hover:text-yellow-200 py-1 pl-4 <?php echo $current_language == $code ? 'text-yellow-200' : ''; ?>">
                        <span class="mr-2"><?php echo $lang['flag']; ?></span>
                        <?php echo $lang['name']; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </nav>