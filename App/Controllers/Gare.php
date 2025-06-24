<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\AccountModel;
use \App\Models\FunctionsModel;
use \App\Models\GareModel;
use \App\Models\AdminGareModel;
use \App\Models\ScontiModel;
use \App\Models\OpzioniAcquistoModel;
use \App\Models\ProdottiModel;
use \App\Models\AttributiProdottiModel;
use \App\Models\SquadreModel;

use DateTime;

class Gare extends \Core\Controller
{
    public function Aperte()
    {
        require_once("Gate.php");

        $GareClass = new GareModel();
        $AccountClass = new AccountModel();
        $ScontiClass = new ScontiModel();
        $OpzioniAcquistoClass = new OpzioniAcquistoModel();
        $ProdottiClass = new ProdottiModel();
        $AttributiProdottiClass = new AttributiProdottiModel();
        $SquadreClass = new SquadreModel();

        $GareAperte = $GareClass->getAllGareAperte();

        $utente_da_iscrivere_id = $this->route_params['id'];

        // Parametro che serve per determinare la pagina che ha attivato la funzione
        $page = $this->route_params['page'];

        $UtenteDaIscrivere = $AccountClass->getUtente($utente_da_iscrivere_id);

        $GareCombinate = [];
        $ArrayGareCollegate = [];
        $ArrayGareSingole = [];

        foreach($GareAperte as $gara)
        {
            // Estrae gara utente
            $GaraUtente = $GareClass->getGaraUtente($UtenteDaIscrivere->ID, $gara->ID_gara);

            if ($GaraUtente)
            {
                $gara->iscritto= 1;
                $gara->pagato= $GaraUtente->pagato;
                $gara->tipo_prezzo= $GaraUtente->tipo_prezzo;
                $gara->richiesta_boa= $GaraUtente->richiesta_boa;
                $gara->prezzo_boa= $GaraUtente->prezzo_boa;
                $gara->IDIscrizione= $GaraUtente->ID;

                if ($gara->combinata == 1)
                    $GareCombinate[]= $gara;
                else
                    $ArrayGareSingole[] = $gara->ID_gara;
            }
            else
            {
                $gara->iscritto= 0;
                $gara->pagato= 0;
                $gara->tipo_prezzo= 0;
                $gara->richiesta_boa= 0;
                $gara->prezzo_boa= 0;
                $gara->IDIscrizione= 0;
            } 
        }

        // Cicla gare combinate e riempie array con gare collegate
        foreach($GareCombinate as $gara_combinata)
        {
            // Estrae gare collegate alla principale
            $GareDaCombinata= $GareClass->getAllGareDaCombinata($gara_combinata->ID_gara);

            foreach($GareDaCombinata as $gara_inclusa)
            {
                if (!in_array($gara_inclusa->ID_gara_collegata, $ArrayGareCollegate))
                    $ArrayGareCollegate[] = $gara_inclusa->ID_gara_collegata;
            }
        }

        // Flagga gare singole come iscritto da combinata
        foreach($GareAperte as $gara)
        {
            // Flagga anche se combinata ha tutte le gare incluse in un altra combinata
            if ($gara->combinata == 1 && $gara->iscritto == 0)
            {
                $gara->iscritto_da_altra_combinata= 0;
                $gara->iscritto_da_singole= 0;

                // Estrae gare collegate alla principale
                $GareDaCombinata= $GareClass->getAllGareDaCombinata($gara->ID_gara);

                foreach($GareDaCombinata as $gara_inclusa)
                {
                    if (in_array($gara_inclusa->ID_gara_collegata, $ArrayGareCollegate))
                        $gara->iscritto_da_altra_combinata= 1;

                    // Flag iscritto_da_singole se almeno una gara singola ha già iscrizione
                    if (in_array($gara_inclusa->ID_gara_collegata, $ArrayGareSingole))
                        $gara->iscritto_da_singole= 1;
                } 
            }
            elseif ($gara->combinata == 0 && $gara->iscritto == 0) // Controlla Singola se inclusa in combinate
            {
                if(in_array($gara->ID_gara, $ArrayGareCollegate))
                    $gara->iscritto_da_combinata= 1;
            }
        }

        // Registra gare
        if ( count($GareAperte) > 0 )
        {
            foreach($GareAperte as $gara)
            {
                $Eventi[$gara->ID_evento]['Gare'][] = $gara;
                $Eventi[$gara->ID_evento]['vendita_boa'] = $gara->vendita_boa;

                // Inizializza variabili boa evento se non presenti
                if ( !isset($Eventi[$gara->ID_evento]['richiesta_boa']) )
                    $Eventi[$gara->ID_evento]['richiesta_boa'] = 0;

                if ( !isset($Eventi[$gara->ID_evento]['prezzo_boa']) )
                    $Eventi[$gara->ID_evento]['prezzo_boa'] = 0.00;

                // Controlla se le gare di questo evento hanno una richiesta per la boa, eventualmente pagata
                foreach($GareAperte as $gara_check_boa)
                {
                    if ($gara_check_boa->ID_evento == $gara->ID_evento)
                    {
                        if ($Eventi[$gara->ID_evento]['richiesta_boa'] == 0 && $gara_check_boa->richiesta_boa == 1)
                            $Eventi[$gara->ID_evento]['richiesta_boa'] = 1;

                        if ($Eventi[$gara->ID_evento]['prezzo_boa'] == 0.00 && $gara_check_boa->prezzo_boa > 0)
                            $Eventi[$gara->ID_evento]['prezzo_boa'] = $gara_check_boa->prezzo_boa;
                    }
                }

                // Aggiunge opzioni acquisto evento
                $OpzioniAcquistoEvento = $OpzioniAcquistoClass->getOpzioniAcquistoEvento($gara->ID_evento);

                foreach($OpzioniAcquistoEvento as $opzione_acquisto_evento)
                {
                    $Eventi[$gara->ID_evento]['OpzioniAcquisto'][$opzione_acquisto_evento->oa_nome]['id'] = $opzione_acquisto_evento->eoa_ID;
                    $Eventi[$gara->ID_evento]['OpzioniAcquisto'][$opzione_acquisto_evento->oa_nome]['prezzo'] = $opzione_acquisto_evento->oa_prezzo;
                    $Eventi[$gara->ID_evento]['OpzioniAcquisto'][$opzione_acquisto_evento->oa_nome]['obbligatorio'] = $opzione_acquisto_evento->eoa_obbligatorio;

                    // Cerca se è stato acquistato ed eventualmente pagato
                    $AcquistoOpzione = $OpzioniAcquistoClass->getAcquistoOpzioneEventoUtente($UtenteDaIscrivere->ID, $opzione_acquisto_evento->eoa_ID);
                    $opzione_acquisto_evento_acquistato = 0;
                    $opzione_acquisto_evento_pagato = 0;

                    if ($AcquistoOpzione)
                    {
                        $opzione_acquisto_evento_acquistato = 1;

                        if ($AcquistoOpzione->pagato == 1)
                            $opzione_acquisto_evento_pagato = 1;
                    }

                    $Eventi[$gara->ID_evento]['OpzioniAcquisto'][$opzione_acquisto_evento->oa_nome]['acquistato'] = $opzione_acquisto_evento_acquistato;
                    $Eventi[$gara->ID_evento]['OpzioniAcquisto'][$opzione_acquisto_evento->oa_nome]['pagato'] = $opzione_acquisto_evento_pagato;
                }
            }
        }
        else
            $Eventi = [];

        // Estrae sconti combo gare
        $ArrayGareScontoComboEffettuato = $ScontiClass->getUtenteGareScontoComboEffettuato($UtenteDaIscrivere->ID);
        $ScontiComboGare = $ScontiClass->getAllScontiComboGare();

        foreach ($ScontiComboGare as $sconto)
        {
            $ScontoListaGare = $ScontiClass->getAllScontiComboGare_GareCollegate($sconto->ID);

            // Salta sconti se non hanno gare configurate
            if (count($ScontoListaGare) == 0)
            {
                $sconto->attivabile = 0;
                continue;
            }

            // Inietta lista gare
            $lista_gare = '';

            foreach ($ScontoListaGare as $gara_key => $gara)
            {
                $lista_gare .= $gara->IDGara;

                if ($gara_key < count($ScontoListaGare) - 1)
                    $lista_gare .= ',';
            }

            $sconto->lista_gare = $lista_gare;

            // Disabilita sconto se l'utente si è già iscritto anche a una sola gara presente nella lista gare dello sconto
            $sconto->attivabile = 1;

            foreach ($ScontoListaGare as $gara)
            {
                $CheckGaraUtente = $GareClass->checkIscrizioneGara($UtenteDaIscrivere->ID, $gara->IDGara);

                // Inietta se pagata
                $GaraUtente = $GareClass->getGaraUtente($UtenteDaIscrivere->ID, $gara->IDGara);
                $gara->pagato = $GaraUtente ? $GaraUtente->pagato : 0;

                // Disattiva se utente già iscritto o gara già disputata
                if ($CheckGaraUtente > 0 || $gara->e_data < date('Y-m-d'))
                    $sconto->attivabile = 0;
            }

            // Riattiva sconto e preseleziona se l'utente è già iscritto a tutte le gare (ma non pagato)
            if ($gara->e_data >= date('Y-m-d'))
            {
                $flag_riattiva_sconto = 1;

                foreach($ScontoListaGare as $gara)
                {
                    if (!in_array($gara->IDGara, $ArrayGareScontoComboEffettuato) || $gara->pagato == 1)
                        $flag_riattiva_sconto = 0;
                }

                if ($flag_riattiva_sconto == 1)
                {
                    $sconto->attivabile = 1;
                    $sconto->preseleziona = 1;
                }
            }
        }

        // Flagga gare per segnalare l'applicazione di uno sconto combo gara
        foreach ($Eventi as $evento)
        {
            foreach($evento['Gare'] as &$gara)
            {
                if (in_array($gara->ID_gara, $ArrayGareScontoComboEffettuato))
                    $gara->sconto_combo_gara_applicato = 1;
                else
                    $gara->sconto_combo_gara_applicato = 0;
            }
        }
        
        // Filtra sconti attivabili
        $ScontiComboAttivabili = array_filter(
            $ScontiComboGare,
            function($sconto, $key)
            {
                return $sconto->attivabile == 1;
            },
            ARRAY_FILTER_USE_BOTH
        );

        // Controlla acquisti in corso magliette
        $CheckAcquistoMaglietta1 = $GareClass->checkUtenteAcquistoMaglietta($UtenteDaIscrivere->ID, 1);
        $CheckAcquistoMaglietta2 = $GareClass->checkUtenteAcquistoMaglietta($UtenteDaIscrivere->ID, 2);
        $CheckAcquistoMaglietta3 = $GareClass->checkUtenteAcquistoMaglietta($UtenteDaIscrivere->ID, 3);
        $TotCheckAcquistoMagliette = 
            $CheckAcquistoMaglietta1 +
            $CheckAcquistoMaglietta2 +
            $CheckAcquistoMaglietta3;

        // Prodotti
        $Prodotti = $ProdottiClass->getAllProdottiConAttributi();

        // Prodotti acquistati (ma da pagare)
        $ProdottiAcquisto = $ProdottiClass->getAllProdottiDaPagareUtenteConAttributi();

        $ArrayTemplateAction = 
        [
            'Eventi' => $Eventi,
            'BoaDaPagare' => $UtenteDaIscrivere->boa_da_pagare,
            'ScontiComboGare' => $ScontiComboGare,
            'ScontiComboAttivabili' => $ScontiComboAttivabili,
            'CheckAcquistoMaglietta1' => $CheckAcquistoMaglietta1,
            'CheckAcquistoMaglietta2' => $CheckAcquistoMaglietta2,
            'CheckAcquistoMaglietta3' => $CheckAcquistoMaglietta3,
            'TotCheckAcquistoMagliette' => $TotCheckAcquistoMagliette,
            'Prodotti' => $Prodotti,
            'ProdottiAcquisto' => $ProdottiAcquisto,
            'UtenteDaIscrivere' => $UtenteDaIscrivere,
            'page' => $page
        ];

        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Gare/aperte.html', $ArrayTemplateCombined);
    }

