<?php
/**
 * AsaanFix Pakistan - Service Model
 */
require_once __DIR__ . '/Model.php';

class ServiceModel extends Model {
    protected string $table = 'services';

    public function getWithCategory(int $id): ?array {
        return dbQueryOne(
            "SELECT s.*, c.name as category_name, c.icon as category_icon, c.slug as category_slug 
             FROM services s JOIN categories c ON s.category_id = c.id WHERE s.id = ?", [$id]
        );
    }

    public function findBySlug(string $slug): ?array {
        return dbQueryOne(
            "SELECT s.*, c.name as category_name, c.icon as category_icon, c.slug as category_slug 
             FROM services s JOIN categories c ON s.category_id = c.id WHERE s.slug = ?", [$slug]
        );
    }

    public function getByCategory(string $catSlug): array {
        return dbQuery(
            "SELECT s.*, c.name as category_name FROM services s 
             JOIN categories c ON s.category_id = c.id 
             WHERE c.slug = ? AND s.is_active = 1 ORDER BY s.name", [$catSlug]
        );
    }

    public function getPopular(int $limit = 6): array {
        return Cache::remember('popular_services_' . $limit, 1800, function() use ($limit) {
            return dbQuery(
                "SELECT s.*, c.name as category_name, c.icon as category_icon 
                 FROM services s JOIN categories c ON s.category_id = c.id 
                 WHERE s.is_active = 1 ORDER BY s.id ASC LIMIT ?", [$limit]
            );
        });
    }
}
