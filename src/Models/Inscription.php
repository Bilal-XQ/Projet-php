<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modèle Inscription — Gestion des inscriptions universitaires annuelles
 */
class Inscription extends Model {
    public const VALID_STATUTS = ['EN_COURS', 'VALIDE', 'AJOURNE'];

    /**
     * Récupère toutes les inscriptions avec jointures sur étudiants et filières.
     */
    public static function getAllWithDetails(?string $annee = null, ?int $idFiliere = null): array {
        $db = self::getDb();
        $sql = "
            SELECT 
                i.idInscription,
                i.idEtudiant,
                i.idFiliere,
                i.anneeUniversitaire,
                i.dateInscription,
                i.statut,
                u.nom AS etudiant_nom,
                u.prenom AS etudiant_prenom,
                u.email AS etudiant_email,
                e.cne AS etudiant_cne,
                e.dateNaissance AS etudiant_date_naissance,
                f.code AS filiere_code,
                f.intitule AS filiere_intitule
            FROM inscriptions i
            JOIN etudiants e ON i.idEtudiant = e.idUtilisateur
            JOIN utilisateurs u ON e.idUtilisateur = u.idUtilisateur
            JOIN filieres f ON i.idFiliere = f.idFiliere
            WHERE 1=1
        ";

        $params = [];
        if (!empty($annee)) {
            $sql .= " AND i.anneeUniversitaire = :annee";
            $params['annee'] = $annee;
        }

        if ($idFiliere !== null && $idFiliere > 0) {
            $sql .= " AND i.idFiliere = :idFiliere";
            $params['idFiliere'] = $idFiliere;
        }

        $sql .= " ORDER BY i.anneeUniversitaire DESC, u.nom ASC, u.prenom ASC";

        if (!empty($params)) {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }

        return $db->query($sql)->fetchAll();
    }

    /**
     * Inscrit un étudiant à une filière pour une année universitaire donnée.
     * Enfreint la contrainte d'unicité (idEtudiant, anneeUniversitaire) si doublon.
     */
    public static function enroll(
        int $idEtudiant,
        int $idFiliere,
        string $anneeUniversitaire,
        ?string $dateInscription = null
    ): int {
        $db = self::getDb();

        // 1. Vérification préventive de la contrainte unique
        $checkStmt = $db->prepare("
            SELECT idInscription FROM inscriptions 
            WHERE idEtudiant = :idEtudiant AND anneeUniversitaire = :anneeUniversitaire
            LIMIT 1
        ");
        $checkStmt->execute([
            'idEtudiant'          => $idEtudiant,
            'anneeUniversitaire'  => trim($anneeUniversitaire),
        ]);

        if ($checkStmt->fetch()) {
            throw new \RuntimeException(
                "L'étudiant est déjà inscrit pour l'année universitaire '{$anneeUniversitaire}'. Une seule inscription par an est autorisée."
            );
        }

        // 2. Insertion
        $date = !empty($dateInscription) ? $dateInscription : date('Y-m-d');
        $stmt = $db->prepare("
            INSERT INTO inscriptions (idEtudiant, idFiliere, anneeUniversitaire, dateInscription, statut)
            VALUES (:idEtudiant, :idFiliere, :anneeUniversitaire, :dateInscription, 'EN_COURS')
        ");
        $stmt->execute([
            'idEtudiant'         => $idEtudiant,
            'idFiliere'          => $idFiliere,
            'anneeUniversitaire' => trim($anneeUniversitaire),
            'dateInscription'    => $date,
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Met à jour les détails d'une inscription (filière, année, statut).
     */
    public static function update(int $idInscription, int $idFiliere, string $anneeUniversitaire, string $statut): bool {
        if (!in_array($statut, self::VALID_STATUTS, true)) {
            throw new \InvalidArgumentException("Statut invalide. Permis : " . implode(', ', self::VALID_STATUTS));
        }

        $db = self::getDb();
        $stmt = $db->prepare("
            UPDATE inscriptions 
            SET idFiliere = :idFiliere, anneeUniversitaire = :annee, statut = :statut
            WHERE idInscription = :id
        ");
        return $stmt->execute([
            'id'        => $idInscription,
            'idFiliere' => $idFiliere,
            'annee'     => trim($anneeUniversitaire),
            'statut'    => $statut,
        ]);
    }

    /**
     * Met à jour le statut d'une inscription (EN_COURS, VALIDE, AJOURNE).
     */
    public static function updateStatus(int $idInscription, string $statut): bool {
        if (!in_array($statut, self::VALID_STATUTS, true)) {
            throw new \InvalidArgumentException("Statut invalide. Permis : " . implode(', ', self::VALID_STATUTS));
        }

        $db = self::getDb();
        $stmt = $db->prepare("UPDATE inscriptions SET statut = :statut WHERE idInscription = :id");
        return $stmt->execute([
            'id'     => $idInscription,
            'statut' => $statut,
        ]);
    }

    /**
     * Supprime une inscription.
     */
    public static function delete(int $idInscription): bool {
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM inscriptions WHERE idInscription = :id");
        return $stmt->execute(['id' => $idInscription]);
    }
}
