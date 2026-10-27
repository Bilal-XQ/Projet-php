<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modèle Filière — Gestion des cursus et programmes d'études
 */
class Filiere extends Model {
    /**
     * Récupère toutes les filières avec les comptages de modules et d'étudiants inscrits.
     */
    public static function getAllWithCounts(): array {
        $db = self::getDb();
        $sql = "
            SELECT 
                f.idFiliere,
                f.code,
                f.intitule,
                f.description,
                COUNT(DISTINCT m.idModule) AS total_modules,
                COUNT(DISTINCT i.idEtudiant) AS total_etudiants
            FROM filieres f
            LEFT JOIN modules m ON f.idFiliere = m.idFiliere
            LEFT JOIN inscriptions i ON f.idFiliere = i.idFiliere
            GROUP BY f.idFiliere, f.code, f.intitule, f.description
            ORDER BY f.code ASC
        ";
        return $db->query($sql)->fetchAll();
    }

    /**
     * Récupère une filière par son identifiant unique.
     */
    public static function findById(int $id): ?array {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM filieres WHERE idFiliere = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Crée une nouvelle filière.
     */
    public static function create(string $code, string $intitule, ?string $description = null): int {
        $db = self::getDb();
        $stmt = $db->prepare("
            INSERT INTO filieres (code, intitule, description)
            VALUES (:code, :intitule, :description)
        ");
        $stmt->execute([
            'code'        => strtoupper(trim($code)),
            'intitule'    => trim($intitule),
            'description' => $description !== null ? trim($description) : null,
        ]);
        return (int)$db->lastInsertId();
    }

    /**
     * Met à jour les informations d'une filière.
     */
    public static function update(int $id, string $code, string $intitule, ?string $description = null): bool {
        $db = self::getDb();
        $stmt = $db->prepare("
            UPDATE filieres 
            SET code = :code, intitule = :intitule, description = :description
            WHERE idFiliere = :id
        ");
        return $stmt->execute([
            'id'          => $id,
            'code'        => strtoupper(trim($code)),
            'intitule'    => trim($intitule),
            'description' => $description !== null ? trim($description) : null,
        ]);
    }

    /**
     * Supprime une filière par son identifiant.
     */
    public static function delete(int $id): bool {
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM filieres WHERE idFiliere = :id");
        return $stmt->execute(['id' => $id]);
    }
}
