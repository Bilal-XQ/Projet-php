<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modèle JournalAudit — Traçabilité et historique d'audit des évaluations
 */
class JournalAudit extends Model {
    /**
     * Récupère l'historique d'audit des notes avec les détails de l'intervenant, du module et de l'étudiant concerné.
     */
    public static function getAllWithDetails(?string $typeAction = null, int $limit = 100): array {
        $db = self::getDb();
        $sql = "
            SELECT 
                j.idAudit,
                j.idNote,
                j.idUtilisateur,
                j.typeAction,
                j.ancienneValeur,
                j.nouvelleValeur,
                j.dateAction,
                u.nom AS auteur_nom,
                u.prenom AS auteur_prenom,
                u.email AS auteur_email,
                u.role AS auteur_role,
                m.code AS module_code,
                m.intitule AS module_intitule,
                ue.nom AS etudiant_nom,
                ue.prenom AS etudiant_prenom,
                e.cne AS etudiant_cne
            FROM journal_audit_notes j
            JOIN utilisateurs u ON j.idUtilisateur = u.idUtilisateur
            JOIN notes n ON j.idNote = n.idNote
            JOIN modules m ON n.idModule = m.idModule
            JOIN etudiants e ON n.idEtudiant = e.idUtilisateur
            JOIN utilisateurs ue ON e.idUtilisateur = ue.idUtilisateur
            WHERE 1=1
        ";

        $params = [];
        if (!empty($typeAction)) {
            $sql .= " AND j.typeAction = :typeAction";
            $params['typeAction'] = $typeAction;
        }

        $sql .= " ORDER BY j.dateAction DESC LIMIT " . (int)$limit;

        if (!empty($params)) {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }

        return $db->query($sql)->fetchAll();
    }
}
