<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\AccountModel;
use \App\Models\AdminAccountModel;
use \App\Models\AdminGareModel;
use \App\Models\FunctionsModel;
use \App\Models\GareModel;
use \App\Models\StaffetteModel;
use \App\Models\SquadreModel;
use \App\Models\AdminGareIndividualiModel;
use \App\Models\OpzioniAcquistoModel;
use stdClass;
use \DateTime;

class AdminAccount extends \Core\Controller
{
    public function Login()
    {
        session_start();

        if ( isset($_SESSION['IDAdmin']) )
            header("location: ../AdminAccount/Profile");
        else
            session_destroy();

        $ArrayTemplateAction= [];
        $ArrayTemplateGate= ['URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/login.html', $ArrayTemplateCombined);
    }

    public function LoginConfirm()
    {
        $AdminAccountClass = new AdminAccountModel();

        if (isset($_POST['Username']) && isset($_POST['Password']))
        {
            $Username= $_POST['Username'];
            $Password= $_POST['Password'];

            $ControllaLogin= $AdminAccountClass->ControllaLogin($Username,$Password);

            if ($ControllaLogin)
            {
                session_start();
                $_SESSION['IDAdmin']= $ControllaLogin->ID;

                header("location: ../AdminAccount/Profile");
            }
            else
            {
                $ArrayTemplateAction= ['LoginFallito' => 1];
                $ArrayTemplateGate= ['URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER];
                $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

                View::renderTemplate('AdminAccount/login.html', $ArrayTemplateCombined);
            }
        }
    }

    public function Logout()
    {
        session_start();
        if(session_destroy())
            header("Location: ../AdminAccount/Login");
    }

    public function Profile()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/profile.html', $ArrayTemplateCombined);
    }

