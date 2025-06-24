<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\AdminGareIndividualiModel;
use \App\Models\GareIndividualiModel;
use \App\Models\GareModel;
use \App\Models\AccountModel;

class AdminGareIndividuali extends \Core\Controller
{
    public function InserisciEvento()
    {
        require_once("AdminGate.php");

        $AdminGareIndividualiClass= new AdminGareIndividualiModel();

        $EventoInserito= '';

        if (isset($_POST['Nome']) && isset($_POST['Data']))
        {
            $Nome= $_POST['Nome'];
            $Data= $_POST['Data'];
            $DataAperturaIscrizioni = $_POST['DataAperturaIscrizioni'];

            $AdminGareIndividualiClass->InserisciEvento($Nome, $Data, $DataAperturaIscrizioni);

            $EventoInserito= '<div class="text-success text-center">Evento inserito!</div>';
        }

        $ArrayTemplateAction= ['EventoInserito' => $EventoInserito];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGareIndividuali/inserisciEvento.html', $ArrayTemplateCombined);
    }
    public function InserisciGara()
    {
        require_once("AdminGate.php");

        $AdminGareIndividualiClass= new AdminGareIndividualiModel();
        $Eventi= $AdminGareIndividualiClass->getEventi(1);

        $GaraInserita= '';

        if (isset($_POST['IDEvento']))
        {
            $IDEvento= $_POST['IDEvento'];
            $Nome= $_POST['Nome'];
            $Prezzo= $_POST['Prezzo'];

            $IDGaraAggiunta= $AdminGareIndividualiClass->InserisciGara(
              $IDEvento,
              $Nome,
              $Prezzo
            );

            if ($IDGaraAggiunta) header('location: ' . URL_TO_PUBLIC_FOLDER . 'AdminGareIndividuali/ModificaGara/' . $IDGaraAggiunta);
        }

        $ArrayTemplateAction= ['Eventi' => $Eventi,
                               'GaraInserita' => $GaraInserita
                              ];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGareIndividuali/inserisciGara.html', $ArrayTemplateCombined);
    }

    public function ModificaEvento()
    {
        require_once("AdminGate.php");

        $id_evento= $this->route_params['id'];

        $AdminGareIndividualiClass= new AdminGareIndividualiModel();
        $Evento= $AdminGareIndividualiClass->getEvento($id_evento);

        $ArrayTemplateAction= ['Evento' => $Evento];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGareIndividuali/modificaEvento.html', $ArrayTemplateCombined);
    }

    public function modificaEventoConfirm()
    {
        require_once("AdminGate.php");

        $AdminGareIndividualiClass= new AdminGareIndividualiModel();

        if ( isset($_POST['ConfermaModificaEvento']) )
        {
            $IDEvento= $_POST['IDEvento'];
            $Nome= $_POST['Nome'];
            $Data= $_POST['Data'];
            $DataAperturaIscrizioni= $_POST['DataAperturaIscrizioni'];

            $AdminGareIndividualiClass->AggiornaEvento($IDEvento, $Nome, $Data, $DataAperturaIscrizioni);
            $Evento= $AdminGareIndividualiClass->getEvento($IDEvento);

            $EventoModificato= '<div class="text-success text-center">Evento Modificato!</div>';
        }

        $ArrayTemplateAction= ['Evento' => $Evento,
                               'EventoModificato' => $EventoModificato];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGareIndividuali/modificaEvento.html', $ArrayTemplateCombined);
    }

    public function ModificaGara()
    {
        require_once("AdminGate.php");

        $id_gara= $this->route_params['id'];

        $AdminGareIndividualiClass= new AdminGareIndividualiModel();
        $Gara= $AdminGareIndividualiClass->getGara($id_gara);
        $Eventi= $AdminGareIndividualiClass->getEventi();

        $ArrayTemplateAction= ['Gara' => $Gara,
                               'Eventi' => $Eventi];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGareIndividuali/modificaGara.html', $ArrayTemplateCombined);
    }

    public function modificaGaraConfirm()
    {
        require_once("AdminGate.php");

        $AdminGareIndividualiClass= new AdminGareIndividualiModel();
        $Eventi= $AdminGareIndividualiClass->getEventi();

        if ( isset($_POST['ConfermaModificaGara']) )
        {
            $IDGara= $_POST['IDGara'];
            $IDEvento= $_POST['IDEvento'];
            $Nome= $_POST['Nome'];
            $Prezzo= $_POST['Prezzo'];
            $MostraRisultati= isset($_POST['MostraRisultati']) ? true : false;

            $AdminGareIndividualiClass->AggiornaGara(
              $IDGara,
              $IDEvento,
              $Nome,
              $Prezzo,
              $MostraRisultati);

            $Gara= $AdminGareIndividualiClass->getGara($IDGara);

            $GaraModificata= '<div class="text-success text-center">Gara Modificata!</div>';
        }

        $ArrayTemplateAction= ['Gara' => $Gara,
                               'GaraModificata' => $GaraModificata,
                               'Eventi' => $Eventi];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGareIndividuali/modificaGara.html', $ArrayTemplateCombined);
    }

