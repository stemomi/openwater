<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\AccountModel;
use App\Models\AdminAccountModel;
use \App\Models\FunctionsModel;
use \App\Models\GareModel;
use \App\Models\GareIndividualiModel;
use \App\Models\SquadreModel;
use \App\Models\ImpostazioniModel;
use \App\Models\ScontiModel;
use \App\Models\ProdottiModel;
use \App\Models\AttributiProdottiModel;

use stdClass;
use DateTime;

class Account extends \Core\Controller
{
    public function Login()
    {
        session_start();

        if ( isset($_SESSION['IDUtente']) )
            header("location: ../Account/Profile");
        else
            session_destroy();

        $ArrayTemplateAction= [];
        $ArrayTemplateGate= ['URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Account/login.html', $ArrayTemplateCombined);
    }

    public function LoginConfirm()
    {
        if (isset($_POST['Email']) && isset($_POST['Password']))
        {
            $Email= $_POST['Email'];
            $Password= $_POST['Password'];

            $AccountClass= new AccountModel();
            $ControllaLogin= $AccountClass->ControllaLogin($Email,$Password);

            if ($ControllaLogin)
            {
                if ($AccountClass->checkUtenteDisabilitato($ControllaLogin->ID))
                {
                    $ArrayTemplateAction= ['LoginFallito' => 2];
                    $ArrayTemplateGate= ['URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER];
                    $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

                    View::renderTemplate('Account/login.html', $ArrayTemplateCombined);
                }
                
                session_start();
                $_SESSION['IDUtente']= $ControllaLogin->ID;

                header("location: ../Account/Profile");
            }
            else
            {
            	$ArrayTemplateAction= ['LoginFallito' => 1];
		        $ArrayTemplateGate= ['URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER];
		        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

		        View::renderTemplate('Account/login.html', $ArrayTemplateCombined);
            }
        }
    }

    public function Logout()
    {
        session_start();
        if(session_destroy())
            header("Location: ../Account/Login");
    }

    public function Register()
    {
        $AccountClass = new AccountModel();
        $SquadreClass = new SquadreModel();

        $ProvinceItaliane = $AccountClass->getListaProvinceItaliane();
        $Squadre = $SquadreClass->getAllSquadre();

        $anno_data_nascita_massima = DateTime::createFromFormat('Y-m-d', date('Y-m-d'))->modify('-10 year')->format('Y');

        $ArrayTemplateAction = 
        [
            'ProvinceItaliane' => $ProvinceItaliane,
            'Squadre' => $Squadre,
            'anno_data_nascita_massima' => $anno_data_nascita_massima
        ];
        $ArrayTemplateGate = ['URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Account/register.html', $ArrayTemplateCombined);
    }

    public function RegisterConfirm()
    {
        $AccountClass = new AccountModel();
        $SquadreClass = new SquadreModel();

        if (isset($_POST['Email']) && isset($_POST['Password']))
        {
            $Nome= $_POST['Nome'];
            $Cognome= $_POST['Cognome'];
            $Email= $_POST['Email'];
            $Password= $_POST['Password'];
            $Telefono= $_POST['Telefono'];
            $Sesso= $_POST['Sesso'];
            $CAP= $_POST['CAP'];
            $Comune= $_POST['Comune'];
            $IDProvincia= $_POST['IDProvincia'];
            $Via= $_POST['Via'];
            $NumeroCivico= $_POST['NumeroCivico'];
            $DataNascita= $_POST['DataNascita'];
            $LuogoNascita= $_POST['LuogoNascita'];
            $TagliaMaglietta= $_POST['TagliaMaglietta'];
            $DonatoreAvis= $_POST['DonatoreAvis'];
            $NomeCognomeGenitore= $_POST['NomeCognomeGenitore'];
            $DataNascitaGenitore= $_POST['DataNascitaGenitore'];
            $LuogoNascitaGenitore= $_POST['LuogoNascitaGenitore'];
            $EmailGenitore= $_POST['EmailGenitore'];
            $TelefonoGenitore= $_POST['TelefonoGenitore'];
            $CAPGenitore= $_POST['CAPGenitore'];
            $ComuneGenitore= $_POST['ComuneGenitore'];
            $ProvinciaGenitore= $_POST['ProvinciaGenitore'];
            $ViaGenitore= $_POST['ViaGenitore'];
            $NumeroCivicoGenitore= $_POST['NumeroCivicoGenitore'];
            $RappresentanteLegaleGenitore= $_POST['RappresentanteLegaleGenitore'];
            $IDSquadra = $_POST['IDSquadra'];
            $CodiceFiscale = $_POST['CodiceFiscale'];
            $PaeseEstero = $_POST['PaeseEstero'];
            $dichiarazione_iscritto_ha_10_anni = isset($_POST['dichiarazione_iscritto_ha_10_anni_checkbox']) ? true : false;

            $controlla_cambio_utente = $AccountClass->ControllaEmailEsistente($Email);

            if ($controlla_cambio_utente < 1)
            {
                $IDUtenteInserito= $AccountClass->InserisciUtente(  
                    $Email,
                    $Password,
                    $Nome,
                    $Cognome,
                    $Telefono,
                    $Sesso,
                    $CAP,
                    $Comune,
                    $IDProvincia,
                    $Via,
                    $NumeroCivico,
                    $DataNascita,
                    $LuogoNascita,
                    $TagliaMaglietta,
                    $DonatoreAvis,
                    $NomeCognomeGenitore,
                    $DataNascitaGenitore,
                    $LuogoNascitaGenitore,
                    $EmailGenitore,
                    $TelefonoGenitore,
                    $CAPGenitore,
                    $ComuneGenitore,
                    $ProvinciaGenitore,
                    $ViaGenitore,
                    $NumeroCivicoGenitore,
                    $RappresentanteLegaleGenitore,
                    $IDSquadra,
                    $CodiceFiscale,
                    $PaeseEstero,
                    $dichiarazione_iscritto_ha_10_anni
                );

                session_start();
                $_SESSION['IDUtente']= $IDUtenteInserito;

                header("location: ../Account/Profile");
            }
            else
            {
                $ProvinceItaliane = $AccountClass->getListaProvinceItaliane();
                $Squadre = $SquadreClass->getAllSquadre();

                $anno_data_nascita_massima = DateTime::createFromFormat('Y-m-d', date('Y-m-d'))->modify('-10 year')->format('Y');

                $ArrayTemplateAction = 
                [
                    'ProvinceItaliane' => $ProvinceItaliane,
                    'Squadre' => $Squadre,
                    'anno_data_nascita_massima' => $anno_data_nascita_massima
                ];
                
                $ArrayTemplateGate = 
                [
                    'URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER,
                    'EmailEsistente' => 1
                ];
                $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

                View::renderTemplate('Account/register.html', $ArrayTemplateCombined);
            }
        }
    }

    public function Profile()
    {
        require_once("Gate.php");

        $AccountClass = new AccountModel();
        $Utente = $AccountClass->getUtenteSessione();
        $ProdottiClass = new ProdottiModel();
        $GareClass = new GareModel();

        $AcquistoMagliette = $GareClass->getAcquistoMaglietteUtente($Utente->ID);

        $ha_selezionato_magliette = 
            $AcquistoMagliette ? 
            $ProdottiClass->getMaglietteSelezionateDaAcquistoUtente($AcquistoMagliette->ID)
            : 0;

        // Prodotti
        $Prodotti = $ProdottiClass->getAllProdottiConAttributi();

        $ArrayTemplateAction = 
        [
            'Utente' => $Utente, 
            'Prodotti' => $Prodotti,
            'AcquistoMagliette' => $AcquistoMagliette,
            'ha_selezionato_magliette' => $ha_selezionato_magliette
        ];

        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Account/profile.html', $ArrayTemplateCombined);
    }

    public function Iscrizioni()
    {
        require_once("Gate.php");

        $GareClass= new GareModel();
        $GareIndividualiClass= new GareIndividualiModel();
        $AccountClass= new AccountModel();
        $ImpostazioniClass = new ImpostazioniModel();
        $ScontiClass = new ScontiModel();
        $ProdottiClass = new ProdottiModel();

        $Impostazioni_AbilitaBonifico = $ImpostazioniClass->getImpostazioni('AbilitaBonifico');

        $Utente= $AccountClass->getUtenteSessione();
        $BoaDaPagare= $Utente->boa_da_pagare;

        // Estrae gare utente
        $EstraeGareAperteUtente= $GareClass->EstraeGareAperteUtente($Utente);
        $Eventi= $EstraeGareAperteUtente->EventiConDettagli;

        // Estrae gare individuali utente
        $EstraeGareIndividualiAperteUtente= $GareIndividualiClass->EstraeGareAperteUtente($Utente);
        $EventiIndividuali= $EstraeGareIndividualiAperteUtente->Eventi;

        // Sconto € 5 per staffette facenti parte di un evento a cui sono state già pagate altre gare
        //$TotaleScontoStaffette = $GareClass->getUtenteStaffetteDaScontare($Utente->ID);

        // Sconti per combo gare
        $ScontoComboGare = $ScontiClass->getUtenteScontoComboGare($Utente->ID);
        $ListaScontiComboGare = $ScontoComboGare->ListaSconti;
        $TotaleScontoComboGare = $ScontoComboGare->Totale;

        // Controlla acquisti in corso magliette
        $CheckAcquistoMaglietta1 = $GareClass->checkUtenteAcquistoMaglietta($Utente->ID, 1); // quantita
        $CheckAcquistoMaglietta2 = $GareClass->checkUtenteAcquistoMaglietta($Utente->ID, 2); // quantita
        $CheckAcquistoMaglietta3 = $GareClass->checkUtenteAcquistoMaglietta($Utente->ID, 3); // quantita
        
        $TotCheckAcquistoMagliette = 
            $CheckAcquistoMaglietta1 +
            $CheckAcquistoMaglietta2 +
            $CheckAcquistoMaglietta3;

        // Creazione variabile per contenere id acquisti_magliette
        $AcquistoMaglietta = $GareClass->getUtenteAcquistoMaglietta($Utente->ID);
        $acquisti_magliette_id = $AcquistoMaglietta ? $AcquistoMaglietta->ID : 0;

        // Prodotti acquistati (ma da pagare)
        $ProdottiAcquisto = $ProdottiClass->getAllProdottiDaPagareUtenteConAttributi();

        $TotaleDaPagareUtente = $AccountClass->getTotaleDaPagareUtente($Utente->ID);
        $TotaleDaSaldare = number_format(
            $TotaleDaPagareUtente -
            $Utente->credito -
            //$TotaleScontoStaffette -
            $TotaleScontoComboGare,
            2, '.', ''
        );

        if ($TotaleDaSaldare <= 70)
            $MaggiorazionePaypalEffettiva = number_format(MAGGIORAZIONE_PAYPAL, 2, '.', '');
        elseif ($TotaleDaPagareUtente > 70 && $TotaleDaPagareUtente <= 130)
            $MaggiorazionePaypalEffettiva = number_format(MAGGIORAZIONE_PAYPAL_2, 2, '.', '');
        elseif ($TotaleDaSaldare > 130 && $TotaleDaSaldare <= 200)
            $MaggiorazionePaypalEffettiva = number_format(MAGGIORAZIONE_PAYPAL_3, 2, '.', '');
        elseif ($TotaleDaSaldare > 200 && $TotaleDaSaldare <= 300)
            $MaggiorazionePaypalEffettiva = number_format(MAGGIORAZIONE_PAYPAL_4, 2, '.', '');
        else
            $MaggiorazionePaypalEffettiva = number_format(MAGGIORAZIONE_PAYPAL_5, 2, '.', '');

        $TotaleDaSaldarePaypal = number_format($TotaleDaSaldare + $MaggiorazionePaypalEffettiva, 2, '.', '');

        $ArrayTemplateAction= [
            'Eventi' => $Eventi,
            'EventiIndividuali' => $EventiIndividuali,
            'TotaleDaPagareUtente' => $TotaleDaPagareUtente,
            'TotaleDaSaldare' => $TotaleDaSaldare,
            'TotaleDaSaldarePaypal' => $TotaleDaSaldarePaypal,
            //'TotaleScontoStaffette' => $TotaleScontoStaffette,
            'ListaScontiComboGare' => $ListaScontiComboGare,
            'TotaleScontoComboGare' => $TotaleScontoComboGare,
            'MaggiorazionePaypalEffettiva' => $MaggiorazionePaypalEffettiva,
            'BoaDaPagare' => $BoaDaPagare,
            'IDUtente' => $Utente->ID,
            'Utente' => $Utente,
            'Impostazioni_AbilitaBonifico' => $Impostazioni_AbilitaBonifico,
            'CheckAcquistoMaglietta1' => $CheckAcquistoMaglietta1,
            'CheckAcquistoMaglietta2' => $CheckAcquistoMaglietta2,
            'CheckAcquistoMaglietta3' => $CheckAcquistoMaglietta3,
            'TotCheckAcquistoMagliette' => $TotCheckAcquistoMagliette,
            'ProdottiAcquisto' => $ProdottiAcquisto,
            'acquisti_magliette_id' => $acquisti_magliette_id
        ];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Account/iscrizioni.html', $ArrayTemplateCombined);
    }

    public function CaricaBonifico()
    {
        require_once("Gate.php");

        $AccountClass= new AccountModel();
        $FunctionsClass= new FunctionsModel();
        $GareClass= new GareModel();
        $GareIndividualiClass = new GareIndividualiModel();
        $ImpostazioniClass = new ImpostazioniModel();
        $ScontiClass = new ScontiModel();

        $Impostazioni_AbilitaBonifico = $ImpostazioniClass->getImpostazioni('AbilitaBonifico');

        $Utente = $AccountClass->getUtenteSessione();

        if ($Impostazioni_AbilitaBonifico < 1 && $AccountClass->getTotaleDaPagareUtente($Utente->ID) < 200)
            die('In bonifico non è disponibile come forma di pagamento!');

        $GareDaPagare= $GareClass->getAllGareAperteUtenteDaPagare($Utente->ID);

        if ( isset($_FILES['FileBonifico']) && isset($_POST['DataBonifico']) && isset($_POST['ImportoBonifico']) )
        {
            $DataBonifico= $_POST['DataBonifico'];
            $ImportoBonifico= $_POST['ImportoBonifico'];

            // File Bonifico
            if ( $_FILES["FileBonifico"]["name"] != '' )
            {
                // CONTROLLI DI SICUREZZA UPLOAD FILE
                $checkUploadFile= $FunctionsClass->checkUploadFile($_FILES["FileBonifico"]["name"], $_FILES["FileBonifico"]["tmp_name"]);
                if ( $checkUploadFile != "") die($checkUploadFile);

                // Crea nome file bonifico
                $ext_var= explode( ".", basename($_FILES["FileBonifico"]["name"]) );
                $ext = end($ext_var);
                $path_bonifico = "bonifici/" . $Utente->ID . "-" . time() . "-" . bin2hex(openssl_random_pseudo_bytes(8)) . "." . $ext;

                // Sposta file bonifico nella cartella
                if (move_uploaded_file($_FILES["FileBonifico"]["tmp_name"], PUBLIC_PATH . $path_bonifico))
                {
                    $EsitoCaricamentoBonifico= '<div class="text-success text-center">Bonifico caricato correttamente!</div>';

                    $BoaPagataFlag= $Utente->boa_da_pagare == 1 ? true : false;

                    // Calcola eventuale credito utilizzato
                    $CreditoUtilizzato = number_format($Utente->credito, 2, '.', '');

                    // Azzera credito
                    $AccountClass->AggiornaCreditoUtente(0.00, $Utente->ID);

                    // Crea record pagamento
                    $IDPagamento= $GareClass->insertPagamento(
                        $Utente->ID,
                        $DataBonifico,
                        $ImportoBonifico,
                        1,
                        $path_bonifico,
                        $BoaPagataFlag,
                        '',
                        '',
                        $CreditoUtilizzato);

                    // Salva sconti combo gare applicati (da fare prima di impostare le iscrizioni come pagate)
                    $ScontiComboGareApplicati = $ScontiClass->getUtenteScontoComboGare($Utente->ID);
                    $ListaScontiComboGareApplicati = $ScontiComboGareApplicati ? $ScontiComboGareApplicati->ListaSconti : [];

                    foreach ($ListaScontiComboGareApplicati as $sconto_combo)
                        $ScontiClass->addScontoComboGaraAPagamento($IDPagamento, $sconto_combo->ID);

                    // Cerca gare da pagare
                    $GareDaPagare= $GareClass->getAllGareAperteUtenteDaPagare($Utente->ID);
                    $GareIndividualiDaPagare= $GareIndividualiClass->getAllGareAperteUtenteDaPagare($Utente->ID);

                    $RigheGarePagate= [];
                    $RigheBoePagate= [];

                    // Gare in contemporanea
                    foreach ($GareDaPagare as $gara)
                    {
                        // Salva record gara pagata
                        $GareClass->salvaPagamentoGara($Utente->ID, $gara->ID_gara, $IDPagamento);

                        // Imposta gara come pagata
                        $GareClass->setGaraPagata($Utente->ID, $gara->ID_gara);

                        // Aggiunge righe per notifica email
                        $RigheGarePagate[]= $gara->nome_evento . ': ' . $gara->nome_gara;

                        // Se acquistato boa inserisce nome evento
                        if ($gara->I_richiesta_boa == 1)
                            $RigheBoePagate[] = $gara->nome_evento;
                    }

                    // Gare individuali
                    foreach ($GareIndividualiDaPagare as $gara)
                    {
                        // Salva record gara pagata (relativo alla singola iscrizione)
                        $GareIndividualiClass->salvaPagamentoGara($Utente->ID, $gara->ID, $IDPagamento);

                        // Imposta gara come pagata
                        $GareIndividualiClass->setGaraPagata($Utente->ID, $gara->ID_gara);

                        // Aggiunge righe per notifica email
                        $RigheGarePagate[]= $gara->nome_evento . ': ' . $gara->nome_gara;
                    }

                    // Segna sconti effettuati su staffette
                    foreach ($GareDaPagare as $gara)
                        $GareClass->segnaStaffettaSeDaScontare($gara);

                    // Imposta boe gare utente come pagate (salvando prezzo attuale)
                    $GareClass->setUtenteGarePrezzoBoa($Utente->ID);

                    // Cerca magliette acquistate per aggiungerle all'email (da chiamare prima di impostarle come pagate!)
                    $MaglietteAcquistate = $GareClass->getStringaEmailMaglietteAcquistate($Utente->ID);

                    // Imposta magliette come pagate
                    $GareClass->impostaUtenteMaglietteComePagate($Utente->ID, $IDPagamento);

                    // Reimposta boa da pagare
                    $AccountClass->AggiornaBoaDaPagareUtente(false, $Utente->ID);

                    // Invia email di notifica
                    $subject= 'ItalianOpenWaterTour: Conferma pagamento bonifico';
                    $messaggio= '<img src="http://italianopenwatertour.com/wp-content/uploads/2019/01/logo_menu.png" style="margin: 0 auto; text-align: center; padding: 20px;">
                                 <br />
                                 <p>Ciao ' . $Utente->nome . ' ' . $Utente->cognome . ',</p>
                                 <br /><br />
                                 <div>Il bonifico dell\'importo di € ' . $ImportoBonifico . ' è stato caricato correttamente ed è ora in fase di convalidazione.</div>
                                 <br />
                                 <div>
                                 Partecipazione alle seguenti gare:
                                 </div>';

                    foreach ($RigheGarePagate as $gara) $messaggio .= '<br /><div>' . $gara . '</div>';
                    foreach ($RigheBoePagate as $boa) $messaggio .= '<br /><div>Boa per evento ' . $boa . '</div>';
                    if ($BoaPagataFlag) $messaggio .= '<br /><div>Attrezzatura acquistata: Boa</div>';
                    $messaggio .= $MaglietteAcquistate;

                    $messaggio .= ' <br />
                                    <p>Per Bonifico: Conto intestato a Glaciali Societa\' Sportiva Dilettantistica A R.I., IBAN IT41G0623010802000047178141, BIC/SWIFT CRPPIT2P351.
                                    <br />NB: Controlla la quota corretta sulle pagine di ogni evento
                                    </p>
                                    <p>Per controllare lo stato delle vostre iscrizioni potete andare sulla nuova area utente del sito ItalianOpenWaterTour.
                                    </p>
                                    <br />
                                    <p><a href="italianopenwatertour.com">italianopenwatertour.com</a></p>';

                    $FunctionsClass->sendEmail($Utente->email,'',$subject,$messaggio);
                }
                else
                    $EsitoCaricamentoBonifico= '<div class="text-danger text-center">Errore durante il caricamento del bonifico!</div>';
            }
            else
                $EsitoCaricamentoBonifico= '<div class="text-danger text-center">Nessun bonifico caricato!</div>';
        }

        // Estrae gare utente per view iscrizioni
        $EstraeGareAperteUtente= $GareClass->EstraeGareAperteUtente($Utente);
        $Eventi= $EstraeGareAperteUtente->Eventi;
        $TotaleDaPagareUtente = $AccountClass->getTotaleDaPagareUtente($Utente->ID);

        $ArrayTemplateAction= ['TestoNotifica' => $EsitoCaricamentoBonifico,
                               'Eventi' => $Eventi,
                               'TotaleDaPagareUtente' => $TotaleDaPagareUtente];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Account/iscrizioni.html', $ArrayTemplateCombined);
    }

    public function CaricaCoupon()
    {
        require_once("Gate.php");

        $AccountClass= new AccountModel();
        $FunctionsClass= new FunctionsModel();
        $GareClass= new GareModel();

        die('Coupon disabilitati');

        $Utente= $AccountClass->getUtenteSessione();

        if ( isset($_POST['CodiceCoupon']) )
        {
            $CodiceCoupon= $_POST['CodiceCoupon'];

            $EsitoCaricamentoCoupon= '<div class="text-success text-center">Il Coupon è stato caricato correttamente ed è ora in fase di convalidazione!</div>';

            $BoaPagataFlag= false;

            // Cerca gare da pagare
            $GareDaPagare = $GareClass->getAllGareAperteUtenteDaPagare($Utente->ID);

            if (count($GareDaPagare) < 1)
                $EsitoCaricamentoCoupon = '<div class="text-danger text-center">Non sei iscritto a nessuna gara, impossibile caricare coupon!</div>';
            else
            {
                // Crea record pagamento
                $IDPagamento= $GareClass->insertPagamento($Utente->ID, date('Y-m-d H:i:s'), 0.00, 3, '', $BoaPagataFlag, '', $CodiceCoupon);

                $RigheGarePagate= [];
                $RigheBoePagate= [];

                foreach ($GareDaPagare as $gara)
                {
                    // Salva record gara pagata
                    $GareClass->salvaPagamentoGara($Utente->ID, $gara->ID_gara, $IDPagamento);

                    // Imposta gara come pagata
                    $GareClass->setGaraPagata($Utente->ID, $gara->ID_gara);

                    // Aggiunge righe per notifica email
                    $RigheGarePagate[]= $gara->nome_evento . ': ' . $gara->nome_gara;

                    // Se acquistato boa inserisce nome evento
                    if ($gara->I_richiesta_boa == 1)
                        $RigheBoePagate[] = $gara->nome_evento;
                }

                // Segna sconti effettuati su staffette
                foreach ($GareDaPagare as $gara)
                    $GareClass->segnaStaffettaSeDaScontare($gara);

                // Imposta boe gare utente come pagate (salvando prezzo attuale)
                $GareClass->setUtenteGarePrezzoBoa($Utente->ID);

                // Cerca magliette acquistate per aggiungerle all'email (da chiamare prima di impostarle come pagate!)
                $MaglietteAcquistate = $GareClass->getStringaEmailMaglietteAcquistate($Utente->ID);

                // Imposta magliette come pagate
                $GareClass->impostaUtenteMaglietteComePagate($Utente->ID, $IDPagamento);

                // Invia email di notifica
                $subject= 'ItalianOpenWaterTour: Conferma pagamento coupon';
                $messaggio= '<img src="http://italianopenwatertour.com/wp-content/uploads/2019/01/logo_menu.png" style="margin: 0 auto; text-align: center; padding: 20px;">
                             <br />
                             <p>Ciao ' . $Utente->nome . ' ' . $Utente->cognome . ',</p>
                             <br /><br />
                             <div>Il coupon è stato caricato correttamente ed è ora in fase di convalidazione.</div>
                             <br />
                             <div>
                             Partecipazione alle seguenti gare:
                             </div>';

                foreach ($RigheGarePagate as $gara) $messaggio .= '<br /><div>' . $gara . '</div>';
                foreach ($RigheBoePagate as $boa) $messaggio .= '<br /><div>Boa per evento ' . $boa . '</div>';
                if ($BoaPagataFlag) $messaggio .= '<br /><div>Attrezzatura acquistata: Boa</div>';
                $messaggio .= $MaglietteAcquistate;

                $messaggio .= ' <br />
                                <p><a href="italianopenwatertour.com">italianopenwatertour.com</a></p>';

                $FunctionsClass->sendEmail($Utente->email,'',$subject,$messaggio);
            }
        }
        else
            $EsitoCaricamentoCoupon= '<div class="text-danger text-center">Nessun coupon caricato!</div>';

        // Estrae gare utente per view iscrizioni
        $EstraeGareAperteUtente= $GareClass->EstraeGareAperteUtente($Utente);
        $Eventi= $EstraeGareAperteUtente->Eventi;
        $TotaleDaPagareUtente = $AccountClass->getTotaleDaPagareUtente($Utente->ID);

        $ArrayTemplateAction= ['TestoNotifica' => $EsitoCaricamentoCoupon,
                               'Eventi' => $Eventi,
                               'TotaleDaPagareUtente' => $TotaleDaPagareUtente];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Account/iscrizioni.html', $ArrayTemplateCombined);
    }

    public function PagaConCredito()
    {
        require_once("Gate.php");

        $AccountClass= new AccountModel();
        $FunctionsClass= new FunctionsModel();
        $GareClass= new GareModel();
        $GareIndividualiClass = new GareIndividualiModel();
        $ScontiClass = new ScontiModel();

        $Utente= $AccountClass->getUtenteSessione();

        if ( isset($_POST['PagaConCredito']) )
        {
            $EsitoPagamentoConCredito= '<div class="text-success text-center">Pagamento con credito effettuato correttamente!</div>';

            $BoaPagataFlag= $Utente->boa_da_pagare == 1 ? true : false;

            $TotaleDaSaldareUtente = $AccountClass->getTotaleDaSaldareUtente($Utente->ID, 1)->totale_da_saldare;

            if ($TotaleDaSaldareUtente <= $Utente->credito)
            {
                // Aggiorna nuovo credito
                $NuovoCredito = number_format($Utente->credito - $TotaleDaSaldareUtente, 2, '.', '');
                $AccountClass->AggiornaCreditoUtente($NuovoCredito, $Utente->ID);

                // Crea record pagamento
                $IDPagamento= $GareClass->insertPagamento(
                    $Utente->ID,
                    date('d/m/Y'),
                    $TotaleDaSaldareUtente,
                    4,
                    '',
                    $BoaPagataFlag,
                    '',
                    '',
                    $TotaleDaSaldareUtente);

                // Salva sconti combo gare applicati (da fare prima di impostare le iscrizioni come pagate)
                $ScontiComboGareApplicati = $ScontiClass->getUtenteScontoComboGare($Utente->ID);
                $ListaScontiComboGareApplicati = $ScontiComboGareApplicati ? $ScontiComboGareApplicati->ListaSconti : [];

                foreach ($ListaScontiComboGareApplicati as $sconto_combo)
                    $ScontiClass->addScontoComboGaraAPagamento($IDPagamento, $sconto_combo->ID);

                // Cerca gare da pagare
                $GareDaPagare= $GareClass->getAllGareAperteUtenteDaPagare($Utente->ID);
                $GareIndividualiDaPagare= $GareIndividualiClass->getAllGareAperteUtenteDaPagare($Utente->ID);

                $RigheGarePagate= [];
                $RigheBoePagate= [];

                // Gare in contemporanea
                foreach ($GareDaPagare as $gara)
                {
                    // Salva record gara pagata
                    $GareClass->salvaPagamentoGara($Utente->ID, $gara->ID_gara, $IDPagamento);

                    // Imposta gara come pagata
                    $GareClass->setGaraPagata($Utente->ID, $gara->ID_gara);

                    // Aggiunge righe per notifica email
                    $RigheGarePagate[]= $gara->nome_evento . ': ' . $gara->nome_gara;

                    // Se acquistato boa inserisce nome evento
                    if ($gara->I_richiesta_boa == 1)
                        $RigheBoePagate[] = $gara->nome_evento;
                }

                // Gare individuali
                foreach ($GareIndividualiDaPagare as $gara)
                {
                    // Salva record gara pagata (relativo alla singola iscrizione)
                    $GareIndividualiClass->salvaPagamentoGara($Utente->ID, $gara->ID, $IDPagamento);

                    // Imposta gara come pagata
                    $GareIndividualiClass->setGaraPagata($Utente->ID, $gara->ID_gara);

                    // Aggiunge righe per notifica email
                    $RigheGarePagate[]= $gara->nome_evento . ': ' . $gara->nome_gara;
                }

                // Segna sconti effettuati su staffette
                foreach ($GareDaPagare as $gara)
                    $GareClass->segnaStaffettaSeDaScontare($gara);

                // Imposta boe gare utente come pagate (salvando prezzo attuale)
                $GareClass->setUtenteGarePrezzoBoa($Utente->ID);

                // Cerca magliette acquistate per aggiungerle all'email (da chiamare prima di impostarle come pagate!)
                $MaglietteAcquistate = $GareClass->getStringaEmailMaglietteAcquistate($Utente->ID);

                // Imposta magliette come pagate
                $GareClass->impostaUtenteMaglietteComePagate($Utente->ID, $IDPagamento);

                // Reimposta boa da pagare
                $AccountClass->AggiornaBoaDaPagareUtente(false);

                // Invia email di notifica
                $subject= 'ItalianOpenWaterTour: Conferma pagamento con credito';
                $messaggio= '<img src="http://italianopenwatertour.com/wp-content/uploads/2019/01/logo_menu.png" style="margin: 0 auto; text-align: center; padding: 20px;">
                             <br />
                             <p>Ciao ' . $Utente->nome . ' ' . $Utente->cognome . ',</p>
                             <br /><br />
                             <div>Il pagamento dell\'importo di € ' . $TotaleDaSaldareUtente . ' è stato effettuato interamente con il credito residuo nel tuo account.</div>
                             <br />
                             <div>
                             Partecipazione alle seguenti gare:
                             </div>';

                foreach ($RigheGarePagate as $gara) $messaggio .= '<br /><div>' . $gara . '</div>';
                foreach ($RigheBoePagate as $boa) $messaggio .= '<br /><div>Boa per evento ' . $boa . '</div>';
                if ($BoaPagataFlag) $messaggio .= '<br /><div>Attrezzatura acquistata: Boa</div>';
                $messaggio .= $MaglietteAcquistate;

                $messaggio .= ' <br />
                                <p>Per controllare lo stato delle vostre iscrizioni potete andare sulla nuova area utente del sito ItalianOpenWaterTour.
                                </p>
                                <br />
                                <p><a href="italianopenwatertour.com">italianopenwatertour.com</a></p>';

                $FunctionsClass->sendEmail($Utente->email,'',$subject,$messaggio);

                header("location: ../Account/Iscrizioni");
            }
            else
                $EsitoPagamentoConCredito= '<div class="text-danger text-center">Impossibile pagare interamente con credito, credito insufficiente!</div>';
        }
        else
             $EsitoPagamentoConCredito= '<div class="text-danger text-center">Errore durante il pagamento con credito!</div>';

        // Riestrae utente per aggiornare credito
        $Utente= $AccountClass->getUtenteSessione();
        $ArrayTemplateGate['Utente'] = $Utente;

        // Estrae gare utente per view iscrizioni
        $EstraeGareAperteUtente= $GareClass->EstraeGareAperteUtente($Utente);
        $Eventi= $EstraeGareAperteUtente->Eventi;
        $TotaleDaPagareUtente = $AccountClass->getTotaleDaPagareUtente($Utente->ID);

        $ArrayTemplateAction= ['TestoNotifica' => $EsitoPagamentoConCredito,
                               'Eventi' => $Eventi,
                               'TotaleDaPagareUtente' => $TotaleDaPagareUtente];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Account/iscrizioni.html', $ArrayTemplateCombined);
    }

    public function EditProfile()
    {
        require_once("Gate.php");

        $AccountClass = new AccountModel();
        $SquadreClass = new SquadreModel();

        $Utente = $AccountClass->getUtenteSessione();
        $ProvinceItaliane = $AccountClass->getListaProvinceItaliane();
        $Squadre = $SquadreClass->getAllSquadre();

        $anno_data_nascita_massima = DateTime::createFromFormat('Y-m-d', date('Y-m-d'))->modify('-10 year')->format('Y');

        $ArrayTemplateAction = 
        [
            'Utente' => $Utente,
            'ProvinceItaliane' => $ProvinceItaliane,
            'Squadre' => $Squadre,
            'anno_data_nascita_massima' => $anno_data_nascita_massima
        ];

        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Account/editProfile.html', $ArrayTemplateCombined);
    }

    public function EditProfileConfirm()
    {
        require_once("Gate.php");

        $AccountClass = new AccountModel();
        $FunctionsClass = new FunctionsModel();
        $SquadreClass = new SquadreModel();

        $Utente = $AccountClass->getUtenteSessione();
        $Squadre = $SquadreClass->getAllSquadre();

        $anno_data_nascita_massima = DateTime::createFromFormat('Y-m-d', date('Y-m-d'))->modify('-10 year')->format('Y');
        
        $utente_modificato = '';

        if (isset($_POST['Email']) && isset($_POST['Password']))
        {
            $nome = $_POST['Nome'];
            $cognome = $_POST['Cognome'];
            $email = $_POST['Email'];
            $password = $_POST['Password'];
            $telefono = $_POST['Telefono'];
            $sesso = $_POST['Sesso'];
            $cap = $_POST['CAP'];
            $comune = $_POST['Comune'];
            $provincia = $_POST['Provincia'];
            $id_provincia = $_POST['IDProvincia'];
            $via = $_POST['Via'];
            $numero_civico = $_POST['NumeroCivico'];
            $data_nascita = $_POST['DataNascita'];
            $luogo_nascita = $_POST['LuogoNascita'];
            $gruppo_sportivo = $_POST['GruppoSportivo'];
            $taglia_maglietta = $_POST['TagliaMaglietta'];
            $donatore_avis = $_POST['DonatoreAvis'];
            $nome_cognome_genitore = $_POST['NomeCognomeGenitore'];
            $data_nascita_genitore = $_POST['DataNascitaGenitore'];
            $luogo_nascita_genitore = $_POST['LuogoNascitaGenitore'];
            $email_genitore = $_POST['EmailGenitore'];
            $telefono_genitore = $_POST['TelefonoGenitore'];
            $cap_genitore = $_POST['CAPGenitore'];
            $comune_genitore = $_POST['ComuneGenitore'];
            $provincia_genitore = $_POST['ProvinciaGenitore'];
            $via_genitore = $_POST['ViaGenitore'];
            $numero_civico_genitore = $_POST['NumeroCivicoGenitore'];
            $rappresentante_legale_genitore = $_POST['RappresentanteLegaleGenitore'];
            $scadenza_certificato = $_POST['ScadenzaCertificato'];
            $codice_fiscale = $_POST['CodiceFiscale'];
            $paese_estero = $_POST['PaeseEstero'];
            $id_squadra = $_POST['IDSquadra'];
            $dichiarazione_iscritto_ha_10_anni = isset($_POST['dichiarazione_iscritto_ha_10_anni_checkbox']) ? true : false;

            $controlla_cambio_utente = $AccountClass->ControllaCambioUtente($email);

            if ($controlla_cambio_utente < 1)
            {
                // Aggiorna dati utente
                $AccountClass->AggiornaUtente(  
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
                    $nome_cognome_genitore,
                    $data_nascita_genitore,
                    $luogo_nascita_genitore,
                    $email_genitore,
                    $telefono_genitore,
                    $cap_genitore,
                    $comune_genitore,
                    $provincia_genitore,
                    $via_genitore,
                    $numero_civico_genitore,
                    $rappresentante_legale_genitore,
                    $scadenza_certificato,
                    0, // ID Utente
                    $codice_fiscale,
                    $paese_estero,
                    $id_provincia,
                    $id_squadra,
                    $dichiarazione_iscritto_ha_10_anni
                );

                // Aggiorna password se cambiata
                if ($password != '') 
                    $AccountClass->AggiornaPasswordUtente($password);

                // Aggiorna conferma dati
                $AccountClass->ConfermaDatiUtente();

                $errore_controllo_files = 0;
                $utente_modificato = '<div class="text-success text-center">Modifiche effettuate con successo!</div>';

                // Certificato
                if ( $_FILES["FileCertificato"]["name"] != '' )
                {
                    // Elimina file precedente
                    if ($Utente->certificato_file != '' && file_exists(PUBLIC_PATH . $Utente->certificato_file)) 
                        unlink(PUBLIC_PATH . $Utente->certificato_file);

                    // CONTROLLI DI SICUREZZA UPLOAD FILE
                    $check_upload_file = $FunctionsClass->checkUploadFile($_FILES["FileCertificato"]["name"], $_FILES["FileCertificato"]["tmp_name"]);

                    if ($check_upload_file != "")
                        die($check_upload_file);

                    // Crea nome file certificato
                    $ext_var = explode(".", basename($_FILES["FileCertificato"]["name"]));
                    $ext = end($ext_var);
                    $path_certificato = "certificati/" . $Utente->ID . "-" . time() . "-" . bin2hex(openssl_random_pseudo_bytes(8)) . "." . $ext;

                    // Aggiorna file certificato
                    $AccountClass->AggiornaCertificatoUtente($path_certificato);

                    if (!move_uploaded_file($_FILES["FileCertificato"]["tmp_name"], PUBLIC_PATH . $path_certificato))
                    {
                        $utente_modificato = '<div class="text-danger text-center">Errore caricamento certificato!</div>';
                        $errore_controllo_files = 1;
                    }
                }

                // Autocertificazione minori 18 anni
                if ( $_FILES["FileAutocertificazioneMinori18Anni"]["name"] != '' )
                {
                    // Elimina file precedente
                    if 
                    (
                        $Utente->autocertificazione_minori_18_anni_file != '' && 
                        file_exists(PUBLIC_PATH . $Utente->autocertificazione_minori_18_anni_file)
                    )
                        unlink(PUBLIC_PATH . $Utente->autocertificazione_minori_18_anni_file);

                    // CONTROLLI DI SICUREZZA UPLOAD FILE
                    $check_upload_file = $FunctionsClass->checkUploadFile($_FILES["FileAutocertificazioneMinori18Anni"]["name"], $_FILES["FileAutocertificazioneMinori18Anni"]["tmp_name"]);

                    if ($check_upload_file != "")
                        die($check_upload_file);

                    // Crea nome file certificato
                    $ext_var = explode( ".", basename($_FILES["FileAutocertificazioneMinori18Anni"]["name"]) );
                    $ext = end($ext_var);
                    $path_autocertificazione_minori_18_anni = "autocertificazioni_minori_18_anni/" . $Utente->ID . "-" . time() . "-" . bin2hex(openssl_random_pseudo_bytes(8)) . "." . $ext;

                    // Aggiorna file certificato
                    $AccountClass->AggiornaAutocertificazioneMinori18AnniUtente($path_autocertificazione_minori_18_anni);

                    if (!move_uploaded_file($_FILES["FileAutocertificazioneMinori18Anni"]["tmp_name"], PUBLIC_PATH . $path_autocertificazione_minori_18_anni))
                    {
                        $utente_modificato = '<div class="text-danger text-center">Errore caricamento autocertificazione!</div>';
                        $errore_controllo_files = 1;
                    }
                }

                // Foto Profilo
                if ($_FILES["FileFotoProfilo"]["name"] != '' && $errore_controllo_files == 0)
                {
                    $nome_file_tmp = $_FILES["FileFotoProfilo"]["tmp_name"];
                    $nome_file = $_FILES["FileFotoProfilo"]["name"];

                    // Elimina file precedente
                    if ($Utente->foto_profilo != '' && file_exists(PUBLIC_PATH . $Utente->foto_profilo)) 
                        unlink(PUBLIC_PATH . $Utente->foto_profilo);

                    // CONTROLLI DI SICUREZZA UPLOAD FILE
                    $check_upload_file = $FunctionsClass->checkUploadFile($nome_file, $nome_file_tmp);
                    if ($check_upload_file != "") 
                        die($check_upload_file);

                    // Controllo che sia immagine
                    $check = getimagesize($nome_file_tmp);

                    if($check[0] < 4 || $check[1] < 4)
                        die("Il file caricato non è un immagine!");

                    // Ricrea immagine
                    $FunctionsClass->RicreaPNG($nome_file_tmp, 200, 200);

                    // Crea nome file foto profilo
                    $path_foto_profilo = "foto-profilo/" . $Utente->ID . "-" . time() . "-" . bin2hex(openssl_random_pseudo_bytes(8)) . ".png";

                    // Aggiorna file foto profilo
                    $AccountClass->AggiornaFotoProfiloUtente($path_foto_profilo);

                    if (!move_uploaded_file($nome_file_tmp, PUBLIC_PATH . $path_foto_profilo))
                    {
                        $utente_modificato = '<div class="text-danger text-center">Errore caricamento foto profilo!</div>';
                        $errore_controllo_files = 1;
                    }
                }

            }
            else
                $utente_modificato = '<div class="text-danger text-center">Email già in uso, modifiche non effettuate!</div>';
        }

        require("Gate.php"); // Per ricaricare dati utente, nel gate la variabile è la stessa

        $Utente = $AccountClass->getUtenteSessione();
        $ProvinceItaliane = $AccountClass->getListaProvinceItaliane();

        $ArrayTemplateAction = 
        [
            'Utente' => $Utente,
            'UtenteModificato' => $utente_modificato,
            'ProvinceItaliane' => $ProvinceItaliane,
            'Squadre' => $Squadre,
            'anno_data_nascita_massima' => $anno_data_nascita_massima
        ];

        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Account/editProfile.html', $ArrayTemplateCombined);
    }

    public function ForgotPassword()
    {
        session_start();

        $ArrayTemplateAction= [];
        $ArrayTemplateGate= ['URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER ];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Account/forgotPassword.html', $ArrayTemplateCombined);
    }

    public function ForgotPasswordConfirm()
    {
        session_start();

        if (isset($_POST['Email']))
        {
            $Email= $_POST['Email'];

            $AccountClass= new AccountModel();
            $FunctionsClass= new FunctionsModel();
            $Utente= $AccountClass->getUtenteDaEmail($Email);

            // Utente esistente
            if ($Utente)
            {
                // Eliminare vecchi codici di recupero
                $AccountClass->deleteRecuperoPassword($Email);

                // Crea codice di recupero
                if (defined('PHP_MAJOR_VERSION') && PHP_MAJOR_VERSION >= 7) 
                    $codice = bin2hex(random_bytes(64));
                else
                    $codice = bin2hex(openssl_random_pseudo_bytes(64));

                $AccountClass->insertRecuperoPassword($Email, $codice);

                //Invio la mail
                $Messaggio =
                '<html>
                <head>
                <meta charset="utf-8">
                <title>DnaRace: Password Reset</title>
                </head>
                <body>

                <p>Ciao ' . $Utente->nome . ', per impostare una nuova password, <a href="https://' . URL_TO_PUBLIC_FOLDER_NO_SLASHES . 'Account/resetPassword/' . $codice . '">clicca qui.</a></p>
                <p>Il link sarà disponibile solamente per un ora dall\'invio di questa email.</p>
                <p>Se il link non dovesse funzionare copiare e incollare nel browser il testo seguente:</p>
                <p>' . URL_TO_PUBLIC_FOLDER . 'Account/resetPassword/' . $codice . '</p>
                </body>
                </html>';

                $FunctionsClass->sendEmail($Email , '', 'DnaRace: Password Reset', $Messaggio);

                $ArrayTemplateAction= ['EsitoRecuperoPassword' => "Un email è stata inviata a " . $Email . ". Clicca il link ricevuto per impostarne una nuova!",
                                       'SuccessoRecuperoPassword' => 1];
            }
            else
                $ArrayTemplateAction= ['EsitoRecuperoPassword' => "Nessun utente trovato con questa email!",
                                       'SuccessoRecuperoPassword' => 0];

            $ArrayTemplateGate= ['URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER];
            $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

            View::renderTemplate('Account/forgotPasswordConfirm.html', $ArrayTemplateCombined);
        }
    }

    public function ResetPassword()
    {
        session_start();

        $codice_inserito= $this->route_params['codice'];

        $AccountClass= new AccountModel();
        $ConvalidaCodice= $AccountClass->checkCodiceResetPassword($codice_inserito);

        if ($ConvalidaCodice)
        {
            $_SESSION['CodiceResetEmail']= $codice_inserito;
            $ArrayTemplateAction= ['CodiceConvalidato' => 1];
        }
        else
            $ArrayTemplateAction= ['CodiceConvalidato' => 0];

        $ArrayTemplateGate= ['URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Account/resetPassword.html', $ArrayTemplateCombined);
    }

    public function ResetPasswordConfirm()
    {
        session_start();

        $CodiceSessione= $_SESSION['CodiceResetEmail'];

        if (isset($_POST['Password']))
        {
            $password= $_POST['Password'];

            $AccountClass= new AccountModel();
            $FunctionsClass= new FunctionsModel();
            $Codice= $AccountClass->checkCodiceResetPassword($CodiceSessione);

            // Email esistente da codice
            if ( isset($Codice->email) )
            {
                // Eliminare vecchi codici di recupero
                $AccountClass->deleteRecuperoPassword($Codice->email);

                // Aggiorna password
                $AccountClass->aggiornaPasswordUtenteDaEmail($Codice->email, $password);

                $ArrayTemplateAction= ['EsitoResetPassword' => "Password modificata!",
                                       'SuccessoResetPassword' => 1];
            }
            else
                $ArrayTemplateAction= ['EsitoResetPassword' => "Codice non valido o scaduto!",
                                       'SuccessoResetPassword' => 0];

            $ArrayTemplateGate= ['URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER];
            $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

            View::renderTemplate('Account/resetPasswordConfirm.html', $ArrayTemplateCombined);
        }
    }

    public function Promo()
    {
        require_once("Gate.php");

        $AccountClass = new AccountModel();
        $ProdottiClass = new ProdottiModel();
        $GareClass = new GareModel();

        $Utente = $AccountClass->getUtenteSessione();

        $Eventi = $GareClass->getAllGareAperteUtente($Utente->ID);

        $AcquistoMagliette = $GareClass->getAcquistoMaglietteUtente($Utente->ID);

        // Prodotti
        $Prodotti = $ProdottiClass->getAllProdottiConAttributi();

        $ArrayTemplateAction = 
        [
            'Utente' => $Utente, 
            'Prodotti' => $Prodotti,
            'AcquistoMagliette' => $AcquistoMagliette,
            'Eventi' => $Eventi
        ];

        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Account/promo.html', $ArrayTemplateCombined);
    }

    public function confermaSelezioneMagliette()
    {
        require_once("Gate.php");

        $AccountClass = new AccountModel();
        $ProdottiClass = new ProdottiModel();
        $GareClass = new GareModel();
        $AttributiProdottiClass = new AttributiProdottiModel();

        $Utente = $AccountClass->getUtenteSessione();
        $Eventi = $GareClass->getAllGareAperteUtente($Utente->ID);
        $AcquistoMagliette = $GareClass->getAcquistoMaglietteUtente($Utente->ID);

        $data_acquisto = $AcquistoMagliette->data_acquisto;
        $data_pagamento = $AcquistoMagliette->data_pagamento;
        $prezzo_acquisto_una_maglietta = $AcquistoMagliette->importo_totale / $AcquistoMagliette->quantita;

        if (isset($_POST['conferma_selezione_magliette_form']))
        {
            $ProdottiShop_ID = $_POST['ProdottiShop_ID'] ?? [];
            $ProdottiShop_Key = $_POST['ProdottiShop_Key'] ?? [];
            $ProdottiShop_Quantita = $_POST['ProdottiShop_Quantita'] ?? [];
            $ProdottiShop_Attributi = $_POST['ProdottiShop_Attributi'] ?? [];
            $ProdottiShop_Prezzo = $_POST['ProdottiShop_Prezzo'] ?? [];
            $evento_id = $_POST['evento_ritiro_maglietta'] ?? null;

            // Aggiunge prodotti
            foreach ($ProdottiShop_ID as $key => $prodotto_id)
            {
                // Tipologie disponibili:
                // 1. Magliette
                $tipologia_prodotto = $ProdottiClass->getTipologiaProdotto($prodotto_id);

                // Impostazione variabile acquisto_magliette_id
                $acquisto_magliette_id = 
                    $tipologia_prodotto == 1 ? 
                    $AcquistoMagliette->ID : 
                    null;

                // Inserisce prodotto
                $IDProdottoAcquisto = $ProdottiClass->inserisciProdottoAcquistato(
                    $prodotto_id,
                    $data_acquisto,
                    $data_pagamento,
                    $ProdottiShop_Quantita[$key],
                    $ProdottiShop_Prezzo[$key],
                    $prezzo_acquisto_una_maglietta,
                    $evento_id,
                    $acquisto_magliette_id
                );

                // Inserisce attributi prodotto
                $ProdottoKey = $ProdottiShop_Key[$key];
                $AttributiProdotto = $ProdottiShop_Attributi[$ProdottiShop_Key[$key]] ?? [];

                foreach ($AttributiProdotto as $attributo_opzione_id)
                    $AttributiProdottiClass->insertAcquistoProdottoAttributo($IDProdottoAcquisto, $attributo_opzione_id);
            }
        }

        $ArrayTemplateAction = [];

        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        header("location: ../Account/Profile");
    }

    public function cancellaIscrizione()
    {
        require_once('Gate.php');

        $AdminAccountClass = new AdminAccountModel();

        $iscrizione_id = $this->route_params['iscrizioneid'];

        $AdminAccountClass->cancellaIscrizione($iscrizione_id);

        header("location: ../Iscrizioni");
    }

    public function cancellaOpzioneAcquisto()
    {
        require_once('Gate.php');

        $AdminAccountClass = new AdminAccountModel();

        $opzione_acquisto_id = $this->route_params['opzioneacquistoid'];

        $AdminAccountClass->cancellaOpzioneAcquistoAcquistata($opzione_acquisto_id);

        header("location: ../Iscrizioni");
    }

    public function cancellaMagliette()
    {
        require_once('Gate.php');

        $AdminAccountClass = new AdminAccountModel();

        $acquisto_magliette_id = $this->route_params['acquistimaglietteid'];

        $AdminAccountClass->cancellaAcquistoMagliette($acquisto_magliette_id);

        header("location: ../Iscrizioni");
    }
}