    public function Passate()
    {
        require_once("Gate.php");

        $GareClass= new GareModel();
        $AccountClass= new AccountModel();

        $Utente= $AccountClass->getUtenteSessione();
        $GarePassateUtente= $GareClass->getAllGarePassateUtente($Utente->ID);

        $Eventi = [];

        // Registra gare
        foreach($GarePassateUtente as $gara)
        {
            // Singole
            if ($gara->gara_combinata == 0) $Eventi[$gara->ID_evento][] = $gara;
            else // Combinate
            {
                // Estrae gare collegate alla principale
                $GareDaCombinata= $GareClass->getAllGareDaCombinataConDettagli($gara->ID_gara);

                foreach ($GareDaCombinata as $gara_da_combinata)
                    $Eventi[$gara->ID_evento][] = $gara_da_combinata;
            }
        }

        $ArrayTemplateAction= ['Eventi' => $Eventi];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Gare/passate.html', $ArrayTemplateCombined);

        // Malfa alessandro, ID 300 cambiato in 5 per testing, Riga 772
    }

    public function RisultatiGara()
    {
        require_once("Gate.php");

        $id_gara= $this->route_params['id'];

        $GareClass = new GareModel();
        $AdminGareClass= new AdminGareModel();

        // Estrae gara
        $Gara = $GareClass->getGaraConDettagli($id_gara);

        // Estrae evento
        $Evento = $AdminGareClass->getEvento($Gara->ID_evento);

        // Controlla se il risultato è condivisibile su facebook (se presente la foto dell'evento)
        $FotoEventoTrovata = 0;

        if ($Evento->foto != '' && file_exists(PATH_EVENTI_FOTO . $Evento->foto))
            $FotoEventoTrovata = 1;

        // Estrae risultati gara
        $RisultatiGara = $AdminGareClass->getRisultatiGara($id_gara);

        // Variabili default
        $Personale_Posizione= '';
        $Personale_NomeCognome= '';
        $Personale_Sesso= '';
        $Personale_PosizioneSesso= '';
        $Personale_RaceTime= '';
        $Personale_OraArrivo= '';
        $Personale_Distacco= '';
        $Personale_Media= '';
        $TabellaRisultati = '';
        $Personale_Crudo_Flag = false;
        $RisultatiDisponibili = 0;
        $TotalePosizioneSessoM= 0;
        $TotalePosizioneSessoF= 0;

        $Personale_PosizioneCrudo= 0;
        $TotalePartecipantiCrudoPerSesso= '';
        $Personale_PosizioneAvis= 0;
        $TotalePartecipantiAvisPerSesso= '';
        $Personale_PosizioneOver40= 0;
        $TotalePartecipantiOver40PerSesso= '';

        if ( count($RisultatiGara) > 0 && $Gara->mostra_risultati == 1)
        {
            $TabellaRisultati = '<table width="100%" class="table table-striped table-bordered table-hover" id="TabellaRisultati">
                                    <thead>
                                        <tr>
                                            <th>Posizione</th>
                                            <th>Utente</th>
                                            <th>Sesso</th>
                                            <th>Posizione Sesso</th>
                                            <th>Race Time</th>
                                            <th>Ora arrivo</th>
                                            <th>Distacco</th>
                                            <th>Media</th>
                                        </tr>
                                    </thead>
                                    <tbody>';

            foreach($RisultatiGara as $risultato)
            {
                $TabellaRisultati .= '  <tr>
                                            <td>' . $risultato->posizione . '</td>
                                            <td>' . $risultato->cognome . ' ' . $risultato->nome . '</td>
                                            <td>' . $risultato->sesso . '</td>
                                            <td>' . $risultato->posizione_sesso . '</td>
                                            <td>' . $risultato->racetime . '</td>
                                            <td>' . $risultato->ora_arrivo . '</td>
                                            <td>' . $risultato->distacco . '</td>
                                            <td>' . $risultato->media . '</td>
                                        </tr>';

                // Copia risultati personali
                if ($risultato->tessera == $_SESSION['IDUtente'])
                {
                    $Personale_ID_Risultato = $risultato->ID;
                    $Personale_Posizione= $risultato->posizione;
                    $Personale_NomeCognome= $risultato->cognome . ' ' . $risultato->nome;
                    $Personale_Sesso= $risultato->sesso;
                    $Personale_PosizioneSesso= $risultato->posizione_sesso;
                    $Personale_RaceTime= $risultato->racetime;
                    $Personale_OraArrivo= $risultato->ora_arrivo;
                    $Personale_Distacco= $risultato->distacco;
                    $Personale_Media= $risultato->media;
                    $Personale_Crudo_Flag = $risultato->crudo_flag;
                }

                // Incrementa totale posizione sesso
                if ($risultato->sesso == 'M') $TotalePosizioneSessoM++;
                elseif ($risultato->sesso == 'F') $TotalePosizioneSessoF++;
            }
            
            $TabellaRisultati .='   </tbody>
                                 </table>';

            $RisultatiDisponibili = 1;
        }

        // Recupera posizione Crudo
        if ($Personale_Posizione != '')
        {
            // Totale
            if ($Personale_Sesso == 'M') $TotalePartecipantiCrudoPerSesso= $AdminGareClass->countRisultatiGaraCrudo($id_gara, 'M');
            elseif ($Personale_Sesso == 'F') $TotalePartecipantiCrudoPerSesso= $AdminGareClass->countRisultatiGaraCrudo($id_gara, 'F');

            // Posizione Personale
            $RisultatiGaraCrudo= $AdminGareClass->getRisultatiGaraCrudo($id_gara);

            foreach ($RisultatiGaraCrudo as $key => $risultato)
            {
                if ($risultato->rg_tessera == $_SESSION['IDUtente'])
                    $Personale_PosizioneCrudo = $key + 1;
            }
        }

        // Recupera posizione Avis
        if ($Personale_Posizione != '')
        {
            // Totale
            if ($Personale_Sesso == 'M') $TotalePartecipantiAvisPerSesso= $AdminGareClass->countRisultatiGaraAvis($id_gara, 'M');
            elseif ($Personale_Sesso == 'F') $TotalePartecipantiAvisPerSesso= $AdminGareClass->countRisultatiGaraAvis($id_gara, 'F');

            // Posizione Personale
            $RisultatiGaraAvis= $AdminGareClass->getRisultatiGaraAvis($id_gara);

            foreach ($RisultatiGaraAvis as $key => $risultato)
            {
                if ($risultato->rg_tessera == $_SESSION['IDUtente'])
                    $Personale_PosizioneAvis = $key + 1;
            }
        }

        // Recupera posizione Over 40
        if ($Personale_Posizione != '')
        {
            // Totale
            if ($Personale_Sesso == 'M') $TotalePartecipantiOver40PerSesso= $AdminGareClass->countRisultatiGaraOver40($id_gara, 'M');
            elseif ($Personale_Sesso == 'F') $TotalePartecipantiOver40PerSesso= $AdminGareClass->countRisultatiGaraOver40($id_gara, 'F');

            // Posizione Personale
            $RisultatiGaraOver40= $AdminGareClass->getRisultatiGaraOver40($id_gara);

            foreach ($RisultatiGaraOver40 as $key => $risultato)
            {
                if ($risultato->rg_tessera == $_SESSION['IDUtente'])
                    $Personale_PosizioneOver40 = $key + 1;
            }
        }

        $ArrayTemplateAction= [
            'TabellaRisultati' => $TabellaRisultati,
            'RisultatiDisponibili' => $RisultatiDisponibili,
            'NomeGara' => $Gara->nome_evento . ' ' . $Gara->nome_gara,
            'Personale_ID_Risultato' => $Personale_ID_Risultato,
            'Personale_NomeCognome' => $Personale_NomeCognome,
            'Personale_Posizione' => $Personale_Posizione,
            'Personale_PosizioneSesso' => $Personale_PosizioneSesso,
            'Personale_PosizioneCrudo' => $Personale_PosizioneCrudo,
            'TotalePartecipantiCrudoPerSesso' => $TotalePartecipantiCrudoPerSesso,
            'Personale_PosizioneAvis' => $Personale_PosizioneAvis,
            'TotalePartecipantiAvisPerSesso' => $TotalePartecipantiAvisPerSesso,
            'Personale_PosizioneOver40' => $Personale_PosizioneOver40,
            'TotalePartecipantiOver40PerSesso' => $TotalePartecipantiOver40PerSesso,
            'Personale_Sesso' => $Personale_Sesso,
            'Personale_RaceTime' => $Personale_RaceTime,
            'Personale_OraArrivo' => $Personale_OraArrivo,
            'Personale_Distacco' => $Personale_Distacco,
            'Personale_Media' => $Personale_Media,
            'Personale_Crudo_Flag' => $Personale_Crudo_Flag,
            'TotalePartecipanti' => count($RisultatiGara),
            'TotalePosizioneSessoM' => $TotalePosizioneSessoM,
            'TotalePosizioneSessoF' => $TotalePosizioneSessoF,
            'FotoEventoTrovata' => $FotoEventoTrovata
        ];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        // 197

        View::renderTemplate('Gare/risultatiGara.html', $ArrayTemplateCombined);
    }

