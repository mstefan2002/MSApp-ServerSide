<?php 

	require_once("include/autoload.php");

	$log = new LogF(Config::$LogBgProc);
	$output = new Output(new LogF(Config::$LogOutput), null);

	$ip = Util::getUserIP();
	if($ip != Config::$IP_BgAPI)
	{
		$log->Write("{$ip} tried to access the page.");
		$output->sendHtmlError();
	}

	$temp = new Temper();
	if(!$temp->checkTimer("nextBackgroundCall"))
	{
		$log->Write("Server tried to load the page but the next timer is not ready.");
		$output->sendHtmlError();
	}
	// Connect to the database
	$db = new Database($log,$output);


	if($temp->checkTimer("checkExpiredEmailVerify"))
	{
		$temp->setTimer("checkExpiredEmailVerify",Config::$TimerEmailVerify);
		$arr = [];
		$arr = EmailVerify::checkExpired($db,null);
		if(count($arr) > 0)
		{
			foreach($arr as $email)
			{
				$log->Write("We find an account that doesnt have verification and the time run out: { email:{$email} }, we delete it.");
				Deleter::all($db,$email);
			}
		}
	}

	if($temp->checkTimer("checkExpiredSession"))
	{
		$temp->setTimer("checkExpiredSession",Config::$TimerSession);
		$arr = [];
		$arr = Session::checkExpired($db,null);
		if(count($arr) > 0)
		{
			foreach($arr as $email)
			{
				$log->Write("We find an expired session: { email:{$email} }, we delete it.");
				Deleter::session($db,$email);
			}
		}
	}