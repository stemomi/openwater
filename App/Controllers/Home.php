<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Models\AdminGareModel;
use \App\Models\FunctionsModel;
use \App\Models\AccountModel;
use \App\Models\GareModel;
use \App\Models\GareIndividualiModel;
use \App\Models\ScontiModel;
use \App\Models\EmailModel;
use \App\Models\OpzioniAcquistoModel;
use \App\Models\PaypalModel;
use DateTime;

class Home extends \Core\Controller
{
    public function indexAction()
    {
        View::renderTemplate('Home/index.html', []);
    }

    public function Test()
    {
        require_once("Gate.php");

        $AccountClass= new AccountModel();
        $GareClass= new GareModel();
        $GareIndividualiClass = new GareIndividualiModel();
        $ScontiClass = new ScontiModel();

        $Utente= $AccountClass->getUtenteSessione();

        /*

            // Calcola eventuale credito utilizzato
            $TotaleDaPagareUtente = $AccountClass->getTotaleDaPagareUtente($Utente->ID);
            $TotaleDaSaldareUtente = $AccountClass->getTotaleDaSaldareUtente($Utente->ID, 1)->totale_da_saldare;
            $TotaleDaSaldareSenzaCreditoUtente = $AccountClass->getTotaleDaSaldareUtente($Utente->ID)->totale_da_saldare;

            print "<pre>";
            print_r($TotaleDaPagareUtente);
            echo '<br>';
            print_r($TotaleDaSaldareUtente);
            echo '<br>';
            print_r($TotaleDaSaldareSenzaCreditoUtente);
            print "</pre>";

            die();

        */

        /*

        $DataPagamentoSlash = '26/01/2021';
        $DataPagamento = '2021-01-26 15:00:00';
        $payment_amount = 67.50;
        $txn_id = '1234';

        // Controlla boa da pagare
        $BoaPagataFlag= $Utente->boa_da_pagare == 1 ? true : false;

        // Calcola eventuale credito utilizzato
        $CreditoUtilizzato = number_format($Utente->credito, 2, '.', '');

        // Azzera credito
        $AccountClass->AggiornaCreditoUtente(0.00, $Utente->ID);

        // Crea record pagamento
        $IDPagamento= $GareClass->insertPagamento(
            $Utente->ID,
            $DataPagamentoSlash,
            $payment_amount,
            2,
            '',
            $BoaPagataFlag,
            $txn_id,
            '',
            $CreditoUtilizzato);

        if ($IDPagamento)
        {
            // Salva sconti combo gare applicati (da fare prima di impostare le iscrizioni come pagate)
            $ScontiComboGareApplicati = $ScontiClass->getUtenteScontoComboGare($Utente->ID);
            $ListaScontiComboGareApplicati = $ScontiComboGareApplicati ? $ScontiComboGareApplicati->ListaSconti : [];

            foreach ($ListaScontiComboGareApplicati as $sconto_combo)
                $ScontiClass->addScontoComboGaraAPagamento($IDPagamento, $sconto_combo->ID);

            // Cerca gare da pagare
            $GareDaPagare= $GareClass->getAllGareAperteUtenteDaPagare($Utente->ID, $DataPagamento);
            $GareIndividualiDaPagare= $GareIndividualiClass->getAllGareAperteUtenteDaPagare($Utente->ID, $DataPagamento);

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

            // Reimposta boa da pagare
            $AccountClass->AggiornaBoaDaPagare($Utente->ID, false);

            // Imposta magliette come pagate
            $GareClass->impostaUtenteMaglietteComePagate($Utente->ID, $IDPagamento);
        }
    	
        die('Fatto!');

        */
    }

