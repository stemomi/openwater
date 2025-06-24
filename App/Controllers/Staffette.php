<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\AccountModel;
use \App\Models\StaffetteModel;
use \App\Models\GareModel;
use \App\Models\SquadreModel;

class Staffette extends \Core\Controller
{
    public function CreaSquadra()
    {
        require_once("Gate.php");

        $StaffetteClass= new StaffetteModel();

        $SquadraCreata= '';
        $StaffetteAperte= $StaffetteClass->getAllStaffetteAperte();

        // Elimina gara se già presente una staffetta creata da questo utente
        foreach ($StaffetteAperte as $key => $gara)
        {
            $ControllaSquadraGiaCreata= $StaffetteClass->checkSquadraGiaCreata($gara->ID_gara);

            if ($ControllaSquadraGiaCreata > 0) unset($StaffetteAperte[$key]);
        }

        if (isset($_POST['ConfermaCreaSquadra']))
        {
            $IDGara= $_POST['IDGara'];
            $NomeStaffetta= $_POST['NomeStaffetta'];

            $IDSquadraCreata= $StaffetteClass->inserisciSquadra($IDGara, $NomeStaffetta);

            header('location: ' . URL_TO_PUBLIC_FOLDER . 'Staffette/ModificaSquadra/' . $IDSquadraCreata);

            //$SquadraCreata= '<div class="text-success text-center">Squadra creata!</div>';
        }

        $ArrayTemplateAction= ['StaffetteAperte' => $StaffetteAperte,
                               'SquadraCreata' => $SquadraCreata];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Staffette/creaSquadra.html', $ArrayTemplateCombined);
    }

