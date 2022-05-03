<?php

class Login implements Request_Type
{
	/*
		RECEIVED: email + password
		Send failure:
				type=0
				email:
				{
					4 = wtf exception(multiple acc with the same email)
					3 = account doesn't exist													 
					2 = hackerman / email invalid							        			 
					0 = email is empty								       						
					-1 = _post["email"] doesn't exist											
					-2 = wtf exception(checkpost get empty key)
				}
				password:
				{
					3 = password doesn't match
					2 = hackerman / password is not md5	
					0 = password is empty
					-1 = _post["password"] doesn't exist
					-2 = wtf exception(checkpost get empty key)
				}
		Send success:
				type=1
				session=id
	*/
	public static function call(Database $db, ProcessingRequest $pRequest, Output $output, Log $log)
	{
		$arrPost = ["email","password"];                        // Create an array with the fields of the received $_POST
		$response = $pRequest->check($arrPost);                 // Verify them if they exist and they are not empty
		if(count($response) == 0)                               // If the response is an empty array then everything is ok
		{
			$fieldsPost = $pRequest->extractFields($arrPost);   // Extract the fields from $_POST
			extract($fieldsPost);                               // Turn fields to variable
			if(!Validator::mail($email))                        // Checking mail
			{
				$log->Write("[null][i] Someone modify the Java Client Side and used this email {$email}");
				$output->sendError(array($arrPost[0]=>2));
			}
			if(!Validator::password($password))                 // Checking password
			{
				$log->Write("[null][i] Someone modify the Java Client Side and used this password {$password}");
				$output->sendError(array($arrPost[1]=>2));
			}

			// Create the user object
			$user = new User($db, $email);

			// Checking the account
			switch($user->verify($password))            
			{
				case 2:	// wtf exception(multiple acc with the same email)
				{
					$log->Write("[null][i] We find many accounts with this email {$email}");
					$output->sendError(array($arrPost[0]=>4));
					break;
				}
				case 1: // everything is ok
				{
					// we create a session
					$sessionID = Session::createSession($db,$email);

					$output->add("type",1);
					$output->add("session",$sessionID);
					$output->send();
					break;
				}
				case -1:	$output->sendError(array($arrPost[0]=>3));break;   // account doesnt exist
				default:	$output->sendError(array($arrPost[1]=>3));break;   // 0=password doesnt match
			}
		}
		$output->sendError($response); // We find some problems about one or many fields(empty/doesnt exist)
	}
}