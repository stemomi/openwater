<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\AccountModel;
use \App\Models\FunctionsModel;
use \App\Models\GareIndividualiModel;
use \App\Models\AdminGareIndividualiModel;

class GareIndividuali extends \Core\Controller
{
    public function Aperte()
    {
        require_once("Gate.php");

        $GareIndividualiClass = new GareIndividualiModel();
        $AccountClass = new AccountModel();

        $GareAperte = $GareIndividualiClass->getAllGareAperte();
        $Utente = $AccountClass->getUtenteSessione();

        foreach($GareAperte as $gara)
        {
            // Estrae gara utente
            $GaraUtente = $GareIndividualiClass->getGaraUtente($Utente->ID, $gara->ID_gara);

            if ($GaraUtente)
            {
                $gara->iscritto = 1;
                $gara->pagato = $GaraUtente->pagato;
                $gara->RGI_ID = $GaraUtente->RGI_ID;
                $gara->ID_iscrizione = $GaraUtente->ID;
            }
            else
            {
                $gara->iscritto = 0;
                $gara->pagato = 0;
                $gara->RGI_ID = 0;
                $gara->ID_iscrizione = 0;
            } 
        }

        // Registra gare
        if ( count($GareAperte) > 0 )
            foreach($GareAperte as $gara) $Eventi[$gara->ID_evento][] = $gara;
        else
            $Eventi = [];

        $ArrayTemplateAction = [ 'Eventi' => $Eventi ];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('GareIndividuali/aperte.html', $ArrayTemplateCombined);
    }

    public function Passate()
    {
        require_once("Gate.php");

        $GareIndividualiClass= new GareIndividualiModel();
        $AccountClass= new AccountModel();

        $Utente= $AccountClass->getUtenteSessione();
        $GarePassateUtente= $GareIndividualiClass->getAllGarePassateUtente($Utente->ID);

        $Eventi = [];

        // Registra gare
        foreach($GarePassateUtente as $gara)
            $Eventi[$gara->ID_evento][] = $gara;

        $ArrayTemplateAction= ['Eventi' => $Eventi];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('GareIndividuali/passate.html', $ArrayTemplateCombined);
    }

    public function ConfermaIscrizioni()
    {
        require_once("Gate.php");

        $AccountClass= new AccountModel();
        $FunctionsClass= new FunctionsModel();
        $GareIndividualiClass= new GareIndividualiModel();

        $Utente= $AccountClass->getUtenteSessione();

        if (isset($_POST['IDGareIscrizioni']))
        {
            $IDGareIscrizioni= $_POST['IDGareIscrizioni'];

            $subject= 'ItalianOpenWaterTour: Completamento iscrizioni/Complete Registration';
            $messaggio= '<img src="http://italianopenwatertour.com/wp-content/uploads/2019/01/logo_menu.png" style="margin: 0 auto; text-align: center; padding: 20px;">
                         <br />
                         <p>Ciao ' . $Utente->nome . ' ' . $Utente->cognome . ',</p>
                         <br /><br />
                         <div>
                         Abbiamo ricevuto la tua richiesta di iscrizione per/we received you request for:
                         </div>';

            foreach ($IDGareIscrizioni as $id_gara)
            {
                // Inserisce record iscrizione
                $GareIndividualiClass->insertGaraUtente($Utente->ID, $id_gara);

                // Estrae dati gara per email
                $gara= $GareIndividualiClass->getGaraConDettagli($id_gara);
                $messaggio .= '<br /><div>' . $gara->nome_evento . ': ' . $gara->nome_gara . '</div>';
            }

            $messaggio .= '<br />
            <ul>
    			<li>per completare la tua iscrizione effettua il pagamento con bonifico o carta di credito (paypal)</li>
    			<li><strong>x il Challenge </strong>-ricorda di portare la copia cartacea del certificato da consegnare al ritiro pacco gara</li>
                N.B se non si completa la procedura la vostra iscrizione sarà annullata.<br>
                <li><strong>ALL YOU NEED IS SWIM</strong><br>
                Per avere accesso alla classifica finale è necessario caricare i seguenti dati della propria nuotata dal
                profilo utente su www.italianopenwatertour.com: giorno, provincia della nuotata, località della
                nuotata, acqua salata o dolce (se dolce specificare il nome del bacino), distanza, tempo effettuato,
                file nuotata del satellitare, file foto di partenza o arrivo.<br>
                 L’evento ha inizio sabato 20 giugno e ha termine domenica 28 giugno.</li>
                <li>
            </ul>
			<ul>
    			<li>to complete your registration make the payment by bank transfer or credit card (paypal) </li>
    			<li>remember to bring your medical certificate when retrieving your race pack</li>
    			N.B if you do not complete the procedure your registration will be canceled.<br>
            </ul>
			
            <p><a href="italianopenwatertour.com">italianopenwatertour.com</a></p>';

            $FunctionsClass->sendEmail($Utente->email,'',$subject,$messaggio);
        }

        header('location: '. URL_TO_PUBLIC_FOLDER .'Account/Iscrizioni');
    }

