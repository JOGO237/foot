<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';
$pageTitle = 'Gestion des Sponsors - Administration';

// Gestion des actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$sponsor_id = isset($_GET['id']) ? $_GET['id'] : null;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_sponsor'])) {
        $nom = $_POST['nom'];
        $type = $_POST['type'];
        $description = $_POST['description'];
        $site_web = $_POST['site_web'];
        $contact = $_POST['contact'];
        $telephone = $_POST['telephone'];
        $email = $_POST['email'];
        
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
                INSERT INTO sponsors (nom, logo, type, description, site_web, contact, telephone, email) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nom, $logo, $type, $description, $site_web, $contact, $telephone, $email]);
            
            $_SESSION['success'] = 'Sponsor ajouté avec succès';
            header('Location: sponsors.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur lors de l\'ajout du sponsor';
        }
    } elseif (isset($_POST['edit_sponsor'])) {
        $nom = $_POST['nom'];
        $type = $_POST['type'];
        $description = $_POST['description'];
        $site_web = $_POST['site_web'];
        $contact = $_POST['contact'];
        $telephone = $_POST['telephone'];
        $email = $_POST['email'];
        $statut = $_POST['statut'];
        
        // Gestion du logo
        $logo_update = "";
        $params = [$nom, $type, $description, $site_web, $contact, $telephone, $email, $statut];
        
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
        
        $params[] = $sponsor_id;
        
        try {
            $stmt = $pdo->prepare("
                UPDATE sponsors 
                SET nom = ?, type = ?, description = ?, site_web = ?, contact = ?, telephone = ?, email = ?, statut = ?$logo_update 
                WHERE id = ?
            ");
            $stmt->execute($params);
            
            $_SESSION['success'] = 'Sponsor modifié avec succès';
            header('Location: sponsors.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erreur lors de la modification du sponsor';
        }
    }
}

// Suppression
if ($action == 'delete' && $sponsor_id) {
    try {
        $pdo->prepare("DELETE FROM sponsors WHERE id = ?")->execute([$sponsor_id]);
        $_SESSION['success'] = 'Sponsor supprimé avec succès';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erreur lors de la suppression';
    }
    header('Location: sponsors.php');
    exit;
}

// Récupération des données
if ($action == 'edit' && $sponsor_id) {
    $stmt = $pdo->prepare("SELECT * FROM sponsors WHERE id = ?");
    $stmt->execute([$sponsor_id]);
    $sponsor = $stmt->fetch();
    
    if (!$sponsor) {
        $_SESSION['error'] = 'Sponsor introuvable';
        header('Location: sponsors.php');
        exit;
    }
}

// Liste des sponsors
$sponsors = $pdo->query("SELECT * FROM sponsors ORDER BY type, nom")->fetchAll();

include 'admin_header.php';
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                <i class="fas fa-handshake mr-3 text-purple-600"></i>
                <span style="color: #1f2937;">Gestion des Sponsors</span>
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Gérer les sponsors, partenaires et investisseurs
            </p>
        </div>
        
        <?php if ($action == 'list'): ?>
        <a href="?action=add" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
            <i class="fas fa-plus mr-2"></i>Ajouter un Sponsor
        </a>
        <?php else: ?>
        <a href="sponsors.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
        <?php endif; ?>
    </div>

    <?php if ($action == 'list'): ?>
    <!-- Liste des sponsors -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Sponsors enregistrés (<?php echo count($sponsors); ?>)
            </h3>
        </div>
        
        <div class="table-responsive">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Sponsor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Contact
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
                    <?php foreach($sponsors as $sponsor): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <?php if($sponsor['logo']): ?>
                                <img src="../uploads/logos/<?php echo htmlspecialchars($sponsor['logo']); ?>" 
                                     alt="<?php echo htmlspecialchars($sponsor['nom']); ?>" 
                                     class="w-10 h-10 mr-3 object-contain">
                                <?php else: ?>
                                <div class="w-10 h-10 mr-3 bg-gray-200 dark:bg-gray-600 rounded"></div>
                                <?php endif; ?>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($sponsor['nom']); ?>
                                    </div>
                                    <?php if($sponsor['site_web']): ?>
                                    <div class="text-sm text-blue-600 hover:text-blue-700">
                                        <a href="<?php echo htmlspecialchars($sponsor['site_web']); ?>" target="_blank">
                                            <i class="fas fa-external-link-alt mr-1"></i>Site web
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php 
                                switch($sponsor['type']) {
                                    case 'sponsor': echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'; break;
                                    case 'partenaire': echo 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'; break;
                                    case 'investisseur': echo 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'; break;
                                }
                                ?>">
                                <?php echo ucfirst($sponsor['type']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <div>
                                <?php if($sponsor['contact']): ?>
                                <div><?php echo htmlspecialchars($sponsor['contact']); ?></div>
                                <?php endif; ?>
                                <?php if($sponsor['email']): ?>
                                <div class="text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($sponsor['email']); ?></div>
                                <?php endif; ?>
                                <?php if($sponsor['telephone']): ?>
                                <div class="text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($sponsor['telephone']); ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="status-badge <?php echo $sponsor['statut'] == 'actif' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; ?>">
                                <?php echo ucfirst($sponsor['statut']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                            <a href="?action=edit&id=<?php echo $sponsor['id']; ?>" 
                               class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?action=delete&id=<?php echo $sponsor['id']; ?>" 
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
            <?php echo $action == 'add' ? 'Ajouter un nouveau sponsor' : 'Modifier le sponsor'; ?>
        </h3>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nom *
                    </label>
                    <input type="text" name="nom" required 
                           value="<?php echo isset($sponsor) ? htmlspecialchars($sponsor['nom']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Type *
                    </label>
                    <select name="type" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Sélectionner un type</option>
                        <option value="sponsor" <?php echo (isset($sponsor) && $sponsor['type'] == 'sponsor') ? 'selected' : ''; ?>>Sponsor</option>
                        <option value="partenaire" <?php echo (isset($sponsor) && $sponsor['type'] == 'partenaire') ? 'selected' : ''; ?>>Partenaire</option>
                        <option value="investisseur" <?php echo (isset($sponsor) && $sponsor['type'] == 'investisseur') ? 'selected' : ''; ?>>Investisseur</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Contact
                    </label>
                    <input type="text" name="contact" 
                           value="<?php echo isset($sponsor) ? htmlspecialchars($sponsor['contact']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Téléphone
                    </label>
                    <input type="tel" name="telephone" 
                           value="<?php echo isset($sponsor) ? htmlspecialchars($sponsor['telephone']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email
                    </label>
                    <input type="email" name="email" 
                           value="<?php echo isset($sponsor) ? htmlspecialchars($sponsor['email']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Site web
                    </label>
                    <input type="url" name="site_web" 
                           value="<?php echo isset($sponsor) ? htmlspecialchars($sponsor['site_web']) : ''; ?>"
                           class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <?php if ($action == 'edit'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Statut
                    </label>
                    <select name="statut" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                        <option value="actif" <?php echo (isset($sponsor) && $sponsor['statut'] == 'actif') ? 'selected' : ''; ?>>Actif</option>
                        <option value="inactif" <?php echo (isset($sponsor) && $sponsor['statut'] == 'inactif') ? 'selected' : ''; ?>>Inactif</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Description
                </label>
                <textarea name="description" rows="3" 
                          class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white"><?php echo isset($sponsor) ? htmlspecialchars($sponsor['description']) : ''; ?></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Logo
                </label>
                <input type="file" name="logo" accept="image/*" 
                       class="w-full px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                <?php if (isset($sponsor) && $sponsor['logo']): ?>
                <div class="mt-2">
                    <img src="../uploads/logos/<?php echo htmlspecialchars($sponsor['logo']); ?>" 
                         alt="Logo actuel" class="w-20 h-20 object-contain">
                </div>
                <?php endif; ?>
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="sponsors.php" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Annuler
                </a>
                <button type="submit" name="<?php echo $action == 'add' ? 'add_sponsor' : 'edit_sponsor'; ?>" 
                        class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold">
                    <?php echo $action == 'add' ? 'Ajouter' : 'Modifier'; ?>
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php include 'admin_footer.php'; ?>