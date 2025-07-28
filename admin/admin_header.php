<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Administration - Football Jeunes Cameroun'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <!-- Navigation Admin -->
    <nav class="bg-gray-800 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="text-white font-bold text-xl">
                        <i class="fas fa-cog mr-2"></i>
                    
                    </div>
                </div>
                
                <div class="hidden md:flex space-x-6">
                    <a href="index.php" class="text-gray-300 hover:text-white transition-colors duration-200">
                        <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                    </a>
                    <a href="clubs.php" class="text-gray-300 hover:text-white transition-colors duration-200">
                        <i class="fas fa-shield-alt mr-1"></i>Clubs
                    </a>
                    <a href="joueurs.php" class="text-gray-300 hover:text-white transition-colors duration-200">
                        <i class="fas fa-users mr-1"></i>Joueurs
                    </a>
                    <a href="matchs.php" class="text-gray-300 hover:text-white transition-colors duration-200">
                        <i class="fas fa-futbol mr-1"></i>Matchs
                    </a>
                    <a href="sponsors.php" class="text-gray-300 hover:text-white transition-colors duration-200">
                        <i class="fas fa-handshake mr-1"></i>Sponsors
                    </a>
                    <a href="../index.php" class="text-gray-300 hover:text-white transition-colors duration-200">
                        <i class="fas fa-eye mr-1"></i>Voir le site
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button id="darkModeToggle" class="text-gray-300 hover:text-white transition-colors duration-200">
                        <i class="fas fa-moon"></i>
                    </button>
                    <div class="relative">
                        <button class="text-gray-300 hover:text-white transition-colors duration-200" onclick="toggleUserMenu()">
                            <i class="fas fa-user-circle text-xl mr-1"></i>
                            Admin
                            <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                        <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50">
                            <!-- Ajoutez ces liens dans la navigation -->
                            <a href="users.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                             <i class="fas fa-users-cog mr-1"></i> Utilisateurs
                            </a> <br>
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fas fa-user-circle mr-1"></i>Profil
                            </a><br>
                            <a href="trash.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <i class="fas fa-trash-restore mr-1"></i>Corbeille
                            </a> <br>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                            </a>
                        </div>
                    </div>
                    <button class="md:hidden text-gray-300" id="mobileMenuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="md:hidden bg-gray-700 hidden">
            <div class="px-4 py-2 space-y-2">
                <a href="index.php" class="block text-gray-300 hover:text-white">Dashboard</a>
                <a href="clubs.php" class="block text-gray-300 hover:text-white">Clubs</a>
                <a href="joueurs.php" class="block text-gray-300 hover:text-white">Joueurs</a>
                <a href="matchs.php" class="block text-gray-300 hover:text-white">Matchs</a>
                <a href="sponsors.php" class="block text-gray-300 hover:text-white">Sponsors</a>
                <a href="../index.php" class="block text-gray-300 hover:text-white">Voir le site</a>
                <a href="logout.php" class="block text-gray-300 hover:text-white">Déconnexion</a>
            </div>
        </div>
    </nav>

    <script>
    function toggleUserMenu() {
        document.getElementById('userMenu').classList.toggle('hidden');
    }
    
    // Fermer le menu si on clique ailleurs
    window.onclick = function(event) {
        if (!event.target.matches('.fa-user-circle') && !event.target.matches('.fa-chevron-down')) {
            const dropdown = document.getElementById('userMenu');
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        }
    }
    </script>