    public function ConfermaIscrizioni()
    {
        require_once("Gate.php");

        $AccountClass = new AccountModel();
        $FunctionsClass = new FunctionsModel();
        $GareClass = new GareModel();
        $OpzioniAcquistoClass = new OpzioniAcquistoModel();
        $ProdottiClass = new ProdottiModel();
        $AttributiProdottiClass = new AttributiProdottiModel();

        $utente_da_iscrivere_id = $this->route_params['id'];

        // Parametro che permette di definire da dove è stata eseguita la chiamata della funzione
        $page = $this->route_params['page'];

        $UtenteSessione = $AccountClass->getUtenteSessione();
        $UtenteDaIscrivere = $AccountClass->getUtente($utente_da_iscrivere_id);

        $capo_squadra_id = 
            $page == 1 ? 
            $UtenteSessione->ID : 
            null;

        $select_boa = $_POST['SelectBoa'];

        if (isset($_POST['ConfermaIscrizioniForm']))
        {
            $IDGareIscrizioni= $_POST['IDGareIscrizioni'] ?? [];
            $RichiestaBoeGare = $_POST['RichiestaBoeGare'] ?? []; // I valori interni sono gli ID delle gare a cui è stata richiesta la boa
            $acquista_maglietta_1 = $_POST['AcquistaMaglietta1'] ?? 0;
            $acquista_maglietta_2 = $_POST['AcquistaMaglietta2'] ?? 0;
            $acquista_maglietta_3 = $_POST['AcquistaMaglietta3'] ?? 0;
            $IDOpzioniAcquistoEvento = $_POST['IDOpzioniAcquistoEvento'] ?? [];
            $NomiOpzioniAcquistoEvento = $_POST['NomiOpzioniAcquistoEvento'] ?? [];

            $subject = 'ItalianOpenWaterTour: Completamento iscrizioni/Complete Registration';
            $messaggio = '<img src="http://italianopenwatertour.com/wp-content/uploads/2019/01/logo_menu.png" style="margin: 0 auto; text-align: center; padding: 20px;">
                         <br />
                         <p>Ciao ' . $UtenteDaIscrivere->nome . ' ' . $UtenteDaIscrivere->cognome . ',</p>
                         <br /><br />
                         <div>
                         Abbiamo ricevuto la tua richiesta di iscrizione per/we received you request for:
                         </div>';

            // Acquisto 1 Maglietta
            if ($acquista_maglietta_1 == 1)
            {
                $check_acquisto_maglietta_1 = $GareClass->checkUtenteAcquistoMaglietta($UtenteDaIscrivere->ID, 1);

                if ($check_acquisto_maglietta_1 < 1)
                {
                    $GareClass->addUtenteAcquistoMaglietta($UtenteDaIscrivere->ID, 1, PREZZO_MAGLIETTA_1);
                    $messaggio .= '<br /><div>Altro: 1 Maglietta</div>';
                }
            }

            // Acquisto 2 Magliette
            if ($acquista_maglietta_2 == 1)
            {
                $check_acquisto_maglietta_2 = $GareClass->checkUtenteAcquistoMaglietta($UtenteDaIscrivere->ID, 2);

                if ($check_acquisto_maglietta_2 < 1)
                {
                    $GareClass->addUtenteAcquistoMaglietta($UtenteDaIscrivere->ID, 2, PREZZO_MAGLIETTA_2);
                    $messaggio .= '<br /><div>Altro: 2 Magliette</div>';
                }
            }

            // Acquisto 3 Magliette
            if ($acquista_maglietta_3 == 1)
            {
                $check_acquisto_maglietta_3 = $GareClass->checkUtenteAcquistoMaglietta($UtenteDaIscrivere->ID, 3);

                if ($check_acquisto_maglietta_3 < 1)
                {
                    $GareClass->addUtenteAcquistoMaglietta($UtenteDaIscrivere->ID, 3, PREZZO_MAGLIETTA_3);
                    $messaggio .= '<br /><div>Altro: 3 Magliette</div>';
                }
            }

            foreach ($IDGareIscrizioni as $id_gara)
            {
                // Controlla che utente non sia già iscritto alla gara
                $check_gara_utente = $GareClass->checkIscrizioneGara($UtenteDaIscrivere->ID, $id_gara);

                if ($check_gara_utente < 1)
                {
                    $PrezzoGara = $GareClass->getPrezzoGara($id_gara);
                    $TipoPrezzo = $PrezzoGara ? $PrezzoGara->tipo_prezzo : 1;
                    $flag_richiesta_boa = in_array($id_gara, $RichiestaBoeGare) ? true : false;

                    // Inserisce record iscrizione
                    $GareClass->insertGaraUtente(
                        $UtenteDaIscrivere->ID,
                        $id_gara,
                        $TipoPrezzo,
                        $flag_richiesta_boa,
                        $capo_squadra_id
                    );

                    // Estrae dati gara per email
                    $Gara = $GareClass->getGaraConDettagli($id_gara);
                    $messaggio .= '<br /><div>' . $Gara->nome_evento . ': ' . $Gara->nome_gara . '</div>';

                    if ($flag_richiesta_boa)
                        $messaggio .= '<br /><div>Boa per gara: ' . $Gara->nome_evento . ': ' . $Gara->nome_gara . '</div>';
                }
            }

            // Controlla richiesta boe in gare senza nuova iscrizione
            foreach ($RichiestaBoeGare as $id_gara)
            {
                if (!in_array($id_gara, $IDGareIscrizioni))
                {
                    $GareClass->setGaraRichiestaBoa($UtenteDaIscrivere->ID, $id_gara, true);

                    $Gara = $GareClass->getGaraConDettagli($id_gara);
                    $messaggio .= '<br /><div>Boa per gara: ' . $Gara->nome_evento . ': ' . $Gara->nome_gara . '</div>';
                }
            }

            // Aggiunge le opzioni acquisto evento
            foreach($IDOpzioniAcquistoEvento as $key => $opzione_acquisto_evento_id)
            {
                $OpzioneAcquistoEvento = $OpzioniAcquistoClass->getOpzioneAcquistoEvento($opzione_acquisto_evento_id);

                $OpzioniAcquistoClass->inserisciAcquistoOpzioneAcquistoEvento(
                    $UtenteDaIscrivere->ID,
                    $opzione_acquisto_evento_id,
                    $OpzioneAcquistoEvento->oa_prezzo
                );

                $messaggio .= '<br /><div>Opzione acquisto evento: ' . $NomiOpzioniAcquistoEvento[$key] . ' (Prezzo: €' . $OpzioneAcquistoEvento->oa_prezzo . ')</div>';
            }

            $messaggio .= '<br />
            <ul>
    			<li>per completare la tua iscrizione effettua il pagamento con carta di credito (paypal)</li>
    			<li>ricorda di portare la copia cartacea del certificato da consegnare al ritiro pacco gara</li>
    			N.B se non si completa la procedura la vostra iscrizione sarà annullata.<br>
            </ul>
			<ul>
    			<li>to complete your registration make the payment by credit card (paypal) </li>
    			<li>remember to bring your medical certificate when retrieving your race pack</li>
    			N.B if you do not complete the procedure your registration will be canceled.<br>
            </ul>
			
            <p><a href="italianopenwatertour.com">italianopenwatertour.com</a></p>';

            if ($capo_squadra_id == null)
                $FunctionsClass->sendEmail(
                    $UtenteDaIscrivere->email,
                    'info@italianopenwater.com',
                    $subject,
                    $messaggio
                );
        }

        // Boa
        if ($select_boa == 1) // Comprata
        {
            // Se da pagare == 0 Genera nuovo numero boa ItalianOpenWater
            if ($UtenteDaIscrivere->boa_da_pagare == 0)
            {
                $nuovo_numero_boa = $AccountClass->GeneraNuovoNumeroBoaIOW();
                $AccountClass->AggiornaNumeroBoaUtente($nuovo_numero_boa, $UtenteDaIscrivere->ID);
            }

            // Segna boa da pagare
            $AccountClass->AggiornaBoaDaPagareUtente(true, $UtenteDaIscrivere->ID);
        }
        elseif ($select_boa == 3) // Genera nuovo numero boa superiore a 10000
        {
            $nuovo_numero_boa = $AccountClass->GeneraNuovoNumeroBoaDaCasa();
            $AccountClass->AggiornaNumeroBoaUtente($nuovo_numero_boa, $UtenteDaIscrivere->ID);
        }

        if ($page == 0)
            header('location: ' . URL_TO_PUBLIC_FOLDER . 'Account/Iscrizioni');
        else
            header('location: ' . URL_TO_PUBLIC_FOLDER . 'Gare/listaMembriSquadra');
    }

