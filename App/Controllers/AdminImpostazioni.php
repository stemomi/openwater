<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\ImpostazioniModel;
use \App\Models\FunctionsModel;
use stdClass;

class AdminImpostazioni extends \Core\Controller
{
    public function Impostazioni()
    {
        require_once("AdminGate.php");

        $ImpostazioniClass = new ImpostazioniModel();

        $ImpostazioniSalvate = '';

        if (isset($_POST['ConfermaImpostazioni']))
        {
            $AbilitaBonificoFlag = isset($_POST['AbilitaBonifico']) ? 1 : 0;

            $ImpostazioniClass->aggiornaImpostazioni('AbilitaBonifico', $AbilitaBonificoFlag);

            $ImpostazioniSalvate = '<div class="text-success text-center">Impostazioni Salvate!</div>';
        }

        $Impostazioni_AbilitaBonifico = $ImpostazioniClass->getImpostazioni('AbilitaBonifico');

        $ArrayTemplateAction = [
            'Impostazioni_AbilitaBonifico' => $Impostazioni_AbilitaBonifico,
            'ImpostazioniSalvate' => $ImpostazioniSalvate
        ];
        $ArrayTemplateCombined= $ArrayTemplateGate + $ArrayTemplateAction;

        View::renderTemplate('AdminImpostazioni/impostazioni.html', $ArrayTemplateCombined);
    }
}