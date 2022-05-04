# MSApp-ServerSide
MySocialsApp is an application where you can put all your social addresses, your favorite movies, your playlist and much more.

List of params(api.php):<br/>
token -> used to check the version of the ap(you can change it at include/Config.php)<br/>
type  -> [register](https://github.com/mstefan2002/MSApp-ServerSide/blob/main/request/Request_Register.php) (email,password,name)
```
               - on success will return type=1 and the sessionID
               - on fail
               		- Stage 1(verify the params):
               			- email/password/name
               				- 2  = the field is not validate by the include/Validator.php
               				- 0  = empty
               				- -1 = the field doesnt exist
               				- -2 = internal problems
               		- Stage 2:
               			- email:
               				- 3 = the email is used by another account
```

  ``   ``-> [login](https://github.com/mstefan2002/MSApp-ServerSide/blob/main/request/Request_Login.php) (email,password)
```
               - on success will return type=1 and the sessionID
               - on fail
               		- Stage 1(verify the params):
               			- email/password
                              		- 2  = the field is not validate by the include/Validator.php
                              		- 0  = empty
                              		- -1 = the field doesnt exist
                              		- -2 = internal problems
               		- Stage 2:
                              	- email:
                              		- 4 = multiple acc with the same email
                              		- 3 = the account doesnt exist
                              	- password:
                              		- 3 = password doesnt match
```
  ``   ``-> [resend_mail](https://github.com/mstefan2002/MSApp-ServerSide/blob/main/request/Request_Resend_Mail.php) (email)
```
               - on success will return type=1
               - on fail
               		- Stage 1(verify the params):
               			- email
                              		- 2  = the field is not validate by the include/Validator.php
                              		- 0  = empty
                              		- -1 = the field doesnt exist
                              		- -2 = internal problems
               		- Stage 2:
                              	- email:
                              		- 6 = account is verified
                              		- 5 = account doesnt exist
                              		- 4 = the row of email verify doesnt exist
                              		- 3 = the waiting time has not ended
				- timeleft=value (only on email=3)
```
  ``   ``-> [updateTag](https://github.com/mstefan2002/MSApp-ServerSide/blob/main/request/Request_UpdateTag.php) (email,sesID,tag) 
```
               - on success will return type=1
               - on fail
               		- Stage 1(verify the params):
               			- email/sesID/tag
                              		- 2  = the field is not validate by the include/Validator.php
                              		- 0  = empty
                              		- -1 = the field doesnt exist
                              		- -2 = internal problems
               		- Stage 2:
                              	- email:
                              		- 3 = account doesnt exist
                              	- sesID
                              		- 4 = hash doesnt match
                              		- 3 = session doesnt exist
                              		- -2 = many rows
                              	- tag:
                              		- 3 = the tag is taken
```


You can configure the script at include/Config.php


