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
		case "login": 
		{
			Request::login($db,$pRequest,$output,$log);
			break;
		}

		case "register":
		{
			Request::register($db,$pRequest,$output,$log);
			break;
		}

		case "resend_mail":
		{
			Request::resend_mail($db,$pRequest,$output,$log);
			break;
		}

		case "updateTag":
		{
			Request::updateTag($db,$pRequest,$output,$log);
			break;
		}
	}

	$output->add("type",-1); // We didnt find any request so we throw type = -1(undefined)
	$output->send();