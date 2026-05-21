<?php
/**
 * AsaanFix Pakistan - Dynamic Sitemap Generator
 * Generates XML sitemap from database content
 */

require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = APP_URL;
$today = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Static Pages -->
    <url><loc><?= $baseUrl ?>/</loc><lastmod><?= $today ?></lastmod><changefreq>daily</changefreq><priority>1.0</priority></url>
    <url><loc><?= $baseUrl ?>/services</loc><lastmod><?= $today ?></lastmod><changefreq>weekly</changefreq><priority>0.9</priority></url>
    <url><loc><?= $baseUrl ?>/technicians</loc><lastmod><?= $today ?></lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>
    <url><loc><?= $baseUrl ?>/about</loc><lastmod><?= $today ?></lastmod><changefreq>monthly</changefreq><priority>0.7</priority></url>
    <url><loc><?= $baseUrl ?>/contact</loc><lastmod><?= $today ?></lastmod><changefreq>monthly</changefreq><priority>0.7</priority></url>
    <url><loc><?= $baseUrl ?>/faq</loc><lastmod><?= $today ?></lastmod><changefreq>monthly</changefreq><priority>0.6</priority></url>

    <!-- Dynamic: Categories -->
<?php
$categories = dbQuery("SELECT slug, updated_at FROM categories WHERE is_active = 1 ORDER BY sort_order ASC");
foreach ($categories as $cat):
?>
    <url><loc><?= $baseUrl ?>/services?cat=<?= $cat['slug'] ?></loc><lastmod><?= date('Y-m-d', strtotime($cat['updated_at'])) ?></lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>
<?php endforeach; ?>

    <!-- Dynamic: Services -->
<?php
$services = dbQuery("SELECT slug, updated_at FROM services WHERE is_active = 1 ORDER BY id ASC");
foreach ($services as $svc):
?>
    <url><loc><?= $baseUrl ?>/service-detail?slug=<?= $svc['slug'] ?></loc><lastmod><?= date('Y-m-d', strtotime($svc['updated_at'])) ?></lastmod><changefreq>weekly</changefreq><priority>0.7</priority></url>
<?php endforeach; ?>

    <!-- Dynamic: Technician Profiles -->
<?php
$techs = dbQuery("SELECT t.id, t.updated_at FROM technicians t WHERE t.status = 'approved' ORDER BY t.id ASC");
foreach ($techs as $tech):
?>
    <url><loc><?= $baseUrl ?>/technician-profile?id=<?= $tech['id'] ?></loc><lastmod><?= date('Y-m-d', strtotime($tech['updated_at'])) ?></lastmod><changefreq>weekly</changefreq><priority>0.6</priority></url>
<?php endforeach; ?>
</urlset>
