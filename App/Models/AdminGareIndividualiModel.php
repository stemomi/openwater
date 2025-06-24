<?php

namespace App\Models;

use PDO;
use DateTime;

class AdminGareIndividualiModel extends \Core\Model
{
    public static function InserisciGara($ID_evento,
                                         $nome,
                                         $prezzo
                                        )
    {
        $db = static::getDB();
        $stmt = $db->prepare(
          'INSERT INTO gare_individuali (
            ID_evento,
            nome,
            prezzo
            )
          VALUES (
            :ID_evento,
            :nome,
            :prezzo
            )');
        $stmt->bindValue(':ID_evento', $ID_evento);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':prezzo', $prezzo);
        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function AggiornaGara(  $id_gara,
                                          $id_evento,
                                          $nome,
                                          $prezzo,
                                          $mostra_risultati
                                        )
    {
        $db = static::getDB();
        $stmt = $db->prepare(
          " UPDATE gare_individuali
            SET ID_evento = :id_evento,
                nome = :nome,
                prezzo = :prezzo,
                mostra_risultati = :mostra_risultati
            WHERE ID = :id_gara
            ");
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':prezzo', $prezzo);
        $stmt->bindValue(':mostra_risultati', $mostra_risultati);
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
        $stmt = $db->prepare('INSERT INTO eventi_individuali (nome, data, data_apertura_iscrizioni)
                              VALUES (:nome, :data, :data_apertura_iscrizioni)');
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':data', $data);
        $stmt->bindValue(':data_apertura_iscrizioni', $data_apertura_iscrizioni);
        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function AggiornaEvento( $id_evento,
                                           $nome,
                                           $data,
                                           $data_apertura_iscrizioni
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
        $stmt = $db->prepare("UPDATE eventi_individuali
                              SET nome = :nome,
                                  data = :data,
                                  data_apertura_iscrizioni = :data_apertura_iscrizioni
                              WHERE ID = :id_evento
                              ");
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':data', $data);
        $stmt->bindValue(':data_apertura_iscrizioni', $data_apertura_iscrizioni);
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->execute();
    }

    public static function getEventi($solo_attivi = 0)
    {
        $db = static::getDB();
        $sql = 'SELECT * FROM eventi_individuali ';

        if ($solo_attivi == 1)
          $sql .= ' WHERE NOW() < data';

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getEvento($id_evento)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT * FROM eventi_individuali WHERE ID = :id_evento');
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getEventiAmount()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM eventi_individuali');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getEventiAmountFiltered($search)
    {
        $db = static::getDB();
        $sql = "SELECT COUNT(*) FROM eventi_individuali WHERE 1 ";

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
        $sql = "SELECT * FROM eventi_individuali WHERE 1 ";

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
        $stmt = $db->prepare('SELECT * FROM gare_individuali WHERE ID = :id_gara');
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getGareAmount()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM gare_individuali');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getGareAmountFiltered($search)
    {
        $db = static::getDB();
        $sql = "SELECT COUNT(*)
                FROM gare_individuali as g
                LEFT JOIN eventi_individuali as e ON e.ID = g.ID_evento
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
                       e.nome as A_nome_evento,
                       e.data as A_data_evento
                FROM gare_individuali as g
                LEFT JOIN eventi_individuali as e ON e.ID = g.ID_evento
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

    public static function getRisultatiGara($id_gara)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            SELECT rgi.*,
                   pi.nome as provincia_nome,
                   u.nome as utente_nome,
                   u.cognome as utente_cognome,
                   igi.convalidato
            FROM risultati_gare_individuali as rgi
            LEFT JOIN iscrizioni_gare_individuali as igi ON igi.ID = rgi.ID_iscrizione
            LEFT JOIN gare_individuali as gi ON gi.ID = igi.ID_gara
            LEFT JOIN province_italiane as pi ON pi.ID = rgi.ID_provincia
            LEFT JOIN utenti as u ON u.ID = igi.ID_utente
            WHERE igi.ID_gara = :id_gara
            ORDER BY rgi.tempo ASC');
        $stmt->bindValue(':id_gara', $id_gara);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getIscrizioneDettagli($id_iscrizione)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            SELECT igi.ID as IGI_ID,
                   igi.dataora as IGI_dataora,
                   igi.pagato as IGI_pagato,
                   igi.convalidato as IGI_convalidato,
                   igi.ID_utente as IGI_ID_utente,
                   rgi.tempo as RGI_tempo,
                   rgi.localita as RGI_localita,
                   rgi.tipo_acqua as RGI_tipo_acqua,
                   CASE
                        WHEN rgi.tipo_acqua = 1 THEN "Mare"
                        WHEN rgi.tipo_acqua = 2 THEN "Lago"
                        ELSE "Tipo acqua non trovato"
                   END as RGI_tipo_acqua_nome,
                   rgi.gps_file as RGI_gps_file,
                   rgi.gps_foto as RGI_gps_foto,
                   rgi.gps_link as RGI_gps_link,
                   pi.nome as PI_nome,
                   gi.nome as GI_nome,
                   gi.prezzo as GI_prezzo,
                   ei.nome as EI_nome,
                   p.tipo as P_tipo,
                   p.file as P_file,
                   p.ID_transazione_paypal as P_ID_transazione_paypal,
                   p.codice_coupon as P_codice_coupon
            FROM iscrizioni_gare_individuali as igi
            LEFT JOIN risultati_gare_individuali AS rgi ON rgi.ID_iscrizione = igi.ID
            LEFT JOIN province_italiane as pi ON pi.ID = rgi.ID_provincia
            LEFT JOIN gare_individuali as gi ON gi.ID = igi.ID_gara
            LEFT JOIN eventi_individuali as ei ON ei.ID = gi.ID_evento
            LEFT JOIN pagamenti_gare_individuali as pgi ON pgi.ID_iscrizione = igi.ID
            LEFT JOIN pagamenti as p ON p.ID = pgi.ID_pagamento
            WHERE igi.ID = :id_iscrizione');
        $stmt->bindValue(':id_iscrizione', $id_iscrizione);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function impostaIscrizioneComeIscritto($id_iscrizione)
    {
        $db = static::getDB();
        $stmt = $db->prepare('UPDATE iscrizioni_gare_individuali SET pagato = 0, convalidato = 0 WHERE ID = :id_iscrizione');
        $stmt->bindValue(':id_iscrizione', $id_iscrizione);
        $stmt->execute();
    }

    public function impostaIscrizioneComePagato($id_iscrizione)
    {
        $db = static::getDB();
        $stmt = $db->prepare('UPDATE iscrizioni_gare_individuali SET pagato = 1, convalidato = 0 WHERE ID = :id_iscrizione');
        $stmt->bindValue(':id_iscrizione', $id_iscrizione);
        $stmt->execute();
    }

    public function impostaIscrizioneComeConvalidato($id_iscrizione)
    {
        $db = static::getDB();
        $stmt = $db->prepare('UPDATE iscrizioni_gare_individuali SET pagato = 1, convalidato = 1 WHERE ID = :id_iscrizione');
        $stmt->bindValue(':id_iscrizione', $id_iscrizione);
        $stmt->execute();
    }
}