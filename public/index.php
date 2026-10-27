<?php
declare(strict_types=1);

/**
 * Front Controller & Dispatcher
 * Application de Gestion Pédagogique — Département Math-Info
 * 100% PHP 8.3 Natif, Architecture MVC, Zéro Dépendance Tierce
 */

// 1. Initialisation de l'Autoloader PSR-4 & Configuration
require_once __DIR__ . '/../config/autoload.php';

use App\Utils\Auth;
use App\Utils\Router;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\EnseignantController;
use App\Controllers\EtudiantController;

// 2. Démarrage de la session sécurisée
Auth::startSession();

// 3. Déclaration et configuration du routeur
$router = new Router();

// Route racine : redirige vers le tableau de bord si connecté, sinon vers le login
$router->get('/', function () {
    if (Auth::check()) {
        Auth::redirectToDashboard();
    } else {
        header('Location: /login');
        exit;
    }
});

// Routes d'Authentification
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);

// =============================================================================
// Routes Protégées — Module Administrateur (Phase 3)
// =============================================================================
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);

// Filières
$router->get('/admin/filieres', [AdminController::class, 'filieres']);
$router->post('/admin/filieres/create', [AdminController::class, 'createFiliere']);
$router->post('/admin/filieres/edit', [AdminController::class, 'editFiliere']);
$router->post('/admin/filieres/delete', [AdminController::class, 'deleteFiliere']);

// Modules
$router->get('/admin/modules', [AdminController::class, 'modules']);
$router->post('/admin/modules/create', [AdminController::class, 'createModule']);
$router->post('/admin/modules/edit', [AdminController::class, 'editModule']);
$router->post('/admin/modules/assign-teacher', [AdminController::class, 'assignTeacher']);
$router->post('/admin/modules/delete', [AdminController::class, 'deleteModule']);

// Utilisateurs
$router->get('/admin/utilisateurs', [AdminController::class, 'utilisateurs']);
$router->post('/admin/utilisateurs/create', [AdminController::class, 'createUser']);
$router->post('/admin/utilisateurs/edit', [AdminController::class, 'editUser']);
$router->post('/admin/utilisateurs/delete', [AdminController::class, 'deleteUser']);

// Inscriptions
$router->get('/admin/inscriptions', [AdminController::class, 'inscriptions']);
$router->post('/admin/inscriptions/create', [AdminController::class, 'createInscription']);
$router->post('/admin/inscriptions/edit', [AdminController::class, 'editInscription']);
$router->post('/admin/inscriptions/status', [AdminController::class, 'updateInscriptionStatus']);
$router->post('/admin/inscriptions/delete', [AdminController::class, 'deleteInscription']);

// Annonces
$router->get('/admin/annonces', [AdminController::class, 'annonces']);
$router->post('/admin/annonces/create', [AdminController::class, 'createAnnonce']);
$router->post('/admin/annonces/edit', [AdminController::class, 'editAnnonce']);
$router->post('/admin/annonces/delete', [AdminController::class, 'deleteAnnonce']);

// Journal d'Audit
$router->get('/admin/audit', [AdminController::class, 'audit']);

// =============================================================================
// Routes Protégées — Espace Enseignant (Phase 4)
// =============================================================================
$router->get('/enseignant/dashboard', [EnseignantController::class, 'dashboard']);
$router->get('/enseignant/modules', [EnseignantController::class, 'modules']);
$router->get('/enseignant/notes', [EnseignantController::class, 'notesIndex']);
$router->get('/enseignant/seances', [EnseignantController::class, 'seancesIndex']);
$router->get('/enseignant/modules/{id}/etudiants', [EnseignantController::class, 'etudiants']);
$router->get('/enseignant/modules/{id}/notes', [EnseignantController::class, 'notes']);
$router->post('/enseignant/modules/{id}/notes/save', [EnseignantController::class, 'saveNotes']);
$router->get('/enseignant/modules/{id}/seances', [EnseignantController::class, 'seances']);
$router->post('/enseignant/modules/{id}/seances/create', [EnseignantController::class, 'createSeance']);
$router->get('/enseignant/seances/{id}/absences', [EnseignantController::class, 'absences']);
$router->post('/enseignant/seances/{id}/absences/save', [EnseignantController::class, 'saveAbsences']);
$router->get('/enseignant/annonces', [EnseignantController::class, 'annonces']);
$router->post('/enseignant/annonces/create', [EnseignantController::class, 'createAnnonce']);
$router->post('/enseignant/annonces/edit', [EnseignantController::class, 'editAnnonce']);
$router->post('/enseignant/annonces/delete', [EnseignantController::class, 'deleteAnnonce']);
$router->get('/enseignant/modules/{id}/pdf', [EnseignantController::class, 'downloadModulePdf']);

// =============================================================================
// Routes Protégées — Espace Étudiant (Phase 5)
// =============================================================================
$router->get('/etudiant/dashboard', [EtudiantController::class, 'dashboard']);
$router->get('/etudiant/notes', [EtudiantController::class, 'notes']);
$router->get('/etudiant/absences', [EtudiantController::class, 'absences']);
$router->get('/etudiant/annonces', [EtudiantController::class, 'annonces']);
$router->get('/etudiant/releve/pdf', [EtudiantController::class, 'downloadRelevePdf']);

// 4. Distribution de la requête HTTP
$router->dispatch();