    public function Risultati()
    {
        require_once("Gate.php");

        $GareClass= new GareModel();

        $ListaGareConRisultati = $GareClass->getAllGareConRisultatiVisibili();

        foreach ($ListaGareConRisultati as $key => $gara)
        {
            if ( $GareClass->getTotalePartecipantiGara($gara->ID_gara) < 1 )
                unset($ListaGareConRisultati[$key]);
        }

        $ArrayTemplateAction= ['ListaGareConRisultati' => $ListaGareConRisultati];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Gare/risultati.html', $ArrayTemplateCombined);
    }

    public function ajaxTabellaRisultati()
    {
        require_once("Gate.php");

        $AdminGareClass = new AdminGareModel();
        $GareClass = new GareModel();

        if ( isset($_POST['IDGara']) )
        {
            $IDGara= $_POST['IDGara'];
            $TipoGara= $_POST['TipoGara'];

            $Gara = $GareClass->getGara($IDGara);

            if ($Gara->mostra_risultati == 0)
                die('Risultati gara non visualizzabili!');

            $PosizioneSessoM= 1;
            $PosizioneSessoF= 1;

            // Estrae risultati gara
            if ($TipoGara == 1) $RisultatiGara= $AdminGareClass->getRisultatiGaraGenerale($IDGara);
            elseif ($TipoGara == 2) $RisultatiGara= $AdminGareClass->getRisultatiGaraCrudo($IDGara);
            elseif ($TipoGara == 3) $RisultatiGara= $AdminGareClass->getRisultatiGaraAvis($IDGara);
            elseif ($TipoGara == 4) $RisultatiGara= $AdminGareClass->getRisultatiGaraOver40($IDGara);
            elseif ($TipoGara == 5) $RisultatiGara = $AdminGareClass->getRisultatiGaraOver50($IDGara);
            else die('Tipo gara non trovato!');
            

            if ( count($RisultatiGara) > 0)
            {
                // Totale per Sesso Crudo
                if ($TipoGara == 2)
                {
                    $TotalePartecipantiM = $AdminGareClass->countRisultatiGaraCrudo($IDGara, 'M');
                    $TotalePartecimantiF = $AdminGareClass->countRisultatiGaraCrudo($IDGara, 'F');
                }
                elseif ($TipoGara == 3) // Totale per Sesso Avis
                {
                    $TotalePartecipantiM = $AdminGareClass->countRisultatiGaraAvis($IDGara, 'M');
                    $TotalePartecimantiF = $AdminGareClass->countRisultatiGaraAvis($IDGara, 'F');
                }
                elseif ($TipoGara == 4) // Totale per Over 40
                {
                    $TotalePartecipantiM = $AdminGareClass->countRisultatiGaraOver40($IDGara, 'M');
                    $TotalePartecimantiF = $AdminGareClass->countRisultatiGaraOver40($IDGara, 'F');
                }
                elseif ($TipoGara == 5) // Totale per Over 50
                {
                    $TotalePartecipantiM = $AdminGareClass->countRisultatiGaraOver50($IDGara, 'M');
                    $TotalePartecimantiF = $AdminGareClass->countRisultatiGaraOver50($IDGara, 'F');
                }

                $TabellaRisultati = '<table width="100%" class="table table-striped table-bordered table-hover" id="TabellaRisultati">
                                        <thead>
                                            <tr>
                                                <th>Posizione</th>
                                                <th>Utente</th>
                                                <th>Sesso</th>
                                                <th>Posizione Sesso</th>
                                                <th>Race Time</th>
                                                <th>Ora arrivo</th>
                                                <th>Distacco</th>
                                                <th>Media</th>
                                            </tr>
                                        </thead>
                                        <tbody>';

                foreach($RisultatiGara as $key => $risultato)
                {
                    if ($TipoGara == 1)
                    {
                        $PosizionePerTipo= $risultato->rg_posizione;
                        $PosizioneSessoPerTipo= $risultato->rg_posizione_sesso;
                    }
                    elseif ($TipoGara == 2)
                    {
                        $PosizionePerTipo= $risultato->rg_posizione_crudo;

                        if ($risultato->rg_sesso == 'M')
                        {
                            $PosizioneSessoPerTipo= $PosizioneSessoM;
                            $PosizioneSessoM++;
                        }
                        elseif ($risultato->rg_sesso == 'F')
                        {
                            $PosizioneSessoPerTipo= $PosizioneSessoF;
                            $PosizioneSessoF++;
                        }
                        else
                             $PosizioneSessoPerTipo= 0;
                    }
                    elseif ($TipoGara == 3 || $TipoGara == 4 || $TipoGara == 5)
                    {
                        $PosizionePerTipo= $key + 1;

                        if ($risultato->rg_sesso == 'M')
                        {
                            $PosizioneSessoPerTipo= $PosizioneSessoM;
                            $PosizioneSessoM++;
                        }
                        elseif ($risultato->rg_sesso == 'F')
                        {
                            $PosizioneSessoPerTipo= $PosizioneSessoF;
                            $PosizioneSessoF++;
                        }
                        else
                             $PosizioneSessoPerTipo= 0;
                    }

                    $TabellaRisultati .= '  <tr>
                                                <td>' . $PosizionePerTipo . '</td>
                                                <td>' . $risultato->rg_cognome . ' ' . $risultato->rg_nome . '</td>
                                                <td>' . $risultato->rg_sesso . '</td>
                                                <td>' . $PosizioneSessoPerTipo . '</td>
                                                <td>' . $risultato->rg_racetime . '</td>
                                                <td>' . $risultato->rg_ora_arrivo . '</td>
                                                <td>' . $risultato->rg_distacco . '</td>
                                                <td>' . $risultato->rg_media . '</td>
                                            </tr>';
                }
                

                $TabellaRisultati .='   </tbody>
                                     </table>';

                $Risposta['TabellaRisultati']= $TabellaRisultati;
                $Risposta['Successo'] = 1;
            }
            else
                $Risposta['Successo'] = 0;
    
            echo json_encode($Risposta);
        }
    }

