<?php

/**
 * Front controller
 *
 * PHP version 7.0
 */

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';




/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

/**
 * Routing
 */
$router = new Core\Router();

define('MAIN_PATH', str_replace('public', '', __DIR__));
define('PUBLIC_PATH', __DIR__ . '/');
$AddPublic= strpos('localhost', $_SERVER['HTTP_HOST']) !== false ? 'public/' : '';
define('URL_TO_PUBLIC_FOLDER', "//" . $_SERVER['HTTP_HOST'] . str_replace('public', '', dirname($_SERVER['SCRIPT_NAME'])) . $AddPublic);
define('URL_TO_PUBLIC_FOLDER_NO_SLASHES', $_SERVER['HTTP_HOST'] . str_replace('public', '', dirname($_SERVER['SCRIPT_NAME'])) . $AddPublic);

define('PATH_GPS_FILES', __DIR__ . '/gps_files/');
define('PATH_GPS_FOTO', __DIR__ . '/gps_foto/');
define('PATH_SHARE_RACE_RESULTS', __DIR__ . '/share_race_results/');
define('PATH_AWARD_IMAGE', __DIR__ . '/immagini/award.jpg');
define('PATH_FONT', __DIR__ . '/font/');
define('PATH_EVENTI_FOTO', __DIR__ . '/foto-eventi/');

define('URL_GPS_FILES', URL_TO_PUBLIC_FOLDER . '/gps_files/');
define('URL_GPS_FOTO', URL_TO_PUBLIC_FOLDER . '/gps_foto/');
define('URL_SHARE_RACE_RESULTS', URL_TO_PUBLIC_FOLDER . '/share_race_results/');
define('URL_SHARE_RACE_RESULTS_NO_SLASHES', URL_TO_PUBLIC_FOLDER_NO_SLASHES . '/share_race_results/');
define('URL_AUTOCERTIFICAZIONE_MINORI_18_ANNI', URL_TO_PUBLIC_FOLDER . '/files/autocertificazione_minori_18_anni.pdf');
define('URL_NO_IMG_AVAILABLE', URL_TO_PUBLIC_FOLDER . '/img/no_image_available.png');
define('URL_EVENTI_FOTO', URL_TO_PUBLIC_FOLDER . '/foto-eventi/');

define('MAGGIORAZIONE_PAYPAL', 2.50);
define('MAGGIORAZIONE_PAYPAL_2', 4.00);
define('MAGGIORAZIONE_PAYPAL_3', 7.00);
define('MAGGIORAZIONE_PAYPAL_4', 10.00);
define('MAGGIORAZIONE_PAYPAL_5', 20.00);
define('PREZZO_BOA', 7.00);
define('SCONTO_STAFFETTA', 5.00);
define('LOGO_LINK_HTML', '<img src="http://italianopenwatertour.com/wp-content/uploads/2019/01/logo_menu.png" style="margin: 0 auto; text-align: center; padding: 20px;">');
define('PREZZO_MAGLIETTA_1', 15.00);
define('PREZZO_MAGLIETTA_2', 20.00);
define('PREZZO_MAGLIETTA_3', 25.00);

// Admin Account
$router->add('AdminAccount/Profile', ['controller' => 'AdminAccount', 'action' => 'Profile']);
$router->add('AdminAccount/Login', ['controller' => 'AdminAccount', 'action' => 'Login']);
$router->add('AdminAccount/LoginConfirm', ['controller' => 'AdminAccount', 'action' => 'LoginConfirm']);
$router->add('AdminAccount/Logout', ['controller' => 'AdminAccount', 'action' => 'Logout']);

