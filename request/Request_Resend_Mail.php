<?php 
class Resend_Mail implements Request_Type
{
	/*
		RECEIVED: email
		Send failure:
			type=0
			email:
			{
				-2 = wtf exception(checkpost get empty key) / wtf exception(multiple rows)
				Stage 1:
				{
					2 = hackerman / editing the data
					0 = empty
					-1 = post doesn't exist
					
				}
				Stage 2:
				{
					6 = account is verified
					5 = account doesnt exist
					4 = the row of email verify doesnt exist
					3 = the waiting time has not ended
				}
			}
			timeleft=value (only on email=3)
		Send success:
			type=1
	*/
	public static function call(Database $db, ProcessingRequest $pRequest, Output $output, Log $log)
	{
		$emailField = "email";
		$response = $pRequest->check($emailField);                     // Verify if the email field exist
		if($response == 1)
		{
			$email = $_POST[$emailField];
			if(!Validator::mail($email))                               // Checking email
			{
				$log->Write("[null][i] Someone modify the Java Client Side and used this email {$email}");
				$output->sendError(array($emailField=>2));
			}

			$user = new User($db,$email);

			// Check if the email is not verified
			switch($user->getValidation())
			{
				case 0:
				{
					// Checking if the row exist in email verify, if exist will return an array with (verifyCode,deleteCode,lastMailSend)
					$arr = EmailVerify::verify($db,$email);

					if(is_array($arr))
					{
						list($verifyHash,$deleteHash,$lastMailSended) = $arr;
						if(strtotime($lastMailSended) + Config::$MailResend     <    time())
						{
							list($verifyUrl,$deleteUrl) = EmailVerify::hashesToUrls($email,array($verifyHash,$deleteHash)); // Get verify and delete url
							list($body,$altbody) = Messages::getMailMessage($email,$verifyUrl,$deleteUrl);                  // Get message for the mail

							$mail = new Mailer(new LogD($db,Config::$LogMailer),new PHPMailer\PHPMailer\PHPMailer(true));   // Create the mailer object
							$mail->addAddress($email);                                                                      // Add the address to the mailer									
							$mail->content("Confirm your email address",$body,$altbody);                                    // Put the title and the messages to the mailer
							$mail->send();                                                                                  // Send the mail

							EmailVerify::updateMailSend($db,$email);
							
							$output->add("type",1);

							// Send the response to the Client Side                                                                     
							$output->send();  
						}
						else                                                   // The waiting time has not ended so we output error
							$output->sendError(array($emailField=>3,"timeleft"=>((strtotime($lastMailSended) + Config::$MailResend)-time())));
					}
					else
						$output->sendError(array($emailField=>4));             // The row of email verify doesnt exist so we output error
					break;
				}
				case -1:
				{
					$output->sendError(array($emailField=>5));                 // The account doesnt exist so we output error
					break;
				}
				case 1:
				{
					$output->sendError(array($emailField=>6));                 // The account is already verified so we output error
					break;
				}
				default: // case 2
				{
					$output->sendError(array($emailField=>-2));                // wtf exception(multiple rows)
					break;
				}
			}
		}
		$output->sendError(array($emailField=>$response)); // We find some problems about one or many fields(empty/doesnt exist)
	}
}