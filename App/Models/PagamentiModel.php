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

class PagamentiModel extends \Core\Model
{
	public function resettaPagamentiDovutiUtente
	(
		$pagamento_id,
		$tipo_logs,
		$invia_email,
		$Utente,
		$data_pagamento,
		$error_log_path,
        $payment_amount,
        $cliente_id = 0,
        $boa_pagata_flag = 0
	)
	{
		error_log("\nID Pagamento inserito: " . $pagamento_id, 3,  $error_log_path);

        // Salva sconti combo gare applicati (da fare prima di impostare le iscrizioni come pagate)
        $ScontiComboGareApplicati = $ScontiClass->getUtenteScontoComboGare($Utente->ID);
        $ListaScontiComboGareApplicati = $ScontiComboGareApplicati ? $ScontiComboGareApplicati->ListaSconti : [];

        foreach ($ListaScontiComboGareApplicati as $ScontoCombo)
            $ScontiClass->addScontoComboGaraAPagamento($pagamento_id, $ScontoCombo->ID);

        // Cerca gare da pagare
        $GareDaPagare = $GareClass->getAllGareAperteUtenteDaPagare($Utente->ID, $data_pagamento);
        $GareIndividualiDaPagare = $GareIndividualiClass->getAllGareAperteUtenteDaPagare($Utente->ID, $data_pagamento);

        $RigheGarePagate = [];
        $RigheBoePagate = [];

        // Gare in contemporanea
        foreach ($GareDaPagare as $Gara)
        {
            // Salva record gara pagata
            $GareClass->salvaPagamentoGara($Utente->ID, $Gara->ID_gara, $pagamento_id);

            // Imposta gara come pagata
            $GareClass->setGaraPagata($Utente->ID, $Gara->ID_gara);

            // Aggiunge righe per notifica email
            $RigheGarePagate[] = $Gara->nome_evento . ': ' . $Gara->nome_gara;

            // Se acquistato boa inserisce nome evento
            if ($Gara->I_richiesta_boa == 1)
                $RigheBoePagate[] = $Gara->nome_evento;

            error_log("\nImpostato gara pagata con ID: " . $Gara->ID_gara, 3,  $error_log_path);
        }

        // Gare individuali
        foreach ($GareIndividualiDaPagare as $Gara)
        {
            // Salva record gara pagata (relativo alla singola iscrizione)
            $GareIndividualiClass->salvaPagamentoGara($Utente->ID, $Gara->ID, $pagamento_id);

            // Imposta gara come pagata
            $GareIndividualiClass->setGaraPagata($Utente->ID, $Gara->ID_gara);

            // Aggiunge righe per notifica email
            $RigheGarePagate[] = $Gara->nome_evento . ': ' . $Gara->nome_gara;

            error_log("\nImpostato gara individuale pagata con ID: " . $Gara->ID_gara, 3,  $error_log_path);
        }

        // Segna sconti effettuati su staffette
        foreach ($GareDaPagare as $Gara)
            $GareClass->segnaStaffettaSeDaScontare($Gara);

        // Imposta boe gare utente come pagate (salvando prezzo attuale)
        $GareClass->setUtenteGarePrezzoBoa($Utente->ID);

        // Cerca magliette acquistate per aggiungerle all'email (da chiamare prima di impostarle come pagate!)
        $magliette_acquistate = $GareClass->getStringaEmailMaglietteAcquistate($Utente->ID);

        // Imposta magliette come pagate
        $GareClass->impostaUtenteMaglietteComePagate($Utente->ID, $pagamento_id);

        // Reimposta boa da pagare
        $AccountClass->AggiornaBoaDaPagare($Utente->ID, false);

        // Imposta opzioni acquisto come pagate
        $OpzioniAcquistiEventiUtente = $OpzioniAcquistoClass->getAcquistiOpzioniEventiUtente($Utente->ID, 1);

        foreach ($OpzioniAcquistiEventiUtente as $OpzioneAcquisto)
            $OpzioniAcquistoClass->impostaOpzioneAcquistoEventoUtenteComePagata($OpzioneAcquisto->ID, $pagamento_id);

        // Aggiunge sconti eventi in base alla quantita gare
        $EventiScontoAssoluto = $AccountClass->getScontiEventiQuantitaGareUtente($Utente->ID)->EventiScontoAssoluto;

        foreach ($EventiScontoAssoluto as $EventoSconto)
        {
            $EventoScontoDatabase = $AccountClass->getEventoScontoQuantitaGareUtente($Utente->ID, $EventoSconto->evento_id);

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
                    $Utente->ID,
                    $EventoSconto->evento_id,
                    $EventoSconto->quantita_gare,
                    $EventoSconto->sconto
                );
            }
        }

        if ($cliente_id)
            error_log("\nIscrizioni impostate come pagate per IDCliente: " . $cliente_id . ", totale gare: " . count($GareDaPagare), 3,  $error_log_path);

        // Invia email di notifica
        $TemplateEmailPaypal = $EmailClass->getTemplateEmail(1);
        
        $subject = $TemplateEmailPaypal->oggetto;
        $messaggio = $TemplateEmailPaypal->messaggio;
        $email_lista_gare = '';

        foreach ($RigheGarePagate as $Gara) 
            $email_lista_gare .= '<br /><div>' . $Gara . '</div>';

        foreach ($RigheBoePagate as $boa) 
            $email_lista_gare .= '<br /><div>Boa per evento ' . $boa . '</div>';

        foreach ($OpzioniAcquistiEventiUtente as $OpzioneAcquisto) 
            $email_lista_gare .= '<br /><div>Opzione acquisto evento: ' . $OpzioneAcquisto->oa_nome . '</div>';

        if ($boa_pagata_flag) 
            $email_lista_gare .= '<br /><div>Attrezzatura acquistata: Boa</div>';

        $email_lista_gare .= $magliette_acquistate;

        $subject = str_replace('{{ Utente.nome }}', $Utente->nome, $subject);
        $subject = str_replace('{{ Utente.cognome }}', $Utente->cognome, $subject);
        $subject = str_replace('{{ ImportoPagamento }}', $payment_amount, $subject);

        $messaggio = str_replace('{{ Logo }}', LOGO_LINK_HTML, $messaggio);
        $messaggio = str_replace('{{ Utente.nome }}', $Utente->nome, $messaggio);
        $messaggio = str_replace('{{ Utente.cognome }}', $Utente->cognome, $messaggio);
        $messaggio = str_replace('{{ ImportoPagamento }}', $payment_amount, $messaggio);
        $messaggio = str_replace('{{ ListaAcquisti }}', $email_lista_gare, $messaggio);

        if ($invia_email)
            $FunctionsClass->sendEmail(
                $Utente->email, 
                '', // bcc
                $subject, 
                $messaggio
            );
	}
}