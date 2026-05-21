<?php
/**
 * AsaanFix Pakistan - Search API
 */
require_once __DIR__ . '/../includes/functions.php';

$q = sanitize($_GET['q'] ?? '');
if (strlen($q) < 2) jsonResponse(['results' => []]);

$services = dbQuery(
    "SELECT s.id, s.name, s.slug, s.base_price, c.name as category, c.icon 
     FROM services s JOIN categories c ON s.category_id = c.id 
     WHERE s.is_active = 1 AND (s.name LIKE ? OR c.name LIKE ?) 
     ORDER BY s.name LIMIT 5",
    ["%$q%", "%$q%"]
);

$techs = dbQuery(
    "SELECT t.id, u.name, u.city, t.avg_rating, t.total_jobs 
     FROM technicians t JOIN users u ON t.user_id = u.id 
     WHERE t.status = 'approved' AND u.is_active = 1 AND (u.name LIKE ? OR t.skills LIKE ?) 
     ORDER BY t.avg_rating DESC LIMIT 5",
    ["%$q%", "%$q%"]
);

jsonResponse([
    'results' => [
        'services' => array_map(fn($s) => [
            'id' => $s['id'], 'name' => $s['name'], 'slug' => $s['slug'],
            'price' => formatPrice($s['base_price']), 'category' => $s['category'], 'icon' => $s['icon'],
            'url' => APP_URL . '/service-detail.php?slug=' . $s['slug']
        ], $services),
        'technicians' => array_map(fn($t) => [
            'id' => $t['id'], 'name' => $t['name'], 'city' => $t['city'],
            'rating' => $t['avg_rating'], 'jobs' => $t['total_jobs'],
            'url' => APP_URL . '/technician-profile.php?id=' . $t['id']
        ], $techs),
    ]
]);
