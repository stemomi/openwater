<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\AdminGareModel;
use \App\Models\GareModel;
use \App\Models\FunctionsModel;
use \App\Models\OpzioniAcquistoModel;

use DateTime;

class AdminGare extends \Core\Controller
{
    public function InserisciEvento()
    {
        require_once("AdminGate.php");

        $AdminGareClass= new AdminGareModel();

        $EventoInserito= '';

        if (isset($_POST['Nome']) && isset($_POST['Data']))
        {
            $Nome= $_POST['Nome'];
            $Data= $_POST['Data'];
            $DataAperturaIscrizioni = $_POST['DataAperturaIscrizioni'];

            $IDEvento = $AdminGareClass->InserisciEvento($Nome, $Data, $DataAperturaIscrizioni);

            $EventoInserito= '<div class="text-success text-center">Evento inserito!</div>';

            header('location: ' . URL_TO_PUBLIC_FOLDER . 'AdminGare/ModificaEvento/'. $IDEvento);
        }

        $ArrayTemplateAction= ['EventoInserito' => $EventoInserito];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGare/inserisciEvento.html', $ArrayTemplateCombined);
    }
    
    public function InserisciGara()
    {
        require_once("AdminGate.php");

        $AdminGareClass = new AdminGareModel();

        $Eventi = $AdminGareClass->getEventi(1);

        $gara_id = $this->route_params['id'];

        $Gara = $AdminGareClass->getGara($gara_id) ?: null;

        // Modifica formato data 
        if ($Gara)
        {
            $Gara->data_prezzo1 = $Gara->data_prezzo1 ? DateTime::createFromFormat('Y-m-d', $Gara->data_prezzo1)->format('d/m/Y') : null;
            $Gara->data_prezzo2 = $Gara->data_prezzo2 ? DateTime::createFromFormat('Y-m-d', $Gara->data_prezzo2)->format('d/m/Y') : null;
            $Gara->data_prezzo3 = $Gara->data_prezzo3 ? DateTime::createFromFormat('Y-m-d', $Gara->data_prezzo3)->format('d/m/Y') : null;
            $Gara->data_prezzo4 = $Gara->data_prezzo4 ? DateTime::createFromFormat('Y-m-d', $Gara->data_prezzo4)->format('d/m/Y') : null;
        }

        $GaraInserita = '';

        if (isset($_POST['IDEvento']))
        {
            $evento_id = $_POST['IDEvento'];
            $nome = $_POST['Nome'];
            $prezzo_1 = $_POST['Prezzo1'];
            $prezzo_2 = $_POST['Prezzo2'];
            $prezzo_3 = $_POST['Prezzo3'];
            $prezzo_4 = $_POST['Prezzo4'];
            $data_prezzo_1 = $_POST['DataPrezzo1'];
            $data_prezzo_2 = $_POST['DataPrezzo2'];
            $data_prezzo_3 = $_POST['DataPrezzo3'];
            $data_prezzo_4 = $_POST['DataPrezzo4'];
            $sconto_prezzo_1 = $_POST['ScontoPrezzo1'];
            $sconto_prezzo_2 = $_POST['ScontoPrezzo2'];
            $sconto_prezzo_3 = $_POST['ScontoPrezzo3'];
            $sconto_prezzo_4 = $_POST['ScontoPrezzo4'];
            $tipo_id = $_POST['IDTipo'];

            $staffetta_flag = isset($_POST['Staffetta']) ? true : false;
            $combinata_flag = isset($_POST['GaraCombinata']) ? true : false;

            // Copia data evento in gara
            $Evento = $AdminGareClass->getEvento($evento_id);
            $evento_data = $Evento ? $Evento->data : '0000-00-00';

            $gara_aggiunta_id = $AdminGareClass->InserisciGara(    
                $evento_id,
                $nome,
                $evento_data,
                $prezzo_1,
                $prezzo_2,
                $prezzo_3,
                $prezzo_4,
                $data_prezzo_1,
                $data_prezzo_2,
                $data_prezzo_3,
                $data_prezzo_4,
                $sconto_prezzo_1,
                $sconto_prezzo_2,
                $sconto_prezzo_3,
                $sconto_prezzo_4,
                $staffetta_flag,
                $combinata_flag,
                $tipo_id
            );

            if ($gara_aggiunta_id) 
                header('location: ' . URL_TO_PUBLIC_FOLDER . 'AdminGare/ModificaGara/' . $gara_aggiunta_id);
        }

        $ArrayTemplateAction = 
        [
            'Eventi' => $Eventi,
            'GaraInserita' => $GaraInserita,
            'Gara' => $Gara
        ];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGare/inserisciGara.html', $ArrayTemplateCombined);
    }