$router->add('AdminAccount/Iscrizioni', ['controller' => 'AdminAccount', 'action' => 'Iscrizioni']);
$router->add('AdminAccount/IscrizioniAnno', ['controller' => 'AdminAccount', 'action' => 'iscrizioniAnno']);
$router->add('AdminAccount/iscrizioniSquadre', ['controller' => 'AdminAccount', 'action' => 'IscrizioniSquadre']);
$router->add('AdminAccount/IscrizioniDaPagare', ['controller' => 'AdminAccount', 'action' => 'IscrizioniDaPagare']);
$router->add('AdminAccount/ScaricaCSVIscrizioni', ['controller' => 'AdminAccount', 'action' => 'ScaricaCSVIscrizioni']);
$router->add('AdminAccount/ajaxGeneraCSVIscrizioniPerGara', ['controller' => 'AdminAccount', 'action' => 'ajaxGeneraCSVIscrizioniPerGara']);
$router->add('AdminAccount/ajaxGeneraCSVNewsletterPerGara', ['controller' => 'AdminAccount', 'action' => 'ajaxGeneraCSVNewsletterPerGara']);
$router->add('AdminAccount/ajaxTabellaIscrizioni/{tipo:\d+}/{dapagare:\d+}', ['controller' => 'AdminAccount', 'action' => 'ajaxTabellaIscrizioni']);
$router->add('AdminAccount/ajaxTabellaIscrizioniSquadre/{id:\d+}', ['controller' => 'AdminAccount', 'action' => 'ajaxTabellaIscrizioniSquadre']);
$router->add('AdminAccount/impostaPagamentoIscrizioneSquadra/{id:\d+}', ['controller' => 'AdminAccount', 'action' => 'impostaPagamentoIscrizioneSquadra']);
$router->add('AdminAccount/ajaxDettagliIscrizioneSquadra/{id:\d+}', ['controller' => 'AdminAccount', 'action' => 'ajaxDettagliIscrizioneSquadra']);
$router->add('AdminAccount/ajaxAcquistiBoeSquadra/{id:\d+}', ['controller' => 'AdminAccount', 'action' => 'ajaxAcquistiBoeSquadra']);
$router->add('AdminAccount/DettagliIscrizione/{id:\d+}', ['controller' => 'AdminAccount', 'action' => 'DettagliIscrizione']);
$router->add('AdminAccount/CancellaIscrizione/{id:\d+}/{iscrizionedapagare:\d+}', ['controller' => 'AdminAccount', 'action' => 'CancellaIscrizione']);
$router->add('AdminAccount/CancellaIscrizioniDopoUnGiorno', ['controller' => 'AdminAccount', 'action' => 'CancellaIscrizioniDopoUnGiorno']);
$router->add('AdminAccount/ConfermaDettagliIscrizione', ['controller' => 'AdminAccount', 'action' => 'ConfermaDettagliIscrizione']);
$router->add('AdminAccount/AcquistiMagliette', ['controller' => 'AdminAccount', 'action' => 'AcquistiMagliette']);
$router->add('AdminAccount/MaglietteDaPagare', ['controller' => 'AdminAccount', 'action' => 'MaglietteDaPagare']);
$router->add('AdminAccount/ajaxTabellaAcquistiMagliette/{dapagare:\d+}', ['controller' => 'AdminAccount', 'action' => 'ajaxTabellaAcquistiMagliette']);
$router->add('AdminAccount/CancellaAcquistoMagliette/{id:\d+}/{dapagare:\d+}', ['controller' => 'AdminAccount', 'action' => 'CancellaAcquistoMagliette']);
$router->add('AdminAccount/listaOpzioniAcquistoAcquistate', ['controller' => 'AdminAccount', 'action' => 'listaOpzioniAcquistoAcquistate']);
$router->add('AdminAccount/ajaxTabellaAcquistiOpzioniAcquisto', ['controller' => 'AdminAccount', 'action' => 'ajaxTabellaAcquistiOpzioniAcquisto']);