    public function ListaEventi()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGareIndividuali/listaEventi.html', $ArrayTemplateCombined);
    }

    public function ajaxTabellaListaEventi()
    {
        if (isset($_GET))
        {
            $AdminGareIndividualiClass= new AdminGareIndividualiModel();

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $field = array("ID","nome","data");
            $order = $field[$column]." ".$dir;

            $total_record    = $AdminGareIndividualiClass->getEventiAmount();
            $filtered_record = $AdminGareIndividualiClass->getEventiAmountFiltered($search);
            $Eventi = $AdminGareIndividualiClass->getEventiPagination($order,$start,$length,$search);
            
            $data_array = array();

            foreach ($Eventi as $evento)
            {
                $modifica= '<a href="' . URL_TO_PUBLIC_FOLDER . 'AdminGareIndividuali/ModificaEvento/' . $evento->ID . '">
                              <button type="button" class="btn button-azzurro btn-block">Modifica</button>
                            </a>';

                $row = array(
                    'id' => $evento->ID,
                    'nome' => $evento->nome,
                    'data_apertura_iscrizioni' => $evento->data_apertura_iscrizioni,
                    'data_chiusura_iscrizioni' => $evento->data,
                    'modifica' => $modifica
                );

                array_push($data_array, $row);
            }

            $arr = array('recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $data_array);
            echo json_encode($arr);
        }
    }

    public function ListaGare()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGareIndividuali/listaGare.html', $ArrayTemplateCombined);
    }

    public function ajaxTabellaListaGare()
    {
        if (isset($_GET))
        {
            $AdminGareIndividualiClass= new AdminGareIndividualiModel();

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $field = array("A_ID_gara","A_nome_gara","A_nome_evento","A_data_evento");
            $order = $field[$column]." ".$dir;

            $total_record    = $AdminGareIndividualiClass->getGareAmount();
            $filtered_record = $AdminGareIndividualiClass->getGareAmountFiltered($search);
            $Gare = $AdminGareIndividualiClass->getGarePagination($order,$start,$length,$search);
            
            $data_array = array();

            foreach ($Gare as $gara)
            {
                $modifica= '<a href="' . URL_TO_PUBLIC_FOLDER . 'AdminGareIndividuali/ModificaGara/' . $gara->A_ID_gara . '">
                              <button type="button" class="btn button-azzurro btn-block">Modifica</button>
                            </a>';

                $row = array(
                    'id' => $gara->A_ID_gara,
                    'nome_gara' => $gara->A_nome_gara,
                    'nome_evento' => $gara->A_nome_evento,
                    'data' => $gara->A_data_evento,
                    'modifica' => $modifica
                );

                array_push($data_array, $row);
            }

            $arr = array('recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $data_array);
            echo json_encode($arr);
        }
    }

    public function Risultati()
    {
        require_once("AdminGate.php");

        $GareIndividualiClass= new GareIndividualiModel();

        $ListaGareAperte = $GareIndividualiClass->getAllGare(1);

        $ArrayTemplateAction= ['ListaGareAperte' => $ListaGareAperte];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGareIndividuali/risultati.html', $ArrayTemplateCombined);
    }

    public function ajaxTabellaRisultati()
    {
        require_once("AdminGate.php");

        $AdminGareIndividualiClass= new AdminGareIndividualiModel();

        if ( isset($_POST['IDGara']) )
        {
            $IDGara= $_POST['IDGara'];
            
            $RisultatiGara= $AdminGareIndividualiClass->getRisultatiGara($IDGara);
            
            if ( count($RisultatiGara) > 0)
            {
                $TabellaRisultati = '<table width="100%" class="table table-striped table-bordered table-hover" id="TabellaRisultati">
                                        <thead>
                                            <tr>
                                                <th>Posizione</th>
                                                <th>Utente</th>
                                                <th>Race Time</th>
                                                <th>Località</th>
                                                <th>Provincia</th>
                                                <th>Tipo Acqua</th>
                                                <th>Verificato</th>
                                                <th>Dettagli</th>
                                            </tr>
                                        </thead>
                                        <tbody>';

                foreach($RisultatiGara as $key => $risultato)
                {
                    $Posizione = $key + 1;
                    $nome_tipo_acqua = $risultato->tipo_acqua == 1 ? 'Mare' : 'Lago';

                    $dettagli= '<a href="' . URL_TO_PUBLIC_FOLDER . 'AdminGareIndividuali/DettagliRisultato/' . $risultato->ID_iscrizione . '">
                                  <button type="button" class="btn button-azzurro btn-block">Dettagli</button>
                                </a>';

                    $verificato = $risultato->convalidato == 1 ? '<i class="fa fa-check text-success" aria-hidden="true"></i>' : '';

                    $TabellaRisultati .= '  <tr>
                                                <td>' . $Posizione . '</td>
                                                <td>' . $risultato->utente_nome . ' ' . $risultato->utente_cognome . '</td>
                                                <td>' . $risultato->tempo . '</td>
                                                <td>' . $risultato->localita . '</td>
                                                <td>' . $risultato->provincia_nome . '</td>
                                                <td>' . $nome_tipo_acqua . '</td>
                                                <td class="text-center">' . $verificato . '</td>
                                                <td>' . $dettagli . '</td>
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

    

    public function DettagliRisultato()
    {
        require_once("AdminGate.php");

        $id_iscrizione= $this->route_params['id'];

        $AdminGareIndividualiClass = new AdminGareIndividualiModel();
        $AccountClass = new AccountModel();

        $Iscrizione = $AdminGareIndividualiClass->getIscrizioneDettagli($id_iscrizione);
        $UtenteIscrizione = $AccountClass->getUtente($Iscrizione->IGI_ID_utente);

        $ArrayTemplateAction = ['UtenteIscrizione' => $UtenteIscrizione,
                               'Iscrizione' => $Iscrizione];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGareIndividuali/dettagliRisultato.html', $ArrayTemplateCombined);
    }

    public function ConfermaDettagliRisultato()
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

        header('location: ' . URL_TO_PUBLIC_FOLDER . 'AdminGareIndividuali/Risultati');
    }
}