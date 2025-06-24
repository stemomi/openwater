<?php

namespace App\Models;

use PDO;
use DateTime;

class AttributiProdottiModel extends \Core\Model
{
    public static function getAttributoProdotti($attributo_prodotti_id)
    {
        $db = static::getDB();

        $sql = "
            SELECT *
            FROM prodotti_attributi
            WHERE ID = :attributo_prodotti_id
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':attributo_prodotti_id', $attributo_prodotti_id);

        $stmt->execute();

        return $stmt->fetch();
    }

    public static function getOpzioniAttributoProdotti($attributo_prodotti_id)
    {
        $db = static::getDB();

        $sql = "
            SELECT *
            FROM prodotti_attributi_opzioni
            WHERE IDAttributo = :attributo_prodotti_id
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':attributo_prodotti_id', $attributo_prodotti_id);

        $stmt->execute();

        return $stmt->fetchAll();
    }
    
    public static function getAllAttributiProdotti()
    {
        $db = static::getDB();

        $sql = "
            SELECT *
            FROM prodotti_attributi
            ORDER BY nome
        ";

        $stmt = $db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getAllAttributiProdotto($prodotto_id)
    {
        $db = static::getDB();

        $sql = "
            SELECT
            pa.*
            FROM prodotti_attributi_opzioni_disponibili as paod
            INNER JOIN prodotti_attributi_opzioni as pao ON paod.IDAttributoOpzione = pao.ID
            INNER JOIN prodotti_attributi as pa ON pao.IDAttributo = pa.ID
            WHERE paod.IDProdotto = :prodotto_id
            GROUP BY pa.ID
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':prodotto_id', $prodotto_id);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getAllOpzioniAttributoProdotto($prodotto_id, $attributo_id)
    {
        $db = static::getDB();

        $sql = "
            SELECT
            paod.*,
            pao.nome as attributo_opzione_nome
            FROM prodotti_attributi_opzioni_disponibili as paod
            LEFT JOIN prodotti_attributi_opzioni as pao ON paod.IDAttributoOpzione = pao.ID
            WHERE paod.IDProdotto = :prodotto_id
            AND pao.IDAttributo = :attributo_id
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':prodotto_id', $prodotto_id);
        $stmt->bindValue(':attributo_id', $attributo_id);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getAllOpzioniAttributiProdotto($prodotto_id)
    {
        $db = static::getDB();

        $sql = "
            SELECT
            paod.*,
            pao.nome as attributo_opzione_nome
            FROM prodotti_attributi_opzioni_disponibili as paod
            LEFT JOIN prodotti_attributi_opzioni as pao ON paod.IDAttributoOpzione = pao.ID
            WHERE paod.IDProdotto = :prodotto_id
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':prodotto_id', $prodotto_id);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function deleteOpzioniAttributoProdotto($prodotto_id)
    {
        $db = static::getDB();

        $sql = "
            DELETE
            FROM prodotti_attributi_opzioni_disponibili
            WHERE IDProdotto = :prodotto_id
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':prodotto_id', $prodotto_id);

        $stmt->execute();
    }

    public function insertOpzioneAttributoProdotto($prodotto_id, $opzione_attributo_id, $disponibile)
    {
        $db = static::getDB();
        $sql = "
            INSERT INTO prodotti_attributi_opzioni_disponibili
            (
                IDProdotto,
                IDAttributoOpzione,
                disponibile
            )
            VALUES
            (
                :prodotto_id,
                :opzione_attributo_id,
                :disponibile
            )
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':prodotto_id', $prodotto_id);
        $stmt->bindValue(':opzione_attributo_id', $opzione_attributo_id);
        $stmt->bindValue(':disponibile', $disponibile, PDO::PARAM_BOOL);

        $stmt->execute();

        return $db->lastInsertId();
    }

    public function insertAcquistoProdottoAttributo($IDProdottoAcquisto, $IDAttributoOpzione)
    {
        $db = static::getDB();
        $sql = "
            INSERT INTO prodotti_acquisto_attributi
            (
                IDProdottoAcquisto,
                IDAttributoOpzione
            )
            VALUES
            (
                :IDProdottoAcquisto,
                :IDAttributoOpzione
            )
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':IDProdottoAcquisto', $IDProdottoAcquisto);
        $stmt->bindValue(':IDAttributoOpzione', $IDAttributoOpzione);

        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function getAllAttributiProdottoAcquisto($IDProdottoAcquisto)
    {
        $db = static::getDB();

        $sql = "
            SELECT
                pa.ID as attributo_ID,
                pa.nome as attributo_nome,
                pao.ID as attributo_opzione_ID,
                pao.nome as attributo_opzione_nome
            FROM prodotti_acquisto_attributi as paa
            LEFT JOIN prodotti_attributi_opzioni as pao ON pao.ID = paa.IDAttributoOpzione
            LEFT JOIN prodotti_attributi as pa ON pa.ID = pao.IDAttributo
            WHERE paa.IDProdottoAcquisto = :IDProdottoAcquisto
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':IDProdottoAcquisto', $IDProdottoAcquisto);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    // DA QUI FUNZIONI
    public function getAllAttributiConOpzioni()
    {
        $AttributiProdotti = $this->getAllAttributiProdotti();

        // Inietta opzioni attributi
        foreach ($AttributiProdotti as &$attributo)
            $attributo->opzioni = $this->getOpzioniAttributoProdotti($attributo->ID);

        return $AttributiProdotti;
    }

    public function aggiornaAttributiOpzioniProdotto($prodotto_id, $AttributiOpzioni_ID, $AttributiOpzioni_Valore)
    {
        // Elimina opzioni attributi prodotto
        $this->deleteOpzioniAttributoProdotto($prodotto_id);

        // Aggiorna attributi opzioni
        foreach ($AttributiOpzioni_ID as $key => $attributo_id)
        {
            if ($AttributiOpzioni_Valore[$key] == '0') // Non disponibile
                $this->insertOpzioneAttributoProdotto($prodotto_id, $attributo_id, false);
            elseif ($AttributiOpzioni_Valore[$key] == '1') // Disponibile
                $this->insertOpzioneAttributoProdotto($prodotto_id, $attributo_id, true);
        }
    }
}
