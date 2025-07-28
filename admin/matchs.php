<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';
$pageTitle = 'Gestion des Matchs - Administration';

// Gestion des actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$match_id = isset($_GET['id']) ? $_GET['id'] : null;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_match'])) {
        $club_domicile_id = $_POST['club_domicile_id'];
        $club_exterieur_id = $_POST['club_exterieur_id'];
        $date_match = $_POST['date_match'];
        $stade = $_POST['stade'];
        $categorie_id = $_POST['categorie_id'];
        $saison_id = $_POST['saison_id'];
        $arbitre = $_POST['arbitre'];
        $observations = $_POST['observations'];
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO matchs (club_domicile_id, club_exterieur_id, date_match, stade, categorie_id, saison_id, arbitre, observations) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$club_domicile_id, $club_exterieur_id, $date_match, $stade, $categorie_id, $saison_id, $arbitre, $observations]);
            
            $_SESSION['success'] = 'Match programmé avec succès';
            header('Location: matchs.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur lors de la programmation du match';
        }
    } elseif (isset($_POST['edit_match'])) {
        $club_domicile_id = $_POST['club_domicile_id'];
        $club_exterieur_id = $_POST['club_exterieur_id'];
        $date_match = $_POST['date_match'];
        $stade = $_POST['stade'];
        $categorie_id = $_POST['categorie_id'];
        $saison_id = $_POST['saison_id'];
        $score_domicile = $_POST['score_domicile'] ?: null;
        $score_exterieur = $_POST['score_exterieur'] ?: null;
        $statut = $_POST['statut'];
        $arbitre = $_POST['arbitre'];
        $observations = $_POST['observations'];
        
        try {
            $stmt = $pdo->prepare("
                UPDATE matchs 
                SET club_domicile_id = ?, club_exterieur_id = ?, date_match = ?, stade = ?, categorie_id = ?, saison_id = ?, score_domicile = ?, score_exterieur = ?, statut = ?, arbitre = ?, observations = ?
                WHERE id = ?
            ");
            $stmt->execute([$club_domicile_id, $club_exterieur_id, $date_match, $stade, $categorie_id, $saison_id, $score_domicile, $score_exterieur, $statut, $arbitre, $observations, $match_id]);
            
            // Mise à jour des classements si le match est terminé
            if ($statut == 'terminé' && $score_domicile !== null && $score_exterieur !== null) {
                updateClassement($pdo, $club_domicile_id, $club_exterieur_id, $score_domicile, $score_exterieur, $categorie_id, $saison_id);
            }
            
            $_SESSION['success'] = 'Match modifié avec succès';
            header('Location: matchs.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur lors de la modification du match';
        }
    }
}

// Fonction pour mettre à jour le classement
function updateClassement($pdo, $club_domicile_id, $club_exterieur_id, $score_domicile, $score_exterieur, $categorie_id, $saison_id) {
    // Points: 3 pour victoire, 1 pour nul, 0 pour défaite
    if ($score_domicile > $score_exterieur) {
        $points_domicile = 3;
        $points_exterieur = 0;
        $victoires_domicile = 1;
        $victoires_exterieur = 0;
        $nuls_domicile = 0;
        $nuls_exterieur = 0;
        $defaites_domicile = 0;
        $defaites_exterieur = 1;
    } elseif ($score_domicile < $score_exterieur) {
        $points_domicile = 0;
        $points_exterieur = 3;
        $victoires_domicile = 0;
        $victoires_exterieur = 1;
        $nuls_domicile = 0;
        $nuls_exterieur = 0;
        $defaites_domicile = 1;
        $defaites_exterieur = 0;
    } else {
        $points_domicile = 1;
        $points_exterieur = 1;
        $victoires_domicile = 0;
        $victoires_exterieur = 0;
        $nuls_domicile = 1;
        $nuls_exterieur = 1;
        $defaites_domicile = 0;
        $defaites_exterieur = 0;
    }
    
    // Mise à jour pour l'équipe domicile
    $stmt = $pdo->prepare("
        INSERT INTO classements (club_id, categorie_id, saison_id, matchs_joues, victoires, nuls, defaites, buts_pour, buts_contre, points)
        VALUES (?, ?, ?, 1, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        matchs_joues = matchs_joues + 1,
        victoires = victoires + ?,
        nuls = nuls + ?,
        defaites = defaites + ?,
        buts_pour = buts_pour + ?,
        buts_contre = buts_contre + ?,
        points = points + ?
    ");
    $stmt->execute([
        $club_domicile_id, $categorie_id, $saison_id, $victoires_domicile, $nuls_domicile, $defaites_domicile, $score_domicile, $score_exterieur, $points_domicile,
        $victoires_domicile, $nuls_domicile, $defaites_domicile, $score_domicile, $score_exterieur, $points_domicile
    ]);
    
    // Mise à jour pour l'équipe extérieure
    $stmt->execute([
        $club_exterieur_id, $categorie_id, $saison_id, $victoires_exterieur, $nuls_exterieur, $defaites_exterieur, $score_exterieur, $score_domicile, $points_exterieur,
        $victoires_exterieur, $nuls_exterieur, $defaites_exterieur, $score_exterieur, $score_domicile, $points_exterieur
    ]);
}

// Suppression
if ($action == 'delete' && $match_id) {
    try {
        $pdo->prepare("DELETE FROM matchs WHERE id = ?")->execute([$match_id]);
        $_SESSION['success'] = 'Match supprimé avec succès';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erreur lors de la suppression';
    }
    header('Location: matchs.php');
    exit;
}

// Récupération des données
if ($action == 'edit' && $match_id) {
    $stmt = $pdo->prepare("SELECT * FROM matchs WHERE id = ?");
    $stmt->execute([$match_id]);
    $match = $stmt->fetch();
    
    if (!$match) {
        $_SESSION['error'] = 'Match introuvable';
        header('Location: matchs.php');
        exit;
    }
}

// Liste des matchs avec filtres
$search = isset($_GET['search']) ? $_GET['search'] : '';
$categorie_filter = isset($_GET['categorie']) ? $_GET['categorie'] : '';
$statut_filter = isset($_GET['statut']) ? $_GET['statut'] : '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(cd.nom LIKE ? OR ce.nom LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categorie_filter) {
    $where_conditions[] = "m.categorie_id = ?";
    $params[] = $categorie_filter;
}

if ($statut_filter) {
    $where_conditions[] = "m.statut = ?";
    $params[] = $statut_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$matchs = $pdo->prepare("
    SELECT m.*, 
           cd.nom as club_domicile, ce.nom as club_exterieur,
           c.nom as categorie_nom, s.nom as saison_nom
    FROM matchs m
    JOIN clubs cd ON m.club_domicile_id = cd.id
    JOIN clubs ce ON m.club_exterieur_id = ce.id
    JOIN categories c ON m.categorie_id = c.id
    JOIN saisons s ON m.saison_id = s.id
    $where_clause
    ORDER BY m.date_match DESC
");
$matchs->execute($params);
$matchs = $matchs->fetchAll();

// Données pour les formulaires
$clubs = $pdo->query("SELECT * FROM clubs ORDER BY nom")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY age_min")->fetchAll();
$saisons = $pdo->query("SELECT * FROM saisons ORDER BY date_debut DESC")->fetchAll();

include 'admin_header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                <i class="fas fa-futbol mr-3 text-yellow-600"></i>
                <span style="color: #1f2937;">Gestion des Matchs</span>
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Programmer et gérer les matchs
            </p>
        </div>
        
        <?php if ($action == 'list'): ?>
        <a href="?action=add" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
            <i class="fas fa-plus mr-2"></i>Programmer un Match
        </a>
        <?php else: ?>
        <a href="matchs.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
        <?php endif; ?>
    </div>

    <?php if ($action == 'list'): ?>
    <!-- Filtres -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rechercher</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Nom des clubs..." 
                       class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catégorie</label>
                <select name="categorie" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Toutes</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categorie_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut</label>
                <select name="statut" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous</option>
                    <option value="programmé" <?php echo $statut_filter == 'programmé' ? 'selected' : ''; ?>>Programmé</option>
                    <option value="en_cours" <?php echo $statut_filter == 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                    <option value="terminé" <?php echo $statut_filter == 'terminé' ? 'selected' : ''; ?>>Terminé</option>
                    <option value="reporté" <?php echo $statut_filter == 'reporté' ? 'selected' : ''; ?>>Reporté</option>
                    <option value="annulé" <?php echo $statut_filter == 'annulé' ? 'selected' : ''; ?>>Annulé</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>Rechercher
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des matchs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Matchs programmés (<?php echo count($matchs); ?>)
            </h3>
        </div>
        
        <div class="table-responsive">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Match
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Date/Heure
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Score
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Statut
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach($matchs as $match): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($match['club_domicile']); ?> vs <?php echo htmlspecialchars($match['club_exterieur']); ?>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo htmlspecialchars($match['categorie_nom']); ?> - <?php echo htmlspecialchars($match['saison_nom']); ?>
                                </div>
                                <?php if($match['stade']): ?>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($match['stade']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <?php echo date('d/m/Y H:i', strtotime($match['date_match'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?php if($match['score_domicile'] !== null && $match['score_exterieur'] !== null): ?>
                            <span class="text-lg font-bold text-gray-900 dark:text-white">
                                <?php echo $match['score_domicile']; ?> - <?php echo $match['score_exterieur']; ?>
                            </span>
                            <?php else: ?>
                            <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="status-badge status-<?php echo $match['statut']; ?>">
                                <?php echo ucfirst($match['statut']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                            <a href="?action=edit&id=<?php echo $match['id']; ?>" 
                               class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?action=delete&id=<?php echo $match['id']; ?>" 
                               class="text-red-600 hover:text-red-700 delete-btn">
                                <i class="fas fa-trash"></i>
                            </a>
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
            <?php echo $action == 'add' ? 'Programmer un nouveau match' : 'Modifier le match'; ?>
        </h3>
        
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Club domicile *
                    </label>
                    <select name="club_domicile_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Sélectionner le club domicile</option>
                        <?php foreach($clubs as $club): ?>
                        <option value="<?php echo $club['id']; ?>" <?php echo (isset($match) && $match['club_domicile_id'] == $club['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($club['nom']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Club extérieur *
                    </label>
                    <select name="club_exterieur_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Sélectionner le club extérieur</option>
                        <?php foreach($clubs as $club): ?>
                        <option value="<?php echo $club['id']; ?>" <?php echo (isset($match) && $match['club_exterieur_id'] == $club['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($club['nom']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Date et heure *
                    </label>
                    <input type="datetime-local" name="date_match" required 
                           value="<?php echo isset($match) ? date('Y-m-d\TH:i', strtotime($match['date_match'])) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Catégorie *
                    </label>
                    <select name="categorie_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($match) && $match['categorie_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nom']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Saison *
                    </label>
                    <select name="saison_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Sélectionner une saison</option>
                        <?php foreach($saisons as $saison): ?>
                        <option value="<?php echo $saison['id']; ?>" <?php echo (isset($match) && $match['saison_id'] == $saison['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($saison['nom']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Stade
                    </label>
                    <input type="text" name="stade" 
                           value="<?php echo isset($match) ? htmlspecialchars($match['stade']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Arbitre
                    </label>
                    <input type="text" name="arbitre" 
                           value="<?php echo isset($match) ? htmlspecialchars($match['arbitre']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <?php if ($action == 'edit'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Score domicile
                    </label>
                    <input type="number" name="score_domicile" min="0" 
                           value="<?php echo isset($match) ? $match['score_domicile'] : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Score extérieur
                    </label>
                    <input type="number" name="score_exterieur" min="0" 
                           value="<?php echo isset($match) ? $match['score_exterieur'] : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Statut
                    </label>
                    <select name="statut" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                        <option value="programmé" <?php echo (isset($match) && $match['statut'] == 'programmé') ? 'selected' : ''; ?>>Programmé</option>
                        <option value="en_cours" <?php echo (isset($match) && $match['statut'] == 'en_cours') ? 'selected' : ''; ?>>En cours</option>
                        <option value="terminé" <?php echo (isset($match) && $match['statut'] == 'terminé') ? 'selected' : ''; ?>>Terminé</option>
                        <option value="reporté" <?php echo (isset($match) && $match['statut'] == 'reporté') ? 'selected' : ''; ?>>Reporté</option>
                        <option value="annulé" <?php echo (isset($match) && $match['statut'] == 'annulé') ? 'selected' : ''; ?>>Annulé</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Observations
                </label>
                <textarea name="observations" rows="3" 
                          class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white"><?php echo isset($match) ? htmlspecialchars($match['observations']) : ''; ?></textarea>
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="matchs.php" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Annuler
                </a>
                <button type="submit" name="<?php echo $action == 'add' ? 'add_match' : 'edit_match'; ?>" 
                        class="px-6 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-semibold">
                    <?php echo $action == 'add' ? 'Programmer' : 'Modifier'; ?>
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php include 'admin_footer.php'; ?>