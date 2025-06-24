<?php

namespace App\Models;

use PDO;
use DateTime;

class AdminGareModel extends \Core\Model
{
    public static function InserisciGara(
        $ID_evento,
        $nome,
        $data,
        $prezzo1,
        $prezzo2,
        $prezzo3,
        $prezzo4,
        $data_prezzo1,
        $data_prezzo2,
        $data_prezzo3,
        $data_prezzo4,
        $sconto_prezzo1,
        $sconto_prezzo2,
        $sconto_prezzo3,
        $sconto_prezzo4,
        $staffetta,
        $combinata,
        $tipo
    )
    {
        // Converte date per formato database
        $data_prezzo1 = $data_prezzo1 == '' ? date('Y-m-d') : DateTime::createFromFormat('d/m/Y',$data_prezzo1)->format('Y-m-d');
        $data_prezzo2 = $data_prezzo2 == '' ? date('Y-m-d') : DateTime::createFromFormat('d/m/Y',$data_prezzo2)->format('Y-m-d');
        $data_prezzo3 = $data_prezzo3 == '' ? date('Y-m-d') : DateTime::createFromFormat('d/m/Y',$data_prezzo3)->format('Y-m-d');
        $data_prezzo4 = null;

        $db = static::getDB();
        $stmt = $db->prepare('
            INSERT INTO gare
            (
                ID_evento,
                nome,
                data,
                prezzo1,
                prezzo2,
                prezzo3,
                prezzo4,
                data_prezzo1,
                data_prezzo2,
                data_prezzo3,
                data_prezzo4,
                sconto_prezzo1,
                sconto_prezzo2,
                sconto_prezzo3,
                sconto_prezzo4,
                staffetta,
                combinata,
                tipo
            )
            VALUES
            (
                :ID_evento,
                :nome,
                :data,
                :prezzo1,
                :prezzo2,
                :prezzo3,
                :prezzo4,
                :data_prezzo1,
                :data_prezzo2,
                :data_prezzo3,
                :data_prezzo4,
                :sconto_prezzo1,
                :sconto_prezzo2,
                :sconto_prezzo3,
                :sconto_prezzo4,
                :staffetta,
                :combinata,
                :tipo
            )');
        $stmt->bindValue(':ID_evento', $ID_evento);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':data', $data);
        $stmt->bindValue(':prezzo1', $prezzo1);
        $stmt->bindValue(':prezzo2', $prezzo2);
        $stmt->bindValue(':prezzo3', $prezzo3);
        $stmt->bindValue(':prezzo4', $prezzo4);
        $stmt->bindValue(':data_prezzo1', $data_prezzo1);
        $stmt->bindValue(':data_prezzo2', $data_prezzo2);
        $stmt->bindValue(':data_prezzo3', $data_prezzo3);
        $stmt->bindValue(':data_prezzo4', $data_prezzo4);
        $stmt->bindValue(':sconto_prezzo1', $sconto_prezzo1);
        $stmt->bindValue(':sconto_prezzo2', $sconto_prezzo2);
        $stmt->bindValue(':sconto_prezzo3', $sconto_prezzo3);
        $stmt->bindValue(':sconto_prezzo4', $sconto_prezzo4);
        $stmt->bindValue(':staffetta', $staffetta);
        $stmt->bindValue(':combinata', $combinata);
        $stmt->bindValue(':tipo', $tipo);
        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function AggiornaGara(
        $id_gara,
        $id_evento,
        $nome,
        $prezzo1,
        $prezzo2,
        $prezzo3,
        $prezzo4,
        $data_prezzo1,
        $data_prezzo2,
        $data_prezzo3,
        $data_prezzo4,
        $sconto_prezzo1,
        $sconto_prezzo2,
        $sconto_prezzo3,
        $sconto_prezzo4,
        $staffetta,
        $combinata,
        $mostra_risultati,
        $gara_bloccata,
        $tipo
    )
    {

        // Converte data_prezzo1 per database
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data_prezzo1))
          $data_prezzo1 = DateTime::createFromFormat('d/m/Y',$data_prezzo1)->format('Y-m-d');
        else
          $data_prezzo1 = null;

