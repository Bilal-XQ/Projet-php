<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modèle Absence — Gestion des présences, retards et justifications
 */
class Absence extends Model {
    public const VALID_STATUTS = ['PRESENT', 'ABSENT', 'RETARD'];

    /**
     * Enregistre ou met à jour l'émargement d'un étudiant pour une séance.
     */
    public static function markAbsence(
        int $idEtudiant,
        int $idSeance,
        string $statut,
        bool $justifiee = false,
        ?string $motif = null
    ): int {
        if (!in_array($statut, self::VALID_STATUTS, true)) {
            throw new \InvalidArgumentException("Statut d'assiduité invalide. Permis : PRESENT, ABSENT, RETARD.");
        }

        $db = self::getDb();
        $stmt = $db->prepare("
            INSERT INTO absences (idEtudiant, idSeance, statut, justifiee, motif)
            VALUES (:idEtudiant, :idSeance, :statut, :justifiee, :motif)
            ON DUPLICATE KEY UPDATE 
                statut = VALUES(statut),
                justifiee = VALUES(justifiee),
                motif = VALUES(motif)
        ");
        $stmt->execute([
            'idEtudiant' => $idEtudiant,
            'idSeance'   => $idSeance,
            'statut'     => $statut,
            'justifiee'  => $justifiee ? 1 : 0,
            'motif'      => $motif !== null && trim($motif) !== '' ? trim($motif) : null,
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Récupère la feuille de présence complète pour une séance donnée.
     * Liste tous les étudiants de la filière avec leur statut d'assiduité courant.
     */
    public static function getAbsencesBySeance(int $idSeance): array {
        $db = self::getDb();

        // 1. Trouve la filière rattachée à la séance
        $stmtMod = $db->prepare("
            SELECT m.idFiliere, m.idModule, s.dateSeance, s.typeSeance
            FROM seances s
            JOIN modules m ON s.idModule = m.idModule
            WHERE s.idSeance = :id
            LIMIT 1
        ");
        $stmtMod->execute(['id' => $idSeance]);
        $seanceInfo = $stmtMod->fetch();

        if (!$seanceInfo) {
            return [];
        }

        // 2. Sélectionne les étudiants inscrits à cette filière avec leur statut d'absence
        $sql = "
            SELECT 
                u.idUtilisateur AS idEtudiant,
                u.nom AS etudiant_nom,
                u.prenom AS etudiant_prenom,
                u.email AS etudiant_email,
                e.cne AS etudiant_cne,
                COALESCE(a.statut, 'PRESENT') AS statut,
                COALESCE(a.justifiee, 0) AS justifiee,
                a.motif
            FROM inscriptions i
            JOIN etudiants e ON i.idEtudiant = e.idUtilisateur
            JOIN utilisateurs u ON e.idUtilisateur = u.idUtilisateur
            LEFT JOIN absences a ON (a.idEtudiant = u.idUtilisateur AND a.idSeance = :idSeance)
            WHERE i.idFiliere = :idFiliere
            ORDER BY u.nom ASC, u.prenom ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'idFiliere' => $seanceInfo['idFiliere'],
            'idSeance'  => $idSeance,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Calcule le bilan d'assiduité d'un étudiant pour un module.
     */
    public static function getStudentAbsenceSummary(int $idEtudiant, int $idModule): array {
        $db = self::getDb();
        $sql = "
            SELECT 
                COUNT(DISTINCT s.idSeance) AS total_seances,
                COUNT(CASE WHEN a.statut = 'ABSENT' THEN 1 END) AS total_absences,
                COUNT(CASE WHEN a.statut = 'ABSENT' AND a.justifiee = 0 THEN 1 END) AS non_justifiees,
                COUNT(CASE WHEN a.statut = 'ABSENT' AND a.justifiee = 1 THEN 1 END) AS justifiees,
                COUNT(CASE WHEN a.statut = 'RETARD' THEN 1 END) AS total_retards
            FROM seances s
            LEFT JOIN absences a ON (s.idSeance = a.idSeance AND a.idEtudiant = :idEtudiant)
            WHERE s.idModule = :idModule
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'idEtudiant' => $idEtudiant,
            'idModule'   => $idModule,
        ]);
        return $stmt->fetch() ?: [
            'total_seances'  => 0,
            'total_absences' => 0,
            'non_justifiees' => 0,
            'justifiees'     => 0,
            'total_retards'  => 0,
        ];
    }

    /**
     * Bilan global d'assiduité d'un étudiant pour tous ses modules.
     */
    public static function getStudentOverallAttendance(int $idEtudiant): array {
        $db = self::getDb();

        // 1. Récupère la filière de l'étudiant
        $stmtIns = $db->prepare("SELECT idFiliere FROM inscriptions WHERE idEtudiant = :id ORDER BY anneeUniversitaire DESC LIMIT 1");
        $stmtIns->execute(['id' => $idEtudiant]);
        $idFiliere = $stmtIns->fetchColumn();

        if (!$idFiliere) {
            return [
                'total_seances'  => 0,
                'total_presences'=> 0,
                'total_absences' => 0,
                'non_justifiees' => 0,
                'justifiees'     => 0,
                'total_retards'  => 0,
                'taux_assiduite' => 100.0,
            ];
        }

        // 2. Total des séances dispensées dans tous les modules de la filière
        $stmtSeances = $db->prepare("
            SELECT COUNT(s.idSeance) 
            FROM seances s
            JOIN modules m ON s.idModule = m.idModule
            WHERE m.idFiliere = :idFiliere
        ");
        $stmtSeances->execute(['idFiliere' => $idFiliere]);
        $totalSeances = (int)$stmtSeances->fetchColumn();

        // 3. Absences et retards enregistrés pour l'étudiant
        $stmtAbs = $db->prepare("
            SELECT 
                COUNT(CASE WHEN a.statut = 'ABSENT' THEN 1 END) AS total_absences,
                COUNT(CASE WHEN a.statut = 'ABSENT' AND a.justifiee = 0 THEN 1 END) AS non_justifiees,
                COUNT(CASE WHEN a.statut = 'ABSENT' AND a.justifiee = 1 THEN 1 END) AS justifiees,
                COUNT(CASE WHEN a.statut = 'RETARD' THEN 1 END) AS total_retards,
                COUNT(CASE WHEN a.statut = 'PRESENT' THEN 1 END) AS total_presents
            FROM absences a
            WHERE a.idEtudiant = :id
        ");
        $stmtAbs->execute(['id' => $idEtudiant]);
        $absStats = $stmtAbs->fetch() ?: [];

        $totalAbs = (int)($absStats['total_absences'] ?? 0);
        $totalRetards = (int)($absStats['total_retards'] ?? 0);
        $totalPresents = max(0, $totalSeances - $totalAbs);
        $taux = $totalSeances > 0 ? round(($totalPresents / $totalSeances) * 100, 1) : 100.0;

        return [
            'total_seances'   => $totalSeances,
            'total_presences' => $totalPresents,
            'total_absences'  => $totalAbs,
            'non_justifiees'  => (int)($absStats['non_justifiees'] ?? 0),
            'justifiees'      => (int)($absStats['justifiees'] ?? 0),
            'total_retards'   => $totalRetards,
            'taux_assiduite'  => $taux,
        ];
    }

    /**
     * Bilan d'assiduité ventilé par module pour un étudiant.
     */
    public static function getStudentAbsencesByAllModules(int $idEtudiant): array {
        $db = self::getDb();

        $stmtIns = $db->prepare("SELECT idFiliere FROM inscriptions WHERE idEtudiant = :id ORDER BY anneeUniversitaire DESC LIMIT 1");
        $stmtIns->execute(['id' => $idEtudiant]);
        $idFiliere = $stmtIns->fetchColumn();

        if (!$idFiliere) {
            return [];
        }

        $stmtMod = $db->prepare("
            SELECT m.idModule, m.code AS module_code, m.intitule AS module_intitule, m.semestre,
                   u.nom AS enseignant_nom, u.prenom AS enseignant_prenom
            FROM modules m
            LEFT JOIN utilisateurs u ON m.idEnseignant = u.idUtilisateur
            WHERE m.idFiliere = :idFiliere
            ORDER BY m.semestre ASC, m.code ASC
        ");
        $stmtMod->execute(['idFiliere' => $idFiliere]);
        $modules = $stmtMod->fetchAll();

        foreach ($modules as &$mod) {
            $summary = self::getStudentAbsenceSummary($idEtudiant, (int)$mod['idModule']);
            $mod['summary'] = $summary;
            $totalS = (int)$summary['total_seances'];
            $totalA = (int)$summary['total_absences'];
            $mod['taux_assiduite'] = $totalS > 0 ? round((max(0, $totalS - $totalA) / $totalS) * 100, 1) : 100.0;
        }
        unset($mod);

        return $modules;
    }

    /**
     * Journal détaillé de toutes les absences et retards d'un étudiant.
     */
    public static function getStudentDetailedAbsences(int $idEtudiant): array {
        $db = self::getDb();

        $sql = "
            SELECT 
                a.*,
                s.dateSeance, s.heureDebut, s.heureFin, s.typeSeance,
                m.code AS module_code, m.intitule AS module_intitule
            FROM absences a
            JOIN seances s ON a.idSeance = s.idSeance
            JOIN modules m ON s.idModule = m.idModule
            WHERE a.idEtudiant = :id AND a.statut IN ('ABSENT', 'RETARD')
            ORDER BY s.dateSeance DESC, s.heureDebut DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $idEtudiant]);
        return $stmt->fetchAll();
    }

    /**
     * Statistiques globales d'assiduité du département pour le dashboard administrateur.
     */
    public static function getDepartmentAttendanceStats(): array {
        $db = self::getDb();

        $stmt = $db->query("
            SELECT 
                COUNT(*) AS total_enregistrements,
                COUNT(CASE WHEN statut = 'PRESENT' THEN 1 END) AS total_presents,
                COUNT(CASE WHEN statut = 'ABSENT' THEN 1 END) AS total_absents,
                COUNT(CASE WHEN statut = 'ABSENT' AND justifiee = 1 THEN 1 END) AS absents_justifies,
                COUNT(CASE WHEN statut = 'ABSENT' AND justifiee = 0 THEN 1 END) AS absents_non_justifies,
                COUNT(CASE WHEN statut = 'RETARD' THEN 1 END) AS total_retards
            FROM absences
        ");
        $res = $stmt->fetch() ?: [];

        $total = (int)($res['total_enregistrements'] ?? 0);
        $presents = (int)($res['total_presents'] ?? 0);
        $tauxPresence = $total > 0 ? round(($presents / $total) * 100, 1) : 100.0;

        return [
            'totalEnregistrements' => $total,
            'presents'             => $presents,
            'absents'              => (int)($res['total_absents'] ?? 0),
            'absentsJustifies'     => (int)($res['absents_justifies'] ?? 0),
            'absentsNonJustifies'  => (int)($res['absents_non_justifies'] ?? 0),
            'retards'              => (int)($res['total_retards'] ?? 0),
            'tauxPresence'         => $tauxPresence,
        ];
    }
}