    public function CaricaRisultato()
    {
        require_once("Gate.php");

        $iscrizione_id= $this->route_params['id'];

        $AccountClass = new AccountModel();
        $GareIndividualiClass = new GareIndividualiModel();

        // Estrae iscrizione
        $Iscrizione= $GareIndividualiClass->getIscrizioneUtente($_SESSION['IDUtente'], $iscrizione_id);

        $ProvinceItaliane = $AccountClass->getListaProvinceItaliane();

        if (!$Iscrizione)
            die('Iscrizione non trovata!');
        elseif ($Iscrizione->RGI_ID > 0)
            die('Risultati già caricati!');

        $ArrayTemplateAction= [
            'Iscrizione' => $Iscrizione,
            'ProvinceItaliane' => $ProvinceItaliane
        ];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('GareIndividuali/caricaRisultato.html', $ArrayTemplateCombined);
    }

    public function CaricaRisultatoConfirm()
    {
        require_once("Gate.php");

        if (isset($_POST['SubmitCaricaRisultato']))
        {
            $GareIndividualiClass = new GareIndividualiModel();
            $FunctionsClass = new FunctionsModel();

            $IDIscrizione = $_POST['IDIscrizione'];
            $Tempo = $_POST['Tempo'];
            $Localita = $_POST['Localita'];
            $IDProvincia = $_POST['IDProvincia'];
            $TipoAcqua = $_POST['TipoAcqua'];
            $LinkGPS = $_POST['LinkGPS'];
            $FileGPSNome = '';
            $FotoGPSNome = '';

            if (isset($_FILES['FileGPS']))
            {
                // CONTROLLI DI SICUREZZA UPLOAD FILE
                $checkUploadFile= $FunctionsClass->checkUploadFile($_FILES["FileGPS"]["name"], $_FILES["FileGPS"]["tmp_name"]);
                if ( $checkUploadFile != "") die($checkUploadFile);

                // Crea nome file
                $ext_var= explode( ".", basename($_FILES["FileGPS"]["name"]) );
                $ext = end($ext_var);
                $path = $Utente->ID . "-" . time() . "-" . bin2hex(openssl_random_pseudo_bytes(8)) . "." . $ext;

                // Sposta file nella cartella
                if (move_uploaded_file($_FILES["FileGPS"]["tmp_name"], PATH_GPS_FILES . $path))
                    $FileGPSNome = $path;
            }

            if (isset($_FILES['FotoGPS']))
            {
                // CONTROLLI DI SICUREZZA UPLOAD FILE
                $checkUploadFile= $FunctionsClass->checkUploadFile($_FILES["FotoGPS"]["name"], $_FILES["FotoGPS"]["tmp_name"]);
                if ( $checkUploadFile != "") die($checkUploadFile);

                // Crea nome file
                $ext_var= explode( ".", basename($_FILES["FotoGPS"]["name"]) );
                $ext = end($ext_var);
                $path = $Utente->ID . "-" . time() . "-" . bin2hex(openssl_random_pseudo_bytes(8)) . "." . $ext;

                // Sposta file nella cartella
                if (move_uploaded_file($_FILES["FotoGPS"]["tmp_name"], PATH_GPS_FOTO . $path))
                    $FotoGPSNome = $path;
            }

            // Controlla se esiste già un risultato per l'iscrizione
            $Iscrizione = $GareIndividualiClass->getIscrizioneUtente($Utente->ID, $IDIscrizione);

            if ($Iscrizione->RGI_ID > 0)
                die('Esiste già un risultato per questa iscrizione!');
            else
            {
                // Inserisce record risultati
                $GareIndividualiClass->addRisultato(
                    $IDIscrizione,
                    $Tempo,
                    $Localita,
                    $IDProvincia,
                    $TipoAcqua,
                    $FileGPSNome,
                    $FotoGPSNome,
                    $LinkGPS
                );
            }

            header('location: '. URL_TO_PUBLIC_FOLDER .'Account/Iscrizioni');
        }
    }
}
