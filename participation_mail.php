<?php
/*
A FAIRE :
    faire le formulaire contenant Participer et rajouter le champ en invisible de adresse mail contenant l'adresse mail de l'user
*/


if(isset($_POST['adressemail'])) {
    // si on a l'adresse email de l'utilisateur, on peut aller lui envoyer un email et générer un message
    // Sauvegarder ce message dans la base de données, et générer le QRCode corresponds. 
    // Ainsi, on peut lui envoyer un email de pariticpation avec son QRCode.


    /*
    * On génère le QR Code
    */
    //set it to writable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    
    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';

    include "qr/qrlib.php";  


    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);


    $MsgQRCODE = bin2hex(random_bytes(12));
    
    $data = $MsgQRCODE;
    $errorCorrectionLevel = 'H';
    $matrixPointSize = 8;

    $filename = $PNG_TEMP_DIR.'test.png';
    
    //processing form input
    //remember to sanitize user input in real-life solution !!!

        //it's very important!
        if ($data == '')
            die('data cannot be empty! <a href="?">back</a>');
            
        // user data
        $filename = $PNG_TEMP_DIR.'test'.md5($data.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    /*
    * FIN DE LA GENERATION DU QR CODE
    */


    require('connect.php');

       	// rentrer le msg du QR code en clair dans la base de données
		$reqInsertToken = $linkdpo->prepare("UPDATE Comptes SET pasQR = :msgqrcode WHERE adressemail = :adressemail");
		$reqInsertToken->execute(array('msgqrcode'=>$MsgQRCODE, 'adressemail'=>$adressemail ));
	

        	// Envoi du mail contenant le QR Code et qui confirme la participation
        
        	$expediteur = 'participation@eventy.com';
        	$headers  = 'MIME-Version: 1.0' . "\n"; // Version MIME
       		$headers .= 'Content-type: text/html; charset=ISO-8859-1'."\n"; // l'en-tete Content-type pour le format HTML
        	$headers .= 'Reply-To: '.$expediteur."\n"; // Mail de reponse
        	$headers .= 'From: "Eventy !"<'.$expediteur.'>'."\n"; // Expediteur
        	$headers .= 'Delivered-to: '.$destinataire."\n"; // Destinataire
	
        	$sujet = " Eventy - Vous venez de vous incrire pour participer à un évènement ";
        	$message ="Vous venez de vous inscrire à un évènement sur Eventy, voici votre code de participation (à garder précisuement): " . '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><hr/>';
    
    		if(mail($destinataire,$sujet,$message,$headers)) {
        	  	    // mail bien envoyé
                    // on dit que l'user participant dans la base de données
                    	$reqInsertToken = $linkdpo->prepare("UPDATE Comptes SET participant = :participant WHERE adressemail = :adressemail");
		                  $reqInsertToken->execute(array('participant'=>1,'adressemail'=>$adressemail ));
        		} else {
          		    // mail mal envoyé
          		    echo "error de l'envoie du mail";
        		}
    
    
		}
    	
}
?>



