<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';
$pageTitle = 'Gestion des Joueurs - Administration';

// Gestion des actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$joueur_id = isset($_GET['id']) ? $_GET['id'] : null;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_joueur'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $date_naissance = $_POST['date_naissance'];
        $poste = $_POST['poste'];
        $numero_maillot = $_POST['numero_maillot'] ?: null;
        $club_id = $_POST['club_id'];
        $categorie_id = $_POST['categorie_id'];
        $taille = $_POST['taille'] ?: null;
        $poids = $_POST['poids'] ?: null;
        $telephone = $_POST['telephone'];
        $adresse = $_POST['adresse'];
        $nom_parent = $_POST['nom_parent'];
        $telephone_parent = $_POST['telephone_parent'];
        $licence_numero = $_POST['licence_numero'];
        
        // Gestion de la photo
        $photo = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $upload_dir = '../uploads/photos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photo = uniqid() . '.' . $file_extension;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo)) {
                // Succès
            } else {
                $photo = null;
            }
        }
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO joueurs (nom, prenom, date_naissance, poste, numero_maillot, photo, club_id, categorie_id, taille, poids, telephone, adresse, nom_parent, telephone_parent, licence_numero) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nom, $prenom, $date_naissance, $poste, $numero_maillot, $photo, $club_id, $categorie_id, $taille, $poids, $telephone, $adresse, $nom_parent, $telephone_parent, $licence_numero]);
            
            $_SESSION['success'] = 'Joueur ajouté avec succès';
            header('Location: joueurs.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur lors de l\'ajout du joueur';
        }
    } elseif (isset($_POST['edit_joueur'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $date_naissance = $_POST['date_naissance'];
        $poste = $_POST['poste'];
        $numero_maillot = $_POST['numero_maillot'] ?: null;
        $club_id = $_POST['club_id'];
        $categorie_id = $_POST['categorie_id'];
        $taille = $_POST['taille'] ?: null;
        $poids = $_POST['poids'] ?: null;
        $telephone = $_POST['telephone'];
        $adresse = $_POST['adresse'];
        $nom_parent = $_POST['nom_parent'];
        $telephone_parent = $_POST['telephone_parent'];
        $licence_numero = $_POST['licence_numero'];
        $statut = $_POST['statut'];
        
        // Gestion de la photo
        $photo_update = "";
        $params = [$nom, $prenom, $date_naissance, $poste, $numero_maillot, $club_id, $categorie_id, $taille, $poids, $telephone, $adresse, $nom_parent, $telephone_parent, $licence_numero, $statut];
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $upload_dir = '../uploads/photos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photo = uniqid() . '.' . $file_extension;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo)) {
                $photo_update = ", photo = ?";
                $params[] = $photo;
            }
        }
        
        $params[] = $joueur_id;
        
        try {
            $stmt = $pdo->prepare("
                UPDATE joueurs 
                SET nom = ?, prenom = ?, date_naissance = ?, poste = ?, numero_maillot = ?, club_id = ?, categorie_id = ?, taille = ?, poids = ?, telephone = ?, adresse = ?, nom_parent = ?, telephone_parent = ?, licence_numero = ?, statut = ?$photo_update 
                WHERE id = ?
            ");
            $stmt->execute($params);
            
            $_SESSION['success'] = 'Joueur modifié avec succès';
            header('Location: joueurs.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur lors de la modification du joueur';
        }
    }
}

// Suppression
if ($action == 'delete' && $joueur_id) {
    try {
        $pdo->prepare("DELETE FROM joueurs WHERE id = ?")->execute([$joueur_id]);
        $_SESSION['success'] = 'Joueur supprimé avec succès';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erreur lors de la suppression';
    }
    header('Location: joueurs.php');
    exit;
}

// Récupération des données
if ($action == 'edit' && $joueur_id) {
    $stmt = $pdo->prepare("SELECT * FROM joueurs WHERE id = ?");
    $stmt->execute([$joueur_id]);
    $joueur = $stmt->fetch();
    
    if (!$joueur) {
        $_SESSION['error'] = 'Joueur introuvable';
        header('Location: joueurs.php');
        exit;
    }
}

// Liste des joueurs avec filtres
$search = isset($_GET['search']) ? $_GET['search'] : '';
$club_filter = isset($_GET['club']) ? $_GET['club'] : '';
$categorie_filter = isset($_GET['categorie']) ? $_GET['categorie'] : '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(j.nom LIKE ? OR j.prenom LIKE ? OR j.licence_numero LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($club_filter) {
    $where_conditions[] = "j.club_id = ?";
    $params[] = $club_filter;
}

