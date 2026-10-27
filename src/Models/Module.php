<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modèle Module — Gestion des unités d'enseignement et attributions pédagogiques
 */
class Module extends Model {
    public const VALID_SEMESTRES = ['S1', 'S2', 'S3', 'S4'];

    /**
     * Récupère tous les modules avec les détails de la filière et de l'enseignant affecté.
     */
    public static function getAllWithDetails(?int $idFiliere = null): array {
        $db = self::getDb();
        $sql = "
            SELECT 
                m.idModule,
                m.code,
                m.intitule,
                m.semestre,
                m.coefficient,
                m.idFiliere,
                m.idEnseignant,
                f.code AS filiere_code,
                f.intitule AS filiere_intitule,
                u.nom AS enseignant_nom,
                u.prenom AS enseignant_prenom,
                u.email AS enseignant_email,
                e.matricule AS enseignant_matricule,
                e.specialite AS enseignant_specialite
            FROM modules m
            JOIN filieres f ON m.idFiliere = f.idFiliere
            LEFT JOIN enseignants e ON m.idEnseignant = e.idUtilisateur
            LEFT JOIN utilisateurs u ON e.idUtilisateur = u.idUtilisateur
        ";

        if ($idFiliere !== null) {
            $sql .= " WHERE m.idFiliere = :idFiliere";
            $sql .= " ORDER BY m.semestre ASC, m.code ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute(['idFiliere' => $idFiliere]);
            return $stmt->fetchAll();
        }

        $sql .= " ORDER BY f.code ASC, m.semestre ASC, m.code ASC";
        return $db->query($sql)->fetchAll();
    }

    /**
     * Récupère un module par son identifiant.
     */
    public static function findById(int $id): ?array {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM modules WHERE idModule = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Crée un nouveau module.
     */
    public static function create(
        string $code,
        string $intitule,
        string $semestre,
        float $coefficient,
        int $idFiliere,
        ?int $idEnseignant = null
    ): int {
        if (!in_array($semestre, self::VALID_SEMESTRES, true)) {
            throw new \InvalidArgumentException("Semestre invalide. Les valeurs permises sont : " . implode(', ', self::VALID_SEMESTRES));
        }

        $db = self::getDb();
        $stmt = $db->prepare("
            INSERT INTO modules (code, intitule, semestre, coefficient, idFiliere, idEnseignant)
            VALUES (:code, :intitule, :semestre, :coefficient, :idFiliere, :idEnseignant)
        ");
        $stmt->execute([
            'code'         => strtoupper(trim($code)),
            'intitule'     => trim($intitule),
            'semestre'     => $semestre,
            'coefficient'  => $coefficient,
            'idFiliere'    => $idFiliere,
            'idEnseignant' => $idEnseignant > 0 ? $idEnseignant : null,
        ]);
        return (int)$db->lastInsertId();
    }

    /**
     * Met à jour un module existant.
     */
    public static function update(
        int $id,
        string $code,
        string $intitule,
        string $semestre,
        float $coefficient,
        int $idFiliere,
        ?int $idEnseignant = null
    ): bool {
        if (!in_array($semestre, self::VALID_SEMESTRES, true)) {
            throw new \InvalidArgumentException("Semestre invalide. Les valeurs permises sont : " . implode(', ', self::VALID_SEMESTRES));
        }

        $db = self::getDb();
        $stmt = $db->prepare("
            UPDATE modules 
            SET code = :code, intitule = :intitule, semestre = :semestre, 
                coefficient = :coefficient, idFiliere = :idFiliere, idEnseignant = :idEnseignant
            WHERE idModule = :id
        ");
        return $stmt->execute([
            'id'           => $id,
            'code'         => strtoupper(trim($code)),
            'intitule'     => trim($intitule),
            'semestre'     => $semestre,
            'coefficient'  => $coefficient,
            'idFiliere'    => $idFiliere,
            'idEnseignant' => $idEnseignant > 0 ? $idEnseignant : null,
        ]);
    }

    /**
     * Affecte ou retire un enseignant responsable d'un module.
     */
    public static function assignTeacher(int $idModule, ?int $idEnseignant): bool {
        $db = self::getDb();
        $stmt = $db->prepare("
            UPDATE modules 
            SET idEnseignant = :idEnseignant
            WHERE idModule = :idModule
        ");
        return $stmt->execute([
            'idModule'     => $idModule,
            'idEnseignant' => $idEnseignant > 0 ? $idEnseignant : null,
        ]);
    }

    /**
     * Supprime un module par son identifiant.
     */
    public static function delete(int $id): bool {
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM modules WHERE idModule = :id");
        return $stmt->execute(['id' => $id]);
    }
}
