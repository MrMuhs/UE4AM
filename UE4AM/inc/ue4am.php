<?php

/*! \mainpage UE4AM - persistent crossplatform online data interface for UE4
 *
 * \section intro_sec Introduction
 * For the default use, you don't need the Data Structures Documentation Tab.
 * But if you want to enhance UE4AM or work with this framework, feel free to constribute and share with
 * the community!
 *
 * This is a very early documentation, you are welcome to work with UE4AM and enhance it to your needs,
 * please share your work you did with UE4AM
 *
 * \section install_sec Installation
 *
 * \subsection step1 Step 1: Copy
 *
 * copy the files to your favourite hosting provider, providing mysql and php support
 *
 * You only need to edit settings.php in the /inc/ sub directory!
 * For easy use, all SQL tables and data will be automatically generated by UE4AM
 *
 * \subsection step2 Step 2: Migrate UE4AM Blueprints from demo project to your project
 *
 * migrate UE4AM Blueprints from demo project to your project
 * \subsection step3 Step 3 : Build
 *
 * build dedicated Server by using unrealbuildtool and prepare your game mode, game instance, 
 * 
 * to handle the data when you need it.
 *
 * Don't forget to register the App in UE4AM and create a security token!
 *
 * (c) 2014 by Tim Koepsel from Seven-Mountains (www.seven-mountains.eu)
 */
 


/**
 * UE4AM (UE4 AccountManager)
 * 
 */
 /*! \brief UE4AM (UE4 AccountManager)
 * This is the main class  which handles all communication between database and clients 
 * 
 * You only need to call
 * Init();
 * AuthCMD();
 * 
 * to process and handle the whole thing
 */
class UE4AM
{

	function __construct() {

	    }
	    
	
	
	/*! The AuthCMD checks if appid and token fit together and then process the command provided by received json array */  
	function AuthCMD()
	{
	global $apps;
	global $database;
	global $accounts;
	global $json;
	global $log;
		
		// First we need to take our received array
		$received_jsonarray = $json->Receive();
		$log->AddLog("Json received");
		
		// And split it up a bit
		$appid = $received_jsonarray["appid"];
		$token = $received_jsonarray["token"];
		$command = $received_jsonarray["command"];
		
		$log->AddLog("Appid: ".$appid);
		$log->AddLog("Security Token: ".$token);
		
		if($apps->Auth($appid, $token) == true)
		{
		
			/*! Add your custom commands here which can be later accessed by blueprint */
			/*! Each command takes its own case */
			
			/*! NOT READY YET - TODO: add functions for all needed commands, see UE4AM_AccountHandler or UE4AM_DBHandler */
			switch ($command) {
			
				/*! Following values need to be send by blueprint: 
				string email, string username, string password */
				case "registeraccount":
				$accounts->Register($received_jsonarray["email"],$received_jsonarray["username"],$received_jsonarray["password"]);
				break; 
				
				/*! Following values need to be send by blueprint: 
				string username, string password */
				case "login":
				$accounts->Login($received_jsonarray["username"],$received_jsonarray["password"]);
				break;
				
				/*! Update the Account with new Data */
				case "updateaccount":
				
				break;
				
				/*! Sends a message through the message system, this is not live chat and person don't need to be online same time */
				case "sendmessage":
				
				break;
				
				/*! Saves the Character to the database */
				/*! This requires at least the characterid */
				case "savecharacter":
				
				break;
				
				case "execsql":
				$database->ExecSql($received_jsonarray["sql"]);
				$log->AddLog("SQL executed: ".$received_jsonarray["sql"]);
				break;
				
				/*! Sends a Ping to recognize user as connected user */
				case "sendping":
				$accounts->SendIPPing();
				break;
				
				/*! Logouts the User */
				case "logout":
				$accounts->Logout();
				break;
			}
			return true;
		} 
		else 
		{
		return false;
		}
		
	}
	