    public function listaMembriSquadra()
    {
        require_once("Gate.php");

        $AccountClass = new AccountModel();
        $SquadreClass = new SquadreModel();
        $GareClass = new GareModel();

        $CapoSquadra = $AccountClass->getUtenteSessione();
        $MembriSquadra = $SquadreClass->getMembriSquadra($CapoSquadra->IDSquadra);
        $totale_da_pagare_squadra = 0;
        $iscrizioni_pendenti = 0;

        foreach ($MembriSquadra as $MembroSquadra)
        {
            $MembroSquadra->totale_da_pagare = $AccountClass->getTotaleDaPagareUtente($MembroSquadra->ID, 1);
            $totale_da_pagare_squadra += $MembroSquadra->totale_da_pagare;

            if ($MembroSquadra->totale_da_pagare > 0)
                $iscrizioni_pendenti = 1;
        }

        $ArrayTemplateAction = 
        [
            'MembriSquadra' => $MembriSquadra, 
            'totale_da_pagare_squadra' => number_format($totale_da_pagare_squadra, 2, '.', ''),
            'iscrizioni_pendenti' => $iscrizioni_pendenti
        ];

        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Gare/lista_membri_squadra.html', $ArrayTemplateCombined);
    }

    public function ajaxAggiungiIscrizioneSquadra()
    {
        require_once("Gate.php");

        $AccountClass = new AccountModel();
        $GareClass = new GareModel();
        $SquadreClass = new SquadreModel();
        $FunctionsClass = new FunctionsModel();

        if (isset($_POST['totale_iscrizione_squadra']))
        {
            $CapoSquadra = $AccountClass->getUtenteSessione();
            $MembriSquadra = $SquadreClass->getMembriSquadra($CapoSquadra->IDSquadra);
            $IscrizioniDiSquadra = $GareClass->getIscrizioniDiSquadra($CapoSquadra->ID);

            $iscrizione_di_squadra_id = $GareClass->insertIscrizioneDiSquadra(
                $CapoSquadra->ID,
                false, // pagato
                $_POST['totale_iscrizione_squadra'],
                $CapoSquadra->IDSquadra
            );

            foreach ($IscrizioniDiSquadra as $Iscrizione)
                $GareClass->collegaIscrizioneAIscrizioneDiSquadra($Iscrizione->ID, $iscrizione_di_squadra_id);

            $subject = 'ItalianOpenWaterTour: Completamento iscrizioni/Complete Registration';

            $messaggio = '
                <img 
                    src="http://italianopenwatertour.com/wp-content/uploads/2019/01/logo_menu.png" 
                    style="margin: 0 auto; text-align: center; padding: 20px;"
                >
                <br />
                <p>Ciao ' . $CapoSquadra->nome . ' ' . $CapoSquadra->cognome . ',</p>
                <br /><br />
                <div>
                    Ti confermiamo che l\'iscrizione squadra è andata a buon fine. / We confirm that the team registration has been successful.
                </div>
                <br />
                <div>
                    Puoi trovare l\'iscrizione squadra nella pagina apposita con l\'id ' . $iscrizione_di_squadra_id . ' / You can find the team registration on the appropriate page with the id ' . $iscrizione_di_squadra_id . '
                </div>
                <br />
                <div>
                    Di seguito le coordinate per fare il bonifico: / Below are the coordinates for making the transfer:
                </div>
                <br />
                <div>
                    Per Bonifico: Conto intestato a Glaciali Societa\' Sportiva Dilettantistica A R.I., IBAN IT41G0623010802000047178141, BIC/SWIFT CRPPIT2P351.
                </div>
            ';

            $FunctionsClass->sendEmail(
                    $CapoSquadra->email,
                    'info@italianopenwater.com',
                    $subject,
                    $messaggio
                );

            echo json_encode('ok');
        }
    }

    public function listaIscrizioniSquadra()
    {
        require_once("Gate.php");

        $ArrayTemplateAction = [];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Gare/iscrizioni_squadre.html', $ArrayTemplateCombined);
    }
}
