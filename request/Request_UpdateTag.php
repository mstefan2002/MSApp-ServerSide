<?php 
class UpdateTag implements Request_Type
{
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
	public static function call(Database $db, ProcessingRequest $pRequest, Output $output, Log $log)
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
	}
}