	/*! The Install function installs all necessary tables if needed*/  
	function InstallCheck()
	{
		global $prefix;
		global $database;
		
		if($this->IsInstalled($prefix."_apps")==false)
		{
		$tablename = $prefix."_apps";
		
		$sql_install_data = '
		CREATE TABLE `'.$prefix.'_apps` (
		`appid` int(255) NOT NULL COMMENT '."'the id of the ue4 game'".',
		  `token` varchar(255) NOT NULL COMMENT '."'the assigned security token'".',
		  `company` varchar(255) NOT NULL COMMENT '."'the company behind'".',
		  `contactemail` varchar(255) NOT NULL COMMENT '."'the contact email'".'
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='."'This table handles all apps by UE4AM_AppHandler'".';
		
		 ALTER TABLE `'.$tablename.'`
		   ADD PRIMARY KEY (`appid`);
		   
		 ALTER TABLE `'.$tablename.'`
		   MODIFY `appid` int(255) NOT NULL AUTO_INCREMENT COMMENT '."'the id of the ue4 game'".';
		 ';
		 
		 //echo $sql_install_data;
		 $database->ExecSql($sql_install_data);
		 echo "UE4AM Apps installed<br>";
		 }
		 
		 if($this->IsInstalled($prefix."_characters")==false)
		{
		$sql_install_data = '
		CREATE TABLE `'.$prefix.'_characters` (
		`characterid` int(255) NOT NULL COMMENT '."'The ID of the Character'".',
		  `userid` int(255) NOT NULL COMMENT '."'The associated User Account'".',
		  `appid` int(255) NOT NULL COMMENT '."'The associated AppID'".',
		  `CharacterName` varchar(255) NOT NULL COMMENT '."'The Name of the Character'".'
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='."'This is a base table for your game characters".';
		ALTER TABLE `'.$prefix.'_characters` 
		ADD PRIMARY KEY (`characterid`);
		 ALTER TABLE `'.$prefix.'_characters`
		 MODIFY `characterid` int(255) NOT NULL AUTO_INCREMENT COMMENT '."'The ID of the Character'".';
		';
		$database->ExecSql($sql_install_data);
		echo "UE4AM Characters installed";
		}
		
		if($this->IsInstalled($prefix."_messages")==false)
		{
		$sql_install_data = '
		
		CREATE TABLE `'.$prefix.'_messages` (
		`messageid` int(255) NOT NULL COMMENT '."'The Message ID'".',
		  `userid_sender` int(255) NOT NULL COMMENT '."'The Sender UID'".',
		  `userid_receiver` int(255) NOT NULL COMMENT '."'The Receiver UID'".',
		  `title` varchar(255) NOT NULL COMMENT '."'The Title of the Message'".',
		  `text` text NOT NULL COMMENT '."'The Text of the Message'".',
		  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '."'Timestamp when message has been sent'".',
		  `hasRead` tinyint(1) NOT NULL COMMENT '."'Boolean if message has been read'".'
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='."'Message Table for handling ingame messages (not chat)'".';
		ALTER TABLE `'.$prefix.'_messages`
		 ADD PRIMARY KEY (`messageid`);
		 ALTER TABLE `'.$prefix.'_messages`
		MODIFY `messageid` int(255) NOT NULL AUTO_INCREMENT COMMENT '."'The Message ID'".';
		 ';
		 $database->ExecSql($sql_install_data);
		 echo "UE4AM Messages installed";
		}
		
		if($this->IsInstalled($prefix."_servers")==false)
		{
		$sql_install_data = '
		CREATE TABLE `'.$prefix.'_servers` (
		`serverid` int(11) NOT NULL,
		  `appid` int(11) NOT NULL,
		  `servername` varchar(255) NOT NULL,
		  `ip` varchar(255) NOT NULL,
		  `port` int(11) NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		ALTER TABLE `'.$prefix.'_servers`
		 ADD PRIMARY KEY (`serverid`);
		 ALTER TABLE `'.$prefix.'_servers`
		MODIFY `serverid` int(11) NOT NULL AUTO_INCREMENT;
		';
		 $database->ExecSql($sql_install_data);
		 echo "UE4AM Servers installed";
		}
		
		if($this->IsInstalled($prefix."_users")==false)
		{
		$sql_install_data = '
		CREATE TABLE `'.$prefix.'_users` (
		`userid` int(255) NOT NULL COMMENT '."'The ID associated to the user'".',
		  `appid` int(255) NOT NULL COMMENT '."'The App ID this user account has been registered for'".',
		  `username` varchar(255) NOT NULL COMMENT '."'The name of the user'".',
		  `email` varchar(255) NOT NULL COMMENT '."'The contact E-Mail'".',
		  `password` varchar(255) NOT NULL COMMENT '."'The encrypted password'".',
		  `userlevel` int(11) DEFAULT NULL COMMENT '."'The Level of the user, default is 0'".',
		  `type` int(11) NOT NULL,
		  `regtimestamp` int(11) NOT NULL COMMENT '."'The Registration Timestamp'".',
		  `lastlogintimestamp` int(11) NOT NULL COMMENT '."'The Last Login Timestamp'".',
		  `lastpingtimestamp` int(11) NOT NULL COMMENT '."'The Last Ping Timestamp'".',
		  `lastlogouttimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `active` tinyint(1) NOT NULL COMMENT '."'Boolean if account is enabled/disabled or banned'".'
		) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='."'This table handles all user accounts - UE4AM_AccountHandler'".';
		ALTER TABLE `'.$prefix.'_users`
		 ADD PRIMARY KEY (`userid`);
		ALTER TABLE `'.$prefix.'_users`
		MODIFY `userid` int(255) NOT NULL AUTO_INCREMENT COMMENT '."'The ID associated to the user'".',AUTO_INCREMENT=2;
		';
		 $database->ExecSql($sql_install_data);
		 echo "UE4AM Users installed";
		}

	}
	


	/*! Checks if Database prerequisites are installed */  
	function IsInstalled($tablename)
	{
	/*! Check must be more detailed when the whole table structure is final in development */
		if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$tablename."'"))==1) 
		{
			return true; /*!< Returns true if prerequisites installed */  
		}
		else {
			return false;
		}
	}
	
	/*! This function should be called to send Data back to the Blueprint */
	/*! $data is a string containing the data encoded in json */
	
	function ExitWithData($data)
	{
		
	}


}

// important pointers as global vars
$ue4am 	= new UE4AM();
$log->AddLog("UE4AM init complete");


?>