<?php

namespace App\Models;

use PDO;
use DateTime;
class EmailModel extends \Core\Model
{
    public static function getUtentiConFiltri($tipo_utente, $tipo_iscrizioni, $evento_id, $gara_id)
    {
        $WhereOrAnd= 'WHERE';

        $db = static::getDB();
        $sql = "SELECT DISTINCT u.email,
                                u.nome,
                                u.cognome
                FROM utenti as u
                INNER JOIN iscrizioni as i ON i.ID_utente = u.ID ";

        if ($tipo_iscrizioni == 1)
            $sql .= " AND pagato = 0 ";

        $sql .= "
                INNER JOIN gare as g ON g.ID = i.ID_gara
                LEFT JOIN gare_collegate as gc ON gc.ID_gara_primaria = g.ID ";

        // Filtro per utente italiano o straniero --- 0= Tutti  1= Italiani 2= Stranieri
        if ($tipo_utente == 1)
        {
            $sql .= " " . $WhereOrAnd . " u.codice_fiscale <> '' ";
            $WhereOrAnd= 'AND';
        }
        elseif ($tipo_utente == 2)
        {
            $sql .= " " . $WhereOrAnd . " u.paese_estero <> '' ";
            $WhereOrAnd= 'AND';
        }

        // Filtro per tutte le gare di 1 evento
        if ($evento_id != 0 && $gara_id == 0)
        {
            $sql .= " " . $WhereOrAnd . " g.ID_evento = :evento_id ";
            $WhereOrAnd= 'AND';
        }
        elseif ($gara_id != 0) // Filtro per gara specifica
        {
            $sql .= " " . $WhereOrAnd . " i.ID_gara = :gara_id
                    OR gc.ID_gara_collegata = :gara_id ";
            $WhereOrAnd= 'AND';
        }

        $sql .= " ORDER BY u.nome, u.cognome";

        $stmt = $db->prepare($sql);

        // Variabili per tutte le gare di 1 evento
        if ($evento_id != 0 && $gara_id == 0)
            $stmt->bindValue(':evento_id', $evento_id);
        elseif ($gara_id != 0) // Variabili per gara specifica
            $stmt->bindValue(':gara_id', $gara_id);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getTemplateEmail($template_id)
    {
        $db = static::getDB();

        $sql = '
        SELECT *
        FROM email_templates
        WHERE ID = :template_id';

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':template_id', $template_id);
        $stmt->execute();

        return $stmt->fetch();
    }

    public static function AggiornaTemplateEmail(
        $template_id,
        $nome,
        $oggetto,
        $messaggio
    )
    {
        $db = static::getDB();

        $sql = '
        UPDATE email_templates
        SET
            nome = :nome,
            oggetto = :oggetto,
            messaggio = :messaggio
        WHERE ID = :template_id';
        
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':template_id', $template_id);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':oggetto', $oggetto);
        $stmt->bindValue(':messaggio', $messaggio);
        $stmt->execute();
    }
}
