<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Football Jeunes Cameroun</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-hero min-h-screen flex items-center justify-center">
    <div class="absolute inset-0 bg-black bg-opacity-60"></div>
    
    <div class="relative z-10 max-w-md w-full mx-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-8">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <div class="text-3xl font-bold bg-gradient-to-r from-green-600 via-yellow-500 to-red-600 bg-clip-text text-transparent mb-2">
                    <i class="fas fa-shield-alt mr-2"></i>
                    Football Cameroun
                </div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Administration</h2>
                <p class="text-gray-600 dark:text-gray-400 text-sm mt-2">Connectez-vous pour accéder au panneau d'administration</p>
            </div>

            <!-- Formulaire de connexion -->
            <form method="POST" class="space-y-6">
                <?php if(isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-user mr-2"></i>
                        Nom d'utilisateur
                    </label>
                    <input type="text" id="username" name="username" required
                           class="w-full px-4 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white"
                           placeholder="Votre nom d'utilisateur">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-lock mr-2"></i>
                        Mot de passe
                    </label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white"
                           placeholder="Votre mot de passe">
                </div>

                <button type="submit" name="login" 
                        class="w-full bg-gradient-to-r from-green-600 via-yellow-500 to-red-600 text-white py-3 px-4 rounded-lg font-semibold hover:opacity-90 transition-opacity duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Se connecter
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <i class="fas fa-info-circle mr-1"></i>
                    Accès réservé aux administrateurs autorisés
                </p>
                <div class="mt-4">
                    <a href="../index.php" class="text-green-600 hover:text-green-700 text-sm font-medium">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Retour au site
                    </a>
                </div>
            </div>
        </div>

        
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>