    public function GestionePaypalIPN()
    {
        $PaypalClass = new PaypalModel();
        $FunctionsClass = new FunctionsModel();

        $dataAttuale= "[" . date("F j, Y, g:i a") . "]";
        $ErrorLogPath= MAIN_PATH . "logs/IPN_Paypal_log.log";

        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();

        foreach ($raw_post_array as $keyval)
        {
          $keyval = explode ('=', $keyval);
          if (count($keyval) == 2) $myPost[$keyval[0]] = urldecode($keyval[1]);
        }

        $req = 'cmd=_notify-validate';
        if(function_exists('get_magic_quotes_gpc')) $get_magic_quotes_exists = true;

        foreach ($myPost as $key => $value)
        {        
           if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1)
                $value = urlencode(stripslashes($value)); 
           else
                $value = urlencode($value);

           $req .= "&$key=$value";
        }

        $ch = curl_init('https://www.paypal.com/cgi-bin/webscr'); // change to [...]sandbox.paypal[...] when using sandbox to test
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        //curl_setopt($ch, CURLOPT_SSLVERSION, 1);

        $RispostaServerPaypal= curl_exec($ch);

        if( $RispostaServerPaypal === false )
        {
            error_log("\n\n" . $dataAttuale . "IPN error while processing IPN data : " . curl_error($ch), 3, $ErrorLogPath);
            curl_close($ch);
            exit;
        }
        curl_close($ch);

        if ($RispostaServerPaypal == "VERIFIED")
        {
            $payment_status = isset($_POST['payment_status']) ? $_POST['payment_status'] : "";
            $payment_date = isset($_POST['payment_date']) ? $_POST['payment_date'] : "";
            $mc_gross = isset($_POST['mc_gross']) ? $_POST['mc_gross'] : "";
            $mc_gross1 = isset($_POST['mc_gross1']) ? $_POST['mc_gross1'] : "";
            $payment_currency = isset($_POST['mc_currency']) ? $_POST['mc_currency'] : "";
            $txn_id = isset($_POST['txn_id']) ? $_POST['txn_id'] : "";
            $receiver_email = isset($_POST['receiver_email']) ? $_POST['receiver_email'] : "";
            $payer_email = isset($_POST['payer_email']) ? $_POST['payer_email'] : "";
            $first_name = isset($_POST['first_name']) ? $_POST['first_name'] : "";
            $last_name = isset($_POST['last_name']) ? $_POST['last_name'] : "";
            $IDCliente = isset($_POST['custom']) ? $_POST['custom'] : "";

            $DataPagamento = $FunctionsClass->ConvertiDataPaypal($payment_date);
            
            $PaypalClass->gestionePagamentoPaypal(
                $payment_status,
                $DataPagamento,
                $mc_gross,
                $mc_gross1,
                $payment_currency,
                $txn_id,
                $receiver_email,
                $payer_email,
                $first_name,
                $last_name,
                $IDCliente
            );

            header("location: ../Account/Profile");
        }
        else
        {
            if ($raw_post_data != '') error_log("\n\n" . $dataAttuale . "\nRisposta Paypal NON VERIFICATA!", 3,  $ErrorLogPath);
            else  error_log("\n\n" . $dataAttuale . "\nRichiesta senza dati POST!", 3,  $ErrorLogPath);
        }
    }

    public function GestionePaypalIPNTest()
    {
        die();
        $PaypalClass = new PaypalModel();

        $payment_status = 'Completed';
        $payment_date = '2024-05-15 16:15:00';
        $mc_gross = '176';
        $mc_gross1 = '';
        $payment_currency = '€';

        // Per testare ricordarsi di cambiare questo campo in quanto dev'essere unico
        $txn_id = '483jfalered03432345';
        $receiver_email = 'iglacialiasd@gmail.com';
        $payer_email = 'rcattaneo@dnami.com';
        $first_name = 'Riccardo';
        $last_name = 'Cattaneo';
        $IDCliente = 6;

        $PaypalClass->gestionePagamentoPaypal(
            $payment_status,
            $payment_date,
            $mc_gross,
            $mc_gross1,
            $payment_currency,
            $txn_id,
            $receiver_email,
            $payer_email,
            $first_name,
            $last_name,
            $IDCliente
        );
    }
}