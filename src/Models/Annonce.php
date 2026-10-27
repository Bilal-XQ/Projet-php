<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modèle Annonce — Avis pédagogiques globaux et communications par module
 */
class Annonce extends Model {
    /**
     * Récupère toutes les annonces avec les informations sur l'auteur et le module concerné.
     */
    public static function getAllWithAuthor(?string $portee = null, ?int $idModule = null): array {
        $db = self::getDb();
        $sql = "
            SELECT 
                a.idAnnonce,
                a.idAuteur,
                a.titre,
                a.contenu,
                a.datePublication,
                a.portee,
                a.idModule,
                u.nom AS auteur_nom,
                u.prenom AS auteur_prenom,
                u.email AS auteur_email,
                u.role AS auteur_role,
                m.code AS module_code,
                m.intitule AS module_intitule
            FROM annonces a
            JOIN utilisateurs u ON a.idAuteur = u.idUtilisateur
            LEFT JOIN modules m ON a.idModule = m.idModule
            WHERE 1=1
        ";

        $params = [];
        if (!empty($portee)) {
            $sql .= " AND a.portee = :portee";
            $params['portee'] = $portee;
        }

        if ($idModule !== null && $idModule > 0) {
            $sql .= " AND a.idModule = :idModule";
            $params['idModule'] = $idModule;
        }

        $sql .= " ORDER BY a.datePublication DESC";

        if (!empty($params)) {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }

        return $db->query($sql)->fetchAll();
    }

    /**
     * Publie une nouvelle annonce pédagogique.
     */
    public static function create(
        int $idAuteur,
        string $titre,
        string $contenu,
        string $portee = 'GLOBALE',
        ?int $idModule = null
    ): int {
        $db = self::getDb();
        $stmt = $db->prepare("
            INSERT INTO annonces (idAuteur, titre, contenu, portee, idModule)
            VALUES (:idAuteur, :titre, :contenu, :portee, :idModule)
        ");
        $stmt->execute([
            'idAuteur'  => $idAuteur,
            'titre'     => trim($titre),
            'contenu'   => trim($contenu),
            'portee'    => $portee === 'MODULE' ? 'MODULE' : 'GLOBALE',
            'idModule'  => ($portee === 'MODULE' && $idModule > 0) ? $idModule : null,
        ]);
        return (int)$db->lastInsertId();
    }

    /**
     * Met à jour une annonce.
     */
    public static function update(
        int $idAnnonce,
        string $titre,
        string $contenu,
        string $portee = 'GLOBALE',
        ?int $idModule = null
    ): bool {
        $db = self::getDb();
        $stmt = $db->prepare("
            UPDATE annonces 
            SET titre = :titre, contenu = :contenu, portee = :portee, idModule = :idModule
            WHERE idAnnonce = :id
        ");
        return $stmt->execute([
            'id'       => $idAnnonce,
            'titre'    => trim($titre),
            'contenu'  => trim($contenu),
            'portee'   => $portee === 'MODULE' ? 'MODULE' : 'GLOBALE',
            'idModule' => ($portee === 'MODULE' && $idModule > 0) ? $idModule : null,
        ]);
    }

    /**
     * Supprime une annonce.
     */
    public static function delete(int $idAnnonce): bool {
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM annonces WHERE idAnnonce = :id");
        return $stmt->execute(['id' => $idAnnonce]);
    }
}
