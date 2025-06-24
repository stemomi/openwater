<?php

namespace App\Models;

use PDO;
use DateTime;

class StaffetteModel extends \Core\Model
{
    public static function getAllStaffetteAperte()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *,
                                      e.nome as nome_evento,
                                      e.foto as foto_evento,
                                      g.nome as nome_gara,
                                      g.ID as ID_gara,
                                      g.foto as foto_gara
                              FROM gare as g
                              LEFT JOIN eventi as e ON g.ID_evento = e.ID
                              WHERE e.data >= CURDATE()
                              AND g.staffetta = 1');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getAllStaffetteGara($id_gara_staffetta)
    {
        $db = static::getDB();
        $stmt = $db->prepare("SELECT GROUP_CONCAT(u.nome ORDER BY ps.ordine SEPARATOR ',') as U_nome,
                                     GROUP_CONCAT(u.cognome ORDER BY ps.ordine SEPARATOR ',') as U_cognome,
                                     GROUP_CONCAT(u.ID ORDER BY ps.ordine SEPARATOR ',') as U_ID,
                                     GROUP_CONCAT(
                                                    CASE
                                                      WHEN u.sesso = 1 THEN 'M'
                                                      WHEN u.sesso = 2 THEN 'F'
                                                      ELSE ''
                                                    END
                                                    ORDER BY ps.ordine
                                                    SEPARATOR ','
                                                  ) as U_sesso,
                                     GROUP_CONCAT(u.data_nascita ORDER BY ps.ordine SEPARATOR ',') as U_data_nascita,
                                     GROUP_CONCAT(u.gruppo_sportivo ORDER BY ps.ordine SEPARATOR ',') as U_gruppo_sportivo,
                                     GROUP_CONCAT(ps.ordine ORDER BY ps.ordine SEPARATOR ',') as PS_ordine,
                                     s.nome as S_nome
                              FROM partecipanti_staffette AS ps
                              LEFT JOIN staffette as s ON ps.ID_staffetta = s.ID 
                              LEFT JOIN utenti AS u ON u.ID = ps.ID_partecipante
                              WHERE s.ID_gara = :id_gara_staffetta
                              GROUP BY s.ID");
        $stmt->bindValue(':id_gara_staffetta', $id_gara_staffetta);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getAllMieSquadre()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *,
                                      e.nome as nome_evento,
                                      e.foto as foto_evento,
                                      e.data as data_evento,
                                      g.nome as nome_gara,
                                      g.ID as ID_gara,
                                      g.foto as foto_gara,
                                      f.nome as nome_staffetta,
                                      f.ID as ID_staffetta
                              FROM staffette as f
                              LEFT JOIN gare as g ON g.ID = f.ID_gara
                              LEFT JOIN eventi as e ON g.ID_evento = e.ID
                              WHERE e.data >= CURDATE()
                              AND g.staffetta = 1
                              AND f.ID_utente = :utente_id');
        $stmt->bindValue(':utente_id', $_SESSION['IDUtente']);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getLeMieSquadreAmount()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*)
                              FROM
                              (
                                SELECT COUNT(*)
                                FROM staffette as f
                                LEFT JOIN gare as g ON g.ID = f.ID_gara
                                LEFT JOIN eventi as e ON g.ID_evento = e.ID
                                LEFT JOIN partecipanti_staffette as ps ON ps.ID_staffetta = f.ID
                                WHERE e.data >= CURDATE()
                                AND g.staffetta = 1
                                AND ( f.ID_utente = :utente_id OR ps.ID_partecipante = :utente_id )
                                GROUP BY f.ID
                              ) AS interna1');
        $stmt->bindValue(':utente_id', $_SESSION['IDUtente']);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getLeMieSquadreAmountFiltered($search)
    {
        $db = static::getDB();
        $sql = "SELECT COUNT(*)
                FROM
                (
                  SELECT COUNT(*)
                  FROM staffette as f
                  LEFT JOIN gare as g ON g.ID = f.ID_gara
                  LEFT JOIN eventi as e ON g.ID_evento = e.ID
                  LEFT JOIN partecipanti_staffette as ps ON ps.ID_staffetta = f.ID
                  LEFT JOIN utenti as u ON u.ID = ps.ID_partecipante
                  WHERE e.data >= CURDATE()
                  AND g.staffetta = 1
                  AND ( f.ID_utente = :utente_id OR ps.ID_partecipante = :utente_id ) ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= " AND (e.nome LIKE '%".$src."%' OR
                              g.nome LIKE '%".$src."%' OR
                              f.nome LIKE '%".$src."%' ";

                // Matcha data solo se numero + trattino per evitare di includere tutto ricercando per ID
                if (preg_match('/(\d+)-|-(\d+)/', $src)) $sql .= " OR e.data LIKE '%".$src."%' ";

                $sql.= ")";
            }
        }

        $sql .= ' GROUP BY f.ID) as interna1';

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':utente_id', $_SESSION['IDUtente']);
        $stmt->execute();
       
