
<?php
require_once 'config/database.php';
$pageTitle = 'Résultats - Football Jeunes Cameroun';

// Filtres
$categorie_filter = isset($_GET['categorie']) ? $_GET['categorie'] : '';
$club_filter = isset($_GET['club']) ? $_GET['club'] : '';
$mois_filter = isset($_GET['mois']) ? $_GET['mois'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Construction de la requête
$where_conditions = ["m.statut = 'terminé'"];
$params = [];

if ($categorie_filter) {
    $where_conditions[] = "m.categorie_id = ?";
    $params[] = $categorie_filter;
}

if ($club_filter) {
    $where_conditions[] = "(m.club_domicile_id = ? OR m.club_exterieur_id = ?)";
    $params[] = $club_filter;
    $params[] = $club_filter;
}

if ($mois_filter) {
    $where_conditions[] = "DATE_FORMAT(m.date_match, '%Y-%m') = ?";
    $params[] = $mois_filter;
}

if ($search) {
    $where_conditions[] = "(cd.nom LIKE ? OR ce.nom LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Récupération des résultats
$sql = "SELECT m.*, 
               cd.nom as club_domicile, cd.logo as logo_domicile,
               ce.nom as club_exterieur, ce.logo as logo_exterieur,
               c.nom as categorie_nom
        FROM matchs m
        JOIN clubs cd ON m.club_domicile_id = cd.id
        JOIN clubs ce ON m.club_exterieur_id = ce.id
        JOIN categories c ON m.categorie_id = c.id
        $where_clause
        ORDER BY m.date_match DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resultats = $stmt->fetchAll();

// Données pour les filtres
$categories = $pdo->query("SELECT * FROM categories ORDER BY age_min")->fetchAll();
$clubs = $pdo->query("SELECT * FROM clubs ORDER BY nom")->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-chart-bar mr-3 text-green-600"></i>
            <span style="color: #1f2937;">Résultats</span>
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-400">
            Consultez tous les résultats des matchs terminés
        </p>
    </div>

    <!-- Filtres et Recherche -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="search-container">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rechercher</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Nom d'un club..." 
                       class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catégorie</label>
                <select name="categorie" class="category-filter w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Toutes les catégories</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categorie_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mois</label>
                <input type="month" name="mois" value="<?php echo htmlspecialchars($mois_filter); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
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
            <?php echo count($resultats); ?> résultat(s) trouvé(s)
        </p>
    </div>

    <!-- Liste des résultats -->
    <div id="resultats-results" class="space-y-6">
        <?php foreach($resultats as $match): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 card-hover animate-on-scroll">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <!-- Date et catégorie -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:space-x-4 mb-4 lg:mb-0">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <?php echo date('d/m/Y à H:i', strtotime($match['date_match'])); ?>
                    </div>
                    <span class="inline-block bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm font-medium">
                        <?php echo htmlspecialchars($match['categorie_nom']); ?>
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
                                 class="w-16 h-16 mx-auto mb-2 object-contain">
                            <?php else: ?>
                            <div class="w-16 h-16 mx-auto mb-2 bg-gray-200 dark:bg-gray-600 rounded"></div>
                            <?php endif; ?>
                            <div class="font-medium text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($match['club_domicile']); ?>
                            </div>
                        </div>
                        
                        <!-- Score -->
                        <div class="text-center px-8">
                            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                <?php echo $match['score_domicile']; ?> - <?php echo $match['score_exterieur']; ?>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Score final
                            </div>
                        </div>
                        
                        <!-- Club extérieur -->
                        <div class="text-center flex-1">
                            <?php if($match['logo_exterieur']): ?>
                            <img src="uploads/logos/<?php echo htmlspecialchars($match['logo_exterieur']); ?>" 
                                 alt="<?php echo htmlspecialchars($match['club_exterieur']); ?>" 
                                 class="w-16 h-16 mx-auto mb-2 object-contain">
                            <?php else: ?>
                            <div class="w-16 h-16 mx-auto mb-2 bg-gray-200 dark:bg-gray-600 rounded"></div>
                            <?php endif; ?>
                            <div class="font-medium text-gray-900 dark:text-white">
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
                    
                    <button onclick="toggleStats(<?php echo $match['id']; ?>)" 
                            class="mt-2 text-blue-600 hover:text-blue-700 font-medium">
                        <i class="fas fa-chart-line mr-1"></i>
                        Voir statistiques
                    </button>
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
            
            <!-- Statistiques détaillées (cachées par défaut) -->
            <div id="stats-<?php echo $match['id']; ?>" class="hidden mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Statistiques du match</h4>
                <div class="loading-stats">
                    <div class="flex justify-center">
                        <div class="loading"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if(empty($resultats)): ?>
    <div class="text-center py-12">
        <i class="fas fa-search text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">Aucun résultat trouvé</h3>
        <p class="text-gray-500 dark:text-gray-500">Essayez de modifier vos critères de recherche</p>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleStats(matchId) {
    const statsDiv = document.getElementById(`stats-${matchId}`);
    
    if (statsDiv.classList.contains('hidden')) {
        statsDiv.classList.remove('hidden');
        loadMatchStats(matchId);
    } else {
        statsDiv.classList.add('hidden');
    }
}

function loadMatchStats(matchId) {
    const statsDiv = document.getElementById(`stats-${matchId}`);
    const loadingDiv = statsDiv.querySelector('.loading-stats');
    
    fetch(`ajax/match_stats.php?match_id=${matchId}`)
        .then(response => response.json())
        .then(data => {
            loadingDiv.innerHTML = generateStatsHTML(data);
        })
        .catch(error => {
            console.error('Erreur:', error);
            loadingDiv.innerHTML = '<p class="text-red-500">Erreur de chargement des statistiques</p>';
        });
}

function generateStatsHTML(stats) {
    if (!stats.length) {
        return '<p class="text-gray-500">Aucune statistique disponible pour ce match</p>';
    }
    
    let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
    
    // Grouper par équipe
    const statsByTeam = {};
    stats.forEach(stat => {
        if (!statsByTeam[stat.club_nom]) {
            statsByTeam[stat.club_nom] = [];
        }
        statsByTeam[stat.club_nom].push(stat);
    });
    
    Object.entries(statsByTeam).forEach(([club, joueurs]) => {
        html += `
            <div>
                <h5 class="font-semibold text-lg mb-3 text-gray-900 dark:text-white">${club}</h5>
                <div class="space-y-2">
        `;
        
        joueurs.forEach(joueur => {
            html += `
                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                    <div>
                        <div class="font-medium text-sm">${joueur.prenom} ${joueur.nom}</div>
                        <div class="text-xs text-gray-500">${joueur.minutes_jouees} min</div>
                    </div>
                    <div class="text-right text-sm">
                        ${joueur.buts > 0 ? `<span class="text-green-600">${joueur.buts} ⚽</span> ` : ''}
                        ${joueur.passes_decisives > 0 ? `<span class="text-blue-600">${joueur.passes_decisives} 🎯</span> ` : ''}
                        ${joueur.cartons_jaunes > 0 ? `<span class="text-yellow-500">${joueur.cartons_jaunes} 🟨</span> ` : ''}
                        ${joueur.cartons_rouges > 0 ? `<span class="text-red-500">${joueur.cartons_rouges} 🟥</span> ` : ''}
                    </div>
                </div>
            `;
        });
        
        html += '</div></div>';
    });
    
    html += '</div>';
    return html;
}
</script>

<?php include 'includes/footer.php'; ?>