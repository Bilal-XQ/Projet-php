<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use Database;

/**
 * Classe de base abstraite Model
 * Fournit l'accès centralisé à l'instance PDO pour tous les modèles métier.
 */
abstract class Model {
    protected static ?PDO $pdo = null;

    /**
     * Récupère l'instance active PDO.
     */
    public static function getDb(): PDO {
        if (self::$pdo === null) {
            self::$pdo = Database::getConnection();
        }
        return self::$pdo;
    }
}
