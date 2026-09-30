<?php
namespace App;

use App\Helpers\Security;

abstract class Controller {
    /**
     * Render view with layout
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void {
        extract($data);
        $currentUser = Auth::user();
        $appName = 'ESG-Pro 智慧永續管理系統';

        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            die("View [{$view}] not found at {$viewFile}");
        }

        // Start output buffering for view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === 'none') {
            echo $content;
            return;
        }

        // Load layout
        $headerFile = __DIR__ . '/../views/layouts/header.php';
        $sidebarFile = __DIR__ . '/../views/layouts/sidebar.php';
        $footerFile = __DIR__ . '/../views/layouts/footer.php';

        if (file_exists($headerFile)) require $headerFile;
        echo $content;
        if (file_exists($footerFile)) require $footerFile;
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $status = 200): void {
        Security::jsonResponse($data, $status);
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    /**
     * Check CSRF token or die
     */
    protected function checkCsrf(): void {
        if (!Security::validateCsrf()) {
            http_response_code(403);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                $this->json(['success' => false, 'message' => 'CSRF 權杖已失效，請重新整理頁面。'], 403);
            } else {
                die('CSRF 權杖已失效，請重新整理頁面。');
            }
        }
    }
}
