<?php

namespace Brmsdi;

use \Rain\Tpl;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


require 'vendor/autoload.php';

class Mailer 
{

	private $mailer = "";
	private $password = "";
	private $mail;

	public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
	{

		$this->getMailerAndPassword();

		$config = array(
   			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/email",
    		"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
    		"debug"         => true, // set to false to improve the speed
		);

		Tpl::configure( $config );

		$tpl = new Tpl;

		foreach ($data as $key => $value) 
		{
			$tpl->assign($key, $value);
		}

		$html = $tpl->draw($tplName, true);
		

		$this->mail = new PHPMailer;

		//Tell PHPMailer to use SMTP
		$this->mail->isSMTP();

		//Enable SMTP debugging
		// SMTP::DEBUG_OFF = off (for production use)
		// SMTP::DEBUG_CLIENT = client messages
		// SMTP::DEBUG_SERVER = client and server messages
		$this->mail->SMTPDebug = SMTP::DEBUG_OFF;

		//Set the hostname of the mail server
		$this->mail->Host = 'smtp.gmail.com';
		// use
		// $this->mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6

		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$this->mail->Port = 587;

		//Set the encryption mechanism to use - STARTTLS or SMTPS
		$this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

		//Whether to use SMTP authentication
		$this->mail->SMTPAuth = true;

		//Username to use for SMTP authentication - use full email address for gmail
		$this->mail->Username = $this->mailer;

		//Password to use for SMTP authentication
		$this->mail->Password = $this->password;

		//Set who the message is to be sent from
		$this->mail->setFrom($this->mailer, 'BrMarques');

		//Set an alternative reply-to address
		$this->mail->addReplyTo($this->mailer, 'Marques');

		//Set who the message is to be sent to
		$this->mail->addAddress($toAddress, $toName);

		//Set the subject line
		$this->mail->Subject = $subject;

		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->msgHTML($html);

		//Replace the plain text body with one created manually
		$this->mail->AltBody = 'This is a plain-text message body';

		//Attach an image file
		$this->mail->addAttachment('images/phpmailer_mini.png');

		//send the message, check for errors
		/*
		if (!$this->mail->send()) {
		    echo 'Mailer Error: '. $this->mail->ErrorInfo;
		} else {
		    echo 'Message sent!';
		    //Section 2: IMAP
		    //Uncomment these to save your message in the 'Sent Mail' folder.
		    #if (save_mail($mail)) {
		    #    echo "Message saved!";
		    #}
		} */

		



		//Section 2: IMAP
		//IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
		//Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
		//You can use imap_getmailboxes($imapStream, '/imap/ssl', '*' ) to get a list of available folders or labels, this can
		//be useful if you are trying to get this working on a non-Gmail IMAP server.
		/*
		function save_mail($mail)
		{
		    //You can change 'Sent Mail' to any other folder or tag
		    $path = '{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail';

		    //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
		    $imapStream = imap_open($path, $this->mail->Username, $this->mail->Password);

		    $result = imap_append($imapStream, $path, $this->mail->getSentMIMEMessage());
		    imap_close($imapStream);

		    return $result;
		} */



	} // END CONSTRUCT 

	private function getMailerAndPassword()
	{
		$filename = $_SERVER["DOCUMENT_ROOT"] . 
			DIRECTORY_SEPARATOR .
			"em"
			. DIRECTORY_SEPARATOR
			. 
			"email.txt";
			

		if(file_exists($filename))
		{
			$file = fopen($filename, "r");

			$data = explode("|", fgets($file));

			
			$this->mailer = $data[0];
			$this->password = $data[1];	

			fclose($file);
		}

	}


	//send
	public function send()
	{
		return $this->mail->send();
	}

}


?>