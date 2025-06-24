<?php

$URLPrincipale= URL_TO_PUBLIC_FOLDER;

if (session_status() == PHP_SESSION_NONE) 
    session_start();

if ( !isset($_SESSION['IDAdmin']))
{
      header("Location: " . $URLPrincipale . "/AdminAccount/login");
      exit();
}

$ControllerChiamato= $this->route_params['controller'];

// Template gate pagine
$ArrayTemplateGate= [
            'IDAdmin' => $_SESSION['IDAdmin'],
            'URL_TO_PUBLIC_FOLDER' => URL_TO_PUBLIC_FOLDER,
            'URL_GPS_FILES' => URL_GPS_FILES,
            'URL_GPS_FOTO' => URL_GPS_FOTO,
            'URL_AUTOCERTIFICAZIONE_MINORI_18_ANNI' => URL_AUTOCERTIFICAZIONE_MINORI_18_ANNI,
            'URL_NO_IMG_AVAILABLE' => URL_NO_IMG_AVAILABLE,
            'PATH_EVENTI_FOTO' => PATH_EVENTI_FOTO,
            'URL_EVENTI_FOTO' => URL_EVENTI_FOTO
            ];
?>