<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Modèle Étudiant (Spécialisation avec CNE et Date de Naissance)
 */
class Etudiant extends Utilisateur {
    protected string $cne = '';
    protected string $dateNaissance = '';

    public function __construct(array $data = []) {
        parent::__construct($data);
        $this->role = 'ETUDIANT';
        $this->cne = (string)($data['cne'] ?? '');
        $this->dateNaissance = (string)($data['dateNaissance'] ?? '');
    }

    public function getCne(): string {
        return $this->cne;
    }

    public function getDateNaissance(): string {
        return $this->dateNaissance;
    }
}