$router->add('AdminAccount/IscrizioniGareIndividuali', ['controller' => 'AdminAccount', 'action' => 'IscrizioniGareIndividuali']);
$router->add('AdminAccount/IscrizioniAnnoGareIndividuali', ['controller' => 'AdminAccount', 'action' => 'IscrizioniAnnoGareIndividuali']);
$router->add('AdminAccount/ajaxTabellaIscrizioniGareIndividuali/{tipo:\d+}', ['controller' => 'AdminAccount', 'action' => 'ajaxTabellaIscrizioniGareIndividuali']);
$router->add('AdminAccount/DettagliIscrizioneGaraIndividuale/{id:\d+}', ['controller' => 'AdminAccount', 'action' => 'DettagliIscrizioneGaraIndividuale']);
$router->add('AdminAccount/CancellaIscrizioneGaraIndividuale/{id:\d+}', ['controller' => 'AdminAccount', 'action' => 'CancellaIscrizioneGaraIndividuale']);
$router->add('AdminAccount/ConfermaDettagliIscrizioneGaraIndividuale', ['controller' => 'AdminAccount', 'action' => 'ConfermaDettagliIscrizioneGaraIndividuale']);
$router->add('AdminAccount/cancellaOpzioneAcquistoAcquistata/{id:\d+}', ['controller' => 'AdminAccount', 'action' => 'cancellaOpzioneAcquistoAcquistata']);

// Admin Utenti
$router->add('AdminAccount/Utenti', ['controller' => 'AdminAccount', 'action' => 'Utenti']);
$router->add('AdminAccount/ajaxUtenti/{isminorenne:\d+}', ['controller' => 'AdminAccount', 'action' => 'ajaxUtenti']);
$router->add('AdminAccount/UtentiMinorenni', ['controller' => 'AdminAccount', 'action' => 'UtentiMinorenni']);
$router->add('AdminAccount/ModificaUtente/{id:\d+}', ['controller' => 'AdminAccount', 'action' => 'ModificaUtente']);
$router->add('AdminAccount/ConfermaModificaUtente', ['controller' => 'AdminAccount', 'action' => 'ConfermaModificaUtente']);
$router->add('AdminAccount/ajaxGeneraCSVStaffetteGara', ['controller' => 'AdminAccount', 'action' => 'ajaxGeneraCSVStaffetteGara']);
$router->add('AdminAccount/ajaxGeneraCSVNewsletterStaffetteGara', ['controller' => 'AdminAccount', 'action' => 'ajaxGeneraCSVNewsletterStaffetteGara']);

// Admin Squadre
$router->add('AdminAccount/Squadre', ['controller' => 'AdminAccount', 'action' => 'Squadre']);
$router->add('AdminAccount/ajaxSquadre', ['controller' => 'AdminAccount', 'action' => 'ajaxSquadre']);
$router->add('AdminAccount/CreaSquadra', ['controller' => 'AdminAccount', 'action' => 'CreaSquadra']);
$router->add('AdminAccount/ConfermaCreaSquadra', ['controller' => 'AdminAccount', 'action' => 'ConfermaCreaSquadra']);
$router->add('AdminAccount/ModificaSquadra/{id:\d+}', ['controller' => 'AdminAccount', 'action' => 'ModificaSquadra']);
$router->add('AdminAccount/ConfermaModificaSquadra', ['controller' => 'AdminAccount', 'action' => 'ConfermaModificaSquadra']);