        // Converte data_prezzo2 per database
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data_prezzo2))
          $data_prezzo2 = DateTime::createFromFormat('d/m/Y',$data_prezzo2)->format('Y-m-d');
        else
          $data_prezzo2 = null;

        // Converte data_prezzo3 per database
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data_prezzo3))
          $data_prezzo3 = DateTime::createFromFormat('d/m/Y',$data_prezzo3)->format('Y-m-d');
        else
          $data_prezzo3 = null;

        // Converte data_prezzo4 per database
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data_prezzo4))
          $data_prezzo4 = DateTime::createFromFormat('d/m/Y',$data_prezzo4)->format('Y-m-d');
        else
          $data_prezzo4 = null;

        $db = static::getDB();
        $stmt = $db->prepare("
            UPDATE gare
            SET ID_evento = :id_evento,
                nome = :nome,
                prezzo1 = :prezzo1,
                prezzo2 = :prezzo2,
                prezzo3 = :prezzo3,
                prezzo4 = :prezzo4,
                data_prezzo1 = :data_prezzo1,
                data_prezzo2 = :data_prezzo2,
                data_prezzo3 = :data_prezzo3,
                data_prezzo4 = :data_prezzo4,
                sconto_prezzo1 = :sconto_prezzo1,
                sconto_prezzo2 = :sconto_prezzo2,
                sconto_prezzo3 = :sconto_prezzo3,
                sconto_prezzo4 = :sconto_prezzo4,
                staffetta = :staffetta,
                combinata = :combinata,
                mostra_risultati = :mostra_risultati,
                gara_bloccata = :gara_bloccata,
                tipo = :tipo
                WHERE ID = :id_gara"
        );
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':prezzo1', $prezzo1);
        $stmt->bindValue(':prezzo2', $prezzo2);
        $stmt->bindValue(':prezzo3', $prezzo3);
        $stmt->bindValue(':prezzo4', $prezzo4);
        $stmt->bindValue(':data_prezzo1', $data_prezzo1);
        $stmt->bindValue(':data_prezzo2', $data_prezzo2);
        $stmt->bindValue(':data_prezzo3', $data_prezzo3);
        $stmt->bindValue(':data_prezzo4', $data_prezzo4);
        $stmt->bindValue(':sconto_prezzo1', $sconto_prezzo1);
        $stmt->bindValue(':sconto_prezzo2', $sconto_prezzo2);
        $stmt->bindValue(':sconto_prezzo3', $sconto_prezzo3);
        $stmt->bindValue(':sconto_prezzo4', $sconto_prezzo4);
        $stmt->bindValue(':staffetta', $staffetta);
        $stmt->bindValue(':combinata', $combinata);
        $stmt->bindValue(':mostra_risultati', $mostra_risultati);
        $stmt->bindValue(':gara_bloccata', $gara_bloccata);
        $stmt->bindValue(':tipo', $tipo);
        $stmt->execute();
    }

    public static function InserisciEvento($nome, $data, $data_apertura_iscrizioni)
    {
        // Converte data per database
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data))
          $data = DateTime::createFromFormat('d/m/Y',$data)->format('Y-m-d');
        else
          $data = null;

        // Converte data_apertura_iscrizioni per database
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data_apertura_iscrizioni))
          $data_apertura_iscrizioni = DateTime::createFromFormat('d/m/Y',$data_apertura_iscrizioni)->format('Y-m-d');
        else
          $data_apertura_iscrizioni = null;

        $db = static::getDB();
        $stmt = $db->prepare('INSERT INTO eventi (nome, data, data_apertura_iscrizioni)
                              VALUES (:nome, :data, :data_apertura_iscrizioni)');
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':data', $data);
        $stmt->bindValue(':data_apertura_iscrizioni', $data_apertura_iscrizioni);
        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function AggiornaEvento(
        $id_evento,
        $nome,
        $data,
        $data_apertura_iscrizioni,
        $vendita_boa
    )
    {

        // Converte data per database
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data))
          $data = DateTime::createFromFormat('d/m/Y',$data)->format('Y-m-d');
        else
          $data = null;

        // Converte data_apertura_iscrizioni per database
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data_apertura_iscrizioni))
          $data_apertura_iscrizioni = DateTime::createFromFormat('d/m/Y',$data_apertura_iscrizioni)->format('Y-m-d');
        else
          $data_apertura_iscrizioni = null;

        $db = static::getDB();
        $stmt = $db->prepare("
            UPDATE eventi
            SET nome = :nome,
              data = :data,
              data_apertura_iscrizioni = :data_apertura_iscrizioni,
              vendita_boa = :vendita_boa
            WHERE ID = :id_evento
        ");
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':data', $data);
        $stmt->bindValue(':data_apertura_iscrizioni', $data_apertura_iscrizioni);
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->bindValue(':vendita_boa', $vendita_boa);
        $stmt->execute();
    }

    public static function AggiornaFotoEvento($id_evento, $foto)
    {
        $db = static::getDB();
        $stmt = $db->prepare("
            UPDATE eventi
            SET foto = :foto
            WHERE ID = :id_evento"
        );
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->bindValue(':foto', $foto);
        $stmt->execute();
    }

    public static function getEventi($solo_attivi = 0)
    {
        $db = static::getDB();
        $sql = 'SELECT * FROM eventi ';

        if ($solo_attivi == 1)
          $sql .= ' WHERE NOW() < data';

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getEvento($id_evento)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT * FROM eventi WHERE ID = :id_evento');
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getEventiAmount()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM eventi');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getEventiAmountFiltered($search)
    {
        $db = static::getDB();
        $sql = "SELECT COUNT(*) FROM eventi WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= " AND (ID LIKE '%".$src."%' OR
                              nome LIKE '%".$src."%' ";

                // Matcha data solo se numero + trattino per evitare di includere tutto ricercando per ID
                if (preg_match('/(\d+)-|-(\d+)/', $src)) $sql .= " OR data LIKE '%".$src."%' ";

                $sql.= ")";
            }
        }

        $stmt = $db->prepare($sql);
        $stmt->execute();
       
        return $stmt->fetchColumn();
    }

    public function getEventiPagination($order,$start,$length,$search)
    {
        $db = static::getDB();
        $sql = "SELECT * FROM eventi WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= " AND (ID LIKE '%".$src."%' OR
                              nome LIKE '%".$src."%' ";

                // Matcha data solo se numero + trattino per evitare di includere tutto ricercando per ID
                if (preg_match('/(\d+)-|-(\d+)/', $src)) $sql .= " OR data LIKE '%".$src."%' ";

                $sql.= ")";
            }
        }

        $sql.= " ORDER BY ". $order . " LIMIT " . $start . ", " . $length;

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getGara($id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            SELECT
                g.*,
                e.nome as A_nome_evento
            FROM gare as g
            LEFT JOIN eventi as e ON e.ID = g.ID_evento
            WHERE g.ID = :id_gara
        ');
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getGareAmount()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM gare');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getGareAmountFiltered($search)
    {
        $db = static::getDB();
        $sql = "SELECT COUNT(*)
                FROM gare as g
                LEFT JOIN eventi as e ON e.ID = g.ID_evento
                WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= " AND (g.ID LIKE '%".$src."%' OR
                              g.nome LIKE '%".$src."%' OR
                              e.nome LIKE '%".$src."%' ";

                // Matcha data solo se numero + trattino per evitare di includere tutto ricercando per ID
                if (preg_match('/(\d+)-|-(\d+)/', $src)) $sql .= " OR e.data LIKE '%".$src."%' ";

                $sql.= ")";
            }
        }

        $stmt = $db->prepare($sql);
        $stmt->execute();
       
        return $stmt->fetchColumn();
    }

    public function getGarePagination($order,$start,$length,$search)
    {
        $db = static::getDB();
        $sql = "SELECT g.ID as A_ID_gara,
                       g.nome as A_nome_gara,
                       g.combinata as A_combinata_gara,
                       g.mostra_risultati as A_mostra_risultati_gara,
                       e.nome as A_nome_evento,
                       e.data as A_data_evento
                FROM gare as g
                LEFT JOIN eventi as e ON e.ID = g.ID_evento
                WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= " AND (g.ID LIKE '%".$src."%' OR
                              g.nome LIKE '%".$src."%' OR
                              e.nome LIKE '%".$src."%' ";

                // Matcha data solo se numero + trattino per evitare di includere tutto ricercando per ID
                if (preg_match('/(\d+)-|-(\d+)/', $src)) $sql .= " OR e.data LIKE '%".$src."%' ";

                $sql.= ")";
            }
        }

        $sql.= " ORDER BY ". $order . " LIMIT " . $start . ", " . $length;

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getGareEvento($id_evento)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *
                              FROM gare
                              WHERE ID_evento = :id_evento');
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getGareStessoEvento($id_evento, $id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *
                              FROM gare
                              WHERE ID_evento = :id_evento
                              AND ID <> :id_gara
                              AND combinata = 0');
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getGareCombinateAggiunte($id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT g.ID as A_g_ID,
                                     g.nome as A_g_nome,
                                     gc.ID as A_gc_ID
                              FROM gare_collegate as gc
                              INNER JOIN gare as g ON g.ID = gc.ID_gara_collegata
                              WHERE gc.ID_gara_primaria = :id_gara');
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function cancellaGaraCollegata($id_gara_collegata)
    {
        $db = static::getDB();
        $stmt = $db->prepare('DELETE gare_collegate
                              FROM gare_collegate
                              WHERE ID = :id_gara_collegata');
        $stmt->bindValue(':id_gara_collegata', $id_gara_collegata);
        $stmt->execute();
    }

    public static function aggiungiGaraDaCollegare($id_gara_primaria, $id_gara_collegata)
    {
        $db = static::getDB();
        $stmt = $db->prepare('INSERT INTO gare_collegate (ID_gara_primaria, ID_gara_collegata) VALUES (:id_gara_primaria, :id_gara_collegata)');
        $stmt->bindValue(':id_gara_primaria', $id_gara_primaria);
        $stmt->bindValue(':id_gara_collegata', $id_gara_collegata);
        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function checkCollegamentoGaraEsistente($id_gara_primaria, $id_gara_collegata)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*)
                              FROM gare_collegate
                              WHERE ID_gara_primaria = :id_gara_primaria
                              AND ID_gara_collegata = :id_gara_collegata');
        $stmt->bindValue(':id_gara_primaria', $id_gara_primaria);
        $stmt->bindValue(':id_gara_collegata', $id_gara_collegata);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function getStaffetteAmount()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM staffette');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getStaffetteAmountFiltered($search)
    {
        $db = static::getDB();
        $sql = "SELECT COUNT(interna.totale)
                FROM
                (
                  SELECT COUNT(DISTINCT s.ID) as totale
                  FROM staffette as s
                  LEFT JOIN gare as g ON g.ID = s.ID_gara
                  LEFT JOIN eventi as e ON e.ID = g.ID_evento
                  LEFT JOIN partecipanti_staffette as ps ON ps.ID_staffetta = s.ID
                  LEFT JOIN utenti as u ON u.ID = ps.ID_partecipante
                  WHERE 1
                
                 ";

                  if ($search != '')
                  {
                      $srcs = explode(' ', $search);
                      foreach ($srcs as $src)
                      {
                          $sql.= " AND (s.ID LIKE '%".$src."%' OR
                                        s.nome LIKE '%".$src."%' OR
                                        g.nome LIKE '%".$src."%' OR
                                        e.nome LIKE '%".$src."%' OR
                                        u.nome LIKE '%".$src."%' OR
                                        u.cognome LIKE '%".$src."%' 
                                        ) ";
                      }
                  }

        $sql.= ' GROUP BY s.ID
                 ) as interna';

        $stmt = $db->prepare($sql);
        $stmt->execute();
       
        return $stmt->fetchColumn();
    }

    public function getStaffettePagination($order,$start,$length,$search)
    {
        $db = static::getDB();
        $sql = "SELECT s.ID as A_ID_staffetta,
                       s.nome as A_nome_staffetta,
                       g.nome as A_nome_gara,
                       e.nome as A_nome_evento
                FROM staffette as s
                LEFT JOIN gare as g ON g.ID = s.ID_gara
                LEFT JOIN eventi as e ON e.ID = g.ID_evento
                LEFT JOIN partecipanti_staffette as ps ON ps.ID_staffetta = s.ID
                LEFT JOIN utenti as u ON u.ID = ps.ID_partecipante
                WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= " AND (s.ID LIKE '%".$src."%' OR
                              s.nome LIKE '%".$src."%' OR
                              g.nome LIKE '%".$src."%' OR
                              e.nome LIKE '%".$src."%' OR
                              u.nome LIKE '%".$src."%' OR
                              u.cognome LIKE '%".$src."%'  
                              )";
            }
        }
        $sql.= ' GROUP BY s.ID';

        $sql.= " ORDER BY ". $order . " LIMIT " . $start . ", " . $length;

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getPartecipantiStaffetta($id_staffetta)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT ps.ordine as A_ps_ordine,
                                     u.nome as A_u_nome,
                                     u.cognome as A_u_cognome
                              FROM partecipanti_staffette as ps  
                              LEFT JOIN utenti as u ON u.ID = ps.ID_partecipante
                              WHERE ps.ID_staffetta = :id_staffetta
                              ORDER BY ps.ordine');
        $stmt->bindValue(':id_staffetta', $id_staffetta);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function ControllaGaraRisultatiPresente($id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM risultati_gare WHERE ID_gara = :id_gara');
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function ControllaGaraStaffettaRisultatiPresente($id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM risultati_staffette WHERE ID_gara = :id_gara');
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function EliminaRisultatiGara($id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('DELETE FROM risultati_gare WHERE ID_gara = :id_gara');
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();
    }

    public function EliminaRisultatiGaraStaffetta($id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('DELETE FROM risultati_staffette WHERE ID_gara = :id_gara');
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();
    }

    public static function InserisciRigaRisultati($id_gara, $valori_riga)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            INSERT INTO risultati_gare
            (
                ID_gara,
                chip,
                boa,
                posizione,
                cognome,
                nome,
                sesso,
                anno,
                categoria,
                nome_team,
                id_societa,
                nazionalita,
                tessera,
                posizione_sesso,
                posizione_categoria,
                racetime,
                realtime,
                ora_arrivo,
                distacco,
                media,
                crudo_flag
            )
            VALUES
            (
                :id_gara,
                :chip,
                :boa,
                :posizione,
                :cognome,
                :nome,
                :sesso,
                :anno,
                :categoria,
                :nome_team,
                :id_societa,
                :nazionalita,
                :tessera,
                :posizione_sesso,
                :posizione_categoria,
                :racetime,
                :realtime,
                :ora_arrivo,
                :distacco,
                :media,
                :crudo_flag
            )');

        // Correzione orari con punto
        $valori_riga[18] = str_replace('.', ':', $valori_riga[18]);
        $valori_riga[19] = str_replace('.', ':', $valori_riga[19]);
        $valori_riga[21] = str_replace('.', ':', $valori_riga[21]);
        $valori_riga[22] = str_replace('.', ':', $valori_riga[22]);

        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->bindValue(':chip', $valori_riga[0]);
        $stmt->bindValue(':boa', $valori_riga[1]);
        $stmt->bindValue(':posizione', $valori_riga[2]);
        $stmt->bindValue(':cognome', $valori_riga[4]);
        $stmt->bindValue(':nome', $valori_riga[5]);
        $stmt->bindValue(':sesso', $valori_riga[6]);
        $stmt->bindValue(':anno', $valori_riga[7]);
        $stmt->bindValue(':categoria', $valori_riga[8]);
        $stmt->bindValue(':nome_team', $valori_riga[9]);
        $stmt->bindValue(':id_societa', $valori_riga[10]);
        $stmt->bindValue(':nazionalita', $valori_riga[11]);
        $stmt->bindValue(':tessera', $valori_riga[12]);
        $stmt->bindValue(':posizione_sesso', $valori_riga[14]);
        $stmt->bindValue(':posizione_categoria', $valori_riga[15]);
        $stmt->bindValue(':racetime', $valori_riga[18]);
        $stmt->bindValue(':realtime', $valori_riga[19]);
        $stmt->bindValue(':ora_arrivo', $valori_riga[20]);
        $stmt->bindValue(':distacco', $valori_riga[21]);
        $stmt->bindValue(':media', $valori_riga[22]);

        $crudo_flag = $valori_riga[16] == 'x' ? true : false;
        $stmt->bindValue(':crudo_flag', $crudo_flag, PDO::PARAM_BOOL);

        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function InserisciRigaRisultatiStaffette($id_gara, $ValoriRiga)
    {
        $Db = static::getDB();

        $sql = "
            INSERT INTO risultati_staffette
            (
                ID_gara,
                ID_Staffetta,
                posizione,
                categoria,
                posizione_categoria,
                realtime,
                ora_arrivo,
                distacco,
                media,
                ID_Utente1,
                ID_Utente2,
                ID_Utente3,
                chip,
                nome_staffetta,
                cognome_1,
                nome_1,
                cognome_2,
                nome_2,
                cognome_3,
                nome_3,
                tempo_1,
                tempo_2,
                tempo_3
            )
            VALUES
            (
                :ID_gara,
                :ID_Staffetta,
                :posizione,
                :categoria,
                :posizione_categoria,
                :realtime,
                :ora_arrivo,
                :distacco,
                :media,
                :ID_Utente1,
                :ID_Utente2,
                :ID_Utente3,
                :chip,
                :nome_staffetta,
                :cognome_1,
                :nome_1,
                :cognome_2,
                :nome_2,
                :cognome_3,
                :nome_3,
                :tempo_1,
                :tempo_2,
                :tempo_3
            )
        ";

        $Stmt = $Db->prepare($sql);

        // Correzione orari con punto
        $ValoriRiga[15] = str_replace('.', ':', $ValoriRiga[15]);
        $ValoriRiga[46] = str_replace('.', ':', $ValoriRiga[46]);
        $ValoriRiga[47] = str_replace('.', ':', $ValoriRiga[47]);

        $Stmt->bindValue(':ID_gara', $id_gara);
        $Stmt->bindValue(':ID_Staffetta', $ValoriRiga[1]);
        $Stmt->bindValue(':posizione', $ValoriRiga[2]);
        $Stmt->bindValue(':categoria', $ValoriRiga[8]);
        $Stmt->bindValue(':posizione_categoria', $ValoriRiga[13]);
        $Stmt->bindValue(':realtime', $ValoriRiga[15]);
        $Stmt->bindValue(':ora_arrivo', $ValoriRiga[16]);
        $Stmt->bindValue(':distacco', $ValoriRiga[46]);
        $Stmt->bindValue(':media', $ValoriRiga[47]);
        $Stmt->bindValue(':ID_Utente1', $ValoriRiga[22]);
        $Stmt->bindValue(':ID_Utente2', $ValoriRiga[28]);
        $Stmt->bindValue(':ID_Utente3', $ValoriRiga[34]);
        $Stmt->bindValue(':chip', $ValoriRiga[0]);
        $Stmt->bindValue(':nome_staffetta', $ValoriRiga[9]);
        $Stmt->bindValue(':cognome_1', $ValoriRiga[17]);
        $Stmt->bindValue(':nome_1', $ValoriRiga[18]);
        $Stmt->bindValue(':cognome_2', $ValoriRiga[23]);
        $Stmt->bindValue(':nome_2', $ValoriRiga[24]);
        $Stmt->bindValue(':cognome_3', $ValoriRiga[29]);
        $Stmt->bindValue(':nome_3', $ValoriRiga[30]);
        $Stmt->bindValue(':tempo_1', $ValoriRiga[35]);
        $Stmt->bindValue(':tempo_2', $ValoriRiga[36]);
        $Stmt->bindValue(':tempo_3', $ValoriRiga[37]);

        $Stmt->execute();

        return $Db->lastInsertId();
    }

    public static function getRisultatiGara($id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *
                              FROM risultati_gare
                              WHERE ID_gara = :id_gara
                              ORDER BY posizione DESC');
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function checkIDTessera($id_tessera)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*)
                              FROM risultati_gare as rg
                              LEFT JOIN utenti as u ON u.ID = rg.tessera
                              WHERE rg.tessera = :id_tessera');
        $stmt->bindValue(':id_tessera', $id_tessera);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public static function AggiornaRigaCrudoRisultati($id_gara, $tessera, $posizione_crudo)
    {
        $db = static::getDB();
        $stmt = $db->prepare("UPDATE risultati_gare
                              SET posizione_crudo = :posizione_crudo
                              WHERE ID_gara = :id_gara
                              AND tessera = :tessera
                              ");
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->bindValue(':tessera', $tessera);
        $stmt->bindValue(':posizione_crudo', $posizione_crudo);
        $stmt->execute();
    }

    public static function getRisultatiGaraGenerale($id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            SELECT
                rg.ID as rg_id,
                rg.posizione as rg_posizione,
                rg.posizione_sesso as rg_posizione_sesso,
                rg.posizione_crudo as rg_posizione_crudo,
                rg.sesso as rg_sesso,
                rg.cognome as rg_cognome,
                rg.nome as rg_nome,
                rg.racetime as rg_racetime,
                rg.ora_arrivo as rg_ora_arrivo,
                rg.distacco as rg_distacco,
                rg.media as rg_media,
                rg.tessera as rg_tessera,
                rg.categoria as rg_categoria,
                u.email as u_email,
                u.sesso as u_sesso,
                u.foto_profilo as u_foto_profilo,
                s.nome as s_nome_squadra,
                s.foto as s_foto_squadra
            FROM risultati_gare as rg
            LEFT JOIN utenti as u ON u.ID = rg.tessera
            LEFT JOIN squadre as s ON u.IDSquadra = s.ID
            WHERE rg.ID_gara = :id_gara
            ORDER BY posizione ASC');
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getRisultatiGaraStaffetta($id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            SELECT *
            FROM risultati_staffette
            WHERE ID_gara = :id_gara
            ORDER BY posizione ASC');
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function countRisultatiGaraCrudo($id_gara, $sesso = 0)
    {
        $db = static::getDB();
        $sql = 'SELECT COUNT(*)
                FROM risultati_gare
                WHERE ID_gara = :id_gara ';

        if ($sesso != 0) $sql .= ' AND sesso = :sesso ';

        $sql .= ' AND crudo_flag > 0 ';

        $stmt = $db->prepare($sql);
        
        $stmt->bindValue(':id_gara', $id_gara);
        if ($sesso != 0)$stmt->bindValue(':sesso', $sesso);

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public static function getRisultatiGaraCrudo($id_gara)
    {
        $db = static::getDB();
        $sql = '
            SELECT
                rg.ID as rg_id,
                rg.posizione as rg_posizione,
                rg.posizione_sesso as rg_posizione_sesso,
                rg.posizione_crudo as rg_posizione_crudo,
                rg.sesso as rg_sesso,
                rg.cognome as rg_cognome,
                rg.nome as rg_nome,
                rg.racetime as rg_racetime,
                rg.ora_arrivo as rg_ora_arrivo,
                rg.distacco as rg_distacco,
                rg.media as rg_media,
                rg.tessera as rg_tessera,
                u.email as u_email,
                u.sesso as u_sesso,
                u.foto_profilo as u_foto_profilo,
                s.nome as s_nome_squadra,
                s.foto as s_foto_squadra
            FROM risultati_gare as rg
            LEFT JOIN utenti as u ON u.ID = rg.tessera
            LEFT JOIN squadre as s ON u.IDSquadra = s.ID
            WHERE rg.ID_gara = :id_gara
            AND rg.crudo_flag > 0
            ORDER BY rg.racetime ASC';

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getRisultatiGaraAvis($id_gara)
    {
        $db = static::getDB();
        $sql = "
            SELECT
                rg.ID as rg_id,
                rg.posizione as rg_posizione,
                rg.posizione_sesso as rg_posizione_sesso,
                rg.posizione_crudo as rg_posizione_crudo,
                rg.sesso as rg_sesso,
                rg.cognome as rg_cognome,
                rg.nome as rg_nome,
                rg.racetime as rg_racetime,
                rg.ora_arrivo as rg_ora_arrivo,
                rg.distacco as rg_distacco,
                rg.media as rg_media,
                rg.tessera as rg_tessera,
                u.email as u_email,
                u.sesso as u_sesso,
                u.foto_profilo as u_foto_profilo,
                s.nome as s_nome_squadra,
                s.foto as s_foto_squadra
            FROM risultati_gare as rg
            LEFT JOIN utenti as u ON u.ID = rg.tessera
            LEFT JOIN squadre as s ON u.IDSquadra = s.ID
            WHERE rg.ID_gara = :id_gara
            AND u.donatore_avis = 'Si'
            ORDER BY rg.posizione ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function countRisultatiGaraAvis($id_gara, $sesso = 0)
    {
        $db = static::getDB();
        $sql = 'SELECT COUNT(*)
                FROM risultati_gare as rg
                LEFT JOIN utenti as u ON u.ID = rg.tessera
                WHERE rg.ID_gara = :id_gara ';

        if ($sesso != 0) $sql .= ' AND rg.sesso = :sesso ';

        $sql .= " AND u.donatore_avis = 'Si' ";

        $stmt = $db->prepare($sql);
        
        $stmt->bindValue(':id_gara', $id_gara);
        if ($sesso != 0)$stmt->bindValue(':sesso', $sesso);

        $stmt->execute();

        return $stmt->fetchColumn();
    }


    public static function getRisultatiGaraOver30($id_gara)
    {
        $db = static::getDB();
        $sql = "SELECT
            rg.ID as rg_id,
            rg.posizione as rg_posizione,
            rg.posizione_sesso as rg_posizione_sesso,
            rg.posizione_crudo as rg_posizione_crudo,
            rg.sesso as rg_sesso,
            rg.cognome as rg_cognome,
            rg.nome as rg_nome,
            rg.racetime as rg_racetime,
            rg.ora_arrivo as rg_ora_arrivo,
            rg.distacco as rg_distacco,
            rg.media as rg_media,
            rg.tessera as rg_tessera,
            rg.categoria as rg_categoria,
            u.email as u_email,
            u.sesso as u_sesso,
            u.foto_profilo as u_foto_profilo,
            s.nome as s_nome_squadra,
            s.foto as s_foto_squadra
        FROM risultati_gare as rg
        LEFT JOIN utenti as u ON u.ID = rg.tessera
        LEFT JOIN squadre as s ON u.IDSquadra = s.ID
        WHERE rg.ID_gara = :id_gara
        AND ( rg.categoria = 'O30F' OR rg.categoria = 'O30M' )
        ORDER BY rg.posizione ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function countRisultatiGaraOver30($id_gara, $sesso = 0)
    {
        $db = static::getDB();
        $sql = 'SELECT COUNT(*)
                FROM risultati_gare as rg
                WHERE rg.ID_gara = :id_gara ';

        if ($sesso != 0) $sql .= ' AND rg.sesso = :sesso ';

        $sql .= " AND ( rg.categoria = 'O30F' OR rg.categoria = 'O30M' ) ";

        $stmt = $db->prepare($sql);
        
        $stmt->bindValue(':id_gara', $id_gara);
        if ($sesso != 0)$stmt->bindValue(':sesso', $sesso);

        $stmt->execute();

        return $stmt->fetchColumn();
    }


    public static function getRisultatiGaraOver40($id_gara)
    {
        $db = static::getDB();
        $sql = "SELECT
            rg.ID as rg_id,
            rg.posizione as rg_posizione,
            rg.posizione_sesso as rg_posizione_sesso,
            rg.posizione_crudo as rg_posizione_crudo,
            rg.sesso as rg_sesso,
            rg.cognome as rg_cognome,
            rg.nome as rg_nome,
            rg.racetime as rg_racetime,
            rg.ora_arrivo as rg_ora_arrivo,
            rg.distacco as rg_distacco,
            rg.media as rg_media,
            rg.tessera as rg_tessera,
            rg.categoria as rg_categoria,
            u.email as u_email,
            u.sesso as u_sesso,
            u.foto_profilo as u_foto_profilo,
            s.nome as s_nome_squadra,
            s.foto as s_foto_squadra
        FROM risultati_gare as rg
        LEFT JOIN utenti as u ON u.ID = rg.tessera
        LEFT JOIN squadre as s ON u.IDSquadra = s.ID
        WHERE rg.ID_gara = :id_gara
        AND ( rg.categoria = 'O40F' OR rg.categoria = 'O40M' )
        ORDER BY rg.posizione ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function countRisultatiGaraOver40($id_gara, $sesso = 0)
    {
        $db = static::getDB();
        $sql = 'SELECT COUNT(*)
                FROM risultati_gare as rg
                WHERE rg.ID_gara = :id_gara ';

        if ($sesso != 0) $sql .= ' AND rg.sesso = :sesso ';

        $sql .= " AND ( rg.categoria = 'O40F' OR rg.categoria = 'O40M' ) ";

        $stmt = $db->prepare($sql);
        
        $stmt->bindValue(':id_gara', $id_gara);
        if ($sesso != 0)$stmt->bindValue(':sesso', $sesso);

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public static function getRisultatiGaraOver50($id_gara)
    {
        $db = static::getDB();
        $sql = "SELECT
            rg.ID as rg_id,
            rg.posizione as rg_posizione,
            rg.posizione_sesso as rg_posizione_sesso,
            rg.posizione_crudo as rg_posizione_crudo,
            rg.sesso as rg_sesso,
            rg.cognome as rg_cognome,
            rg.nome as rg_nome,
            rg.racetime as rg_racetime,
            rg.ora_arrivo as rg_ora_arrivo,
            rg.distacco as rg_distacco,
            rg.media as rg_media,
            rg.tessera as rg_tessera,
            rg.categoria as rg_categoria,
            u.email as u_email,
            u.sesso as u_sesso,
            u.foto_profilo as u_foto_profilo,
            s.nome as s_nome_squadra,
            s.foto as s_foto_squadra
        FROM risultati_gare as rg
        LEFT JOIN utenti as u ON u.ID = rg.tessera
        LEFT JOIN squadre as s ON u.IDSquadra = s.ID
        WHERE rg.ID_gara = :id_gara
        AND ( rg.categoria = 'O50F' OR rg.categoria = 'O50M' )
        ORDER BY rg.posizione ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function countRisultatiGaraOver50($id_gara, $sesso = 0)
    {
        $db = static::getDB();
        $sql = 'SELECT COUNT(*)
                FROM risultati_gare as rg
                WHERE rg.ID_gara = :id_gara ';

        if ($sesso != 0) $sql .= ' AND rg.sesso = :sesso ';

        $sql .= " AND ( rg.categoria = 'O50F' OR rg.categoria = 'O50M' ) ";

        $stmt = $db->prepare($sql);
        
        $stmt->bindValue(':id_gara', $id_gara);
        if ($sesso != 0)$stmt->bindValue(':sesso', $sesso);

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public static function getRisultatiGaraOver60($id_gara)
    {
        $db = static::getDB();
        $sql = "SELECT
            rg.ID as rg_id,
            rg.posizione as rg_posizione,
            rg.posizione_sesso as rg_posizione_sesso,
            rg.posizione_crudo as rg_posizione_crudo,
            rg.sesso as rg_sesso,
            rg.cognome as rg_cognome,
            rg.nome as rg_nome,
            rg.racetime as rg_racetime,
            rg.ora_arrivo as rg_ora_arrivo,
            rg.distacco as rg_distacco,
            rg.media as rg_media,
            rg.tessera as rg_tessera,
            rg.categoria as rg_categoria,
            u.email as u_email,
            u.sesso as u_sesso,
            u.foto_profilo as u_foto_profilo,
            s.nome as s_nome_squadra,
            s.foto as s_foto_squadra
        FROM risultati_gare as rg
        LEFT JOIN utenti as u ON u.ID = rg.tessera
        LEFT JOIN squadre as s ON u.IDSquadra = s.ID
        WHERE rg.ID_gara = :id_gara
        AND ( rg.categoria = 'O60F' OR rg.categoria = 'O60M' )
        ORDER BY rg.posizione ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function countRisultatiGaraOver60($id_gara, $sesso = 0)
    {
        $db = static::getDB();
        $sql = 'SELECT COUNT(*)
                FROM risultati_gare as rg
                WHERE rg.ID_gara = :id_gara ';

        if ($sesso != 0) $sql .= ' AND rg.sesso = :sesso ';

        $sql .= " AND ( rg.categoria = 'O60F' OR rg.categoria = 'O60M' ) ";

        $stmt = $db->prepare($sql);
        
        $stmt->bindValue(':id_gara', $id_gara);
        if ($sesso != 0)$stmt->bindValue(':sesso', $sesso);

        $stmt->execute();

        return $stmt->fetchColumn();
    }


    public static function ImpostaMostraRisultatiGara($id_gara, $flag)
    {
        $db = static::getDB();
        $stmt = $db->prepare("
            UPDATE gare
            SET mostra_risultati = :flag
            WHERE ID = :id_gara
        ");
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->bindValue(':flag', $flag, PDO::PARAM_BOOL);
        $stmt->execute();
    }
}
