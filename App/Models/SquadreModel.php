<?php

namespace App\Models;

use PDO;
use DateTime;
class SquadreModel extends \Core\Model
{
    public static function getSquadra($squadra_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *
                              FROM squadre
                              WHERE ID = :squadra_id');
        $stmt->bindValue(':squadra_id', $squadra_id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public static function getAllSquadre()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *
                              FROM squadre
                              ORDER BY nome');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getSquadreAmount()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM squadre');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getSquadreAmountFiltered($search)
    {
        $db = static::getDB();
        $sql = "SELECT COUNT(*) FROM squadre
                WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= " AND (ID LIKE '%".$src."%' OR
                              nome LIKE '%".$src."%') ";
            }
        }

        $stmt = $db->prepare($sql);
        $stmt->execute();
       
        return $stmt->fetchColumn();
    }

    public function getSquadrePagination($order,$start,$length,$search)
    {
        $db = static::getDB();
        $sql = "SELECT * FROM squadre
                WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= " AND (ID LIKE '%".$src."%' OR
                              nome LIKE '%".$src."%') ";
            }
        }

        $sql.= " ORDER BY ". $order . " LIMIT " . $start . ", " . $length;

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function AggiornaSquadra($squadra_id, $nome, $capo_squadra_id)
    {
    	$Db = static::getDB();

    	$sql = "
            UPDATE squadre
			SET 
                nome = :nome,
                capo_squadra_id = :capo_squadra_id
			WHERE ID = :squadra_id
        ";

    	$Stmt = $Db->prepare($sql);

    	$Stmt->bindValue(':squadra_id', $squadra_id);
    	$Stmt->bindValue(':nome', $nome);
        $Stmt->bindValue(':capo_squadra_id', $capo_squadra_id);

    	$Stmt->execute();
    }

    public function AggiornaLogoSquadra($squadra_id, $logo)
    {
    	$db = static::getDB();
    	$sql = "UPDATE squadre
    			SET foto = :logo
    			WHERE ID = :squadra_id";

    	$stmt = $db->prepare($sql);
    	$stmt->bindValue(':squadra_id', $squadra_id);
    	$stmt->bindValue(':logo', $logo);
    	$stmt->execute();
    }

    public function CreaSquadra($nome)
    {
        $db = static::getDB();
        $sql = "INSERT INTO squadre
                (nome)
                VALUES(:nome)";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->execute();

        return $db->lastInsertId();
    }

    public function getMembriSquadra($squadra_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT *
            FROM utenti
            WHERE IDSquadra = :squadra_id
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':squadra_id', $squadra_id);

        $Stmt->execute();

        return $Stmt->fetchAll();
    }

    public function getCapoSquadraId($squadra_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT capo_squadra_id
            FROM squadre
            WHERE ID = :squadra_id
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':squadra_id', $squadra_id);

        $Stmt->execute();

        return $Stmt->fetchColumn();
    }
}