    public function ModificaEvento()
    {
        require_once("AdminGate.php");

        $AdminGareClass = new AdminGareModel();
        $OpzioniAcquistoClass = new OpzioniAcquistoModel();

        $id_evento = $this->route_params['id'];
        $Evento = $AdminGareClass->getEvento($id_evento);
        $OpzioniAcquisto = $OpzioniAcquistoClass->getAllOpzioniAcquisto();
        $OpzioniAcquistoEvento = $OpzioniAcquistoClass->getOpzioniAcquistoEvento($id_evento);

        $ArrayTemplateAction = [
            'Evento' => $Evento,
            'OpzioniAcquisto' => $OpzioniAcquisto,
            'OpzioniAcquistoEvento'=> $OpzioniAcquistoEvento
        ];
        $ArrayTemplateCombined = $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGare/modificaEvento.html', $ArrayTemplateCombined);
    }

    public function modificaEventoConfirm()
    {
        require_once("AdminGate.php");

        $AdminGareClass = new AdminGareModel();
        $FunctionsClass = new FunctionsModel();
        $OpzioniAcquistoClass = new OpzioniAcquistoModel();

        if ( isset($_POST['ConfermaModificaEvento']) )
        {
            $IDEvento = $_POST['IDEvento'];
            $Nome = $_POST['Nome'];
            $Data = $_POST['Data'];
            $DataAperturaIscrizioni = $_POST['DataAperturaIscrizioni'];
            $vendita_boa = isset($_POST['vendita_boa']) ? true : false;

            $AdminGareClass->AggiornaEvento($IDEvento, $Nome, $Data, $DataAperturaIscrizioni, $vendita_boa);
            $Evento= $AdminGareClass->getEvento($IDEvento);

            // Aggiorna flag obbligatorio su opzioni acquisto
            $OpzioniAcquistoID = $_POST['OpzioniAcquistoID'] ?? [];
            $OpzioniAcquistoObbligatorio = $_POST['OpzioniAcquistoObbligatorio'] ?? [];

            foreach($OpzioniAcquistoID as $key => $opzione_acquisto_id)
            {
                $flag_obbligatorio = $OpzioniAcquistoObbligatorio[$key] == 1 ? true : false;
                $OpzioniAcquistoClass->setOpzioneAcquistoEventoObbligatorio($opzione_acquisto_id, $flag_obbligatorio);
            }

            $EventoModificato= '<div class="text-success text-center">Evento Modificato!</div>';

            // Foto Evento
            $ErroreControlloFiles = 0;

            if ( $_FILES["FileFotoEvento"]["name"] != '' && $ErroreControlloFiles == 0)
            {
                $NomeFileTMP = $_FILES["FileFotoEvento"]["tmp_name"];
                $NomeFile = $_FILES["FileFotoEvento"]["name"];

                // Elimina file precedente
                if ( $Evento->foto != '' && file_exists(PATH_EVENTI_FOTO . $Evento->foto)) unlink(PATH_EVENTI_FOTO . $Evento->foto);

                // CONTROLLI DI SICUREZZA UPLOAD FILE
                $checkUploadFile= $FunctionsClass->checkUploadFile($NomeFile, $NomeFileTMP);
                if ( $checkUploadFile != "") die($checkUploadFile);

                // Controllo che sia immagine
                $check = getimagesize($NomeFileTMP);
                if($check[0] < 4 || $check[1] < 4)
                  die("Il file caricato non è un immagine!");

                // Ricrea immagine
                $FunctionsClass->RicreaJPG($NomeFileTMP, 1200, 630);

                // Crea nome file foto evento
                $path_foto_evento = $Evento->ID . "-" . time() . "-" . bin2hex(openssl_random_pseudo_bytes(8)) . ".jpg";

                // Aggiorna file foto evento
                $AdminGareClass->AggiornaFotoEvento($IDEvento, $path_foto_evento);

                if (!move_uploaded_file($NomeFileTMP, PATH_EVENTI_FOTO . $path_foto_evento))
                {
                    $UtenteModificato= '<div class="text-danger text-center">Errore caricamento foto evento!</div>';
                    $ErroreControlloFiles= 1;
                }
            }
        }

        // Riestrae evento
        $Evento= $AdminGareClass->getEvento($IDEvento);
        $OpzioniAcquisto = $OpzioniAcquistoClass->getAllOpzioniAcquisto();
        $OpzioniAcquistoEvento = $OpzioniAcquistoClass->getOpzioniAcquistoEvento($IDEvento);

        $ArrayTemplateAction= [
            'Evento' => $Evento,
            'EventoModificato' => $EventoModificato,
            'OpzioniAcquisto' => $OpzioniAcquisto,
            'OpzioniAcquistoEvento'=> $OpzioniAcquistoEvento
        ];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGare/modificaEvento.html', $ArrayTemplateCombined);
    }

