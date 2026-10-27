<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modèle Note — Évaluations pédagogiques, calcul des moyennes et audit réglementaire
 */
class Note extends Model {
    /**
     * Récupère la liste des étudiants inscrits au cursus du module avec leurs notes éventuelles.
     */
    public static function getNotesByModule(int $idModule, string $session = 'NORMALE'): array {
        $db = self::getDb();

        // 1. Détermine la filière du module
        $stmtMod = $db->prepare("SELECT idFiliere FROM modules WHERE idModule = :id LIMIT 1");
        $stmtMod->execute(['id' => $idModule]);
        $idFiliere = $stmtMod->fetchColumn();

        if (!$idFiliere) {
            return [];
        }

        // 2. Sélectionne tous les étudiants inscrits à cette filière avec jointure sur la note de cette session
        $sql = "
            SELECT 
                u.idUtilisateur AS idEtudiant,
                u.nom AS etudiant_nom,
                u.prenom AS etudiant_prenom,
                u.email AS etudiant_email,
                e.cne AS etudiant_cne,
                n.idNote,
                n.noteCC,
                n.noteExam,
                n.noteFinale,
                n.statutValidation,
                n.session,
                n.remarque
            FROM inscriptions i
            JOIN etudiants e ON i.idEtudiant = e.idUtilisateur
            JOIN utilisateurs u ON e.idUtilisateur = u.idUtilisateur
            LEFT JOIN notes n ON (n.idEtudiant = u.idUtilisateur AND n.idModule = :idModule AND n.session = :session)
            WHERE i.idFiliere = :idFiliere
            ORDER BY u.nom ASC, u.prenom ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'idFiliere' => $idFiliere,
            'idModule'  => $idModule,
            'session'   => $session,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Calcule la note finale et le statut réglementaire sans coefficient (la note est directement la note).
     */
    public static function calculateValidation($noteOrCc, ?float $exam = null, string $session = 'NORMALE'): array {
        if ($noteOrCc === null && $exam === null) {
            return ['noteFinale' => null, 'statut' => 'NV'];
        }

        // La note est directement la note sans coefficient
        $val = $exam !== null ? (float)$exam : (float)$noteOrCc;
        $finale = round($val, 2);

        if ($finale >= 10.00) {
            $statut = 'V';
        } else {
            $statut = ($session === 'NORMALE') ? 'RATT' : 'NV';
        }

        return ['noteFinale' => $finale, 'statut' => $statut];
    }

    /**
     * Enregistre ou met à jour la note d'un étudiant avec déclenchement automatique d'un audit (la note est directement la note).
     */
    public static function saveOrUpdateNote(
        int $idEtudiant,
        int $idModule,
        ?float $noteVal,
        $arg4 = null,
        $arg5 = null,
        $arg6 = 'NORMALE',
        ?int $arg7 = null
    ): int {
        $db = self::getDb();

        if ($arg7 !== null) {
            // Signature historique à 7 arguments : ($idEtudiant, $idModule, $cc, $exam, $remarque, $session, $idProf)
            $note = $arg4 !== null ? (float)$arg4 : $noteVal;
            $remarque = is_string($arg5) ? $arg5 : null;
            $session = is_string($arg6) ? $arg6 : 'NORMALE';
            $idProfesseur = $arg7;
        } else {
            // Signature directe : ($idEtudiant, $idModule, $note, $remarque, $session, $idProf)
            $note = $noteVal;
            $remarque = is_string($arg4) ? $arg4 : null;
            $session = is_string($arg5) ? $arg5 : 'NORMALE';
            $idProfesseur = is_int($arg6) ? $arg6 : (int)$arg4;
        }

        $calc = self::calculateValidation($note, null, $session);
        $noteFinale = $calc['noteFinale'];
        $statut = $calc['statut'];

        // 1. Vérifie si une note existe déjà pour cette session
        $stmtCheck = $db->prepare("
            SELECT idNote, noteFinale FROM notes 
            WHERE idEtudiant = :idEtudiant AND idModule = :idModule AND session = :session
            LIMIT 1
        ");
        $stmtCheck->execute([
            'idEtudiant' => $idEtudiant,
            'idModule'   => $idModule,
            'session'    => $session,
        ]);
        $existing = $stmtCheck->fetch();

        $db->beginTransaction();

        try {
            if ($existing) {
                $idNote = (int)$existing['idNote'];
                $ancienneValeur = $existing['noteFinale'] !== null ? (float)$existing['noteFinale'] : null;

                $stmtUpdate = $db->prepare("
                    UPDATE notes 
                    SET noteCC = NULL, noteExam = :note, noteFinale = :finale, 
                        statutValidation = :statut, remarque = :remarque
                    WHERE idNote = :idNote
                ");
                $stmtUpdate->execute([
                    'note'     => $noteFinale,
                    'finale'   => $noteFinale,
                    'statut'   => $statut,
                    'remarque' => $remarque !== null && trim($remarque) !== '' ? trim($remarque) : null,
                    'idNote'   => $idNote,
                ]);

                // Journalisation de la modification
                $stmtAudit = $db->prepare("
                    INSERT INTO journal_audit_notes (idNote, idUtilisateur, typeAction, ancienneValeur, nouvelleValeur)
                    VALUES (:idNote, :idUser, 'MODIFICATION', :ancienne, :nouvelle)
                ");
                $stmtAudit->execute([
                    'idNote'   => $idNote,
                    'idUser'   => $idProfesseur,
                    'ancienne' => $ancienneValeur,
                    'nouvelle' => $noteFinale,
                ]);

            } else {
                $stmtInsert = $db->prepare("
                    INSERT INTO notes (idEtudiant, idModule, noteCC, noteExam, noteFinale, session, statutValidation, remarque)
                    VALUES (:idEtudiant, :idModule, NULL, :note, :finale, :session, :statut, :remarque)
                ");
                $stmtInsert->execute([
                    'idEtudiant' => $idEtudiant,
                    'idModule'   => $idModule,
                    'note'       => $noteFinale,
                    'finale'     => $noteFinale,
                    'session'    => $session,
                    'statut'     => $statut,
                    'remarque'   => $remarque !== null && trim($remarque) !== '' ? trim($remarque) : null,
                ]);
                $idNote = (int)$db->lastInsertId();

                // Journalisation de la création
                $stmtAudit = $db->prepare("
                    INSERT INTO journal_audit_notes (idNote, idUtilisateur, typeAction, ancienneValeur, nouvelleValeur)
                    VALUES (:idNote, :idUser, 'CREATION', NULL, :nouvelle)
                ");
                $stmtAudit->execute([
                    'idNote'   => $idNote,
                    'idUser'   => $idProfesseur,
                    'nouvelle' => $noteFinale,
                ]);
            }

            $db->commit();
            return $idNote;

        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Récupère la liste complète des notes d'un étudiant pour tous les modules de sa filière active.
     */
    public static function getStudentNotesWithDetails(int $idEtudiant, string $session = 'NORMALE'): array {
        $db = self::getDb();

        // 1. Détermine la filière active de l'étudiant
        $stmtIns = $db->prepare("
            SELECT idFiliere, anneeUniversitaire 
            FROM inscriptions 
            WHERE idEtudiant = :id 
            ORDER BY anneeUniversitaire DESC 
            LIMIT 1
        ");
        $stmtIns->execute(['id' => $idEtudiant]);
        $ins = $stmtIns->fetch();

        if (!$ins) {
            return [];
        }

        // 2. Sélectionne tous les modules de la filière et la note associée
        $sql = "
            SELECT 
                m.idModule, m.code AS module_code, m.intitule AS module_intitule,
                m.semestre, m.coefficient,
                u.nom AS enseignant_nom, u.prenom AS enseignant_prenom,
                n.idNote, n.noteCC, n.noteExam, n.noteFinale,
                n.statutValidation, n.remarque, n.session
            FROM modules m
            LEFT JOIN utilisateurs u ON m.idEnseignant = u.idUtilisateur
            LEFT JOIN notes n ON (n.idModule = m.idModule AND n.idEtudiant = :idEtudiant AND n.session = :session)
            WHERE m.idFiliere = :idFiliere
            ORDER BY m.semestre ASC, m.code ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'idEtudiant' => $idEtudiant,
            'idFiliere'  => $ins['idFiliere'],
            'session'    => $session,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Calcule la Moyenne Générale annuelle directe d'un étudiant et la décision du jury (sans coefficient).
     */
    public static function getStudentGPA(int $idEtudiant, string $session = 'NORMALE'): array {
        $notes = self::getStudentNotesWithDetails($idEtudiant, $session);

        $totalPoints = 0.0;
        $modulesNotes = 0;
        $valides = 0;
        $ratt = 0;
        $nv = 0;
        $hasEliminatoire = false; // note < 7.00

        foreach ($notes as $n) {
            if ($n['noteFinale'] !== null) {
                $finale = (float)$n['noteFinale'];
                $totalPoints += $finale;
                $modulesNotes++;

                if ($n['statutValidation'] === 'V') {
                    $valides++;
                } elseif ($n['statutValidation'] === 'RATT') {
                    $ratt++;
                } else {
                    $nv++;
                }

                if ($finale < 7.00) {
                    $hasEliminatoire = true;
                }
            }
        }

        // Moyenne directe sans coefficient
        $moyenne = $modulesNotes > 0 ? round($totalPoints / $modulesNotes, 2) : null;

        $decision = 'En cours d\'évaluation';
        if ($modulesNotes === count($notes) && count($notes) > 0) {
            if ($moyenne !== null && $moyenne >= 10.00 && !$hasEliminatoire) {
                $decision = 'Admis';
            } elseif ($session === 'NORMALE') {
                $decision = 'Session de Rattrapage';
            } else {
                $decision = 'Ajourné';
            }
        }

        return [
            'moyenne'         => $moyenne,
            'totalPoints'     => round($totalPoints, 2),
            'totalCoeff'      => 0.0,
            'totalModules'    => count($notes),
            'modulesEvalues'  => $modulesNotes,
            'modulesValides'  => $valides,
            'modulesRatt'     => $ratt,
            'modulesNV'       => $nv,
            'decision'        => $decision,
            'hasEliminatoire' => $hasEliminatoire,
        ];
    }

    /**
     * Statistiques globales du département pour le tableau de bord administrateur.
     */
    public static function getDepartmentStats(): array {
        $db = self::getDb();

        $stmt = $db->query("
            SELECT 
                COUNT(*) AS total_notes,
                AVG(noteFinale) AS moyenne_generale,
                COUNT(CASE WHEN statutValidation = 'V' THEN 1 END) AS total_valides,
                COUNT(CASE WHEN statutValidation = 'RATT' THEN 1 END) AS total_ratt,
                COUNT(CASE WHEN statutValidation = 'NV' THEN 1 END) AS total_nv,
                COUNT(CASE WHEN noteFinale < 10 THEN 1 END) AS dist_ajourne,
                COUNT(CASE WHEN noteFinale >= 10 AND noteFinale < 12 THEN 1 END) AS dist_passable,
                COUNT(CASE WHEN noteFinale >= 12 AND noteFinale < 14 THEN 1 END) AS dist_assez_bien,
                COUNT(CASE WHEN noteFinale >= 14 AND noteFinale < 16 THEN 1 END) AS dist_bien,
                COUNT(CASE WHEN noteFinale >= 16 THEN 1 END) AS dist_tres_bien
            FROM notes
            WHERE noteFinale IS NOT NULL AND session = 'NORMALE'
        ");
        $stats = $stmt->fetch() ?: [];

        $total = (int)($stats['total_notes'] ?? 0);
        $valides = (int)($stats['total_valides'] ?? 0);
        $tauxReussite = $total > 0 ? round(($valides / $total) * 100, 1) : 0.0;
        $moyenne = $stats['moyenne_generale'] !== null ? round((float)$stats['moyenne_generale'], 2) : 0.0;

        return [
            'totalNotes'      => $total,
            'moyenneGenerale' => $moyenne,
            'tauxReussite'    => $tauxReussite,
            'distribution'    => [
                'ajourne'    => (int)($stats['dist_ajourne'] ?? 0),
                'passable'   => (int)($stats['dist_passable'] ?? 0),
                'assez_bien' => (int)($stats['dist_assez_bien'] ?? 0),
                'bien'       => (int)($stats['dist_bien'] ?? 0),
                'tres_bien'  => (int)($stats['dist_tres_bien'] ?? 0),
            ],
        ];
    }

    /**
     * Statistiques spécifiques à un module pour le tableau de bord enseignant.
     */
    public static function getModuleStats(int $idModule): array {
        $db = self::getDb();

        $stmt = $db->prepare("
            SELECT 
                COUNT(*) AS total_evalues,
                AVG(noteCC) AS moyenne_cc,
                AVG(noteExam) AS moyenne_exam,
                AVG(noteFinale) AS moyenne_finale,
                MIN(noteFinale) AS min_finale,
                MAX(noteFinale) AS max_finale,
                COUNT(CASE WHEN statutValidation = 'V' THEN 1 END) AS total_valides,
                COUNT(CASE WHEN noteFinale < 8 THEN 1 END) AS tranche_0_8,
                COUNT(CASE WHEN noteFinale >= 8 AND noteFinale < 10 THEN 1 END) AS tranche_8_10,
                COUNT(CASE WHEN noteFinale >= 10 AND noteFinale < 12 THEN 1 END) AS tranche_10_12,
                COUNT(CASE WHEN noteFinale >= 12 AND noteFinale < 14 THEN 1 END) AS tranche_12_14,
                COUNT(CASE WHEN noteFinale >= 14 AND noteFinale < 16 THEN 1 END) AS tranche_14_16,
                COUNT(CASE WHEN noteFinale >= 16 THEN 1 END) AS tranche_16_20,
                COUNT(CASE WHEN noteFinale < 10 THEN 1 END) AS dist_ajourne,
                COUNT(CASE WHEN noteFinale >= 10 AND noteFinale < 12 THEN 1 END) AS dist_passable,
                COUNT(CASE WHEN noteFinale >= 12 AND noteFinale < 14 THEN 1 END) AS dist_assez_bien,
                COUNT(CASE WHEN noteFinale >= 14 AND noteFinale < 16 THEN 1 END) AS dist_bien,
                COUNT(CASE WHEN noteFinale >= 16 THEN 1 END) AS dist_tres_bien
            FROM notes
            WHERE idModule = :id AND session = 'NORMALE' AND noteFinale IS NOT NULL
        ");
        $stmt->execute(['id' => $idModule]);
        $stats = $stmt->fetch() ?: [];

        $total = (int)($stats['total_evalues'] ?? 0);
        $valides = (int)($stats['total_valides'] ?? 0);
        $tauxReussite = $total > 0 ? round(($valides / $total) * 100, 1) : 0.0;

        return [
            'totalEvalues'  => $total,
            'moyenneCC'     => $stats['moyenne_cc'] !== null ? round((float)$stats['moyenne_cc'], 2) : 0.0,
            'moyenneExam'   => $stats['moyenne_exam'] !== null ? round((float)$stats['moyenne_exam'], 2) : 0.0,
            'moyenneFinale' => $stats['moyenne_finale'] !== null ? round((float)$stats['moyenne_finale'], 2) : 0.0,
            'minFinale'     => $stats['min_finale'] !== null ? round((float)$stats['min_finale'], 2) : 0.0,
            'maxFinale'     => $stats['max_finale'] !== null ? round((float)$stats['max_finale'], 2) : 0.0,
            'tauxReussite'  => $tauxReussite,
            'tranche_0_8'   => (int)($stats['tranche_0_8'] ?? 0),
            'tranche_8_10'  => (int)($stats['tranche_8_10'] ?? 0),
            'tranche_10_12' => (int)($stats['tranche_10_12'] ?? 0),
            'tranche_12_14' => (int)($stats['tranche_12_14'] ?? 0),
            'tranche_14_16' => (int)($stats['tranche_14_16'] ?? 0),
            'tranche_16_20' => (int)($stats['tranche_16_20'] ?? 0),
            'distribution'  => [
                'ajourne'    => (int)($stats['dist_ajourne'] ?? 0),
                'passable'   => (int)($stats['dist_passable'] ?? 0),
                'assez_bien' => (int)($stats['dist_assez_bien'] ?? 0),
                'bien'       => (int)($stats['dist_bien'] ?? 0),
                'tres_bien'  => (int)($stats['dist_tres_bien'] ?? 0),
            ],
        ];
    }
}
