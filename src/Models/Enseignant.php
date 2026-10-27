<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Modèle Enseignant (Spécialisation avec matricule et spécialité)
 */
class Enseignant extends Utilisateur {
    protected string $matricule = '';
    protected string $specialite = '';

    public function __construct(array $data = []) {
        parent::__construct($data);
        $this->role = 'ENSEIGNANT';
        $this->matricule = (string)($data['matricule'] ?? '');
        $this->specialite = (string)($data['specialite'] ?? '');
    }

    public function getMatricule(): string {
        return $this->matricule;
    }

    public function getSpecialite(): string {
        return $this->specialite;
    }
}
