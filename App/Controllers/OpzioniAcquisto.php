<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\OpzioniAcquistoModel;

class OpzioniAcquisto extends \Core\Controller
{
    public function Lista()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('OpzioniAcquisto/lista.html', $ArrayTemplateCombined);
    }

    public function CreaOpzioneAcquisto()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('OpzioniAcquisto/creaOpzioneAcquisto.html', $ArrayTemplateCombined);
    }

    public function CreaOpzioneAcquistoConferma()
    {
        require_once("AdminGate.php");

        $OpzioniAcquistoClass = new OpzioniAcquistoModel();

        if (isset($_POST['Nome'], $_POST['Prezzo']))
        {
            $Nome = $_POST['Nome'];
            $Prezzo = $_POST['Prezzo'];

            // Inserisce opzione acquisto
            $OpzioniAcquistoClass->CreaOpzioneAcquisto($Nome, $Prezzo);
        }
        else
            die('Missing POST data');
        
        header("location: ../OpzioniAcquisto/Lista");       
    }

    public function ModificaOpzioneAcquisto()
    {
        require_once("AdminGate.php");

        $id_opzione_acquisto = $this->route_params['id'];

        $OpzioniAcquistoClass = new OpzioniAcquistoModel();

        $OpzioneAcquisto = $OpzioniAcquistoClass->getOpzioneAcquisto($id_opzione_acquisto);

        $ArrayTemplateAction = ['OpzioneAcquisto' => $OpzioneAcquisto];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('OpzioniAcquisto/modificaOpzioneAcquisto.html', $ArrayTemplateCombined);
    }

    public function ModificaOpzioneAcquistoConferma()
    {
        require_once("AdminGate.php");

        $OpzioniAcquistoClass = new OpzioniAcquistoModel();

        if (isset($_POST['Nome'], $_POST['Prezzo']))
        {
            $IDOpzioneAcquisto = $_POST['IDOpzioneAcquisto'];
            $Nome = $_POST['Nome'];
            $Prezzo = $_POST['Prezzo'];

            // Aggiorna opzione acquisto
            $OpzioniAcquistoClass->AggiornaOpzioneAcquisto($IDOpzioneAcquisto, $Nome, $Prezzo);
        }
        else
            die('Missing POST data');

        header("location: ../OpzioniAcquisto/Lista");
    }

    public function ajaxOpzioniAcquisto()
    {
        if (isset($_GET))
        {
            $OpzioniAcquistoClass = new OpzioniAcquistoModel();

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $field = array("ID","nome", "prezzo");
            $order = $field[$column]." ".$dir;

            $total_record    = $OpzioniAcquistoClass->getOpzioniAcquistoAmount();
            $filtered_record = $OpzioniAcquistoClass->getOpzioniAcquistoAmountFiltered($search);
            $OpzioniAcquisto = $OpzioniAcquistoClass->getOpzioniAcquistoPagination($order,$start,$length,$search);
            
            $data_array = array();

            foreach ($OpzioniAcquisto as $opzione_acquisto)
            {
                $modifica= '<a href="' . URL_TO_PUBLIC_FOLDER . 'OpzioniAcquisto/ModificaOpzioneAcquisto/' . $opzione_acquisto->ID . '">
                              <button type="button" class="btn button-azzurro btn-block">Modifica</button>
                            </a>';

                $row = array(
                    'id' => $opzione_acquisto->ID,
                    'nome' => $opzione_acquisto->nome,
                    'prezzo' => $opzione_acquisto->prezzo,
                    'modifica' => $modifica
                );

                array_push($data_array, $row);
            }

            $arr = array('recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $data_array);
            echo json_encode($arr);
        }
    }

    public function ajaxCancellaOpzioneAcquistoEvento()
    {
        require_once("AdminGate.php");

        if (isset($_POST['CancellaOpzioneAcquistoEvento']))
        {
            $IDOpzioneAcquisto = $_POST['IDOpzioneAcquisto'];

            $OpzioniAcquistoClass = new OpzioniAcquistoModel();

            // Controlla prima che l'opzione acquisto evento non sia stata già acquistata dagli utenti
            if (!$OpzioniAcquistoClass->checkOpzioneAcquistoEventoUtilizzata($IDOpzioneAcquisto))
            {
                $OpzioniAcquistoClass->cancellaOpzioneAcquistoEvento($IDOpzioneAcquisto);
                $Risposta['Risposta'] = 1;
            }
            else
                $Risposta['Risposta'] = 0;

            echo json_encode($Risposta);
        }
    }

    public function ajaxAggiungiOpzioneAcquistoEvento()
    {
        require_once("AdminGate.php");

        if (isset($_POST['AggiungiOpzioneAcquistoEvento']))
        {
            $IDOpzioneAcquisto = $_POST['IDOpzioneAcquisto'];
            $IDEvento = $_POST['IDEvento'];

            $OpzioniAcquistoClass = new OpzioniAcquistoModel();

            // Aggiunge se non presente
            $Check = $OpzioniAcquistoClass->checkCollegamentoEventoOpzioneAcquisto($IDEvento, $IDOpzioneAcquisto);

            $IDCollegamento = $Check < 1 ? $OpzioniAcquistoClass->aggiungiOpzioneAcquistoEvento($IDEvento, $IDOpzioneAcquisto) : 0;

            $Risposta['Risposta'] = 'ok';
            $Risposta['IDCollegamento'] = $IDCollegamento;
            echo json_encode($Risposta);
        }
    }
}
