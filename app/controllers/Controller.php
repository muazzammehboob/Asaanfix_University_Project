<?php
/**
 * AsaanFix Pakistan - Base Controller
 * All controllers should extend this class
 * 
 * MVC Pattern: Controllers handle request logic,
 * call Models for data, and render Views
 */

class Controller {
    
    /**
     * Render a view file with data
     */
    protected function view(string $viewName, array $data = []): void {
        extract($data);
        $viewFile = ROOT_PATH . 'app/views/' . str_replace('.', '/', $viewName) . '.php';
        
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new \RuntimeException("View not found: $viewName");
        }
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to URL
     */
    protected function redirect(string $url): void {
        header("Location: $url");
        exit;
    }

    /**
     * Get POST input
     */
    protected function input(string $key, mixed $default = null): mixed {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Validate required fields
     */
    protected function validate(array $rules): array {
        $errors = [];
        foreach ($rules as $field => $label) {
            if (empty($_POST[$field])) {
                $errors[] = "$label is required.";
            }
        }
        return $errors;
    }
}
