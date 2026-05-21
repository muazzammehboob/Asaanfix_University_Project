<?php
/**
 * AsaanFix Pakistan - SEO Helper
 * Dynamic meta tags, Open Graph, structured data
 */

class SEO {
    private static array $meta = [];

    public static function init(array $options = []): void {
        self::$meta = array_merge([
            'title'       => APP_NAME,
            'description' => 'Book trusted technicians for mobile repair, electrician, plumbing, AC services across Pakistan.',
            'keywords'    => 'fixithub, technician, repair, plumbing, electrician, pakistan, home services',
            'url'         => APP_URL . ($_SERVER['REQUEST_URI'] ?? '/'),
            'image'       => ASSETS_URL . '/images/og-banner.png',
            'type'        => 'website',
            'locale'      => 'en_PK',
            'siteName'    => APP_NAME,
        ], $options);
    }

    public static function renderMeta(): string {
        if (empty(self::$meta)) self::init();
        $m = self::$meta;
        $html = '';

        // Standard meta
        $html .= '<meta name="description" content="' . htmlspecialchars($m['description']) . '">' . "\n";
        $html .= '<meta name="keywords" content="' . htmlspecialchars($m['keywords']) . '">' . "\n";
        $html .= '<meta name="author" content="' . htmlspecialchars($m['siteName']) . '">' . "\n";
        $html .= '<link rel="canonical" href="' . htmlspecialchars($m['url']) . '">' . "\n";

        // Open Graph
        $html .= '<meta property="og:title" content="' . htmlspecialchars($m['title']) . '">' . "\n";
        $html .= '<meta property="og:description" content="' . htmlspecialchars($m['description']) . '">' . "\n";
        $html .= '<meta property="og:type" content="' . $m['type'] . '">' . "\n";
        $html .= '<meta property="og:url" content="' . htmlspecialchars($m['url']) . '">' . "\n";
        $html .= '<meta property="og:image" content="' . htmlspecialchars($m['image']) . '">' . "\n";
        $html .= '<meta property="og:locale" content="' . $m['locale'] . '">' . "\n";
        $html .= '<meta property="og:site_name" content="' . htmlspecialchars($m['siteName']) . '">' . "\n";

        // Twitter Card
        $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        $html .= '<meta name="twitter:title" content="' . htmlspecialchars($m['title']) . '">' . "\n";
        $html .= '<meta name="twitter:description" content="' . htmlspecialchars($m['description']) . '">' . "\n";
        $html .= '<meta name="twitter:image" content="' . htmlspecialchars($m['image']) . '">' . "\n";

        // Google verification
        $gv = env('GOOGLE_SITE_VERIFICATION', '');
        if ($gv) $html .= '<meta name="google-site-verification" content="' . $gv . '">' . "\n";

        return $html;
    }

    public static function renderJsonLd(): string {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => APP_NAME,
            'url' => APP_URL,
            'telephone' => APP_PHONE,
            'email' => APP_EMAIL,
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Islamabad',
                'addressCountry' => 'PK',
            ],
            'priceRange' => 'Rs. 500 - Rs. 10,000',
        ];
        return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES) . '</script>';
    }
}
