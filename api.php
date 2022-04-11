<?php 
	// Testing Time
	$GLOBALS['time_start'] = microtime(true);

	// Loading classes
	require_once("include/autoload.php");

	$output = new Output(new LogF(CVar::$LogOutput));

	$log = new LogF();

	$db = new Database($log,new LogF(CVar::$LogQuery),$output);

	if(!isset($_POST["type"])||empty($_POST["type"]))
		$output->sendError("Type doesn't exist!");


	$pString = new ProcessingPOST(new LogF(CVar::$LogProcPOST));
	$pString->protectPOST("type");

	$type = $_POST["type"];

	$user = new User($db);

	switch($type)
	{
		// RECEIVED: email + password
		// Send failure:
		// 	  email: 4 = wtf exception(multiple acc with the same email)			password:  	3 = password doesn't match
		//		 3 = account doesn't exist								2 = hackerman / password is not md5	
		//		 2 = hackerman / email invalid							        0 = password is empty
		//		 0 = email is empty								       -1 = _post["password"] doesn't exist
		//		-1 = _post["email"] doesn't exist						       -2 = wtf exception(checkpost get empty key)
		//		-2 = wtf exception(checkpost get empty key)
		// Send success:
		// 	type=1
		case "login": 
		{
			$arrPost = ["email","password"];
			$response = $pString->checkPOSTS($arrPost);
			if(count($response) == 0)
			{
				list($arrPost[0]=>$email,$arrPost[1]=>$password) = $_POST;
				if(!Util::validMail($email))
				{
					$log->Write("Someone modify the Java Client Side and used this email {$email}");
					$output->sendError(array($arrPost[0]=>2));
				}
				if(!Util::validPassword($password))
				{
					$log->Write("Someone modify the Java Client Side and used this password {$password}");
					$output->sendError(array($arrPost[1]=>2));
				}
				switch($user->verify($email,$password))
				{
					case 2:
					{
						$LogsF->Write("We find many accounts with this email {$email}");
						$output->sendError(array($arrPost[0]=>4));
						break;
					}
					case 1:
					{
						$output->add("type",1);
						$output->send();
						break;
					}
					case -1:	$output->sendError(array($arrPost[0]=>3));break;
					default:	$output->sendError(array($arrPost[1]=>3));break;
				}
			}
			$output->sendError($response);
			break;
		}
		// RECEIVED: email + password + name
		// Send failure:
		//    Stage 1:
		// 	email/password/name 	: 2 = hackerman / editing the date
		//				  0 = empty
		//				 -1 = post doesn't exist
		//				 -2 = wtf exception(checkpost get empty key)
		//    Stage 2:
		//	email			: 3 = account exist
		//				 -3 = wtf exception(error insert sql)
		// Send success:
		// 	type=1

		case "register":
		{
			$arrPost = ["email","password","name"];
			$response = $pString->checkPOSTS($arrPost);
			if(count($response) == 0)
			{
				list($arrPost[0]=>$email,$arrPost[1]=>$password,$arrPost[2]=>$name) = $_POST;
				if(!Util::validMail($email))
				{
					$log->Write("Someone modify the Java Client Side and used this email {$email}");
					$output->sendError(array($arrPost[0]=>2));
				}
				if(!Util::validPassword($password))
				{
					$log->Write("Someone modify the Java Client Side and used this password {$password}");
					$output->sendError(array($arrPost[1]=>2));
				}
				if(!Util::validName($name))
				{
					$log->Write("Someone modify the Java Client Side and used this name {$name}");
					$output->sendError(array($arrPost[2]=>2));
				}

				if($user->verify($email) == -1)
				{
					list($verifyUrl,$deleteUrl) = $user->add($email,$password,$name);
					list($body,$altbody) = Lang::getMailMessage($email,$verifyUrl,$deleteUrl);

					$mail = new Mailer(new LogF(CVar::$LogMailer),new PHPMailer\PHPMailer\PHPMailer(true));
					$mail->addAddress($email);
					$mail->content("Confirm your email address",$body,$altbody);
					$mail->send();

					$output->add("type",1);
					$output->send();
				}
				$output->sendError(array($arrPost[0]=>3));
			}
			$output->sendError($response);
			break;
		}
	}

	$output->add("type",-1);
	$output->send();