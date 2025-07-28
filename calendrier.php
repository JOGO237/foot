
<?php
require_once 'config/database.php';
$pageTitle = 'Calendrier - Football Jeunes Cameroun';

// Filtres
$categorie_filter = isset($_GET['categorie']) ? $_GET['categorie'] : '';
$mois_filter = isset($_GET['mois']) ? $_GET['mois'] : date('Y-m');
$statut_filter = isset($_GET['statut']) ? $_GET['statut'] : '';

// Construction de la requête
$where_conditions = [];
$params = [];

if ($categorie_filter) {
    $where_conditions[] = "m.categorie_id = ?";
    $params[] = $categorie_filter;
}

if ($mois_filter) {
    $where_conditions[] = "DATE_FORMAT(m.date_match, '%Y-%m') = ?";
    $params[] = $mois_filter;
}

if ($statut_filter) {
    $where_conditions[] = "m.statut = ?";
    $params[] = $statut_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Récupération des matchs
$sql = "SELECT m.*, 
               cd.nom as club_domicile, cd.logo as logo_domicile,
               ce.nom as club_exterieur, ce.logo as logo_exterieur,
               c.nom as categorie_nom
        FROM matchs m
        JOIN clubs cd ON m.club_domicile_id = cd.id
        JOIN clubs ce ON m.club_exterieur_id = ce.id
        JOIN categories c ON m.categorie_id = c.id
        $where_clause
        ORDER BY m.date_match ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$matchs = $stmt->fetchAll();

// Regroupement par date
$matchs_par_date = [];
foreach($matchs as $match) {
    $date = date('Y-m-d', strtotime($match['date_match']));
    $matchs_par_date[$date][] = $match;
}

// Données pour les filtres
$categories = $pdo->query("SELECT * FROM categories ORDER BY age_min")->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-calendar-alt mr-3 text-blue-600"></i>
            <span style="color: #1f2937;">Calendrier</span>
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-400">
            Consultez tous les matchs programmés
        </p>
    </div>

    <!-- Filtres -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mois</label>
                <input type="month" name="mois" value="<?php echo htmlspecialchars($mois_filter); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catégorie</label>
                <select name="categorie" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Toutes les catégories</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categorie_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut</label>
                <select name="statut" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous les statuts</option>
                    <option value="programmé" <?php echo $statut_filter == 'programmé' ? 'selected' : ''; ?>>Programmé</option>
                    <option value="en_cours" <?php echo $statut_filter == 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                    <option value="terminé" <?php echo $statut_filter == 'terminé' ? 'selected' : ''; ?>>Terminé</option>
                    <option value="reporté" <?php echo $statut_filter == 'reporté' ? 'selected' : ''; ?>>Reporté</option>
                    <option value="annulé" <?php echo $statut_filter == 'annulé' ? 'selected' : ''; ?>>Annulé</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors duration-200">
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Résultats -->
    <div class="mb-6">
        <p class="text-gray-600 dark:text-gray-400">
            <i class="fas fa-info-circle mr-2"></i>
            <?php echo count($matchs); ?> match(s) trouvé(s)
        </p>
    </div>

    <!-- Calendrier des matchs -->
    <?php if (empty($matchs_par_date)): ?>
    <div class="text-center py-12">
        <i class="fas fa-calendar-times text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">Aucun match trouvé</h3>
        <p class="text-gray-500 dark:text-gray-500">Essayez de modifier vos critères de recherche</p>
    </div>
    <?php else: ?>
        <?php foreach($matchs_par_date as $date => $matchs_jour): ?>
        <div class="mb-8 animate-on-scroll">
            <!-- Header de la date -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-t-lg p-4">
                <h2 class="text-xl font-bold text-white">
                    <i class="fas fa-calendar-day mr-2"></i>
                    <?php 
                    setlocale(LC_TIME, 'fr_FR.UTF-8');
                    echo strftime('%A %d %B %Y', strtotime($date)); 
                    ?>
                </h2>
            </div>
            
            <!-- Matchs du jour -->
            <div class="bg-white dark:bg-gray-800 rounded-b-lg shadow-lg">
                <?php foreach($matchs_jour as $index => $match): ?>
                <div class="p-6 <?php echo $index > 0 ? 'border-t border-gray-200 dark:border-gray-700' : ''; ?> live-scores" id="match-<?php echo $match['id']; ?>">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <!-- Heure et catégorie -->
                        <div class="flex flex-col lg:flex-row lg:items-center lg:space-x-4 mb-4 lg:mb-0">
                            <div class="text-lg font-bold text-gray-900 dark:text-white">
                                <?php echo date('H:i', strtotime($match['date_match'])); ?>
                            </div>
                            <span class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm font-medium">
                                <?php echo htmlspecialchars($match['categorie_nom']); ?>
                            </span>
                            <span class="status-badge status-<?php echo $match['statut']; ?> mt-2 lg:mt-0">
                                <?php echo ucfirst($match['statut']); ?>
                            </span>
                        </div>
                        
                        <!-- Match principal -->
                        <div class="flex-1 max-w-2xl mx-auto">
                            <div class="flex items-center justify-between">
                                <!-- Club domicile -->
                                <div class="text-center flex-1">
                                    <?php if($match['logo_domicile']): ?>
                                    <img src="uploads/logos/<?php echo htmlspecialchars($match['logo_domicile']); ?>" 
                                         alt="<?php echo htmlspecialchars($match['club_domicile']); ?>" 
                                         class="w-12 h-12 mx-auto mb-2 object-contain">
                                    <?php else: ?>
                                    <div class="w-12 h-12 mx-auto mb-2 bg-gray-200 dark:bg-gray-600 rounded"></div>
                                    <?php endif; ?>
                                    <div class="font-medium text-gray-900 dark:text-white text-sm">
                                        <?php echo htmlspecialchars($match['club_domicile']); ?>
                                    </div>
                                </div>
                                
                                <!-- Score/VS -->
                                <div class="text-center px-6">
                                    <?php if($match['statut'] == 'terminé' && $match['score_domicile'] !== null): ?>
                                    <div class="score text-2xl font-bold text-gray-900 dark:text-white">
                                        <?php echo $match['score_domicile']; ?> - <?php echo $match['score_exterieur']; ?>
                                    </div>
                                    <?php elseif($match['statut'] == 'en_cours'): ?>
                                    <div class="text-red-600 font-bold">
                                        <i class="fas fa-circle text-red-500 animate-pulse"></i> EN DIRECT
                                    </div>
                                    <div class="score text-xl font-bold text-gray-900 dark:text-white">
                                        <?php echo $match['score_domicile'] ?? 0; ?> - <?php echo $match['score_exterieur'] ?? 0; ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-xl font-bold text-gray-500 dark:text-gray-400">VS</div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Club extérieur -->
                                <div class="text-center flex-1">
                                    <?php if($match['logo_exterieur']): ?>
                                    <img src="uploads/logos/<?php echo htmlspecialchars($match['logo_exterieur']); ?>" 
                                         alt="<?php echo htmlspecialchars($match['club_exterieur']); ?>" 
                                         class="w-12 h-12 mx-auto mb-2 object-contain">
                                    <?php else: ?>
                                    <div class="w-12 h-12 mx-auto mb-2 bg-gray-200 dark:bg-gray-600 rounded"></div>
                                    <?php endif; ?>
                                    <div class="font-medium text-gray-900 dark:text-white text-sm">
                                        <?php echo htmlspecialchars($match['club_exterieur']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations supplémentaires -->
                        <div class="text-right text-sm text-gray-600 dark:text-gray-400 mt-4 lg:mt-0">
                            <?php if($match['stade']): ?>
                            <div class="flex items-center justify-end">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <?php echo htmlspecialchars($match['stade']); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if($match['arbitre']): ?>
                            <div class="flex items-center justify-end mt-1">
                                <i class="fas fa-whistle mr-1"></i>
                                <?php echo htmlspecialchars($match['arbitre']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Observations -->
                    <?php if($match['observations']): ?>
                    <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <i class="fas fa-sticky-note mr-2"></i>
                            <?php echo htmlspecialchars($match['observations']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
// Auto-refresh pour les matchs en cours
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier s'il y a des matchs en cours
    const matchsEnCours = document.querySelectorAll('.status-en_cours');
    if (matchsEnCours.length > 0) {
        // Rafraîchir toutes les 30 secondes
        setInterval(refreshLiveScores, 30000);
    }
});
</script>

<?php include 'includes/footer.php'; ?>