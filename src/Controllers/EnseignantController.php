<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Utils\Auth;
use App\Utils\PdfGenerator;
use App\Models\Model;
use App\Models\Module;
use App\Models\Note;
use App\Models\Seance;
use App\Models\Absence;
use App\Models\Annonce;
use PDO;

/**
 * Contrôleur Enseignant (Phase 4)
 * Gère l'évaluation pédagogique, les séances, l'assiduité et les annonces par module.
 */
class EnseignantController extends Controller {
    public function __construct() {
        Auth::requireRole('ENSEIGNANT');
    }

    /**
     * Tableau de bord principal de l'enseignant.
     */
    public function dashboard(): void {
        $user = Auth::user();
        $db = Model::getDb();

        // 1. Modules attribués
        $stmtMod = $db->prepare("
            SELECT m.*, f.code AS filiere_code, f.intitule AS filiere_intitule,
                   (SELECT COUNT(*) FROM inscriptions i WHERE i.idFiliere = m.idFiliere) AS total_etudiants
            FROM modules m
            JOIN filieres f ON m.idFiliere = f.idFiliere
            WHERE m.idEnseignant = :id
            ORDER BY m.semestre ASC, m.code ASC
        ");
        $stmtMod->execute(['id' => $user->getId()]);
        $modules = $stmtMod->fetchAll();

        $primaryModuleStats = null;
        $totalEvaluationsTeacher = 0;
        $totalValidesTeacher = 0;

        foreach ($modules as &$mod) {
            $mStats = Note::getModuleStats((int)$mod['idModule']);
            $mod['stats'] = $mStats;
            $totalEvaluationsTeacher += $mStats['totalEvalues'];
            $totalValidesTeacher += (int)($mStats['total_valides'] ?? 0);
            if ($primaryModuleStats === null && $mStats['totalEvalues'] > 0) {
                $primaryModuleStats = $mStats;
                $primaryModuleStats['module_code'] = $mod['code'];
            }
        }
        unset($mod);

        if ($primaryModuleStats === null && !empty($modules)) {
            $primaryModuleStats = $modules[0]['stats'];
            $primaryModuleStats['module_code'] = $modules[0]['code'];
        }

        // 2. Dernières séances planifiées
        $stmtSeances = $db->prepare("
            SELECT s.*, m.code AS module_code, m.intitule AS module_intitule
            FROM seances s
            JOIN modules m ON s.idModule = m.idModule
            WHERE m.idEnseignant = :id
            ORDER BY s.dateSeance DESC, s.heureDebut DESC
            LIMIT 5
        ");
        $stmtSeances->execute(['id' => $user->getId()]);
        $recentSeances = $stmtSeances->fetchAll();

        // 3. Dernières annonces
        $stmtAnn = $db->prepare("
            SELECT a.*, m.code AS module_code
            FROM annonces a
            LEFT JOIN modules m ON a.idModule = m.idModule
            WHERE a.idAuteur = :id OR a.portee = 'GLOBALE'
            ORDER BY a.datePublication DESC
            LIMIT 4
        ");
        $stmtAnn->execute(['id' => $user->getId()]);
        $recentAnnonces = $stmtAnn->fetchAll();

        $this->render('enseignant.dashboard', [
            'title'               => 'Espace Pédagogique — Tableau de bord',
            'user'                => $user,
            'modules'             => $modules,
            'primaryModuleStats'  => $primaryModuleStats,
            'recentSeances'       => $recentSeances,
            'recentAnnonces'      => $recentAnnonces,
            'activeNav'           => 'dashboard',
        ]);
    }

    /**
     * Liste complète des modules attribués à l'enseignant.
     */
    public function modules(): void {
        $user = Auth::user();
        $db = Model::getDb();

        $stmt = $db->prepare("
            SELECT m.*, f.code AS filiere_code, f.intitule AS filiere_intitule,
                   (SELECT COUNT(*) FROM inscriptions i WHERE i.idFiliere = m.idFiliere) AS total_etudiants,
                   (SELECT COUNT(*) FROM seances s WHERE s.idModule = m.idModule) AS total_seances
            FROM modules m
            JOIN filieres f ON m.idFiliere = f.idFiliere
            WHERE m.idEnseignant = :id
            ORDER BY m.semestre ASC, m.code ASC
        ");
        $stmt->execute(['id' => $user->getId()]);
        $modules = $stmt->fetchAll();

        $this->render('enseignant.modules', [
            'title'     => 'Mes Modules d\'Enseignement',
            'user'      => $user,
            'modules'   => $modules,
            'activeNav' => 'modules',
        ]);
    }

    /**
     * Liste des étudiants inscrits au module avec synthèse de leurs notes et assiduité.
     */
    public function etudiants(int $id): void {
        $user = Auth::user();
        $module = $this->verifyModuleOwnership($id, $user->getId());

        $db = Model::getDb();
        $sql = "
            SELECT 
                u.idUtilisateur, u.nom, u.prenom, u.email,
                e.cne, e.dateNaissance,
                i.anneeUniversitaire, i.statut AS statut_inscription,
                n.noteFinale, n.statutValidation
            FROM inscriptions i
            JOIN etudiants e ON i.idEtudiant = e.idUtilisateur
            JOIN utilisateurs u ON e.idUtilisateur = u.idUtilisateur
            LEFT JOIN notes n ON (n.idEtudiant = u.idUtilisateur AND n.idModule = :idModule AND n.session = 'NORMALE')
            WHERE i.idFiliere = :idFiliere
            ORDER BY u.nom ASC, u.prenom ASC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'idFiliere' => $module['idFiliere'],
            'idModule'  => $id,
        ]);
        $etudiants = $stmt->fetchAll();

        // Récupération des synthèses d'absences pour chaque étudiant
        foreach ($etudiants as &$etu) {
            $etu['absences_summary'] = Absence::getStudentAbsenceSummary((int)$etu['idUtilisateur'], $id);
        }
        unset($etu);

        $this->render('enseignant.etudiants', [
            'title'     => 'Effectif Étudiants — ' . $module['code'],
            'module'    => $module,
            'etudiants' => $etudiants,
            'activeNav' => 'modules',
        ]);
    }

    /**
     * Point d'accès direct depuis la navigation pour la saisie des notes.
     */
    public function notesIndex(): void {
        $user = Auth::user();
        $db = Model::getDb();
        $stmt = $db->prepare("SELECT idModule FROM modules WHERE idEnseignant = :id ORDER BY code ASC LIMIT 1");
        $stmt->execute(['id' => $user->getId()]);
        $firstId = $stmt->fetchColumn();

        if ($firstId) {
            $this->redirect("/enseignant/modules/{$firstId}/notes");
        } else {
            $_SESSION['flash_info'] = "Aucun module ne vous est actuellement attribué.";
            $this->redirect('/enseignant/dashboard');
        }
    }

    /**
     * Point d'accès direct depuis la navigation pour les feuilles d'assiduité.
     */
    public function seancesIndex(): void {
        $user = Auth::user();
        $db = Model::getDb();
        $stmt = $db->prepare("SELECT idModule FROM modules WHERE idEnseignant = :id ORDER BY code ASC LIMIT 1");
        $stmt->execute(['id' => $user->getId()]);
        $firstId = $stmt->fetchColumn();

        if ($firstId) {
            $this->redirect("/enseignant/modules/{$firstId}/seances");
        } else {
            $_SESSION['flash_info'] = "Aucun module ne vous est actuellement attribué.";
            $this->redirect('/enseignant/dashboard');
        }
    }

    /**
     * Grille de saisie des notes (Contrôle Continu & Examen) avec calcul dynamique en direct.
     */
    public function notes(int $id): void {
        $user = Auth::user();
        $module = $this->verifyModuleOwnership($id, $user->getId());

        $session = (string)($_GET['session'] ?? 'NORMALE');
        if (!in_array($session, ['NORMALE', 'RATTRAPAGE'], true)) {
            $session = 'NORMALE';
        }

        $notesList = Note::getNotesByModule($id, $session);

        $this->render('enseignant.notes', [
            'title'          => 'Saisie des Notes — ' . $module['code'],
            'module'         => $module,
            'notesList'      => $notesList,
            'currentSession' => $session,
            'csrfToken'      => Auth::csrfToken(),
            'activeNav'      => 'notes',
        ]);
    }

    /**
     * Enregistre en lot les notes saisies par l'enseignant.
     */
    public function saveNotes(int $id): void {
        $user = Auth::user();
        $module = $this->verifyModuleOwnership($id, $user->getId());

        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect("/enseignant/modules/{$id}/notes");
            return;
        }

        $session = (string)($_POST['session'] ?? 'NORMALE');
        $rawNotes = $_POST['notes'] ?? [];

        if (!is_array($rawNotes)) {
            $_SESSION['flash_error'] = "Format de soumission des notes invalide.";
            $this->redirect("/enseignant/modules/{$id}/notes?session={$session}");
            return;
        }

        $countUpdated = 0;

        try {
            foreach ($rawNotes as $idEtudiant => $data) {
                $idEtudiant = (int)$idEtudiant;
                if ($idEtudiant <= 0) continue;

                $noteRaw = null;
                if (isset($data['note']) && trim((string)$data['note']) !== '') {
                    $noteRaw = (float)$data['note'];
                } elseif (isset($data['exam']) && trim((string)$data['exam']) !== '') {
                    $noteRaw = (float)$data['exam'];
                } elseif (isset($data['cc']) && trim((string)$data['cc']) !== '') {
                    $noteRaw = (float)$data['cc'];
                }

                $remarque = isset($data['remarque']) ? (string)$data['remarque'] : null;

                // Validation intervalle [0, 20]
                if ($noteRaw !== null && ($noteRaw < 0 || $noteRaw > 20)) {
                    throw new \InvalidArgumentException("La note doit être comprise entre 0 et 20.");
                }

                // Enregistre uniquement si au moins une note ou remarque est renseignée
                if ($noteRaw !== null || !empty($remarque)) {
                    Note::saveOrUpdateNote($idEtudiant, $id, $noteRaw, $remarque, $session, $user->getId());
                    $countUpdated++;
                }
            }

            $_SESSION['flash_success'] = "{$countUpdated} évaluation(s) ont été enregistrées et auditées avec succès.";

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Erreur lors de l'enregistrement des notes : " . $e->getMessage();
        }

        $this->redirect("/enseignant/modules/{$id}/notes?session={$session}");
    }

    /**
     * Gestion des séances d'un module.
     */
    public function seances(int $id): void {
        $user = Auth::user();
        $module = $this->verifyModuleOwnership($id, $user->getId());

        $seances = Seance::getSeancesByModule($id);

        $this->render('enseignant.seances', [
            'title'     => 'Séances & Assiduité — ' . $module['code'],
            'module'    => $module,
            'seances'   => $seances,
            'csrfToken' => Auth::csrfToken(),
            'activeNav' => 'seances',
        ]);
    }

    /**
     * Déclare une nouvelle séance pédagogique pour un module.
     */
    public function createSeance(int $id): void {
        $user = Auth::user();
        $module = $this->verifyModuleOwnership($id, $user->getId());

        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect("/enseignant/modules/{$id}/seances");
            return;
        }

        $date = trim((string)($_POST['dateSeance'] ?? ''));
        $debut = trim((string)($_POST['heureDebut'] ?? ''));
        $fin = trim((string)($_POST['heureFin'] ?? ''));
        $type = (string)($_POST['typeSeance'] ?? 'COURS');

        if ($date === '' || $debut === '' || $fin === '') {
            $_SESSION['flash_error'] = "La date, l'heure de début et l'heure de fin sont obligatoires.";
            $this->redirect("/enseignant/modules/{$id}/seances");
            return;
        }

        try {
            $idSeance = Seance::createSeance($id, $date, $debut, $fin, $type);
            $_SESSION['flash_success'] = "Séance de {$type} planifiée avec succès. Vous pouvez maintenant émarger.";
            $this->redirect("/enseignant/seances/{$idSeance}/absences");
            return;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Erreur lors de la planification : " . $e->getMessage();
            $this->redirect("/enseignant/modules/{$id}/seances");
        }
    }