// Admin Gare
$router->add('AdminGare/InserisciEvento', ['controller' => 'AdminGare', 'action' => 'InserisciEvento']);
$router->add('AdminGare/InserisciGara/{id:\d+}', ['controller' => 'AdminGare', 'action' => 'InserisciGara']);
$router->add('AdminGare/ModificaEvento/{id:\d+}', ['controller' => 'AdminGare', 'action' => 'ModificaEvento']);
$router->add('AdminGare/ModificaEventoConfirm', ['controller' => 'AdminGare', 'action' => 'ModificaEventoConfirm']);
$router->add('AdminGare/ModificaGara/{id:\d+}', ['controller' => 'AdminGare', 'action' => 'ModificaGara']);
$router->add('AdminGare/ModificaGaraConfirm', ['controller' => 'AdminGare', 'action' => 'ModificaGaraConfirm']);
$router->add('AdminGare/ListaEventi', ['controller' => 'AdminGare', 'action' => 'ListaEventi']);
$router->add('AdminGare/ListaGare', ['controller' => 'AdminGare', 'action' => 'ListaGare']);
$router->add('AdminGare/ListaStaffette', ['controller' => 'AdminGare', 'action' => 'ListaStaffette']);
$router->add('AdminGare/ajaxTabellaListaEventi', ['controller' => 'AdminGare', 'action' => 'ajaxTabellaListaEventi']);
$router->add('AdminGare/ajaxTabellaListaGare', ['controller' => 'AdminGare', 'action' => 'ajaxTabellaListaGare']);
$router->add('AdminGare/ajaxTabellaListaStaffette', ['controller' => 'AdminGare', 'action' => 'ajaxTabellaListaStaffette']);
$router->add('AdminGare/ajaxCancellaGaraCollegata', ['controller' => 'AdminGare', 'action' => 'ajaxCancellaGaraCollegata']);
$router->add('AdminGare/ajaxAggiungiGaraCollegata', ['controller' => 'AdminGare', 'action' => 'ajaxAggiungiGaraCollegata']);
$router->add('AdminGare/CaricaRisultati', ['controller' => 'AdminGare', 'action' => 'CaricaRisultati']);
$router->add('AdminGare/CaricaRisultatiConfirm', ['controller' => 'AdminGare', 'action' => 'CaricaRisultatiConfirm']);
$router->add('AdminGare/CaricaRisultatiStaffette', ['controller' => 'AdminGare', 'action' => 'CaricaRisultatiStaffette']);
$router->add('AdminGare/CaricaRisultatiStaffetteConfirm', ['controller' => 'AdminGare', 'action' => 'CaricaRisultatiStaffetteConfirm']);
$router->add('AdminGare/Risultati', ['controller' => 'AdminGare', 'action' => 'Risultati']);
$router->add('AdminGare/Risultati/{id:\d+}', ['controller' => 'AdminGare', 'action' => 'Risultati']);
$router->add('AdminGare/ajaxTabellaRisultati', ['controller' => 'AdminGare', 'action' => 'ajaxTabellaRisultati']);
$router->add('AdminGare/StartingList', ['controller' => 'AdminGare','action' => 'StartingList']);
$router->add('AdminGare/getPartecipanti', ['controller' => 'AdminGare','action' => 'getPartecipanti']);


// Admin Gare Individuali
$router->add('AdminGareIndividuali/InserisciEvento', ['controller' => 'AdminGareIndividuali', 'action' => 'InserisciEvento']);
$router->add('AdminGareIndividuali/InserisciGara', ['controller' => 'AdminGareIndividuali', 'action' => 'InserisciGara']);
$router->add('AdminGareIndividuali/ModificaEvento/{id:\d+}', ['controller' => 'AdminGareIndividuali', 'action' => 'ModificaEvento']);
$router->add('AdminGareIndividuali/ModificaEventoConfirm', ['controller' => 'AdminGareIndividuali', 'action' => 'ModificaEventoConfirm']);
$router->add('AdminGareIndividuali/ModificaGara/{id:\d+}', ['controller' => 'AdminGareIndividuali', 'action' => 'ModificaGara']);
$router->add('AdminGareIndividuali/ModificaGaraConfirm', ['controller' => 'AdminGareIndividuali', 'action' => 'ModificaGaraConfirm']);
$router->add('AdminGareIndividuali/ListaEventi', ['controller' => 'AdminGareIndividuali', 'action' => 'ListaEventi']);
$router->add('AdminGareIndividuali/ListaGare', ['controller' => 'AdminGareIndividuali', 'action' => 'ListaGare']);
$router->add('AdminGareIndividuali/ajaxTabellaListaEventi', ['controller' => 'AdminGareIndividuali', 'action' => 'ajaxTabellaListaEventi']);
$router->add('AdminGareIndividuali/ajaxTabellaListaGare', ['controller' => 'AdminGareIndividuali', 'action' => 'ajaxTabellaListaGare']);
$router->add('AdminGareIndividuali/Risultati', ['controller' => 'AdminGareIndividuali', 'action' => 'Risultati']);
$router->add('AdminGareIndividuali/ajaxTabellaRisultati', ['controller' => 'AdminGareIndividuali', 'action' => 'ajaxTabellaRisultati']);
$router->add('AdminGareIndividuali/DettagliRisultato/{id:\d+}', ['controller' => 'AdminGareIndividuali', 'action' => 'DettagliRisultato']);
$router->add('AdminGareIndividuali/ConfermaDettagliRisultato', ['controller' => 'AdminGareIndividuali', 'action' => 'ConfermaDettagliRisultato']);

