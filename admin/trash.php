<?php
session_start();
if (!isset($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';
$pageTitle = 'Corbeille - Administration';

// Créer les tables de corbeille si elles n'existent pas
$pdo->exec("
    CREATE TABLE IF NOT EXISTS trash_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        table_name VARCHAR(50) NOT NULL,
        item_id INT NOT NULL,
        item_data JSON NOT NULL,
        deleted_by INT NOT NULL,
        deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (deleted_by) REFERENCES admin_users(id)
    )
");

// Gestion des actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$trash_id = isset($_GET['id']) ? $_GET['id'] : null;

// Restauration
if ($action == 'restore' && $trash_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM trash_items WHERE id = ?");
        $stmt->execute([$trash_id]);
        $trash_item = $stmt->fetch();
        
        if ($trash_item) {
            $data = json_decode($trash_item['item_data'], true);
            $table = $trash_item['table_name'];
            
            // Construire la requête d'insertion
            $columns = array_keys($data);
            $placeholders = str_repeat('?,', count($columns) - 1) . '?';
            $sql = "INSERT INTO $table (" . implode(',', $columns) . ") VALUES ($placeholders)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));
            
            // Supprimer de la corbeille
            $pdo->prepare("DELETE FROM trash_items WHERE id = ?")->execute([$trash_id]);
            
            $_SESSION['success'] = 'Élément restauré avec succès';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erreur lors de la restauration : ' . $e->getMessage();
    }
    header('Location: trash.php');
    exit;
}

// Suppression définitive
if ($action == 'delete' && $trash_id) {
    try {
        $pdo->prepare("DELETE FROM trash_items WHERE id = ?")->execute([$trash_id]);
        $_SESSION['success'] = 'Élément supprimé définitivement';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erreur lors de la suppression';
    }
    header('Location: trash.php');
    exit;
}

// Vider la corbeille
if ($action == 'empty') {
    try {
        $pdo->exec("DELETE FROM trash_items");
        $_SESSION['success'] = 'Corbeille vidée avec succès';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erreur lors du vidage de la corbeille';
    }
    header('Location: trash.php');
    exit;
}

// Récupération des éléments de la corbeille
$trash_items = $pdo->query("
    SELECT t.*, u.username as deleted_by_user
    FROM trash_items t
    JOIN admin_users u ON t.deleted_by = u.id
    ORDER BY t.deleted_at DESC
")->fetchAll();

include 'admin_header.php';
?>

<!-- Wrapper pour s'assurer que le footer reste en bas -->
<div class="min-h-screen flex flex-col">
    <div class="flex-grow">
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        <i class="fas fa-trash-restore mr-3 text-red-600"></i>
                        <span style="color: #1f2937;">Corbeille</span>
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Restaurer ou supprimer définitivement les éléments
                    </p>
                </div>
                
                <?php if (!empty($trash_items)): ?>
                <button onclick="emptyTrash()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                    <i class="fas fa-trash mr-2"></i>Vider la Corbeille
                </button>
                <?php endif; ?>
            </div>

            <?php if (empty($trash_items)): ?>
            <!-- Corbeille vide -->
            <div class="text-center py-12">
                <i class="fas fa-trash text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 dark:text-gray-400 mb-2">Corbeille vide</h3>
                <p class="text-gray-500 dark:text-gray-500">Aucun élément supprimé à restaurer</p>
            </div>
            <?php else: ?>
            <!-- Liste des éléments dans la corbeille -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Éléments supprimés (<?php echo count($trash_items); ?>)
                    </h3>
                </div>
                
                <div class="table-responsive">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Nom/Identifiant
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Supprimé par
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Date de suppression
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach($trash_items as $item): ?>
                            <?php
                            $data = json_decode($item['item_data'], true);
                            $name = '';
                            $icon = '';
                            $color = '';
                            
                            switch($item['table_name']) {
                                case 'clubs':
                                    $name = $data['nom'];
                                    $icon = 'fas fa-shield-alt';
                                    $color = 'text-green-600';
                                    break;
                                case 'joueurs':
                                    $name = $data['prenom'] . ' ' . $data['nom'];
                                    $icon = 'fas fa-user';
                                    $color = 'text-blue-600';
                                    break;
                                case 'matchs':
                                    $name = 'Match #' . $data['id'];
                                    $icon = 'fas fa-futbol';
                                    $color = 'text-yellow-600';
                                    break;
                                case 'sponsors':
                                    $name = $data['nom'];
                                    $icon = 'fas fa-handshake';
                                    $color = 'text-purple-600';
                                    break;
                                default:
                                    $name = 'ID: ' . $data['id'];
                                    $icon = 'fas fa-file';
                                    $color = 'text-gray-600';
                            }
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="<?php echo $icon . ' ' . $color; ?> mr-3"></i>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo ucfirst($item['table_name']); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($name); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($item['deleted_by_user']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo date('d/m/Y H:i', strtotime($item['deleted_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                                    <button onclick="restoreItem(<?php echo $item['id']; ?>)" 
                                            class="text-green-600 hover:text-green-700 px-3 py-1 bg-green-100 hover:bg-green-200 rounded">
                                        <i class="fas fa-undo mr-1"></i>Restaurer
                                    </button>
                                    <button onclick="deleteItemPermanently(<?php echo $item['id']; ?>)" 
                                            class="text-red-600 hover:text-red-700 px-3 py-1 bg-red-100 hover:bg-red-200 rounded">
                                        <i class="fas fa-times mr-1"></i>Supprimer
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function restoreItem(id) {
        showConfirmDialog(
            'Êtes-vous sûr de vouloir restaurer cet élément ?',
            function() {
                window.location.href = `?action=restore&id=${id}`;
            }
        );
    }

    function deleteItemPermanently(id) {
        showConfirmDialog(
            'Êtes-vous sûr de vouloir supprimer définitivement cet élément ? Cette action est irréversible.',
            function() {
                window.location.href = `?action=delete&id=${id}`;
            }
        );
    }

    function emptyTrash() {
        showConfirmDialog(
            'Êtes-vous sûr de vouloir vider complètement la corbeille ? Cette action est irréversible.',
            function() {
                window.location.href = '?action=empty';
            }
        );
    }
    </script>
</div>

<?php include 'admin_footer.php'; ?>