<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Utils\Auth;

/**
 * Contrôleur d'Authentification
 * Gère la connexion, la déconnexion et la redirection vers les espaces spécifiques.
 */
class AuthController extends Controller {
    /**
     * Affiche l'écran de connexion.
     */
    public function showLogin(): void {
        Auth::startSession();

        // Si l'utilisateur est déjà connecté, redirection vers son dashboard
        if (Auth::check()) {
            Auth::redirectToDashboard();
            return;
        }

        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);

        $csrfToken = Auth::csrfToken();

        $this->render('auth.login', [
            'error'     => $error,
            'success'   => $success,
            'csrfToken' => $csrfToken,
        ], null);
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function login(): void {
        Auth::startSession();

        $token = $_POST['_csrf_token'] ?? null;
        if (!Auth::validateCsrf($token)) {
            $_SESSION['flash_error'] = "Jeton de sécurité CSRF expiré ou invalide. Veuillez réessayer.";
            $this->redirect('/login');
            return;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $_SESSION['flash_error'] = "Veuillez renseigner votre adresse email et votre mot de passe.";
            $this->redirect('/login');
            return;
        }

        if (Auth::attempt($email, $password)) {
            $intended = $_SESSION['intended_url'] ?? null;
            unset($_SESSION['intended_url']);

            if ($intended && !str_contains($intended, '/login')) {
                $this->redirect($intended);
                return;
            }

            Auth::redirectToDashboard();
            return;
        }

        $_SESSION['flash_error'] = "Identifiants incorrects. Vérifiez votre email et votre mot de passe.";
        $this->redirect('/login');
    }

    /**
     * Déconnecte l'utilisateur et redirige vers /login.
     */
    public function logout(): void {
        Auth::logout();
        Auth::startSession();
        $_SESSION['flash_success'] = "Vous avez été déconnecté avec succès.";
        $this->redirect('/login');
    }
}
