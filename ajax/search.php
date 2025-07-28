<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$query = isset($_POST['query']) ? trim($_POST['query']) : '';
$type = isset($_POST['type']) ? $_POST['type'] : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];

try {
    switch ($type) {
        case 'clubs':
            $stmt = $pdo->prepare("
                SELECT c.*, d.nom as departement_nom, r.nom as region_nom
                FROM clubs c
                JOIN departements d ON c.departement_id = d.id
                JOIN regions r ON d.region_id = r.id
                WHERE c.nom LIKE ? OR c.president LIKE ? OR c.entraineur_principal LIKE ?
                ORDER BY c.nom
                LIMIT 10
            ");
            $stmt->execute(["%$query%", "%$query%", "%$query%"]);
            $results = $stmt->fetchAll();
            break;
            
        case 'joueurs':
            $stmt = $pdo->prepare("
                SELECT j.*, c.nom as club_nom
                FROM joueurs j
                JOIN clubs c ON j.club_id = c.id
                WHERE (j.nom LIKE ? OR j.prenom LIKE ? OR j.licence_numero LIKE ?) AND j.statut = 'actif'
                ORDER BY j.nom, j.prenom
                LIMIT 10
            ");
            $stmt->execute(["%$query%", "%$query%", "%$query%"]);
            $results = $stmt->fetchAll();
            break;
            
        case 'matchs':
            $stmt = $pdo->prepare("
                SELECT m.*, cd.nom as club_domicile, ce.nom as club_exterieur, c.nom as categorie_nom
                FROM matchs m
                JOIN clubs cd ON m.club_domicile_id = cd.id
                JOIN clubs ce ON m.club_exterieur_id = ce.id
                JOIN categories c ON m.categorie_id = c.id
                WHERE cd.nom LIKE ? OR ce.nom LIKE ?
                ORDER BY m.date_match DESC
                LIMIT 10
            ");
            $stmt->execute(["%$query%", "%$query%"]);
            $results = $stmt->fetchAll();
            break;
            
        default:
            echo json_encode(['error' => 'Invalid search type']);
            exit;
    }
    
    echo json_encode($results);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>