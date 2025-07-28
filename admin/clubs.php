<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';
$pageTitle = 'Gestion des Clubs - Administration';

// Gestion des actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$club_id = isset($_GET['id']) ? $_GET['id'] : null;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_club'])) {
        $nom = $_POST['nom'];
        $adresse = $_POST['adresse'];
        $telephone = $_POST['telephone'];
        $email = $_POST['email'];
        $departement_id = $_POST['departement_id'];
        $date_creation = $_POST['date_creation'];
        $president = $_POST['president'];
        $entraineur_principal = $_POST['entraineur_principal'];
        $stade = $_POST['stade'];
        
        // Gestion du logo
        $logo = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $upload_dir = '../uploads/logos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $logo = uniqid() . '.' . $file_extension;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo)) {
                // Succès
            } else {
                $logo = null;
            }
        }
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO clubs (nom, logo, adresse, telephone, email, departement_id, date_creation, president, entraineur_principal, stade) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nom, $logo, $adresse, $telephone, $email, $departement_id, $date_creation, $president, $entraineur_principal, $stade]);
            
            $_SESSION['success'] = 'Club ajouté avec succès';
            header('Location: clubs.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur lors de l\'ajout du club';
        }
    } elseif (isset($_POST['edit_club'])) {
        $nom = $_POST['nom'];
        $adresse = $_POST['adresse'];
        $telephone = $_POST['telephone'];
        $email = $_POST['email'];
        $departement_id = $_POST['departement_id'];
        $date_creation = $_POST['date_creation'];
        $president = $_POST['president'];
        $entraineur_principal = $_POST['entraineur_principal'];
        $stade = $_POST['stade'];
        
        // Gestion du logo
        $logo_update = "";
        $params = [$nom, $adresse, $telephone, $email, $departement_id, $date_creation, $president, $entraineur_principal, $stade];
        
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $upload_dir = '../uploads/logos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $logo = uniqid() . '.' . $file_extension;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo)) {
                $logo_update = ", logo = ?";
                $params[] = $logo;
            }
        }
        
        $params[] = $club_id;
        
        try {
            $stmt = $pdo->prepare("
                UPDATE clubs 
                SET nom = ?, adresse = ?, telephone = ?, email = ?, departement_id = ?, date_creation = ?, president = ?, entraineur_principal = ?, stade = ?$logo_update 
                WHERE id = ?
            ");
            $stmt->execute($params);
            
            $_SESSION['success'] = 'Club modifié avec succès';
            header('Location: clubs.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur lors de la modification du club';
        }
    }
}