// Admin Sconti
$router->add('AdminSconti/ListaScontiComboGare', ['controller' => 'AdminSconti', 'action' => 'ListaScontiComboGare']);
$router->add('AdminSconti/InserisciScontoComboGare', ['controller' => 'AdminSconti', 'action' => 'InserisciScontoComboGare']);
$router->add('AdminSconti/ajaxTabellaListaScontiComboGare', ['controller' => 'AdminSconti', 'action' => 'ajaxTabellaListaScontiComboGare']);

// Admin Email
$router->add('AdminEmail/EmailConFiltri', ['controller' => 'AdminEmail', 'action' => 'EmailConFiltri']);
$router->add('AdminEmail/ModificaTemplateEmail/{id:\d+}', ['controller' => 'AdminEmail', 'action' => 'ModificaTemplateEmail']);
$router->add('AdminEmail/ModificaTemplateEmailConfirm', ['controller' => 'AdminEmail', 'action' => 'ModificaTemplateEmailConfirm']);
$router->add('AdminEmail/ajaxUtentiEmailConFiltri', ['controller' => 'AdminEmail', 'action' => 'ajaxUtentiEmailConFiltri']);
$router->add('AdminEmail/ajaxInviaEmail', ['controller' => 'AdminEmail', 'action' => 'ajaxInviaEmail']);
$router->add('AdminEmail/ajaxInviaTestTemplateEmail', ['controller' => 'AdminEmail', 'action' => 'ajaxInviaTestTemplateEmail']);
$router->add('AdminEmail/ajaxInviaEmailRisultatoGara', ['controller' => 'AdminEmail', 'action' => 'ajaxInviaEmailRisultatoGara']);

// Admin Impostazioni
$router->add('AdminImpostazioni/Impostazioni', ['controller' => 'AdminImpostazioni', 'action' => 'Impostazioni']);

// Home
$router->add('GestionePaypalIPN', ['controller' => 'Home', 'action' => 'GestionePaypalIPN']);
$router->add('GestionePaypalIPNTest', ['controller' => 'Home', 'action' => 'GestionePaypalIPNTest']);
$router->add('Test', ['controller' => 'Home', 'action' => 'Test']);

// Account
$router->add('', ['controller' => 'Account', 'action' => 'login']); // Index va a login
$router->add('Account/Profile', ['controller' => 'Account', 'action' => 'Profile']);
$router->add('Account/EditProfile', ['controller' => 'Account', 'action' => 'EditProfile']);
$router->add('Account/EditProfileConfirm', ['controller' => 'Account', 'action' => 'EditProfileConfirm']);

$router->add('Account/Iscrizioni', ['controller' => 'Account', 'action' => 'Iscrizioni']);
$router->add('Account/CaricaBonifico', ['controller' => 'Account', 'action' => 'CaricaBonifico']);
$router->add('Account/CaricaCoupon', ['controller' => 'Account', 'action' => 'CaricaCoupon']);
$router->add('Account/PagaConCredito', ['controller' => 'Account', 'action' => 'PagaConCredito']);

$router->add('Account/Login', ['controller' => 'Account', 'action' => 'Login']);
$router->add('Account/LoginConfirm', ['controller' => 'Account', 'action' => 'LoginConfirm']);

$router->add('Account/Register', ['controller' => 'Account', 'action' => 'Register']);
$router->add('Account/RegisterConfirm', ['controller' => 'Account', 'action' => 'RegisterConfirm']);

$router->add('Account/ForgotPassword', ['controller' => 'Account', 'action' => 'ForgotPassword']);
$router->add('Account/ForgotPasswordConfirm', ['controller' => 'Account', 'action' => 'ForgotPasswordConfirm']);