    public function LeMieSquadre()
    {
        require_once("Gate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Staffette/leMieSquadre.html', $ArrayTemplateCombined);
    }

    public function ModificaSquadra()
    {
        require_once("Gate.php");

        $id_squadra= $this->route_params['id'];
        $SquadraModificata= '';

        $StaffetteClass= new StaffetteModel();

        $StaffetteAperte= $StaffetteClass->getAllStaffetteAperte();

        $Squadra= $StaffetteClass->getSquadra($id_squadra);

        // Controlli sicurezza per evitare modifiche squadra da parte di altri utenti
        $CreatoreSquadra= isset($Squadra->ID_utente) ? $Squadra->ID_utente : 0;
        if ($_SESSION['IDUtente'] != $CreatoreSquadra) die('Non puoi modificare una squadra di cui non sei il proprietario!');

        if (isset($_POST['ConfermaModificaSquadra']))
        {
            $IDGara= $_POST['IDGara'];
            $NomeStaffetta= $_POST['NomeStaffetta'];

            $ControllaIDGaraSquadra= $StaffetteClass->checkIDGaraSquadra($id_squadra, $IDGara);

            if ($ControllaIDGaraSquadra > 0) $SquadraModificata= '<div class="text-danger text-center">Hai già un altra squadra per questa gara!</div>';
            else
            {
                $StaffetteClass->modificaSquadra($id_squadra, $IDGara, $NomeStaffetta);

                if ($Squadra->ID_gara != $IDGara) $StaffetteClass->eliminaPartecipantiStaffetta($id_squadra);

                $SquadraModificata= '<div class="text-success text-center">Squadra modificata!</div>';
            }
        }

        $Squadra= $StaffetteClass->getSquadra($id_squadra);
        $Partecipanti= $StaffetteClass->getPartecipantiSquadra($id_squadra);

        $ArrayTemplateAction= ['Squadra' => $Squadra,
                               'SquadraModificata' => $SquadraModificata,
                               'StaffetteAperte' => $StaffetteAperte,
                               'Partecipanti' => $Partecipanti];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Staffette/modificaSquadra.html', $ArrayTemplateCombined);
    }

    public function ajaxTabellaLeMieSquadre()
    {
        require_once("Gate.php");

        if (isset($_GET))
        {
            $StaffetteClass= new StaffetteModel();

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $field = array("nome_evento,nome_gara","nome_staffetta","data_evento");
            $order = $field[$column]." ".$dir;

            $total_record    = $StaffetteClass->getLeMieSquadreAmount();
            $filtered_record = $StaffetteClass->getLeMieSquadreAmountFiltered($search);
            $LeMieSquadre = $StaffetteClass->getLeMieSquadrePagination($order,$start,$length,$search);
            
            $data_array = array();

            foreach ($LeMieSquadre as $staffetta)
            {
                // Nomi partecipanti
                $StringaPartecipanti= '';
                $Partecipanti= $staffetta->A_nomi_partecipanti ? explode(',', $staffetta->A_nomi_partecipanti) : null;

                if ($Partecipanti)
                {
                    foreach ($Partecipanti as $key => $partecipante)
                    {
                        $ordine= $key + 1;
                        $StringaPartecipanti .= '<div class="text-body"><b class="text-danger">' . $ordine . ')</b> ' . $partecipante . '</div>';
                    }
                }
                
                $modifica= '';

                $modifica .= '<div class="mb-3">' . $StringaPartecipanti . '</div>';

                if ($staffetta->ID_creatore_staffetta == $_SESSION['IDUtente'])
                    $modifica .= '  <a href="' . URL_TO_PUBLIC_FOLDER . 'Staffette/ModificaSquadra/' . $staffetta->ID_staffetta . '">
                                        <button type="button" class="btn button-azzurro btn-block">Staffettisti</button>';

                $modifica .= '      </a>';

                $row = array(
                    'nome_evento_gara' => $staffetta->nome_evento . ': ' . $staffetta->nome_gara,
                    'nome_staffetta' => $staffetta->nome_staffetta,
                    'data_evento_gara' => $staffetta->data_evento,
                    'modifica' => $modifica
                );

                array_push($data_array, $row);
            }

            $arr = array('recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $data_array);
            echo json_encode($arr);
        }

    }

    public function ajaxCancellaPartecipazione()
    {
        require_once("Gate.php");

        if (isset($_POST['CancellaPartecipazione']))
        {
            $IDPartecipazione= $_POST['IDPartecipazione'];

            $StaffetteClass= new StaffetteModel();
            $StaffetteClass->cancellaPartecipazione($IDPartecipazione);

            $Risposta['Risposta']= 'ok';
            echo json_encode($Risposta);
        }
    }

    public function ajaxAggiungiPartecipazione()
    {
        require_once("Gate.php");

        if (isset($_POST['AggiungiPartecipazione']))
        {
            $IDTessera= $_POST['IDTessera'];
            $IDSquadra= $_POST['IDSquadra'];
            $Ordine= $_POST['Ordine'];
            $IDGaraAttuale = $_POST['IDGaraAttuale'];

            $AccountClass= new AccountModel();
            $StaffetteClass= new StaffetteModel();
            $GareClass= new GareModel();

            $IDPartecipazione= '';
            $Errore= '';
            $ArrayUtenteGareIscritto= [];
            $IscrizionePagata = 0;

            // Estrae dati utente
            $Partecipante= $AccountClass->getUtente($IDTessera);

            // Controlla tutte le gare staffette o combinate a cui è iscritto il paziente
            $UtenteGareIscritto= $GareClass->getAllGareAperteUtenteStaffettaOCombinata($IDTessera);

            foreach ($UtenteGareIscritto as $gara)
            {
                if ($gara->A_g_staffetta == 1)
                {
                    if (!in_array($gara->ID_gara, $ArrayUtenteGareIscritto))
                    {
                        $ArrayUtenteGareIscritto[]= $gara->ID_gara;

                        if ($gara->ID_gara == $IDGaraAttuale && $gara->pagato == 1)
                            $IscrizionePagata = 1;
                    }
                }
                elseif ($gara->A_g_combinata == 1)
                {
                    $GareDaCombinata= $GareClass->getAllGareDaCombinata($gara->ID_gara);

                    foreach($GareDaCombinata as $gara_collegata)
                    {
                        if (!in_array($gara_collegata->ID_gara_collegata, $ArrayUtenteGareIscritto))
                        {
                            $ArrayUtenteGareIscritto[]= $gara_collegata->ID_gara_collegata;

                            if ($gara_collegata->ID_gara_collegata == $IDGaraAttuale && $gara->pagato == 1)
                                $IscrizionePagata = 1;
                        }
                    }
                }
            }

            // Controlla che l'utente sia effettivamente iscritto alla gara
            if(in_array($IDGaraAttuale, $ArrayUtenteGareIscritto))
            {
                // Controlla che l'iscrizione alla gara sia stata pagata
                if ($IscrizionePagata == 1)
                {
                    // Controlla che l'utente non sia già assegnato alla staffetta
                    $ControlloPartecipanteStaffetta= $StaffetteClass->checkPartecipanteStaffetta($Partecipante->ID, $IDSquadra);
                    if ($ControlloPartecipanteStaffetta < 1)
                    {
                        // Controlla che l'utente non sia già assegnato ad altre staffette per la stessa gara
                        $ControlloPartecipanteStaffetta= $StaffetteClass->checkPartecipanteInStaffetteStessaGara($Partecipante->ID, $IDGaraAttuale);

                        if ($ControlloPartecipanteStaffetta < 1)
                        {
                            // Controlla che non si sia raggiunto il numero massimo di partecipanti
                            $ControlloMaxPartecipantiStaffetta= $StaffetteClass->checkMaxPartecipantiStaffetta($IDSquadra);

                            if ($ControlloMaxPartecipantiStaffetta < 3) 
                            {
                                // Controlla che non ci sia un altro partecipante assegnato allo stesso ordine di partenza
                                $ControlloOrdineStaffetta= $StaffetteClass->checkOrdineStaffetta($IDSquadra, $Ordine);

                                if ($ControlloOrdineStaffetta < 1) 
                                {
                                    // Aggiunge partecipante alla squadra
                                    $IDPartecipazione= $StaffetteClass->inserisciPartecipante($IDSquadra, $IDTessera, $Ordine);
                                }
                                else
                                    $Errore = 'Un altro partecipante è già assegnato nella posizione di partenza ' . $Ordine . ', scegliere un altra posizione!';
                            }
                            else
                                $Errore = 'La squadra può essere composta massimo da 3 atleti!';
                        }
                        else
                            $Errore = 'Partecipante già presente in un\'altra squadra per la stessa gara!';
                    }
                    else
                        $Errore = 'Partecipante già presente in squadra!';
                }
                else
                    $Errore = 'Il partecipante selezionato non può essere aggiunto perchè non ha pagato l\'iscrizione alla gara!';
            }
            else
                $Errore = 'Il partecipante selezionato non può essere aggiunto perchè non iscritto alla gara!';
            
            
            $Risposta['Errore']= $Errore;
            $Risposta['IDPartecipazione']= $IDPartecipazione;
            $Risposta['Nome_Cognome']= $Partecipante->nome . ' ' . $Partecipante->cognome;
            $Risposta['OrdinePartecipazione']= $Ordine;
            echo json_encode($Risposta);
        }
    }

}
