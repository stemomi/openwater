<?php

namespace App\Models;

use PDO;
use DateTime;

class TipologieProdottiModel extends \Core\Model
{
    public static function getTipologiaProdotti($tipologia_prodotti_id)
    {
        $db = static::getDB();

        $sql = "
            SELECT *
            FROM prodotti_tipologia
            WHERE ID = :tipologia_prodotti_id
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':tipologia_prodotti_id', $tipologia_prodotti_id);

        $stmt->execute();

        return $stmt->fetch();
    }
    
    public static function getAllTipologieProdotti()
    {
        $db = static::getDB();

        $sql = "
            SELECT *
            FROM prodotti_tipologia
            ORDER BY nome
        ";

        $stmt = $db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll();
    }
}
