<?php
// Initialiser le système de langue
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/languages.php';
$current_language = getCurrentLanguage();
$translations = loadTranslations($current_language);
?>
<?php
require_once 'config/database.php';
$pageTitle = 'Accueil - Football Jeunes Cameroun';

// Statistiques générales
$stats = [];
$stats['clubs'] = $pdo->query("SELECT COUNT(*) FROM clubs")->fetchColumn();
$stats['joueurs'] = $pdo->query("SELECT COUNT(*) FROM joueurs WHERE statut = 'actif'")->fetchColumn();
$stats['matchs'] = $pdo->query("SELECT COUNT(*) FROM matchs")->fetchColumn();
$stats['saisons'] = $pdo->query("SELECT COUNT(*) FROM saisons")->fetchColumn();

// Derniers résultats
$derniers_resultats = $pdo->query("
    SELECT m.*, 
           cd.nom as club_domicile, cd.logo as logo_domicile,
           ce.nom as club_exterieur, ce.logo as logo_exterieur,
           c.nom as categorie_nom
    FROM matchs m
    JOIN clubs cd ON m.club_domicile_id = cd.id
    JOIN clubs ce ON m.club_exterieur_id = ce.id
    JOIN categories c ON m.categorie_id = c.id
    WHERE m.statut = 'terminé'
    ORDER BY m.date_match DESC
    LIMIT 5
")->fetchAll();

// Prochains matchs
$prochains_matchs = $pdo->query("
    SELECT m.*, 
           cd.nom as club_domicile, cd.logo as logo_domicile,
           ce.nom as club_exterieur, ce.logo as logo_exterieur,
           c.nom as categorie_nom
    FROM matchs m
    JOIN clubs cd ON m.club_domicile_id = cd.id
    JOIN clubs ce ON m.club_exterieur_id = ce.id
    JOIN categories c ON m.categorie_id = c.id
    WHERE m.statut = 'programmé' AND m.date_match > NOW()
    ORDER BY m.date_match ASC
    LIMIT 5
")->fetchAll();

// Meilleurs buteurs
$buteurs = $pdo->query("
    SELECT j.nom, j.prenom, j.photo, c.nom as club_nom, SUM(s.buts) as total_buts
    FROM joueurs j
    JOIN clubs c ON j.club_id = c.id
    JOIN stats_joueurs s ON j.id = s.joueur_id
    WHERE j.statut = 'actif'
    GROUP BY j.id
    ORDER BY total_buts DESC
    LIMIT 10
")->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-hero min-h-screen flex items-center">
    <div class="absolute inset-0 bg-black bg-opacity-60"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center text-white">
            <h1 class="text-5xl md:text-7xl font-bold mb-6 fade-in-up">
                Football Jeunes
                <span class="bg-gradient-to-r from-green-400 via-yellow-400 to-red-400 bg-clip-text text-transparent">
                    Cameroun
                </span>
            </h1>
            <p class="text-xl md:text-2xl mb-8 fade-in-up" style="animation-delay: 0.2s">
                L'avenir du football camerounais commence ici
            </p>
            <div class="flex flex-col md:flex-row justify-center space-y-4 md:space-y-0 md:space-x-6 fade-in-up" style="animation-delay: 0.4s">
                <a href="clubs.php" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-shield-alt mr-2"></i>Découvrir les Clubs
                </a>
                <a href="joueurs.php" class="bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-users mr-2"></i>Voir les Joueurs
                </a>
                <a href="classements.php" class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-trophy mr-2"></i>Classements
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Statistiques Générales -->
<section class="py-16 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white">
            <i class="fas fa-chart-line mr-3 text-green-600"></i>
            Statistiques Générales
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="stats-card p-6 rounded-lg text-center card-hover animate-on-scroll">
                <div class="text-4xl font-bold text-green-600 mb-2" id="total-clubs"><?php echo $stats['clubs']; ?></div>
                <div class="text-gray-600 dark:text-gray-400">Clubs Inscrits</div>
                <i class="fas fa-shield-alt text-2xl text-green-600 mt-3"></i>
            </div>
            <div class="stats-card p-6 rounded-lg text-center card-hover animate-on-scroll">
                <div class="text-4xl font-bold text-yellow-600 mb-2" id="total-joueurs"><?php echo $stats['joueurs']; ?></div>
                <div class="text-gray-600 dark:text-gray-400">Joueurs Actifs</div>
                <i class="fas fa-users text-2xl text-yellow-600 mt-3"></i>
            </div>
            <div class="stats-card p-6 rounded-lg text-center card-hover animate-on-scroll">
                <div class="text-4xl font-bold text-red-600 mb-2" id="total-matchs"><?php echo $stats['matchs']; ?></div>
                <div class="text-gray-600 dark:text-gray-400">Matchs Disputés</div>
                <i class="fas fa-futbol text-2xl text-red-600 mt-3"></i>
            </div>
            <div class="stats-card p-6 rounded-lg text-center card-hover animate-on-scroll">
                <div class="text-4xl font-bold text-blue-600 mb-2" id="total-saisons"><?php echo $stats['saisons']; ?></div>
                <div class="text-gray-600 dark:text-gray-400">Saisons</div>
                <i class="fas fa-calendar text-2xl text-blue-600 mt-3"></i>
            </div>
        </div>
    </div>
</section>

<!-- Derniers Résultats -->
<section class="py-16 bg-gray-50 dark:bg-gray-800">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-chart-bar mr-3 text-green-600"></i>
               <span style="color: black;"> Derniers Résultats</span>
            </h2>
            <a href="resultats.php" class="text-green-600 hover:text-green-700 font-semibold">
                Voir tous <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($derniers_resultats as $match): ?>
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-6 card-hover animate-on-scroll">
                <div class="text-center mb-4">
                    <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm font-medium">
                        <?php echo htmlspecialchars($match['categorie_nom']); ?>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-center flex-1">
                        <?php if($match['logo_domicile']): ?>
                        <img src="uploads/logos/<?php echo htmlspecialchars($match['logo_domicile']); ?>" 
                             alt="<?php echo htmlspecialchars($match['club_domicile']); ?>" 
                             class="w-12 h-12 mx-auto mb-2 object-contain">
                        <?php else: ?>
                        <div class="w-12 h-12 mx-auto mb-2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <?php endif; ?>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($match['club_domicile']); ?>
                        </div>
                    </div>
                    <div class="text-center px-4">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?php echo $match['score_domicile']; ?> - <?php echo $match['score_exterieur']; ?>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <?php echo date('d/m/Y', strtotime($match['date_match'])); ?>
                        </div>
                    </div>
                    <div class="text-center flex-1">
                        <?php if($match['logo_exterieur']): ?>
                        <img src="uploads/logos/<?php echo htmlspecialchars($match['logo_exterieur']); ?>" 
                             alt="<?php echo htmlspecialchars($match['club_exterieur']); ?>" 
                             class="w-12 h-12 mx-auto mb-2 object-contain">
                        <?php else: ?>
                        <div class="w-12 h-12 mx-auto mb-2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <?php endif; ?>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($match['club_exterieur']); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Prochains Matchs -->
<section class="py-16 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-calendar mr-3 text-yellow-600"></i>
                Prochains Matchs
            </h2>
            <a href="calendrier.php" class="text-yellow-600 hover:text-yellow-700 font-semibold">
                Voir le calendrier <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($prochains_matchs as $match): ?>
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-6 card-hover animate-on-scroll">
                <div class="text-center mb-4">
                    <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-3 py-1 rounded-full text-sm font-medium">
                        <?php echo htmlspecialchars($match['categorie_nom']); ?>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-center flex-1">
                        <?php if($match['logo_domicile']): ?>
                        <img src="uploads/logos/<?php echo htmlspecialchars($match['logo_domicile']); ?>" 
                             alt="<?php echo htmlspecialchars($match['club_domicile']); ?>" 
                             class="w-12 h-12 mx-auto mb-2 object-contain">
                        <?php else: ?>
                        <div class="w-12 h-12 mx-auto mb-2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <?php endif; ?>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($match['club_domicile']); ?>
                        </div>
                    </div>
                    <div class="text-center px-4">
                        <div class="text-lg font-bold text-gray-900 dark:text-white">VS</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <?php echo date('d/m/Y H:i', strtotime($match['date_match'])); ?>
                        </div>
                        <?php if($match['stade']): ?>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <i class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($match['stade']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-center flex-1">
                        <?php if($match['logo_exterieur']): ?>
                        <img src="uploads/logos/<?php echo htmlspecialchars($match['logo_exterieur']); ?>" 
                             alt="<?php echo htmlspecialchars($match['club_exterieur']); ?>" 
                             class="w-12 h-12 mx-auto mb-2 object-contain">
                        <?php else: ?>
                        <div class="w-12 h-12 mx-auto mb-2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <?php endif; ?>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($match['club_exterieur']); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Meilleurs Buteurs -->
<section class="py-16 bg-gray-50 dark:bg-gray-800">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-trophy mr-3 text-red-600"></i>
                <span style="color: black;">Meilleurs Buteurs</span>
            </h2>
            <a href="joueurs.php?sort=buts" class="text-red-600 hover:text-red-700 font-semibold">
                Voir tous <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <?php foreach(array_slice($buteurs, 0, 5) as $index => $buteur): ?>
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-6 text-center card-hover animate-on-scroll">
                <div class="relative mb-4">
                    <?php if($buteur['photo']): ?>
                    <img src="uploads/photos/<?php echo htmlspecialchars($buteur['photo']); ?>" 
                         alt="<?php echo htmlspecialchars($buteur['prenom'] . ' ' . $buteur['nom']); ?>" 
                         class="w-20 h-20 mx-auto rounded-full object-cover">
                    <?php else: ?>
                    <div class="w-20 h-20 mx-auto bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-gray-400 text-2xl"></i>
                    </div>
                    <?php endif; ?>
                    <div class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">
                        <?php echo $index + 1; ?>
                    </div>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white mb-1">
                    <?php echo htmlspecialchars($buteur['prenom'] . ' ' . $buteur['nom']); ?>
                </h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">
                    <?php echo htmlspecialchars($buteur['club_nom']); ?>
                </p>
                <div class="text-2xl font-bold text-red-600">
                    <?php echo $buteur['total_buts']; ?>
                    <div class="text-sm font-normal text-gray-500 dark:text-gray-400">buts</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
// Charger les statistiques dynamiquement
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
});
</script>

<?php include 'includes/footer.php'; ?>