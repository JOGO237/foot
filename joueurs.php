
<?php
require_once 'config/database.php';
$pageTitle = 'Joueurs - Football Jeunes Cameroun';

// Filtres
$club_filter = isset($_GET['club']) ? $_GET['club'] : '';
$categorie_filter = isset($_GET['categorie']) ? $_GET['categorie'] : '';
$poste_filter = isset($_GET['poste']) ? $_GET['poste'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'nom';

// Construction de la requête
$where_conditions = ["j.statut = 'actif'"];
$params = [];

if ($club_filter) {
    $where_conditions[] = "j.club_id = ?";
    $params[] = $club_filter;
}

if ($categorie_filter) {
    $where_conditions[] = "j.categorie_id = ?";
    $params[] = $categorie_filter;
}

if ($poste_filter) {
    $where_conditions[] = "j.poste = ?";
    $params[] = $poste_filter;
}

if ($search) {
    $where_conditions[] = "(j.nom LIKE ? OR j.prenom LIKE ? OR j.licence_numero LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Gestion du tri
$order_clause = "ORDER BY ";
switch($sort) {
    case 'buts':
        $order_clause .= "total_buts DESC, j.nom ASC";
        break;
    case 'age':
        $order_clause .= "j.date_naissance DESC";
        break;
    case 'club':
        $order_clause .= "c.nom ASC, j.nom ASC";
        break;
    default:
        $order_clause .= "j.nom ASC, j.prenom ASC";
}

// Récupération des joueurs avec statistiques
$sql = "SELECT j.*, c.nom as club_nom, c.logo as club_logo, cat.nom as categorie_nom,
               YEAR(CURDATE()) - YEAR(j.date_naissance) as age,
               COALESCE(SUM(s.buts), 0) as total_buts,
               COALESCE(SUM(s.passes_decisives), 0) as total_passes,
               COALESCE(SUM(s.cartons_jaunes), 0) as total_cartons_jaunes,
               COALESCE(SUM(s.cartons_rouges), 0) as total_cartons_rouges,
               COALESCE(SUM(s.minutes_jouees), 0) as total_minutes,
               COUNT(s.match_id) as matchs_joues
        FROM joueurs j
        JOIN clubs c ON j.club_id = c.id
        JOIN categories cat ON j.categorie_id = cat.id
        LEFT JOIN stats_joueurs s ON j.id = s.joueur_id
        $where_clause
        GROUP BY j.id
        $order_clause";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$joueurs = $stmt->fetchAll();

// Récupération des données pour les filtres
$clubs = $pdo->query("SELECT * FROM clubs ORDER BY nom")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY age_min")->fetchAll();
$postes = $pdo->query("SELECT DISTINCT poste FROM joueurs WHERE poste IS NOT NULL AND poste != '' ORDER BY poste")->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-users mr-3 text-green-600"></i>
            Joueurs
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-400">
            Découvrez les talents du football jeunes camerounais
        </p>
    </div>

    <!-- Filtres et Recherche -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="search-container">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rechercher</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Nom, prénom, licence..." 
                       class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Club</label>
                <select name="club" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous les clubs</option>
                    <?php foreach($clubs as $club): ?>
                    <option value="<?php echo $club['id']; ?>" <?php echo $club_filter == $club['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($club['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catégorie</label>
                <select name="categorie" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Toutes</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categorie_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Poste</label>
                <select name="poste" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous</option>
                    <?php foreach($postes as $poste): ?>
                    <option value="<?php echo $poste['poste']; ?>" <?php echo $poste_filter == $poste['poste'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($poste['poste']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Trier par</label>
                <select name="sort" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                    <option value="nom" <?php echo $sort == 'nom' ? 'selected' : ''; ?>>Nom</option>
                    <option value="buts" <?php echo $sort == 'buts' ? 'selected' : ''; ?>>Buts marqués</option>
                    <option value="age" <?php echo $sort == 'age' ? 'selected' : ''; ?>>Âge</option>
                    <option value="club" <?php echo $sort == 'club' ? 'selected' : ''; ?>>Club</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>Rechercher
                </button>
            </div>
        </form>
    </div>

    <!-- Résultats -->
    <div class="mb-6">
        <p class="text-gray-600 dark:text-gray-400">
            <i class="fas fa-info-circle mr-2"></i>
            <?php echo count($joueurs); ?> joueur(s) trouvé(s)
        </p>
    </div>

    <!-- Grille des joueurs -->
    <div id="joueurs-results" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach($joueurs as $joueur): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden player-card card-hover animate-on-scroll">
            <!-- Photo du joueur -->
            <div class="relative h-48 bg-gradient-to-br from-green-400 via-yellow-400 to-red-400">
                <?php if($joueur['photo']): ?>
                <img src="uploads/photos/<?php echo htmlspecialchars($joueur['photo']); ?>" 
                     alt="<?php echo htmlspecialchars($joueur['prenom'] . ' ' . $joueur['nom']); ?>" 
                     class="w-full h-full object-cover">
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center">
                    <i class="fas fa-user text-6xl text-white opacity-50"></i>
                </div>
                <?php endif; ?>
                
                <!-- Badge numéro -->
                <?php if($joueur['numero_maillot']): ?>
                <div class="absolute top-4 right-4 bg-black bg-opacity-70 text-white rounded-full w-12 h-12 flex items-center justify-center font-bold text-lg">
                    <?php echo $joueur['numero_maillot']; ?>
                </div>
                <?php endif; ?>
                
                <!-- Badge catégorie -->
                <div class="absolute top-4 left-4 bg-white bg-opacity-90 text-gray-800 px-2 py-1 rounded-full text-xs font-semibold">
                    <?php echo htmlspecialchars($joueur['categorie_nom']); ?>
                </div>
            </div>
            
            <!-- Informations du joueur -->
            <div class="p-6">
                <!-- Nom et club -->
                <div class="text-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($joueur['prenom'] . ' ' . $joueur['nom']); ?>
                    </h3>
                    <div class="flex items-center justify-center mt-2 text-gray-600 dark:text-gray-400">
                        <?php if($joueur['club_logo']): ?>
                        <img src="uploads/logos/<?php echo htmlspecialchars($joueur['club_logo']); ?>" 
                             alt="<?php echo htmlspecialchars($joueur['club_nom']); ?>" 
                             class="w-6 h-6 mr-2 object-contain">
                        <?php endif; ?>
                        <span class="text-sm"><?php echo htmlspecialchars($joueur['club_nom']); ?></span>
                    </div>
                </div>
                
                <!-- Informations de base -->
                <div class="space-y-2 mb-4">
                    <?php if($joueur['poste']): ?>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Poste:</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($joueur['poste']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Âge:</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo $joueur['age']; ?> ans</span>
                    </div>
                    
                    <?php if($joueur['taille']): ?>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Taille:</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo $joueur['taille']; ?>m</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($joueur['poids']): ?>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Poids:</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo $joueur['poids']; ?>kg</span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Statistiques -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3 text-center">Statistiques</h4>
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div>
                            <div class="text-2xl font-bold text-green-600"><?php echo $joueur['total_buts']; ?></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Buts</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600"><?php echo $joueur['total_passes']; ?></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Passes D.</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-yellow-600"><?php echo $joueur['total_cartons_jaunes']; ?></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">C. Jaunes</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-red-600"><?php echo $joueur['total_cartons_rouges']; ?></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">C. Rouges</div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <div class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo $joueur['matchs_joues']; ?></div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Matchs joués</div>
                    </div>
                    <?php if($joueur['total_minutes'] > 0): ?>
                    <div class="mt-2 text-center">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <?php echo number_format($joueur['total_minutes'] / 60, 1); ?> heures de jeu
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if(empty($joueurs)): ?>
    <div class="text-center py-12">
        <i class="fas fa-search text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">Aucun joueur trouvé</h3>
        <p class="text-gray-500 dark:text-gray-500">Essayez de modifier vos critères de recherche</p>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>