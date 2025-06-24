<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\ShareModel;
use \App\Models\GareModel;
use \App\Models\AdminGareModel;
use \App\Models\AccountModel;
use stdClass;

class Share extends \Core\Controller
{
    public function CurrentRaceResults()
    {
        $AdminGareClass = new AdminGareModel();

        $gara_id = $this->route_params['garaid'];
        $autoscroll = $this->route_params['autoscroll'];

        $Gara = $AdminGareClass->getGara($gara_id);

        // Controlla che la gara abbia i risultati visionabili
        if ($Gara->mostra_risultati == 0)
            die('I risultati per la gara non sono visualizzabili al momento');

        $RisultatiGaraGenerale = $AdminGareClass->getRisultatiGaraGenerale($gara_id);
        $RisultatiGaraCrudo = $AdminGareClass->getRisultatiGaraCrudo($gara_id);
        $RisultatiGaraAvis = $AdminGareClass->getRisultatiGaraAvis($gara_id);
        $RisultatiGaraOver40 = $AdminGareClass->getRisultatiGaraOver40($gara_id);
        $RisultatiGaraOver50 = $AdminGareClass->getRisultatiGaraOver50($gara_id);
        $TipoGara = 1; // Generale

        $PosizioneSessoM = 1;
        $PosizioneSessoF = 1;

        // Contatori special guests
        $special_guests_m_generale = 0;
        $special_guests_f_generale = 0;
        $special_guests_m_crudo = 0;
        $special_guests_f_crudo = 0;
        $special_guests_m_over_40 = 0;
        $special_guests_f_over_40 = 0;
        $special_guests_m_over_50 = 0;
        $special_guests_f_over_50 = 0;

        foreach($RisultatiGaraGenerale as $key => $risultato)
        {
            if ($TipoGara == 1)
            {
                $PosizionePerTipo= $risultato->rg_posizione;
                $PosizioneSessoPerTipo= $risultato->rg_posizione_sesso;

                // Se squadra = 'Special Guest' incremento contatore in base al sesso
                if ($risultato->s_nome_squadra == 'Special Guest')
                {
                    if ($risultato->rg_sesso == 'M')
                    {
                        $special_guests_m_generale++;
                        $PosizioneSessoPerTipo = 0;
                    }
                    else 
                    {
                        $special_guests_f_generale++;
                        $PosizioneSessoPerTipo = 0;
                    }
                }

                // Riduco la posizione dei partecipanti successivi in base al sesso dei special guests
                if ($PosizioneSessoPerTipo != 0)
                    $PosizioneSessoPerTipo =
                        $risultato->rg_sesso == 'M' ?
                        $PosizioneSessoPerTipo - $special_guests_m_generale :
                        $PosizioneSessoPerTipo - $special_guests_f_generale;
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

            $risultato->rg_posizione_per_tipo = $PosizionePerTipo;
            $risultato->rg_posizione_sesso_per_tipo = $PosizioneSessoPerTipo;

            $risultato->foto_profilo =
                $risultato->u_foto_profilo != '' && file_exists(PUBLIC_PATH . $risultato->u_foto_profilo) ?
                URL_TO_PUBLIC_FOLDER . $risultato->u_foto_profilo :
                URL_TO_PUBLIC_FOLDER . 'img/foto_profilo_default.png';

            $risultato->foto_squadra =
                $risultato->s_foto_squadra != '' && file_exists(PUBLIC_PATH . $risultato->s_foto_squadra) ?
                URL_TO_PUBLIC_FOLDER . $risultato->s_foto_squadra :
                '';

            $risultato->colore = 
                $risultato->rg_sesso == 'M' ?
                '#0a6478' :
                '#cd4eb1';
        }

        // Crudo per genere
        $PosizioneSessoM = 1;
        $PosizioneSessoF = 1;

        foreach($RisultatiGaraCrudo as $key => $risultato_crudo)
        {
            if ($risultato_crudo->rg_sesso == 'M')
            {
                $PosizioneSessoPerTipo = $PosizioneSessoM;
                $PosizioneSessoM++;
            }
            elseif ($risultato_crudo->rg_sesso == 'F')
            {
                $PosizioneSessoPerTipo = $PosizioneSessoF;
                $PosizioneSessoF++;
            }
            else
                 $PosizioneSessoPerTipo = 0;

            // Se squadra = 'Special Guest' incremento contatore in base al sesso
            if ($risultato_crudo->s_nome_squadra == 'Special Guest')
            {
                if ($risultato_crudo->rg_sesso == 'M')
                {
                    $special_guests_m_crudo++;
                    $PosizioneSessoPerTipo = 0;
                }
                else 
                {
                    $special_guests_f_crudo++;
                    $PosizioneSessoPerTipo = 0;
                }
            }

            // Riduco la posizione dei partecipanti successivi in base al sesso dei special guests
            if ($PosizioneSessoPerTipo != 0)
                $PosizioneSessoPerTipo =
                    $risultato_crudo->rg_sesso == 'M' ?
                    $PosizioneSessoPerTipo - $special_guests_m_crudo :
                    $PosizioneSessoPerTipo - $special_guests_f_crudo;

            // Inietta valore
            foreach($RisultatiGaraGenerale as $key => &$risultato)
            {
                if ($risultato->rg_id == $risultato_crudo->rg_id)
                    $risultato->rg_posizione_crudo_per_sesso = $PosizioneSessoPerTipo;
            }
        }

        // Avis generale
        foreach($RisultatiGaraAvis as $key => $risultato_avis)
        {
            // Inietta valore
            foreach($RisultatiGaraGenerale as $key2 => &$risultato)
            {
                if ($risultato->rg_id == $risultato_avis->rg_id)
                    $risultato->rg_posizione_avis_generale = $key + 1;
            }
        }

        // Over 40 per genere
        $PosizioneSessoM = 1;
        $PosizioneSessoF = 1;

        foreach($RisultatiGaraOver40 as $key => $risultato_over_40)
        {
            if ($risultato_over_40->rg_sesso == 'M')
            {
                $PosizioneSessoPerTipo = $PosizioneSessoM;
                $PosizioneSessoM++;
            }
            elseif ($risultato_over_40->rg_sesso == 'F')
            {
                $PosizioneSessoPerTipo = $PosizioneSessoF;
                $PosizioneSessoF++;
            }
            else
                 $PosizioneSessoPerTipo = 0;

            // Se squadra = 'Special Guest' incremento contatore in base al sesso
            if ($risultato_over_40->s_nome_squadra == 'Special Guest')
            {
                if ($risultato_over_40->rg_sesso == 'M')
                {
                    $special_guests_m_over_40++;
                    $PosizioneSessoPerTipo = 0;
                }
                else 
                {
                    $special_guests_f_over_40++;
                    $PosizioneSessoPerTipo = 0;
                }
            }

            // Riduco la posizione dei partecipanti successivi in base al sesso dei special guests
            if ($PosizioneSessoPerTipo != 0)
                $PosizioneSessoPerTipo =
                    $risultato_over_40->rg_sesso == 'M' ?
                    $PosizioneSessoPerTipo - $special_guests_m_over_40 :
                    $PosizioneSessoPerTipo - $special_guests_f_over_40;

            // Inietta valore
            foreach($RisultatiGaraGenerale as $key => &$risultato)
            {
                if ($risultato->rg_id == $risultato_over_40->rg_id)
                    $risultato->rg_posizione_over_40_per_sesso = $PosizioneSessoPerTipo;
            }
        }

        // Over 50 per genere
        $posizione_sesso_m = 1;
        $posizione_sesso_f = 1;

        foreach ($RisultatiGaraOver50 as $key => $risultato_over_50)
        {
            if ($risultato_over_50->rg_sesso == 'M')
            {
                $PosizioneSessoPerTipo = $posizione_sesso_m;
                $posizione_sesso_m++;
            }
            elseif ($risultato_over_50->rg_sesso == 'F')
            {
                $PosizioneSessoPerTipo = $posizione_sesso_f;
                $posizione_sesso_f++;
            }
            else
                 $PosizioneSessoPerTipo = 0;

            // Se squadra = 'Special Guest' incremento contatore in base al sesso
            if ($risultato_over_50->s_nome_squadra == 'Special Guest')
            {
                if ($risultato_over_50->rg_sesso == 'M')
                {
                    $special_guests_m_over_50++;
                    $PosizioneSessoPerTipo = 0;
                }
                else 
                {
                    $special_guests_f_over_50++;
                    $PosizioneSessoPerTipo = 0;
                }
            }

            // Riduco la posizione dei partecipanti successivi in base al sesso dei special guests
            if ($PosizioneSessoPerTipo != 0)
                $PosizioneSessoPerTipo =
                    $risultato_over_50->rg_sesso == 'M' ?
                    $PosizioneSessoPerTipo - $special_guests_m_over_50 :
                    $PosizioneSessoPerTipo - $special_guests_f_over_50;

            // Inietta valore
            foreach ($RisultatiGaraGenerale as $key => &$risultato)
            {
                if ($risultato->rg_id == $risultato_over_50->rg_id)
                    $risultato->rg_posizione_over_50_per_sesso = $PosizioneSessoPerTipo;
            }
        }

        $ArrayTemplateAction = [
            'URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER,
            'gara_id' => $gara_id,
            'Gara' => $Gara,
            'RisultatiGara' => $RisultatiGaraGenerale,
            'autoscroll' => $autoscroll
        ];

        View::renderTemplate('Share/CurrentRaceResults.html', $ArrayTemplateAction);
    }

    public function CurrentRaceRelayResults()
    {
        $AdminGareClass = new AdminGareModel();
        $AccountClass = new AccountModel();

        $gara_id = $this->route_params['garaid'];
        $autoscroll = $this->route_params['autoscroll'];

        $Gara = $AdminGareClass->getGara($gara_id);

        // Controlla che la gara abbia i risultati visionabili
        if ($Gara->mostra_risultati == 0)
            die('I risultati per la gara non sono visualizzabili al momento');

        $RisultatiGaraStaffetta = $AdminGareClass->getRisultatiGaraStaffetta($gara_id);

        foreach($RisultatiGaraStaffetta as $key => $risultato)
        {
            // Estrae foto utente 1
            $Utente_1 = $AccountClass->getUtente($risultato->ID_Utente1);
            $risultato->foto_profilo_1 =
                $Utente_1 && $Utente_1->foto_profilo != '' && file_exists(PUBLIC_PATH . $Utente_1->foto_profilo) ?
                URL_TO_PUBLIC_FOLDER . $Utente_1->foto_profilo :
                URL_TO_PUBLIC_FOLDER . 'img/foto_profilo_default.png';

            // Estrae foto utente 2
            $Utente_2 = $AccountClass->getUtente($risultato->ID_Utente2);
            $risultato->foto_profilo_2 =
                $Utente_2 && $Utente_2->foto_profilo != '' && file_exists(PUBLIC_PATH . $Utente_2->foto_profilo) ?
                URL_TO_PUBLIC_FOLDER . $Utente_2->foto_profilo :
                URL_TO_PUBLIC_FOLDER . 'img/foto_profilo_default.png';

            // Estrae foto utente 3
            $Utente_3 = $AccountClass->getUtente($risultato->ID_Utente3);
            $risultato->foto_profilo_3 =
                $Utente_3 && $Utente_3->foto_profilo != '' && file_exists(PUBLIC_PATH . $Utente_3->foto_profilo) ?
                URL_TO_PUBLIC_FOLDER . $Utente_3->foto_profilo :
                URL_TO_PUBLIC_FOLDER . 'img/foto_profilo_default.png';

            // Estrae foto staffetta (l'ID nei CSV è sbagliato, prendo dalla squadra collegata al primo utente)
            $risultato->foto_squadra =
                $Utente_1 && $Utente_1->S_foto != '' && file_exists(PUBLIC_PATH . $Utente_1->S_foto) ?
                URL_TO_PUBLIC_FOLDER . $Utente_1->S_foto :
                URL_TO_PUBLIC_FOLDER . 'img/no_image_available.png';

            $risultato->colore = 
                $risultato->categoria == 'MX' ?
                '#0a6478' :
                '#cd4eb1';
        }

        $ArrayTemplateAction = [
            'URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER,
            'gara_id' => $gara_id,
            'Gara' => $Gara,
            'RisultatiGara' => $RisultatiGaraStaffetta,
            'autoscroll' => $autoscroll
        ];

        View::renderTemplate('Share/CurrentRaceRelayResults.html', $ArrayTemplateAction);
    }

    public function SharedRaceResult()
    {
        $ShareClass = new ShareModel();

        $token = $this->route_params['token'];

        $SharedRaceResult = $ShareClass->getCondivisioneRisultatoGaraByToken($token);

        $ArrayTemplateAction = [
            'URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER,
            'URL_SHARE_RACE_RESULTS' => URL_SHARE_RACE_RESULTS,
            'URL_SHARE_RACE_RESULTS_NO_SLASHES' => URL_SHARE_RACE_RESULTS_NO_SLASHES,
            'SharedRaceResult' => $SharedRaceResult
        ];

        View::renderTemplate('Share/SharedRaceResult.html', $ArrayTemplateAction);
    }

    public function SharedUserRaceResult()
    {
        $GareClass = new GareModel();
        $AdminGareClass = new AdminGareModel();

        $risultato_id = $this->route_params['risultatoid'];

        $SharedRaceResult = $GareClass->getRisultatoGara($risultato_id);

        $RisultatiGaraGenerale = $AdminGareClass->getRisultatiGaraGenerale($SharedRaceResult->A_gara_ID);
        $RisultatiGaraCrudo = $AdminGareClass->getRisultatiGaraCrudo($SharedRaceResult->A_gara_ID);
        $RisultatiGaraAvis = $AdminGareClass->getRisultatiGaraAvis($SharedRaceResult->A_gara_ID);
        $RisultatiGaraOver40 = $AdminGareClass->getRisultatiGaraOver40($SharedRaceResult->A_gara_ID);
        $RisultatiGaraOver50 = $AdminGareClass->getRisultatiGaraOver50($SharedRaceResult->A_gara_ID);

        $foto_profilo =
            $SharedRaceResult->A_utenti_foto_profilo != '' && file_exists(PUBLIC_PATH . $SharedRaceResult->A_utenti_foto_profilo) ?
            URL_TO_PUBLIC_FOLDER . $SharedRaceResult->A_utenti_foto_profilo :
            URL_TO_PUBLIC_FOLDER . 'img/foto_profilo_default.png';

        $foto_squadra =
            $SharedRaceResult->A_foto_squadra != '' && file_exists(PUBLIC_PATH . $SharedRaceResult->A_foto_squadra) ?
            URL_TO_PUBLIC_FOLDER . $SharedRaceResult->A_foto_squadra :
            '';

        $totale_partecipanti = $GareClass->getTotalePartecipantiGara($SharedRaceResult->A_gara_ID);

        // Contatori special guests
        $special_guests_m_generale = 0;
        $special_guests_f_generale = 0;
        $special_guests_m_crudo = 0;
        $special_guests_f_crudo = 0;
        $special_guests_m_over_40 = 0;
        $special_guests_f_over_40 = 0;
        $special_guests_m_over_50 = 0;
        $special_guests_f_over_50 = 0;

        // Cerco le informazioni del special guest
        foreach ($RisultatiGaraGenerale as $risultato)
        {
            $PosizioneSessoPerTipo = $risultato->rg_posizione_sesso;
            
            // Se squadra = 'Special Guest' incremento contatore in base al sesso
            if ($risultato->s_nome_squadra == 'Special Guest')
            {
                if ($risultato->rg_sesso == 'M')
                {
                    $special_guests_m_generale++;
                    $PosizioneSessoPerTipo = 0;
                }
                else 
                {
                    $special_guests_f_generale++;
                    $PosizioneSessoPerTipo = 0;
                }
            }

            // Riduco la posizione dei partecipanti successivi in base al sesso dei special guests
            if ($PosizioneSessoPerTipo != 0)
                $PosizioneSessoPerTipo =
                    $risultato->rg_sesso == 'M' ?
                    $PosizioneSessoPerTipo - $special_guests_m_generale :
                    $PosizioneSessoPerTipo - $special_guests_f_generale;

            if ($risultato->rg_id == $SharedRaceResult->ID)
            {
                if($risultato->rg_sesso == 'M')
                    $SharedRaceResult->posizione_sesso = $SharedRaceResult->posizione_sesso - $special_guests_m_generale;
                else
                    $SharedRaceResult->posizione_sesso = $SharedRaceResult->posizione_sesso - $special_guests_f_generale;
                
                break;
            }
        }

        // Avis generale
        $SharedRaceResult->rg_posizione_avis_generale = 0;

        if ($SharedRaceResult->A_utenti_donatore_avis == 1)
        {
            foreach($RisultatiGaraAvis as $key => $risultato_avis)
            {
                // Inietta valore
                if ($SharedRaceResult->ID == $risultato_avis->rg_id)
                    $SharedRaceResult->rg_posizione_avis_generale = $key + 1;
            }
        }

        // Crudo per genere
        $SharedRaceResult->rg_posizione_crudo_per_sesso = 0;

        if ($SharedRaceResult->crudo_flag > 0)
        {
            $PosizioneSessoM = 1;
            $PosizioneSessoF = 1;

            foreach($RisultatiGaraCrudo as $key => $risultato_crudo)
            {
                if ($risultato_crudo->rg_sesso == 'M')
                {
                    $PosizioneSessoPerTipo = $PosizioneSessoM;
                    $PosizioneSessoM++;
                }
                elseif ($risultato_crudo->rg_sesso == 'F')
                {
                    $PosizioneSessoPerTipo = $PosizioneSessoF;
                    $PosizioneSessoF++;
                }
                else
                    $PosizioneSessoPerTipo = 0;

                // Se squadra = 'Special Guest' incremento contatore in base al sesso
                if ($risultato_crudo->s_nome_squadra == 'Special Guest')
                {
                    if ($risultato_crudo->rg_sesso == 'M')
                    {
                        $special_guests_m_crudo++;
                        $PosizioneSessoPerTipo = 0;
                    }
                    else 
                    {
                        $special_guests_f_crudo++;
                        $PosizioneSessoPerTipo = 0;
                    }
                }

                // Riduco la posizione dei partecipanti successivi in base al sesso dei special guests
                if ($PosizioneSessoPerTipo != 0)
                    $PosizioneSessoPerTipo =
                        $risultato_crudo->rg_sesso == 'M' ?
                        $PosizioneSessoPerTipo - $special_guests_m_crudo :
                        $PosizioneSessoPerTipo - $special_guests_f_crudo;

                // Inietta valore
                if ($SharedRaceResult->ID == $risultato_crudo->rg_id)
                    $SharedRaceResult->rg_posizione_crudo_per_sesso = $PosizioneSessoPerTipo;
            }
        }

        // Over 40 per genere
        $SharedRaceResult->rg_posizione_over_40_per_sesso = 0;

        if ($SharedRaceResult->categoria == 'O40M' || $SharedRaceResult->categoria == 'O40F')
        {
            $PosizioneSessoM = 1;
            $PosizioneSessoF = 1;

            foreach($RisultatiGaraOver40 as $key => $risultato_over_40)
            {
                if ($risultato_over_40->rg_sesso == 'M')
                {
                    $PosizioneSessoPerTipo = $PosizioneSessoM;
                    $PosizioneSessoM++;
                }
                elseif ($risultato_over_40->rg_sesso == 'F')
                {
                    $PosizioneSessoPerTipo = $PosizioneSessoF;
                    $PosizioneSessoF++;
                }
                else
                     $PosizioneSessoPerTipo = 0;

                // Se squadra = 'Special Guest' incremento contatore in base al sesso
                if ($risultato_over_40->s_nome_squadra == 'Special Guest')
                {
                    if ($risultato_over_40->rg_sesso == 'M')
                    {
                        $special_guests_m_over_40++;
                        $PosizioneSessoPerTipo = 0;
                    }
                    else 
                    {
                        $special_guests_f_over_40++;
                        $PosizioneSessoPerTipo = 0;
                    }
                }

                // Riduco la posizione dei partecipanti successivi in base al sesso dei special guests
                if ($PosizioneSessoPerTipo != 0)
                    $PosizioneSessoPerTipo =
                        $risultato_over_40->rg_sesso == 'M' ?
                        $PosizioneSessoPerTipo - $special_guests_m_over_40 :
                        $PosizioneSessoPerTipo - $special_guests_f_over_40;

                // Inietta valore
                if ($SharedRaceResult->ID == $risultato_over_40->rg_id)
                    $SharedRaceResult->rg_posizione_over_40_per_sesso = $PosizioneSessoPerTipo;
            }
        }

        // Over 50 per genere
        $SharedRaceResult->rg_posizione_over_50_per_sesso = 0;

        if ($SharedRaceResult->categoria == 'O50M' || $SharedRaceResult->categoria == 'O50F')
        {
            $posizione_sesso_m = 1;
            $posizione_sesso_f = 1;

            foreach($RisultatiGaraOver50 as $key => $risultato_over_50)
            {
                if ($risultato_over_50->rg_sesso == 'M')
                {
                    $PosizioneSessoPerTipo = $posizione_sesso_m;
                    $posizione_sesso_m++;
                }
                elseif ($risultato_over_50->rg_sesso == 'F')
                {
                    $PosizioneSessoPerTipo = $posizione_sesso_f;
                    $posizione_sesso_f++;
                }
                else
                     $PosizioneSessoPerTipo = 0;

                // Se squadra = 'Special Guest' incremento contatore in base al sesso
                if ($risultato_over_50->s_nome_squadra == 'Special Guest')
                {
                    if ($risultato_over_50->rg_sesso == 'M')
                    {
                        $special_guests_m_over_50++;
                        $PosizioneSessoPerTipo = 0;
                    }
                    else 
                    {
                        $special_guests_f_over_50++;
                        $PosizioneSessoPerTipo = 0;
                    }
                }

                // Riduco la posizione dei partecipanti successivi in base al sesso dei special guests
                if ($PosizioneSessoPerTipo != 0)
                    $PosizioneSessoPerTipo =
                        $risultato_over_50->rg_sesso == 'M' ?
                        $PosizioneSessoPerTipo - $special_guests_m_over_50 :
                        $PosizioneSessoPerTipo - $special_guests_f_over_50;

                // Inietta valore
                if ($SharedRaceResult->ID == $risultato_over_50->rg_id)
                    $SharedRaceResult->rg_posizione_over_50_per_sesso = $PosizioneSessoPerTipo;
            }
        }

        $SharedRaceResult->colore = 
            $SharedRaceResult->A_utenti_sesso == 1 ?
            '#0a6478' :
            '#cd4eb1';

        $ArrayTemplateAction = [
            'URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER,
            'URL_SHARE_RACE_RESULTS' => URL_SHARE_RACE_RESULTS,
            'URL_SHARE_RACE_RESULTS_NO_SLASHES' => URL_SHARE_RACE_RESULTS_NO_SLASHES,
            'URL_EVENTI_FOTO' => URL_EVENTI_FOTO,
            'SharedRaceResult' => $SharedRaceResult,
            'foto_profilo' => $foto_profilo,
            'foto_squadra' => $foto_squadra,
            'totale_partecipanti' => $totale_partecipanti,
            'risultato_id' => $risultato_id
        ];

        View::renderTemplate('Share/congratulazioni.html', $ArrayTemplateAction);
    }

    public function ajaxGeneraCondivisioneRisultatoGara()
    {
        require_once("Gate.php");

        $ShareClass = new ShareModel();

        if (isset($_POST['IDRisultato']))
        {
            $IDRisultato = $_POST['IDRisultato'];
            $token = bin2hex(random_bytes(32));
            $link_immagine = bin2hex(random_bytes(32)) . '.jpg';
            $Risposta = (object) [];

            // Controlla che non sia già presente un record
            $CondivisioneRisultato = $ShareClass->getCondivisioneRisultatoGara($IDRisultato);

            // Elimina vecchia condivisione
            if ($CondivisioneRisultato)
            {
                // Elimina immagine
                if (file_exists(PATH_SHARE_RACE_RESULTS . $CondivisioneRisultato->immagine))
                    unlink(PATH_SHARE_RACE_RESULTS . $CondivisioneRisultato->immagine);

                // Elimina condivisione
                $ShareClass->deleteCondivisioneRisultatoGara($CondivisioneRisultato->ID);
            }

            // Genera immagine
            $EsitoGeneraImmagine = $ShareClass->generaImmagineCondivisioneRisultatoGara($IDRisultato, $link_immagine);

            if ($EsitoGeneraImmagine)
            {
                // Inserisce record
                $ShareClass->InserisciCondivisioneRisultatoGara($IDRisultato, $token, $link_immagine);
                $Risposta->esito = true;
                $Risposta->token = $token;
            }
            else
                $Risposta->esito = false;


            echo json_encode($Risposta);
        }
    }
}
