<?php
@require_once dirname(__FILE__)."/src/phpfreechat.class.php";
require_once('../../../wp-load.php');
	global $phpchat_directory, $current_user, $table_prefix;
	get_currentuserinfo();
	$options = get_option('phpfreechat');
	if (!empty($current_user) && isset($current_user->display_name)) {
		$user_name = addslashes($current_user->display_name);
	}
	if ( (!isset($options['registered']) || empty($options['registered'])) || ( (isset($options['registered']) || $options['registered']) && !empty($user_name)) ) {
		@require_once($phpchat_directory.'/src/phpfreechat.class.php');
		$params = array();	
		/* PARAMETERS */
		//		$params["nick"] = "guest".rand(1,1000);  // setup the intitial nickname
		$params["title"] = $options['chatname'];
		if (!empty($user_name))
			$params["nick"] = $user_name;
		$params["isadmin"] = false; // do not use it on production servers ;)
		if ( current_user_can('manage_options') ) $params['isadmin'] = true;
		$params["serverid"] = $options['serverid']; // calculate a unique id for this chat
		// setup urls
		$params["data_public_url"]   = site_url()."/wp-content/plugins/phpfreechat/data/public";
		$params["server_script_url"] = site_url()."/wp-content/plugins/phpfreechat/chat.php";
		$params["theme_default_url"] = site_url()."/wp-content/plugins/phpfreechat/themes";
		$params["theme"] = $options['theme'];
		// admins
		$params['admins'] = array('admin'  => $options['adminpassword']);
		// setup paths
		if ($options['method'] == 'mysql') {
			$params["container_type"] = "mysql";
			$params["container_cfg_mysql_host"]     = DB_HOST;							// default value is "localhost"
			$params["container_cfg_mysql_port"]     = '3306';							// default value is 3306
			$params["container_cfg_mysql_database"] = DB_NAME;							// default value is "phpfreechat"
			$params["container_cfg_mysql_table"]    = $table_prefix . 'phpfreechat';	// default value is "phpfreechat"
			$params["container_cfg_mysql_username"] = DB_USER;							// default value is "root"
			$params["container_cfg_mysql_password"] = DB_PASSWORD;						// default value is ""
		} else {
			$params["container_type"]         = "file";
			$params["container_cfg_chat_dir"] = dirname(__FILE__)."/data/private/chat";
		}
		if (empty($options['language'])) $options['language'] = 'en_US';
		$params["language"] = $options['language'];
		$params["channels"] = array('General');
		$params["quit_on_closedwindow"] = false;
		//		$params['shownotice'] = 1;
		$params['startwithsound'] = false;
		//		if ( current_user_can('manage_options') ) $params['shownotice'] = 7;
		if ($options['ping'] == '1') { $params['display_ping'] = true; } else { $params['display_ping'] = false; }
		
		if (isset($options['debug']) && $options['debug']) { $params['debug'] = true; } else { $params['debug'] = false; }
		if (isset($options['clock']) && $options['clock']) { $params['clock'] = false; } else { $params['clock'] = true; }
		
		$skip_proxies = array();
		
		if (isset($options['flood']) && ($options['flood'] == '1')) $skip_proxies[] = 'noflood';
		if (isset($options['censor']) && ($options['censor'] == '1')) $skip_proxies[] = 'censor';
		if (isset($options['log']) && ($options['log'] == '1')) $skip_proxies[] = 'log';
		
		if (isset($params['skip_proxies']))
			$params['skip_proxies'] = $skip_proxies;
		
		
		$params['short_url'] = false;
		$params['showsmileys'] = true;
		
		$params['time_offset'] = get_option('gmt_offset') * 3600;
		
		$chat = new phpFreeChat( $params );
		$chat->printChat();
	} else {
		echo '<span class="pfc_registered">You need to be a registered user to login to the Chat!</span>';
	}
?>