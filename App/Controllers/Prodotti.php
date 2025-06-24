<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\ProdottiModel;
use \App\Models\TipologieProdottiModel;
use \App\Models\AttributiProdottiModel;
use \App\Models\FunctionsModel;
use \App\Models\GareModel;

class Prodotti extends \Core\Controller
{
    public function Lista()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction = [];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Prodotti/lista.html', $ArrayTemplateCombined);
    }

    public function CreaProdotto()
    {
        require_once("AdminGate.php");

        $TipologieProdottiClass = new TipologieProdottiModel();
        $AttributiProdottiClass = new AttributiProdottiModel();

        $TipologieProdotti = $TipologieProdottiClass->getAllTipologieProdotti();
        $AttributiProdotti = $AttributiProdottiClass->getAllAttributiConOpzioni();

        $ArrayTemplateAction =
        [ 
            'TipologieProdotti' => $TipologieProdotti,
            'AttributiProdotti' => $AttributiProdotti
        ];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Prodotti/creaProdotto.html', $ArrayTemplateCombined);
    }

    public function CreaProdottoConferma()
    {
        require_once("AdminGate.php");

        $ProdottiClass = new ProdottiModel();
        $AttributiProdottiClass = new AttributiProdottiModel();

        if
        (
            isset(
                $_POST['nome'],
                $_POST['prezzo'],
                $_POST['prezzo_listino'],
                $_POST['IDTipologia']
            )
        )
        {
            $nome = $_POST['nome'];
            $prezzo = $_POST['prezzo'];
            $prezzo_listino = $_POST['prezzo_listino'];
            $IDTipologia = $_POST['IDTipologia'];

            $AttributiOpzioni_ID = $_POST['AttributiOpzioni_ID'] ?? [];
            $AttributiOpzioni_Valore = $_POST['AttributiOpzioni_Valore'] ?? [];

            // Inserisce prodotto
            $prodotto_id = $ProdottiClass->CreaProdotto(
                $nome,
                $prezzo,
                $prezzo_listino,
                $IDTipologia
            );

            // Aggiorna attributi opzioni prodotto
            $AttributiProdottiClass->aggiornaAttributiOpzioniProdotto($prodotto_id, $AttributiOpzioni_ID, $AttributiOpzioni_Valore);

            // Estrae prodotto
            $Prodotto = $ProdottiClass->getProdotto($prodotto_id);

            // Foto
            if (isset($_FILES["foto"]))
                $ProdottiClass->caricaFotoProdotto(
                    $_FILES["foto"]["name"],
                    $_FILES["foto"]["tmp_name"],
                    $prodotto_id,
                    $Prodotto->foto
                );
        }
        else
            die('Missing POST data');
        
        header("location: ../Prodotti/Lista");       
    }

    public function ModificaProdotto()
    {
        require_once("AdminGate.php");

        $prodotto_id = $this->route_params['id'];

        $ProdottiClass = new ProdottiModel();
        $TipologieProdottiClass = new TipologieProdottiModel();
        $AttributiProdottiClass = new AttributiProdottiModel();

        $TipologieProdotti = $TipologieProdottiClass->getAllTipologieProdotti();
        $Prodotto = $ProdottiClass->getProdotto($prodotto_id);
        $AttributiProdotti = $AttributiProdottiClass->getAllAttributiConOpzioni();
        $OpzioniAttributiProdotto = $AttributiProdottiClass->getAllOpzioniAttributiProdotto($prodotto_id);

        $ArrayTemplateAction =
        [
            'Prodotto' => $Prodotto,
            'TipologieProdotti' => $TipologieProdotti,
            'AttributiProdotti' => $AttributiProdotti,
            'OpzioniAttributiProdotto' => $OpzioniAttributiProdotto
        ];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Prodotti/modificaProdotto.html', $ArrayTemplateCombined);
    }

    public function ModificaProdottoConferma()
    {
        require_once("AdminGate.php");

        $ProdottiClass = new ProdottiModel();
        $AttributiProdottiClass = new AttributiProdottiModel();

        if
        (
            isset(
                $_POST['prodotto_id'],
                $_POST['nome'],
                $_POST['prezzo'],
                $_POST['prezzo_listino'],
                $_POST['IDTipologia']
            )
        )
        {
            $prodotto_id = $_POST['prodotto_id'];
            $nome = $_POST['nome'];
            $prezzo = $_POST['prezzo'];
            $prezzo_listino = $_POST['prezzo_listino'];
            $IDTipologia = $_POST['IDTipologia'];

            $AttributiOpzioni_ID = $_POST['AttributiOpzioni_ID'] ?? [];
            $AttributiOpzioni_Valore = $_POST['AttributiOpzioni_Valore'] ?? [];

            // Aggiorna prodotto
            $ProdottiClass->AggiornaProdotto(
                $prodotto_id,
                $nome,
                $prezzo,
                $prezzo_listino,
                $IDTipologia
            );

            // Aggiorna attributi opzioni prodotto
            $AttributiProdottiClass->aggiornaAttributiOpzioniProdotto($prodotto_id, $AttributiOpzioni_ID, $AttributiOpzioni_Valore);

            // Estrae prodotto
            $Prodotto = $ProdottiClass->getProdotto($prodotto_id);

            // Foto
            if (isset($_FILES["foto"]))
                $ProdottiClass->caricaFotoProdotto(
                    $_FILES["foto"]["name"],
                    $_FILES["foto"]["tmp_name"],
                    $prodotto_id,
                    $Prodotto->foto
                );
        }
        else
            die('Missing POST data');

        header("location: ../Prodotti/Lista");
    }

    public function ajaxProdotti()
    {
        if (isset($_GET))
        {
            $ProdottiClass = new ProdottiModel();

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $field = array("ID","nome", "prezzo", "prezzo_listino", "IDTipologia");
            $order = $field[$column]." ".$dir;

            $total_record    = $ProdottiClass->getProdottiAmount();
            $filtered_record = $ProdottiClass->getProdottiAmountFiltered($search);
            $Prodotti = $ProdottiClass->getProdottiPagination($order,$start,$length,$search);
            
            $data_array = [];

            foreach ($Prodotti as $prodotto)
            {
                $modifica = '
                    <a href="' . URL_TO_PUBLIC_FOLDER . 'Prodotti/ModificaProdotto/' . $prodotto->ID . '">
                        <button type="button" class="btn button-azzurro btn-block">Modifica</button>
                    </a>';

                $foto_src = 
                    $prodotto->foto != '' ?
                    URL_TO_PUBLIC_FOLDER . $prodotto->foto :
                    URL_TO_PUBLIC_FOLDER . 'img/no_image_available.png'
                ;

                $foto = '<img src="' . $foto_src . '" class="rounded-circle" width="64" height="64">';

                $row = 
                [
                    'foto' => $foto,
                    'id' => $prodotto->ID,
                    'nome' => $prodotto->nome,
                    'prezzo' => $prodotto->prezzo,
                    'prezzo_listino' => $prodotto->prezzo_listino,
                    'tipologia' => $prodotto->tipologia_nome,
                    'modifica' => $modifica
                ];

                array_push($data_array, $row);
            }

            $arr =
            [
                'recordsTotal' => $total_record,
                'recordsFiltered' => $filtered_record,
                'data' => $data_array
            ];

            echo json_encode($arr);
        }
    }

    public function listaProdottiAcquistati()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction = [];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('Prodotti/lista_acquistati.html', $ArrayTemplateCombined);
    }

    public function ajaxProdottiAcquistati()
    {
        if (isset($_GET))
        {
            $ProdottiClass = new ProdottiModel();

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $field = array("nome_evento","nome_prodotto", "nome_utente", "cognome_utente", "taglia");
            $order = $field[$column]." ".$dir;

            $total_record    = $ProdottiClass->getProdottiAcquistatiAmount();
            $filtered_record = $ProdottiClass->getProdottiAcquistatiAmountFiltered($search);
            $Prodotti = $ProdottiClass->getProdottiAcquistatiPagination($order,$start,$length,$search);
            
            $DataArray = [];

            foreach ($Prodotti as $Prodotto)
            {
                $consegnato = '
                    <input 
                        type="checkbox" 
                        name="MagliettaConsegnata[]" 
                        class="check-consegnato"
                        data-check-maglietta-consegnata=' . $Prodotto->ID . '
                    >
                ';

                $Row = 
                [
                    'id' => $Prodotto->ID,
                    'evento' => $Prodotto->nome_evento,
                    'nome' => $Prodotto->nome_prodotto,
                    'taglia' => $Prodotto->taglia,
                    'utente' => $Prodotto->nome_utente . ' ' . $Prodotto->cognome_utente,
                    'consegnato' => $consegnato
                ];

                $DataArray[] = $Row;
            }

            $Arr =
            [
                'recordsTotal' => $total_record,
                'recordsFiltered' => $filtered_record,
                'data' => $DataArray
            ];

            echo json_encode($Arr);
        }
    }

    public function ajaxImpostaMagliettaConsegnata()
    {
        require_once("AdminGate.php");

        if (isset($_POST['imposta_maglietta_consegnata']))
        {
            $prodotto_acquistato_id = $_POST['prodotto_acquistato_id'];

            $ProdottiClass = new ProdottiModel();
            $AcquistiMaglietteClass = new GareModel();

            // Imposta il campo "consegnato" a 1
            $ProdottiClass->impostaMagliettaConsegnata($prodotto_acquistato_id);

            // ProdottoAcquistato che serve per avere l'id di acquisto magliette
            $ProdottoAcquistato = $ProdottiClass->getProdottoAcquistato($prodotto_acquistato_id);

            // Lista prodotti acquistati in base all'id di acquisto magliette
            $ListaProdottiAcquistati = $ProdottiClass->getListaProdottiAcquistatiByAcquistoMagliette($ProdottoAcquistato->acquisto_magliette_id);

            // Variabile che serve per determinare se tutte le magliette acquistate sono state consegnate
            $tutte_magliette_acquistate_consegnate = 1;

            // Ciclo lista prodotti acquistati
            foreach ($ListaProdottiAcquistati as $ProdottoAcquistato)
            {
                // Impostazione variabile in base al valore del campo "consegnato" all'interno della tabella "prodotti_acquisto"
                if ($ProdottoAcquistato->consegnato == 0)
                {
                    $tutte_magliette_acquistate_consegnate = 0;
                    break;
                }
            }

            // Aggiornamento campo "confermato" in "acquisti_magliette" se tutte le magliette sono state consegnate
            if ($tutte_magliette_acquistate_consegnate)
                $AcquistiMaglietteClass->aggiornaCampoConfermato($ProdottoAcquistato->acquisto_magliette_id); 

            $Risposta['Risposta']= 'ok';
            
            echo json_encode($Risposta);
        }
    }
}
