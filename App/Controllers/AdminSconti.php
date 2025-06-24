<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\ScontiModel;
use \App\Models\GareModel;

class AdminSconti extends \Core\Controller
{
    public function ListaScontiComboGare()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminSconti/listaScontiComboGare.html', $ArrayTemplateCombined);
    }

    public function InserisciScontoComboGare()
    {
        require_once("AdminGate.php");

        $ScontiClass = new ScontiModel();
        $GareClass = new GareModel();

        $ScontoInserito = '';

        if (isset($_POST['ConfermaInserisciScontoComboGare']))
        {
            $NomeSconto = $_POST['Nome'];
            $ListaGare = $_POST['ListaGare'];
            $Sconto = $_POST['Sconto'];

            $ListaGare = str_replace(' ', '', $ListaGare);
            $ListaGareArray = explode(',', $ListaGare);

            if (count($ListaGareArray) > 0)
            {
                // Controlla che tutte le gare esistano
                foreach ($ListaGareArray as $gara)
                {
                    if ($GareClass->getGara($gara) == null)
                        $ScontoInserito = '<div class="text-danger text-center">Hai inserito delle gare che non esistono!</div>';
                }

                // Se tutto ok le inserisce nella scontistica
                if ($ScontoInserito == '')
                {
                    // Crea scontistica
                    $IDSconto = $ScontiClass->addScontoComboGare($NomeSconto, $Sconto);

                    // Collega gare alla scontistica
                    foreach ($ListaGareArray as $gara)
                        $ScontiClass->addGaraAScontoComboGare($IDSconto, $gara);

                    $ScontoInserito = '<div class="text-success text-center">Sconto combo gare inserito!</div>';
                }
            }
            else
                $ScontoInserito = '<div class="text-danger text-center">Compila correttamente il campo "Lista Gare"!</div>';
        }

        $ArrayTemplateAction= ['ScontoInserito' => $ScontoInserito];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminSconti/inserisciScontoComboGare.html', $ArrayTemplateCombined);
    }

    public function ajaxTabellaListaScontiComboGare()
    {
        if (isset($_GET))
        {
            $ScontiClass= new ScontiModel();

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $field = array("ID","nome","ListaGare");
            $order = $field[$column]." ".$dir;

            $total_record    = $ScontiClass->getScontiComboGareAmount();
            $filtered_record = $ScontiClass->getScontiComboGareAmountFiltered($search);
            $Sconti = $ScontiClass->getScontiComboGarePagination($order,$start,$length,$search);
            
            $data_array = array();

            foreach ($Sconti as $sconto)
            {
                $ListaGareStringa = '';
                $ListaGare = explode('|', $sconto->ListaGare);

                foreach ($ListaGare as $key => $gara)
                {
                    $NumeroGara = $key + 1;

                    $ListaGareStringa .= '<div><b class="text-danger">' . $NumeroGara . ')</b> ' . $gara . '</div>';
                }

                $row = array(
                    'id' => $sconto->ID,
                    'nome' => $sconto->nome,
                    'gare' => $ListaGareStringa,
                    'sconto' => '€ ' . $sconto->sconto
                );

                array_push($data_array, $row);
            }

            $arr = array('recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $data_array);
            echo json_encode($arr);
        }
    }
}