    public function ModificaGara()
    {
        require_once("AdminGate.php");

        $id_gara= $this->route_params['id'];

        $AdminGareClass= new AdminGareModel();
        $Gara= $AdminGareClass->getGara($id_gara);
        $Eventi= $AdminGareClass->getEventi();
        $GareStessoEvento= $AdminGareClass->getGareStessoEvento($Gara->ID_evento, $Gara->ID);
        $GareCombinateAggiunte= $AdminGareClass->getGareCombinateAggiunte($Gara->ID);

        $ArrayTemplateAction= ['Gara' => $Gara,
                               'Eventi' => $Eventi,
                               'GareStessoEvento' => $GareStessoEvento,
                               'GareCombinateAggiunte' => $GareCombinateAggiunte];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGare/modificaGara.html', $ArrayTemplateCombined);
    }

    public function modificaGaraConfirm()
    {
        require_once("AdminGate.php");

        $AdminGareClass= new AdminGareModel();
        $Eventi= $AdminGareClass->getEventi();

        if ( isset($_POST['ConfermaModificaGara']) )
        {
            $gara_id = $_POST['IDGara'];
            $evento_id = $_POST['IDEvento'];
            $nome = $_POST['Nome'];
            $prezzo_1 = $_POST['Prezzo1'];
            $prezzo_2 = $_POST['Prezzo2'];
            $prezzo_3 = $_POST['Prezzo3'];
            $prezzo_4 = $_POST['Prezzo4'];
            $data_prezzo_1 = $_POST['DataPrezzo1'];
            $data_prezzo_2 = $_POST['DataPrezzo2'];
            $data_prezzo_3 = $_POST['DataPrezzo3'];
            $data_prezzo_4 = $_POST['DataPrezzo4'];
            $sconto_prezzo_1 = $_POST['ScontoPrezzo1'];
            $sconto_prezzo_2 = $_POST['ScontoPrezzo2'];
            $sconto_prezzo_3 = $_POST['ScontoPrezzo3'];
            $sconto_prezzo_4 = $_POST['ScontoPrezzo4'];
            $tipo_id = $_POST['IDTipo'];
            $flag_staffetta = isset($_POST['Staffetta']) ? true : false;
            $flag_combinata = isset($_POST['GaraCombinata']) ? true : false;
            $mostra_risultati = isset($_POST['MostraRisultati']) ? true : false;
            $gara_bloccata = isset($_POST['BloccaGara']) ? true : false;


            $AdminGareClass->AggiornaGara(
                $gara_id,
                $evento_id,
                $nome,
                $prezzo_1,
                $prezzo_2,
                $prezzo_3,
                $prezzo_4,
                $data_prezzo_1,
                $data_prezzo_2,
                $data_prezzo_3,
                $data_prezzo_4,
                $sconto_prezzo_1,
                $sconto_prezzo_2,
                $sconto_prezzo_3,
                $sconto_prezzo_4,
                $flag_staffetta,
                $flag_combinata,
                $mostra_risultati,
                $gara_bloccata,
                $tipo_id
            );
            $Gara= $AdminGareClass->getGara($gara_id);

            $GaraModificata= '<div class="text-success text-center">Gara Modificata!</div>';

            $GareCombinateAggiunte= $AdminGareClass->getGareCombinateAggiunte($gara_id);
        }

        $ArrayTemplateAction= ['Gara' => $Gara,
                               'GaraModificata' => $GaraModificata,
                               'Eventi' => $Eventi,
                               'GareCombinateAggiunte' => $GareCombinateAggiunte];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGare/modificaGara.html', $ArrayTemplateCombined);
    }

