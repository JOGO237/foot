<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Récupérer les matchs en cours ou récemment terminés
    $stmt = $pdo->query("
        SELECT m.id, m.score_domicile, m.score_exterieur, m.statut, m.date_match,
               cd.nom as club_domicile, ce.nom as club_exterieur, c.nom as categorie_nom
        FROM matchs m
        JOIN clubs cd ON m.club_domicile_id = cd.id
        JOIN clubs ce ON m.club_exterieur_id = ce.id
        JOIN categories c ON m.categorie_id = c.id
        WHERE m.statut IN ('en_cours', 'terminé') 
        AND m.date_match >= DATE_SUB(NOW(), INTERVAL 3 HOUR)
        ORDER BY m.date_match DESC
    ");
    
    $matches = $stmt->fetchAll();
    
    // Simuler quelques changements de score pour les matchs en cours (pour la démo)
    foreach ($matches as &$match) {
        if ($match['statut'] == 'en_cours') {
            // Simuler des changements aléatoires de score
            $random_change = rand(0, 100);
            if ($random_change < 5) { // 5% de chance de changement
                if (rand(0, 1)) {
                    $match['score_domicile'] = max(0, $match['score_domicile'] + 1);
                } else {
                    $match['score_exterieur'] = max(0, $match['score_exterieur'] + 1);
                }
            }
        }
    }
    
    echo json_encode($matches);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>