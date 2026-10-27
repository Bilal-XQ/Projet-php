<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Modèle Administrateur (Chef de Département / Scolarité)
 */
class Administrateur extends Utilisateur {
    public function __construct(array $data = []) {
        parent::__construct($data);
        $this->role = 'ADMIN';
    }
}
