<?php
/*
Plugin Name: PHPFreeChat
Plugin URI: http://frumph.net/
Description: A MultiUser Chat room using PHP and Ajax with PHPFreeChat http://phpfreechat.net/
Version: 0.2.8
Author: Philip M. Hofer (Frumph)
Author URI: http://frumph.net/

Copyright 2009 Philip M. Hofer (Frumph)  (email : philip@frumph.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

$phpchat_directory = dirname (__FILE__);

//instantiate the class
if (class_exists('phpfreechat_admin')) {
	$phpfreechat_admin = new phpfreechat_admin();
}

add_shortcode('phpfreechat','run_phpfreechat');

function run_phpfreechat() {
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
		echo '<span class="pfc_registered">You need to be a registered user to login to the chat.</span>';
	}
}

class widget_phpfreechat_who_online extends WP_Widget {
	
	function widget_phpfreechat_who_online() {
		$widget_ops = array('classname' => 'widget_phpfreechat_who_online', 'description' => 'Displays a list of users online the phpfreechat.' );
		$this->WP_Widget('phpfreechat_who_online', "PHPFreeChat Who Online", $widget_ops);
	}
	
	function widget($args, $instance) {
		global $post;
		extract($args, EXTR_SKIP); 
		require_once dirname(__FILE__)."/src/pfcinfo.class.php";
		$options = get_option('phpfreechat');
		$info  = new pfcInfo( $options['serverid'] );
		// NULL is used to get all the connected users, but you can specify
		// a channel name to get only the connected user on a specific channel
		$users = $info->getOnlineNick(NULL);
		$pfcerrors = $info->getErrors();
		$info = "";
		$nb_users = count($users);
		if ($nb_users == 1) { $nb_count = "User"; } else { $nb_count = "Users"; }
		echo $before_widget;
		$title = empty($instance['title']) ? $nb_users.' '.$nb_count." Online Chatting" : apply_filters('widget_title', $instance['title']); 
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }; 
		echo "<ul>";
		if ($nb_users == 0) {
			echo "<li>No one</li>"; 
		} else {
			foreach($users as $u)
			{
				echo "<li>".$u."</li>";
			}
		}
		echo "</ul>";
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<?php
	}
}

add_action( 'widgets_init', create_function('', 'return register_widget("widget_phpfreechat_who_online");') );

class widget_phpfreechat_latest_chatter extends WP_Widget {
	
	function widget_phpfreechat_latest_chatter() {
		$widget_ops = array('classname' => 'widget_phpfreechat_latest_chatter', 'description' => 'Displays a list the latest talk in the channel you specify.' );
		$this->WP_Widget('phpfreechat_latest_chatter', "PHPFreeChat Latest Chatter", $widget_ops);
	}
	
	function widget($args, $instance) {
		global $post;
		extract($args, EXTR_SKIP); 
		require_once dirname(__FILE__)."/src/pfcinfo.class.php";
		$options = get_option('phpfreechat');
		
		$info  = new pfcInfo( $options['serverid'] );
		
		if (!empty($instance['lines']) && (int)$instance['lines'] > 0) { 
			$numlines = (int)$instance['lines'];
		} else {
			$numlines = 10;
		}
		$lastmsg_raw = $info->getLastMsg($instance['channel'], $numlines);
		
		echo $before_widget;
		$title = empty($instance['title']) ? "Lastest Chat: ".$instance['channel'] : apply_filters('widget_title', $instance['title']); 
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }; 
		if (empty($instance['channel'])) {
			echo "<li>No Channel Selected in Widget.</li>";
			return;
		}
		echo "<ul>";
		
		if (empty($lastmsg_raw)) {
			echo "<li>No Messages Currently in Queue</li>"; 
		} else {
			if (is_array($lastmsg_raw)) {
				foreach($lastmsg_raw as $msg) {
					if (is_array($msg)) {
						foreach($msg as $output) {
							switch ($output['cmd']) {
								case 'notice': 
									// don't display notices
									break;
								case 'me':
									// don't display emotes
									break;
								default:
									$output_text = preg_replace('/\[color\=(.*)\](.*)\[\/color\]/i', '<span style="color: $1;">$2</span>', $output['param']);
									echo '<li>'.date('H:i:s',$output['timestamp']+get_option( 'gmt_offset' ) * 3600).' ['.$output['sender'].'] '.$output_text.'</li>';
									break;
							}
						}
					} 
				}
			} else {
				echo "<li>No Messages Currently in Queue</li>";
			}
		}
		echo "</ul>";
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['channel'] = strip_tags($new_instance['channel']);
		$instance['lines'] = strip_tags($new_instance['lines']);
		return $instance;
	}
	
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'channel' => 'General', 'lines' => '10' ) );
		$title = strip_tags($instance['title']);
		$channel = strip_tags($instance['channel']);
		$lines = strip_tags($instance['lines']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('channel'); ?>">Channel Name to watch: <input class="widefat" id="<?php echo $this->get_field_id('channel'); ?>" name="<?php echo $this->get_field_name('channel'); ?>" type="text" value="<?php echo esc_attr($channel); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('lines'); ?>">Display how many lines: <input class="widefat" id="<?php echo $this->get_field_id('lines'); ?>" name="<?php echo $this->get_field_name('lines'); ?>" type="text" value="<?php echo esc_attr($lines); ?>" /></label></p>
		
		<?php
	}
}

add_action( 'widgets_init', create_function('', 'return register_widget("widget_phpfreechat_latest_chatter");') );

// Install and Uninstall
register_activation_hook(__FILE__,'phpfreechat_install');
register_deactivation_hook(__FILE__,'phpfreechat_uninstall');

add_action('admin_menu', 'phpfreechat_admin_add_page');


function phpfreechat_install() {
	$options = array();
	$options = get_option('phpfreechat');
	if (empty($options['serverid'])) {
		$options['serverid'] = md5(get_bloginfo('url'));
		$options['chatname'] = 'My Chat';
		$options['channelname'] = 'General';
		$options['channelnametwo'] = '';
		$options['adminpassword'] = 'nostalgia';
		$options['clock'] = 0;
		$options['flood'] = 1;
		$options['ping'] = 0;
		$options['log'] = 1;
		$options['debug'] = 0;
		$options['censor'] = 0;
		$options['registered'] = 0;
		$options['method'] = 'mysql';
		$options['theme'] = 'zilveer';
		$options['language'] = 'en_US';
		add_option('phpfreechat', $options, '', 'yes');
	}
}

function phpfreechat_uninstall() {
	delete_option('phpfreechat');
}

// Add menu page
function phpfreechat_admin_add_page() {
	add_options_page('PHPFreeChat Settings', 'PHPFreeChat Settings', 'manage_options', 'phpfreechat_admin', 'phpfreechat_admin_do_page');
}

// Draw the menu page itself
function phpfreechat_admin_do_page() {
	global $phpchat_directory, $table_prefix;
	
	$options = get_option('phpfreechat');
	
	if ( wp_verify_nonce($_POST['_wpnonce'], 'update-options') ) {
		if ('phpfreechat_save_settings' == $_REQUEST['action'] ) {
			// Our first value is either 0 or 1
			$input = array();
			$input['clock'] = ( isset($_REQUEST['clock']) == 1 ? 1: 0 );
			$input['flood'] = ( isset($_REQUEST['flood']) == 1 ? 1 : 0 );
			
			$input['ping'] = ( isset($_REQUEST['ping']) == 1 ? 1: 0 );
			$input['debug'] = ( isset($_REQUEST['debug']) == 1 ? 1: 0 );
			$input['registered'] = ( isset($_REQUEST['registered']) == 1 ? 1: 0 );
			$input['censor'] = ( isset($_REQUEST['censor']) == 1 ? 1: 0 );
			$input['log'] = ( isset($_REQUEST['log']) == 1 ? 1: 0 );
			if (isset($_REQUEST['method']) && ($_REQUEST['method'] == 'mysql')) {
				$input['method'] = 'mysql';
			} else {
				$input['method'] = 'file';
			}
			// Say our second option must be safe text with no HTML tags
			$input['serverid'] =  wp_filter_nohtml_kses($_REQUEST['serverid']);
			$input['chatname'] =  wp_filter_nohtml_kses($_REQUEST['chatname']);
			$input['adminpassword'] =  wp_filter_nohtml_kses($_REQUEST['adminpassword']);
			$input['theme'] =  wp_filter_nohtml_kses($_REQUEST['theme']);
			$input['language'] =  wp_filter_nohtml_kses($_REQUEST['language']);
			update_option('phpfreechat',$input);
		}
		
	}
	
	?>
	<div style="clear:both;"></div>
	<div class="wrap">

		<h2>PHPFreeChat Settings</h2>
		<div class="stuffbox">
			<div class="inside">
			
				<form method="post" id="myForm" name="template">
				<?php wp_nonce_field('update-options') ?>
					<?php $options = get_option('phpfreechat'); 
						
						if (!is_array($options) || empty($options['serverid'])) {
							$options['serverid'] = md5(get_bloginfo('url'));
							$options['chatname'] = 'My Chat';
							$options['adminpassword'] = 'permagrass';
							$options['flood'] = 1;
							$options['ping'] = 0;
							$options['log'] = 1;
							$options['debug'] = 0;
							$options['clock'] = 0;
							$options['censor'] = 0;
							$options['registered'] = 0;
							$options['method'] = 'mysql';
							$options['theme'] = 'zilveer';
							$options['language'] = 'en_US';
							add_option('phpfreechat', $options, '', 'yes');
						}

					?>
					<table class="form-table">
						<tr><td scope="row" width="200"><h4>General Settings</h4></td>
							<td valign="top">Add [phpfreechat] to whatever page you want to use as your chat room.  When you change any of the settings you still need to login, identify yourself as an admin and use the /rehash command.<br /><br />If you want to hardcode phpfreechat into a template you can also do:   if (function_exists('run_phpfreechat')) run_phpfreechat(); </td>
						</tr>
						<tr><td valign="top"><strong>Server ID</strong><br /><br />The Server ID needs to be a very unique identifier especially if this is on a WPMU site.<br /></td>
							<td valign="top"><input type="text" name="serverid" value="<?php echo $options['serverid']; ?>" /></td>
						</tr>
						<tr><td valign="top"><strong>Chat Name</strong></td>
							<td valign="top"><input type="text" name="chatname" style="width: 300px;" value="<?php echo $options['chatname']; ?>" /></td>
						</tr>
						<tr><td valign="top"><strong>Admin Password</strong></td>
							<td valign="top"><input type="text" name="adminpassword" value="<?php echo $options['adminpassword']; ?>" /></td>
						</tr>
						<tr><td valign="top"><strong>Disable the timestamp on each chat message?</strong></td>
							<td valign="top"><input name="clock" type="checkbox" value="1" <?php checked('1', $options['clock']); ?> /></td>
						</tr>
						<tr><td valign="top"><strong>Turn off Flood Checking?</strong></td>
							<td valign="top"><input name="flood" type="checkbox" value="1" <?php checked('1', $options['flood']); ?> /></td>
						</tr>
						<tr><td valign="top"><strong>Turn ON Ping?</strong></td>
							<td valign="top"><input name="ping" type="checkbox" value="1" <?php checked('1', $options['ping']); ?> /></td>
						</tr>
						<tr><td valign="top"><strong>Turn ON Debug Mode?</strong></td>
							<td valign="top"><input name=debug" type="checkbox" value="1" <?php checked('1', $options['debug']); ?> /></td>
						</tr>
						<tr><td valign="top"><strong>Turn OFF the Censor Proxy?</strong></td>
							<td valign="top"><input name="censor" type="checkbox" value="1" <?php checked('1', $options['censor']); ?> /></td>
						</tr>
						<tr><td valign="top"><strong>Disable text logging of the chat?</strong><br /></td>
							<td valign="top"><input name="log" type="checkbox" value="1" <?php checked('1', $options['log']); ?> /><br />Text chat logs are stored in the plugins/phpfreechat/private/logs/serverid directory.</td>
						</tr>
						<tr><td valign="top"><strong>Registered Users Only?</strong></td>
							<td valign="top"><input name="registered" type="checkbox" value="1" <?php checked('1', $options['registered']); ?> /></td>
						</tr>
						<?php 
							$current_theme_directory = $options['theme'];
							if (empty($current_theme_directory))$current_theme_directory = 'default';					
							$theme_directories = glob($phpchat_directory . '/themes/*');
							
						?>
						<tr><td valign="top"><strong>Theme</td>
							<td valign="top">
								<label>
									<select name="theme">
							<?php
								foreach ($theme_directories as $theme) {
									if (is_dir($theme)) { 
										$theme_dir_name = basename($theme); ?>
										<option class="level-0" value="<?php echo $theme_dir_name; ?>" <?php if ($current_theme_directory == $theme_dir_name) { ?>selected="selected"<?php } ?>><?php echo $theme_dir_name; ?></option>
								<?php }
								}
							?>						
									</select>
								</label>
							</td>
						</tr>			
						<?php 
							$current_language_directory = $options['language'];
							if (empty($current_language_directory)) $current_language_directory = 'en_US';					
							$language_directories = glob($phpchat_directory . '/i18n/*');
							
						?>
						<tr><td valign="top"><strong>Language</strong></td>
							<td valign="top">
								<label>
									<select name="language">
							<?php
								foreach ($language_directories as $language) {
									if (is_dir($language)) { 
										$language_dir_name = basename($language); ?>
										<option class="level-0" value="<?php echo $language_dir_name; ?>" <?php if ($current_language_directory == $language_dir_name) { ?>selected="selected"<?php } ?>><?php echo $language_dir_name; ?></option>
								<?php }
								}
							?>						
									</select>
								</label>
							</td>
						</tr>
						<tr><td valign="top"><strong>PHPFreeChat Method</strong><br />Setting the MYSQL Option will take the proper information directly from wordpress installation and set the appropriate table name for both wpmu and wordpress (<?php echo $table_prefix; ?>)</td>
							<td valign="top">
								<input name="method" type="radio" value="text" <?php checked('text', $options['method']); ?> />Text w/Ajax<br />
								<input name="method" type="radio" value="mysql" <?php checked('mysql', $options['method']); ?> />Mysql w/Ajax<br />
								<br />
								This still requires the innodb engine in mysql to be installed if you use the mysql container option.<br />
								<br />
							</td>
						</tr>
					</table>
					<p class="submit" style="margin-left: 10px;">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
					<input type="hidden" name="action" value="phpfreechat_save_settings" />
					</p>
				</form>

			</div>
			<div style="float: left;">
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
						<input type="hidden" name="cmd" value="_s-xclick" />
						<input type="hidden" name="hosted_button_id" value="46RNWXBE7467Q" />
						<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online!" />
						<img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
					</form>

</div>
<div style="float: left;">Donate to help continue producing WordPress Plugins and support existing.<br />
</div>
<div style="clear: both;"></div>
		</div>
	</div>
	<?php	
}

?>