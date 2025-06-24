<?php

namespace App\Models;

use PDO;
use DateTime;

use \App\Models\GareModel;

class ShareModel extends \Core\Model
{
    public static function getCondivisioneRisultatoGara($IDRisultato)
    {
        $db = static::getDB();

        $sql = '
        SELECT *
        FROM risultati_gare_condividi
        WHERE IDRisultato = :IDRisultato
        LIMIT 1';

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':IDRisultato', $IDRisultato);
        $stmt->execute();

        return $stmt->fetch();
    }

    public static function deleteCondivisioneRisultatoGara($ID)
    {
        $db = static::getDB();

        $sql = '
        DELETE 
        FROM risultati_gare_condividi
        WHERE ID = :ID';

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':ID', $ID);
        $stmt->execute();
    }

    public static function getCondivisioneRisultatoGaraByToken($token)
    {
        $db = static::getDB();

        $sql = '
        SELECT *
        FROM risultati_gare_condividi
        WHERE token = :token
        LIMIT 1';

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->execute();

        return $stmt->fetch();
    }

    public static function InserisciCondivisioneRisultatoGara(
        $IDRisultato,
        $token,
        $immagine
    )
    {
        $db = static::getDB();

        $sql = '
        INSERT INTO risultati_gare_condividi
        (
            IDRisultato,
            token,
            immagine
        )
        VALUES
        (
            :IDRisultato,
            :token,
            :immagine
        )';
        
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':IDRisultato', $IDRisultato);
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':immagine', $immagine);
        $stmt->execute();

        return $db->lastInsertId();
    }

    // DA QUI FUNZIONI

    public function generaImmagineCondivisioneRisultatoGara($IDRisultato, $link_immagine)
    {
        $GareClass = new GareModel();
        
        // Estrae risultato
        $Risultato = $GareClass->getRisultatoGara($IDRisultato);
        $TotalePartecipanti = $GareClass->getTotalePartecipantiGara($Risultato->ID_gara);

        // Controlla foto evento
        if ( !($Risultato->A_evento_foto != '' && file_exists(PATH_EVENTI_FOTO . $Risultato->A_evento_foto)) )
            return false;

        // Immagine profilo
        $LinkFotoProfiloUtente = PUBLIC_PATH . $Risultato->A_utenti_foto_profilo;

        if ($Risultato->A_utenti_foto_profilo != '' && file_exists($LinkFotoProfiloUtente))
        {
            $ImmagineProfilo = 
                strtolower(pathinfo($LinkFotoProfiloUtente, PATHINFO_EXTENSION)) == 'png' ? 
                imagecreatefrompng($LinkFotoProfiloUtente) :
                imagecreatefromjpeg($LinkFotoProfiloUtente);
                
            list($width, $height) = getimagesize($LinkFotoProfiloUtente); 
        }

        if ($Risultato && $TotalePartecipanti)
        {
            // GENERA IMMAGINE

            // Carica immagine di base
            $image = imagecreatefromjpeg(PATH_EVENTI_FOTO . $Risultato->A_evento_foto);

            // Aggiunge immagine profilo
            if ($Risultato->A_utenti_foto_profilo != '' && file_exists($LinkFotoProfiloUtente))
                imagecopyresampled($image, $ImmagineProfilo, 800, 50, 0, 0, 200, 200, $width, $height);

            // Font
            $font_path = PATH_FONT . 'MateSC-Regular.ttf';

            // Colore Testo
            $text_color = imagecolorallocate($image, 255, 255, 255);

            // Nome partecipante
            $NomePartecipante = $Risultato->cognome . ' ' . $Risultato->nome;
            $text_color = imagecolorallocate($image, 230, 190, 100);
            $text = ucwords( strtolower($NomePartecipante) );
            $text_size = 60 - (strlen($NomePartecipante) * 1.1);

            // Aggiunge testo
            imagettftext(
                $image,
                $text_size, // size
                0, // angle
                650, // x
                320, // y
                $text_color, // color
                $font_path, // font
                $text
            );

            // Classificato
            $text_color = imagecolorallocate($image, 255, 255, 255);
            $text = "Classificato " . $Risultato->posizione . ' su ' . $TotalePartecipanti;

            // Aggiunge testo
            imagettftext(
                $image,
                32, // size
                0, // angle
                650, // x
                430, // y
                $text_color, // color
                $font_path, // font
                $text
            );

            // Race time
            $text = "Race Time: " . $Risultato->racetime;

            // Aggiunge testo
            imagettftext(
                $image,
                32, // size
                0, // angle
                650, // x
                500, // y
                $text_color, // color
                $font_path, // font
                $text
            );

            // Salva immagine
            imagejpeg($image, PATH_SHARE_RACE_RESULTS . '/' . $link_immagine);

            // Pulisce dalla memoria
            imagedestroy($image);

            return true;
        }
        else
            return false;
    }
}