    /**
     * Feuille d'émargement / présence pour une séance donnée.
     */
    public function absences(int $id): void {
        $user = Auth::user();
        $seance = Seance::findById($id);

        if (!$seance || (int)$seance['idEnseignant'] !== $user->getId()) {
            http_response_code(403);
            echo "Accès refusé à cette feuille de présence.";
            exit;
        }

        $absencesList = Absence::getAbsencesBySeance($id);

        $this->render('enseignant.absences', [
            'title'        => "Feuille de Présence — {$seance['typeSeance']} du " . date('d/m/Y', strtotime($seance['dateSeance'])),
            'seance'       => $seance,
            'absencesList' => $absencesList,
            'csrfToken'    => Auth::csrfToken(),
            'activeNav'    => 'seances',
        ]);
    }

    /**
     * Enregistre l'émargement complet d'une séance.
     */
    public function saveAbsences(int $id): void {
        $user = Auth::user();
        $seance = Seance::findById($id);

        if (!$seance || (int)$seance['idEnseignant'] !== $user->getId()) {
            http_response_code(403);
            echo "Accès refusé.";
            exit;
        }

        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect("/enseignant/seances/{$id}/absences");
            return;
        }

        $records = $_POST['absences'] ?? [];
        $count = 0;

        try {
            foreach ($records as $idEtudiant => $data) {
                $idEtudiant = (int)$idEtudiant;
                if ($idEtudiant <= 0) continue;

                $statut = (string)($data['statut'] ?? 'PRESENT');
                $justifiee = !empty($data['justifiee']);
                $motif = isset($data['motif']) ? (string)$data['motif'] : null;

                Absence::markAbsence($idEtudiant, $id, $statut, $justifiee, $motif);
                $count++;
            }

            $_SESSION['flash_success'] = "Feuille d'émargement enregistrée avec succès ({$count} étudiants).";

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Erreur lors de l'enregistrement de l'assiduité : " . $e->getMessage();
        }