    public function ListaEventi()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGare/listaEventi.html', $ArrayTemplateCombined);
    }

    public function ListaStaffette()
    {
        require_once("AdminGate.php");

        $ArrayTemplateAction= [];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGare/listaStaffette.html', $ArrayTemplateCombined);
    }

    public function CaricaRisultati()
    {
        require_once("AdminGate.php");

        $GareClass= new GareModel();

        $ListaGareAperte = $GareClass->getAllGare(1);

        $ArrayTemplateAction= ['ListaGareAperte' => $ListaGareAperte];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGare/caricaRisultati.html', $ArrayTemplateCombined);
    }

    public function CaricaRisultatiConfirm()
    {
        require_once("AdminGate.php");

        $AdminGareClass = new AdminGareModel();
        $GareClass = new GareModel();
        $FunctionsClass = new FunctionsModel();

        $ListaGareAperte = $GareClass->getAllGare(1);

        if ( isset($_POST['ConfermaCaricaRisultati']) )
        {
            $IDGara= $_POST['IDGara'];
            $Gara= $GareClass->getGaraConDettagli($IDGara);
            $Scrivi= 1;
            $EsitoCaricaRisultati= '';

            // Principale
            if ( $_FILES['FileExcel']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['FileExcel']['tmp_name']) )
            {
                $FileCSV= file_get_contents($_FILES['FileExcel']['tmp_name']);
                $FileCSV = $FunctionsClass->RemoveBomUtf8($FileCSV);

                preg_match_all('/(.*)' . PHP_EOL . '/',$FileCSV,$Risultati);
                $Righe= $Risultati[1];

                // Controlla prima che non ci siano già dei record relativi alla gara
                if ($AdminGareClass->ControllaGaraRisultatiPresente($IDGara) < 1)
                {
                    $EsitoCaricaRisultati = '<div class="text-success text-center">Risultati caricati correttamente per la gara: ' . $Gara->nome_evento . ' ' . $Gara->nome_gara . '!</div>';
                }
                elseif ( isset($_POST['Sovrascrivi']) ) // Cancella righe gara se sovrascrivi selezionato
                {
                    $AdminGareClass->EliminaRisultatiGara($IDGara);
                    $EsitoCaricaRisultati = '<div class="text-success text-center">Risultati sovrascritti correttamente per la gara: ' . $Gara->nome_evento . ' ' . $Gara->nome_gara . '!</div>';
                }
                else
                {
                    $Scrivi= 0;
                    $EsitoCaricaRisultati = '<div class="text-danger text-center">
                                                Risultati gara già presenti!
                                                <br>
                                                Clicca il checkbox "Sovrascrivi" per cancellare i vecchi risultati ed inserire i nuovi relativi alla stessa gara!
                                            </div>';
                }

                foreach ($Righe as $key => $riga)
                {
                    $ValoriRiga= explode(';', $riga);

                    // Elimina eventuali spazi vuoti prima e dopo
                    foreach ($ValoriRiga as &$valore)
                        $valore = trim($valore);

                    // Controlla che le colonne siano le stesse del CSV originale
                    if ($key == 0)
                    {
                        if ( $ValoriRiga[0] != 'CHIP' ||
                             $ValoriRiga[1] != 'BOA' ||
                             $ValoriRiga[2] != 'POSIZIONE' ||
                             $ValoriRiga[3] != 'GLOBALPOS' ||
                             $ValoriRiga[4] != 'COGNOME' ||
                             $ValoriRiga[5] != 'NOME' ||
                             $ValoriRiga[6] != 'SESSO' ||
                             $ValoriRiga[7] != 'ANNO' ||
                             $ValoriRiga[8] != 'CATEGORIA' ||
                             $ValoriRiga[9] != 'NOME_TEAM' ||
                             $ValoriRiga[10] != 'IDSOCIETA' ||
                             $ValoriRiga[11] != 'NAZIONALITA' ||
                             $ValoriRiga[12] != 'ID' ||
                             $ValoriRiga[13] != 'REALPOS' ||
                             $ValoriRiga[14] != 'POSSEX' ||
                             $ValoriRiga[15] != 'POSIZIONECAT' ||
                             $ValoriRiga[16] != 'CRUDO' ||
                             $ValoriRiga[17] != 'RACETIME' ||
                             $ValoriRiga[18] != 'REALTIME' ||
                             $ValoriRiga[19] != 'ORAARRIVO' ||
                             $ValoriRiga[20] != 'DISTACCO' ||
                             $ValoriRiga[21] != 'MEDIA' ||
                             $ValoriRiga[22] != 'NOME_ENTE' ||
                             $ValoriRiga[23] != 'GIORNALIERO'
                           )
                        {
                            print "<pre>";
                            var_dump($ValoriRiga);
                            print "</pre>";

                            die('I campi del CSV non coincidono con quelli del CSV Originario!');
                        }
                    }
                    else
                    {
                        if ($Scrivi == 1)
                            $AdminGareClass->InserisciRigaRisultati($IDGara, $ValoriRiga);

                        // Imposta mostra risultati nella gara
                        $AdminGareClass->ImpostaMostraRisultatiGara($IDGara, true);
                    }
                }
            }

            // Redirect su pagina risultati con preselezione gara
            header("location: ../AdminGare/Risultati/" . $IDGara);

            // Crudo
            /*
            if ( $_FILES['FileExcelCrudo']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['FileExcelCrudo']['tmp_name']) )
            {
                $FileCSV= file_get_contents($_FILES['FileExcelCrudo']['tmp_name']);

                preg_match_all('/(.*)' . PHP_EOL . '/',$FileCSV,$Risultati);
                $Righe= $Risultati[1];

                // Controlla prima che ci siano già dei record relativi alla gara
                if ($AdminGareClass->ControllaGaraRisultatiPresente($IDGara) > 0)
                {
                    foreach ($Righe as $key => $riga)
                    {
                        $ValoriRiga= explode(';', $riga);

                        // Controlla che le colonne siano le stesse del CSV originale
                        if ($key == 0)
                        {
                            if ( $ValoriRiga[0] != 'CHIP' ||
                                 $ValoriRiga[1] != 'BOA' ||
                                 $ValoriRiga[2] != 'POSIZIONE' ||
                                 $ValoriRiga[3] != 'COGNOME' ||
                                 $ValoriRiga[4] != 'NOME' ||
                                 $ValoriRiga[5] != 'SESSO' ||
                                 $ValoriRiga[6] != 'ANNO' ||
                                 $ValoriRiga[7] != 'CATEGORIA' ||
                                 $ValoriRiga[8] != 'NOME_TEAM' ||
                                 $ValoriRiga[9] != 'IDSOCIETA' ||
                                 $ValoriRiga[10] != 'NAZIONALITA' ||
                                 $ValoriRiga[11] != 'TESSERA' ||
                                 $ValoriRiga[12] != 'RACETIME' ||
                                 $ValoriRiga[13] != 'ORAARRIVO' ||
                                 $ValoriRiga[14] != 'DISTACCO'
                               )
                            {
                                print "<pre>";
                                var_dump($ValoriRiga);
                                print "</pre>";

                                die('I campi del CSV Crudo non coincidono con quelli del CSV Originario!');
                            }
                                
                        }
                        else
                        {
                            // Cerca prima se trova ID Utente da tessera
                            if ( $AdminGareClass->checkIDTessera($ValoriRiga[11]) > 0 && $ValoriRiga[11] > 0 )
                                $AdminGareClass->AggiornaRigaCrudoRisultati($IDGara, $ValoriRiga[11], $ValoriRiga[2]);
                        }
                    }

                    $EsitoCaricaRisultati .= '<div class="text-success text-center">Risultati Crudo inseriti correttamente per la gara: ' . $Gara->nome_evento . ' ' . $Gara->nome_gara . '!</div>';
                }
                else
                    $EsitoCaricaRisultati .= '<div class="text-danger text-center">Risultati gara non trovati per l\'inserimento dati Crudo!</div>';
            }

            */
        }
        else
            $EsitoCaricaRisultati= '<div class="text-danger text-center">Errore invio form!</div>';

        $ArrayTemplateAction= ['ListaGareAperte' => $ListaGareAperte,
                               'EsitoCaricaRisultati' => $EsitoCaricaRisultati];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGare/caricaRisultati.html', $ArrayTemplateCombined);
    }

    public function CaricaRisultatiStaffette()
    {
        require_once("AdminGate.php");

        $GareClass= new GareModel();

        $ListaGareAperte = $GareClass->getAllGare(1);

        $ArrayTemplateAction= ['ListaGareAperte' => $ListaGareAperte];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGare/caricaRisultatiStaffette.html', $ArrayTemplateCombined);
    }

    public function CaricaRisultatiStaffetteConfirm()
    {
        require_once("AdminGate.php");

        $AdminGareClass = new AdminGareModel();
        $GareClass = new GareModel();
        $FunctionsClass = new FunctionsModel();

        $ListaGareAperte = $GareClass->getAllGare(1);

        if ( isset($_POST['ConfermaCaricaRisultati']) )
        {
            $IDGara= $_POST['IDGara'];
            $Gara= $GareClass->getGaraConDettagli($IDGara);
            $Scrivi= 1;
            $EsitoCaricaRisultati= '';

            // Principale
            if ( $_FILES['FileExcel']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['FileExcel']['tmp_name']) )
            {
                $FileCSV= file_get_contents($_FILES['FileExcel']['tmp_name']);
                $FileCSV = $FunctionsClass->RemoveBomUtf8($FileCSV);

                preg_match_all('/(.*)' . PHP_EOL . '/',$FileCSV,$Risultati);
                $Righe= $Risultati[1];

                // Controlla prima che non ci siano già dei record relativi alla gara
                if ($AdminGareClass->ControllaGaraStaffettaRisultatiPresente($IDGara) < 1)
                {
                    $EsitoCaricaRisultati = '<div class="text-success text-center">Risultati caricati correttamente per la gara: ' . $Gara->nome_evento . ' ' . $Gara->nome_gara . '!</div>';
                }
                elseif ( isset($_POST['Sovrascrivi']) ) // Cancella righe gara se sovrascrivi selezionato
                {
                    $AdminGareClass->EliminaRisultatiGaraStaffetta($IDGara);
                    $EsitoCaricaRisultati = '<div class="text-success text-center">Risultati sovrascritti correttamente per la gara: ' . $Gara->nome_evento . ' ' . $Gara->nome_gara . '!</div>';
                }
                else
                {
                    $Scrivi= 0;
                    $EsitoCaricaRisultati = '<div class="text-danger text-center">
                                                Risultati gara già presenti!
                                                <br>
                                                Clicca il checkbox "Sovrascrivi" per cancellare i vecchi risultati ed inserire i nuovi relativi alla stessa gara!
                                            </div>';
                }

                foreach ($Righe as $key => $riga)
                {
                    $ValoriRiga= explode(';', $riga);

                    // Elimina eventuali spazi vuoti prima e dopo
                    foreach ($ValoriRiga as &$valore)
                        $valore = trim($valore);

                    // Controlla che le colonne siano le stesse del CSV originale
                    if ($key == 0)
                    {
                        if 
                        ( 
                            $ValoriRiga[0] != 'CHIP' ||
                            $ValoriRiga[1] != 'IDSTAFF' ||
                            $ValoriRiga[2] != 'POSIZIONE' ||
                            $ValoriRiga[3] != 'GLOBALPOS' ||
                            $ValoriRiga[4] != 'COGNOME' ||
                            $ValoriRiga[5] != 'NOME' ||
                            $ValoriRiga[6] != 'SESSO' ||
                            $ValoriRiga[7] != 'ANNO' ||
                            $ValoriRiga[8] != 'CATEGORIA' ||
                            $ValoriRiga[9] != 'NOME_TEAM' ||
                            $ValoriRiga[10] != 'IDSOCIETA' ||
                            $ValoriRiga[11] != 'REALPOS' ||
                            $ValoriRiga[12] != 'POSSEX' ||
                            $ValoriRiga[13] != 'POSIZIONECAT' ||
                            $ValoriRiga[14] != 'UFF' ||
                            $ValoriRiga[15] != 'REALTIME' ||
                            $ValoriRiga[16] != 'ORAARRIVO' ||
                            $ValoriRiga[17] != 'COG1' ||
                            $ValoriRiga[18] != 'NOM1' ||
                            $ValoriRiga[19] != 'SESSO1' ||
                            $ValoriRiga[20] != 'TEAM1' ||
                            $ValoriRiga[21] != 'DATA1' ||
                            $ValoriRiga[22] != 'ID1' ||
                            $ValoriRiga[23] != 'COG2' ||
                            $ValoriRiga[24] != 'NOM2' ||
                            $ValoriRiga[25] != 'SESSO2' ||
                            $ValoriRiga[26] != 'TEAM2' ||
                            $ValoriRiga[27] != 'DATA2' ||
                            $ValoriRiga[28] != 'ID2' ||
                            $ValoriRiga[29] != 'COG3' ||
                            $ValoriRiga[30] != 'NOM3' ||
                            $ValoriRiga[31] != 'SESSO3' ||
                            $ValoriRiga[32] != 'TEAM3' ||
                            $ValoriRiga[33] != 'DATA3' ||
                            $ValoriRiga[34] != 'ID3' ||
                            $ValoriRiga[35] != 'INTER1' ||
                            $ValoriRiga[36] != 'INTER2' ||
                            $ValoriRiga[37] != 'INTER3' ||
                            $ValoriRiga[38] != 'MEDIA1' ||
                            $ValoriRiga[39] != 'MEDIA2' ||
                            $ValoriRiga[40] != 'MEDIA3' ||
                            $ValoriRiga[41] != 'POSINTER1' ||
                            $ValoriRiga[42] != 'POSINTER2' ||
                            $ValoriRiga[43] != 'POSINTER3' ||
                            $ValoriRiga[44] != 'POSINTER4' ||
                            $ValoriRiga[45] != 'POSINTER5' ||
                            $ValoriRiga[46] != 'DISTACCO' ||
                            $ValoriRiga[47] != 'MEDIA'
                        )
                        {
                            print "<pre>";
                            var_dump($ValoriRiga);
                            print "</pre>";

                            die('I campi del CSV non coincidono con quelli del CSV Originario!');
                        }
                    }
                    else
                    {
                        if ($Scrivi == 1)
                            $AdminGareClass->InserisciRigaRisultatiStaffette($IDGara, $ValoriRiga);
                    }
                }
            }
        }
        else
            $EsitoCaricaRisultati= '<div class="text-danger text-center">Errore invio form!</div>';

        $ArrayTemplateAction= ['ListaGareAperte' => $ListaGareAperte,
                               'EsitoCaricaRisultati' => $EsitoCaricaRisultati];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGare/caricaRisultatiStaffette.html', $ArrayTemplateCombined);
    }

    public function Risultati()
    {
        require_once("AdminGate.php");

        $GareClass= new GareModel();

        $id_gara = $this->route_params['id'] ?? 0;

        $ListaGareConRisultati = $GareClass->getAllGare(1);

        foreach ($ListaGareConRisultati as $key => $gara)
        {
            if ( $GareClass->getTotalePartecipantiGara($gara->ID_gara) < 1 )
                unset($ListaGareConRisultati[$key]);
        }

        $ArrayTemplateAction =
        [
            'ListaGareConRisultati' => $ListaGareConRisultati,
            'id_gara' => $id_gara
        ];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminGare/risultati.html', $ArrayTemplateCombined);
    }

    public function ajaxTabellaRisultati()
    {
        require_once("AdminGate.php");

        $AdminGareClass= new AdminGareModel();

        if ( isset($_POST['IDGara']) )
        {
            $IDGara= $_POST['IDGara'];
            $TipoGara= $_POST['TipoGara'];

            $PosizioneSessoM= 1;
            $PosizioneSessoF= 1;

            // Estrae risultati gara
            if ($TipoGara == 1) $RisultatiGara= $AdminGareClass->getRisultatiGaraGenerale($IDGara);
            elseif ($TipoGara == 2) $RisultatiGara= $AdminGareClass->getRisultatiGaraCrudo($IDGara);
            elseif ($TipoGara == 3) $RisultatiGara= $AdminGareClass->getRisultatiGaraAvis($IDGara);
            elseif ($TipoGara == 4) $RisultatiGara= $AdminGareClass->getRisultatiGaraOver40($IDGara);
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
                    elseif ($TipoGara == 3 || $TipoGara == 4)
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
                }
                
                $Risposta['RisultatiGara']= $RisultatiGara;
                $Risposta['Successo'] = 1;
            }
            else
                $Risposta['Successo'] = 0;
    
            echo json_encode($Risposta);
        }
    }

    public function ajaxTabellaListaEventi()
    {
        if (isset($_GET))
        {
            $AdminGareClass= new AdminGareModel();

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $field = array("ID","nome","data");
            $order = $field[$column]." ".$dir;

            $total_record    = $AdminGareClass->getEventiAmount();
            $filtered_record = $AdminGareClass->getEventiAmountFiltered($search);
            $Eventi = $AdminGareClass->getEventiPagination($order,$start,$length,$search);
            
            $data_array = array();

            foreach ($Eventi as $evento)
            {
                $modifica= '<a href="' . URL_TO_PUBLIC_FOLDER . 'AdminGare/ModificaEvento/' . $evento->ID . '">
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

        View::renderTemplate('AdminGare/listaGare.html', $ArrayTemplateCombined);
    }

    public function ajaxTabellaListaGare()
    {
        if (isset($_GET))
        {
            $AdminGareClass= new AdminGareModel();
            $GareClass= new GareModel();

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $field = array("A_ID_gara","A_nome_gara","A_nome_evento","A_data_evento");
            $order = $field[$column]." ".$dir;

            $total_record    = $AdminGareClass->getGareAmount();
            $filtered_record = $AdminGareClass->getGareAmountFiltered($search);
            $Gare = $AdminGareClass->getGarePagination($order,$start,$length,$search);
            
            $data_array = array();

            foreach ($Gare as $gara)
            {
                $NomeGara= $gara->A_combinata_gara ? $gara->A_nome_gara . ' <span class="badge badge-danger mx-3">combinata</span>' :  $gara->A_nome_gara;
                $modifica= '<a href="' . URL_TO_PUBLIC_FOLDER . 'AdminGare/ModificaGara/' . $gara->A_ID_gara . '">
                              <button type="button" class="btn button-azzurro btn-block">Modifica</button>
                            </a>';

                $duplica = '
                    <a href="' . URL_TO_PUBLIC_FOLDER . 'AdminGare/InserisciGara/' . $gara->A_ID_gara . '" style="margin: 5px;">
                        <button type="button" class="btn button-azzurro btn-block">Duplica</button>
                    </a>
                ';

                // Risultati caricati
                $Risultati = $GareClass->getTotalePartecipantiGara($gara->A_ID_gara) > 0 ?
                    '<i class="fa fa-list-ol fa-lg p-3" aria-hidden="true" title="Risultati caricati"></i>' :
                    '';

                // Risultati visualizzabili
                $Risultati .= $gara->A_mostra_risultati_gara == 1 ?
                    '<i class="fa fa-eye fa-lg p-3" aria-hidden="true" title="Risultati visualizzabili"></i>' :
                    '';

                $row = array(
                    'id' => $gara->A_ID_gara,
                    'nome_gara' => $NomeGara,
                    'nome_evento' => $gara->A_nome_evento,
                    'data' => $gara->A_data_evento,
                    'risultati' => $Risultati,
                    'modifica' => $modifica . $duplica
                );

                array_push($data_array, $row);
            }

            $arr = array('recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $data_array);
            echo json_encode($arr);
        }
    }

    public function ajaxTabellaListaStaffette()
    {
        if (isset($_GET))
        {
            $AdminGareClass= new AdminGareModel();

            $column =  $_GET['order'][0]['column'];
            $dir    =  $_GET['order'][0]['dir'];
            $search =  $_GET['search']['value'];
            $start =   $_GET['start'];
            $length =  $_GET['length'];
            $field = array("A_ID_staffetta","A_nome_gara","A_nome_staffetta");
            $order = $field[$column]." ".$dir;

            $total_record    = $AdminGareClass->getStaffetteAmount();
            $filtered_record = $AdminGareClass->getStaffetteAmountFiltered($search);
            $Staffette = $AdminGareClass->getStaffettePagination($order,$start,$length,$search);
            
            $data_array = array();

            foreach ($Staffette as $staffetta)
            {
                $Partecipanti = $AdminGareClass->getPartecipantiStaffetta($staffetta->A_ID_staffetta);

                $StringaPartecipanti= '';

                foreach ($Partecipanti as $partecipante)
                {
                    $StringaPartecipanti .= '<div><b class="text-danger">' . $partecipante->A_ps_ordine . ')</b> ' . $partecipante->A_u_cognome . ' ' . $partecipante->A_u_nome . '</div>';
                }

                $row = array(
                    'id' => $staffetta->A_ID_staffetta,
                    'gara' => $staffetta->A_nome_evento . ': ' . $staffetta->A_nome_gara,
                    'nome' => $staffetta->A_nome_staffetta,
                    'partecipanti' => $StringaPartecipanti
                );

                array_push($data_array, $row);
            }

            $arr = array('recordsTotal' => $total_record, 'recordsFiltered' => $filtered_record, 'data' => $data_array);
            echo json_encode($arr);
        }
    }

    public function ajaxCancellaGaraCollegata()
    {
        require_once("AdminGate.php");

        if (isset($_POST['CancellaGaraCollegata']))
        {
            $IDGaraCollegata= $_POST['IDGaraCollegata'];

            $AdminGareClass= new AdminGareModel();
            $AdminGareClass->cancellaGaraCollegata($IDGaraCollegata);

            $Risposta['Risposta']= 'ok';
            echo json_encode($Risposta);
        }
    }

    public function ajaxAggiungiGaraCollegata()
    {
        require_once("AdminGate.php");

        if (isset($_POST['AggiungiGaraCollegata']))
        {
            $IDGaraDaCollegare= $_POST['IDGaraDaCollegare'];
            $IDGara= $_POST['IDGara'];

            $AdminGareClass= new AdminGareModel();

            // Aggiunge se non presente
            $Check= $AdminGareClass->checkCollegamentoGaraEsistente($IDGara, $IDGaraDaCollegare);

            $IDCollegamento= $Check < 1 ? $AdminGareClass->aggiungiGaraDaCollegare($IDGara, $IDGaraDaCollegare) : 0;

            $Risposta['Risposta']= 'ok';
            $Risposta['IDCollegamento'] = $IDCollegamento;
            echo json_encode($Risposta);
        }
    }
}
