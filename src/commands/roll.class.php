<?php
// create the customized command
require_once(dirname(__FILE__)."/../pfccommand.class.php");
class pfcCommand_roll extends pfcCommand
{
	function run(&$xml_reponse, $p)
	{
		$clientid    = $p["clientid"];
		$param       = $p["param"];
		$sender      = $p["sender"];
		$recipient   = $p["recipient"];
		$recipientid = $p["recipientid"];
		
		$c =& pfcGlobalConfig::Instance();
		
		$nick = $c->nick;
		$ct   =& pfcContainer::Instance();
		$text = trim($param);
		
		// Call parse roll
		require_once dirname(__FILE__).'/dice.class.php';
		$dice = new Dice();
		if (!$dice->check($text))
		{ 
			$result = $dice->error_get();
			$cmdp = $p;
			$cmdp["param"] = "Cmd_roll failed: " . $result;
			$cmd =& pfcCommand::Factory("error", $c);
			$cmd->run($xml_reponse, $cmdp);
		}
		else
		{
			$result = $dice->roll();
			$sender .= "(Rolling Dice)";
			$ct->write($recipient, $sender, "send", $result);
		}
	}
}

?>