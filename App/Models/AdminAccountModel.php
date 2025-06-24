<?php

namespace App\Models;

use PDO;
use DateTime;

class AdminAccountModel extends \Core\Model
{
    public static function ControllaLogin($username, $password)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT * FROM admin WHERE username LIKE :username AND password = :password');
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':password', hash('sha256', $password));
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getUtentiAmount($is_minorenne = false)
    {
        $Db = static::getDB();

        $sql = "
            SELECT COUNT(*) 
            FROM utenti 
            WHERE 1 ";

        if ($is_minorenne)
            $sql .= " AND data_nascita > DATE_SUB( CURDATE(), INTERVAL 18 YEAR ) ";

        $Stmt = $Db->prepare($sql);
        $Stmt->execute();

        return $Stmt->fetchColumn();
    }

    public function getUtentiAmountFiltered($search, $is_minorenne = false)
    {
        $Db = static::getDB();
        $sql = "SELECT COUNT(*) FROM utenti
                WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= " AND (ID LIKE '%".$src."%' OR
                              nome LIKE '%".$src."%' OR
                              cognome LIKE '%".$src."%' OR
                              email LIKE '%".$src."%') ";
            }
        }

        if ($is_minorenne)
            $sql .= " AND data_nascita > DATE_SUB( CURDATE(), INTERVAL 18 YEAR ) ";

        $Stmt = $Db->prepare($sql);
        $Stmt->execute();
       
        return $Stmt->fetchColumn();
    }

    public function getUtentiPagination($order, $start, $length, $search, $is_minorenne = false)
    {
        $Db = static::getDB();
        $sql = "SELECT * FROM utenti
                WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= " AND (ID LIKE '%".$src."%' OR
                              nome LIKE '%".$src."%' OR
                              cognome LIKE '%".$src."%' OR
                              email LIKE '%".$src."%') ";
            }
        }

        if ($is_minorenne)
            $sql .= " AND data_nascita > DATE_SUB( CURDATE(), INTERVAL 18 YEAR ) ";

        $sql.= " ORDER BY ". $order . " LIMIT " . $start . ", " . $length;

        $Stmt = $Db->prepare($sql);
        $Stmt->execute();

        return $Stmt->fetchAll();
    }

    public function getAllIscrizioniPerEvento($evento_id)
    {
        $db = static::getDB();
        $sql = "SELECT *,
                       SUM(A_importo_interno) as A_importo,
                       SUM(A_prezzo_boa_interno) as A_prezzo_boe,
                       group_concat(G_ID ORDER BY G_ID separator ', ') as A_ID_gare,
                       group_concat(G_nome ORDER BY G_nome separator ', ') as A_nome_gara,
                       group_concat(DATE_FORMAT(I_dataora, '%d/%m/%Y') ORDER BY G_ID separator ', ') as A_dataora,
                       DATE_FORMAT(MAX(I_dataora), '%d/%m/%Y') as A_max_data_ora,
                       group_concat(  CASE
                                          WHEN I_pagato = 1 AND I_convalidato = 1 THEN 'Convalidato'
                                          WHEN I_pagato = 1 AND I_convalidato = 0 THEN 'Pagato'
                                          ELSE 'Iscritto'
                                      END ORDER BY G_ID separator ', '
                                    ) as A_pagato
                FROM
                (
                  SELECT i.ID as A_ID_iscrizione,
                         i.ID_Utente as A_ID_utente,
                         i.dataora as I_dataora,
                         i.pagato as I_pagato,
                         i.convalidato as I_convalidato,
                         g.ID as G_ID,
                         g.nome as G_nome,
                         u.nome as A_nome_utente,
                         u.cognome as A_cognome_utente,
                         CASE
                            WHEN u.sesso = 1 THEN 'M'
                            WHEN u.sesso = 2 THEN 'F'
                         END as A_sesso_utente,
                         u.gruppo_sportivo as A_gruppo_sportivo_utente,
                         u.boa_numero as A_boa_numero_utente,
                         u.certificato_scadenza as A_certificato_scadenza_utente,
                         u.email as A_email_utente,
                         u.telefono as A_telefono_utente,
                         CASE
                            WHEN u.donatore_avis = 'Si' THEN '1'
                            WHEN u.donatore_avis = 'No' THEN '0'
                            ELSE ''
                         END as A_donatore_avis_utente,
                         u.taglia_maglietta as A_taglia_maglietta,
                         u.data_nascita as A_data_nascita,
                         u.via as A_via,
                         u.numero_civico as A_numero_civico,
                         u.cap as A_cap,
                         u.comune as A_comune,
                         pi.nome as A_provincia,
                         u.luogo_nascita as A_luogo_nascita,
                         u.certificato_file as A_certificato_file,
                         u.codice_fiscale as A_codice_fiscale,
                         u.paese_estero as A_paese_estero,
                         sqa.nome as SQA_nome,
                         e.nome as A_nome_evento,
                         CASE
                            WHEN SUM(p.pagato_boa) > 0 THEN '1'
                            ELSE '0'
                         END as A_pagato_boa,
                         CASE
                            WHEN i.tipo_prezzo = 1 THEN SUM(g.prezzo1 * (100 - i.sconto) / 100)
                            WHEN i.tipo_prezzo = 2 THEN SUM(g.prezzo2 * (100 - i.sconto) / 100)
                            WHEN i.tipo_prezzo = 3 THEN SUM(g.prezzo3 * (100 - i.sconto) / 100)
                            ELSE SUM(g.prezzo4 * (100 - i.sconto) / 100)
                         END as A_importo_interno,
                         SUM(i.prezzo_boa) as A_prezzo_boa_interno
                  FROM iscrizioni as i
                  INNER JOIN utenti as u ON u.ID = i.ID_utente
                  INNER JOIN gare as g ON g.ID = i.ID_gara
                  INNER JOIN eventi as e ON e.ID = g.ID_evento AND e.ID = :evento_id
                  LEFT JOIN pagamenti_gare as pg ON pg.ID_utente = u.ID AND pg.ID_gara = g.ID
                  LEFT JOIN pagamenti as p ON p.ID = pg.ID_pagamento
                  LEFT JOIN squadre as sqa ON sqa.ID = u.IDSquadra
                LEFT JOIN province_italiane as pi ON pi.ID = u.IDProvincia
                  GROUP BY i.ID
                ) as interna1
              GROUP BY A_ID_utente";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':evento_id', $evento_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getPagamentoBoa($evento_id, $utente_id)
    {
        $db = static::getDB();

        $sql = "
            SELECT * FROM acquisti_opzioni_acquisto_eventi as a
            INNER JOIN eventi_opzioni_acquisto as e ON e.ID = a.IDOpzioneAcquistoEvento
            INNER JOIN opzioni_acquisto as o ON o.ID = e.IDOpzioneAcquisto
            WHERE e.IDEvento = :evento_id
            AND o.ID IN (1, 2)
            AND a.IDPagamento is not null
            AND a.IDUtente = :utente_id";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':evento_id', $evento_id);
        $stmt->bindValue(':utente_id', $utente_id);

        $stmt->execute();

        return $stmt->fetch();
    }

    public function getAllIscrizioniPerGara($gara_id)
    {
        $db = static::getDB();
        $sql = "SELECT i.ID as A_ID_iscrizione,
                       i.ID_Utente as A_ID_utente,
                       u.nome as A_nome_utente,
                       u.cognome as A_cognome_utente,
                       CASE
                          WHEN u.sesso = 1 THEN 'M'
                          WHEN u.sesso = 2 THEN 'F'
                       END as A_sesso_utente,
                       u.gruppo_sportivo as A_gruppo_sportivo_utente,
                       u.boa_numero as A_boa_numero_utente,
                       u.certificato_scadenza as A_certificato_scadenza_utente,
                       u.email as A_email_utente,
                       u.telefono as A_telefono_utente,
                       CASE
                          WHEN u.donatore_avis = 'Si' THEN '1'
                          WHEN u.donatore_avis = 'No' THEN '0'
                          ELSE ''
                       END as A_donatore_avis_utente,
                       u.taglia_maglietta as A_taglia_maglietta,
                       u.data_nascita as A_data_nascita,
                       u.via as A_via,
                       u.numero_civico as A_numero_civico,
                       u.cap as A_cap,
                       u.comune as A_comune,
                       pi.nome as A_provincia,
                       u.luogo_nascita as A_luogo_nascita,
                       u.certificato_file as A_certificato_file,
                       sqa.nome as SQA_nome,
                       e.nome as A_nome_evento,
                       g.nome as A_nome_gara,
                       DATE_FORMAT(i.dataora, '%d/%m/%Y') as A_dataora,
                       CASE
                          WHEN i.pagato = 1 AND i.convalidato = 1 THEN 'Convalidato'
                          WHEN i.pagato = 1 AND i.convalidato = 0 THEN 'Pagato'
                          ELSE 'Iscritto'
                        END as A_pagato,
                       CASE
                          WHEN p.pagato_boa = 1 THEN '1'
                          ELSE '0'
                       END as A_pagato_boa,
                       CASE
                          WHEN i.tipo_prezzo = 1 THEN g.prezzo1 * (100 - i.sconto) / 100
                          WHEN i.tipo_prezzo = 2 THEN g.prezzo2 * (100 - i.sconto) / 100
                          WHEN i.tipo_prezzo = 3 THEN g.prezzo3 * (100 - i.sconto) / 100
                          ELSE g.prezzo4 * (100 - i.sconto) / 100
                       END as A_importo
                FROM iscrizioni as i
                INNER JOIN utenti as u ON u.ID = i.ID_utente
                INNER JOIN gare as g ON g.ID = i.ID_gara
                LEFT JOIN gare_collegate as gc ON gc.ID_gara_primaria = i.ID_gara
                INNER JOIN eventi as e ON e.ID = g.ID_evento
                LEFT JOIN pagamenti_gare as pg ON pg.ID_utente = u.ID AND pg.ID_gara = g.ID
                LEFT JOIN pagamenti as p ON p.ID = pg.ID_pagamento
                LEFT JOIN squadre as sqa ON sqa.ID = u.IDSquadra
                LEFT JOIN province_italiane as pi ON pi.ID = u.IDProvincia
                WHERE g.ID = :gara_id OR gc.ID_gara_collegata = :gara_id";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':gara_id', $gara_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getStaffettaUtenteDaEvento($id_utente, $id_evento)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT s.nome as A_nome_staffetta
                              FROM utenti as u
                              INNER JOIN partecipanti_staffette as ps ON ps.ID_partecipante = u.ID
                              INNER JOIN staffette as s ON s.ID = ps.ID_staffetta
                              INNER JOIN gare as g ON g.ID = s.ID_gara
                              INNER JOIN eventi as e ON e.ID = g.ID_evento
                              WHERE e.ID = :id_evento
                              AND u.ID = :id_utente');
        $stmt->bindValue(':id_utente', $id_utente);
        $stmt->bindValue(':id_evento', $id_evento);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getIscrizioniAmount($tipo, $da_pagare = false)
    {
        $db = static::getDB();

        $sql= '
            SELECT COUNT(*)
            FROM iscrizioni
            WHERE 1
        ';

        if ($tipo == 1)
          $sql .= ' AND YEAR(dataora) = YEAR(CURDATE()) ';

        if ($da_pagare == 1)
            $sql .= ' AND pagato = 0'; 

        $stmt = $db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function getIscrizioniAmountFiltered($search, $tipo, $da_pagare = false)
    {
        $db = static::getDB();
        $sql = "
            SELECT *, COUNT(DISTINCT interna1.A_I_ID) as A_COUNT
            FROM
                (
                    SELECT
                        CASE
                            WHEN i.pagato = 0 THEN 'Da Pagare'
                            WHEN i.pagato = 1 AND i.convalidato = 0 THEN 'Pagato'
                            WHEN i.convalidato = 1 THEN 'Convalidato'
                            ELSE ''
                        END as A_stato_pagamento,
                        CASE
                            WHEN p.tipo = 1 THEN 'Bonifico'
                            WHEN p.tipo = 2 THEN 'Paypal'
                            WHEN p.tipo = 3 THEN 'Coupon'
                            WHEN p.tipo = 4 THEN 'Credito'
                            ELSE ''
                        END as A_tipo_pagamento,
                        i.ID_Utente as A_ID_utente,
                        u.nome as A_nome_utente,
                        u.cognome as A_cognome_utente,
                        e.nome as A_nome_evento,
                        g.nome as A_nome_gara,
                        i.dataora as A_dataora,
                        i.ID as A_I_ID
                    FROM iscrizioni as i
                    LEFT JOIN utenti as u ON u.ID = i.ID_utente
                    LEFT JOIN gare as g ON g.ID = i.ID_gara
                    LEFT JOIN eventi as e ON e.ID = g.ID_evento
                    LEFT JOIN pagamenti_gare as pg ON i.ID_gara = pg.ID_gara AND i.ID_utente = pg.ID_utente
                    LEFT JOIN pagamenti as p ON p.ID = pg.ID_pagamento AND p.ID_utente = pg.ID_utente";

                    if ($da_pagare == 1)
                        $sql .= " WHERE i.pagato = 0 ";

            $sql .= "
                ) as interna1
            WHERE 1";

            if ($search != '')
            {
                $srcs = explode(' ', $search);
                foreach ($srcs as $src)
                {
                    $sql.= " AND (A_ID_utente LIKE '%".$src."%' OR
                                  A_nome_utente LIKE '%".$src."%' OR
                                  A_cognome_utente LIKE '%".$src."%' OR
                                  A_nome_gara LIKE '%".$src."%' OR
                                  A_nome_evento LIKE '%".$src."%' OR
                                  A_stato_pagamento LIKE '%".$src."%' OR
                                  A_tipo_pagamento LIKE '%".$src."%' ";

                    // Matcha data solo se numero + trattino per evitare di includere tutto ricercando per ID
                    if (preg_match('/(\d+)-|-(\d+)/', $src)) $sql .= " OR DATE_FORMAT(A_dataora, '%Y-%m-%d') LIKE '%".$src."%' ";

                    $sql.= ")";
                }
            }

        if ($tipo == 1)
          $sql .= ' AND YEAR(A_dataora) = YEAR(CURDATE()) ';

        $stmt = $db->prepare($sql);
        $stmt->execute();
       
        return $stmt->fetch()->A_COUNT;
    }

    public function getIscrizioniPagination
    (
        $order, 
        $start, 
        $length, 
        $search, 
        $tipo, 
        $da_pagare = false
    )
    {
        $Db = static::getDB();

        $sql = "
            SELECT *
            FROM
                (
                    SELECT
                        i.ID as A_ID_iscrizione,
                        i.ID_Utente as A_ID_utente,
                        u.nome as A_nome_utente,
                        s.nome as nome_squadra,
                        u.cognome as A_cognome_utente,
                        u.data_nascita as A_data_nascita_utente,
                        u.autocertificazione_minori_18_anni_file as A_autocertificazione_minori_18_anni_file_utente,
                        e.nome as A_nome_evento,
                        g.nome as A_nome_gara,
                        i.dataora as A_dataora,
                        i.pagato as A_pagato,
                        i.convalidato as A_convalidato,
                        CASE
                            WHEN i.tipo_prezzo = 1 THEN g.prezzo1 * (100 - g.sconto_prezzo1) / 100
                            WHEN i.tipo_prezzo = 2 THEN g.prezzo2 * (100 - g.sconto_prezzo2) / 100
                            WHEN i.tipo_prezzo = 3 THEN g.prezzo3 * (100 - g.sconto_prezzo3) / 100
                            ELSE g.prezzo4 * (100 - g.sconto_prezzo4) / 100
                        END as A_importo,
                        CASE
                            WHEN p.tipo = 1 THEN 'Bonifico'
                            WHEN p.tipo = 2 THEN 'Paypal'
                            WHEN p.tipo = 3 THEN 'Coupon'
                            WHEN p.tipo = 4 THEN 'Credito'
                            ELSE ''
                        END as A_tipo_pagamento,
                        CASE
                            WHEN i.pagato = 0 THEN 'Da Pagare'
                            WHEN i.pagato = 1 AND i.convalidato = 0 THEN 'Pagato'
                            WHEN i.convalidato = 1 THEN 'Convalidato'
                            ELSE ''
                        END as A_stato_pagamento
                    FROM iscrizioni as i
                    LEFT JOIN utenti as u ON u.ID = i.ID_utente
                    LEFT JOIN squadre as s ON s.ID = u.IDSquadra
                    LEFT JOIN gare as g ON g.ID = i.ID_gara
                    LEFT JOIN eventi as e ON e.ID = g.ID_evento
                    LEFT JOIN pagamenti_gare as pg ON i.ID_gara = pg.ID_gara AND i.ID_utente = pg.ID_utente
                    LEFT JOIN pagamenti as p ON p.ID = pg.ID_pagamento AND p.ID_utente = pg.ID_utente
        ";

        if ($da_pagare == 1)
            $sql .= " WHERE i.pagato = 0 ";

        $sql .= "
                ) as interna1
            WHERE 1
        ";

        if ($search != '')
        {
            $Srcs = explode(' ', $search);

            foreach ($Srcs as $src)
            {
                $sql .= " 
                    AND 
                    (
                        A_ID_utente LIKE '%" . $src . "%' OR
                        A_nome_utente LIKE '%" . $src . "%' OR
                        A_cognome_utente LIKE '%" . $src . "%' OR
                        A_nome_gara LIKE '%" . $src . "%' OR
                        A_nome_evento LIKE '%" . $src . "%' OR
                        A_stato_pagamento LIKE '%" . $src . "%' OR
                        A_tipo_pagamento LIKE '%" . $src . "%' OR
                        nome_squadra LIKE '%" . $src . "%' ";

                // Matcha data solo se numero + trattino per evitare di includere tutto ricercando per ID
                if (preg_match('/(\d+)-|-(\d+)/', $src)) $sql .= " OR DATE_FORMAT(A_dataora, '%Y-%m-%d') LIKE '%" . $src . "%' ";

                $sql .= "
                    )
                ";
            }
        }

        if ($tipo == 1)
          $sql .= ' AND YEAR(A_dataora) = YEAR(CURDATE()) ';

        $sql .= "  ORDER BY " . $order . " LIMIT " . $start . ", " . $length;

        $Stmt = $Db->prepare($sql);

        $Stmt->execute();

        return $Stmt->fetchAll();
    }

    public function getIscrizione($id_iscrizione)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT * FROM iscrizioni WHERE ID = :id_iscrizione');
        $stmt->bindValue(':id_iscrizione', $id_iscrizione);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getIscrizioneDettagli($id_iscrizione)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT i.ID as A_ID_iscrizione,
                                     i.ID_Utente as A_ID_utente,
                                     u.nome as A_nome_utente,
                                     u.cognome as A_cognome_utente,
                                     e.nome as A_nome_evento,
                                     g.nome as A_nome_gara,
                                     i.dataora as A_dataora,
                                     i.pagato as A_pagato,
                                     i.convalidato as A_convalidato,
                                     p.ID as A_IDPagamento,
                                     p.file as A_file_bonifico,
                                     p.tipo as A_tipo_pagamento,
                                     p.ID_transazione_paypal as A_ID_transazione_paypal,
                                     p.codice_coupon as A_codice_coupon,
                                     i.ID_gara as gara_id,
                                     CASE
                                        WHEN i.tipo_prezzo = 1 THEN g.prezzo1 * (100 - g.sconto_prezzo1) / 100
                                        WHEN i.tipo_prezzo = 2 THEN g.prezzo2 * (100 - g.sconto_prezzo2) / 100
                                        WHEN i.tipo_prezzo = 3 THEN g.prezzo3 * (100 - g.sconto_prezzo3) / 100
                                        ELSE g.prezzo4 * (100 - g.sconto_prezzo4) / 100
                                     END as A_importo
                              FROM iscrizioni as i
                              LEFT JOIN utenti as u ON u.ID = i.ID_utente
                              LEFT JOIN gare as g ON g.ID = i.ID_gara
                              LEFT JOIN eventi as e ON e.ID = g.ID_evento
                              LEFT JOIN pagamenti_gare as pg ON i.ID_gara = pg.ID_gara AND i.ID_utente = pg.ID_utente
                              LEFT JOIN pagamenti as p ON p.ID = pg.ID_pagamento AND p.ID_utente = pg.ID_utente
                              WHERE i.ID = :id_iscrizione');
        $stmt->bindValue(':id_iscrizione', $id_iscrizione);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getPagamentoDettagli($id_pagamento)
    {
        $db = static::getDB();

        $stmt = $db->prepare("
            SELECT
                p.*,
                GROUP_CONCAT(DISTINCT e.nome, ' ', g.nome separator '|') as lista_gare,
                GROUP_CONCAT(DISTINCT sc.nome, ': - € ', sc.sconto separator '|') as lista_sconti,
                ac.quantita as magliette_quantita,
                ac.importo_totale as magliette_importo_totale,
                aoae.prezzo as prezzo_opzione_acquisto
            FROM pagamenti as p
            LEFT JOIN pagamenti_gare as pg ON pg.ID_pagamento = p.ID
            LEFT JOIN gare as g ON g.ID = pg.ID_gara
            LEFT JOIN eventi as e ON e.ID = g.ID_evento
            LEFT JOIN pagamenti_sconti_combo AS psc ON psc.IDPagamento = p.ID
            LEFT JOIN sconti_combo AS sc ON sc.ID = psc.IDSconto
            LEFT JOIN acquisti_magliette AS ac ON ac.IDPagamento = p.ID
            LEFT JOIN acquisti_opzioni_acquisto_eventi as aoae ON aoae.IDPagamento = p.ID
            WHERE p.ID = :id_pagamento
            GROUP BY p.ID");

        $stmt->bindValue(':id_pagamento', $id_pagamento);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function checkPagamentoPrezzoBoe($id_pagamento)
    {
        $db = static::getDB();

        $stmt = $db->prepare("
            SELECT SUM(i.prezzo_boa)
            FROM pagamenti as p
            LEFT JOIN pagamenti_gare as pg ON pg.ID_pagamento = p.ID
            LEFT JOIN gare as g ON g.ID = pg.ID_gara
            LEFT JOIN iscrizioni as i ON i.ID_gara = g.ID AND p.ID_utente = i.ID_utente
            WHERE p.ID = :id_pagamento");

        $stmt->bindValue(':id_pagamento', $id_pagamento);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function checkPagamentoScontoStaffette($id_pagamento, $utente_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT SUM(i.sconto_staffetta)
            FROM pagamenti as p
            LEFT JOIN pagamenti_gare as pg ON pg.ID_pagamento = p.ID AND pg.ID_utente = :utente_id
            LEFT JOIN gare as g ON g.ID = pg.ID_gara
            LEFT JOIN iscrizioni as i ON i.ID_gara = g.ID AND i.ID_utente = :utente_id
            WHERE p.ID = :id_pagamento
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':id_pagamento', $id_pagamento);
        $Stmt->bindValue(':utente_id', $utente_id);

        $Stmt->execute();

        return $Stmt->fetchColumn();
    }

    public function cancellaIscrizione($id_iscrizione)
    {
        $db = static::getDB();
        $stmt = $db->prepare('DELETE FROM iscrizioni WHERE ID = :id_iscrizione');
        $stmt->bindValue(':id_iscrizione', $id_iscrizione);
        $stmt->execute();
    }

    public function cancellaIscrizioniDopoUnGiorno()
    {
        $db = static::getDB();

        $sql = '
                SELECT *
                FROM iscrizioni
                WHERE DATEDIFF(NOW(), dataora) > 1
                AND pagato = 0';

        $stmt = $db->prepare($sql);

        $stmt->execute();

        $lista_iscrizioni = $stmt->fetchAll();

        foreach ($lista_iscrizioni as $iscrizione)
        {
            $stmt = $db->prepare('DELETE FROM iscrizioni WHERE ID = :id_iscrizione');
            $stmt->bindValue(':id_iscrizione', $iscrizione->ID);
            $stmt->execute();   
        }
    }

    public function cancellaIscrizioneGaraIndividuale($id_iscrizione)
    {
        $db = static::getDB();
        $stmt = $db->prepare('DELETE FROM iscrizioni_gare_individuali WHERE ID = :id_iscrizione');
        $stmt->bindValue(':id_iscrizione', $id_iscrizione);
        $stmt->execute();
    }

    public function cancellaAcquistoMagliette($id_acquisto)
    {
        $db = static::getDB();
        $stmt = $db->prepare('DELETE FROM acquisti_magliette WHERE ID = :id_acquisto');
        $stmt->bindValue(':id_acquisto', $id_acquisto);
        $stmt->execute();
    }

    public function impostaIscrizioneComeIscritto($id_iscrizione)
    {
        $db = static::getDB();
        $stmt = $db->prepare('UPDATE iscrizioni SET pagato = 0, convalidato = 0 WHERE ID = :id_iscrizione');
        $stmt->bindValue(':id_iscrizione', $id_iscrizione);
        $stmt->execute();
    }

    public function impostaIscrizioneComePagato($id_iscrizione)
    {
        $db = static::getDB();
        $stmt = $db->prepare('UPDATE iscrizioni SET pagato = 1, convalidato = 0 WHERE ID = :id_iscrizione');
        $stmt->bindValue(':id_iscrizione', $id_iscrizione);
        $stmt->execute();
    }

    public function impostaIscrizioneComeConvalidato($id_iscrizione)
    {
        $db = static::getDB();
        $stmt = $db->prepare('UPDATE iscrizioni SET pagato = 1, convalidato = 1 WHERE ID = :id_iscrizione');
        $stmt->bindValue(':id_iscrizione', $id_iscrizione);
        $stmt->execute();
    }

    public function getIscrizioniGareIndividualiAmount($tipo)
    {
        $db = static::getDB();
        $sql= 'SELECT COUNT(*) FROM iscrizioni_gare_individuali ';

        if ($tipo == 1)
          $sql .= ' WHERE YEAR(dataora) = YEAR(CURDATE()) ';

        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getIscrizioniGareIndividualiAmountFiltered($tipo, $search)
    {
        $db = static::getDB();
        $sql = "SELECT COUNT(*)
                FROM iscrizioni_gare_individuali as i
                LEFT JOIN utenti as u ON u.ID = i.ID_utente
                LEFT JOIN gare_individuali as g ON g.ID = i.ID_gara
                LEFT JOIN eventi_individuali as e ON e.ID = g.ID_evento
                LEFT JOIN pagamenti_gare_individuali as pg ON i.ID = pg.ID_iscrizione AND i.ID_utente = pg.ID_utente
                LEFT JOIN pagamenti as p ON p.ID = pg.ID_pagamento AND p.ID_utente = pg.ID_utente
                WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= " AND (i.ID_utente LIKE '%".$src."%' OR
                              u.nome LIKE '%".$src."%' OR
                              u.cognome LIKE '%".$src."%' OR
                              g.nome LIKE '%".$src."%' OR
                              e.nome LIKE '%".$src."%' ";

                // Matcha data solo se numero + trattino per evitare di includere tutto ricercando per ID
                if (preg_match('/(\d+)-|-(\d+)/', $src)) $sql .= " OR DATE_FORMAT(i.dataora, '%Y-%m-%d') LIKE '%".$src."%' ";

                $sql.= ")";
            }
        }

        if ($tipo == 1)
          $sql .= ' AND YEAR(i.dataora) = YEAR(CURDATE()) ';

        $stmt = $db->prepare($sql);
        $stmt->execute();
       
        return $stmt->fetchColumn();
    }

    public function getIscrizioniGareIndividualiPagination($tipo, $order,$start,$length,$search)
    {
        $db = static::getDB();
        $sql = "SELECT i.ID as A_ID_iscrizione,
                       i.ID_Utente as A_ID_utente,
                       u.nome as A_nome_utente,
                       u.cognome as A_cognome_utente,
                       e.nome as A_nome_evento,
                       g.nome as A_nome_gara,
                       i.dataora as A_dataora,
                       i.pagato as A_pagato,
                       i.convalidato as A_convalidato,
                       g.prezzo as A_importo,
                       CASE
                          WHEN p.tipo = 1 THEN 'Bonifico'
                          WHEN p.tipo = 2 THEN 'Paypal'
                          WHEN p.tipo = 3 THEN 'Coupon'
                          WHEN p.tipo = 4 THEN 'Credito'
                          ELSE ''
                       END as A_tipo_pagamento,
                       CASE
                          WHEN i.pagato = 0 THEN 'Da Pagare'
                          WHEN i.pagato = 1 AND i.convalidato = 0 THEN 'Pagato'
                          WHEN i.convalidato = 1 THEN 'Convalidato'
                          ELSE ''
                       END as A_stato_pagamento
                FROM iscrizioni_gare_individuali as i
                LEFT JOIN utenti as u ON u.ID = i.ID_utente
                LEFT JOIN gare_individuali as g ON g.ID = i.ID_gara
                LEFT JOIN eventi_individuali as e ON e.ID = g.ID_evento
                LEFT JOIN pagamenti_gare_individuali as pg ON i.ID = pg.ID_iscrizione AND i.ID_utente = pg.ID_utente
                LEFT JOIN pagamenti as p ON p.ID = pg.ID_pagamento AND p.ID_utente = pg.ID_utente
                WHERE 1 ";

        if ($search != '')
        {
            $srcs = explode(' ', $search);
            foreach ($srcs as $src)
            {
                $sql.= " AND (i.ID_utente LIKE '%".$src."%' OR
                              u.nome LIKE '%".$src."%' OR
                              u.cognome LIKE '%".$src."%' OR
                              g.nome LIKE '%".$src."%' OR
                              e.nome LIKE '%".$src."%' ";

                // Matcha data solo se numero + trattino per evitare di includere tutto ricercando per ID
                if (preg_match('/(\d+)-|-(\d+)/', $src)) $sql .= " OR DATE_FORMAT(i.dataora, '%Y-%m-%d') LIKE '%".$src."%' ";

                $sql.= ")";
            }
        }

        if ($tipo == 1)
          $sql .= ' AND YEAR(i.dataora) = YEAR(CURDATE()) ';

        $sql.= " ORDER BY ". $order . " LIMIT " . $start . ", " . $length;

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getAcquistiMaglietteAmount($da_pagare)
    {
        $Db = static::getDB();

        $sql = "
            SELECT COUNT(*) 
            FROM acquisti_magliette 
            WHERE YEAR(dataora) = YEAR(CURDATE())
        ";

        if ($da_pagare)
            $sql .= " AND IDPagamento IS NULL ";

        $Stmt = $Db->prepare($sql);

        $Stmt->execute();

        return $Stmt->fetchColumn();
    }

    public function getAcquistiMaglietteAmountFiltered($search, $da_pagare)
    {
        $Db = static::getDB();

        $sql = "
            SELECT COUNT(DISTINCT am.ID)
            FROM acquisti_magliette as am
            LEFT JOIN pagamenti as p ON p.ID = am.IDPagamento
            LEFT JOIN pagamenti_gare as pg ON pg.ID_pagamento = p.ID
            LEFT JOIN gare as g ON g.ID = pg.ID_gara
            LEFT JOIN eventi as e ON e.ID = g.ID_evento
            LEFT JOIN utenti as u ON u.ID = pg.ID_utente
            WHERE YEAR(am.dataora) = YEAR(CURDATE())
        ";

        if ($search != '')
        {
            $Srcs = explode(' ', $search);

            foreach ($Srcs as $src)
            {
                $sql .= "
                    AND
                    (
                        u.nome LIKE '%" . $src . "%' OR
                        u.cognome LIKE '%" . $src . "%' OR
                        u.email LIKE '%" . $src . "%' OR
                        g.nome LIKE '%" . $src . "%' OR
                        e.nome LIKE '%" . $src . "%'
                    ) 
                ";
            }
        }

        if ($da_pagare)
            $sql .= " AND am.IDPagamento IS NULL";

        $Stmt = $Db->prepare($sql);

        $Stmt->execute();
       
        return $Stmt->fetchColumn();
    }

    public function getAcquistiMagliettePagination
    (
        $order, 
        $start, 
        $length, 
        $search, 
        $da_pagare
    )
    {
        $Db = static::getDB();

        $sql = "
            SELECT
              am.ID as A_am_ID,
              am.dataora as A_am_dataora,
              am.quantita as A_am_quantita,
              am.importo_totale as A_am_importo_totale,
              u.nome as A_utente_nome,
              u.cognome as A_utente_cognome,
              u.email as A_utente_email,
              g.nome as A_gara_nome,
              e.nome as A_evento_nome,
              p.ID as A_pagamento_ID,
              GROUP_CONCAT(DISTINCT g.ID separator '|') as A_lista_gare_ID
            FROM acquisti_magliette as am
            LEFT JOIN pagamenti as p ON p.ID = am.IDPagamento
            LEFT JOIN pagamenti_gare as pg ON pg.ID_pagamento = p.ID
            LEFT JOIN gare as g ON g.ID = pg.ID_gara
            LEFT JOIN eventi as e ON e.ID = g.ID_evento
            LEFT JOIN utenti as u ON u.ID = am.IDUtente
            WHERE YEAR(am.dataora) = YEAR(CURDATE())
        ";

        if ($search != '')
        {
            $Srcs = explode(' ', $search);

            foreach ($Srcs as $src)
            {
                $sql .= "
                    AND
                    (
                        u.nome LIKE '%" . $src . "%' OR
                        u.cognome LIKE '%" . $src . "%' OR
                        u.email LIKE '%" . $src . "%' OR
                        g.nome LIKE '%" . $src . "%' OR
                        e.nome LIKE '%" . $src . "%'
                    ) 
                ";
            }
        }

        if ($da_pagare)
            $sql .= " AND am.IDPagamento IS NULL";

        $sql .= " GROUP BY am.ID ";

        $sql .= "  ORDER BY " . $order . " LIMIT " . $start . ", " . $length;

        $Stmt = $Db->prepare($sql);

        $Stmt->execute();

        return $Stmt->fetchAll();
    }

    public function getIscrizioniSquadreAmount($squadra_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT COUNT(*)
            FROM iscrizioni_di_squadra as ids
            JOIN squadre as s ON ids.squadra_id = s.ID
            JOIN utenti as u On ids.capo_squadra_id = u.ID
            WHERE 1
        ";

        if ($squadra_id)
            $sql .= " AND s.ID = :squadra_id";

        $Stmt = $Db->prepare($sql);

        if ($squadra_id)
            $Stmt->bindValue(':squadra_id', $squadra_id);

        $Stmt->execute();

        return $Stmt->fetchColumn();
    }

    public function getIscrizioniSquadreAmountFiltered($search, $squadra_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT COUNT(*)
            FROM iscrizioni_di_squadra as ids
            JOIN squadre as s ON ids.squadra_id = s.ID
            JOIN utenti as u On ids.capo_squadra_id = u.ID
            WHERE 1
        ";

        if ($search != '')
        {
            $Srcs = explode(' ', $search);
            
            foreach ($Srcs as $src)
            {
                $sql .= " 
                    AND 
                    (
                        s.nome LIKE '%" . $src . "%' OR
                        u.nome LIKE '%" . $src . "%' OR
                        u.cognome LIKE '%" . $src . "%'
                    ";

                // Matcha data solo se numero + trattino per evitare di includere tutto ricercando per ID
                if (preg_match('/(\d+)-|-(\d+)/', $src)) 
                    $sql .= " OR DATE_FORMAT(ids.data, '%Y-%m-%d') LIKE '%". $src . "%' ";

                $sql .= "
                    )
                ";
            }
        }

        if ($squadra_id)
            $sql .= " AND s.ID = :squadra_id ";

        $Stmt = $Db->prepare($sql);

        if ($squadra_id)
            $Stmt->bindValue(':squadra_id', $squadra_id);

        $Stmt->execute();
       
        return $Stmt->fetchColumn();
    }

    public function getIscrizioniSquadrePagination
    (
        $order, 
        $start, 
        $length, 
        $search,
        $squadra_id
    )
    {
        $Db = static::getDB();

        $sql = "
            SELECT 
                ids.id,
                ids.data,
                ids.pagato,
                ids.totale,
                s.nome as nome_squadra,
                u.nome,
                u.cognome
            FROM iscrizioni_di_squadra as ids
            JOIN squadre as s ON ids.squadra_id = s.ID
            JOIN utenti as u On ids.capo_squadra_id = u.ID
            WHERE 1
        ";

        if ($search != '')
        {
            $Srcs = explode(' ', $search);

            foreach ($Srcs as $src)
            {
                $sql .= " 
                    AND 
                    (
                        s.nome LIKE '%" . $src . "%' OR
                        u.nome LIKE '%" . $src . "%' OR
                        u.cognome LIKE '%" . $src . "%'
                    ";

                // Matcha data solo se numero + trattino per evitare di includere tutto ricercando per ID
                if (preg_match('/(\d+)-|-(\d+)/', $src)) 
                    $sql .= " OR DATE_FORMAT(ids.data, '%Y-%m-%d') LIKE '%". $src . "%' ";

                $sql .= "
                    )
                ";
            }
        }

        if ($squadra_id)
            $sql .= " AND s.ID = :squadra_id";

        $sql .= "  ORDER BY " . $order . " LIMIT " . $start . ", " . $length;

        $Stmt = $Db->prepare($sql);

        if ($squadra_id)
            $Stmt->bindValue(':squadra_id', $squadra_id);

        $Stmt->execute();

        return $Stmt->fetchAll();
    }

    public function getDettagliIscrizioniSquadre($iscrizione_di_squadra_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT 
                u.ID as id_tessera,
                u.nome as nome_membro,
                u.cognome as cognome_membro,
                g.nome as nome_gara,
                e.nome as nome_evento
            FROM iscrizioni_iscrizioni_di_squadra as iids
            JOIN iscrizioni as i ON i.ID = iids.iscrizione_id
            JOIN utenti as u ON u.ID = i.ID_utente
            JOIN gare as g ON i.ID_gara = g.ID
            JOIN eventi as e ON e.ID = g.ID_evento
            WHERE iids.iscrizione_di_squadra_id = :iscrizione_di_squadra_id
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':iscrizione_di_squadra_id', $iscrizione_di_squadra_id);

        $Stmt->execute();

        return $Stmt->fetchAll();
    }

    public function getIscrizioneDiSquadra($iscrizione_di_squadra_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT *
            FROM iscrizioni_di_squadra
            WHERE id = :iscrizione_di_squadra_id
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':iscrizione_di_squadra_id', $iscrizione_di_squadra_id);

        $Stmt->execute();

        return $Stmt->fetch();
    }

    public function getListaIscrizioniSquadra($iscrizione_di_squadra_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT *
            FROM iscrizioni_iscrizioni_di_squadra
            WHERE iscrizione_di_squadra_id = :iscrizione_di_squadra_id
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':iscrizione_di_squadra_id', $iscrizione_di_squadra_id);

        $Stmt->execute();

        return $Stmt->fetchAll();
    }

    public function setIscrizioneDiSquadraPagato($iscrizione_di_squadra_id)
    {
        $Db = static::getDB();

        $sql = "
            UPDATE iscrizioni_di_squadra
            SET pagato = 1
            WHERE id = :iscrizione_di_squadra_id
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':iscrizione_di_squadra_id', $iscrizione_di_squadra_id);

        $Stmt->execute();
    }

    public function getOpzioniAcquistoAcquistateAmount()
    {
        $Db = static::getDB();

        $sql = "
            SELECT COUNT(*)
            FROM acquisti_opzioni_acquisto_eventi as aoae
            LEFT JOIN eventi_opzioni_acquisto as eoa ON aoae.IDOpzioneAcquistoEvento = eoa.ID
            LEFT JOIN eventi as e ON e.ID = eoa.IDEvento
            LEFT JOIN utenti as u ON u.ID = aoae.IDUtente
            LEFT JOIN opzioni_acquisto as o ON o.ID = eoa.IDOpzioneAcquisto
            WHERE YEAR(e.data) = YEAR(NOW())
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->execute();

        return $Stmt->fetchColumn();
    }

    public function getOpzioniAcquistoAcquistateAmountFiltered($search)
    {
        $Db = static::getDB();

        $sql = "
            SELECT COUNT(*)
            FROM acquisti_opzioni_acquisto_eventi as aoae
            LEFT JOIN eventi_opzioni_acquisto as eoa ON aoae.IDOpzioneAcquistoEvento = eoa.ID
            LEFT JOIN eventi as e ON e.ID = eoa.IDEvento
            LEFT JOIN utenti as u ON u.ID = aoae.IDUtente
            LEFT JOIN opzioni_acquisto as o ON o.ID = eoa.IDOpzioneAcquisto
            WHERE YEAR(e.data) = YEAR(NOW())
        ";

        if ($search != '')
        {
            $Srcs = explode(' ', $search);

            foreach ($Srcs as $src)
            {
                $sql .= " 
                    AND 
                    (
                        e.nome LIKE '%" . $src . "%' OR
                        u.nome LIKE '%" . $src . "%' OR
                        o.nome LIKE '%" . $src . "%' OR
                        u.cognome LIKE '%" . $src . "%'
                    )
                ";
            }
        }

        $Stmt = $Db->prepare($sql);

        $Stmt->execute();

        return $Stmt->fetchColumn();
    }

    public function getOpzioniAcquistoAcquistatePagination
    (
        $order, 
        $start, 
        $length, 
        $search
    )
    {
        $Db = static::getDB();

        $sql = "
            SELECT 
                aoae.dataora as opzione_acquisto_data,
                aoae.prezzo as opzione_acquisto_prezzo,
                aoae.IDPagamento as pagamento_id,
                e.nome as evento_nome,
                u.nome as utente_nome,
                u.cognome as utente_cognome,
                u.email as utente_email,
                o.nome as opzione_acquisto_nome,
                aoae.ID as opzione_acquisto_id
            FROM acquisti_opzioni_acquisto_eventi as aoae
            LEFT JOIN eventi_opzioni_acquisto as eoa ON aoae.IDOpzioneAcquistoEvento = eoa.ID
            LEFT JOIN eventi as e ON e.ID = eoa.IDEvento
            LEFT JOIN utenti as u ON u.ID = aoae.IDUtente
            LEFT JOIN opzioni_acquisto as o ON o.ID = eoa.IDOpzioneAcquisto
            WHERE YEAR(e.data) = YEAR(NOW())
        ";

        if ($search != '')
        {
            $Srcs = explode(' ', $search);

            foreach ($Srcs as $src)
            {
                $sql .= " 
                    AND 
                    (
                        e.nome LIKE '%" . $src . "%' OR
                        u.nome LIKE '%" . $src . "%' OR
                        o.nome LIKE '%" . $src . "%' OR
                        u.cognome LIKE '%" . $src . "%'
                    )
                ";
            }
        }

        $sql .= "  ORDER BY " . $order . " LIMIT " . $start . ", " . $length;

        $Stmt = $Db->prepare($sql);

        $Stmt->execute();

        return $Stmt->fetchAll();
    }

    public function getOpzioniAcquistoSquadra($iscrizione_di_squadra_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT 
                u.nome as nome_membro,
                u.cognome as cognome_membro,
                e.nome as nome_evento
            FROM iscrizioni_iscrizioni_di_squadra as iids
            JOIN iscrizioni as i ON i.ID = iids.iscrizione_id
            JOIN utenti as u ON u.ID = i.ID_utente
            JOIN gare as g ON i.ID_gara = g.ID
            JOIN eventi as e ON e.ID = g.ID_evento
            JOIN eventi_opzioni_acquisto as eoa ON eoa.IDEvento = e.ID
            JOIN acquisti_opzioni_acquisto_eventi as aoae ON (aoae.IDOpzioneAcquistoEvento, u.ID) = (eoa.ID, aoae.IDUtente)
            WHERE iids.iscrizione_di_squadra_id = :iscrizione_di_squadra_id 
            GROUP BY e.nome
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':iscrizione_di_squadra_id', $iscrizione_di_squadra_id);

        $Stmt->execute();

        return $Stmt->fetchAll();
    }

    public function cancellaOpzioneAcquistoAcquistata($opzione_acquisto_id)
    {
        $Db = static::getDB();

        $sql = "
            DELETE FROM acquisti_opzioni_acquisto_eventi 
            WHERE ID = :opzione_acquisto_id
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':opzione_acquisto_id', $opzione_acquisto_id);

        $Stmt->execute();
    }
}
