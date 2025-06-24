<?php

namespace App\Models;

use \App\Models\AdminGareModel;
use \App\Models\FunctionsModel;
use \App\Models\AccountModel;
use \App\Models\GareModel;
use \App\Models\GareIndividualiModel;
use \App\Models\ScontiModel;
use \App\Models\EmailModel;
use \App\Models\OpzioniAcquistoModel;
use PDO;
use DateTime;

class PaypalModel extends \Core\Model
{
    public function gestionePagamentoPaypal
    (
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
    )
    {
        $AdminGareClass = new AdminGareModel();
        $FunctionsClass = new FunctionsModel();
        $AccountClass = new AccountModel();
        $GareClass = new GareModel();
        $GareIndividualiClass = new GareIndividualiModel();
        $ScontiClass = new ScontiModel();
        $EmailClass = new EmailModel();
        $OpzioniAcquistoClass = new OpzioniAcquistoModel();

        $dataAttuale = "[" . date("F j, Y, g:i a") . "]";
        $ErrorLogPath = MAIN_PATH . "logs/IPN_Paypal_log.log";

        $payment_amount = $mc_gross != "" ? $mc_gross : $mc_gross1;
        $DataPagamento = $payment_date;
        $DataPagamentoSlash = DateTime::createFromFormat('Y-m-d H:i:s', $DataPagamento)->format('d/m/Y');

        error_log("\n\n" . $dataAttuale .
                  " \nIPN Status Verified Ok:" .
                  " \n- First Name: " . $first_name .
                  " \n- Last Name: " . $last_name .
                  " \n- Payment status: " . $payment_status .
                  " \n- Payment amount: " . $payment_amount .
                  " \n- Payment currency: " . $payment_currency .
                  " \n- Transaction id: " . $txn_id .
                  " \n- Receiver email: " . $receiver_email .
                  " \n- Payer email: " . $payer_email .
                  " \n- Data Paypal: " . $payment_date .
                  " \n- DataPagamento: " . $DataPagamento .
                  " \n- DataPagamentoSlash: " . $DataPagamentoSlash .
                  " \n- IDCliente: " . $IDCliente,
                  3,  $ErrorLogPath);

        if 
        (
            $payment_status == "Completed" &&
            ( $receiver_email == "iglacialiasd@gmail.com" || $receiver_email == "glacialissd@gmail.com" )
        )
        {
            $Utente = $AccountClass->getUtente($IDCliente);
            $CheckTransazionePaypal = $GareClass->checkTransazionePaypal($txn_id);

            if ($CheckTransazionePaypal < 1)
            {
                if ($Utente)
                {
                    error_log("\n\nUtente Trovato: ID " . $Utente->ID, 3,  $ErrorLogPath);

                    // Controlla boa da pagare
                    $BoaPagataFlag = $Utente->boa_da_pagare == 1 ? true : false;

                    // Calcola eventuale credito utilizzato
                    $CreditoUtilizzato = number_format($Utente->credito, 2, '.', '');

                    // Azzera credito
                    $AccountClass->AggiornaCreditoUtente(0.00, $Utente->ID);

                    // Crea record pagamento
                    $IDPagamento = $GareClass->insertPagamento(
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
                        error_log("\nID Pagamento inserito: " . $IDPagamento, 3,  $ErrorLogPath);

                        // Salva sconti combo gare applicati (da fare prima di impostare le iscrizioni come pagate)
                        $ScontiComboGareApplicati = $ScontiClass->getUtenteScontoComboGare($Utente->ID);
                        $ListaScontiComboGareApplicati = $ScontiComboGareApplicati ? $ScontiComboGareApplicati->ListaSconti : [];

                        foreach ($ListaScontiComboGareApplicati as $sconto_combo)
                            $ScontiClass->addScontoComboGaraAPagamento($IDPagamento, $sconto_combo->ID);

                        // Cerca gare da pagare
                        $GareDaPagare = $GareClass->getAllGareAperteUtenteDaPagare($Utente->ID, $DataPagamento);
                        $GareIndividualiDaPagare = $GareIndividualiClass->getAllGareAperteUtenteDaPagare($Utente->ID, $DataPagamento);

                        $RigheGarePagate = [];
                        $RigheBoePagate = [];

                        // Gare in contemporanea
                        foreach ($GareDaPagare as $gara)
                        {
                            // Salva record gara pagata
                            $GareClass->salvaPagamentoGara($Utente->ID, $gara->ID_gara, $IDPagamento);

                            // Imposta gara come pagata
                            $GareClass->setGaraPagata($Utente->ID, $gara->ID_gara);

                            // Aggiunge righe per notifica email
                            $RigheGarePagate[] = $gara->nome_evento . ': ' . $gara->nome_gara;

                            // Se acquistato boa inserisce nome evento
                            if ($gara->I_richiesta_boa == 1)
                                $RigheBoePagate[] = $gara->nome_evento;

                            error_log("\nImpostato gara pagata con ID: " . $gara->ID_gara, 3,  $ErrorLogPath);
                        }

                        // Gare individuali
                        foreach ($GareIndividualiDaPagare as $gara)
                        {
                            // Salva record gara pagata (relativo alla singola iscrizione)
                            $GareIndividualiClass->salvaPagamentoGara($Utente->ID, $gara->ID, $IDPagamento);

                            // Imposta gara come pagata
                            $GareIndividualiClass->setGaraPagata($Utente->ID, $gara->ID_gara);

                            // Aggiunge righe per notifica email
                            $RigheGarePagate[] = $gara->nome_evento . ': ' . $gara->nome_gara;

                            error_log("\nImpostato gara individuale pagata con ID: " . $gara->ID_gara, 3,  $ErrorLogPath);
                        }

                        // Segna sconti effettuati su staffette
                        /*foreach ($GareDaPagare as $gara)
                            $GareClass->segnaStaffettaSeDaScontare($gara);*/

                        // Imposta boe gare utente come pagate (salvando prezzo attuale)
                        $GareClass->setUtenteGarePrezzoBoa($Utente->ID);

                        // Cerca magliette acquistate per aggiungerle all'email (da chiamare prima di impostarle come pagate!)
                        $MaglietteAcquistate = $GareClass->getStringaEmailMaglietteAcquistate($Utente->ID);

                        // Imposta magliette come pagate
                        $GareClass->impostaUtenteMaglietteComePagate($Utente->ID, $IDPagamento);

                        // Reimposta boa da pagare
                        $AccountClass->AggiornaBoaDaPagare($Utente->ID, false);

                        // Imposta opzioni acquisto come pagate
                        $OpzioniAcquistiEventiUtente = $OpzioniAcquistoClass->getAcquistiOpzioniEventiUtente($Utente->ID, 1);

                        foreach($OpzioniAcquistiEventiUtente as $opzione_acquisto)
                            $OpzioniAcquistoClass->impostaOpzioneAcquistoEventoUtenteComePagata($opzione_acquisto->ID, $IDPagamento);

                        // Aggiunge sconti eventi in base alla quantita gare
                        $EventiScontoAssoluto = $AccountClass->getScontiEventiQuantitaGareUtente($Utente->ID)->EventiScontoAssoluto;

                        foreach($EventiScontoAssoluto as $evento_sconto)
                        {
                            $EventoScontoDatabase = $AccountClass->getEventoScontoQuantitaGareUtente($Utente->ID, $evento_sconto->evento_id);

                            if ($EventoScontoDatabase)
                            {
                                $AccountClass->updateEventoScontoQuantitaGareUtente(
                                    $EventoScontoDatabase->ID,
                                    $evento_sconto->quantita_gare,
                                    $evento_sconto->sconto
                                );
                            }
                            else
                            {
                                $AccountClass->insertEventoScontoQuantitaGareUtente(
                                    $Utente->ID,
                                    $evento_sconto->evento_id,
                                    $evento_sconto->quantita_gare,
                                    $evento_sconto->sconto
                                );
                            }
                        }

                        error_log("\nIscrizioni impostate come pagate per IDCliente: " . $IDCliente . ", totale gare: " . count($GareDaPagare), 3,  $ErrorLogPath);

                        // Invia email di notifica
                        $TemplateEmailPaypal = $EmailClass->getTemplateEmail(1);
                        
                        $subject = $TemplateEmailPaypal->oggetto;
                        $messaggio = $TemplateEmailPaypal->messaggio;
                        $EmailListaGare = '';

                        foreach ($RigheGarePagate as $gara) $EmailListaGare .= '<br /><div>' . $gara . '</div>';
                        foreach ($RigheBoePagate as $boa) $EmailListaGare .= '<br /><div>Boa per evento ' . $boa . '</div>';
                        foreach ($OpzioniAcquistiEventiUtente as $opzione_acquisto) $EmailListaGare .= '<br /><div>Opzione acquisto evento: ' . $opzione_acquisto->oa_nome . '</div>';
                        if ($BoaPagataFlag) $EmailListaGare .= '<br /><div>Attrezzatura acquistata: Boa</div>';
                        $EmailListaGare .= $MaglietteAcquistate;

                        $subject = str_replace('{{ Utente.nome }}', $Utente->nome, $subject);
                        $subject = str_replace('{{ Utente.cognome }}', $Utente->cognome, $subject);
                        $subject = str_replace('{{ ImportoPagamento }}', $payment_amount, $subject);

                        $messaggio = str_replace('{{ Logo }}', LOGO_LINK_HTML, $messaggio);
                        $messaggio = str_replace('{{ Utente.nome }}', $Utente->nome, $messaggio);
                        $messaggio = str_replace('{{ Utente.cognome }}', $Utente->cognome, $messaggio);
                        $messaggio = str_replace('{{ ImportoPagamento }}', $payment_amount, $messaggio);
                        $messaggio = str_replace('{{ ListaAcquisti }}', $EmailListaGare, $messaggio);

                        $FunctionsClass->sendEmail($Utente->email,'',$subject,$messaggio);
                    }
                    else
                        error_log("\n\n" . $dataAttuale . "\nErrore durante la creazione di ID Pagamento", 3,  $ErrorLogPath);
                }
                else
                    error_log("\n\n" . $dataAttuale . "\nUtente non trovato per IDCliente: " . $IDCliente, 3,  $ErrorLogPath);
            }
            else
                error_log("\n\n" . $dataAttuale . "\nTransazione già presente: " . $txn_id, 3,  $ErrorLogPath);
        }
        else
            error_log("\n\n" . $dataAttuale . "\nPagamento non processato, Payment Status: " . $payment_status, 3,  $ErrorLogPath);
    }
}
