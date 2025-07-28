<?php
session_start();
require_once '../config/database.php';

// Vérification de connexion admin (simple pour demo)
if (!isset($_SESSION['admin_logged'])) {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_role'] = $admin['role'];
            
            // Mise à jour last_login
            $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);
            
            header('Location: index.php');
            exit;
        } else {
            $error = "Identifiants incorrects";
        }
    }
    
    // Affichage du formulaire de connexion
    include 'login_form.php';
    exit;
}

$pageTitle = 'Administration - Football Jeunes Cameroun';

// Statistiques pour le dashboard
$stats = [];
$stats['clubs'] = $pdo->query("SELECT COUNT(*) FROM clubs")->fetchColumn();
$stats['joueurs'] = $pdo->query("SELECT COUNT(*) FROM joueurs WHERE statut = 'actif'")->fetchColumn();
$stats['matchs'] = $pdo->query("SELECT COUNT(*) FROM matchs")->fetchColumn();
$stats['sponsors'] = $pdo->query("SELECT COUNT(*) FROM sponsors WHERE statut = 'actif'")->fetchColumn();

// Dernières activités
$derniers_joueurs = $pdo->query("
    SELECT j.*, c.nom as club_nom 
    FROM joueurs j 
    JOIN clubs c ON j.club_id = c.id 
    ORDER BY j.created_at DESC 
    LIMIT 5
")->fetchAll();

$derniers_matchs = $pdo->query("
    SELECT m.*, cd.nom as club_domicile, ce.nom as club_exterieur, cat.nom as categorie_nom
    FROM matchs m
    JOIN clubs cd ON m.club_domicile_id = cd.id
    JOIN clubs ce ON m.club_exterieur_id = ce.id
    JOIN categories cat ON m.categorie_id = cat.id
    ORDER BY m.created_at DESC
    LIMIT 5
")->fetchAll();

include 'admin_header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header Dashboard -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            <i class="fas fa-tachometer-alt mr-3 text-blue-600"></i>
            <span style="color: #1f2937;">Tableau de Bord</span>
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            Bienvenue dans l'interface d'administration
        </p>
    </div>

    <!-- Cartes statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 card-hover">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-alt text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $stats['clubs']; ?></div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Clubs</div>
                </div>
            </div>
            <div class="mt-4">
                <a href="clubs.php" class="text-green-600 hover:text-green-700 text-sm font-medium">
                    Gérer les clubs <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 card-hover">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $stats['joueurs']; ?></div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Joueurs Actifs</div>
                </div>
            </div>
            <div class="mt-4">
                <a href="joueurs.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    Gérer les joueurs <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 card-hover">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                        <i class="fas fa-futbol text-yellow-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $stats['matchs']; ?></div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Matchs</div>
                </div>
            </div>
            <div class="mt-4">
                <a href="matchs.php" class="text-yellow-600 hover:text-yellow-700 text-sm font-medium">
                    Gérer les matchs <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 card-hover">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                        <i class="fas fa-handshake text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $stats['sponsors']; ?></div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Sponsors</div>
                </div>
            </div>
            <div class="mt-4">
                <a href="sponsors.php" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                    Gérer les sponsors <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Derniers joueurs ajoutés -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-user-plus mr-2 text-blue-600"></i>
                    Derniers Joueurs Ajoutés
                </h3>
            </div>
            <div class="p-6">
                <?php if(empty($derniers_joueurs)): ?>
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">Aucun joueur récent</p>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($derniers_joueurs as $joueur): ?>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                        <div class="flex items-center">
                            <?php if($joueur['photo']): ?>
                            <img src="../uploads/photos/<?php echo htmlspecialchars($joueur['photo']); ?>" 
                                 alt="<?php echo htmlspecialchars($joueur['prenom'] . ' ' . $joueur['nom']); ?>" 
                                 class="w-10 h-10 rounded-full object-cover mr-3">
                            <?php else: ?>
                            <div class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <?php endif; ?>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($joueur['prenom'] . ' ' . $joueur['nom']); ?>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo htmlspecialchars($joueur['club_nom']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <?php echo date('d/m/Y', strtotime($joueur['created_at'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="joueurs.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        Voir tous les joueurs <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Derniers matchs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-futbol mr-2 text-green-600"></i>
                    Derniers Matchs Programmés
                </h3>
            </div>
            <div class="p-6">
                <?php if(empty($derniers_matchs)): ?>
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">Aucun match récent</p>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($derniers_matchs as $match): ?>
                    <div class="py-3 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($match['club_domicile']); ?> 
                                    vs 
                                    <?php echo htmlspecialchars($match['club_exterieur']); ?>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <?php echo htmlspecialchars($match['categorie_nom']); ?> • 
                                    <?php echo date('d/m/Y H:i', strtotime($match['date_match'])); ?>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo $match['statut']; ?>">
                                <?php echo ucfirst($match['statut']); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="matchs.php" class="text-green-600 hover:text-green-700 text-sm font-medium">
                        Voir tous les matchs <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="mt-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-bolt mr-2 text-yellow-600"></i>
            <span style="color: #1f2937;">Actions Rapides</span>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="clubs.php?action=add" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 hover:shadow-lg transition-shadow duration-200 block">
                <div class="text-center">
                    <i class="fas fa-plus-circle text-3xl text-green-600 mb-2"></i>
                    <div class="font-medium text-gray-900 dark:text-white">Ajouter un Club</div>
                </div>
            </a>
            
            <a href="joueurs.php?action=add" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 hover:shadow-lg transition-shadow duration-200 block">
                <div class="text-center">
                    <i class="fas fa-user-plus text-3xl text-blue-600 mb-2"></i>
                    <div class="font-medium text-gray-900 dark:text-white">Ajouter un Joueur</div>
                </div>
            </a>
            
            <a href="matchs.php?action=add" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 hover:shadow-lg transition-shadow duration-200 block">
                <div class="text-center">
                    <i class="fas fa-calendar-plus text-3xl text-yellow-600 mb-2"></i>
                    <div class="font-medium text-gray-900 dark:text-white">Programmer un Match</div>
                </div>
            </a>
            
            <a href="sponsors.php?action=add" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 hover:shadow-lg transition-shadow duration-200 block">
                <div class="text-center">
                    <i class="fas fa-handshake text-3xl text-purple-600 mb-2"></i>
                    <div class="font-medium text-gray-900 dark:text-white">Ajouter un Sponsor</div>
                </div>
            </a>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>