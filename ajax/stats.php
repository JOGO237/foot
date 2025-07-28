<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $stats = [];
    
    // Statistiques générales
    $stats['clubs'] = $pdo->query("SELECT COUNT(*) FROM clubs")->fetchColumn();
    $stats['joueurs'] = $pdo->query("SELECT COUNT(*) FROM joueurs WHERE statut = 'actif'")->fetchColumn();
    $stats['matchs'] = $pdo->query("SELECT COUNT(*) FROM matchs")->fetchColumn();
    $stats['saisons'] = $pdo->query("SELECT COUNT(*) FROM saisons")->fetchColumn();
    
    // Statistiques par région
    $stats['regions'] = $pdo->query("
        SELECT r.nom, COUNT(c.id) as nb_clubs
        FROM regions r
        LEFT JOIN departements d ON r.id = d.region_id
        LEFT JOIN clubs c ON d.id = c.departement_id
        GROUP BY r.id, r.nom
        ORDER BY nb_clubs DESC
    ")->fetchAll();
    
    // Statistiques par catégorie
    $stats['categories'] = $pdo->query("
        SELECT cat.nom, COUNT(j.id) as nb_joueurs
        FROM categories cat
        LEFT JOIN joueurs j ON cat.id = j.categorie_id AND j.statut = 'actif'
        GROUP BY cat.id, cat.nom
        ORDER BY cat.age_min
    ")->fetchAll();
    
    // Matchs par statut
    $stats['matchs_statut'] = $pdo->query("
        SELECT statut, COUNT(*) as count
        FROM matchs
        GROUP BY statut
        ORDER BY statut
    ")->fetchAll();
    
    // Top 5 clubs avec le plus de joueurs
    $stats['top_clubs'] = $pdo->query("
        SELECT c.nom, COUNT(j.id) as nb_joueurs
        FROM clubs c
        LEFT JOIN joueurs j ON c.id = j.club_id AND j.statut = 'actif'
        GROUP BY c.id, c.nom
        ORDER BY nb_joueurs DESC
        LIMIT 5
    ")->fetchAll();
    
    echo json_encode($stats);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>