        $this->redirect("/enseignant/seances/{$id}/absences");
    }

    /**
     * Espace des annonces pédagogiques de l'enseignant.
     */
    public function annonces(): void {
        $user = Auth::user();
        $db = Model::getDb();

        // Modules de l'enseignant
        $stmtMod = $db->prepare("SELECT idModule, code, intitule FROM modules WHERE idEnseignant = :id ORDER BY code ASC");
        $stmtMod->execute(['id' => $user->getId()]);
        $modules = $stmtMod->fetchAll();

        // Annonces publiées par cet enseignant ou avis globaux
        $stmtAnn = $db->prepare("
            SELECT a.*, m.code AS module_code, m.intitule AS module_intitule,
                   u.nom AS auteur_nom, u.prenom AS auteur_prenom, u.role AS auteur_role, u.email AS auteur_email
            FROM annonces a
            JOIN utilisateurs u ON a.idAuteur = u.idUtilisateur
            LEFT JOIN modules m ON a.idModule = m.idModule
            WHERE a.idAuteur = :id OR a.portee = 'GLOBALE'
            ORDER BY a.datePublication DESC
        ");
        $stmtAnn->execute(['id' => $user->getId()]);
        $annonces = $stmtAnn->fetchAll();

        $this->render('enseignant.annonces', [
            'title'     => 'Annonces Pédagogiques',
            'modules'   => $modules,
            'annonces'  => $annonces,
            'csrfToken' => Auth::csrfToken(),
            'activeNav' => 'annonces',
        ]);
    }

    /**
     * Publie une nouvelle annonce ciblant l'un des modules de l'enseignant.
     */
    public function createAnnonce(): void {
        $user = Auth::user();

        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/enseignant/annonces');
            return;
        }

        $titre = trim((string)($_POST['titre'] ?? ''));
        $contenu = trim((string)($_POST['contenu'] ?? ''));
        $idModule = (int)($_POST['idModule'] ?? 0);

        if ($titre === '' || $contenu === '' || $idModule <= 0) {
            $_SESSION['flash_error'] = "Veuillez renseigner le titre, le contenu et sélectionner un module.";
            $this->redirect('/enseignant/annonces');
            return;
        }

        $this->verifyModuleOwnership($idModule, $user->getId());

        try {
            Annonce::create($user->getId(), $titre, $contenu, 'MODULE', $idModule);
            $_SESSION['flash_success'] = "L'annonce pédagogique a été publiée pour le module.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Erreur lors de la publication : " . $e->getMessage();
        }

        $this->redirect('/enseignant/annonces');
    }

    /**
     * Modifie une annonce existante publiée par l'enseignant.
     */
    public function editAnnonce(): void {
        $user = Auth::user();

        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/enseignant/annonces');
            return;
        }

        $id = (int)($_POST['idAnnonce'] ?? 0);
        $titre = trim((string)($_POST['titre'] ?? ''));
        $contenu = trim((string)($_POST['contenu'] ?? ''));
        $idModule = (int)($_POST['idModule'] ?? 0);

        if ($id <= 0 || $titre === '' || $contenu === '' || $idModule <= 0) {
            $_SESSION['flash_error'] = "Veuillez renseigner le titre, le contenu et sélectionner un module.";
            $this->redirect('/enseignant/annonces');
            return;
        }

        $this->verifyModuleOwnership($idModule, $user->getId());

        try {
            $db = Model::getDb();
            // Vérifie que l'enseignant est l'auteur
            $checkStmt = $db->prepare("SELECT idAnnonce FROM annonces WHERE idAnnonce = :id AND idAuteur = :author");
            $checkStmt->execute(['id' => $id, 'author' => $user->getId()]);
            if (!$checkStmt->fetch()) {
                throw new \RuntimeException("Vous n'êtes pas autorisé à modifier cette annonce.");
            }

            Annonce::update($id, $titre, $contenu, 'MODULE', $idModule);
            $_SESSION['flash_success'] = "L'annonce a été mise à jour avec succès.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Erreur de modification : " . $e->getMessage();
        }

        $this->redirect('/enseignant/annonces');
    }

    /**
     * Supprime une annonce publiée par l'enseignant.
     */
    public function deleteAnnonce(): void {
        $user = Auth::user();

        if (!Auth::validateCsrf($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = "Jeton de sécurité invalide.";
            $this->redirect('/enseignant/annonces');
            return;
        }

        $id = (int)($_POST['idAnnonce'] ?? 0);
        if ($id > 0) {
            $db = Model::getDb();
            $stmt = $db->prepare("DELETE FROM annonces WHERE idAnnonce = :id AND idAuteur = :author");
            $stmt->execute(['id' => $id, 'author' => $user->getId()]);
            $_SESSION['flash_success'] = "L'annonce a été supprimée.";
        }

        $this->redirect('/enseignant/annonces');
    }

    /**
     * Téléchargement du procès-verbal officiel des notes du module au format PDF.
     */
    public function downloadModulePdf(int $id): void {
        $user = Auth::user();
        $module = $this->verifyModuleOwnership($id, $user->getId());

        $session = (string)($_GET['session'] ?? 'NORMALE');
        if (!in_array($session, ['NORMALE', 'RATTRAPAGE'], true)) {
            $session = 'NORMALE';
        }

        // Complète les informations du module avec l'enseignant et la filière
        $db = Model::getDb();
        $stmt = $db->prepare("
            SELECT m.*, f.code AS filiere_code, f.intitule AS filiere_intitule,
                   u.nom AS enseignant_nom, u.prenom AS enseignant_prenom
            FROM modules m
            JOIN filieres f ON m.idFiliere = f.idFiliere
            JOIN utilisateurs u ON m.idEnseignant = u.idUtilisateur
            WHERE m.idModule = :id
        ");
        $stmt->execute(['id' => $id]);
        $modDetails = $stmt->fetch() ?: $module;

        $students = Note::getNotesByModule($id, $session);
        $stats = Note::getModuleStats($id);

        $pdfContent = PdfGenerator::generateModuleRoster($modDetails, $students, $stats);
        $filename = "PV_Notes_" . preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$module['code']) . "_{$session}.pdf";

        PdfGenerator::stream($pdfContent, $filename, false);
    }

    /**
     * Vérifie qu'un module est bien attribué à l'enseignant connecté.
     */
    private function verifyModuleOwnership(int $idModule, int $idTeacher): array {
        $module = Module::findById($idModule);
        if (!$module || (int)($module['idEnseignant'] ?? 0) !== $idTeacher) {
            http_response_code(403);
            echo "Accès refusé : vous n'êtes pas le responsable pédagogique de ce module.";
            exit;
        }
        return $module;
    }
}
