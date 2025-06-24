<?php
use \App\Models\AccountModel;
use \App\Models\SquadreModel;

$AccountClass = new AccountModel();
$SquadreClass = new SquadreModel();

$URLPrincipale = URL_TO_PUBLIC_FOLDER;

if (session_status() == PHP_SESSION_NONE) 
    session_start();

if (!isset($_SESSION['IDUtente']))
{
      header("Location: " . $URLPrincipale . "/Account/login");
      exit();
}

// Controlla permessi pagina
$ControllerChiamato = $this->route_params['controller'];

$Utente = $AccountClass->getUtenteSessione();

$capo_squadra_id = $SquadreClass->getCapoSquadraId($Utente->IDSquadra);

// Calcolo età utente
$DataNascitaGate = new DateTime($Utente->data_nascita);
$DataOdiernaGate = new DateTime("now");

$Utente->minorenne = 
    $DataNascitaGate->diff($DataOdiernaGate)->y < 18 ?
    1 : 
    0;

$Utente->autocertificazione_minori_18_anni_file_da_caricare = 
    $Utente->autocertificazione_minori_18_anni_file == null && $Utente->minorenne ?
    1 :
    0;

// Template gate pagine
$ArrayTemplateGate = 
[
    'IDUtente' => $_SESSION['IDUtente'],
    'Utente' => $Utente,
    'capo_squadra_id' => $capo_squadra_id,
    'MAGGIORAZIONE_PAYPAL' => number_format(MAGGIORAZIONE_PAYPAL, 2 , '.', ''),
    'MAGGIORAZIONE_PAYPAL_2' => number_format(MAGGIORAZIONE_PAYPAL_2, 2 , '.', ''),
    'PREZZO_BOA' => number_format(PREZZO_BOA, 2 , '.', ''),
    //'SCONTO_STAFFETTA' => number_format(SCONTO_STAFFETTA, 2 , '.', ''),
    'URL_AUTOCERTIFICAZIONE_MINORI_18_ANNI' => URL_AUTOCERTIFICAZIONE_MINORI_18_ANNI,
    'PREZZO_MAGLIETTA_1' => PREZZO_MAGLIETTA_1,
    'PREZZO_MAGLIETTA_2' => PREZZO_MAGLIETTA_2,
    'PREZZO_MAGLIETTA_3' => PREZZO_MAGLIETTA_3,
    'URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER,
    'URL_TO_PUBLIC_FOLDER_NO_SLASHES' => URL_TO_PUBLIC_FOLDER_NO_SLASHES,
    'URL_NO_IMG_AVAILABLE' => URL_NO_IMG_AVAILABLE,
    'PATH_EVENTI_FOTO' => PATH_EVENTI_FOTO,
    'URL_EVENTI_FOTO' => URL_EVENTI_FOTO
];
?>