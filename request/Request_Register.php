<?php 
class Register implements Request_Type
{
	/*
		RECEIVED: email + password + name
		Send failure:
			type=0
			Stage 1:
				email/password/name:
				{
					2 = hackerman / editing the data
					0 = empty
					-1 = post doesn't exist
					-2 = wtf exception(checkpost get empty key)
				}
			Stage 2:
				email:
				{
					3 = account exist
				}
		Send success:
			type=1
			session=id
	*/
	public static function call(Database $db, ProcessingRequest $pRequest, Output $output, Log $log)
	{
		$arrPost = ["email","password","name"];                    // Create an array with the fields of the received $_POST
		$response = $pRequest->check($arrPost);                    // Verify them if they exist and they are not empty
		if(count($response) == 0)                                  // If the response is an empty array then everything is ok
		{
			$fieldsPost = $pRequest->extractFields($arrPost);      // Extract the fields from $_POST
			extract($fieldsPost);                                  // Turn fields to variable
			if(!Validator::mail($email))                           // Checking email
			{
				$log->Write("[null][i] Someone modify the Java Client Side and used this email {$email}");
				$output->sendError(array($arrPost[0]=>2));
			}
			if(!Validator::hash256($password))                     // Checking password
			{
				$log->Write("[null][i] Someone modify the Java Client Side and used this password {$password}");
				$output->sendError(array($arrPost[1]=>2));
			}
			if(!Validator::name($name))                            // Checking name
			{
				$log->Write("[null][i] Someone modify the Java Client Side and used this name {$name}");
				$output->sendError(array($arrPost[2]=>2));
			}

			// Create the user object
			$user = new User($db,$email);

			// Check if the account does not exist
			if($user->verify() == -1)
			{
				$db->start_transaction();

				$user->add($password,$name);                                                                    // Register account

				$arr = EmailVerify::add($db,$email);                                                            // Get verify and delete hash and insert them into database
				list($verifyUrl,$deleteUrl) = EmailVerify::hashesToUrls($email,$arr);                           // Get verify and delete url
				list($body,$altbody) = Messages::getMailMessage($email,$verifyUrl,$deleteUrl);                  // Get message for the mail

				$sessionID = Session::createSession($db,$email);                                                // Create the session

				$db->end_transaction();

				$mail = new Mailer(new LogD($db,Config::$LogMailer),new PHPMailer\PHPMailer\PHPMailer(true));   // Create the mailer object
				$mail->addAddress($email);                                                                      // Add the address to the mailer									
				$mail->content("Confirm your email address",$body,$altbody);                                    // Put the title and the messages to the mailer
				$mail->send();                                                                                  // Send the mail

				$output->add("type",1);
				$output->add("session",$sessionID);

				// Send the response to the Client Side                                                                     
				$output->send();                                                                            
			}
			$output->sendError(array($arrPost[0]=>3));             // Account exist so we output error
		}
		$output->sendError($response); // We find some problems about one or many fields(empty/doesnt exist)
	}
}