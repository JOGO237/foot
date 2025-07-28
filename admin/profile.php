<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';
$pageTitle = 'Mon Profil - Administration';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        
        $params = [$username, $email, $nom, $prenom];
        $password_update = "";
        
        if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
            // Vérifier l'ancien mot de passe
            $stmt = $pdo->prepare("SELECT password FROM admin_users WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $current_hash = $stmt->fetchColumn();
            
            if (password_verify($_POST['current_password'], $current_hash)) {
                if ($_POST['new_password'] == $_POST['confirm_password']) {
                    $password_update = ", password = ?";
                    $params[] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                } else {
                    $_SESSION['error'] = 'Les nouveaux mots de passe ne correspondent pas';
                }
            } else {
                $_SESSION['error'] = 'Mot de passe actuel incorrect';
            }
        }
        
        if (!isset($_SESSION['error'])) {
            $params[] = $_SESSION['admin_id'];
            
            try {
                $stmt = $pdo->prepare("
                    UPDATE admin_users 
                    SET username = ?, email = ?, nom = ?, prenom = ?$password_update 
                    WHERE id = ?
                ");
                $stmt->execute($params);
                
                $_SESSION['success'] = 'Profil mis à jour avec succès';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Erreur lors de la mise à jour du profil';
            }
        }
    }
}

// Récupération des données du profil
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$profile = $stmt->fetch();

include 'admin_header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            <i class="fas fa-user-circle mr-3 text-blue-600"></i>
            <span style="color: #1f2937;">Mon Profil</span>
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            Gérer vos informations personnelles
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Informations du profil -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div class="text-center">
                    <div class="w-24 h-24 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user text-4xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($profile['prenom'] . ' ' . $profile['nom']); ?>
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">@<?php echo htmlspecialchars($profile['username']); ?></p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-2
                        <?php 
                        switch($profile['role']) {
                            case 'super_admin': echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; break;
                            case 'admin': echo 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'; break;
                            case 'moderateur': echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'; break;
                        }
                        ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $profile['role'])); ?>
                    </span>
                </div>
                
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($profile['email']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Membre depuis</dt>
                            <dd class="text-sm text-gray-900 dark:text-white"><?php echo date('d/m/Y', strtotime($profile['created_at'])); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dernière connexion</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                <?php if($profile['last_login']): ?>
                                    <?php echo date('d/m/Y H:i', strtotime($profile['last_login'])); ?>
                                <?php else: ?>
                                    Jamais
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Formulaire de modification -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                    Modifier les informations
                </h3>
                
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nom d'utilisateur
                            </label>
                            <input type="text" name="username" required 
                                   value="<?php echo htmlspecialchars($profile['username']); ?>"
                                   class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Email
                            </label>
                            <input type="email" name="email" required 
                                   value="<?php echo htmlspecialchars($profile['email']); ?>"
                                   class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Prénom
                            </label>
                            <input type="text" name="prenom" required 
                                   value="<?php echo htmlspecialchars($profile['prenom']); ?>"
                                   class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nom
                            </label>
                            <input type="text" name="nom" required 
                                   value="<?php echo htmlspecialchars($profile['nom']); ?>"
                                   class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                    
                    <!-- Changement de mot de passe -->
                    <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4">
                            Changer le mot de passe (optionnel)
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Mot de passe actuel
                                </label>
                                <input type="password" name="current_password"
                                       class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Nouveau mot de passe
                                </label>
                                <input type="password" name="new_password"
                                       class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Confirmer le mot de passe
                                </label>
                                <input type="password" name="confirm_password"
                                       class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" name="update_profile" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                            <i class="fas fa-save mr-2"></i>Sauvegarder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>