        return $stmt->fetchColumn();
    }

    public function getLeMieSquadrePagination($order,$start,$length,$search)
    {
        $db = static::getDB();
        $sql = "SELECT *,
                        e.nome as nome_evento,
                        e.foto as foto_evento,
                        e.data as data_evento,
                        g.nome as nome_gara,
                        g.ID as ID_gara,
                        g.foto as foto_gara,
                        f.nome as nome_staffetta,
                        f.ID as ID_staffetta,
                        f.ID_utente as ID_creatore_staffetta,
                        GROUP_CONCAT(u.nome, ' ' , u.cognome ORDER BY ps.ordine separator ',' ) as A_nomi_partecipanti
                FROM staffette as f
                LEFT JOIN gare as g ON g.ID = f.ID_gara
                LEFT JOIN eventi as e ON g.ID_evento = e.ID
                LEFT JOIN partecipanti_staffette as ps ON ps.ID_staffetta = f.ID
                LEFT JOIN utenti as u ON u.ID = ps.ID_partecipante
                WHERE e.data >= CURDATE()
                AND g.staffetta = 1
                AND ( f.ID_utente = :utente_id OR ps.ID_partecipante = :utente_id ) ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= " AND (e.nome LIKE '%".$src."%' OR
                              g.nome LIKE '%".$src."%' OR
                              f.nome LIKE '%".$src."%' ";

                // Matcha data solo se numero + trattino per evitare di includere tutto ricercando per ID
                if (preg_match('/(\d+)-|-(\d+)/', $src)) $sql .= " OR e.data LIKE '%".$src."%' ";

                $sql.= ")";
            }
        }

        $sql .= ' GROUP BY f.ID ';

        $sql.= " ORDER BY ". $order . " LIMIT " . $start . ", " . $length;

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':utente_id', $_SESSION['IDUtente']);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function inserisciSquadra($id_gara, $nome_staffetta)
    {
        $db = static::getDB();
        $stmt = $db->prepare('INSERT INTO staffette (ID_utente, ID_gara, nome)
                              VALUES  (:utente_id, :id_gara, :nome_staffetta)');
        $stmt->bindValue(':utente_id', $_SESSION['IDUtente']);
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->bindValue(':nome_staffetta', $nome_staffetta);
        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function modificaSquadra($id_staffetta, $id_gara, $nome_staffetta)
    {
        $db = static::getDB();
        $stmt = $db->prepare('UPDATE staffette
                              SET ID_gara = :id_gara,
                                  nome = :nome_staffetta
                              WHERE ID_utente = :utente_id
                              AND ID = :id_staffetta');
        $stmt->bindValue(':utente_id', $_SESSION['IDUtente']);
        $stmt->bindValue(':id_staffetta', $id_staffetta);
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->bindValue(':nome_staffetta', $nome_staffetta);
        $stmt->execute();
    }

    public static function checkSquadraGiaCreata($id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*)
                              FROM staffette
                              WHERE ID_utente = :utente_id
                              AND ID_gara = :id_gara');
        $stmt->bindValue(':utente_id', $_SESSION['IDUtente']);
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function checkIDGaraSquadra($id_squadra, $id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*)
                              FROM staffette
                              WHERE ID_utente = :utente_id
                              AND ID_gara = :id_gara
                              AND ID <> :id_squadra');
        $stmt->bindValue(':utente_id', $_SESSION['IDUtente']);
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->bindValue(':id_squadra', $id_squadra);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function getSquadra($id_staffetta)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *
                              FROM staffette
                              WHERE ID_utente = :utente_id
                              AND ID = :id_staffetta');
        $stmt->bindValue(':utente_id', $_SESSION['IDUtente']);
        $stmt->bindValue(':id_staffetta', $id_staffetta);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function getPartecipantiSquadra($id_staffetta)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *,
                                      ps.ID as ID_partecipazione,
                                      CONCAT(u.nome, " " , u.cognome) as utente_nome_cognome
                              FROM partecipanti_staffette as ps
                              LEFT JOIN staffette as s ON ps.ID_staffetta = s.ID
                              LEFT JOIN utenti as u ON ps.ID_partecipante = u.ID
                              WHERE s.ID_utente = :utente_id
                              AND s.ID = :id_staffetta
                              ORDER BY ordine');
        $stmt->bindValue(':utente_id', $_SESSION['IDUtente']);
        $stmt->bindValue(':id_staffetta', $id_staffetta);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function cancellaPartecipazione($id_parecipazione)
    {
        $db = static::getDB();
        $stmt = $db->prepare('DELETE ps
                              FROM partecipanti_staffette as ps
                              INNER JOIN staffette as s ON ps.ID_staffetta = s.ID
                              WHERE s.ID_utente = :utente_id
                              AND ps.ID = :id_parecipazione');
        $stmt->bindValue(':utente_id', $_SESSION['IDUtente']);
        $stmt->bindValue(':id_parecipazione', $id_parecipazione);
        $stmt->execute();
    }

    public static function inserisciPartecipante($id_squadra, $utente_id, $ordine)
    {
        $db = static::getDB();
        $stmt = $db->prepare('INSERT INTO partecipanti_staffette
                                      (ID_partecipante, ID_staffetta, ordine)
                              VALUES  (:utente_id, :id_squadra, :ordine)');
        $stmt->bindValue(':utente_id', $utente_id);
        $stmt->bindValue(':id_squadra', $id_squadra);
        $stmt->bindValue(':ordine', $ordine);
        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function checkPartecipanteStaffetta($utente_id, $id_squadra)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*)
                              FROM partecipanti_staffette
                              WHERE ID_partecipante = :utente_id
                              AND ID_staffetta = :id_squadra');
        $stmt->bindValue(':utente_id', $utente_id);
        $stmt->bindValue(':id_squadra', $id_squadra);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function checkMaxPartecipantiStaffetta($id_squadra)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*)
                              FROM partecipanti_staffette
                              WHERE ID_staffetta = :id_squadra');
        $stmt->bindValue(':id_squadra', $id_squadra);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function checkPartecipanteInStaffetteStessaGara($utente_id, $id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*)
                              FROM partecipanti_staffette AS ps
                              INNER JOIN staffette AS s ON s.ID = ps.ID_staffetta
                              WHERE ps.ID_partecipante = :utente_id
                              AND s.ID_gara = :id_gara');
        $stmt->bindValue(':utente_id', $utente_id);
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function checkOrdineStaffetta($id_squadra, $ordine)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*)
                              FROM partecipanti_staffette
                              WHERE ID_staffetta = :id_squadra
                              AND ordine = :ordine ');
        $stmt->bindValue(':id_squadra', $id_squadra);
        $stmt->bindValue(':ordine', $ordine);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function eliminaPartecipantiStaffetta($id_squadra)
    {
        $db = static::getDB();
        $stmt = $db->prepare('DELETE partecipanti_staffette
                              FROM partecipanti_staffette
                              WHERE ID_staffetta = :id_squadra');
        $stmt->bindValue(':id_squadra', $id_squadra);
        $stmt->execute();
    }
}
