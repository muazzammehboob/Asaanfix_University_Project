<?php
/**
 * AsaanFix Pakistan - Image Optimization Helper
 * WebP conversion, lazy loading, compression
 */

class ImageOptimizer {

    /**
     * Upload and optimize an image
     * Compresses and optionally converts to WebP
     */
    public static function upload(array $file, string $directory, string $prefix = 'img', bool $convertWebP = true): ?string {
        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > MAX_FILE_SIZE) return null;
        if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) return null;

        if (!is_dir($directory)) mkdir($directory, 0755, true);

        // Randomized filename for security
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '_' . time();

        // Compress original
        $sourcePath = $file['tmp_name'];
        $image = self::loadImage($sourcePath, $file['type']);
        if (!$image) return null;

        // Resize if too large (max 1920px width)
        $width = imagesx($image);
        $height = imagesy($image);
        if ($width > 1920) {
            $newWidth = 1920;
            $newHeight = (int) ($height * (1920 / $width));
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        // Convert to WebP if supported and requested
        if ($convertWebP && function_exists('imagewebp')) {
            $webpFile = $filename . '.webp';
            $webpPath = $directory . $webpFile;
            imagewebp($image, $webpPath, 82);
            imagedestroy($image);
            return $webpFile;
        }

        // Save as compressed original format
        $outputFile = $filename . '.' . $ext;
        $outputPath = $directory . $outputFile;
        self::saveImage($image, $outputPath, $file['type']);
        imagedestroy($image);

        return $outputFile;
    }

    /**
     * Generate a thumbnail
     */
    public static function createThumbnail(string $sourcePath, string $destPath, int $maxWidth = 300, int $maxHeight = 300): bool {
        if (!file_exists($sourcePath)) return false;

        $info = getimagesize($sourcePath);
        if (!$info) return false;

        $image = self::loadImage($sourcePath, $info['mime']);
        if (!$image) return false;

        $origW = imagesx($image);
        $origH = imagesy($image);
        $ratio = min($maxWidth / $origW, $maxHeight / $origH);
        $newW = (int) ($origW * $ratio);
        $newH = (int) ($origH * $ratio);

        $thumb = imagecreatetruecolor($newW, $newH);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($image);

        if (function_exists('imagewebp')) {
            imagewebp($thumb, $destPath, 80);
        } else {
            imagejpeg($thumb, $destPath, 80);
        }
        imagedestroy($thumb);
        return true;
    }

    /**
     * Get optimized img tag with lazy loading
     */
    public static function imgTag(string $src, string $alt, string $class = '', array $attrs = []): string {
        $attrStr = '';
        foreach ($attrs as $k => $v) $attrStr .= " $k=\"" . htmlspecialchars($v) . "\"";
        $classStr = $class ? " class=\"$class\"" : '';
        return "<img src=\"$src\" alt=\"" . htmlspecialchars($alt) . "\" loading=\"lazy\" decoding=\"async\"$classStr$attrStr>";
    }

    /**
     * Load image from file based on MIME type
     */
    private static function loadImage(string $path, string $mime): ?\GdImage {
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path) ?: null,
            'image/png'  => imagecreatefrompng($path) ?: null,
            'image/gif'  => imagecreatefromgif($path) ?: null,
            'image/webp' => function_exists('imagecreatefromwebp') ? (imagecreatefromwebp($path) ?: null) : null,
            default      => null,
        };
    }

    /**
     * Save image to file based on MIME type
     */
    private static function saveImage(\GdImage $image, string $path, string $mime): void {
        match ($mime) {
            'image/jpeg' => imagejpeg($image, $path, 82),
            'image/png'  => imagepng($image, $path, 8),
            'image/gif'  => imagegif($image, $path),
            'image/webp' => imagewebp($image, $path, 82),
            default      => imagejpeg($image, $path, 82),
        };
    }
}
