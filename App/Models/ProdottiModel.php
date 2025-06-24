<?php

namespace App\Models;

use \App\Models\FunctionsModel;
use \App\Models\AttributiProdottiModel;

use PDO;
use DateTime;


class ProdottiModel extends \Core\Model
{
    public static function getProdotto($prodotto_id)
    {
        $db = static::getDB();

        $sql = "
            SELECT *
            FROM prodotti
            WHERE ID = :prodotto_id
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':prodotto_id', $prodotto_id);

        $stmt->execute();

        return $stmt->fetch();
    }
    
    public static function getAllProdotti()
    {
        $db = static::getDB();

        $sql = "
            SELECT *
            FROM prodotti
            ORDER BY nome
        ";

        $stmt = $db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getProdottiAmount()
    {
        $db = static::getDB();

        $sql = "
            SELECT COUNT(*)
            FROM prodotti
        ";

        $stmt = $db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function getProdottiAmountFiltered($search)
    {
        $db = static::getDB();
        $sql = "
            SELECT COUNT(*)
            FROM prodotti as p
            LEFT JOIN prodotti_tipologia as pt ON pt.ID = p.IDTipologia
            WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);

            foreach ($srcs as $src)
            {
                $sql.= "
                AND
                (
                    p.ID LIKE '%" . $src . "%' OR
                    p.nome LIKE '%" . $src . "%' OR
                    p.prezzo LIKE '%" . $src . "%' OR
                    p.prezzo_listino LIKE '%" . $src . "%' OR
                    pt.nome LIKE '%" . $src . "%'
                ) ";
            }
        }

        $stmt = $db->prepare($sql);

        $stmt->execute();
       
        return $stmt->fetchColumn();
    }

    public function getProdottiPagination($order,$start,$length,$search)
    {
        $db = static::getDB();
        $sql = "
            SELECT
                p.*,
                pt.nome as tipologia_nome
            FROM prodotti as p
            LEFT JOIN prodotti_tipologia as pt ON pt.ID = p.IDTipologia
            WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);

            foreach ($srcs as $src)
            {
                $sql.= "
                AND
                (
                    p.ID LIKE '%" . $src . "%' OR
                    p.nome LIKE '%" . $src . "%' OR
                    p.prezzo LIKE '%" . $src . "%' OR
                    p.prezzo_listino LIKE '%" . $src . "%' OR
                    pt.nome LIKE '%" . $src . "%'
                ) ";
            }
        }

        $sql.= " ORDER BY ". $order . " LIMIT " . $start . ", " . $length;

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function AggiornaProdotto(
        $prodotto_id,
        $nome,
        $prezzo,
        $prezzo_listino,
        $IDTipologia
    )
    {
    	$db = static::getDB();
    	$sql = "
            UPDATE prodotti
			SET
                nome = :nome,
                prezzo = :prezzo,
                prezzo_listino = :prezzo_listino,
                IDTipologia = :IDTipologia
			WHERE ID = :prodotto_id";

    	$stmt = $db->prepare($sql);

    	$stmt->bindValue(':prodotto_id', $prodotto_id);
    	$stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':prezzo', $prezzo);
        $stmt->bindValue(':prezzo_listino', $prezzo_listino);
        $stmt->bindValue(':IDTipologia', $IDTipologia);

    	$stmt->execute();
    }   

    public static function AggiornaFotoProdotto($prodotto_id, $foto)
    {
        $db = static::getDB();

        $sql = "
            UPDATE prodotti
            SET foto = :foto
            WHERE ID = :prodotto_id
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':foto', $foto);
        $stmt->bindValue(':prodotto_id', $prodotto_id);

        $stmt->execute();
    }

    public function CreaProdotto(
        $nome,
        $prezzo,
        $prezzo_listino,
        $IDTipologia
    )
    {
        $db = static::getDB();
        $sql = "
            INSERT INTO prodotti
            (
                nome,
                prezzo,
                prezzo_listino,
                IDTipologia
            )
            VALUES
            (
                :nome,
                :prezzo,
                :prezzo_listino,
                :IDTipologia
            )
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':prezzo', $prezzo);
        $stmt->bindValue(':prezzo_listino', $prezzo_listino);
        $stmt->bindValue(':IDTipologia', $IDTipologia);

        $stmt->execute();

        return $db->lastInsertId();
    }

    public function inserisciProdottoAcquistato
    (
        $IDProdotto,
        $data_acquisto,
        $data_pagamento,
        $quantita,
        $prezzo,
        $prezzo_effettivo,
        $evento_ritiro_id = null,
        $acquisto_magliette_id = null
    )
    {
        $db = static::getDB();

        $prezzo_effettivo = round($prezzo_effettivo * $quantita, 2);

        $sql = "
            INSERT INTO prodotti_acquisto
            (
                IDProdotto,
                IDUtente,
                data_acquisto,
                data_pagamento,
                prezzo,
                prezzo_effettivo,
                quantita,
                evento_ritiro_id,
                acquisto_magliette_id
            )
            VALUES
            (
                :IDProdotto,
                :IDUtente,
                :data_acquisto,
                :data_pagamento,
                :prezzo,
                :prezzo_effettivo,
                :quantita,
                :evento_ritiro_id,
                :acquisto_magliette_id
            )
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':IDProdotto', $IDProdotto);
        $stmt->bindValue(':IDUtente', $_SESSION['IDUtente']);
        $stmt->bindValue(':data_acquisto', $data_acquisto);
        $stmt->bindValue(':data_pagamento', $data_pagamento);
        $stmt->bindValue(':prezzo', $prezzo);
        $stmt->bindValue(':prezzo_effettivo', $prezzo_effettivo);
        $stmt->bindValue(':quantita', $quantita);
        $stmt->bindValue(':evento_ritiro_id', $evento_ritiro_id);
        $stmt->bindValue(':acquisto_magliette_id', $acquisto_magliette_id);

        $stmt->execute();

        return $db->lastInsertId();
    }
    
    public static function getAllProdottiDaPagareUtente()
    {
        $db = static::getDB();

        $sql = "
            SELECT
                pa.*,
                p.nome as prodotto_nome,
                p.foto as prodotto_foto
            FROM prodotti_acquisto as pa
            LEFT JOIN prodotti as p ON p.ID = pa.IDProdotto
            WHERE pa.IDUtente = :IDUtente
            AND pa.data_pagamento IS NULL
            ORDER BY pa.IDProdotto
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':IDUtente', $_SESSION['IDUtente']);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function rimuoviProdottiAcquistatiUtente()
    {
        $db = static::getDB();

        $sql = "
            DELETE pa,paa
            FROM prodotti_acquisto as pa
            LEFT JOIN prodotti_acquisto_attributi as paa ON paa.IDProdottoAcquisto = pa.ID
            WHERE pa.IDUtente = :IDUtente
        ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':IDUtente', $_SESSION['IDUtente']);

        $stmt->execute();
    }

    public function getProdottiAcquistatiAmount()
    {
        $db = static::getDB();

        $sql = "
            SELECT COUNT(*)
            FROM prodotti_acquisto as pa
            JOIN utenti as u ON u.ID = pa.IDUtente
            JOIN prodotti as p ON p.ID = pa.IDProdotto
            JOIN prodotti_acquisto_attributi as paa ON paa.IDProdottoAcquisto = pa.ID
            JOIN prodotti_attributi_opzioni as pao ON pao.ID = paa.IDAttributoOpzione
            JOIN acquisti_magliette as am ON am.ID = pa.acquisto_magliette_id
            JOIN eventi as e on e.ID = pa.evento_ritiro_id
        ";

        $stmt = $db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function getProdottiAcquistatiAmountFiltered($search)
    {
        $db = static::getDB();

        $sql = "
            SELECT COUNT(*)
            FROM prodotti_acquisto as pa
            JOIN utenti as u ON u.ID = pa.IDUtente
            JOIN prodotti as p ON p.ID = pa.IDProdotto
            JOIN prodotti_acquisto_attributi as paa ON paa.IDProdottoAcquisto = pa.ID
            JOIN prodotti_attributi_opzioni as pao ON pao.ID = paa.IDAttributoOpzione
            JOIN acquisti_magliette as am ON am.ID = pa.acquisto_magliette_id
            JOIN eventi as e on e.ID = pa.evento_ritiro_id
            WHERE 1 
        ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);

            foreach ($srcs as $src)
            {
                $sql .= "
                    AND
                    (
                        p.nome LIKE '%" . $src . "%' OR
                        e.nome LIKE '%" . $src . "%' OR
                        u.nome LIKE '%" . $src . "%' OR
                        u.cognome LIKE '%" . $src . "%' OR
                        pao.nome LIKE '%" . $src . "%'
                    ) 
                ";
            }
        }

        $stmt = $db->prepare($sql);

        $stmt->execute();
       
        return $stmt->fetchColumn();
    }

    public function getProdottiAcquistatiPagination($order,$start,$length,$search)
    {
        $db = static::getDB();

        $sql = "
            SELECT 
                pa.ID,
                u.nome as nome_utente,
                u.cognome as cognome_utente,
                pa.consegnato as consegnato,
                p.nome as nome_prodotto,
                pao.nome as taglia,
                e.nome as nome_evento,
                am.dataora
            FROM prodotti_acquisto as pa
            JOIN utenti as u ON u.ID = pa.IDUtente
            JOIN prodotti as p ON p.ID = pa.IDProdotto
            JOIN prodotti_acquisto_attributi as paa ON paa.IDProdottoAcquisto = pa.ID
            JOIN prodotti_attributi_opzioni as pao ON pao.ID = paa.IDAttributoOpzione
            JOIN acquisti_magliette as am ON am.ID = pa.acquisto_magliette_id
            JOIN eventi as e on e.ID = pa.evento_ritiro_id
            WHERE 1  
        ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);

            foreach ($srcs as $src)
            {
                $sql .= "
                    AND
                    (
                        p.nome LIKE '%" . $src . "%' OR
                        e.nome LIKE '%" . $src . "%' OR
                        u.nome LIKE '%" . $src . "%' OR
                        u.cognome LIKE '%" . $src . "%' OR
                        pao.nome LIKE '%" . $src . "%'
                    ) 
                ";
            }
        }

        $sql .= " 
            AND am.confermato = 0 
            AND pa.consegnato = 0
        ";

        $sql .= " ORDER BY ". $order . " LIMIT " . $start . ", " . $length;

        $stmt = $db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTipologiaProdotto($prodotto_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT pt.ID
            FROM prodotti_tipologia as pt
            JOIN prodotti as p ON pt.ID = p.IDTipologia
            WHERE 1 
        ";

        $sql .= " AND p.ID = :prodotto_id ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':prodotto_id', $prodotto_id);

        $Stmt->execute();

        return $Stmt->fetchColumn();
    }

    public function getMaglietteSelezionateDaAcquistoUtente($acquisto_magliette_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT *
            FROM prodotti_acquisto
            WHERE acquisto_magliette_id = :acquisto_magliette_id
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':acquisto_magliette_id', $acquisto_magliette_id);

        $Stmt->execute();

        return $Stmt->fetchAll();
    }

    public function getProdottoAcquistato($prodotto_acquistato_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT *
            FROM prodotti_acquisto
            WHERE ID = :prodotto_acquistato_id
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':prodotto_acquistato_id', $prodotto_acquistato_id);

        $Stmt->execute();

        return $Stmt->fetch();
    }

    public function getListaProdottiAcquistatiByAcquistoMagliette($acquisto_magliette_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT *
            FROM prodotti_acquisto
            WHERE acquisto_magliette_id = :acquisto_magliette_id
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':acquisto_magliette_id', $acquisto_magliette_id);

        $Stmt->execute();

        return $Stmt->fetchAll();
    }

    // DA QUI FUNZIONI
    public function caricaFotoProdotto(
        $NomeFile,
        $NomeFileTMP,
        $prodotto_id,
        $prodotto_foto
    )
    {
        $FunctionsClass = new FunctionsModel();

        if ( $NomeFile != '')
        {
            $NomeFileTMP = $_FILES["foto"]["tmp_name"];
            $NomeFile = $_FILES["foto"]["name"];

            // Elimina file precedente
            if ( $prodotto_foto != '' && file_exists(PUBLIC_PATH . $prodotto_foto))
                unlink(PUBLIC_PATH . $prodotto_foto);

            // CONTROLLI DI SICUREZZA UPLOAD FILE
            $checkUploadFile = $FunctionsClass->checkUploadFile($NomeFile, $NomeFileTMP);

            if ( $checkUploadFile != "")
                die($checkUploadFile);

            // Controllo che sia immagine
            $check = getimagesize($NomeFileTMP);
            
            if($check[0] < 4 || $check[1] < 4)
                die("Il file caricato non è un immagine!");

            // Ricrea immagine
            $FunctionsClass->RicreaPNG($NomeFileTMP, 512, 512);

            // Crea nome file foto profilo
            $path_foto = "foto_prodotti/" . $prodotto_id . "-" . time() . "-" . bin2hex(openssl_random_pseudo_bytes(8)) . ".png";

            // Aggiorna file foto profilo
            $this->AggiornaFotoProdotto($prodotto_id, $path_foto);

            if (!move_uploaded_file($NomeFileTMP, PUBLIC_PATH . $path_foto))
                die('Errore caricamento foto prodotto!');
        }
    }

    public function getAllProdottiConAttributi()
    {
        $AttributiProdottiClass = new AttributiProdottiModel();

        $Prodotti = $this->getAllProdotti();

        // Inietta attributi prodotti
        foreach ($Prodotti as &$prodotto)
        {
            $AttributiProdotto = $AttributiProdottiClass->getAllAttributiProdotto($prodotto->ID);
            
            // Inietta opzioni attributo prodotto
            foreach ($AttributiProdotto as &$attributo_prodotto)
                $attributo_prodotto->opzioni = $AttributiProdottiClass->getAllOpzioniAttributoProdotto($prodotto->ID, $attributo_prodotto->ID);

            $prodotto->AttributiProdotto = $AttributiProdotto;
        }

        return $Prodotti;
    }

    public function getAllProdottiDaPagareUtenteConAttributi()
    {
        $AttributiProdottiClass = new AttributiProdottiModel();

        $ProdottiDaPagare = $this->getAllProdottiDaPagareUtente();

        // Inietta attributi prodotti
        foreach ($ProdottiDaPagare as &$prodotto_acquisto)
        {
            $AttributiProdotto = $AttributiProdottiClass->getAllAttributiProdottoAcquisto($prodotto_acquisto->ID);

            $prodotto_acquisto->AttributiProdotto = $AttributiProdotto;

            // Inietta foto
            $prodotto_acquisto->foto_src =
                $prodotto_acquisto->prodotto_foto != '' ?
                $prodotto_acquisto->prodotto_foto :
                URL_TO_PUBLIC_FOLDER . 'img/no_image_available.png';
        }

        return $ProdottiDaPagare;
    }

    public function impostaMagliettaConsegnata($prodotto_acquistato_id)
    {
        $Db = static::getDB();

        $sql = "
            UPDATE prodotti_acquisto
            SET consegnato = 1
            WHERE ID = :prodotto_acquistato_id
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':prodotto_acquistato_id', $prodotto_acquistato_id);

        $Stmt->execute();
    }
}
