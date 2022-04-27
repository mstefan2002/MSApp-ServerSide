<?php 
class Mailer
{
	private Log $log;
	private PHPMailer\PHPMailer\PHPMailer $mail;

	/**
	 * Construct
	 *
	 * @param Log $pLog
	 * @param PHPMailer\PHPMailer\PHPMailer $pMail
	 * 
	 */
	public function __construct(Log $pLog, PHPMailer\PHPMailer\PHPMailer $pMail)
	{
		$this->log = $pLog;
		$this->mail = $pMail;
		try
		{
			$this->mail->SMTPDebug = false;
			$this->mail->isSMTP();
			$this->mail->Host       = Config::$SMTPHost;
			$this->mail->SMTPAuth   = true;
			$this->mail->Username   = Config::$SMTPUser;
			$this->mail->Password   = Config::$SMTPPass;
			$this->mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
			$this->mail->Port       = Config::$SMTPPort;

			$this->mail->setFrom(Config::$SMTPUser, Config::$SMTPName);
			$this->mail->addReplyTo(Config::$SMTPUser, Config::$SMTPName);

			$this->mail->isHTML(true);
		}
		catch (PHPMailer\PHPMailer\Exception $e)
		{
			$this->log->Write("[".__METHOD__."][E] We catch an exception: {$this->mail->ErrorInfo}");
		}
	}

	/**
	 * Clear all the address from mailer
	 *
	 * @return void
	 * 
	 */
	public function clearAllRecipients() : void
	{
		$this->mail->clearAllRecipients();
	}

	/**
	 * Add an address to the mailer
	 *
	 * @param string $email
	 * 
	 * @return void
	 * 
	 */
	public function addAddress(string $email) : void
	{
		try
		{
			$this->mail->addAddress($email);
		}
		catch (Exception $e)
		{
			$this->log->Write("[".__METHOD__."][E] We catch an exception: {$this->mail->ErrorInfo}");
		}
	}

	/**
	 * Edit the content
	 *
	 * @param string $subject            The title of the mail
	 * @param string $body               The message in html format
	 * @param string $altbody            The message in text format, `default` is the same as `$body`
	 * 
	 * @return void
	 * 
	 */
	public function content(string $subject, string $body, string $altbody="") : void
	{
		$this->mail->Subject = $subject;
		$this->mail->Body    = $body;
		if(empty($altbody))
			$this->mail->AltBody = $body;
		else
			$this->mail->AltBody = $altbody;
	}

	/**
	 * Send the mail
	 *
	 * @return void
	 * 
	 */
	public function send() : void
	{
		try
		{
			$this->mail->send();
			$this->log->Write("[".__METHOD__."][D] We sended this mail: {$this->mail->Body}");
		}
		catch (Exception $e)
		{
			$this->log->Write("[".__METHOD__."][E] We catch an exception: {$this->mail->ErrorInfo}");
		}
	}
}
?>