if ($categorie_filter) {
    $where_conditions[] = "j.categorie_id = ?";
    $params[] = $categorie_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$joueurs = $pdo->prepare("
    SELECT j.*, c.nom as club_nom, cat.nom as categorie_nom,
           YEAR(CURDATE()) - YEAR(j.date_naissance) as age
    FROM joueurs j
    JOIN clubs c ON j.club_id = c.id
    JOIN categories cat ON j.categorie_id = cat.id
    $where_clause
    ORDER BY j.nom, j.prenom
");
$joueurs->execute($params);
$joueurs = $joueurs->fetchAll();

// Données pour les formulaires
$clubs = $pdo->query("SELECT * FROM clubs ORDER BY nom")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY age_min")->fetchAll();

include 'admin_header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                <i class="fas fa-users mr-3 text-blue-600"></i>
                <span style="color: #1f2937;">Gestion des Joueurs</span>
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Gérer les joueurs de football jeunes
            </p>
        </div>
        
        <?php if ($action == 'list'): ?>
        <a href="?action=add" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
            <i class="fas fa-plus mr-2"></i>Ajouter un Joueur
        </a>
        <?php else: ?>
        <a href="joueurs.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
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
                       placeholder="Nom, prénom, licence..." 
                       class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Club</label>
                <select name="club" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
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
                <select name="categorie" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Toutes</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categorie_filter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>Rechercher
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des joueurs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Joueurs enregistrés (<?php echo count($joueurs); ?>)
            </h3>
        </div>
        
        <div class="table-responsive">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Joueur
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Club
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Âge
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Poste
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            N°
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
                    <?php foreach($joueurs as $joueur): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <?php if($joueur['photo']): ?>
                                <img src="../uploads/photos/<?php echo htmlspecialchars($joueur['photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($joueur['prenom'] . ' ' . $joueur['nom']); ?>" 
                                     class="w-10 h-10 mr-3 rounded-full object-cover">
                                <?php else: ?>
                                <div class="w-10 h-10 mr-3 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($joueur['prenom'] . ' ' . $joueur['nom']); ?>
                                    </div>
                                    <?php if($joueur['licence_numero']): ?>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        Licence: <?php echo htmlspecialchars($joueur['licence_numero']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($joueur['club_nom']); ?></div>
                            <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($joueur['categorie_nom']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-white">
                            <?php echo $joueur['age']; ?> ans
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($joueur['poste'] ?: 'Non défini'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?php if($joueur['numero_maillot']): ?>
                            <span class="text-sm font-medium text-blue-600">
                                <?php echo $joueur['numero_maillot']; ?>
                            </span>
                            <?php else: ?>
                            <span class="text-sm text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="status-badge status-<?php echo $joueur['statut']; ?>">
                                <?php echo ucfirst($joueur['statut']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                            <a href="?action=edit&id=<?php echo $joueur['id']; ?>" 
                               class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?action=delete&id=<?php echo $joueur['id']; ?>" 
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
            <?php echo $action == 'add' ? 'Ajouter un nouveau joueur' : 'Modifier le joueur'; ?>
        </h3>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Prénom *
                    </label>
                    <input type="text" name="prenom" required 
                           value="<?php echo isset($joueur) ? htmlspecialchars($joueur['prenom']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nom *
                    </label>
                    <input type="text" name="nom" required 
                           value="<?php echo isset($joueur) ? htmlspecialchars($joueur['nom']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Date de naissance *
                    </label>
                    <input type="date" name="date_naissance" required 
                           value="<?php echo isset($joueur) ? $joueur['date_naissance'] : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Club *
                    </label>
                    <select name="club_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Sélectionner un club</option>
                        <?php foreach($clubs as $club): ?>
                        <option value="<?php echo $club['id']; ?>" <?php echo (isset($joueur) && $joueur['club_id'] == $club['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($club['nom']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Catégorie *
                    </label>
                    <select name="categorie_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($joueur) && $joueur['categorie_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nom']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Poste
                    </label>
                    <select name="poste" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Sélectionner un poste</option>
                        <?php 
                        $postes = ['Gardien', 'Défenseur central', 'Défenseur latéral', 'Milieu défensif', 'Milieu central', 'Milieu offensif', 'Ailier', 'Attaquant'];
                        foreach($postes as $poste_option): 
                        ?>
                        <option value="<?php echo $poste_option; ?>" <?php echo (isset($joueur) && $joueur['poste'] == $poste_option) ? 'selected' : ''; ?>>
                            <?php echo $poste_option; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Numéro de maillot
                    </label>
                    <input type="number" name="numero_maillot" min="1" max="99" 
                           value="<?php echo isset($joueur) ? $joueur['numero_maillot'] : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Taille (m)
                    </label>
                    <input type="number" name="taille" step="0.01" min="1" max="2.5" 
                           value="<?php echo isset($joueur) ? $joueur['taille'] : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Poids (kg)
                    </label>
                    <input type="number" name="poids" step="0.1" min="20" max="150" 
                           value="<?php echo isset($joueur) ? $joueur['poids'] : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Téléphone
                    </label>
                    <input type="tel" name="telephone" 
                           value="<?php echo isset($joueur) ? htmlspecialchars($joueur['telephone']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nom du parent/tuteur
                    </label>
                    <input type="text" name="nom_parent" 
                           value="<?php echo isset($joueur) ? htmlspecialchars($joueur['nom_parent']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Téléphone parent
                    </label>
                    <input type="tel" name="telephone_parent" 
                           value="<?php echo isset($joueur) ? htmlspecialchars($joueur['telephone_parent']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Numéro de licence
                    </label>
                    <input type="text" name="licence_numero" 
                           value="<?php echo isset($joueur) ? htmlspecialchars($joueur['licence_numero']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <?php if ($action == 'edit'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Statut
                    </label>
                    <select name="statut" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="actif" <?php echo (isset($joueur) && $joueur['statut'] == 'actif') ? 'selected' : ''; ?>>Actif</option>
                        <option value="inactif" <?php echo (isset($joueur) && $joueur['statut'] == 'inactif') ? 'selected' : ''; ?>>Inactif</option>
                        <option value="blessé" <?php echo (isset($joueur) && $joueur['statut'] == 'blessé') ? 'selected' : ''; ?>>Blessé</option>
                        <option value="suspendu" <?php echo (isset($joueur) && $joueur['statut'] == 'suspendu') ? 'selected' : ''; ?>>Suspendu</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Adresse
                </label>
                <textarea name="adresse" rows="3" 
                          class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"><?php echo isset($joueur) ? htmlspecialchars($joueur['adresse']) : ''; ?></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Photo du joueur
                </label>
                <input type="file" name="photo" accept="image/*" 
                       class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                <?php if (isset($joueur) && $joueur['photo']): ?>
                <div class="mt-2">
                    <img src="../uploads/photos/<?php echo htmlspecialchars($joueur['photo']); ?>" 
                         alt="Photo actuelle" class="w-20 h-20 object-cover rounded-full">
                </div>
                <?php endif; ?>
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="joueurs.php" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Annuler
                </a>
                <button type="submit" name="<?php echo $action == 'add' ? 'add_joueur' : 'edit_joueur'; ?>" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                    <?php echo $action == 'add' ? 'Ajouter' : 'Modifier'; ?>
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php include 'admin_footer.php'; ?>