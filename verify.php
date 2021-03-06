<?php 

	/*
	* TODO:
	* Success = redirect back to app
	* Fail    = show 403 error/template with messages
	*/
	// Loading classes
	require_once("include/autoload.php");

	// Make the output object
	$output = new Output(new LogF(Config::$LogOutput), new Temper());

	// Connect to the database
	$db = new Database(new LogF(Config::$LogVerify),$output);

	// Change the log of output to db
	$output->changeLog(new LogD($db,Config::$LogOutput));

	// We make a processing object to process the $_GET's
	$pRequest = new ProcessingRequest(new LogD($db,Config::$LogProcReq), $output);

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

	if(!Validator::mail($email))                                    // Checking email
	{
		$output->sendHtmlResponse(Messages::getInvalidMailMessage());
	}
	if($type == 1 && !Validator::hash256($deleteCode)		        // Checking hashes
	|| $type == 0 && !Validator::hash256($verifyCode))                   								
	{
		$output->sendHtmlResponse(Messages::getInvalidHashMessage());
	}

	$hash = "";
	if($type == 0)
		$hash = $verifyCode;
	else
		$hash = $deleteCode;



	switch(EmailVerify::verify($db,$email,$hash,$type))
	{
		case 1:		// hash matches
		{
			$user = new User($db,$email);

			if($type == 0)  // Verify
			{
				$db->start_transaction();

				$user->setValidate();
				Deleter::emailVerification($db,$email);
				
				$db->end_transaction();

				$output->sendHtmlResponse(Messages::getSuccessValidationMessage());
			}
			else            // Delete
			{
				Deleter::all($db,$email);
				$output->sendHtmlResponse(Messages::getSuccessDeleteMessage());
			}
			break;
		}
		default:
		{
			$output->sendHtmlResponse(Messages::getVerifyInvalidMessage());
			break;
		}
	}
