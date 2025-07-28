<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['match_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Match ID required']);
    exit;
}

$match_id = $_GET['match_id'];

try {
    // Récupérer les statistiques des joueurs pour ce match
    $stmt = $pdo->prepare("
        SELECT s.*, j.nom, j.prenom, c.nom as club_nom
        FROM stats_joueurs s
        JOIN joueurs j ON s.joueur_id = j.id
        JOIN clubs c ON j.club_id = c.id
        WHERE s.match_id = ?
        ORDER BY c.nom, j.nom, j.prenom
    ");
    $stmt->execute([$match_id]);
    $stats = $stmt->fetchAll();
    
    echo json_encode($stats);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>