<?php
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_role'] !== 'super_admin') {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';
$pageTitle = 'Gestion des Utilisateurs - Administration';

// Gestion des actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$user_id = isset($_GET['id']) ? $_GET['id'] : null;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $role = $_POST['role'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO admin_users (username, password, email, nom, prenom, role) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $password, $email, $nom, $prenom, $role]);
            
            $_SESSION['success'] = 'Utilisateur ajouté avec succès';
            header('Location: users.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur lors de l\'ajout de l\'utilisateur : ' . $e->getMessage();
        }
    } elseif (isset($_POST['edit_user'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $role = $_POST['role'];
        
        $params = [$username, $email, $nom, $prenom, $role];
        $password_update = "";
        
        if (!empty($_POST['password'])) {
            $password_update = ", password = ?";
            $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        $params[] = $user_id;
        
        try {
            $stmt = $pdo->prepare("
                UPDATE admin_users 
                SET username = ?, email = ?, nom = ?, prenom = ?, role = ?$password_update 
                WHERE id = ?
            ");
            $stmt->execute($params);
            
            $_SESSION['success'] = 'Utilisateur modifié avec succès';
            header('Location: users.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur lors de la modification de l\'utilisateur';
        }
    }
}

// Suppression
if ($action == 'delete' && $user_id) {
    if ($user_id == $_SESSION['admin_id']) {
        $_SESSION['error'] = 'Vous ne pouvez pas supprimer votre propre compte';
    } else {
        try {
            $pdo->prepare("DELETE FROM admin_users WHERE id = ?")->execute([$user_id]);
            $_SESSION['success'] = 'Utilisateur supprimé avec succès';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur lors de la suppression';
        }
    }
    header('Location: users.php');
    exit;
}

// Récupération des données
if ($action == 'edit' && $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = 'Utilisateur introuvable';
        header('Location: users.php');
        exit;
    }
}

// Liste des utilisateurs
$users = $pdo->query("SELECT * FROM admin_users ORDER BY nom, prenom")->fetchAll();

include 'admin_header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                <i class="fas fa-users-cog mr-3 text-indigo-600"></i>
                <span style="color: #1f2937;">Gestion des Utilisateurs</span>
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Gérer les comptes administrateurs
            </p>
        </div>
        
        <?php if ($action == 'list'): ?>
        <a href="?action=add" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
            <i class="fas fa-plus mr-2"></i>Ajouter un Utilisateur
        </a>
        <?php else: ?>
        <a href="users.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
        <?php endif; ?>
    </div>

    <?php if ($action == 'list'): ?>
    <!-- Liste des utilisateurs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Utilisateurs administrateurs (<?php echo count($users); ?>)
            </h3>
        </div>
        
        <div class="table-responsive">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Utilisateur
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Rôle
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Dernière connexion
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach($users as $user): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-indigo-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        @<?php echo htmlspecialchars($user['username']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php 
                                switch($user['role']) {
                                    case 'super_admin': echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; break;
                                    case 'admin': echo 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'; break;
                                    case 'moderateur': echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'; break;
                                }
                                ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-white">
                            <?php if($user['last_login']): ?>
                                <?php echo date('d/m/Y H:i', strtotime($user['last_login'])); ?>
                            <?php else: ?>
                                <span class="text-gray-400">Jamais</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                            <a href="?action=edit&id=<?php echo $user['id']; ?>" 
                               class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if($user['id'] != $_SESSION['admin_id']): ?>
                            <a href="?action=delete&id=<?php echo $user['id']; ?>" 
                               class="text-red-600 hover:text-red-700 delete-btn">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php elseif ($action == 'add' || $action == 'edit'): ?>
    <!-- Formulaire d'ajout/modification -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
            <?php echo $action == 'add' ? 'Ajouter un nouvel utilisateur' : 'Modifier l\'utilisateur'; ?>
        </h3>
        
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nom d'utilisateur *
                    </label>
                    <input type="text" name="username" required 
                           value="<?php echo isset($user) ? htmlspecialchars($user['username']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email *
                    </label>
                    <input type="email" name="email" required 
                           value="<?php echo isset($user) ? htmlspecialchars($user['email']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Prénom *
                    </label>
                    <input type="text" name="prenom" required 
                           value="<?php echo isset($user) ? htmlspecialchars($user['prenom']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nom *
                    </label>
                    <input type="text" name="nom" required 
                           value="<?php echo isset($user) ? htmlspecialchars($user['nom']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Rôle *
                    </label>
                    <select name="role" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Sélectionner un rôle</option>
                        <option value="moderateur" <?php echo (isset($user) && $user['role'] == 'moderateur') ? 'selected' : ''; ?>>Modérateur</option>
                        <option value="admin" <?php echo (isset($user) && $user['role'] == 'admin') ? 'selected' : ''; ?>>Administrateur</option>
                        <option value="super_admin" <?php echo (isset($user) && $user['role'] == 'super_admin') ? 'selected' : ''; ?>>Super Administrateur</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Mot de passe <?php echo $action == 'edit' ? '(laisser vide pour ne pas changer)' : '*'; ?>
                    </label>
                    <input type="password" name="password" <?php echo $action == 'add' ? 'required' : ''; ?>
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="users.php" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Annuler
                </a>
                <button type="submit" name="<?php echo $action == 'add' ? 'add_user' : 'edit_user'; ?>" 
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold">
                    <?php echo $action == 'add' ? 'Ajouter' : 'Modifier'; ?>
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>
<br> <br> <br> <br> <br> <br> <br> <br>
<?php include 'admin_footer.php';?>