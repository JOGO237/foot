
<?php
require_once 'config/database.php';
$pageTitle = 'Clubs - Football Jeunes Cameroun';

// Filtres
$region_filter = isset($_GET['region']) ? $_GET['region'] : '';
$departement_filter = isset($_GET['departement']) ? $_GET['departement'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Construction de la requête
$where_conditions = [];
$params = [];

if ($region_filter) {
    $where_conditions[] = "r.id = ?";
    $params[] = $region_filter;
}

if ($departement_filter) {
    $where_conditions[] = "d.id = ?";
    $params[] = $departement_filter;
}

if ($search) {
    $where_conditions[] = "(c.nom LIKE ? OR c.president LIKE ? OR c.entraineur_principal LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Récupération des clubs
$sql = "SELECT c.*, d.nom as departement_nom, r.nom as region_nom, r.code as region_code,
               (SELECT COUNT(*) FROM joueurs j WHERE j.club_id = c.id AND j.statut = 'actif') as nb_joueurs
        FROM clubs c
        JOIN departements d ON c.departement_id = d.id
        JOIN regions r ON d.region_id = r.id
        $where_clause
        ORDER BY r.nom, d.nom, c.nom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clubs = $stmt->fetchAll();

// Récupération des régions pour le filtre
$regions = $pdo->query("SELECT * FROM regions ORDER BY nom")->fetchAll();

// Récupération des départements pour le filtre
$departements = $pdo->query("SELECT d.*, r.nom as region_nom FROM departements d JOIN regions r ON d.region_id = r.id ORDER BY r.nom, d.nom")->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-shield-alt mr-3 text-green-600"></i>
            <span style="color: #1f2937;">Academie du Cameroun</span>
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-400">
            Découvrez tous les clubs de football jeunes à travers le pays
        </p>
    </div>

    <!-- Filtres et Recherche -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="search-container">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rechercher</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Nom du club, président, entraîneur..." 
                       class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Région</label>
                <select name="region" id="regionFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Toutes les régions</option>
                    <?php foreach($regions as $region): ?>
                    <option value="<?php echo $region['id']; ?>" <?php echo $region_filter == $region['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($region['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Département</label>
                <select name="departement" id="departementFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous les départements</option>
                    <?php foreach($departements as $dept): ?>
                    <option value="<?php echo $dept['id']; ?>" <?php echo $departement_filter == $dept['id'] ? 'selected' : ''; ?> data-region="<?php echo $dept['region_id']; ?>">
                        <?php echo htmlspecialchars($dept['nom']) . ' (' . htmlspecialchars($dept['region_nom']) . ')'; ?>
                    </option>
                    <?php endforeach; ?>
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
            <?php echo count($clubs); ?> club(s) trouvé(s)
        </p>
    </div>

    <!-- Grille des clubs -->
    <div id="clubs-results" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($clubs as $club): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden card-hover animate-on-scroll">
            <!-- Header du club -->
            <div class="bg-gradient-to-r from-green-600 via-yellow-500 to-red-600 p-6 text-white text-center">
                <?php if($club['logo']): ?>
                <img src="uploads/logos/<?php echo htmlspecialchars($club['logo']); ?>" 
                     alt="<?php echo htmlspecialchars($club['nom']); ?>" 
                     class="w-20 h-20 mx-auto mb-4 object-contain bg-white rounded-full p-2">
                <?php else: ?>
                <div class="w-20 h-20 mx-auto mb-4 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-shield-alt text-3xl"></i>
                </div>
                <?php endif; ?>
                <h3 class="text-xl font-bold"><?php echo htmlspecialchars($club['nom']); ?></h3>
            </div>
            
            <!-- Informations du club -->
            <div class="p-6">
                <div class="space-y-3">
                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                        <i class="fas fa-map-marker-alt w-5 text-green-600"></i>
                        <span class="ml-2"><?php echo htmlspecialchars($club['departement_nom'] . ', ' . $club['region_nom']); ?></span>
                    </div>
                    
                    <?php if($club['president']): ?>
                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                        <i class="fas fa-user-tie w-5 text-yellow-600"></i>
                        <span class="ml-2">Président: <?php echo htmlspecialchars($club['president']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($club['entraineur_principal']): ?>
                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                        <i class="fas fa-whistle w-5 text-red-600"></i>
                        <span class="ml-2">Entraîneur: <?php echo htmlspecialchars($club['entraineur_principal']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                        <i class="fas fa-users w-5 text-blue-600"></i>
                        <span class="ml-2"><?php echo $club['nb_joueurs']; ?> joueur(s) actif(s)</span>
                    </div>
                    
                    <?php if($club['stade']): ?>
                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                        <i class="fas fa-building w-5 text-purple-600"></i>
                        <span class="ml-2">Stade: <?php echo htmlspecialchars($club['stade']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($club['date_creation']): ?>
                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                        <i class="fas fa-calendar w-5 text-indigo-600"></i>
                        <span class="ml-2">Fondé en <?php echo date('Y', strtotime($club['date_creation'])); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Contact -->
                <?php if($club['telephone'] || $club['email']): ?>
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <?php if($club['telephone']): ?>
                    <a href="tel:<?php echo htmlspecialchars($club['telephone']); ?>" 
                       class="inline-flex items-center text-green-600 hover:text-green-700 mr-4">
                        <i class="fas fa-phone mr-1"></i>
                        <?php echo htmlspecialchars($club['telephone']); ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if($club['email']): ?>
                    <a href="mailto:<?php echo htmlspecialchars($club['email']); ?>" 
                       class="inline-flex items-center text-blue-600 hover:text-blue-700">
                        <i class="fas fa-envelope mr-1"></i>
                        Email
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Actions -->
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="joueurs.php?club=<?php echo $club['id']; ?>" 
                       class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200 inline-block text-center">
                        <i class="fas fa-users mr-2"></i>Voir les joueurs
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if(empty($clubs)): ?>
    <div class="text-center py-12">
        <i class="fas fa-search text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">Aucun club trouvé</h3>
        <p class="text-gray-500 dark:text-gray-500">Essayez de modifier vos critères de recherche</p>
    </div>
    <?php endif; ?>
</div>

<script>
// Filtrage dynamique des départements par région
document.getElementById('regionFilter').addEventListener('change', function() {
    const selectedRegion = this.value;
    const departementSelect = document.getElementById('departementFilter');
    const options = departementSelect.querySelectorAll('option[data-region]');
    
    options.forEach(option => {
        if (!selectedRegion || option.dataset.region === selectedRegion) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Reset département selection if not compatible
    if (selectedRegion && departementSelect.value) {
        const selectedOption = departementSelect.querySelector(`option[value="${departementSelect.value}"]`);
        if (selectedOption && selectedOption.dataset.region !== selectedRegion) {
            departementSelect.value = '';
        }
    }
});

// Trigger au chargement pour l'état initial
document.getElementById('regionFilter').dispatchEvent(new Event('change'));
</script>

<?php include 'includes/footer.php'; ?>