<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\AdminGareModel;
use \App\Models\GareModel;
use \App\Models\EmailModel;
use \App\Models\FunctionsModel;
use stdClass;

class AdminEmail extends \Core\Controller
{
    public function EmailConFiltri()
    {
        require_once("AdminGate.php");

        $AdminGareClass= new AdminGareModel();
        $GareClass= new GareModel();

        $Eventi= $AdminGareClass->getEventi();
        $Gare = $GareClass->getAllGare(0);

        $ArrayTemplateAction= ['Eventi' => $Eventi,
                               'Gare' => $Gare];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminEmail/emailConFiltri.html', $ArrayTemplateCombined);
    }

    public function ModificaTemplateEmail()
    {
        require_once("AdminGate.php");

        $id_template_email= $this->route_params['id'];

        $EmailClass = new EmailModel();
        $TemplateEmail = $EmailClass->getTemplateEmail($id_template_email);

        $ArrayTemplateAction= ['TemplateEmail' => $TemplateEmail];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminEmail/modificaTemplateEmail.html', $ArrayTemplateCombined);
    }

    public function ModificaTemplateEmailConfirm()
    {
        require_once("AdminGate.php");

        $EmailClass = new EmailModel();

        if ( isset($_POST['ConfermaModificaTemplateEmail']) )
        {
            $IDTemplateEmail= $_POST['IDTemplateEmail'];
            $Nome= $_POST['Nome'];
            $Oggetto= $_POST['Oggetto'];
            $Messaggio= $_POST['Messaggio'];

            $EmailClass->AggiornaTemplateEmail($IDTemplateEmail, $Nome, $Oggetto, $Messaggio);
            $TemplateEmail= $EmailClass->getTemplateEmail($IDTemplateEmail);

            $TemplateEmailModificato= '<div class="text-success text-center">Template Email Modificato!</div>';
        }

        $ArrayTemplateAction= ['TemplateEmail' => $TemplateEmail,
                               'TemplateEmailModificato' => $TemplateEmailModificato];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminEmail/modificaTemplateEmail.html', $ArrayTemplateCombined);
    }

    public function ajaxUtentiEmailConFiltri()
    {
        require_once("AdminGate.php");

        if (isset($_POST['UtentiEmailConFiltri']))
        {
            $TipoUtente = $_POST['TipoUtente'];
            $TipoIscrizioni = $_POST['TipoIscrizioni'];
            $IDEvento = $_POST['IDEvento'];
            $IDGara = $_POST['IDGara'];
            
            $EmailClass = new EmailModel();
            $Risultati = $EmailClass->getUtentiConFiltri($TipoUtente, $TipoIscrizioni, $IDEvento, $IDGara);

            $Risposta['Risultati']= $Risultati;

            echo json_encode($Risposta);
        }
    }

    public function ajaxInviaEmail()
    {
        require_once("AdminGate.php");

        if (isset($_POST['InviaEmail']))
        {
            $Email = $_POST['Email'];
            $OggettoEmail = $_POST['OggettoEmail'];
            $MessaggioEmail = $_POST['MessaggioEmail'];
            $Simulazione = $_POST['Simulazione'];

            $FunctionClass = new FunctionsModel();

            if ($Simulazione == 'true')
            {
                $Random = mt_rand(0, 10);
                usleep(200000);

                if ($Random < 8)
                    $Esito= true;
                else
                    $Esito= false;

                $Risposta['Tipo']= 'Simulazione';
            }
            else
            {
                $Esito= $FunctionClass->sendEmail($Email,'',$OggettoEmail,$MessaggioEmail);
                $Risposta['Tipo']= 'Invio';
            }

            $Risposta['Esito']= $Esito;

            echo json_encode($Risposta);
        }
    }

    public function ajaxInviaEmailRisultatoGara()
    {
        require_once("AdminGate.php");

        if (isset($_POST['InviaEmail']))
        {
            $Nome = $_POST['Nome'];
            $Cognome = $_POST['Cognome'];
            $Email = $_POST['Email'];
            $IDRisultato = $_POST['IDRisultato'];
            $Simulazione = $_POST['Simulazione'];

            $OggettoEmail = 'Risultati ItalianOpenWaterTour';

            $MessaggioEmail = '
                <h1>
                    Congratulazioni ' . $Nome . ' ' . $Cognome . '!
                </h1>
                <p>
                    Clicca
                    <a href="https://' . URL_TO_PUBLIC_FOLDER . 'Share/SharedUserRaceResult/' . $IDRisultato .'">qui</a>
                    per vedere il risultato!
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <table cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="border-radius: 2px;" bgcolor=" #05abe0">
                                            <a href=https://' . URL_TO_PUBLIC_FOLDER . 'Share/SharedUserRaceResult/' . $IDRisultato .' target="_blank" style="padding: 8px 12px; border: 1px solid #ED2939;border-radius: 2px;font-family: Helvetica, Arial, sans-serif;font-size: 14px; color: #ffffff;text-decoration: none;font-weight:bold;display: inline-block;">
                                            vedi il tuo risultato          
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                <p>le classifiche in tempo reale le trovi nella pagina della tappa sul sito <a href="https://italianopenwatertour.com"> italianopenwatertour.com </a><br>
                    <img src="https://italianopenwatertour.com/wp-content/uploads/2022/02/IOWT-2022-LOGO-1200x122.png">
                </p>
            ';

            $FunctionClass = new FunctionsModel();

            if ($Simulazione == 'true')
            {
                $Random = mt_rand(0, 10);
                usleep(20000);

                if ($Random < 8)
                    $Esito = true;
                else
                    $Esito = false;

                $Risposta['Tipo']= 'Simulazione';
            }
            else
            {
                $Esito = $FunctionClass->sendEmail($Email,'',$OggettoEmail,$MessaggioEmail);
                $Risposta['Tipo'] = 'Invio';
            }

            $Risposta['Esito']= $Esito;

            echo json_encode($Risposta);
        }
    }

    public function ajaxInviaTestTemplateEmail()
    {
        require_once("AdminGate.php");

        if (isset($_POST['TestEmail']))
        {
            $TestEmail = $_POST['TestEmail'];
            $Oggetto = $_POST['Oggetto'];
            $Messaggio = $_POST['Messaggio'];

            $FunctionClass = new FunctionsModel();

            $Oggetto = str_replace('{{ Utente.nome }}', 'Mario', $Oggetto);
            $Oggetto = str_replace('{{ Utente.cognome }}', 'Rossi', $Oggetto);
            $Oggetto = str_replace('{{ ImportoPagamento }}', '45.85', $Oggetto);

            $EmailListaGare = '
            <br/>
            <div>Gara 1</div>
            <br/>
            <div>Gara 2</div>
            <br/>
            <div>Gara 3</div>';

            $Messaggio = str_replace('{{ Logo }}', LOGO_LINK_HTML, $Messaggio);
            $Messaggio = str_replace('{{ Utente.nome }}', 'Mario', $Messaggio);
            $Messaggio = str_replace('{{ Utente.cognome }}', 'Rossi', $Messaggio);
            $Messaggio = str_replace('{{ ImportoPagamento }}', '45.85', $Messaggio);
            $Messaggio = str_replace('{{ ListaAcquisti }}', $EmailListaGare, $Messaggio);

            $Esito= $FunctionClass->sendEmail($TestEmail,'',$Oggetto,$Messaggio);

            $Risposta['Esito']= $Esito;

            echo json_encode($Risposta);
        }
    }
}
