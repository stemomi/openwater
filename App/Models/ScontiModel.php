<?php

namespace App\Models;

use PDO;
use DateTime;
use \App\Models\GareModel;

class ScontiModel extends \Core\Model
{
    public function addScontoComboGare($nome, $sconto)
    {
        $db = static::getDB();

        $sql = "
        INSERT INTO sconti_combo
        (
            nome,
            sconto
        )
        VALUES
        (
            :nome,
            :sconto
        )";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':sconto', $sconto);

        $stmt->execute();

        return $db->lastInsertId();
    }

    public function addGaraAScontoComboGare($IDSconto, $IDGara)
    {
        $db = static::getDB();

        $sql = "
        INSERT INTO sconti_combo_gare
        (
            IDSconto,
            IDGara
        )
        VALUES
        (
            :IDSconto,
            :IDGara
        )";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':IDSconto', $IDSconto);
        $stmt->bindValue(':IDGara', $IDGara);

        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function getAllScontiComboGare()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT * FROM sconti_combo');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getAllScontiComboGare_GareCollegate($IDSconto)
    {
        $db = static::getDB();

        $stmt = $db->prepare('
        SELECT
            scg.*,
            e.data as e_data
        FROM sconti_combo_gare as scg
        LEFT JOIN gare as g ON g.ID = scg.IDGara
        LEFT JOIN eventi as e ON g.ID_evento = e.ID
        WHERE scg.IDSconto = :IDSconto
        AND CURDATE() <= e.data
        ');

        $stmt->bindValue(':IDSconto', $IDSconto);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getScontiComboGareAmount()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*) FROM sconti_combo');
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getScontiComboGareAmountFiltered($search)
    {
        $db = static::getDB();
        $sql = "
        SELECT COUNT(*)
        FROM sconti_combo
        WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= "
                    AND (
                            ID LIKE '%".$src."%' OR
                            nome LIKE '%".$src."%'
                        ) ";
            }
        }

        $stmt = $db->prepare($sql);
        $stmt->execute();
       
        return $stmt->fetchColumn();
    }

    public function getScontiComboGarePagination($order,$start,$length,$search)
    {
        $db = static::getDB();
        $sql = "
        SELECT
            sc.*,
            GROUP_CONCAT( CONCAT(e.nome, ' ', g.nome) SEPARATOR '| ') as ListaGare
        FROM sconti_combo as sc
        LEFT JOIN sconti_combo_gare as scg ON scg.IDSconto = sc.ID
        LEFT JOIN gare as g ON g.ID = scg.IDGara
        LEFT JOIN eventi as e ON e.ID = g.ID_evento
        WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= "
                    AND (
                            sc.ID LIKE '%".$src."%' OR
                            sc.nome LIKE '%".$src."%'
                        ) ";
            }
        }

        $sql .= ' GROUP BY sc.ID ';

        $sql .= " ORDER BY ". $order . " LIMIT " . $start . ", " . $length;

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function addScontoComboGaraAPagamento($IDPagamento, $IDSconto)
    {
        $db = static::getDB();

        $sql = "
        INSERT INTO pagamenti_sconti_combo
        (
            IDPagamento,
            IDSconto
        )
        VALUES
        (
            :IDPagamento,
            :IDSconto
        )";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':IDPagamento', $IDPagamento);
        $stmt->bindValue(':IDSconto', $IDSconto);

        $stmt->execute();

        return $db->lastInsertId();
    }

    // DA QUI FUNZIONI GLOBALI
    public static function getUtenteScontoComboGare($utente_id)
    {
        $GareClass = new GareModel();

        $ListaSconti = [];
        $TotaleScontoComboGare = 0.00;

        $ScontiComboGare = self::getAllScontiComboGare();

        foreach ($ScontiComboGare as $sconto_combo)
        {
            $ScontoCombo_GareCollegate = self::getAllScontiComboGare_GareCollegate($sconto_combo->ID);

            $ListaGareCollegate = '';

            foreach ($ScontoCombo_GareCollegate as $key => $gara)
            {
                $ListaGareCollegate .= $gara->IDGara;

                if ($key < (count($ScontoCombo_GareCollegate) - 1) )
                    $ListaGareCollegate .= ',';
            }

            if ($ListaGareCollegate != '')
            {
                $TotaleIscrizioniUtente = $GareClass->countUtenteIscrizioniNonPagatePerListaGare($utente_id, $ListaGareCollegate);

                if ( $TotaleIscrizioniUtente == count($ScontoCombo_GareCollegate) )
                {
                    $ListaSconti[] = $sconto_combo;
                    $TotaleScontoComboGare += $sconto_combo->sconto;
                }
            }
        }

        $Risposta = (object)[];
        $Risposta->ListaSconti = $ListaSconti;
        $Risposta->Totale = number_format($TotaleScontoComboGare, 2, '.', '');

        return $Risposta;
    }

    function getUtenteGareScontoComboEffettuato($utente_id)
    {
        $GareClass = new GareModel();

        $ArrayGareScontoComboEffettuato = [];
        $ScontiComboGare = self::getAllScontiComboGare();

        foreach ($ScontiComboGare as $sconto)
        {
            $ScontoListaGare = self::getAllScontiComboGare_GareCollegate($sconto->ID);

            // Counter per ArrayGareScontoComboEffettuato
            $counter_gare_sconto_combo = 0;

            foreach ($ScontoListaGare as $gara)
            {
                $CheckGaraUtente = $GareClass->checkIscrizioneGara($utente_id, $gara->IDGara);

                if ($CheckGaraUtente)
                    $counter_gare_sconto_combo++;
            }

            // Inietta gare sconto combo in ArrayGareScontoComboEffettuato
            if (count($ScontoListaGare) == $counter_gare_sconto_combo)
            {
                foreach($ScontoListaGare as $gara)
                    $ArrayGareScontoComboEffettuato[] = $gara->IDGara;
            }
        }

        return array_unique($ArrayGareScontoComboEffettuato);
    }
}
