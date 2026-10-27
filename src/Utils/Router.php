<?php
declare(strict_types=1);

namespace App\Utils;

/**
 * Routeur HTTP Léger avec Support de Paramètres Dynamiques
 * Enregistre et distribue les requêtes GET et POST vers les contrôleurs MVC.
 */
class Router {
    private array $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    /**
     * Enregistre une route GET.
     */
    public function get(string $path, callable|array $handler): void {
        $this->routes['GET'][$this->normalizePath($path)] = $handler;
    }

    /**
     * Enregistre une route POST.
     */
    public function post(string $path, callable|array $handler): void {
        $this->routes['POST'][$this->normalizePath($path)] = $handler;
    }

    /**
     * Normalise le chemin (supprime les slashes de fin sauf racine).
     */
    private function normalizePath(string $path): string {
        $trimmed = rtrim($path, '/');
        return $trimmed === '' ? '/' : $trimmed;
    }

    /**
     * Résout et exécute le gestionnaire correspondant à la requête courante.
     */
    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Extraction du chemin sans la query string
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = $this->normalizePath($path);

        $handler = $this->routes[$method][$path] ?? null;
        $params = [];

        // Recherche par pattern paramétré (ex: /modules/{id}/notes)
        if ($handler === null) {
            foreach ($this->routes[$method] as $routePath => $routeHandler) {
                if (str_contains($routePath, '{')) {
                    $pattern = '#^' . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath) . '$#';
                    if (preg_match($pattern, $path, $matches)) {
                        $handler = $routeHandler;
                        foreach ($matches as $k => $v) {
                            if (is_string($k)) {
                                $params[$k] = is_numeric($v) ? (int)$v : $v;
                            }
                        }
                        break;
                    }
                }
            }
        }

        if ($handler === null) {
            $this->handleNotFound($path);
            return;
        }

        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $action] = $handler;
            if (class_exists($class)) {
                $controller = new $class();
                if (method_exists($controller, $action)) {
                    $controller->$action(...array_values($params));
                    return;
                }
            }
        }

        http_response_code(500);
        echo "Erreur interne: Gestionnaire de route invalide pour {$method} {$path}.";
    }

    /**
     * Affiche une page 404 épurée sans émojis selon la charte shadcn Zinc.
     */
    private function handleNotFound(string $path): void {
        http_response_code(404);
        ?>
        <!DOCTYPE html>
        <html lang="fr" data-theme="dark">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>404 — Page Non Trouvée</title>
          <link rel="stylesheet" href="/css/style.css">
        </head>
        <body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
          <div class="card" style="text-align: center; max-width: 450px; width: 100%;">
            <div style="margin-bottom: 1rem; color: var(--text-muted); display: flex; justify-content: center;">
              <?= Icon::get('search', 36) ?>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">Page non trouvée</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">
              La ressource <code><?= htmlspecialchars($path) ?></code> n'existe pas ou a été déplacée.
            </p>
            <a href="/" class="btn btn-primary" style="text-decoration: none; display: inline-flex;">
              Retour à l'accueil
            </a>
          </div>
        </body>
        </html>
        <?php
        exit;
    }
}
