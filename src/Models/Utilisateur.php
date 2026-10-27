<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modèle abstrait Utilisateur (Héritage POO correspondant à la table utilisateurs)
 */
abstract class Utilisateur extends Model {
    protected int $idUtilisateur;
    protected string $nom;
    protected string $prenom;
    protected string $email;
    protected string $motDePasse;
    protected string $role;
    protected string $dateCreation;

    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->idUtilisateur = (int)($data['idUtilisateur'] ?? 0);
            $this->nom = (string)($data['nom'] ?? '');
            $this->prenom = (string)($data['prenom'] ?? '');
            $this->email = (string)($data['email'] ?? '');
            $this->motDePasse = (string)($data['motDePasse'] ?? '');
            $this->role = (string)($data['role'] ?? '');
            $this->dateCreation = (string)($data['dateCreation'] ?? '');
        }
    }

    // --- Getters ---
    public function getId(): int {
        return $this->idUtilisateur;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function getNomComplet(): string {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getMotDePasse(): string {
        return $this->motDePasse;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function getDateCreation(): string {
        return $this->dateCreation;
    }

    /**
     * Vérifie si le mot de passe en clair correspond au hash stocké.
     */
    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->motDePasse);
    }

    /**
     * Recherche un utilisateur par son adresse email et retourne l'instance concrète (Factory).
     */
    public static function findByEmail(string $email): ?self {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => strtolower(trim($email))]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return self::createInstance($row);
    }

    /**
     * Recherche un utilisateur par son identifiant unique.
     */
    public static function findById(int $id): ?self {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE idUtilisateur = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return self::createInstance($row);
    }

    /**
     * Instancie la classe concrète correspondant au rôle de l'utilisateur.
     */
    protected static function createInstance(array $row): self {
        $role = $row['role'] ?? '';
        $id = (int)$row['idUtilisateur'];
        $db = self::getDb();

        switch ($role) {
            case 'ADMIN':
                return new Administrateur($row);

            case 'ENSEIGNANT':
                $stmt = $db->prepare("SELECT matricule, specialite FROM enseignants WHERE idUtilisateur = :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $ensData = $stmt->fetch() ?: [];
                return new Enseignant(array_merge($row, $ensData));

            case 'ETUDIANT':
                $stmt = $db->prepare("SELECT cne, dateNaissance FROM etudiants WHERE idUtilisateur = :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $etuData = $stmt->fetch() ?: [];
                return new Etudiant(array_merge($row, $etuData));

            default:
                throw new \RuntimeException("Rôle utilisateur inconnu: {$role}");
        }
    }
}
