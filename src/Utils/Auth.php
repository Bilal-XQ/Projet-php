<?php
declare(strict_types=1);

namespace App\Utils;

use App\Models\Utilisateur;

/**
 * Gestionnaire d'Authentification & Sessions Sécurisées
 * Encapsule les sessions natives PHP, le contrôle d'accès basé sur les rôles (RBAC) et la protection CSRF.
 */
class Auth {
    private static ?Utilisateur $currentUser = null;

    /**
     * Initialise la session avec des paramètres de cookies stricts.
     */
    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                        (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

            session_set_cookie_params([
                'lifetime' => 86400 * 7, // 7 jours
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            session_start();
        }
    }

    /**
     * Tente d'authentifier un utilisateur avec son email et mot de passe.
     */
    public static function attempt(string $email, string $password): bool {
        self::startSession();

        $user = Utilisateur::findByEmail($email);
        if (!$user) {
            return false;
        }

        if (!$user->verifyPassword($password)) {
            return false;
        }

        // Régénération de l'identifiant de session anti-fixation
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'     => $user->getId(),
            'email'  => $user->getEmail(),
            'nom'    => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'role'   => $user->getRole(),
        ];

        self::$currentUser = $user;
        return true;
    }

    /**
     * Vérifie si un utilisateur est actuellement connecté.
     */
    public static function check(): bool {
        self::startSession();
        return !empty($_SESSION['user']['id']);
    }

    /**
     * Retourne l'identifiant de l'utilisateur connecté ou null.
     */
    public static function id(): ?int {
        self::startSession();
        return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
    }

    /**
     * Retourne le rôle de l'utilisateur connecté ou null.
     */
    public static function role(): ?string {
        self::startSession();
        return $_SESSION['user']['role'] ?? null;
    }

    /**
     * Retourne l'instance complète du modèle Utilisateur connecté.
     */
    public static function user(): ?Utilisateur {
        self::startSession();
        if (!self::check()) {
            return null;
        }

        if (self::$currentUser === null && self::id() !== null) {
            self::$currentUser = Utilisateur::findById(self::id());
        }

        return self::$currentUser;
    }

    /**
     * Déconnecte l'utilisateur et détruit la session.
     */
    public static function logout(): void {
        self::startSession();
        self::$currentUser = null;

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Redirige l'utilisateur vers son tableau de bord respectif selon son rôle.
     */
    public static function redirectToDashboard(): void {
        $role = self::role();
        switch ($role) {
            case 'ADMIN':
                header('Location: /admin/dashboard');
                exit;
            case 'ENSEIGNANT':
                header('Location: /enseignant/dashboard');
                exit;
            case 'ETUDIANT':
                header('Location: /etudiant/dashboard');
                exit;
            default:
                header('Location: /login');
                exit;
        }
    }

    /**
     * Exige que l'utilisateur soit connecté et possède l'un des rôles spécifiés.
     */
    public static function requireRole(array|string $allowedRoles): void {
        self::startSession();

        if (!self::check()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/';
            header('Location: /login');
            exit;
        }

        $roles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
        $currentRole = self::role();

        if (!in_array($currentRole, $roles, true)) {
            // Rôle non autorisé -> redirection vers son propre dashboard ou 403
            http_response_code(403);
            echo "<!DOCTYPE html><html><head><title>403 Accès Refusé</title></head><body style='font-family: sans-serif; text-align: center; padding-top: 5rem;'><h1>403 — Accès Refusé</h1><p>Vous n'avez pas les autorisations nécessaires pour accéder à cette section.</p><p><a href='/login'>Retour à l'accueil</a></p></body></html>";
            exit;
        }
    }

    /**
     * Génère ou récupère le jeton CSRF de la session.
     */
    public static function csrfToken(): string {
        self::startSession();
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Valide le jeton CSRF fourni dans une requête POST.
     */
    public static function validateCsrf(?string $token): bool {
        self::startSession();
        if (empty($_SESSION['_csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }
}
