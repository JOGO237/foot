
<?php
require_once 'config/database.php';
$pageTitle = 'Classements - Football Jeunes Cameroun';

// Filtres
$categorie_filter = isset($_GET['categorie']) ? $_GET['categorie'] : '';
$saison_filter = isset($_GET['saison']) ? $_GET['saison'] : '';

// Si aucune saison spécifiée, prendre la saison active
if (!$saison_filter) {
    $saison_active = $pdo->query("SELECT id FROM saisons WHERE statut = 'active' ORDER BY date_debut DESC LIMIT 1")->fetchColumn();
    $saison_filter = $saison_active;
}

// Récupération des données pour les filtres
$categories = $pdo->query("SELECT * FROM categories ORDER BY age_min")->fetchAll();
$saisons = $pdo->query("SELECT * FROM saisons ORDER BY date_debut DESC")->fetchAll();

// Construction des classements
$classements_par_categorie = [];

if ($categorie_filter && $saison_filter) {
    // Classement pour une catégorie spécifique
    $stmt = $pdo->prepare("
        SELECT cl.*, c.nom as club_nom, c.logo, cat.nom as categorie_nom,
               (cl.buts_pour - cl.buts_contre) as difference_buts
        FROM classements cl
        JOIN clubs c ON cl.club_id = c.id
        JOIN categories cat ON cl.categorie_id = cat.id
        WHERE cl.categorie_id = ? AND cl.saison_id = ?
        ORDER BY cl.points DESC, difference_buts DESC, cl.buts_pour DESC, c.nom ASC
    ");
    $stmt->execute([$categorie_filter, $saison_filter]);
    $classements_par_categorie[$categorie_filter] = $stmt->fetchAll();
} else {
    // Tous les classements si saison sélectionnée
    if ($saison_filter) {
        foreach($categories as $cat) {
            $stmt = $pdo->prepare("
                SELECT cl.*, c.nom as club_nom, c.logo, cat.nom as categorie_nom,
                       (cl.buts_pour - cl.buts_contre) as difference_buts
                FROM classements cl
                JOIN clubs c ON cl.club_id = c.id
                JOIN categories cat ON cl.categorie_id = cat.id
                WHERE cl.categorie_id = ? AND cl.saison_id = ?
                ORDER BY cl.points DESC, difference_buts DESC, cl.buts_pour DESC, c.nom ASC
            ");
            $stmt->execute([$cat['id'], $saison_filter]);
            $resultats = $stmt->fetchAll();
            if (!empty($resultats)) {
                $classements_par_categorie[$cat['id']] = $resultats;
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-trophy mr-3 text-yellow-600"></i>
            <span style="color: #1f2937;">Classements</span>
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-400">
            Suivez le classement de tous les championnats
        </p>
    </div>

    <!-- Filtres -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Saison</label>
                <select name="saison" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                    <?php foreach($saisons as $saison): ?>
                    <option value="<?php echo $saison['id']; ?>" <?php echo $saison_filter == $saison['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($saison['nom']); ?>
                        <?php if($saison['statut'] == 'active'): ?>(Active)<?php endif; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catégorie</label>
                <select name="categorie" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Toutes les catégories</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categorie_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors duration-200">
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Classements -->
    <?php if (empty($classements_par_categorie)): ?>
    <div class="text-center py-12">
        <i class="fas fa-trophy text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">Aucun classement disponible</h3>
        <p class="text-gray-500 dark:text-gray-500">Sélectionnez une saison pour voir les classements</p>
    </div>
    <?php else: ?>
        <?php foreach($classements_par_categorie as $cat_id => $classement): ?>
        <?php 
        $categorie_nom = '';
        foreach($categories as $cat) {
            if($cat['id'] == $cat_id) {
                $categorie_nom = $cat['nom'];
                break;
            }
        }
        ?>
        <div class="mb-12 animate-on-scroll">
            <div class="bg-gradient-to-r from-yellow-500 via-green-500 to-red-500 rounded-t-lg p-6">
                <h2 class="text-2xl font-bold text-white text-center">
                    <i class="fas fa-medal mr-2"></i>
                    Classement <?php echo htmlspecialchars($categorie_nom); ?>
                </h2>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-b-lg shadow-lg overflow-hidden">
                <div class="table-responsive">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Pos
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Club
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    MJ
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    V
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    N
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    D
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    BP
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    BC
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Diff
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Pts
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach($classement as $index => $equipe): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 <?php echo $index < 3 ? 'bg-yellow-50 dark:bg-yellow-900 dark:bg-opacity-20' : ''; ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-sm font-bold <?php echo $index < 3 ? 'text-yellow-600' : 'text-gray-900 dark:text-white'; ?>">
                                            <?php echo $index + 1; ?>
                                        </span>
                                        <?php if($index == 0): ?>
                                        <i class="fas fa-crown text-yellow-500 ml-2"></i>
                                        <?php elseif($index == 1): ?>
                                        <i class="fas fa-medal text-gray-400 ml-2"></i>
                                        <?php elseif($index == 2): ?>
                                        <i class="fas fa-medal text-yellow-600 ml-2"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if($equipe['logo']): ?>
                                        <img src="uploads/logos/<?php echo htmlspecialchars($equipe['logo']); ?>" 
                                             alt="<?php echo htmlspecialchars($equipe['club_nom']); ?>" 
                                             class="w-8 h-8 mr-3 object-contain">
                                        <?php else: ?>
                                        <div class="w-8 h-8 mr-3 bg-gray-200 dark:bg-gray-600 rounded"></div>
                                        <?php endif; ?>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($equipe['club_nom']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900 dark:text-white">
                                    <?php echo $equipe['matchs_joues']; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 font-semibold">
                                    <?php echo $equipe['victoires']; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-yellow-600 font-semibold">
                                    <?php echo $equipe['nuls']; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-red-600 font-semibold">
                                    <?php echo $equipe['defaites']; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900 dark:text-white">
                                    <?php echo $equipe['buts_pour']; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900 dark:text-white">
                                    <?php echo $equipe['buts_contre']; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-semibold <?php echo $equipe['difference_buts'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $equipe['difference_buts'] >= 0 ? '+' : ''; ?><?php echo $equipe['difference_buts']; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-lg font-bold text-blue-600">
                                        <?php echo $equipe['points']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Légende -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mt-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-info-circle mr-2 text-blue-600"></i>
            Légende
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
            <div><strong>MJ:</strong> Matchs Joués</div>
            <div><strong>V:</strong> Victoires</div>
            <div><strong>N:</strong> Nuls</div>
            <div><strong>D:</strong> Défaites</div>
            <div><strong>BP:</strong> Buts Pour</div>
            <div><strong>BC:</strong> Buts Contre</div>
            <div><strong>Diff:</strong> Différence de buts</div>
            <div><strong>Pts:</strong> Points</div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-6 text-sm">
                <div class="flex items-center">
                    <i class="fas fa-crown text-yellow-500 mr-2"></i>
                    <span>Champion</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-yellow-50 dark:bg-yellow-900 dark:bg-opacity-20 mr-2 rounded"></div>
                    <span>Podium</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>