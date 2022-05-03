<?php 

	/*
	* type = 1 (success)
	* type = 0 (error)
	* type = -1 (undefined)[a type was given, but we couldn't find a match]
	* type = -2 (the token is invalid)
	*/
	// Loading classes
	require_once("include/autoload.php");

	// Create the output object
	$output = new Output(new LogF(Config::$LogOutput), new Temper());

	// Connect to the database
	$db = new Database(new LogF(),$output);

	// Change the log of output to db
	$output->changeLog(new LogD($db,Config::$LogOutput));

	// Create a processing object to process the $_POST's
	$pRequest = new ProcessingRequest(new LogD($db,Config::$LogProcReq), $output);

	// Check if the token exists and is valid
	if($pRequest->check("token") != 1)
	{	$output->sendError("Token doesn't exist!");	}
	elseif(!hash_equals($_POST["token"],Config::$tokenApi))
	{
		$output->add("type",-2);
		$output->send();
	}

	// Create the log object
	$log = new LogD($db);

	// Check for any type of request
	if($pRequest->check("type") != 1)
	{	$output->sendError("Type doesn't exist!");	}

	$pRequest->protect("type");     // We encode $_POST['type'] to html special char
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
				session=id
		*/
		case "login": 
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
				if(!Validator::hash256($password))                  // Checking password
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
				}
		Send success:
			type=1
			session=id
		*/
		case "register":
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
			break;
		}
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
		case "resend_mail":
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
			break;
		}

		/*
		RECEIVED: email + sesID + tag
		Send failure:
			type=0
			Stage 1:
				email/sesID/tag:
				{
					2 = hackerman / editing the data
					0 = empty
					-1 = post doesn't exist
					-2 = wtf exception(checkpost get empty key)
				}
			Stage 2:
				email:
				{
					3 = account doesnt exist
				}
				sesID
				{
					4 = hash doesnt match
					3 = session doesnt exist
					-2 = wtf exception(many rows)
				}
				tag:
				{
					3 = the tag is taken
				}
		Send success:
			type=1
			session=id
		*/
		case "updateTag":
		{
			$arrPost = ["email","sesID","tag"];                        // Create an array with the fields of the received $_POST
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
				if(!Validator::hash256($sesID))                        // Checking session id
				{
					$log->Write("[null][i] Someone modify the Java Client Side and used this sessionID {$sesID}");
					$output->sendError(array($arrPost[1]=>2));
				}
				if(!Validator::tag($tag))                              // Checking tag
				{
					$log->Write("[null][i] Someone modify the Java Client Side and used this tag {$tag}");
					$output->sendError(array($arrPost[2]=>2));
				}

				// Make the user object
				$user = new User($db,$email);

				// Check if the account exist
				if($user->verify() == -2)
				{
					// Check if the session exist and match with received session
					switch(Session::verify($db,$email,$sesID))
					{
						case 1:
						{
							if($user->verifyTag($tag) == 0)
							{
								// Allow the script to run even if we get error 1062(MYSQLI_CODE_DUPLICATE_KEY)
								// Because the race condition may happend
								$db->catchErrorCode(array(1062));
								$user->setTag($tag);
								if($db->get_errorCode() == 1062)
									$output->sendError(array($arrPost[2]=>3));         // The tag is been taken by another user(race condition) so we output error

								$output->add("type",1);                                                                  
								$output->send();  
							}
							else
								$output->sendError(array($arrPost[2]=>3));             // The tag is already taken so we output error
							break;
						}
						case -1:
						{
							$output->sendError(array($arrPost[1]=>3));                 // The account doesnt have any session so we output error
							break;
						}
						case 0:
						{
							$output->sendError(array($arrPost[1]=>4));                 // The sesID doesnt match with the sesID in the db so we output error
							break;
						}
						default:  // case 2
						{
							$output->sendError(array($arrPost[1]=>-2));                // wtf exception(multiple rows)
							break;
						}
					}
				}
				$output->sendError(array($arrPost[0]=>3));             // Account doesnt exist so we output error
			}
			$output->sendError($response); // We find some problems about one or many fields(empty/doesnt exist)
			break;
		}
	}

	$output->add("type",-1); // We didnt find any request so we throw type = -1(undefined)
	$output->send();