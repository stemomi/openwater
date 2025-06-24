<?php

namespace App\Models;

use PDO;
use PHPMailer;
use DateTime;
use DateTimeZone;
use stdClass;
use \App\Models\User;

class FunctionsModel extends \Core\Model
{
    public function sendEmail($to,$bcc,$subject,$body,$attachment = null, $sender_account = "info@dnami.com", $sender_password = "*Mavicpro18*")
    {
        $mail = new PHPMailer(true);
        $mail->IsSMTP(true);                            // Uso l'autenticazione SMTP
        $mail->IsHTML(true);                            // Dichiaro che è un'e-mail HTML
        $mail->CharSet = 'UTF-8';                       // Set di Caratteri
        $mail->SMTPAuth = true;                         // Abilito l'autenticazione SMTP

        $mail->Host = 'pro.eu.turbo-smtp.com';          // Server SMTP
        $mail->Username = $sender_account;              // Nome utente SMTP autenticato
        $mail->Password = $sender_password;             // Password account email con SMTP autenticato
        $mail->SMTPSecure = 'TLS';                      // TLS / SSL
        $mail->Port = 587;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->SMTPDebug = 0;                                                           // Informazioni per il DEBUG (1=solo errori, 2=tutti i messaggi, 0=niente)
        $mail->Priority = 3;                                                            // Priorità
        $mail->SetFrom('info@italianopenwatertour.com','ItalianOpenWaterTour');         // Mittente
        $mail->addReplyTo('info@italianopenwatertour.com', 'ItalianOpenWaterTour');     // Rispondi a

        $mail->AddAddress($to);                                                         // Destinatario
        if ($bcc!='') $mail->addBCC($bcc);

        if (isset($attachment))
        {
            foreach($attachment as $file)
            {
                $mail->addAttachment($file,'new.pdf');                                  // Optional name
            }
        }
        
        $mail->Subject = $subject;                                                      // Oggetto del messaggio
        $mail->Body = $body;                                                            // Body
        //$mail->Send();
        //$mail = NULL;

        // Manda solo se non in locale
        if ($_SERVER['HTTP_HOST'] != 'localhost')
        {
            if(!$mail->Send())
                return $mail->ErrorInfo;
            else 
                return true;
        }
        else
            return true; 
    }

    public static function checkUploadFile($name, $tmp_name)
    {
        if ($tmp_name)
        {
            if (preg_match('/(htaccess|\.php|\.html|\.pl|\.py|\.jsp|\.asp|\.htm|\.shtml|\.sh|\.cgi|\.bak)/i', $name)) // Estensione pericolosa
                $Errore= "Formato file non valido!";
            else
                $Errore= "";
        }
        else
            $Errore= "";

        return $Errore;
    }

    function ConvertiDataPaypal($date)
    {
        // PAYPAL DATE FORMAT IS HH:mm:ss Jan DD, YYYY PST
        // We want to convert that to Unixtime stamp, then do the timeshift from PST to EST 

        $hours = substr($date, 0,2);
        $mins = substr($date, 3,2);
        $secs = substr($date, 6,2);
        $monthword = strtoupper(substr($date, 9,3));
        $monthnames =array('JAN'=>'01','FEB'=>'02','MAR'=>'03','APR'=>'04','MAY'=>'05','JUN'=>'06','JUL'=>'07','AUG'=>'08','SEP'=>'09','OCT'=>'10','NOV'=>'11','DEC'=>'12');
        $monthnum = $monthnames[$monthword];

        $day = substr($date, 13,2);
        $year = substr($date, 17,4);

        $phpdate = mktime($hours,$mins,$secs,$monthnum,$day,$year);

        // PAYPAL TIMESTAMPS ARE IN PST (or PDT). SO, THE PHPDATE WE JUST CALCULATED IS OFF BY SEVERAL HOURS.
        // TO FIND THE TIME IN EST(or EDT), WE RECALC THE DATE BY 
        // 1. ADDING THE UNIX TIMESTAME TO THE EPOCH START
        // 2.TAKE 5 HOURS OFF FOR GMT
        // 3. THEN ADD 3 FOR PST->EST <<<< Change THIS part for your timezone

        $phpdate = strtotime ("1 Jan 1970 + $phpdate seconds +9 hours");
        return date('Y-m-d H:i:s',$phpdate);
    }

    function RicreaPNG($nome_file_tmp, $width = 200, $height = 200)
    {
        $size = getimagesize( $nome_file_tmp );
        $src = imagecreatefromstring(file_get_contents($nome_file_tmp));
        $dst = imagecreatetruecolor( $width, $height );

        // Per trasparenza
        imagesavealpha($dst, true);
        imagealphablending($dst, false);
        imagefill($dst,0,0,0x7fff0000);
        
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1] );
        imagepng($dst, $nome_file_tmp);
    }

    function RicreaJPG($nome_file_tmp, $width = 200, $height = 200)
    {
        $size = getimagesize( $nome_file_tmp );
        $src = imagecreatefromstring(file_get_contents($nome_file_tmp));
        $dst = imagecreatetruecolor( $width, $height );
        
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1] );
        imagejpeg($dst, $nome_file_tmp);
    }

    public function RemoveBomUtf8($string)
    {
      if( substr($string,0,3) == chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF')) )
           return substr($string,3);
       else
           return $string;
    }
}

?>