$router->add('Account/ResetPassword/{codice:\w+}', ['controller' => 'Account', 'action' => 'ResetPassword']);
$router->add('Account/ResetPasswordConfirm', ['controller' => 'Account', 'action' => 'ResetPasswordConfirm']);

$router->add('Account/Logout', ['controller' => 'Account', 'action' => 'Logout']);

$router->add('Account/Promo', ['controller' => 'Account', 'action' => 'Promo']);
$router->add('Account/confermaSelezioneMagliette', ['controller' => 'Account', 'action' => 'confermaSelezioneMagliette']);
$router->add('Account/cancellaIscrizione/{iscrizioneid:\d+}', ['controller' => 'Account', 'action' => 'cancellaIscrizione']);
$router->add('Account/cancellaOpzioneAcquisto/{opzioneacquistoid:\d+}', ['controller' => 'Account', 'action' => 'cancellaOpzioneAcquisto']);
$router->add('Account/cancellaMagliette/{acquistimaglietteid:\d+}', ['controller' => 'Account', 'action' => 'cancellaMagliette']);

// Gare
$router->add('Iscrizioni', ['controller' => 'Gare', 'action' => 'Aperte']); // da link sito
$router->add('Gare/Aperte/{id:\d+}/{page:\d+}', ['controller' => 'Gare', 'action' => 'Aperte']);
$router->add('Gare/Passate', ['controller' => 'Gare', 'action' => 'Passate']);
$router->add('Gare/ConfermaIscrizioni/{id:\d+}/{page:\d+}', ['controller' => 'Gare', 'action' => 'ConfermaIscrizioni']);
$router->add('Gare/RisultatiGara/{id:\d+}', ['controller' => 'Gare', 'action' => 'RisultatiGara']);
$router->add('Gare/Risultati', ['controller' => 'Gare', 'action' => 'Risultati']);
$router->add('Gare/ajaxTabellaRisultati', ['controller' => 'Gare', 'action' => 'ajaxTabellaRisultati']);
$router->add('Gare/listaMembriSquadra', ['controller' => 'Gare', 'action' => 'listaMembriSquadra']);
$router->add('Gare/ajaxAggiungiIscrizioneSquadra', ['controller' => 'Gare', 'action' => 'ajaxAggiungiIscrizioneSquadra']);
$router->add('Gare/listaIscrizioniSquadra', ['controller' => 'Gare', 'action' => 'listaIscrizioniSquadra']);

// Gare Individuali
$router->add('GareIndividuali/Aperte', ['controller' => 'GareIndividuali', 'action' => 'Aperte']);
$router->add('GareIndividuali/Passate', ['controller' => 'GareIndividuali', 'action' => 'Passate']);
$router->add('GareIndividuali/ConfermaIscrizioni', ['controller' => 'GareIndividuali', 'action' => 'ConfermaIscrizioni']);
$router->add('GareIndividuali/CaricaRisultato/{id:\d+}', ['controller' => 'GareIndividuali', 'action' => 'CaricaRisultato']);
$router->add('GareIndividuali/CaricaRisultatoConfirm', ['controller' => 'GareIndividuali', 'action' => 'CaricaRisultatoConfirm']);

// Staffette
$router->add('Staffette/CreaSquadra', ['controller' => 'Staffette', 'action' => 'CreaSquadra']);
$router->add('Staffette/ModificaSquadra/{id:\d+}', ['controller' => 'Staffette', 'action' => 'ModificaSquadra']);
$router->add('Staffette/LeMieSquadre', ['controller' => 'Staffette', 'action' => 'LeMieSquadre']);
$router->add('Staffette/ajaxTabellaLeMieSquadre', ['controller' => 'Staffette', 'action' => 'ajaxTabellaLeMieSquadre']);
$router->add('Staffette/ajaxCancellaPartecipazione', ['controller' => 'Staffette', 'action' => 'ajaxCancellaPartecipazione']);
$router->add('Staffette/ajaxAggiungiPartecipazione', ['controller' => 'Staffette', 'action' => 'ajaxAggiungiPartecipazione']);

