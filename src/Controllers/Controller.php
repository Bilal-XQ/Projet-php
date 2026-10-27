<?php
declare(strict_types=1);

namespace App\Controllers;

/**
 * Classe abstraite de base Controller
 * Fournit les utilitaires de rendu de vues avec layout, redirections et réponses JSON.
 */
abstract class Controller {
    /**
     * Rend une vue avec injection de données et gabarit de layout optionnel.
     */
    protected function render(string $view, array $data = [], ?string $layout = 'main'): void {
        $viewFile = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "Erreur interne: Vue '{$view}' introuvable à l'emplacement {$viewFile}.";
            exit;
        }

        // Extrait les variables pour les rendre accessibles dans la vue
        extract($data);

        // Capture le contenu de la vue
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Si aucun layout n'est spécifié, afficher directement le contenu
        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = __DIR__ . '/../Views/layout/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            http_response_code(500);
            echo "Erreur interne: Layout '{$layout}' introuvable à l'emplacement {$layoutFile}.";
            exit;
        }

        // Injecte $content et les variables dans le layout
        require $layoutFile;
    }

    /**
     * Redirige vers une URL relative ou absolue et termine l'exécution.
     */
    protected function redirect(string $path): void {
        header("Location: {$path}");
        exit;
    }

    /**
     * Émet une réponse JSON avec code HTTP spécifique.
     */
    protected function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
