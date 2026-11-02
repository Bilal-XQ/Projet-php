<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modèle Reclamation
 * Gère les réclamations déposées par les étudiants et traitées par les enseignants ou administrateurs.
 */
class Reclamation extends Model {
    private int $idReclamation;
    private int $idEtudiant;
    private string $motif;
    private string $objet;
    private string $message;
    private string $statut;
    private ?string $reponse;
    private ?int $idRepondeur;
    private string $dateCreation;
    private ?string $dateReponse;

    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->idReclamation = (int)($data['idReclamation'] ?? 0);
            $this->idEtudiant    = (int)($data['idEtudiant'] ?? 0);
            $this->motif         = (string)($data['motif'] ?? '');
            $this->objet         = (string)($data['objet'] ?? '');
            $this->message       = (string)($data['message'] ?? '');
            $this->statut        = (string)($data['statut'] ?? 'EN_ATTENTE');
            $this->reponse       = isset($data['reponse']) ? (string)$data['reponse'] : null;
            $this->idRepondeur   = isset($data['idRepondeur']) ? (int)$data['idRepondeur'] : null;
            $this->dateCreation  = (string)($data['dateCreation'] ?? '');
            $this->dateReponse   = isset($data['dateReponse']) ? (string)$data['dateReponse'] : null;
        }
    }

    /**
     * Crée une nouvelle réclamation pour un étudiant.
     */
    public static function create(int $idEtudiant, string $motif, string $objet, string $message): int {
        $db = self::getDb();
        $stmt = $db->prepare("
            INSERT INTO reclamations (idEtudiant, motif, objet, message, statut, dateCreation)
            VALUES (:idEtudiant, :motif, :objet, :message, 'EN_ATTENTE', NOW())
        ");
        $stmt->execute([
            ':idEtudiant' => $idEtudiant,
            ':motif'      => $motif,
            ':objet'      => $objet,
            ':message'    => $message,
        ]);
        return (int)$db->lastInsertId();
    }

    /**
     * Récupère toutes les réclamations d'un étudiant donné avec infos du répondeur.
     */
    public static function getByEtudiant(int $idEtudiant): array {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT r.*, 
                   u.nom AS repondeur_nom, 
                   u.prenom AS repondeur_prenom, 
                   u.role AS repondeur_role
            FROM reclamations r
            LEFT JOIN utilisateurs u ON r.idRepondeur = u.idUtilisateur
            WHERE r.idEtudiant = :idEtudiant
            ORDER BY r.dateCreation DESC
        ");
        $stmt->execute([':idEtudiant' => $idEtudiant]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère toutes les réclamations pour le module gestion (Admin et Enseignants).
     */
    public static function getAllWithDetails(?string $filterStatut = null): array {
        $db = self::getDb();
        $sql = "
            SELECT r.*, 
                   e_u.nom AS etudiant_nom, 
                   e_u.prenom AS etudiant_prenom, 
                   e_u.email AS etudiant_email,
                   et.cne AS etudiant_cne,
                   f.code AS filiere_code,
                   f.intitule AS filiere_intitule,
                   rep.nom AS repondeur_nom, 
                   rep.prenom AS repondeur_prenom, 
                   rep.role AS repondeur_role
            FROM reclamations r
            JOIN utilisateurs e_u ON r.idEtudiant = e_u.idUtilisateur
            LEFT JOIN etudiants et ON r.idEtudiant = et.idUtilisateur
            LEFT JOIN inscriptions i ON et.idUtilisateur = i.idEtudiant
            LEFT JOIN filieres f ON i.idFiliere = f.idFiliere
            LEFT JOIN utilisateurs rep ON r.idRepondeur = rep.idUtilisateur
        ";

        if ($filterStatut) {
            $sql .= " WHERE r.statut = :statut";
        }

        $sql .= " ORDER BY r.dateCreation DESC";

        $stmt = $db->prepare($sql);
        if ($filterStatut) {
            $stmt->execute([':statut' => $filterStatut]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une réclamation par son identifiant.
     */
    public static function findById(int $idReclamation): ?array {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT r.*, 
                   e_u.nom AS etudiant_nom, 
                   e_u.prenom AS etudiant_prenom, 
                   e_u.email AS etudiant_email,
                   et.cne AS etudiant_cne,
                   rep.nom AS repondeur_nom, 
                   rep.prenom AS repondeur_prenom, 
                   rep.role AS repondeur_role
            FROM reclamations r
            JOIN utilisateurs e_u ON r.idEtudiant = e_u.idUtilisateur
            LEFT JOIN etudiants et ON r.idEtudiant = et.idUtilisateur
            LEFT JOIN utilisateurs rep ON r.idRepondeur = rep.idUtilisateur
            WHERE r.idReclamation = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $idReclamation]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Met à jour le traitement d'une réclamation (réponse, statut, répondeur).
     */
    public static function traiter(int $idReclamation, string $statut, string $reponse, int $idRepondeur): bool {
        $db = self::getDb();
        $stmt = $db->prepare("
            UPDATE reclamations 
            SET statut = :statut,
                reponse = :reponse,
                idRepondeur = :idRepondeur,
                dateReponse = NOW()
            WHERE idReclamation = :id
        ");
        return $stmt->execute([
            ':statut'      => $statut,
            ':reponse'     => $reponse,
            ':idRepondeur' => $idRepondeur,
            ':id'          => $idReclamation,
        ]);
    }

    /**
     * Retourne les statistiques globales sur les réclamations.
     */
    public static function getStats(): array {
        $db = self::getDb();
        $stmt = $db->query("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN statut = 'EN_ATTENTE' THEN 1 ELSE 0 END) AS en_attente,
                SUM(CASE WHEN statut = 'EN_COURS' THEN 1 ELSE 0 END) AS en_cours,
                SUM(CASE WHEN statut = 'RESOLUE' THEN 1 ELSE 0 END) AS resolues,
                SUM(CASE WHEN statut = 'REJETEE' THEN 1 ELSE 0 END) AS rejetees
            FROM reclamations
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total'      => 0,
            'en_attente' => 0,
            'en_cours'   => 0,
            'resolues'   => 0,
            'rejetees'   => 0,
        ];
    }
}

