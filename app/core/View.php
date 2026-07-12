<?php
namespace App\Core;

/**
 * View Loader Class
 * Responsible for locating, resolving, and rendering template view files.
 */
class View {
    /**
     * Render a view template file with data extracted to local scope.
     * 
     * @param string $view Name of view file relative to app/views/ (e.g., 'auth/login')
     * @param array $data Data variables to expose to template scope
     * @return void
     */
    public static function render($view, $data = []) {
        // Expose data array keys as local variables
        extract($data);

        // Resolve absolute template file path
        $viewFile = dirname(__DIR__) . '/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
            die("System Error: View file [{$view}] does not exist at path [{$viewFile}].");
        }

        // Render target view template
        if (isset($data['no_layout']) && $data['no_layout'] === true) {
            require $viewFile;
        } else {
            require dirname(__DIR__) . '/views/layouts/header.php';
            require dirname(__DIR__) . '/views/layouts/sidebar.php';
            require $viewFile;
            require dirname(__DIR__) . '/views/layouts/footer.php';
        }
    }
}
