<?php 

	/*
	* Success = redirect back to app
	* Fail    = show 403 error(todo template with messages)
	*/
	// Loading classes
	require_once("include/autoload.php");

	// Make the output object
	$output = new Output(new LogF(CVar::$LogOutput));

	// Make the main log address
	$log = new LogF(CVar::$LogVerify);

	// We make a processing object to process the $_GET's
	$pRequest = new ProcessingRequest(new LogF(CVar::$LogProcReq), $output);

	// Check if the parms email and verifyCode/deleteCode exist
	$responseDelete = $pRequest->check("deleteCode",null,1);
	$responseVerify = $pRequest->check("verifyCode",null,1);

	if($pRequest->check("email",null,1) != 1||$responseDelete != 1 && $responseVerify != 1
	 ||$responseDelete == 1 && $responseVerify == 1)
	{
		$output->sendHtmlError();
	}

	$type=0;
	$arrGet = ["email"];
	if($responseDelete == 1)
	{
		$arrGet[] = "deleteCode";
		$type=1;
	}
	else
	{
		$arrGet[] = "verifyCode";
		$type=0;
	}

	$fieldsGET = $pRequest->extractFields($arrGet,1);               // Extract the fields from $_GET
	extract($fieldsGET);                                            // Turn fields to variable

	if(!Util::validMail($email))                                    // Checking email
	{
		$output->sendHtmlError();
	}
	if($type == 1 && !Util::validHash256($deleteCode)		        // Checking hashes
	|| $type == 0 && !Util::validHash256($verifyCode))                   								
	{
		$output->sendHtmlError();
	}

	$hash = "";
	if($type == 0)
		$hash = $verifyCode;
	else
		$hash = $deleteCode;

	// Connect to the database
	$db = new Database($log,new LogF(CVar::$LogQuery),$output);

	switch(EmailVerify::verify($db,$email,$hash,$type))
	{
		case 1:		// hash matches
		{
			$user = new User($db,$email);
			EmailVerify::remove($db,$email);
			if($type == 0)
				$user->setValidate();
			else
			{
				$user->remove();
			}
			break;
		}
	}
