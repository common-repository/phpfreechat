=== PHPFreeChat ===
Contributors: Philip M. Hofer (Frumph)
Tags: plugin, chat, wpmu, chatroom, fun, ajax, php
Requires at least: 2.8
Tested up to: 3.1
Stable tag: 0.2.8
Donate link: http://frumph.net/
Author URI: http://frumph.net/

This plugin creates a text based ajax chat page on your site for users to interact with each other.

== Description ==

* Based on [PHPFreeChat.NET](http://phpfreechat.net/), version 1.3

PHPFreeChat is a free, simple to install, fast, customizable and multi languages chat that uses a simple filesystem for 
message and nickname storage. It uses AJAX to smoothly refresh (no flicker) and display the chat zone and the 
nickname zone. It supports multi-rooms (/join), private messages, moderation (/kick, /ban), dice rolling and more!

PHPFreeChat is all contained on your server with no extra daemons or connections to other hosts.

Includes 2 widgets, showing who is online and the latest chatter going on in a chat channel you choose.

Warning: This is untested with quite a few hosts, it uses sessions and mysql-innodb, ctype-* functions and flock.

* Once activated add a page in wordpress and put [phpfreechat] in it. 
* Go to the dashboard -> settings -> phpfreechat settings and change settings, save.
* Remember to save the settings once and all of the settings are appropriate.
* To refresh your settings after making changes, change your name to `admin` with the command `/nick admin` and type `/identify <password>` the password being the one you 
set in options, then type `/help` to get a list of commands, `/rehash` resets all the settings to what was changed in the dashboard administration.
* If you use wordpress - phpfreechat please send me a message on [Twitter](http://twitter.com/frumph/)

More hosts are now supported

== Changelog == 

= 0.2.8 =
Bug fix I introduced in 0.2.7 for the registered users

= 0.2.7 =
Adjusted some of the css to allow automatic width settings
fixed the error with subdirectory installations where it wouldn't load properly

= 0.2.4 =
Fixed/hopefully the timezone issue, i'm sending the wordpress timezone offset to the phpfreechat now.

= 0.2.3 =
Integrated version 1.3 of phpfreechat, some issue still exists with the rehash but overall looks good.

= 0.2.2 = 
Fixed the log disable button in the settings.

= 0.2.1 = 
Defaults to mysql now and doesnt *stop* because the database is already created, since we're taking from the wordpress database it's already created anyways.

= 0.2.0 = 
Rewrote how the disable proxies work, rewrote the admin screen to use the old method.  Setting chat end on close of browser to 
false now and letting the time-out handle it, otherwise if someone just switchs browser windows it will declare inactive and time them
out all the time giving the timeout messages.  Enabled logging of chat and ability to disable it from logging.  shownotice is now set to 1 for
non-admins and 7 for those who login as admin (not op'd)  This way the annoying timeout messages are not displayed, but are to the admin. 

= 0.1.9 =
Added Censor removal toggle.

= 0.1.8 = 
Proper use of the MySQL Container for WordPress and WPMU for creating tables per blog.

= 0.1.7 =
Registered Users Only Option, fixed the /me command when switching Server ID's, Debug option in admin, couple other minor things.

= 0.1.6 =
Added selectable language packs in the options and 'if current_user_can' will auto-set the person to administrator, important update where if you
set it to the mysql type connection it will no longer create a file-log file releasing alot of file writes on your hosting.

= 0.1.5 =
Quick hotfix to fix the mkdir_r renaming it to pfc_mkdir_r command to avoid conflict.

= 0.1.4 =
Used svn sourceforge repository phpfreechat, used patch to avoid flock, added forcenick but forcenick isn't exactly working yet.  Important fix
using the mysql will no longer do any text based reading and writing increasing your performance.

= 0.1.3 = 

Fix for the Session.start error.

= 0.1.2 =
Fixed pfc_mkdir_r conflicts by renaming pfc_mkdir_r in this plugin to phpfreechat_pfc_mkdir_r and all the addressing of it.

= 0.1.1 =
Fixed SVN's problems with uploading.

= 0.1.0 =
* First release, works with Wordpress and WPMU ComicPress theme installations.

== Installation ==

1. Upload the `phpfreechat` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to Settings -> PHPFreeChat Settings and set our options once.
1. Create a page on your site and embed the shortcode [phpfreechat] inside the page.

== Frequently Asked Questions ==

* This is not fully tested on all hosting/server types.

* where `is_page('chat')` is the page your phpfreechat resides on, rename the word `chat` for your page name.

= Page doesnt come up, or stops with the loading box. =

* This is generally due to the page being cached, with wp-supercache and the like you can specify which pages dont get cached, this is one of them. =
	
= Warning: session_start() [function.session-start]: Cannot send session cache limiter - headers already sent  =

* This means that your there is a session problem usually another plugin is already creating the sessions, contact me to further debug, [WebComic Planet Forums](http://webcomicplanet.com/forum/)

= Uh, what are the requirements of my web host? =

* Same as the standalone phpfreechat - [REquired COnfiguration](http://www.phpfreechat.net/required-config)

= I'm having another problem. =

* Check the [PHPFreeChat Forums](http://phpfreechat.net/).  

Make sure you're running the most recent stable version of Wordpress - PHPFreeChat, 
as there are a lot of critical bug fixes between versions.

If it's a serious problem, such as WordPress becoming non-functional or blank screens/errors after you perform certain 
operations, you will be asked to provide error logs for your server.  If you don't know how to get access to these, 
talk with your Webhost.  At that time just remove the plugin from the plugins/ directory.  If it still
continue's it is probably not a PHPFreeChat problem.

== License ==

WordPress PHPFreeChat is released under the GNU GPL version 2.0 or later.



