<?php

namespace App\Models;

use PDO;
use DateTime;

class ImpostazioniModel extends \Core\Model
{
    public static function getImpostazioni($nome = '')
    {
        $db = static::getDB();
        $sql = "
        SELECT *
        FROM impostazioni ";

        if ($nome != '')
            $sql .= ' WHERE nome = :nome ';

        $stmt = $db->prepare($sql);

        if ($nome != '')
            $stmt->bindValue(':nome', $nome);

        $stmt->execute();

        if ($nome == '')
            return $stmt->fetchAll();
        else
            return $stmt->fetch()->valore;
    }

    public static function aggiornaImpostazioni($nome, $valore)
    {
        $db = static::getDB();

        $sql = "
        UPDATE impostazioni
        SET valore = :valore
        WHERE nome = :nome";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':valore', $valore);

        $stmt->execute();
    }
}
