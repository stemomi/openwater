<?php

namespace App\Models;

use PDO;
use DateTime;
use stdClass;

class GareIndividualiModel extends \Core\Model
{
    public static function getAllGareAperte()
    {
        $db = static::getDB();
        $sql = 'SELECT *,
                        e.nome as nome_evento,
                        g.nome as nome_gara,
                        g.ID as ID_gara
                FROM gare_individuali as g
                LEFT JOIN eventi_individuali as e ON g.ID_evento = e.ID
                WHERE CURDATE() <= e.data
                AND e.data_apertura_iscrizioni <= CURDATE()
                ORDER BY e.data';

        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getAllGare()
    {
        $db = static::getDB();
        $sql = 'SELECT *,
                        e.nome as nome_evento,
                        g.nome as nome_gara,
                        g.ID as ID_gara
                FROM gare_individuali as g
                LEFT JOIN eventi_individuali as e ON g.ID_evento = e.ID ';

        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getAllGareAperteUtente($utente_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT  i.*,
                                      e.ID as ID_evento,
                                      e.nome as nome_evento,
                                      g.nome as nome_gara,
                                      g.ID as ID_gara,
                                      g.prezzo
                              FROM iscrizioni_gare_individuali as i
                              LEFT JOIN gare_individuali as g ON i.ID_gara = g.ID
                              LEFT JOIN eventi_individuali as e ON g.ID_evento = e.ID
                              WHERE CURDATE() <= e.data
                              AND i.ID_utente = :utente_id');
        $stmt->bindValue(':utente_id', $utente_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getAllGarePassateUtente($utente_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT  e.ID as ID_evento,
                                      e.nome as nome_evento,
                                      g.nome as nome_gara,
                                      g.ID as ID_gara,
                                      g.mostra_risultati as mostra_risultati
                              FROM iscrizioni_gare_individuali as i
                              INNER JOIN gare_individuali as g ON i.ID_gara = g.ID
                              INNER JOIN eventi_individuali as e ON g.ID_evento = e.ID
                              WHERE e.data < CURDATE()
                              AND i.ID_utente = :utente_id');
        $stmt->bindValue(':utente_id', $utente_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getAllGareAperteUtenteDaPagare($utente_id, $data_pagamento = '')
    {
        $db = static::getDB();
        $sql= 'SELECT i.*,
                      e.nome as nome_evento,
                      g.nome as nome_gara
              FROM iscrizioni_gare_individuali as i
              LEFT JOIN gare_individuali as g ON i.ID_gara = g.ID
              LEFT JOIN eventi_individuali as e ON g.ID_evento = e.ID
              WHERE CURDATE() <= e.data
              AND i.ID_utente = :utente_id
              AND i.pagato = 0 ';

        if ($data_pagamento != '') $sql .= ' AND i.dataora < :data_pagamento';

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':utente_id', $utente_id);
        if ($data_pagamento != '') $stmt->bindValue(':data_pagamento', $data_pagamento);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getGara($gara_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *
                              FROM gare_individuali
                              WHERE ID = :gara_id');
        $stmt->bindValue(':gara_id', $gara_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function getGaraConDettagli($gara_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *,
                                      e.nome as nome_evento,
                                      g.nome as nome_gara,
                                      g.ID as ID_gara
                              FROM gare_individuali as g
                              LEFT JOIN eventi_individuali as e ON g.ID_evento = e.ID 
                              WHERE g.ID = :gara_id');
        $stmt->bindValue(':gara_id', $gara_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function getGaraUtente($utente_id, $gara_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT igi.*,
                                     rgi.ID as RGI_ID,
                                     rgi.tempo as RGI_tempo
                              FROM iscrizioni_gare_individuali as igi
                              LEFT JOIN risultati_gare_individuali as rgi ON rgi.ID_iscrizione = igi.ID
                              WHERE igi.ID_utente = :utente_id
                              AND igi.ID_gara = :gara_id
                              ORDER BY igi.ID DESC
                              LIMIT 1');
        $stmt->bindValue(':utente_id', $utente_id);
        $stmt->bindValue(':gara_id', $gara_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function getIscrizioneUtente($utente_id, $iscrizione_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT igi.*,
                                     rgi.ID as RGI_ID,
                                     rgi.tempo as RGI_tempo
                              FROM iscrizioni_gare_individuali as igi
                              LEFT JOIN risultati_gare_individuali as rgi ON rgi.ID_iscrizione = igi.ID
                              WHERE igi.ID_utente = :utente_id
                              AND igi.ID = :iscrizione_id');
        $stmt->bindValue(':utente_id', $utente_id);
        $stmt->bindValue(':iscrizione_id', $iscrizione_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function addRisultato(
        $ID_iscrizione,
        $tempo,
        $localita,
        $ID_provincia,
        $tipo_acqua,
        $gps_file,
        $gps_foto,
        $gps_link
    )
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            INSERT INTO risultati_gare_individuali
            (
                ID_iscrizione,
                tempo,
                localita,
                ID_provincia,
                tipo_acqua,
                gps_file,
                gps_foto,
                gps_link
            )
            VALUES
            (
                :ID_iscrizione,
                :tempo,
                :localita,
                :ID_provincia,
                :tipo_acqua,
                :gps_file,
                :gps_foto,
                :gps_link
            )');

        $stmt->bindValue(':ID_iscrizione', $ID_iscrizione);
        $stmt->bindValue(':tempo', $tempo);
        $stmt->bindValue(':localita', $localita);
        $stmt->bindValue(':ID_provincia', $ID_provincia);
        $stmt->bindValue(':tipo_acqua', $tipo_acqua);
        $stmt->bindValue(':gps_file', $gps_file);
        $stmt->bindValue(':gps_foto', $gps_foto);
        $stmt->bindValue(':gps_link', $gps_link);
        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function checkIscrizioneGara($utente_id, $gara_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*)
                              FROM iscrizioni_gare_individuali
                              WHERE ID_utente = :utente_id
                              AND ID_gara = :gara_id');
        $stmt->bindValue(':utente_id', $utente_id);
        $stmt->bindValue(':gara_id', $gara_id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function checkPagamentoGara($utente_id, $gara_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*)
                              FROM iscrizioni_gare_individuali
                              WHERE ID_utente = :utente_id
                              AND ID_gara = :gara_id
                              AND pagato = 1');
        $stmt->bindValue(':utente_id', $utente_id);
        $stmt->bindValue(':gara_id', $gara_id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function getTotaleDaPagareUtente($utente_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT g.prezzo
                              FROM iscrizioni_gare_individuali as i
                              LEFT JOIN gare_individuali as g ON i.ID_gara = g.ID
                              LEFT JOIN eventi_individuali as e ON g.ID_evento = e.ID
                              WHERE i.ID_utente = :utente_id
                              AND CURDATE() <= e.data
                              AND i.pagato = 0');
        $stmt->bindValue(':utente_id', $utente_id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function setGaraPagata($utente_id, $gara_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('UPDATE iscrizioni_gare_individuali
                              SET pagato = 1
                              WHERE ID_utente = :utente_id
                              AND ID_gara = :gara_id');
        $stmt->bindValue(':gara_id', $gara_id);
        $stmt->bindValue(':utente_id', $utente_id);
        $stmt->execute();
    }

    public static function insertGaraUtente($utente_id, $gara_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('INSERT INTO iscrizioni_gare_individuali
                              (ID_utente, ID_gara, dataora)
                              VALUES
                              (:utente_id, :gara_id, NOW())');
        $stmt->bindValue(':utente_id', $utente_id);
        $stmt->bindValue(':gara_id', $gara_id);
        $stmt->execute();
    }

    public static function salvaPagamentoGara($utente_id, $ID_iscrizione, $pagamento_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('INSERT INTO pagamenti_gare_individuali
                              (ID_utente, ID_iscrizione, ID_pagamento)
                              VALUES
                              (:utente_id, :ID_iscrizione, :pagamento_id)');
        $stmt->bindValue(':utente_id', $utente_id);
        $stmt->bindValue(':ID_iscrizione', $ID_iscrizione);
        $stmt->bindValue(':pagamento_id', $pagamento_id);
        $stmt->execute();
    }

    // DA QUI FUNZIONI
    public static function EstraeGareAperteUtente($Utente)
    {
        $GareAperteUtente= self::getAllGareAperteUtente($Utente->ID);
        $Eventi = [];

        foreach($GareAperteUtente as $gara)
        {
            // Estrae gara utente
            $GaraUtente = self::getIscrizioneUtente($Utente->ID, $gara->ID);

            if ($GaraUtente)
            {
                $gara->iscritto = 1;
                $gara->pagato = $GaraUtente->pagato;
                $gara->ID_iscrizione = $GaraUtente->ID;
                $gara->ID_risultato = $GaraUtente->RGI_ID;
                $gara->tempo = $GaraUtente->RGI_tempo;
            }
            else
            {
                $gara->iscritto = 0;
                $gara->pagato = 0;
                $gara->ID_iscrizione = 0;
                $gara->ID_risultato = 0;
                $gara->tempo = '';
            }
            
            $Eventi[$gara->ID_evento][] = $gara;
        }

        // Calcola totale da pagare
        $TotaleDaPagareUtente = self::getTotaleDaPagareUtente($Utente->ID);
        if (!$TotaleDaPagareUtente) $TotaleDaPagareUtente= "0.00";

        $Risposta = new stdClass();
        $Risposta->Eventi = $Eventi;
        $Risposta->TotaleDaPagareUtente = $TotaleDaPagareUtente;
        return $Risposta;
    }
}