    public function Utenti()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/utenti.html', $ArrayTemplateCombined);
    }

    public function UtentiMinorenni()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/utenti_minorenni.html', $ArrayTemplateCombined);
    }

    public function Squadre()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/squadre.html', $ArrayTemplateCombined);
    }

    public function Iscrizioni()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/iscrizioni.html', $ArrayTemplateCombined);
    }

    public function IscrizioniAnno()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/iscrizioniAnno.html', $ArrayTemplateCombined);
    }

    public function IscrizioniDaPagare()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/iscrizioniDaPagare.html', $ArrayTemplateCombined);
    }

    public function IscrizioniGareIndividuali()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/iscrizioniGareIndividuali.html', $ArrayTemplateCombined);
    }

    public function IscrizioniAnnoGareIndividuali()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/iscrizioniAnnoGareIndividuali.html', $ArrayTemplateCombined);
    }

    public function AcquistiMagliette()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/acquisti_magliette.html', $ArrayTemplateCombined);
    }

    public function MaglietteDaPagare()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/magliette_da_pagare.html', $ArrayTemplateCombined);
    }

    public function ScaricaCSVIscrizioni()
    {
        require_once("AdminGate.php");

        $GareClass= new GareModel();
        $AdminGareClass= new AdminGareModel();
        $StaffetteClass= new StaffetteModel();

        $ListaGareAperte = $GareClass->getAllGareAperte(1);
        $ListaEventi= $AdminGareClass->getEventi();
        $ListaGareStaffette= $StaffetteClass->getAllStaffetteAperte();

        $ArrayTemplateAction= ['ListaGareAperte' => $ListaGareAperte,
                               'ListaEventi' => $ListaEventi,
                               'ListaGareStaffette' => $ListaGareStaffette];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/scaricaCSVIscrizioni.html', $ArrayTemplateCombined);
    }

    public function DettagliIscrizione()
    {
        require_once("AdminGate.php");

        $AdminAccountClass = new AdminAccountModel();
        $AccountClass = new AccountModel();

        $id_iscrizione= $this->route_params['id'];

        $Iscrizione= $AdminAccountClass->getIscrizioneDettagli($id_iscrizione);
        $UtenteIscrizione= $AccountClass->getUtente($Iscrizione->A_ID_utente);

        $Pagamento = $AdminAccountClass->getPagamentoDettagli($Iscrizione->A_IDPagamento);

        if ($Pagamento)
        {
            $ListaGare = $Pagamento->lista_gare != null ? explode('|', $Pagamento->lista_gare) : [];
            $ListaSconti = $Pagamento->lista_sconti != null ? explode('|', $Pagamento->lista_sconti) : [];
            $Pagamento->prezzo_boe = $AdminAccountClass->checkPagamentoPrezzoBoe($Iscrizione->A_IDPagamento);
            $Pagamento->sconto_staffette = $AdminAccountClass->checkPagamentoScontoStaffette($Iscrizione->A_IDPagamento, $Iscrizione->A_ID_utente);
        }
        else
        {
            $ListaGare = [];
            $ListaSconti = [];
        }
        

        //if ($Iscrizione->A_tipo_pagamento == 2) $Iscrizione->A_importo = number_format($Iscrizione->A_importo + MAGGIORAZIONE_PAYPAL, 2, '.', '');

        $ArrayTemplateAction= [
            'UtenteIscrizione' => $UtenteIscrizione,
            'Iscrizione' => $Iscrizione,
            'Pagamento' => $Pagamento,
            'ListaGare' => $ListaGare,
            'ListaSconti' => $ListaSconti
        ];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/dettagliIscrizione.html', $ArrayTemplateCombined);
    }

    public function ConfermaDettagliIscrizione()
    {
        require_once("AdminGate.php");

        $AdminAccountClass = new AdminAccountModel();

        if ( isset($_POST['ConvalidaPagamento']) )
        {
            $convalida = $_POST['ConvalidaPagamento'];
            $iscrizione_id = $_POST['IDIscrizione'];

            if ($convalida == 1) $AdminAccountClass->impostaIscrizioneComeIscritto($iscrizione_id);
            elseif ($convalida == 2) $AdminAccountClass->impostaIscrizioneComePagato($iscrizione_id);
            elseif ($convalida == 3) $AdminAccountClass->impostaIscrizioneComeConvalidato($iscrizione_id);
        }

        $ArrayTemplateAction = [];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/iscrizioni.html', $ArrayTemplateCombined);
    }

    public function CancellaIscrizione()
    {
        require_once("AdminGate.php");

        $AdminAccountClass = new AdminAccountModel();

        $id_iscrizione = $this->route_params['id'];
        $iscrizione_da_pagare = $this->route_params['iscrizionedapagare'];

        $AdminAccountClass->cancellaIscrizione($id_iscrizione);

        $ArrayTemplateAction = [];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        if ($iscrizione_da_pagare)
            View::renderTemplate('AdminAccount/iscrizioniDaPagare.html', $ArrayTemplateCombined);
        else    
            View::renderTemplate('AdminAccount/iscrizioni.html', $ArrayTemplateCombined);
    }

    public function CancellaIscrizioniDopoUnGiorno()
    {
        require_once("AdminGate.php");

        $AdminAccountClass= new AdminAccountModel();
        $AdminAccountClass->cancellaIscrizioniDopoUnGiorno();

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/iscrizioniDaPagare.html', $ArrayTemplateCombined);
    }

    public function CancellaIscrizioneGaraIndividuale()
    {
        require_once("AdminGate.php");

        $AdminAccountClass= new AdminAccountModel();

        $id_iscrizione= $this->route_params['id'];

        $AdminAccountClass->cancellaIscrizioneGaraIndividuale($id_iscrizione);

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/iscrizioniGareIndividuali.html', $ArrayTemplateCombined);
    }

    public function CancellaAcquistoMagliette()
    {
        require_once("AdminGate.php");

        $AdminAccountClass = new AdminAccountModel();

        $id_acquisto= $this->route_params['id'];
        $da_pagare = $this->route_params['dapagare'];

        $AdminAccountClass->cancellaAcquistoMagliette($id_acquisto);

        if ($da_pagare)
            header("location: " . URL_TO_PUBLIC_FOLDER . "/AdminAccount/MaglietteDaPagare");
        else
            header("location: " . URL_TO_PUBLIC_FOLDER . "/AdminAccount/AcquistiMagliette");
    }

    public function ModificaUtente()
    {
        require_once("AdminGate.php");

        $AccountClass = new AccountModel();
        $SquadreClass = new SquadreModel();

        $id_iscrizione= $this->route_params['id'];

        $Utente= $AccountClass->getUtente($id_iscrizione);
        $ProvinceItaliane= $AccountClass->getListaProvinceItaliane();
        $Squadre= $SquadreClass->getAllSquadre();

        $ArrayTemplateAction= ['Utente' => $Utente,
                               'ProvinceItaliane' => $ProvinceItaliane,
                               'Squadre' => $Squadre];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/modificaUtente.html', $ArrayTemplateCombined);
    }

    public function ConfermaModificaUtente()
    {
        require_once("AdminGate.php");

        $AccountClass = new AccountModel();
        $FunctionsClass = new FunctionsModel();

        $UtenteModificato= '';

        if (isset($_POST['Email']) && isset($_POST['Password']))
        {
            $IDUtente= $_POST['IDUtente'];
            $Utente= $AccountClass->getUtente($IDUtente);

            $Nome= $_POST['Nome'];
            $Cognome= $_POST['Cognome'];
            $Email= $_POST['Email'];
            $Password= $_POST['Password'];
            $Telefono= $_POST['Telefono'];
            $Sesso= $_POST['Sesso'];
            $CAP= $_POST['CAP'];
            $Comune= $_POST['Comune'];
            $Provincia= $_POST['Provincia'];
            $IDProvincia = $_POST['IDProvincia'];
            $Via= $_POST['Via'];
            $NumeroCivico= $_POST['NumeroCivico'];
            $DataNascita= $_POST['DataNascita'];
            $LuogoNascita= $_POST['LuogoNascita'];
            $GruppoSportivo= $_POST['GruppoSportivo'];
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
            $ScadenzaCertificato= $_POST['ScadenzaCertificato'];
            $CodiceFiscale= $_POST['CodiceFiscale'];
            $PaeseEstero= $_POST['PaeseEstero'];
            $IDSquadra= $_POST['IDSquadra'];
            $Credito= $_POST['Credito'];

            $ControllaCambioUtente= $AccountClass->ControllaCambioUtente($Email, $IDUtente);

            if ( $ControllaCambioUtente < 1)
            {
                // Aggiorna dati utente
                $AccountClass->AggiornaUtente( 
                    $Email,
                    $Nome,
                    $Cognome,
                    $Telefono,
                    $Sesso,
                    $CAP,
                    $Comune,
                    $Provincia,
                    $Via,
                    $NumeroCivico,
                    $DataNascita,
                    $LuogoNascita,
                    $GruppoSportivo,
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
                    $ScadenzaCertificato,
                    $IDUtente,
                    $CodiceFiscale,
                    $PaeseEstero,
                    $IDProvincia,
                    $IDSquadra);

                // Aggiorna password se cambiata
                if ($Password != '') $AccountClass->aggiornaPasswordUtente($Password, $IDUtente);

                // Aggiorna credito
                $AccountClass->AggiornaCreditoUtente($Credito, $IDUtente);

                $ErroreControlloFiles= 0;
                $UtenteModificato= '<div class="text-success text-center">Modifiche effettuate con successo!</div>';

                // Certificato
                if ( $_FILES["FileCertificato"]["name"] != '' )
                {
                    // Elimina file precedente
                    if ( $Utente->certificato_file != '' && file_exists(PUBLIC_PATH . $Utente->certificato_file)) unlink(PUBLIC_PATH . $Utente->certificato_file);

                    // CONTROLLI DI SICUREZZA UPLOAD FILE
                    $checkUploadFile= $FunctionsClass->checkUploadFile($_FILES["FileCertificato"]["name"], $_FILES["FileCertificato"]["tmp_name"]);
                    if ( $checkUploadFile != "") die($checkUploadFile);

                    // Crea nome file certificato
                    $ext_var= explode( ".", basename($_FILES["FileCertificato"]["name"]) );
                    $ext = end($ext_var);
                    $path_certificato = "certificati/" . $Utente->ID . "-" . time() . "-" . bin2hex(openssl_random_pseudo_bytes(8)) . "." . $ext;

                    // Aggiorna file certificato
                    $AccountClass->AggiornaCertificatoUtente($path_certificato, $IDUtente);

                    if (!move_uploaded_file($_FILES["FileCertificato"]["tmp_name"], PUBLIC_PATH . $path_certificato))
                    {
                        $UtenteModificato= '<div class="text-danger text-center">Errore caricamento certificato!</div>';
                        $ErroreControlloFiles= 1;
                    }
                }

                // Autocertificazione minori 18 anni
                if ( $_FILES["FileAutocertificazioneMinori18Anni"]["name"] != '' )
                {
                    // Elimina file precedente
                    if ( $Utente->autocertificazione_minori_18_anni_file != '' && file_exists(PUBLIC_PATH . $Utente->autocertificazione_minori_18_anni_file)) unlink(PUBLIC_PATH . $Utente->autocertificazione_minori_18_anni_file);

                    // CONTROLLI DI SICUREZZA UPLOAD FILE
                    $checkUploadFile= $FunctionsClass->checkUploadFile($_FILES["FileAutocertificazioneMinori18Anni"]["name"], $_FILES["FileAutocertificazioneMinori18Anni"]["tmp_name"]);
                    if ( $checkUploadFile != "") die($checkUploadFile);

                    // Crea nome file certificato
                    $ext_var= explode( ".", basename($_FILES["FileAutocertificazioneMinori18Anni"]["name"]) );
                    $ext = end($ext_var);
                    $path_autocertificazione_minori_18_anni = "autocertificazioni_minori_18_anni/" . $Utente->ID . "-" . time() . "-" . bin2hex(openssl_random_pseudo_bytes(8)) . "." . $ext;

                    // Aggiorna file certificato
                    $AccountClass->AggiornaAutocertificazioneMinori18AnniUtente($path_autocertificazione_minori_18_anni, $IDUtente);

                    if (!move_uploaded_file($_FILES["FileAutocertificazioneMinori18Anni"]["tmp_name"], PUBLIC_PATH . $path_autocertificazione_minori_18_anni))
                    {
                        $UtenteModificato= '<div class="text-danger text-center">Errore caricamento autocertificazione!</div>';
                        $ErroreControlloFiles= 1;
                    }
                }

                // Foto Profilo
                if ( $_FILES["FileFotoProfilo"]["name"] != '' && $ErroreControlloFiles == 0)
                {
                    $NomeFileTMP= $_FILES["FileFotoProfilo"]["tmp_name"];
                    $NomeFile= $_FILES["FileFotoProfilo"]["name"];

                    // Elimina file precedente
                    if ( $Utente->foto_profilo != '' && file_exists(PUBLIC_PATH . $Utente->foto_profilo)) unlink(PUBLIC_PATH . $Utente->foto_profilo);

                    // CONTROLLI DI SICUREZZA UPLOAD FILE
                    $checkUploadFile= $FunctionsClass->checkUploadFile($NomeFile, $NomeFileTMP);
                    if ( $checkUploadFile != "") die($checkUploadFile);

                    // Controllo che sia immagine
                    $check = getimagesize($NomeFileTMP);
                    if($check[0] < 4 || $check[1] < 4)
                      die("Il file caricato non è un immagine!");

                    // Ricrea immagine
                    $FunctionsClass->RicreaPNG($NomeFileTMP, 200, 200);

                    // Crea nome file foto profilo
                    $path_foto_profilo = "foto-profilo/" . $Utente->ID . "-" . time() . "-" . bin2hex(openssl_random_pseudo_bytes(8)) . ".png";

                    // Aggiorna file foto profilo
                    $AccountClass->AggiornaFotoProfiloUtente($path_foto_profilo, $IDUtente);

                    if (!move_uploaded_file($NomeFileTMP, PUBLIC_PATH . $path_foto_profilo))
                    {
                        $UtenteModificato= '<div class="text-danger text-center">Errore caricamento foto profilo!</div>';
                        $ErroreControlloFiles= 1;
                    }
                }

            }
            else
                $UtenteModificato= '<div class="text-danger text-center">Email già in uso, modifiche non effettuate!</div>';
        }

        $Utente= $AccountClass->getUtente($IDUtente);
        $ProvinceItaliane= $AccountClass->getListaProvinceItaliane();

        $ArrayTemplateAction= ['Utente' => $Utente,
                               'UtenteModificato' => $UtenteModificato,
                               'ProvinceItaliane' => $ProvinceItaliane];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/modificaUtente.html', $ArrayTemplateCombined);
    }

    public function CreaSquadra()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/creaSquadra.html', $ArrayTemplateCombined);
    }

    public function ConfermaCreaSquadra()
    {
        require_once("AdminGate.php");

        $SquadreClass = new SquadreModel();
        $FunctionsClass = new FunctionsModel();

        $SquadraModificata= '';

        $IDSquadraCreata= 0;

        if (isset($_POST['Nome']))
        {
            $Nome= $_POST['Nome'];

            // Aggiorna dati utente
            $IDSquadraCreata= $SquadreClass->CreaSquadra($Nome);

            $ErroreControlloFiles= 0;
            $SquadraModificata= '<div class="text-success text-center">Modifiche effettuate con successo!</div>';

            // Foto Profilo
            if ( $_FILES["FileLogoSquadra"]["name"] != '' && $ErroreControlloFiles == 0)
            {
                $NomeFileTMP= $_FILES["FileLogoSquadra"]["tmp_name"];
                $NomeFile= $_FILES["FileLogoSquadra"]["name"];

                // CONTROLLI DI SICUREZZA UPLOAD FILE
                $checkUploadFile= $FunctionsClass->checkUploadFile($NomeFile, $NomeFileTMP);
                if ( $checkUploadFile != "") die($checkUploadFile);

                // Controllo che sia immagine
                $check = getimagesize($NomeFileTMP);
                if($check[0] < 4 || $check[1] < 4)
                  die("Il file caricato non è un immagine!");

                // Ricrea immagine
                $FunctionsClass->RicreaPNG($NomeFileTMP, 200, 200);

                // Crea nome file foto
                $path_logo_squadra = "img/loghi-squadre/" . $IDSquadraCreata . "-" . time() . "-" . bin2hex(openssl_random_pseudo_bytes(8)) . ".png";

                // Aggiorna file foto profilo
                $SquadreClass->AggiornaLogoSquadra($IDSquadraCreata, $path_logo_squadra);

                if (!move_uploaded_file($NomeFileTMP, PUBLIC_PATH . $path_logo_squadra))
                {
                    $SquadraModificata= '<div class="text-danger text-center">Errore caricamento logo!</div>';
                    $ErroreControlloFiles= 1;
                }
            }
        }

        if ($IDSquadraCreata > 0)
        {
            $Squadra= $SquadreClass->getSquadra($IDSquadraCreata);

            $ArrayTemplateAction= ['Squadra' => $Squadra,
                                   'SquadraModificata' => $SquadraModificata];
            $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

            View::renderTemplate('AdminAccount/modificaSquadra.html', $ArrayTemplateCombined);
        }
        else
        {
            $ArrayTemplateAction= ['ErroreCreazioneSquadra' => '<div class="text-danger text-center">Errore creazione squadra!</div>'];
            $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

            View::renderTemplate('AdminAccount/creaSquadra.html', $ArrayTemplateCombined);
        }
        
    }

    public function ModificaSquadra()
    {
        require_once("AdminGate.php");

        $SquadreClass = new SquadreModel();
        
        $id_squadra = $this->route_params['id'];

        $Squadra = $SquadreClass->getSquadra($id_squadra);

        $ListaMembriSquadra = $SquadreClass->getMembriSquadra($id_squadra);

        $ArrayTemplateAction = ['Squadra' => $Squadra, 'ListaMembriSquadra' => $ListaMembriSquadra];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/modificaSquadra.html', $ArrayTemplateCombined);
    }

    public function ConfermaModificaSquadra()
    {
        require_once("AdminGate.php");

        $SquadreClass = new SquadreModel();
        $FunctionsClass = new FunctionsModel();

        $SquadraModificata = '';

        if (isset($_POST['Nome']))
        {
            $squadra_id = $_POST['IDSquadra'];
            $Squadra = $SquadreClass->getSquadra($squadra_id);

            $nome = $_POST['Nome'];
            $capo_squadra_id = $_POST['capo_squadra'];

            // Aggiorna dati utente
            $SquadreClass->AggiornaSquadra($squadra_id, $nome, $capo_squadra_id);

            $errore_controllo_files = 0;
            $squadra_modificata = '<div class="text-success text-center">Modifiche effettuate con successo!</div>';

            // Foto Profilo
            if ( $_FILES["FileLogoSquadra"]["name"] != '' && $errore_controllo_files == 0)
            {
                $nome_file_tmp = $_FILES["FileLogoSquadra"]["tmp_name"];
                $nome_file = $_FILES["FileLogoSquadra"]["name"];

                // Elimina file precedente
                if ($Squadra->foto != '' && file_exists(PUBLIC_PATH . $Squadra->foto)) unlink(PUBLIC_PATH . $Squadra->foto);

                // CONTROLLI DI SICUREZZA UPLOAD FILE
                $check_upload_file = $FunctionsClass->checkUploadFile($nome_file, $nome_file_tmp);
                if ( $check_upload_file != "") die($check_upload_file);

                // Controllo che sia immagine
                $check = getimagesize($nome_file_tmp);
                if($check[0] < 4 || $check[1] < 4)
                  die("Il file caricato non è un immagine!");

                // Ricrea immagine
                $FunctionsClass->RicreaPNG($nome_file_tmp, 200, 200);

                // Crea nome file foto
                $path_logo_squadra = "img/loghi-squadre/" . $Squadra->ID . "-" . time() . "-" . bin2hex(openssl_random_pseudo_bytes(8)) . ".png";

                // Aggiorna file foto profilo
                $SquadreClass->AggiornaLogoSquadra($squadra_id, $path_logo_squadra);

                if (!move_uploaded_file($nome_file_tmp, PUBLIC_PATH . $path_logo_squadra))
                {
                    $squadra_modificata = '<div class="text-danger text-center">Errore caricamento logo!</div>';
                    $errore_controllo_files = 1;
                }
            }
        }

        $Squadra = $SquadreClass->getSquadra($squadra_id);
        $ListaMembriSquadra = $SquadreClass->getMembriSquadra($squadra_id);

        $ArrayTemplateAction = 
        [
            'Squadra' => $Squadra,
            'SquadraModificata' => $squadra_modificata,
            'ListaMembriSquadra' => $ListaMembriSquadra
        ];

        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/modificaSquadra.html', $ArrayTemplateCombined);
    }

    public function ajaxTabellaIscrizioni()
    {
        $AdminAccountClass = new AdminAccountModel();

        if (isset($_GET))
        {
            $tipo= $this->route_params['tipo'];
            $da_pagare = $this->route_params['dapagare'] ?? 0;

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $Fields = 
            [
                "A_ID_utente",
                "A_nome_utente",
                "nome_squadra",
                "A_dataora",
                "A_nome_evento",
                "A_importo",
                "A_tipo_pagamento", 
                "A_stato_pagamento",
                "A_autocertificazione_minori_18_anni_file_utente"
            ];

            $order = $Fields[$column] . " " . $dir;

            $total_record = $AdminAccountClass->getIscrizioniAmount($tipo, $da_pagare);
            $filtered_record = $AdminAccountClass->getIscrizioniAmountFiltered($search, $tipo, $da_pagare);
            $Iscrizioni = $AdminAccountClass->getIscrizioniPagination(
                $order, 
                $start, 
                $length, 
                $search, 
                $tipo, 
                $da_pagare
            );
            
            $DataArray = [];

            foreach ($Iscrizioni as $Iscrizione)
            {
                $riga_importo = number_format($Iscrizione->A_importo, 2, '.', '');

                // Incrementa importo Paypal con costante MAGGIORAZIONE_PAYPAL
                //if ($Iscrizione->A_tipo_pagamento == 'Paypal') $riga_importo += MAGGIORAZIONE_PAYPAL;
                //$riga_importo= number_format($riga_importo, 2, '.', '');

                // Icone stato importo
                if ($Iscrizione->A_pagato == 1) $riga_importo .= '<span class="text-warning p-2">€</span>';
                if ($Iscrizione->A_convalidato == 1) $riga_importo .= '<i class="fa fa-check text-success p-2" aria-hidden="true"></i>';

                if ($Iscrizione->A_tipo_pagamento == 'Paypal') $riga_importo .= '<span class="text-danger">*</span>';

                // Nome utente
                $nome_cognome = preg_replace('/\'|\"/', '', $Iscrizione->A_nome_utente . ' ' . $Iscrizione->A_cognome_utente);

                $dettagli = '<a href="' . URL_TO_PUBLIC_FOLDER . 'AdminAccount/DettagliIscrizione/' . $Iscrizione->A_ID_iscrizione . '">
                              <button type="button" class="btn button-azzurro btn-block">Dettagli</button>
                            </a>';

                $cancella = '<a  href="#" data-toggle="modal" data-target="#cancellaIscrizioneModal"
                                onclick="cancellaIscrizione(' . $Iscrizione->A_ID_iscrizione . ', \'' .
                                                            $nome_cognome .'\', ' .
                                                            '\'' . $Iscrizione->A_nome_evento . ': ' . $Iscrizione->A_nome_gara  . '\')">
                                <button type="button" class="btn sfondo-rosso btn-block">Cancella</button>
                            </a>';

                $autocertificazione_minori_18_anni = '';

                $DataOdierna = new DateTime('now');
                $DataNascitaUtente = new DateTime($Iscrizione->A_data_nascita_utente);

                if ($DataOdierna->diff($DataNascitaUtente)->y < 18)
                {
                    if ($Iscrizione->A_autocertificazione_minori_18_anni_file_utente != '')
                        $autocertificazione_minori_18_anni .= '
                            <a href="' . URL_TO_PUBLIC_FOLDER . $Iscrizione->A_autocertificazione_minori_18_anni_file_utente .'" target="_blank">
                                <div class="text-center"><i class="fa fa-file-pdf fa-2x"></i></div>
                            </a>
                        ';
                    else
                        $autocertificazione_minori_18_anni .= '
                            <div class="text-center"><i class="fa fa-file-pdf fa-2x"></i></div>
                        ';
                }

                $Row = 
                [
                    'id_tessera' => $Iscrizione->A_ID_utente,
                    'nome_cognome' => $Iscrizione->A_nome_utente . ' ' . $Iscrizione->A_cognome_utente,
                    'nome_squadra' => $Iscrizione->nome_squadra,
                    'data_iscrizione' => $Iscrizione->A_dataora,
                    'nome_evento_gara' => $Iscrizione->A_nome_evento . ' - ' . $Iscrizione->A_nome_gara,
                    'importo' => $riga_importo,
                    'tipo' => $Iscrizione->A_tipo_pagamento,
                    'stato' => $Iscrizione->A_stato_pagamento,
                    'dettagli' => $dettagli,
                    'cancella' => $cancella,
                    'autocertificazione_minori_18_anni' => $autocertificazione_minori_18_anni
                ];

                $DataArray[] = $Row;
            }

            $Response = ['recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $DataArray];
            
            echo json_encode($Response);
        }
    }

    public function ajaxTabellaAcquistiMagliette()
    {
        $AdminAccountClass = new AdminAccountModel();
        $GareClass = new GareModel();

        if (isset($_GET))
        {
            $da_pagare = $this->route_params['dapagare'] ?? 0;

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $Fields = 
            [
                "A_am_dataora", 
                "A_utente_cognome", 
                "A_utente_nome", 
                "A_utente_email", 
                "A_evento_nome", 
                "A_pagamento_ID",
                "A_am_quantita", 
                "A_am_importo_totale"
            ];
            $order = $Fields[$column] . " " . $dir;

            $total_record = $AdminAccountClass->getAcquistiMaglietteAmount($da_pagare);
            $filtered_record = $AdminAccountClass->getAcquistiMaglietteAmountFiltered($search, $da_pagare);
            $AcquistiMagliette = $AdminAccountClass->getAcquistiMagliettePagination(
                $order, 
                $start, 
                $length, 
                $search, 
                $da_pagare
            );
            
            $DataArray = [];

            foreach ($AcquistiMagliette as $AcquistoMagliette)
            {
                // Calcola prima gara per consegna maglietta
                $ListaGareID = explode('|', $AcquistoMagliette->A_lista_gare_ID);
                $prima_gara = '';

                foreach ($ListaGareID as $key => $gara_id)
                {
                    $Gara = $GareClass->getGaraConDettagli($gara_id);

                    if ($Gara)
                    {
                        if ($key == 0)
                        {
                            $prima_data = $Gara->data_gara;
                            $prima_gara = $Gara->nome_evento . ' - ' . $Gara->nome_gara;
                        }
                        elseif ($Gara->data_gara < $prima_data)
                        {
                            $prima_data = $Gara->data_gara;
                            $prima_gara = $Gara->nome_evento . ' - ' . $Gara->nome_gara;
                        }
                    }
                }

                // Pagamento ID
                $pagamento_id =
                    $AcquistoMagliette->A_pagamento_ID > 0 ?
                    $AcquistoMagliette->A_pagamento_ID :
                    '
                    <a  href="#" data-toggle="modal" data-target="#cancellaAcqusitoMaglietteModal"
                        onclick="cancellaAcquistoMagliette(' .
                            $AcquistoMagliette->A_am_ID . ', \'' .
                            $AcquistoMagliette->A_utente_nome . ' ' . $AcquistoMagliette->A_utente_cognome  . '\')">
                        <button type="button" class="btn sfondo-rosso btn-block">Cancella</button>
                    </a>';

                $Row =
                [
                    'data' => $AcquistoMagliette->A_am_dataora,
                    'utente_cognome' => $AcquistoMagliette->A_utente_cognome,
                    'utente_nome' => $AcquistoMagliette->A_utente_nome,
                    'utente_email' => $AcquistoMagliette->A_utente_email,
                    'evento_gara_nome' => $prima_gara,
                    'pagamento_id' => $pagamento_id,
                    'quantita' => $AcquistoMagliette->A_am_quantita,
                    'importo_totale' => $AcquistoMagliette->A_am_importo_totale
                ];

                $DataArray[] = $Row;
            }

            $Response = ['recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $DataArray];

            echo json_encode($Response);
        }
    }

    public function ajaxTabellaIscrizioniGareIndividuali()
    {
        $AdminAccountClass = new AdminAccountModel();

        if (isset($_GET))
        {
            $tipo= $this->route_params['tipo'];

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $field = array("A_ID_utente","u.nome","i.dataora","e.nome","A_importo","A_tipo_pagamento", "A_stato_pagamento");
            $order = $field[$column]." ".$dir;

            $total_record    = $AdminAccountClass->getIscrizioniGareIndividualiAmount($tipo);
            $filtered_record = $AdminAccountClass->getIscrizioniGareIndividualiAmountFiltered($tipo, $search);
            $Iscrizioni = $AdminAccountClass->getIscrizioniGareIndividualiPagination($tipo, $order, $start, $length, $search);
            
            $data_array = array();

            foreach ($Iscrizioni as $iscrizione)
            {
                $riga_importo= number_format($iscrizione->A_importo, 2, '.', '');

                // Incrementa importo Paypal con costante MAGGIORAZIONE_PAYPAL
                //if ($iscrizione->A_tipo_pagamento == 'Paypal') $riga_importo += MAGGIORAZIONE_PAYPAL;
                //$riga_importo= number_format($riga_importo, 2, '.', '');

                // Icone stato importo
                if ($iscrizione->A_pagato == 1) $riga_importo .= '<span class="text-warning p-2">€</span>';
                if ($iscrizione->A_convalidato == 1) $riga_importo .= '<i class="fa fa-check text-success p-2" aria-hidden="true"></i>';

                if ($iscrizione->A_tipo_pagamento == 'Paypal') $riga_importo .= '<span class="text-danger">*</span>';

                $dettagli= '<a href="' . URL_TO_PUBLIC_FOLDER . 'AdminAccount/DettagliIscrizioneGaraIndividuale/' . $iscrizione->A_ID_iscrizione . '">
                              <button type="button" class="btn button-azzurro btn-block">Dettagli</button>
                            </a>';

                $cancella= '<a  href="#" data-toggle="modal" data-target="#cancellaIscrizioneGaraIndividualeModal"
                                onclick="cancellaIscrizioneGaraIndividuale(' .
                                    $iscrizione->A_ID_iscrizione . ', \'' .
                                    $iscrizione->A_nome_utente . ' ' . $iscrizione->A_cognome_utente .'\', ' .
                                    '\'' . $iscrizione->A_nome_evento . ': ' . $iscrizione->A_nome_gara  . '\')">
                                <button type="button" class="btn sfondo-rosso btn-block">Cancella</button>
                            </a>';

                $row = array(
                    'id_tessera' => $iscrizione->A_ID_utente,
                    'nome_cognome' => $iscrizione->A_nome_utente . ' ' . $iscrizione->A_cognome_utente,
                    'data_iscrizione' => $iscrizione->A_dataora,
                    'nome_evento_gara' => $iscrizione->A_nome_evento . ' - ' . $iscrizione->A_nome_gara,
                    'importo' => $riga_importo,
                    'tipo' => $iscrizione->A_tipo_pagamento,
                    'stato' => $iscrizione->A_stato_pagamento,
                    'dettagli' => $dettagli,
                    'cancella' => $cancella
                );

                array_push($data_array, $row);
            }

            $arr = array('recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $data_array);
            echo json_encode($arr);
        }
    }


    public function DettagliIscrizioneGaraIndividuale()
    {
        require_once("AdminGate.php");

        $AdminGareIndividualiClass = new AdminGareIndividualiModel();
        $AccountClass = new AccountModel();

        $id_iscrizione= $this->route_params['id'];

        $Iscrizione= $AdminGareIndividualiClass->getIscrizioneDettagli($id_iscrizione);
        $UtenteIscrizione= $AccountClass->getUtente($Iscrizione->IGI_ID_utente);

        //if ($Iscrizione->A_tipo_pagamento == 2) $Iscrizione->A_importo = number_format($Iscrizione->A_importo + MAGGIORAZIONE_PAYPAL, 2, '.', '');

        $ArrayTemplateAction= ['UtenteIscrizione' => $UtenteIscrizione,
                               'Iscrizione' => $Iscrizione];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/dettagliIscrizioneGaraIndividuale.html', $ArrayTemplateCombined);
    }

    public function ConfermaDettagliIscrizioneGaraIndividuale()
    {
        require_once("AdminGate.php");

        $AdminGareIndividualiClass= new AdminGareIndividualiModel();

        if ( isset($_POST['ConvalidaPagamento']) )
        {
            $Convalida= $_POST['ConvalidaPagamento'];
            $IDIscrizione= $_POST['IDIscrizione'];

            if ($Convalida == 1) $AdminGareIndividualiClass->impostaIscrizioneComeIscritto($IDIscrizione);
            elseif ($Convalida == 2) $AdminGareIndividualiClass->impostaIscrizioneComePagato($IDIscrizione);
            elseif ($Convalida == 3) $AdminGareIndividualiClass->impostaIscrizioneComeConvalidato($IDIscrizione);
        }

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/iscrizioniGareIndividuali.html', $ArrayTemplateCombined);
    }

    public function ajaxUtenti()
    {
        $AdminAccountClass = new AdminAccountModel();

        if (isset($_GET))
        {
            $is_minorenne = $this->route_params['isminorenne'];

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $field = array("ID","nome","cognome","email");

            $order = $field[$column]." ".$dir;

            $total_record    = $AdminAccountClass->getUtentiAmount($is_minorenne);
            $filtered_record = $AdminAccountClass->getUtentiAmountFiltered($search, $is_minorenne);
            $Utenti = $AdminAccountClass->getUtentiPagination($order, $start, $length, $search, $is_minorenne);
            
            $data_array = array();

            foreach ($Utenti as $Utente)
            {
                if ($is_minorenne)
                {
                    $dettagli = '
                        <div class="row">
                            <div class="col-8">
                                <a href="' . URL_TO_PUBLIC_FOLDER . 'AdminAccount/ModificaUtente/' . $Utente->ID . '">
                                  <button type="button" class="btn button-azzurro btn-block">Modifica</button>
                                </a>
                            </div>';

                    if ($Utente->autocertificazione_minori_18_anni_file != '')
                        $dettagli .= '
                            <div class="col-4">
                                <a href="' . URL_TO_PUBLIC_FOLDER . $Utente->autocertificazione_minori_18_anni_file .'" target="_blank">
                                    <div class="text-center"><i class="fa fa-file-pdf fa-2x"></i></div>
                                </a>
                            </div>';
                    else
                        $dettagli .= '
                            <div class="col-4">
                                <div class="text-center"><i class="fa fa-file-pdf fa-2x"></i></div>
                            </div>';

                    $dettagli .= '
                        </div>';
                }
                else 
                    $dettagli= '
                        <a href="' . URL_TO_PUBLIC_FOLDER . 'AdminAccount/ModificaUtente/' . $Utente->ID . '">
                          <button type="button" class="btn button-azzurro btn-block">Modifica</button>
                        </a>';
                

                $row = array(
                    'id_tessera' => $Utente->ID,
                    'nome' => $Utente->nome,
                    'cognome' => $Utente->cognome,
                    'email' => $Utente->email,
                    'dettagli' => $dettagli
                );

                array_push($data_array, $row);
            }

            $arr = array('recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $data_array);
            echo json_encode($arr);
        }
    }

    public function ajaxSquadre()
    {
        $SquadreClass = new SquadreModel();

        if (isset($_GET))
        {
            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $field = array("ID","nome");
            $order = $field[$column]." ".$dir;

            $total_record    = $SquadreClass->getSquadreAmount();
            $filtered_record = $SquadreClass->getSquadreAmountFiltered($search);
            $Squadre = $SquadreClass->getSquadrePagination($order,$start,$length,$search);
            
            $data_array = array();

            foreach ($Squadre as $squadra)
            {
                $modifica= '<a href="' . URL_TO_PUBLIC_FOLDER . 'AdminAccount/ModificaSquadra/' . $squadra->ID . '">
                              <button type="button" class="btn button-azzurro btn-block">Modifica</button>
                            </a>';

                $row = array(
                    'id' => $squadra->ID,
                    'nome' => $squadra->nome,
                    'modifica' => $modifica
                );

                array_push($data_array, $row);
            }

            $arr = array('recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $data_array);
            echo json_encode($arr);
        }
    }

    public function ajaxGeneraCSVIscrizioniPerGara()
    {
        require_once("AdminGate.php");

        $AdminAccountClass= new AdminAccountModel();
        $AdminGareClass= new AdminGareModel();
        $GareClass= new GareModel();

        // Per Gara
        if (isset($_POST['GeneraCSV']))
        {
            $GeneraCSV= $_POST['GeneraCSV'];

            // Per Gara o Evento
            if ($GeneraCSV == 'Gara')
            {
                $IscrizioniEstratte= $AdminAccountClass->getAllIscrizioniPerGara($_POST['IDGara']);
                $NomeGara= $AdminGareClass->getGara($_POST['IDGara'])->nome;
                $NomeFile= 'Iscrizioni_' . $GeneraCSV . '_' . $NomeGara;

                $CSV= 'Evento; Gara; Data Iscrizione; ID Tessera; Cognome; Nome; Sesso; Gruppo Sportivo; N° Boa; Scadenza Certificato; Pagamento; Email; Telefono; Stato; Donatore Avis; Pagamento Boa' . PHP_EOL;

                $CSV= 'Evento; Gara; ID Tessera; Cognome; Nome; Sesso; Taglia T-Shirt; Data Di Nascita; Gruppo Sportivo; Email; Telefono; Data Iscrizione; Donatore Avis; Certificato Caricato; Scadenza Certificato; Pagamento Boa; Pagamento; Indirizzo; N° Civico; CAP; Località; Provincia; Luogo Di Nascita; N° Boa; Stato  ' . PHP_EOL;

                foreach ($IscrizioniEstratte as $iscrizione)
                {
                    $CSV .= $iscrizione->A_nome_evento . ';' .
                            $iscrizione->A_nome_gara . ';' .
                            $iscrizione->A_ID_utente . ';' .
                            $iscrizione->A_cognome_utente . ';' .
                            $iscrizione->A_nome_utente . ';' .
                            $iscrizione->A_sesso_utente . ';' .
                            $iscrizione->A_taglia_maglietta . ';' .
                            $iscrizione->A_data_nascita . ';' .
                            $iscrizione->SQA_nome . ';' .
                            $iscrizione->A_email_utente . ';' .
                            $iscrizione->A_telefono_utente . ';' .
                            $iscrizione->A_dataora . ';';
                            PHP_EOL;

                    $FlagFileCertificato= $iscrizione->A_certificato_file != '' ? '1' : '0';

                    $CSV .= $iscrizione->A_donatore_avis_utente . ';' .
                            $FlagFileCertificato . ';' .
                            $iscrizione->A_certificato_scadenza_utente . ';' .
                            $iscrizione->A_pagato_boa . ';' .
                            '€ ' . $iscrizione->A_importo . ';' .
                            $iscrizione->A_via . ';' .
                            $iscrizione->A_numero_civico . ';' .
                            $iscrizione->A_cap . ';' .
                            $iscrizione->A_comune . ';' .
                            $iscrizione->A_provincia . ';' .
                            $iscrizione->A_luogo_nascita . ';' .
                            $iscrizione->A_boa_numero_utente . ';' .
                            $iscrizione->A_pagato . ';' .
                            PHP_EOL;

                }
            }
            elseif ($GeneraCSV == 'Evento')
            {
                $IscrizioniEstratte= $AdminAccountClass->getAllIscrizioniPerEvento($_POST['IDEvento']);
                $NomeEvento= $AdminGareClass->getEvento($_POST['IDEvento'])->nome;
                $NomeFile= 'Iscrizioni_' . $GeneraCSV . '_' . $NomeEvento;

                $GareEvento= $AdminGareClass->getGareEvento($_POST['IDEvento']);

                $CSV= 'Evento; ID; Cognome; Nome; Sesso; Taglia; Data_Nascita; Gruppo; Email; Telefono; Data_Iscrizione;';

                // Assegna gare a tipo
                $GaraCorta = $GaraMedia = $GaraLunga = $GaraStaffetta = $GaraKmLanciato = null;

                foreach ($GareEvento as $gara)
                {
                    if ($gara->combinata == 0)
                    {
                        if ($gara->staffetta == 1)
                            $GaraStaffetta = $gara;
                        elseif ($gara->tipo == 1)
                            $GaraCorta = $gara;
                        elseif ($gara->tipo == 2)
                            $GaraMedia = $gara;
                        elseif ($gara->tipo == 3)
                            $GaraLunga = $gara;
                        elseif ($gara->tipo == 4)
                            $GaraKmLanciato = $gara;
                    }
                }

                // Gara corta
                if ($GaraCorta)
                    $CSV .= $GaraCorta->nome . ';';
                else
                    $CSV .= 'Non trovata;';

                // Gara media
                if ($GaraMedia)
                    $CSV .= $GaraMedia->nome . ';';
                else
                    $CSV .= 'Non trovata;';

                // Gara lunga
                if ($GaraLunga)
                    $CSV .= $GaraLunga->nome . ';';
                else
                    $CSV .= 'Non trovata;';

                // Gara Staffetta
                if ($GaraStaffetta)
                    $CSV .= $GaraStaffetta->nome . ';';
                else
                    $CSV .= 'Non trovata;';

                // Gara KmLanciato
                if ($GaraKmLanciato)
                    $CSV .= $GaraKmLanciato->nome . ';';
                else
                    $CSV .= 'Non trovata;';


                $CSV .= 'Staffetta; Avis; Certificato; Scadenza_Certificato; Boa; Importo; Indirizzo; Numero_Civico; CAP; Localita; Provincia; Luogo_Nascita; Codice_Fiscale; Estero  ' . PHP_EOL;

                foreach ($IscrizioniEstratte as $iscrizione)
                {
                    $Staffetta= $AdminAccountClass->getStaffettaUtenteDaEvento($iscrizione->A_ID_utente, $_POST['IDEvento']);
                    $NomeStaffetta = $Staffetta ? $Staffetta->A_nome_staffetta : '';
                    $boa_pagata = $AdminAccountClass->getPagamentoBoa($_POST['IDEvento'], $iscrizione->A_ID_utente);

                    $CSV .= $iscrizione->A_nome_evento . ';' .
                            $iscrizione->A_ID_utente . ';' .
                            $iscrizione->A_cognome_utente . ';' .
                            $iscrizione->A_nome_utente . ';' .
                            $iscrizione->A_sesso_utente . ';' .
                            $iscrizione->A_taglia_maglietta . ';' .
                            $iscrizione->A_data_nascita . ';' .
                            $iscrizione->SQA_nome . ';' .
                            $iscrizione->A_email_utente . ';' .
                            $iscrizione->A_telefono_utente . ';' .
                            $iscrizione->A_max_data_ora . ';' ;
                            

                    $GareIscrizione= explode(',', $iscrizione->A_ID_gare);

                    // Aggiunge da combinate
                    $ArrayDaCombinate= [];

                    foreach ($GareEvento as $gara)
                    {
                        if ( $gara->combinata == 1 && in_array($gara->ID, $GareIscrizione) )
                        {
                            $GareCollegate= $GareClass->getAllGareDaCombinata($gara->ID);

                            foreach ($GareCollegate as $gara_collegata)
                            {
                                if (!in_array($gara_collegata->ID_gara_collegata, $ArrayDaCombinate))
                                    $ArrayDaCombinate[]= $gara_collegata->ID_gara_collegata;
                            }
                        }
                    }

                    // Flagga gara corta
                    if ($GaraCorta)
                    {
                        if ( in_array($GaraCorta->ID, $GareIscrizione) || in_array($GaraCorta->ID, $ArrayDaCombinate) )
                            $CSV .= '1;';
                        else
                            $CSV .= '0;';
                    }
                    else
                        $CSV .= ';';

                    // Flagga gara media
                    if ($GaraMedia)
                    {
                        if ( in_array($GaraMedia->ID, $GareIscrizione) || in_array($GaraMedia->ID, $ArrayDaCombinate) )
                            $CSV .= '1;';
                        else
                            $CSV .= '0;';
                    }
                    else
                        $CSV .= ';';

                    // Flagga gara lunga
                    if ($GaraLunga)
                    {
                        if ( in_array($GaraLunga->ID, $GareIscrizione) || in_array($GaraLunga->ID, $ArrayDaCombinate) )
                            $CSV .= '1;';
                        else
                            $CSV .= '0;';
                    }
                    else
                        $CSV .= ';';

                    // Flagga gara staffetta
                    if ($GaraStaffetta)
                    {
                        if ( in_array($GaraStaffetta->ID, $GareIscrizione) || in_array($GaraStaffetta->ID, $ArrayDaCombinate) )
                            $CSV .= '1;';
                        else
                            $CSV .= '0;';
                    }
                    else
                        $CSV .= ';';

                    // Flagga gara Kmlancio
                    if ($GaraKmLanciato)
                    {
                        if ( in_array($GaraKmLanciato->ID, $GareIscrizione) || in_array($GaraKmLanciato->ID, $ArrayDaCombinate) )
                            $CSV .= '1;';
                        else
                            $CSV .= '0;';
                    }
                    else
                        $CSV .= ';';

                    $FlagFileCertificato = $iscrizione->A_certificato_file != '' ? '1' : '0';
                    $PaeseEstero = $iscrizione->A_paese_estero != '' ? '1' : '0';
                    $CodiceFiscale = $iscrizione->A_paese_estero == '' ? $iscrizione->A_codice_fiscale : '';
                    $FlagBoaPagata = $boa_pagata != false ? '1' : '0';

                    $CSV .= $NomeStaffetta . ';' .
                            $iscrizione->A_donatore_avis_utente . ';' .
                            $FlagFileCertificato . ';' .
                            $iscrizione->A_certificato_scadenza_utente . ';' .
                            $FlagBoaPagata . ';' .
                            '€ ' . number_format($iscrizione->A_importo, 2, '.', '') . ';' .
                            $iscrizione->A_via . ';' .
                            $iscrizione->A_numero_civico . ';' .
                            $iscrizione->A_cap . ';' .
                            $iscrizione->A_comune . ';' .
                            $iscrizione->A_provincia . ';' .
                            $iscrizione->A_luogo_nascita . ';' .
                            $iscrizione->A_codice_fiscale . ';' .
                            $PaeseEstero . ';' .
                            PHP_EOL;
                }
            }
            else die();

            $NomeFileFull= 'temp/' . preg_replace( '/[^a-z0-9]+/', '-', strtolower( $NomeFile ) ) . '.csv';

            // $CSV= iconv("UTF-8", "CP1252//IGNORE", $CSV);

            if (count($IscrizioniEstratte) > 0)
            {
                $FileCSV = fopen($NomeFileFull, "w") or die("Unable to open file!");
                fwrite($FileCSV, $CSV);
                fclose($FileCSV);
                $Risposta['Trovate']= 1;
            }
            else
                $Risposta['Trovate']= 0;

            $Risposta['LinkCSV'] = URL_TO_PUBLIC_FOLDER . $NomeFileFull;

            echo json_encode($Risposta);
        }
    }

    public function ajaxGeneraCSVNewsletterPerGara()
    {
        require_once("AdminGate.php");

        $AdminAccountClass = new AdminAccountModel();
        $AdminGareClass = new AdminGareModel();
        $GareClass = new GareModel();

        // Array per la sostituzione delle lettere accentate all'interno di nome e cognome di un iscritto
        $LettereAccentate = ['à', 'é', 'è', 'ì', 'ò', 'ù', '\''];
        $LettereNonAccentate = ['a', 'e', 'e', 'i', 'o', 'u', ' '];

        // Per Gara
        if (isset($_POST['genera_csv']))
        {
            $genera_csv = $_POST['genera_csv'];

            // Per Gara o Evento
            if ($genera_csv == 'Gara')
            {
                $IscrizioniEstratte = $AdminAccountClass->getAllIscrizioniPerGara($_POST['id_gara']);
                $NomeGara = $AdminGareClass->getGara($_POST['id_gara'])->nome;
                $nome_file = 'Newsletter_' . $genera_csv . '_' . $NomeGara;

                $csv = 'Cognome; Nome; Sesso; Email; Telefono' . PHP_EOL;

                foreach ($IscrizioniEstratte as $iscrizione)
                {
                    $csv .= str_replace($LettereAccentate, $LettereNonAccentate, $iscrizione->A_cognome_utente) . ';' .
                            str_replace($LettereAccentate, $LettereNonAccentate, $iscrizione->A_nome_utente) . ';' .
                            $iscrizione->A_sesso_utente . ';' .
                            $iscrizione->A_email_utente . ';' .
                            $iscrizione->A_telefono_utente . ';' .
                            PHP_EOL;
                }
            }
            elseif ($genera_csv == 'Evento')
            {
                $IscrizioniEstratte = $AdminAccountClass->getAllIscrizioniPerEvento($_POST['id_evento']);
                $NomeEvento = $AdminGareClass->getEvento($_POST['id_evento'])->nome;
                $nome_file = 'Newsletter_' . $genera_csv . '_' . $NomeEvento;

                $GareEvento = $AdminGareClass->getGareEvento($_POST['id_evento']);

                $csv = 'Cognome; Nome; Sesso; Email; Telefono;' . PHP_EOL;

                foreach ($IscrizioniEstratte as $iscrizione)
                {

                    $csv .= str_replace($LettereAccentate, $LettereNonAccentate, $iscrizione->A_cognome_utente) . ';' .
                            str_replace($LettereAccentate, $LettereNonAccentate, $iscrizione->A_nome_utente) . ';' .
                            $iscrizione->A_sesso_utente . ';' .
                            $iscrizione->A_email_utente . ';' .
                            $iscrizione->A_telefono_utente . ';' .
                            PHP_EOL;
                }
            }
            else die();

            $nome_file_full = 'temp/' . preg_replace( '/[^a-z0-9]+/', '-', strtolower( $nome_file ) ) . '.csv';

            $csv = iconv("UTF-8", "CP1252//IGNORE", $csv);

            if (count($IscrizioniEstratte) > 0)
            {
                $file_csv = fopen($nome_file_full, "w") or die("Unable to open file!");
                fwrite($file_csv, $csv);
                fclose($file_csv);
                $Risposta['Trovate']= 1;
            }
            else
                $Risposta['Trovate']= 0;

            $Risposta['LinkCSV'] = URL_TO_PUBLIC_FOLDER . $nome_file_full;

            echo json_encode($Risposta);
        }
    }

    public function ajaxGeneraCSVStaffetteGara()
    {
        require_once("AdminGate.php");

        $StaffetteClass= new StaffetteModel();
        $AdminGareClass= new AdminGareModel();

        // Per Gara
        if (isset($_POST['GeneraCSV']))
        {
            $IDGaraStaffetta= $_POST['IDGaraStaffetta'];
            $Staffette= $StaffetteClass->getAllStaffetteGara($IDGaraStaffetta);

            $CSV= 'NOME STAFFETTA;' .
                  'ID 1; COGNOME 1; NOME 1; SESSO 1; DATA DI NASCITA 1; TEAM 1;' .
                  'ID 2; COGNOME 2; NOME 2; SESSO 2; DATA DI NASCITA 2; TEAM 2;' .
                  'ID 3; COGNOME 3; NOME 3; SESSO 3; DATA DI NASCITA 3; TEAM 3' .
                   PHP_EOL;

            foreach ($Staffette as $staffetta)
            {
                // Estrae Nomi
                $NomiStaffetta= explode( ',', $staffetta->U_nome);

                $Nome1= isset($NomiStaffetta[0]) ? $NomiStaffetta[0] : '';
                $Nome2= isset($NomiStaffetta[1]) ? $NomiStaffetta[1] : '';
                $Nome3= isset($NomiStaffetta[2]) ? $NomiStaffetta[2] : '';

                // Estrae Cognomi
                $CognomiStaffetta= explode( ',', $staffetta->U_cognome);

                $Cognome1= isset($CognomiStaffetta[0]) ? $CognomiStaffetta[0] : '';
                $Cognome2= isset($CognomiStaffetta[1]) ? $CognomiStaffetta[1] : '';
                $Cognome3= isset($CognomiStaffetta[2]) ? $CognomiStaffetta[2] : '';

                // Estrae ID Tessera
                $IDTesseraStaffetta= explode( ',', $staffetta->U_ID);

                $IDTessera1= isset($IDTesseraStaffetta[0]) ? $IDTesseraStaffetta[0] : '';
                $IDTessera2= isset($IDTesseraStaffetta[1]) ? $IDTesseraStaffetta[1] : '';
                $IDTessera3= isset($IDTesseraStaffetta[2]) ? $IDTesseraStaffetta[2] : '';

                // Estrae Sesso
                $SessoStaffetta= explode( ',', $staffetta->U_sesso);

                $Sesso1= isset($SessoStaffetta[0]) ? $SessoStaffetta[0] : '';
                $Sesso2= isset($SessoStaffetta[1]) ? $SessoStaffetta[1] : '';
                $Sesso3= isset($SessoStaffetta[2]) ? $SessoStaffetta[2] : '';

                // Estrae Date Di Nascita
                $DateNascitaStaffetta= explode( ',', $staffetta->U_data_nascita);

                $DataNascita1= isset($DateNascitaStaffetta[0]) ? $DateNascitaStaffetta[0] : '';
                $DataNascita2= isset($DateNascitaStaffetta[1]) ? $DateNascitaStaffetta[1] : '';
                $DataNascita3= isset($DateNascitaStaffetta[2]) ? $DateNascitaStaffetta[2] : '';

                // Estrae Team (Gruppo Sportivo)
                $GruppoSportivoStaffetta= explode( ',', $staffetta->U_gruppo_sportivo);

                $GruppoSportivo1= isset($GruppoSportivoStaffetta[0]) ? $GruppoSportivoStaffetta[0] : '';
                $GruppoSportivo2= isset($GruppoSportivoStaffetta[1]) ? $GruppoSportivoStaffetta[1] : '';
                $GruppoSportivo3= isset($GruppoSportivoStaffetta[2]) ? $GruppoSportivoStaffetta[2] : '';


                $CSV .= $staffetta->S_nome . ';' .
                        $IDTessera1 . ';' .
                        $Cognome1 . ';' .
                        $Nome1 . ';' .
                        $Sesso1 . ';' .
                        $DataNascita1 . ';' .
                        $GruppoSportivo1 . ';' .
                        $IDTessera2 . ';' .
                        $Cognome2 . ';' .
                        $Nome2 . ';' .
                        $Sesso2 . ';' .
                        $DataNascita2 . ';' .
                        $GruppoSportivo2 . ';' .
                        $IDTessera3 . ';' .
                        $Cognome3 . ';' .
                        $Nome3 . ';' .
                        $Sesso3 . ';' .
                        $DataNascita3 . ';' .
                        $GruppoSportivo3 . ';' .
                        PHP_EOL;
            }

            $NomeGara= $AdminGareClass->getGara($_POST['IDGaraStaffetta'])->nome;
            $NomeFile= 'ListaStaffette_Gara_' . $NomeGara;
            $NomeFileFull= 'temp/' . preg_replace( '/[^a-z0-9]+/', '-', strtolower( $NomeFile ) ) . '.csv';

            $CSV= iconv("UTF-8", "CP1252//TRANSLIT", $CSV);

            if (count($Staffette) > 0)
            {
                $FileCSV = fopen($NomeFileFull, "w") or die("Unable to open file!");
                fwrite($FileCSV, $CSV);
                fclose($FileCSV);
                $Risposta['Trovate']= 1;
            }
            else
                $Risposta['Trovate']= 0;

            $Risposta['LinkCSV'] = URL_TO_PUBLIC_FOLDER . $NomeFileFull;

            echo json_encode($Risposta);
        }
    }

    public function iscrizioniSquadre()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction = [];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/iscrizioni_squadre.html', $ArrayTemplateCombined);
    }

    public function ajaxTabellaIscrizioniSquadre()
    {
        $squadra_id = $this->route_params['id'];

        if ($squadra_id)
            require_once("Gate.php");
        else
            require_once("AdminGate.php");

        $AdminAccountClass = new AdminAccountModel();

        if (isset($_GET))
        {
            $column =  $_GET['order'][0]['column'];
            $dir =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $Fields = 
            [
                "id",
                "ids.data",
                "s.nome",
                "capo_squadra"
            ];

            $order = $Fields[$column] . " " . $dir;

            $total_record = $AdminAccountClass->getIscrizioniSquadreAmount($squadra_id);
            $filtered_record = $AdminAccountClass->getIscrizioniSquadreAmountFiltered($search, $squadra_id);
            $Iscrizioni = $AdminAccountClass->getIscrizioniSquadrePagination(
                $order, 
                $start, 
                $length, 
                $search,
                $squadra_id
            );
            
            $DataArray = [];

            foreach ($Iscrizioni as $Iscrizione)
            {
                $imposta_pagato_button = 
                    $Iscrizione->pagato == 0 ? 
                    '
                        <form action="' . URL_TO_PUBLIC_FOLDER. 'AdminAccount/impostaPagamentoIscrizioneSquadra/' . $Iscrizione->id . '" method="POST">
                            <button type="submit" class="btn btn-block button-azzurro">Imposta pagata</button>
                        </form>
                    ' :
                    '<button class="btn btn-block btn-success" disabled>Pagata</button>';

                $dettagli = '
                    <button 
                        class="btn button-azzurro dettagli-squadra"
                        data-toggle="modal" 
                        data-target="#visualizza-dettagli-iscrizione-squadra"
                        class="dettagli-squadra"
                        data-iscrizione-squadra-id=' . $Iscrizione->id . '
                    >
                        Dettagli
                    </button>
                ';

                $boe_acquistate = '
                    <button 
                        class="btn button-azzurro acquisti-boe-squadra"
                        data-toggle="modal" 
                        data-target="#visualizza-acquisti-boe-squadra"
                        class="acquisti-boe-squadra"
                        data-iscrizione-squadra-id=' . $Iscrizione->id . '
                    >
                        Boe
                    </button>
                ';

                $Row = 
                [
                    'id' => $Iscrizione->id,
                    'data' => $Iscrizione->data,
                    'squadra' => $Iscrizione->nome_squadra,
                    'capo_squadra' => $Iscrizione->nome . ' ' . $Iscrizione->cognome,
                    'totale' => $Iscrizione->totale,
                    'stato' => $Iscrizione->pagato ? 'Pagato' : 'Non pagato',
                    'dettagli' => $dettagli . $boe_acquistate,
                    'imposta_pagato' => $imposta_pagato_button
                ];

                $DataArray[] = $Row;
            }

            $Response = ['recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $DataArray];
            
            echo json_encode($Response);
        }
    }

    public function impostaPagamentoIscrizioneSquadra()
    {
        require_once("AdminGate.php");

        $AdminAccountClass = new AdminAccountModel();
        $GareClass = new GareModel();
        $AdminGareClass = new AdminGareModel();
        $OpzioniAcquistoClass = new OpzioniAcquistoModel();
        $AccountClass = new AccountModel();

        $iscrizione_di_squadra_id = $this->route_params['id'];

        // Dettagli iscrizione di squadra
        $IscrizioneDiSquadra = $AdminAccountClass->getIscrizioneDiSquadra($iscrizione_di_squadra_id);

        if ($IscrizioneDiSquadra->pagato == 0)
        {
            $AdminAccountClass->setIscrizioneDiSquadraPagato($iscrizione_di_squadra_id);

            // Creazione pagamento bonifico
            $pagamento_id = $GareClass->insertPagamento(
                $IscrizioneDiSquadra->capo_squadra_id,
                date('d/m/Y'),
                $IscrizioneDiSquadra->totale,
                5, // tipo: 5 => pagamento messo manualmente
                '', // file
                1 // pagamento boa
            );

            $ListaIscrizioniDellaSquadra = $AdminAccountClass->getListaIscrizioniSquadra($iscrizione_di_squadra_id);

            // Ciclo lista iscrizioni membri squadra per impostare il pagamento
            foreach ($ListaIscrizioniDellaSquadra as $IscrizioneMembroSquadra)
            {
                $Iscrizione = $AdminAccountClass->getIscrizione($IscrizioneMembroSquadra->iscrizione_id);
                $Gara = $GareClass->getGara($Iscrizione->ID_gara);

                // Salva record gara pagata
                $GareClass->salvaPagamentoGara($Iscrizione->ID_utente, $Gara->ID, $pagamento_id);

                // Imposta gara come pagata
                $GareClass->setGaraPagata($Iscrizione->ID_utente, $Gara->ID);

                // Imposta boe gare utente come pagate (salvando prezzo attuale)
                $GareClass->setUtenteGarePrezzoBoa($Iscrizione->ID_utente, $IscrizioneMembroSquadra->iscrizione_id);

                $Evento = $AdminGareClass->getEvento($Gara->ID_evento);

                // Lista opzioni acquisto fatte dall'utente per l'evento 
                $OpzioniAcquistiEventoUtente = $OpzioniAcquistoClass->getAcquistiOpzioniEventiUtente(
                    $Iscrizione->ID_utente,
                    1, // da pagare
                    $Evento->ID
                );

                foreach ($OpzioniAcquistiEventoUtente as $OpzioneAcquistoEvento)
                    $OpzioniAcquistoClass->impostaOpzioneAcquistoEventoUtenteComePagata($OpzioneAcquistoEvento->ID, $pagamento_id);

                // Aggiunge sconti eventi in base alla quantita gare
                $EventiScontoAssoluto = $AccountClass->getScontiEventiQuantitaGareUtente($Iscrizione->ID_utente)->EventiScontoAssoluto;

                foreach ($EventiScontoAssoluto as $EventoSconto)
                {
                    $EventoScontoDatabase = $AccountClass->getEventoScontoQuantitaGareUtente($Iscrizione->ID_utente, $EventoSconto->evento_id);

                    if ($EventoScontoDatabase)
                    {
                        $AccountClass->updateEventoScontoQuantitaGareUtente(
                            $EventoScontoDatabase->ID,
                            $EventoSconto->quantita_gare,
                            $EventoSconto->sconto
                        );
                    }
                    else
                    {
                        $AccountClass->insertEventoScontoQuantitaGareUtente(
                            $Iscrizione->ID_utente,
                            $EventoSconto->evento_id,
                            $EventoSconto->quantita_gare,
                            $EventoSconto->sconto
                        );
                    }
                }
            }
        }

        

        $ArrayTemplateAction = [];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/iscrizioni_squadre.html', $ArrayTemplateCombined);
    }

    public function ajaxDettagliIscrizioneSquadra()
    {
        $squadra_id = $this->route_params['id'];

        if ($squadra_id)
            require_once("Gate.php");
        else
            require_once("AdminGate.php");

        $AdminAccountClass = new AdminAccountModel();
        
        if ($_POST['iscrizione_squadra_id'])
        {
            $body_tabella = '';

            $Iscrizioni = $AdminAccountClass->getDettagliIscrizioniSquadre($_POST['iscrizione_squadra_id']);

            foreach ($Iscrizioni as $Iscrizione)
                $body_tabella .= '
                    <tr>
                        <td>' . $Iscrizione->id_tessera . '</td>
                        <td>' . $Iscrizione->nome_membro . ' ' . $Iscrizione->cognome_membro . '</td>
                        <td>' . $Iscrizione->nome_evento . ' - ' . $Iscrizione->nome_gara . '</td>
                    </tr>
                ';

            echo json_encode($body_tabella);
        }
    }

    public function ajaxAcquistiBoeSquadra()
    {
        $squadra_id = $this->route_params['id'];

        if ($squadra_id)
            require_once("Gate.php");
        else
            require_once("AdminGate.php");

        $AdminAccountClass = new AdminAccountModel();
        
        if ($_POST['iscrizione_squadra_id'])
        {
            $body_tabella = '';

            $Iscrizioni = $AdminAccountClass->getOpzioniAcquistoSquadra($_POST['iscrizione_squadra_id']);

            foreach ($Iscrizioni as $Iscrizione)
                $body_tabella .= '
                    <tr>
                        <td>' . $Iscrizione->nome_membro . ' ' . $Iscrizione->cognome_membro . '</td>
                        <td>' . $Iscrizione->nome_evento . '</td>
                    </tr>
                ';

            echo json_encode($body_tabella);
        }
    }

    public function listaOpzioniAcquistoAcquistate()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction = [];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminAccount/lista_opzioni_acquisto_acquistate.html', $ArrayTemplateCombined);
    }

    public function ajaxTabellaAcquistiOpzioniAcquisto()
    {
        require_once("AdminGate.php");
        
        $AdminAccountClass = new AdminAccountModel();

        if (isset($_GET))
        {
            $column = $_GET['order'][0]['column'];
            $dir = $_GET['order'][0]['dir'];
            $search = $_GET['search']['value'];
            $start = $_GET['start'];
            $length = $_GET['length'];
            $Fields = 
            [
                "aoae.dataora",
                "o.nome",
                "u.cognome",
                "u.email",
                "e.nome",
                "",
                "aoae.IDPagamento"
            ];

            $order = $Fields[$column] . " " . $dir;

            $total_record = $AdminAccountClass->getOpzioniAcquistoAcquistateAmount();
            $filtered_record = $AdminAccountClass->getOpzioniAcquistoAcquistateAmountFiltered($search);
            $OpzioniAcquistoAcquistate = $AdminAccountClass->getOpzioniAcquistoAcquistatePagination(
                $order, 
                $start, 
                $length, 
                $search
            );
            
            $DataArray = [];

            foreach ($OpzioniAcquistoAcquistate as $OpzioneAcquistoAcquistata)
            {
                $cancella_button = 
                    $OpzioneAcquistoAcquistata->pagamento_id > 0 ?
                    '' :
                    '
                        <button
                            type="button" 
                            class="btn sfondo-rosso btn-block"
                            data-toggle="modal"
                            data-target="#cancella-opzione-acquisto-modal"
                            onclick="cancellaOpzioneAcquistoAcquistata(' .
                                $OpzioneAcquistoAcquistata->opzione_acquisto_id . ', \'' .
                                $OpzioneAcquistoAcquistata->utente_nome . ' ' . $OpzioneAcquistoAcquistata->utente_cognome  . '\'
                            )"
                        >
                            Cancella
                        </button>
                    ';

                $Row = 
                [
                    'data' => $OpzioneAcquistoAcquistata->opzione_acquisto_data,
                    'nome' => $OpzioneAcquistoAcquistata->opzione_acquisto_nome,
                    'utente_nome' => $OpzioneAcquistoAcquistata->utente_nome . ' ' . $OpzioneAcquistoAcquistata->utente_cognome,
                    'utente_email' => $OpzioneAcquistoAcquistata->utente_email,
                    'evento_nome' => $OpzioneAcquistoAcquistata->evento_nome,
                    'importo' => $OpzioneAcquistoAcquistata->opzione_acquisto_prezzo,
                    'pagamento_id' => $OpzioneAcquistoAcquistata->pagamento_id ?: 'Non pagato',
                    'cancella' => $cancella_button
                ];

                $DataArray[] = $Row;
            }

            $Response = ['recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $DataArray];
            
            echo json_encode($Response);
        }
    }

    public function cancellaOpzioneAcquistoAcquistata()
    {
        require_once("AdminGate.php");

        $AdminAccountClass = new AdminAccountModel();

        $opzione_acquisto_id = $this->route_params['id'];

        $AdminAccountClass->cancellaOpzioneAcquistoAcquistata($opzione_acquisto_id);

        header("location: " . URL_TO_PUBLIC_FOLDER . "/AdminAccount/listaOpzioniAcquistoAcquistate");
    }
}