// Suppression
if ($action == 'delete' && $club_id) {
    try {
        // Vérifier s'il y a des joueurs liés
        $count = $pdo->prepare("SELECT COUNT(*) FROM joueurs WHERE club_id = ?")->execute([$club_id]);
        if ($count > 0) {
            $_SESSION['error'] = 'Impossible de supprimer un club ayant des joueurs';
        } else {
            $pdo->prepare("DELETE FROM clubs WHERE id = ?")->execute([$club_id]);
            $_SESSION['success'] = 'Club supprimé avec succès';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erreur lors de la suppression';
    }
    header('Location: clubs.php');
    exit;
}

// Récupération des données
if ($action == 'edit' && $club_id) {
    $stmt = $pdo->prepare("SELECT * FROM clubs WHERE id = ?");
    $stmt->execute([$club_id]);
    $club = $stmt->fetch();
    
    if (!$club) {
        $_SESSION['error'] = 'Club introuvable';
        header('Location: clubs.php');
        exit;
    }
}

// Liste des clubs
$clubs = $pdo->query("
    SELECT c.*, d.nom as departement_nom, r.nom as region_nom,
           (SELECT COUNT(*) FROM joueurs j WHERE j.club_id = c.id AND j.statut = 'actif') as nb_joueurs
    FROM clubs c
    JOIN departements d ON c.departement_id = d.id
    JOIN regions r ON d.region_id = r.id
    ORDER BY r.nom, d.nom, c.nom
")->fetchAll();

// Départements pour le formulaire
$departements = $pdo->query("
    SELECT d.*, r.nom as region_nom 
    FROM departements d 
    JOIN regions r ON d.region_id = r.id 
    ORDER BY r.nom, d.nom
")->fetchAll();

include 'admin_header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                <i class="fas fa-shield-alt mr-3 text-green-600"></i>
                <span style="color: #1f2937;">Gestion des Clubs</span>
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Gérer les clubs de football jeunes
            </p>
        </div>
        
        <?php if ($action == 'list'): ?>
        <a href="?action=add" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
            <i class="fas fa-plus mr-2"></i>Ajouter un Club
        </a>
        <?php else: ?>
        <a href="clubs.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
        <?php endif; ?>
    </div>

    <?php if ($action == 'list'): ?>
    <!-- Liste des clubs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Clubs enregistrés (<?php echo count($clubs); ?>)
            </h3>
        </div>
        
        <div class="table-responsive">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Club
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Localisation
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Président
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Joueurs
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach($clubs as $club): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <?php if($club['logo']): ?>
                                <img src="../uploads/logos/<?php echo htmlspecialchars($club['logo']); ?>" 
                                     alt="<?php echo htmlspecialchars($club['nom']); ?>" 
                                     class="w-10 h-10 mr-3 object-contain">
                                <?php else: ?>
                                <div class="w-10 h-10 mr-3 bg-gray-200 dark:bg-gray-600 rounded"></div>
                                <?php endif; ?>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($club['nom']); ?>
                                    </div>
                                    <?php if($club['email']): ?>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo htmlspecialchars($club['email']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($club['departement_nom'] . ', ' . $club['region_nom']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($club['president'] ?: 'Non défini'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm font-medium text-blue-600">
                                <?php echo $club['nb_joueurs']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                            <a href="?action=edit&id=<?php echo $club['id']; ?>" 
                               class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?action=delete&id=<?php echo $club['id']; ?>" 
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
            <?php echo $action == 'add' ? 'Ajouter un nouveau club' : 'Modifier le club'; ?>
        </h3>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nom du club *
                    </label>
                    <input type="text" name="nom" required 
                           value="<?php echo isset($club) ? htmlspecialchars($club['nom']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Département *
                    </label>
                    <select name="departement_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Sélectionner un département</option>
                        <?php foreach($departements as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo (isset($club) && $club['departement_id'] == $dept['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['nom'] . ' (' . $dept['region_nom'] . ')'); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Président
                    </label>
                    <input type="text" name="president" 
                           value="<?php echo isset($club) ? htmlspecialchars($club['president']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Entraîneur principal
                    </label>
                    <input type="text" name="entraineur_principal" 
                           value="<?php echo isset($club) ? htmlspecialchars($club['entraineur_principal']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Téléphone
                    </label>
                    <input type="tel" name="telephone" 
                           value="<?php echo isset($club) ? htmlspecialchars($club['telephone']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email
                    </label>
                    <input type="email" name="email" 
                           value="<?php echo isset($club) ? htmlspecialchars($club['email']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Stade
                    </label>
                    <input type="text" name="stade" 
                           value="<?php echo isset($club) ? htmlspecialchars($club['stade']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Date de création
                    </label>
                    <input type="date" name="date_creation" 
                           value="<?php echo isset($club) ? $club['date_creation'] : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Adresse
                </label>
                <textarea name="adresse" rows="3" 
                          class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white"><?php echo isset($club) ? htmlspecialchars($club['adresse']) : ''; ?></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Logo du club
                </label>
                <input type="file" name="logo" accept="image/*" 
                       class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                <?php if (isset($club) && $club['logo']): ?>
                <div class="mt-2">
                    <img src="../uploads/logos/<?php echo htmlspecialchars($club['logo']); ?>" 
                         alt="Logo actuel" class="w-20 h-20 object-contain">
                </div>
                <?php endif; ?>
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="clubs.php" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Annuler
                </a>
                <button type="submit" name="<?php echo $action == 'add' ? 'add_club' : 'edit_club'; ?>" 
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold">
                    <?php echo $action == 'add' ? 'Ajouter' : 'Modifier'; ?>
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php include 'admin_footer.php'; ?>