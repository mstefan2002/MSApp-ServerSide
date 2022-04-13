<?php 

	/*
	* type = 1 (success)
	* type = 0 (error)
	* type = -1 (undefined)[a type was gived but we didnt find a match]
	* type = -2 (token is not valid)
	*/
	// Loading classes
	require_once("include/autoload.php");

	// Make the output object
	$output = new Output(new LogF(CVar::$LogOutput));

	// Make the main log address
	$log = new LogF();

	// We make a processing object to process the $_POST's
	$pRequest = new ProcessingRequest(new LogF(CVar::$LogProcReq), $output);

	// Check if the token exist and is valid
	if($pRequest->check("token") != 1)
	{	$output->sendError("Token doesn't exist!");	}
	elseif(!hash_equals($_POST["token"],CVar::$tokenApi))
	{
		$output->add("type",-2);
		$output->send();
	}

	// Connect to the database
	$db = new Database($log,new LogF(CVar::$LogQuery),$output);

	// Check if is any type of request
	if($pRequest->check("type") != 1)
	{	$output->sendError("Type doesn't exist!");	}

	$pRequest->protect("type");      // We encode $_POST['type'] to html special char
	$type = $_POST["type"];         // Transfer the value into $type

	switch($type)
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
		*/
		case "login": 
		{
			$arrPost = ["email","password"];                        // Create an array with the fields of the received $_POST
			$response = $pRequest->check($arrPost);                 // Verify them if they exist and they are not empty
			if(count($response) == 0)                               // If the response is an empty array then everything is ok
			{
				$fieldsPost = $pRequest->extractFields($arrPost);   // Extract the fields from $_POST
				extract($fieldsPost);                               // Turn fields to variable
				if(!Util::validMail($email))                        // Checking mail
				{
					$log->Write("Someone modify the Java Client Side and used this email {$email}");
					$output->sendError(array($arrPost[0]=>2));
				}
				if(!Util::validHash256($password))                  // Checking password
				{
					$log->Write("Someone modify the Java Client Side and used this password {$password}");
					$output->sendError(array($arrPost[1]=>2));
				}

				// Make the user object
				$user = new User($db, $email);

				// Checking the account
				switch($user->verify($password))            
				{
					case 2:	// wtf exception(multiple acc with the same email)
					{
						$LogsF->Write("We find many accounts with this email {$email}");
						$output->sendError(array($arrPost[0]=>4));
						break;
					}
					case 1: // everything is ok
					{
						$output->add("type",1);
						$output->send();
						break;
					}
					case -1:	$output->sendError(array($arrPost[0]=>3));break;   // account doesnt exist
					default:	$output->sendError(array($arrPost[1]=>3));break;   // 0=password doesnt match
				}
			}
			$output->sendError($response); // We find some problems about one or many fields(empty/doesnt exist)
			break;
		}
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
					-3 = wtf exception(error insert sql)
				}
		Send success:
			type=1
		*/
		case "register":
		{
			$arrPost = ["email","password","name"];                    // Create an array with the fields of the received $_POST
			$response = $pRequest->check($arrPost);                    // Verify them if they exist and they are not empty
			if(count($response) == 0)                                  // If the response is an empty array then everything is ok
			{
				$fieldsPost = $pRequest->extractFields($arrPost);      // Extract the fields from $_POST
				extract($fieldsPost);                                  // Turn fields to variable
				if(!Util::validMail($email))                           // Checking email
				{
					$log->Write("Someone modify the Java Client Side and used this email {$email}");
					$output->sendError(array($arrPost[0]=>2));
				}
				if(!Util::validHash256($password))                     // Checking password
				{
					$log->Write("Someone modify the Java Client Side and used this password {$password}");
					$output->sendError(array($arrPost[1]=>2));
				}
				if(!Util::validName($name))                            // Checking name
				{
					$log->Write("Someone modify the Java Client Side and used this name {$name}");
					$output->sendError(array($arrPost[2]=>2));
				}

				// Make the user object
				$user = new User($db,$email);

				// Checking if the account doesnt exist
				if($user->verify() == -1)
				{
					$user->add($email,$password,$name);                                                         // Register account

					list($verifyUrl,$deleteUrl) = EmailVerify::add($db,$email);                                 // Get verify url and delete url and insert them into database
					list($body,$altbody) = Lang::getMailMessage($email,$verifyUrl,$deleteUrl);                  // Get message for the mail

					$mail = new Mailer(new LogF(CVar::$LogMailer),new PHPMailer\PHPMailer\PHPMailer(true));     // Create the mailer object
					$mail->addAddress($email);                                                                  // Add the address to the mailer									
					$mail->content("Confirm your email address",$body,$altbody);                                // Put the title and the messages to the mailer
					$mail->send();                                                                              // Send the mail

					$output->add("type",1);                                                                     // Everything is ok
					$output->send();                                                                            // Send the response to the Client Side
				}
				$output->sendError(array($arrPost[0]=>3));             // Account exist so we output error
			}
			$output->sendError($response); // We find some problems about one or many fields(empty/doesnt exist)
			break;
		}
	}

	$output->add("type",-1); // We didnt find any request so we throw type = -1(undefined)
	$output->send();