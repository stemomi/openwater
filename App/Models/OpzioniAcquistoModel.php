<?php

namespace App\Models;

use PDO;
use DateTime;

class OpzioniAcquistoModel extends \Core\Model
{
    public static function getOpzioneAcquisto($opzione_acquisto_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *
                              FROM opzioni_acquisto
                              WHERE ID = :opzione_acquisto_id');
        $stmt->bindValue(':opzione_acquisto_id', $opzione_acquisto_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function getOpzioneAcquistoEvento($opzione_acquisto_evento_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            SELECT
                eoa.*,
                oa.prezzo as oa_prezzo
            FROM eventi_opzioni_acquisto as eoa
            LEFT JOIN opzioni_acquisto as oa ON oa.ID = eoa.IDOpzioneAcquisto
            WHERE eoa.ID = :opzione_acquisto_evento_id');
        $stmt->bindValue(':opzione_acquisto_evento_id', $opzione_acquisto_evento_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function checkOpzioneAcquistoEventoUtilizzata($opzione_acquisto_evento_id)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            SELECT COUNT(*)
            FROM acquisti_opzioni_acquisto_eventi
            WHERE IDOpzioneAcquistoEvento = :opzione_acquisto_evento_id');

        $stmt->bindValue(':opzione_acquisto_evento_id', $opzione_acquisto_evento_id);
        $stmt->execute();

        return $stmt->fetchColumn() > 0 ? true : false;
    }
    
    public static function getAllOpzioniAcquisto()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *
                              FROM opzioni_acquisto
                              ORDER BY nome');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getOpzioniAcquistoAmount()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM opzioni_acquisto');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getOpzioniAcquistoAmountFiltered($search)
    {
        $db = static::getDB();
        $sql = "SELECT COUNT(*) FROM opzioni_acquisto
                WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= "
                AND
                (
                    ID LIKE '%".$src."%' OR
                    nome LIKE '%".$src."%' OR
                    prezzo LIKE '%".$src."%'
                ) ";
            }
        }

        $stmt = $db->prepare($sql);
        $stmt->execute();
       
        return $stmt->fetchColumn();
    }

