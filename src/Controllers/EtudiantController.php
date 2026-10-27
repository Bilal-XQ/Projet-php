<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Utils\Auth;
use App\Utils\PdfGenerator;
use App\Models\Model;
use App\Models\Note;
use App\Models\Absence;
use PDO;

/**
 * Contrôleur Espace Étudiant (Phase 5)
 * Gère la consultation du cursus, des évaluations, de l'assiduité, des annonces et l'édition du relevé PDF officiel.
 */
class EtudiantController extends Controller {
    public function __construct() {
        Auth::requireRole('ETUDIANT');
    }

    /**
     * Tableau de bord principal de l'étudiant.
     */
    public function dashboard(): void {
        $user = Auth::user();
        $db = Model::getDb();

        // 1. Inscription active & Filière
        $stmtIns = $db->prepare("
            SELECT i.*, f.code AS filiere_code, f.intitule AS filiere_intitule, f.description AS filiere_description
            FROM inscriptions i
            JOIN filieres f ON i.idFiliere = f.idFiliere
            WHERE i.idEtudiant = :id
            ORDER BY i.anneeUniversitaire DESC 
            LIMIT 1
        ");
        $stmtIns->execute(['id' => $user->getId()]);
        $inscription = $stmtIns->fetch() ?: null;

        // 2. Moyenne Générale et Bilan Académique (GPA)
        $gpa = Note::getStudentGPA($user->getId());

        // 3. Bilan Global d'Assiduité
        $attendance = Absence::getStudentOverallAttendance($user->getId());

        // 4. Notes obtenues
        $notes = Note::getStudentNotesWithDetails($user->getId());

        // 5. Dernières annonces ciblées (Globale ou Modules de la filière de l'étudiant)
        $filiereId = $inscription ? (int)$inscription['idFiliere'] : 0;
        $stmtAnn = $db->prepare("
            SELECT a.*, u.nom AS auteur_nom, u.prenom AS auteur_prenom, m.code AS module_code
            FROM annonces a
            JOIN utilisateurs u ON a.idAuteur = u.idUtilisateur
            LEFT JOIN modules m ON a.idModule = m.idModule
            WHERE a.portee = 'GLOBALE' 
               OR (a.portee = 'MODULE' AND m.idFiliere = :idFiliere)
            ORDER BY a.datePublication DESC
            LIMIT 4
        ");
        $stmtAnn->execute(['idFiliere' => $filiereId]);
        $annonces = $stmtAnn->fetchAll();

        $this->render('etudiant.dashboard', [
            'title'       => 'Portail Étudiant — Tableau de bord',
            'user'        => $user,
            'inscription' => $inscription,
            'gpa'         => $gpa,
            'attendance'  => $attendance,
            'notes'       => $notes,
            'annonces'    => $annonces,
            'activeNav'   => 'dashboard',
        ]);
    }

    /**
     * Relevé complet des notes et appréciations des enseignants.
     */
    public function notes(): void {
        $user = Auth::user();

        $session = (string)($_GET['session'] ?? 'NORMALE');
        if (!in_array($session, ['NORMALE', 'RATTRAPAGE'], true)) {
            $session = 'NORMALE';
        }

        $notes = Note::getStudentNotesWithDetails($user->getId(), $session);
        $gpa = Note::getStudentGPA($user->getId(), $session);

        $this->render('etudiant.notes', [
            'title'          => 'Mes Notes & Résultats Académiques',
            'user'           => $user,
            'notes'          => $notes,
            'gpa'            => $gpa,
            'currentSession' => $session,
            'activeNav'      => 'notes',
        ]);
    }

    /**
     * Bilan complet de l'assiduité et journal des absences.
     */
    public function absences(): void {
        $user = Auth::user();

        $overall = Absence::getStudentOverallAttendance($user->getId());
        $byModule = Absence::getStudentAbsencesByAllModules($user->getId());
        $detailed = Absence::getStudentDetailedAbsences($user->getId());

        $this->render('etudiant.absences', [
            'title'     => 'Bilan d\'Assiduité & Absences',
            'user'      => $user,
            'overall'   => $overall,
            'byModule'  => $byModule,
            'detailed'  => $detailed,
            'activeNav' => 'absences',
        ]);
    }

    /**
     * Flux d'annonces et communications ciblées pour l'étudiant.
     */
    public function annonces(): void {
        $user = Auth::user();
        $db = Model::getDb();

        // Récupération de la filière active
        $stmtIns = $db->prepare("SELECT idFiliere FROM inscriptions WHERE idEtudiant = :id ORDER BY anneeUniversitaire DESC LIMIT 1");
        $stmtIns->execute(['id' => $user->getId()]);
        $filiereId = (int)$stmtIns->fetchColumn();

        $stmt = $db->prepare("
            SELECT a.*, u.nom AS auteur_nom, u.prenom AS auteur_prenom, u.role AS auteur_role,
                   m.code AS module_code, m.intitule AS module_intitule
            FROM annonces a
            JOIN utilisateurs u ON a.idAuteur = u.idUtilisateur
            LEFT JOIN modules m ON a.idModule = m.idModule
            WHERE a.portee = 'GLOBALE' 
               OR (a.portee = 'MODULE' AND m.idFiliere = :idFiliere)
            ORDER BY a.datePublication DESC
        ");
        $stmt->execute(['idFiliere' => $filiereId]);
        $annonces = $stmt->fetchAll();

        $this->render('etudiant.annonces', [
            'title'     => 'Avis & Annonces Pédagogiques',
            'user'      => $user,
            'annonces'  => $annonces,
            'activeNav' => 'annonces',
        ]);
    }

    /**
     * Téléchargement du relevé de notes officiel au format PDF 1.4.
     */
    public function downloadRelevePdf(): void {
        $user = Auth::user();
        $db = Model::getDb();

        // 1. Récupération des informations d'inscription
        $stmtIns = $db->prepare("
            SELECT i.*, f.code, f.intitule 
            FROM inscriptions i
            JOIN filieres f ON i.idFiliere = f.idFiliere
            WHERE i.idEtudiant = :id 
            ORDER BY i.anneeUniversitaire DESC 
            LIMIT 1
        ");
        $stmtIns->execute(['id' => $user->getId()]);
        $filiere = $stmtIns->fetch() ?: [
            'code'               => 'INFO-L3',
            'intitule'           => 'Licence Informatique',
            'anneeUniversitaire' => '2026-2027',
        ];

        // 2. Récupération des notes et calcul du GPA
        $notes = Note::getStudentNotesWithDetails($user->getId(), 'NORMALE');
        $gpa = Note::getStudentGPA($user->getId(), 'NORMALE');

        $studentData = [
            'nom'           => $user->getNom(),
            'prenom'        => $user->getPrenom(),
            'cne'           => $user->getCne(),
            'dateNaissance' => $user->getDateNaissance(),
        ];

        $pdfContent = PdfGenerator::generateStudentReleve($studentData, $filiere, $notes, $gpa);
        $filename = "Releve_Notes_" . preg_replace('/[^a-zA-Z0-9_-]/', '', $user->getCne() ?: 'ETUDIANT') . ".pdf";

        PdfGenerator::stream($pdfContent, $filename, false);
    }
}