// Opzioni acquisto
$router->add('OpzioniAcquisto/Lista', ['controller' => 'OpzioniAcquisto', 'action' => 'Lista']);
$router->add('OpzioniAcquisto/ajaxOpzioniAcquisto', ['controller' => 'OpzioniAcquisto', 'action' => 'ajaxOpzioniAcquisto']);
$router->add('OpzioniAcquisto/CreaOpzioneAcquisto', ['controller' => 'OpzioniAcquisto', 'action' => 'CreaOpzioneAcquisto']);
$router->add('OpzioniAcquisto/CreaOpzioneAcquistoConferma', ['controller' => 'OpzioniAcquisto', 'action' => 'CreaOpzioneAcquistoConferma']);
$router->add('OpzioniAcquisto/ModificaOpzioneAcquisto/{id:\d+}', ['controller' => 'OpzioniAcquisto', 'action' => 'ModificaOpzioneAcquisto']);
$router->add('OpzioniAcquisto/ModificaOpzioneAcquistoConferma', ['controller' => 'OpzioniAcquisto', 'action' => 'ModificaOpzioneAcquistoConferma']);
$router->add('OpzioniAcquisto/ajaxCancellaOpzioneAcquistoEvento', ['controller' => 'OpzioniAcquisto', 'action' => 'ajaxCancellaOpzioneAcquistoEvento']);
$router->add('OpzioniAcquisto/ajaxAggiungiOpzioneAcquistoEvento', ['controller' => 'OpzioniAcquisto', 'action' => 'ajaxAggiungiOpzioneAcquistoEvento']);

// Prodotti
$router->add('Prodotti/Lista', ['controller' => 'Prodotti', 'action' => 'Lista']);
$router->add('Prodotti/ajaxProdotti', ['controller' => 'Prodotti', 'action' => 'ajaxProdotti']);
$router->add('Prodotti/CreaProdotto', ['controller' => 'Prodotti', 'action' => 'CreaProdotto']);
$router->add('Prodotti/CreaProdottoConferma', ['controller' => 'Prodotti', 'action' => 'CreaProdottoConferma']);
$router->add('Prodotti/ModificaProdotto/{id:\d+}', ['controller' => 'Prodotti', 'action' => 'ModificaProdotto']);
$router->add('Prodotti/ModificaProdottoConferma', ['controller' => 'Prodotti', 'action' => 'ModificaProdottoConferma']);
$router->add('Prodotti/listaProdottiAcquistati', ['controller' => 'Prodotti', 'action' => 'listaProdottiAcquistati']);
$router->add('Prodotti/ajaxProdottiAcquistati', ['controller' => 'Prodotti', 'action' => 'ajaxProdottiAcquistati']);
$router->add('Prodotti/ajaxImpostaMagliettaConsegnata', ['controller' => 'Prodotti', 'action' => 'ajaxImpostaMagliettaConsegnata']);
$router->add('Proodotti/ajaxConfermaConsegnaProdotti', ['controller' => 'Prodotti', 'action' => 'ajaxConfermaConsegnaProdotti']);

// Shares
$router->add('Share/CurrentRaceResults/{garaid:\d+}/{autoscroll:\d+}', ['controller' => 'Share', 'action' => 'CurrentRaceResults']);
$router->add('Share/CurrentRaceRelayResults/{garaid:\d+}/{autoscroll:\d+}', ['controller' => 'Share', 'action' => 'CurrentRaceRelayResults']);
$router->add('Share/SharedRaceResult/{token:\w+}', ['controller' => 'Share', 'action' => 'SharedRaceResult']);
$router->add('Share/SharedUserRaceResult/{risultatoid:\d+}', ['controller' => 'Share', 'action' => 'SharedUserRaceResult']);
$router->add('Share/ajaxGeneraCondivisioneRisultatoGara', ['controller' => 'Share', 'action' => 'ajaxGeneraCondivisioneRisultatoGara']);
    
$router->dispatch($_SERVER['QUERY_STRING']);