    public function getOpzioniAcquistoPagination($order,$start,$length,$search)
    {
        $db = static::getDB();
        $sql = "SELECT * FROM opzioni_acquisto
                WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= "
                AND
                (
                    ID LIKE '%".$src."%' OR
                    nome LIKE '%".$src."%' OR
                    prezzo LIKE '%".$src."%'
                ) ";
            }
        }

        $sql.= " ORDER BY ". $order . " LIMIT " . $start . ", " . $length;

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function AggiornaOpzioneAcquisto($opzione_acquisto_id, $nome, $prezzo)
    {
    	$db = static::getDB();
    	$sql = "
            UPDATE opzioni_acquisto
			SET nome = :nome,
                prezzo = :prezzo
			WHERE ID = :opzione_acquisto_id";

    	$stmt = $db->prepare($sql);
    	$stmt->bindValue(':opzione_acquisto_id', $opzione_acquisto_id);
    	$stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':prezzo', $prezzo);
    	$stmt->execute();
    }

    public function CreaOpzioneAcquisto($nome, $prezzo)
    {
        $db = static::getDB();
        $sql = "
        INSERT INTO opzioni_acquisto
        (
            nome,
            prezzo
        )
        VALUES
        (
            :nome,
            :prezzo
        )";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':prezzo', $prezzo);
        $stmt->execute();

        return $db->lastInsertId();
    } 

    public static function cancellaOpzioneAcquistoEvento($id_opzione_acquisto_evento)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            DELETE eventi_opzioni_acquisto
            FROM eventi_opzioni_acquisto
            WHERE ID = :id_opzione_acquisto_evento');
        $stmt->bindValue(':id_opzione_acquisto_evento', $id_opzione_acquisto_evento);
        $stmt->execute();
    }

    public static function aggiungiOpzioneAcquistoEvento($id_evento, $id_opzione_acquisto_evento)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            INSERT INTO eventi_opzioni_acquisto
            (
                IDEvento,
                IDOpzioneAcquisto
            )
            VALUES
            (
                :id_evento,
                :id_opzione_acquisto_evento
            )'
        );
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->bindValue(':id_opzione_acquisto_evento', $id_opzione_acquisto_evento);
        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function checkCollegamentoEventoOpzioneAcquisto($id_evento, $id_opzione_acquisto_evento)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            SELECT COUNT(*)
            FROM eventi_opzioni_acquisto
            WHERE IDEvento = :id_evento
            AND IDOpzioneAcquisto = :id_opzione_acquisto_evento');
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->bindValue(':id_opzione_acquisto_evento', $id_opzione_acquisto_evento);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public static function getOpzioniAcquistoEvento($id_evento)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            SELECT
                oa.ID as oa_ID,
                oa.nome as oa_nome,
                oa.prezzo as oa_prezzo,
                eoa.ID as eoa_ID,
                eoa.obbligatorio as eoa_obbligatorio
            FROM eventi_opzioni_acquisto as eoa
            INNER JOIN opzioni_acquisto as oa ON oa.ID = eoa.IDOpzioneAcquisto
            WHERE eoa.IDEvento = :id_evento');
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function setOpzioneAcquistoEventoObbligatorio($opzione_acquisto_id, $obbligatorio)
    {
        $db = static::getDB();
        $sql = "
            UPDATE eventi_opzioni_acquisto
            SET obbligatorio = :obbligatorio
            WHERE ID = :opzione_acquisto_id";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':opzione_acquisto_id', $opzione_acquisto_id);
        $stmt->bindValue(':obbligatorio', $obbligatorio);
        $stmt->execute();
    }

    public function inserisciAcquistoOpzioneAcquistoEvento(
        $IDUtente,
        $IDOpzioneAcquistoEvento,
        $prezzo
    )
    {
        $db = static::getDB();
        $sql = "
        INSERT INTO acquisti_opzioni_acquisto_eventi
        (
            IDUtente,
            IDOpzioneAcquistoEvento,
            prezzo,
            dataora
        )
        VALUES
        (
            :IDUtente,
            :IDOpzioneAcquistoEvento,
            :prezzo,
            :dataora
        )";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':IDUtente', $IDUtente);
        $stmt->bindValue(':IDOpzioneAcquistoEvento', $IDOpzioneAcquistoEvento);
        $stmt->bindValue(':prezzo', $prezzo);
        $stmt->bindValue(':dataora', date('Y-m-d H:i:s'));
        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function getAcquistoOpzioneEventoUtente($id_utente, $id_opzione_acquisto_evento)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            SELECT *
            FROM acquisti_opzioni_acquisto_eventi as aoae
            WHERE IDUtente = :id_utente
            AND IDOpzioneAcquistoEvento = :id_opzione_acquisto_evento');

        $stmt->bindValue(':id_utente', $id_utente);
        $stmt->bindValue(':id_opzione_acquisto_evento', $id_opzione_acquisto_evento);
        $stmt->execute();

        return $stmt->fetch();
    }

    public static function getAcquistiOpzioniEventiUtente($id_utente, $da_pagare = 0, $evento_id = 0)
    {
        $Db = static::getDB();

        $sql = '
            SELECT
                aoae.*,
                oa.nome as oa_nome
            FROM acquisti_opzioni_acquisto_eventi as aoae
            LEFT JOIN eventi_opzioni_acquisto as eoa on eoa.ID = aoae.IDOpzioneAcquistoEvento
            LEFT JOIN opzioni_acquisto as oa ON oa.ID = eoa.IDOpzioneAcquisto
            LEFT JOIN eventi as e ON e.ID = eoa.IDEvento
            WHERE aoae.IDUtente = :id_utente
            AND CURDATE() <= e.data ';

        if ($da_pagare == 1)
            $sql .= ' AND aoae.pagato = 0 ';

        if ($evento_id)
            $sql .= ' AND eoa.IDEvento = :evento_id ';
        else
            $sql .= " 
                AND e.ID NOT IN (
                    SELECT g.ID_evento FROM iscrizioni_iscrizioni_di_squadra as iids 
                    LEFT JOIN iscrizioni as s ON s.ID = iids.iscrizione_id
                    LEFT JOIN gare as g ON g.ID = s.ID_gara
                    WHERE s.ID_utente = :id_utente
                )
            ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':id_utente', $id_utente);

        if ($evento_id)
            $Stmt->bindValue(':evento_id', $evento_id);

        $Stmt->execute();

        return $Stmt->fetchAll();
    }

    public function impostaOpzioneAcquistoEventoUtenteComePagata($id_opzione_acquisto_evento, $IDPagamento)
    {
        $db = static::getDB();
        $sql = "
            UPDATE acquisti_opzioni_acquisto_eventi
            SET pagato = 1,
                IDPagamento = :IDPagamento
            WHERE ID = :id_opzione_acquisto_evento";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id_opzione_acquisto_evento', $id_opzione_acquisto_evento);
        $stmt->bindValue(':IDPagamento', $IDPagamento);
        $stmt->execute();
    }
}
