<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Utils\Auth;
use App\Models\Model;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\Inscription;
use App\Models\Annonce;
use App\Models\JournalAudit;
use App\Models\Note;
use App\Models\Absence;
use PDO;

/**
 * Contrôleur Administrateur (Chef de Département / Scolarité)
 * Couvre l'ensemble des fonctionnalités de gestion académique (Phase 3 & Phase 5).
 */
class AdminController extends Controller {
    public function __construct() {
        Auth::requireRole('ADMIN');
    }

    /**
     * Tableau de bord principal avec statistiques réelles et visualisations Chart.js.
     */
    public function dashboard(): void {
        $user = Auth::user();
        $db = Model::getDb();

        $stats = [
            'utilisateurs' => (int)$db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn(),
            'enseignants'  => (int)$db->query("SELECT COUNT(*) FROM enseignants")->fetchColumn(),
            'etudiants'    => (int)$db->query("SELECT COUNT(*) FROM etudiants")->fetchColumn(),
            'filieres'     => (int)$db->query("SELECT COUNT(*) FROM filieres")->fetchColumn(),
            'modules'      => (int)$db->query("SELECT COUNT(*) FROM modules")->fetchColumn(),
        ];

        // Statistiques académiques et d'assiduité du département
        $deptStats = Note::getDepartmentStats();
        $attendanceStats = Absence::getDepartmentAttendanceStats();

        // Dernières inscriptions
        $stmtRecent = $db->query("
            SELECT i.*, u.nom, u.prenom, f.code AS filiere_code
            FROM inscriptions i
            JOIN utilisateurs u ON i.idEtudiant = u.idUtilisateur
            JOIN filieres f ON i.idFiliere = f.idFiliere
            ORDER BY i.dateInscription DESC
            LIMIT 5
        ");
        $recentInscriptions = $stmtRecent->fetchAll();

        $this->render('admin.dashboard', [
            'title'              => 'Tableau de bord Administrateur',
            'user'               => $user,
            'stats'              => $stats,
            'deptStats'          => $deptStats,
            'attendanceStats'    => $attendanceStats,
            'recentInscriptions' => $recentInscriptions,
            'activeNav'          => 'dashboard',
        ]);
    }

    // =========================================================================
    // 1. GESTION DES FILIÈRES
    // =========================================================================

    public function filieres(): void {
        $filieres = Filiere::getAllWithCounts();
        $this->render('admin.filieres', [
            'title'     => 'Gestion des Filières',
            'filieres'  => $filieres,
            'csrfToken' => Auth::csrfToken(),
            'activeNav' => 'filieres',
        ]);
    }

    public function createFiliere(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/filieres');
            return;
        }

        $code = trim((string)($_POST['code'] ?? ''));
        $intitule = trim((string)($_POST['intitule'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        if ($code === '' || $intitule === '') {
            $_SESSION['flash_error'] = "Le code et l'intitulé de la filière sont obligatoires.";
            $this->redirect('/admin/filieres');
            return;
        }

        try {
            Filiere::create($code, $intitule, $description !== '' ? $description : null);
            $_SESSION['flash_success'] = "La filière '{$code}' a été créée avec succès.";
        } catch (\PDOException $e) {
            $_SESSION['flash_error'] = "Erreur lors de la création : le code '{$code}' existe peut-être déjà.";
        }

        $this->redirect('/admin/filieres');
    }

    public function editFiliere(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/filieres');
            return;
        }

        $id = (int)($_POST['idFiliere'] ?? 0);
        $code = trim((string)($_POST['code'] ?? ''));
        $intitule = trim((string)($_POST['intitule'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        if ($id <= 0 || $code === '' || $intitule === '') {
            $_SESSION['flash_error'] = "Données invalides pour la mise à jour.";
            $this->redirect('/admin/filieres');
            return;
        }

        try {
            Filiere::update($id, $code, $intitule, $description !== '' ? $description : null);
            $_SESSION['flash_success'] = "La filière '{$code}' a été mise à jour.";
        } catch (\PDOException $e) {
            $_SESSION['flash_error'] = "Erreur lors de la mise à jour de la filière.";
        }

        $this->redirect('/admin/filieres');
    }

    public function deleteFiliere(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/filieres');
            return;
        }

        $id = (int)($_POST['idFiliere'] ?? 0);
        if ($id > 0) {
            Filiere::delete($id);
            $_SESSION['flash_success'] = "La filière a été supprimée.";
        }

        $this->redirect('/admin/filieres');
    }

    // =========================================================================
    // 2. GESTION DES MODULES
    // =========================================================================

    public function modules(): void {
        $db = Model::getDb();
        $modules = Module::getAllWithDetails();
        $filieres = Filiere::getAllWithCounts();

        // Enseignants disponibles pour affectation
        $stmtEns = $db->query("
            SELECT u.idUtilisateur, u.nom, u.prenom, e.matricule, e.specialite
            FROM utilisateurs u
            JOIN enseignants e ON u.idUtilisateur = e.idUtilisateur
            WHERE u.role = 'ENSEIGNANT'
            ORDER BY u.nom ASC
        ");
        $enseignants = $stmtEns->fetchAll();

        $this->render('admin.modules', [
            'title'       => 'Gestion des Modules',
            'modules'     => $modules,
            'filieres'    => $filieres,
            'enseignants' => $enseignants,
            'csrfToken'   => Auth::csrfToken(),
            'activeNav'   => 'modules',
        ]);
    }

    public function createModule(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/modules');
            return;
        }

        $code = trim((string)($_POST['code'] ?? ''));
        $intitule = trim((string)($_POST['intitule'] ?? ''));
        $semestre = (string)($_POST['semestre'] ?? 'S1');
        $coefficient = (float)($_POST['coefficient'] ?? 1.0);
        $idFiliere = (int)($_POST['idFiliere'] ?? 0);
        $idEnseignant = (int)($_POST['idEnseignant'] ?? 0);

        if ($code === '' || $intitule === '' || $idFiliere <= 0) {
            $_SESSION['flash_error'] = "Veuillez renseigner tous les champs obligatoires du module.";
            $this->redirect('/admin/modules');
            return;
        }

        try {
            Module::create($code, $intitule, $semestre, $coefficient, $idFiliere, $idEnseignant > 0 ? $idEnseignant : null);
            $_SESSION['flash_success'] = "Le module '{$code}' a été créé avec succès.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Erreur : " . $e->getMessage();
        }

        $this->redirect('/admin/modules');
    }

    public function editModule(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/modules');
            return;
        }

        $id = (int)($_POST['idModule'] ?? 0);
        $code = trim((string)($_POST['code'] ?? ''));
        $intitule = trim((string)($_POST['intitule'] ?? ''));
        $semestre = (string)($_POST['semestre'] ?? 'S1');
        $coefficient = (float)($_POST['coefficient'] ?? 1.0);
        $idFiliere = (int)($_POST['idFiliere'] ?? 0);
        $idEnseignant = (int)($_POST['idEnseignant'] ?? 0);

        if ($id <= 0 || $code === '' || $intitule === '' || $idFiliere <= 0) {
            $_SESSION['flash_error'] = "Données invalides pour la mise à jour du module.";
            $this->redirect('/admin/modules');
            return;
        }

        try {
            Module::update($id, $code, $intitule, $semestre, $coefficient, $idFiliere, $idEnseignant > 0 ? $idEnseignant : null);
            $_SESSION['flash_success'] = "Le module '{$code}' a été mis à jour.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Erreur : " . $e->getMessage();
        }

        $this->redirect('/admin/modules');
    }

    public function assignTeacher(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/modules');
            return;
        }

        $idModule = (int)($_POST['idModule'] ?? 0);
        $idEnseignant = (int)($_POST['idEnseignant'] ?? 0);

        if ($idModule <= 0) {
            $_SESSION['flash_error'] = "Module introuvable.";
            $this->redirect('/admin/modules');
            return;
        }

        Module::assignTeacher($idModule, $idEnseignant > 0 ? $idEnseignant : null);
        $_SESSION['flash_success'] = "L'affectation de l'enseignant a été mise à jour.";
        $this->redirect('/admin/modules');
    }

    public function deleteModule(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/modules');
            return;
        }

        $id = (int)($_POST['idModule'] ?? 0);
        if ($id > 0) {
            Module::delete($id);
            $_SESSION['flash_success'] = "Le module a été supprimé.";
        }

        $this->redirect('/admin/modules');
    }

    // =========================================================================
    // 3. GESTION DES UTILISATEURS (TRANSACTIONNEL POLYMORPHE)
    // =========================================================================

    public function utilisateurs(): void {
        $db = Model::getDb();
        $roleFilter = $_GET['role'] ?? null;

        $sql = "
            SELECT 
                u.idUtilisateur, u.nom, u.prenom, u.email, u.role, u.dateCreation,
                e.matricule, e.specialite,
                et.cne, et.dateNaissance
            FROM utilisateurs u
            LEFT JOIN enseignants e ON u.idUtilisateur = e.idUtilisateur
            LEFT JOIN etudiants et ON u.idUtilisateur = et.idUtilisateur
            ORDER BY u.idUtilisateur DESC
        ";
        $allUsers = $db->query($sql)->fetchAll();

        $enseignantsList = array_filter($allUsers, fn($u) => $u['role'] === 'ENSEIGNANT');
        $etudiantsList = array_filter($allUsers, fn($u) => $u['role'] === 'ETUDIANT');
        $adminsList = array_filter($allUsers, fn($u) => $u['role'] === 'ADMIN');

        $filteredUsers = $allUsers;
        if (!empty($roleFilter)) {
            $filteredUsers = array_filter($allUsers, fn($u) => $u['role'] === $roleFilter);
        }

        $this->render('admin.utilisateurs', [
            'title'           => 'Gestion des Utilisateurs',
            'allUsers'        => $allUsers,
            'filteredUsers'   => $filteredUsers,
            'enseignantsList' => $enseignantsList,
            'etudiantsList'   => $etudiantsList,
            'adminsList'      => $adminsList,
            'currentFilter'   => $roleFilter,
            'csrfToken'       => Auth::csrfToken(),
            'activeNav'       => 'utilisateurs',
        ]);
    }

    public function createUser(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/utilisateurs');
            return;
        }

        $role = trim((string)($_POST['role'] ?? ''));
        $nom = trim((string)($_POST['nom'] ?? ''));
        $prenom = trim((string)($_POST['prenom'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');

        if ($nom === '' || $prenom === '' || $email === '' || $password === '' || !in_array($role, ['ADMIN', 'ENSEIGNANT', 'ETUDIANT'], true)) {
            $_SESSION['flash_error'] = "Tous les champs de base sont requis.";
            $this->redirect('/admin/utilisateurs');
            return;
        }

        $db = Model::getDb();

        try {
            $db->beginTransaction();

            // 1. Insertion dans utilisateurs
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmtUser = $db->prepare("
                INSERT INTO utilisateurs (nom, prenom, email, motDePasse, role)
                VALUES (:nom, :prenom, :email, :motDePasse, :role)
            ");
            $stmtUser->execute([
                'nom'        => $nom,
                'prenom'     => $prenom,
                'email'      => $email,
                'motDePasse' => $hash,
                'role'       => $role,
            ]);
            $idUser = (int)$db->lastInsertId();

            // 2. Spécialisation selon le rôle
            if ($role === 'ENSEIGNANT') {
                $matricule = trim((string)($_POST['matricule'] ?? ''));
                $specialite = trim((string)($_POST['specialite'] ?? ''));
                if ($matricule === '' || $specialite === '') {
                    throw new \InvalidArgumentException("Le matricule et la spécialité sont obligatoires pour un enseignant.");
                }
                $stmtEns = $db->prepare("
                    INSERT INTO enseignants (idUtilisateur, matricule, specialite)
                    VALUES (:id, :matricule, :specialite)
                ");
                $stmtEns->execute([
                    'id'         => $idUser,
                    'matricule'  => $matricule,
                    'specialite' => $specialite,
                ]);

            } elseif ($role === 'ETUDIANT') {
                $cne = strtoupper(trim((string)($_POST['cne'] ?? '')));
                $dateNaissance = trim((string)($_POST['dateNaissance'] ?? ''));
                if ($cne === '' || $dateNaissance === '') {
                    throw new \InvalidArgumentException("Le CNE et la date de naissance sont obligatoires pour un étudiant.");
                }
                $stmtEtu = $db->prepare("
                    INSERT INTO etudiants (idUtilisateur, cne, dateNaissance)
                    VALUES (:id, :cne, :dateNaissance)
                ");
                $stmtEtu->execute([
                    'id'            => $idUser,
                    'cne'           => $cne,
                    'dateNaissance' => $dateNaissance,
                ]);
            }

            $db->commit();
            $_SESSION['flash_success'] = "Le compte {$role} '{$prenom} {$nom}' a été créé avec succès.";

        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['flash_error'] = "Erreur lors de la création : " . $e->getMessage();
        }

        $this->redirect('/admin/utilisateurs');
    }

    public function editUser(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/utilisateurs');
            return;
        }

        $id = (int)($_POST['idUtilisateur'] ?? 0);
        $nom = trim((string)($_POST['nom'] ?? ''));
        $prenom = trim((string)($_POST['prenom'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $motDePasse = (string)($_POST['motDePasse'] ?? '');
        $role = (string)($_POST['role'] ?? '');

        if ($id <= 0 || $nom === '' || $prenom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = "Informations invalides pour la mise à jour de l'utilisateur.";
            $this->redirect('/admin/utilisateurs');
            return;
        }

        $db = Model::getDb();
        $db->beginTransaction();

        try {
            if ($motDePasse !== '') {
                $hash = password_hash($motDePasse, PASSWORD_BCRYPT);
                $stmt = $db->prepare("
                    UPDATE utilisateurs 
                    SET nom = :nom, prenom = :prenom, email = :email, motDePasse = :pwd 
                    WHERE idUtilisateur = :id
                ");
                $stmt->execute(['nom' => $nom, 'prenom' => $prenom, 'email' => $email, 'pwd' => $hash, 'id' => $id]);
            } else {
                $stmt = $db->prepare("
                    UPDATE utilisateurs 
                    SET nom = :nom, prenom = :prenom, email = :email 
                    WHERE idUtilisateur = :id
                ");
                $stmt->execute(['nom' => $nom, 'prenom' => $prenom, 'email' => $email, 'id' => $id]);
            }

            if ($role === 'ENSEIGNANT') {
                $matricule = trim((string)($_POST['matricule'] ?? ''));
                $specialite = trim((string)($_POST['specialite'] ?? ''));
                $stmtEns = $db->prepare("
                    INSERT INTO enseignants (idUtilisateur, matricule, specialite)
                    VALUES (:id, :matricule, :specialite)
                    ON DUPLICATE KEY UPDATE matricule = :matricule, specialite = :specialite
                ");
                $stmtEns->execute(['id' => $id, 'matricule' => $matricule, 'specialite' => $specialite]);
            } elseif ($role === 'ETUDIANT') {
                $cne = strtoupper(trim((string)($_POST['cne'] ?? '')));
                $dateNaissance = trim((string)($_POST['dateNaissance'] ?? ''));
                $stmtEtu = $db->prepare("
                    INSERT INTO etudiants (idUtilisateur, cne, dateNaissance)
                    VALUES (:id, :cne, :dateNaissance)
                    ON DUPLICATE KEY UPDATE cne = :cne, dateNaissance = :dateNaissance
                ");
                $stmtEtu->execute(['id' => $id, 'cne' => $cne, 'dateNaissance' => $dateNaissance !== '' ? $dateNaissance : null]);
            }

            $db->commit();
            $_SESSION['flash_success'] = "L'utilisateur '{$prenom} {$nom}' a été mis à jour avec succès.";
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['flash_error'] = "Erreur de modification : " . $e->getMessage();
        }

        $this->redirect('/admin/utilisateurs');
    }

    public function deleteUser(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/utilisateurs');
            return;
        }

        $id = (int)($_POST['idUtilisateur'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = "Identifiant utilisateur invalide.";
            $this->redirect('/admin/utilisateurs');
            return;
        }

        if ($id === Auth::id()) {
            $_SESSION['flash_error'] = "Action interdite : vous ne pouvez pas supprimer votre propre compte administrateur.";
            $this->redirect('/admin/utilisateurs');
            return;
        }

        try {
            $db = Model::getDb();
            $stmt = $db->prepare("DELETE FROM utilisateurs WHERE idUtilisateur = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['flash_success'] = "Le compte utilisateur #{$id} a été supprimé.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Erreur lors de la suppression de l'utilisateur.";
        }

        $this->redirect('/admin/utilisateurs');
    }

    // =========================================================================
    // 4. GESTION DES INSCRIPTIONS
    // =========================================================================

    public function inscriptions(): void {
        $db = Model::getDb();
        $inscriptions = Inscription::getAllWithDetails();
        $filieres = Filiere::getAllWithCounts();

        // Récupération de tous les étudiants pour le formulaire d'inscription
        $stmtEtu = $db->query("
            SELECT u.idUtilisateur, u.nom, u.prenom, u.email, e.cne
            FROM utilisateurs u
            JOIN etudiants e ON u.idUtilisateur = e.idUtilisateur
            ORDER BY u.nom ASC
        ");
        $etudiants = $stmtEtu->fetchAll();

        $this->render('admin.inscriptions', [
            'title'        => 'Inscriptions Universitaires',
            'inscriptions' => $inscriptions,
            'filieres'     => $filieres,
            'etudiants'    => $etudiants,
            'csrfToken'    => Auth::csrfToken(),
            'activeNav'    => 'inscriptions',
        ]);
    }

    public function createInscription(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/inscriptions');
            return;
        }

        $idEtudiant = (int)($_POST['idEtudiant'] ?? 0);
        $idFiliere = (int)($_POST['idFiliere'] ?? 0);
        $annee = trim((string)($_POST['anneeUniversitaire'] ?? '2026-2027'));
        $date = trim((string)($_POST['dateInscription'] ?? ''));

        if ($idEtudiant <= 0 || $idFiliere <= 0 || $annee === '') {
            $_SESSION['flash_error'] = "Tous les champs d'inscription sont obligatoires.";
            $this->redirect('/admin/inscriptions');
            return;
        }

        try {
            Inscription::enroll($idEtudiant, $idFiliere, $annee, $date !== '' ? $date : null);
            $_SESSION['flash_success'] = "L'étudiant a été inscrit avec succès pour l'année {$annee}.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        $this->redirect('/admin/inscriptions');
    }

    public function updateInscriptionStatus(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/inscriptions');
            return;
        }

        $id = (int)($_POST['idInscription'] ?? 0);
        $statut = (string)($_POST['statut'] ?? '');

        if ($id > 0 && in_array($statut, Inscription::VALID_STATUTS, true)) {
            Inscription::updateStatus($id, $statut);
            $_SESSION['flash_success'] = "Le statut de l'inscription a été actualisé.";
        }

        $this->redirect('/admin/inscriptions');
    }

    public function editInscription(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/inscriptions');
            return;
        }

        $id = (int)($_POST['idInscription'] ?? 0);
        $idFiliere = (int)($_POST['idFiliere'] ?? 0);
        $annee = trim((string)($_POST['anneeUniversitaire'] ?? ''));
        $statut = (string)($_POST['statut'] ?? 'EN_COURS');

        if ($id <= 0 || $idFiliere <= 0 || $annee === '') {
            $_SESSION['flash_error'] = "Données incomplètes pour la modification de l'inscription.";
            $this->redirect('/admin/inscriptions');
            return;
        }

        try {
            Inscription::update($id, $idFiliere, $annee, $statut);
            $_SESSION['flash_success'] = "L'inscription a été mise à jour avec succès.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Erreur de modification : " . $e->getMessage();
        }

        $this->redirect('/admin/inscriptions');
    }

    public function deleteInscription(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/inscriptions');
            return;
        }

        $id = (int)($_POST['idInscription'] ?? 0);
        if ($id > 0) {
            Inscription::delete($id);
            $_SESSION['flash_success'] = "L'inscription a été supprimée du registre.";
        }

        $this->redirect('/admin/inscriptions');
    }

    // =========================================================================
    // 5. GESTION DES ANNONCES
    // =========================================================================

    public function annonces(): void {
        $annonces = Annonce::getAllWithAuthor();
        $modules = Module::getAllWithDetails();

        $this->render('admin.annonces', [
            'title'     => 'Avis & Annonces',
            'annonces'  => $annonces,
            'modules'   => $modules,
            'csrfToken' => Auth::csrfToken(),
            'activeNav' => 'annonces',
        ]);
    }

    public function createAnnonce(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/annonces');
            return;
        }

        $titre = trim((string)($_POST['titre'] ?? ''));
        $contenu = trim((string)($_POST['contenu'] ?? ''));
        $portee = (string)($_POST['portee'] ?? 'GLOBALE');
        $idModule = (int)($_POST['idModule'] ?? 0);
        $idAuteur = Auth::id() ?? 1;

        if ($titre === '' || $contenu === '') {
            $_SESSION['flash_error'] = "Le titre et le contenu sont obligatoires pour diffuser une annonce.";
            $this->redirect('/admin/annonces');
            return;
        }

        try {
            Annonce::create($idAuteur, $titre, $contenu, $portee, $portee === 'MODULE' ? $idModule : null);
            $_SESSION['flash_success'] = "L'annonce a été publiée avec succès.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Erreur de publication : " . $e->getMessage();
        }

        $this->redirect('/admin/annonces');
    }

    public function editAnnonce(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/annonces');
            return;
        }

        $id = (int)($_POST['idAnnonce'] ?? 0);
        $titre = trim((string)($_POST['titre'] ?? ''));
        $contenu = trim((string)($_POST['contenu'] ?? ''));
        $portee = (string)($_POST['portee'] ?? 'GLOBALE');
        $idModule = (int)($_POST['idModule'] ?? 0);

        if ($id <= 0 || $titre === '' || $contenu === '') {
            $_SESSION['flash_error'] = "Le titre et le contenu sont obligatoires pour modifier une annonce.";
            $this->redirect('/admin/annonces');
            return;
        }

        try {
            Annonce::update($id, $titre, $contenu, $portee, $portee === 'MODULE' ? $idModule : null);
            $_SESSION['flash_success'] = "L'annonce a été modifiée avec succès.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Erreur de modification : " . $e->getMessage();
        }

        $this->redirect('/admin/annonces');
    }

    public function deleteAnnonce(): void {
        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/admin/annonces');
            return;
        }

        $id = (int)($_POST['idAnnonce'] ?? 0);
        if ($id > 0) {
            Annonce::delete($id);
            $_SESSION['flash_success'] = "L'annonce a été supprimée.";
        }

        $this->redirect('/admin/annonces');
    }

    // =========================================================================
    // 6. JOURNAL D'AUDIT
    // =========================================================================

    public function audit(): void {
        $filter = $_GET['type'] ?? null;
        $logs = JournalAudit::getAllWithDetails(!empty($filter) ? (string)$filter : null);

        $this->render('admin.audit', [
            'title'         => 'Journal d\'Audit',
            'logs'          => $logs,
            'currentFilter' => $filter,
            'activeNav'     => 'audit',
        ]);
    }
}
