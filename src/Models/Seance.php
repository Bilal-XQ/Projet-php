<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modèle Séance — Planification des cours, TD et TP pour le suivi d'assiduité
 */
class Seance extends Model {
    public const VALID_TYPES = ['COURS', 'TD', 'TP'];

    /**
     * Crée une nouvelle séance pédagogique.
     */
    public static function createSeance(
        int $idModule,
        string $date,
        string $debut,
        string $fin,
        string $type = 'COURS'
    ): int {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException("Type de séance invalide. Permis : COURS, TD, TP.");
        }

        $db = self::getDb();
        $stmt = $db->prepare("
            INSERT INTO seances (idModule, dateSeance, heureDebut, heureFin, typeSeance)
            VALUES (:idModule, :dateSeance, :debut, :fin, :typeSeance)
        ");
        $stmt->execute([
            'idModule'   => $idModule,
            'dateSeance' => $date,
            'debut'      => $debut,
            'fin'        => $fin,
            'typeSeance' => $type,
        ]);
        return (int)$db->lastInsertId();
    }

    /**
     * Récupère toutes les séances d'un module avec les statistiques d'absence.
     */
    public static function getSeancesByModule(int $idModule): array {
        $db = self::getDb();
        $sql = "
            SELECT 
                s.idSeance,
                s.idModule,
                s.dateSeance,
                s.heureDebut,
                s.heureFin,
                s.typeSeance,
                COUNT(CASE WHEN a.statut = 'ABSENT' THEN 1 END) AS total_absents,
                COUNT(CASE WHEN a.statut = 'PRESENT' THEN 1 END) AS total_presents
            FROM seances s
            LEFT JOIN absences a ON s.idSeance = a.idSeance
            WHERE s.idModule = :idModule
            GROUP BY s.idSeance, s.idModule, s.dateSeance, s.heureDebut, s.heureFin, s.typeSeance
            ORDER BY s.dateSeance DESC, s.heureDebut DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute(['idModule' => $idModule]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère une séance avec les informations de son module.
     */
    public static function findById(int $idSeance): ?array {
        $db = self::getDb();
        $sql = "
            SELECT 
                s.*,
                m.code AS module_code,
                m.intitule AS module_intitule,
                m.idEnseignant,
                m.idFiliere,
                f.code AS filiere_code,
                f.intitule AS filiere_intitule
            FROM seances s
            JOIN modules m ON s.idModule = m.idModule
            JOIN filieres f ON m.idFiliere = f.idFiliere
            WHERE s.idSeance = :id
            LIMIT 1
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $idSeance]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
