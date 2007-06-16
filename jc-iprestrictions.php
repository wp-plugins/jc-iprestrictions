<?php
/*
Plugin Name: JC-IPRestrictions
Plugin URI: http://www.joshcook.net/programming/wordpress/jc-iprestrictions/
Description: Provides the ability to restrict access to your web site by IP address and/or IP network.
Version: 1.1
Author: Joshua R. Cook
Author URI: http://www.joshcook.net/
*/

/*
---
JC-IPRestrictions

Copyright 2007 Joshua R. Cook (http://www.joshcook.net/)
Released under the GNU General Public License (http://www.gnu.org/licenses/gpl.html)

* For information on using JC-IPRestrictions, please reference the readme.txt file
* Visit www.joshcook.net for general information or to submit feature and support requests
---
*/

//Defaults that will probably never be changed
//- Delimiter Character
//- Comment Character
//- Timestamp Formatting
$jc_ipr_v_DelimiterChar	= '|';
$jc_ipr_v_CommentChar		= '*';
$jc_ipr_v_DateFormat		= 'D M j G:i:s T Y';

function jc_ipr_f_IsSameNetwork($ip1, $ip2, $mask)
{
	if ($mask == '') $mask = '255.255.255.255';
	
	$masked1 = ip2long($ip1) & ip2long($mask);
	$masked2 = ip2long($ip2) & ip2long($mask);
	
	if ($masked1 == $masked2)
	{
		return true;	
	}
	else
	{
		return false;
	}	
}

function jc_ipr_f_IsBlockedDay($blocked_day_list)
{
	if (strpos($blocked_day_list, date('w')) !== False)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function jc_ipr_f_IsBetweenTime($time_begin, $time_end, $time_test)
{
	$time_begin	= strtotime($time_begin);
	$time_end	= strtotime($time_end);
	
	if ($time_begin == -1) $time_begin = strtotime('00:00');
	if ($time_end == -1) $time_end = strtotime('23:59');
	
	if (($time_begin <= $time_test) && ($time_end >= $time_test))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function jc_ipr_f_GenerateDailyPassword($pass_len = 21, $pass_num = true, $pass_alpha = true, $pass_mc = true, $pass_exclude = '') 
{ 
	$salt = ''; 
	if ($pass_alpha)
	{
		$salt .= 'abcdefghijklmnopqrstuvwxyz'; 
	  if ($pass_mc)
	  { 
			$salt .= strtoupper($salt); 
		} 
	} 

	if ($pass_num)
	{
		$salt .= '0123456789'; 
	}

	if ($pass_exclude)
	{
		$exclude = array_unique(preg_split('//', $pass_exclude)); 
		$salt = str_replace($exclude, '', $salt); 
	}
	$salt_len = strlen($salt); 

	mt_srand ((int) date('y')*date('z')*($salt_len+$pass_len)); 

	$pass = ''; 
	for ($i=0; $i<$pass_len; $i++)
	{ 
		$pass .= substr($salt, mt_rand() % $salt_len, 1); 
	}
	
	return $pass; 
}

function jc_ipr_f_cleanbothquotes($tmpNeedsCleaning)
{
	// How sloppy are these next two lines...
	$replacethis	= array('/\\\\"/', "/\\\\'/");
	$replacethat	= array('"', "'");
	$tmpNeedsCleaning = preg_replace($replacethis, $replacethat, $tmpNeedsCleaning);
	return $tmpNeedsCleaning;
}

function jc_ipr_f_GetDomainName($tmpUrl)
{
	preg_match('@^(?:http://)?([^/]+)@i',$tmpUrl, $matches);
	$host = $matches[1];
	preg_match('/[^.]+\.[^.]+$/', $host, $usHost);
	
	if (strlen($usHost[0])>5)
	{
		return $usHost[0];
	}
	else
	{
		preg_match('/[^.]+\.[^.]+\.[^.]+$/', $host, $restofWorldHost);
		return $restofWorldHost[0];
	}
}

function jc_ipr_f_DefaultRestrictedMessage()
{
	return include 'default-restricted-message.php';
}

function jc_ipr_f_RestrictProcess()
{
	global $jc_ipr_v_DelimiterChar, $jc_ipr_v_CommentChar, $jc_ipr_v_DateFormat;
	
	if ($_COOKIE['jc_ipr_c_RestrictIP'] == jc_ipr_f_GenerateDailyPassword()) return;
	
	if (get_option('jc_ipr_v_RestrictMode') != 1) return;
	
	$ClientIP	= $_SERVER['REMOTE_ADDR'];
	$TestTime	= Time();
	$BlockIP	= False;
	
	$ipr_struct = (array)explode('<br />', nl2br(get_option('jc_ipr_v_IPRestrictions')));

	$ipr_tmp1 = array();
	$ipr_tmp2 = array();
	$ipr_item = 0;

	foreach ($ipr_struct as $ipr)
	{
		if ((trim($ipr) != '') && (substr($ipr, 0, 1) != $jc_ipr_v_CommentChar))
		{
			$ipr_tmp1 = (array)explode($jc_ipr_v_DelimiterChar, trim($ipr));
			
			foreach ($ipr_tmp1 as $ipr1)
			{
				list($k, $v) = (array)explode('=', trim($ipr1), 2);	
				$ipr_tmp2[$ipr_item][strtolower($k)] = $v;
			}			
		}
		$ipr_item++;
	}
	$ipr_struct = $ipr_tmp2;
	
	foreach ($ipr_struct as $ipr)
	{
		$tmpNet			= $ipr['net'];
		$tmpMask		= $ipr['mask'];
		$tmpDay			= $ipr['day'];
		$tmpBegin		= $ipr['begin'];
		$tmpEnd			= $ipr['end'];
		$tmpMessage	= $ipr['message'];
		$tmpPasswd	= $ipr['password'];
		
		$tmpIsSameNetwork = jc_ipr_f_IsSameNetwork($ClientIP, $tmpNet, $tmpMask);
		$tmpIsBlockedDay	= ($tmpDay != Null) ? jc_ipr_f_IsBlockedDay($tmpDay) : true;
		$tmpIsBetweenTime = (($tmpBegin != Null) && ($tmpEnd != Null)) ? jc_ipr_f_IsBetweenTime($tmpBegin, $tmpEnd, $TestTime) : true;
		
		if ($tmpIsSameNetwork && $tmpIsBlockedDay && $tmpIsBetweenTime)
		{
			$BlockIP = True;
			break;
		}
	}
	
	if ((!$BlockIP) || ((isset($_POST['jc_ipr_FormBypassPassword'])) && ($tmpPasswd != NULL) && ($_POST['jc_ipr_FormBypassPassword'] == $tmpPasswd)))
	{
		setcookie('jc_ipr_c_RestrictIP', jc_ipr_f_GenerateDailyPassword(), 0, '/', '.' . jc_ipr_f_GetDomainName(get_option('siteurl')));
		return;
	}
	
	$jc_ipr_v_RestrictedMessage	= get_option('jc_ipr_v_RestrictedMessage');
	
	$jc_ipr_v_RestrictedMessage = preg_replace('/\[MESSAGE\]/i', $tmpMessage, $jc_ipr_v_RestrictedMessage);
	$jc_ipr_v_RestrictedMessage = preg_replace('/\[NET\]/i', $tmpNet, $jc_ipr_v_RestrictedMessage);
	$jc_ipr_v_RestrictedMessage = preg_replace('/\[MASK\]/i', $tmpMask, $jc_ipr_v_RestrictedMessage);
	$jc_ipr_v_RestrictedMessage = preg_replace('/\[BEGIN\]/i', $tmpBegin, $jc_ipr_v_RestrictedMessage);
	$jc_ipr_v_RestrictedMessage = preg_replace('/\[END\]/i', $tmpEnd, $jc_ipr_v_RestrictedMessage);
	$jc_ipr_v_RestrictedMessage = preg_replace('/\[TIMESTAMP\]/i', date($jc_ipr_v_DateFormat) , $jc_ipr_v_RestrictedMessage);
	$jc_ipr_v_RestrictedMessage = jc_ipr_f_cleanbothquotes($jc_ipr_v_RestrictedMessage);
	
	if ($tmpPasswd != Null) $jc_ipr_v_RestrictedMessage = preg_replace('/\[FORM\]/i', '<form action="" method="POST"><input type="password" name="jc_ipr_FormBypassPassword" id="jc_ipr_css_input"></form>', $jc_ipr_v_RestrictedMessage);
	
	die($jc_ipr_v_RestrictedMessage);
}

function jc_ipr_f_DisplayManagePage()
{
	if (isset($_POST['submit']))
	{
		check_admin_referer('jc_ipr__options_' . $your_object); 
		if (function_exists('current_user_can') && !current_user_can('manage_options')) die(__('Cheatin&#8217; uh?'));
		
		$jc_ipr_v_RestrictMode			= $_POST['jc_ipr_v_RestrictMode'];
		$jc_ipr_v_IPRestrictions		= $_POST['jc_ipr_v_IPRestrictions'];
		$jc_ipr_v_RestrictedMessage	= $_POST['jc_ipr_v_RestrictedMessage'];
		
		if ($_POST['jc_ipr_v_RestoreMessage'] == 'restoredefaultmessage')
		{
			$jc_ipr_v_RestrictedMessage = jc_ipr_f_DefaultRestrictedMessage();
		}
		
		update_option('jc_ipr_v_RestrictMode',			$jc_ipr_v_RestrictMode);
		update_option('jc_ipr_v_IPRestrictions',		$jc_ipr_v_IPRestrictions);
		update_option('jc_ipr_v_RestrictedMessage',	$jc_ipr_v_RestrictedMessage);
		
		echo '<div id="message" class="updated fade"><p><strong>Options saved.</strong></p></div>';		
	}
	else
	{
		$jc_ipr_v_RestrictMode			= get_option('jc_ipr_v_RestrictMode');
		$jc_ipr_v_IPRestrictions		= get_option('jc_ipr_v_IPRestrictions');
		$jc_ipr_v_RestrictedMessage	= get_option('jc_ipr_v_RestrictedMessage');
	}
?>
<script>
<!--
function popUp(URL, tmpWidth, tmpHeight) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=" + tmpWidth + ",height=" + tmpHeight + "');");
}
-->
</script>
<div class="wrap">
<h2>IP Restrictions</h2>
<form action="" method="post" name="jc_ipr_form">
<?php if (function_exists('wp_nonce_field')) wp_nonce_field('jc_ipr__options_' . $your_object); ?>
<h4>Mode</h4>
<label><input type="radio" name="jc_ipr_v_RestrictMode" value="0"<?php if ($jc_ipr_v_RestrictMode == '0') echo ' checked="checked"'; ?> /> No Restrictions</label>
<br />
<label><input type="radio" name="jc_ipr_v_RestrictMode" value="1"<?php if ($jc_ipr_v_RestrictMode == '1') echo ' checked="checked"'; ?> /> Actively Restricting</label>
<h4>IP Restrictions</h4>
<textarea wrap="off" name="jc_ipr_v_IPRestrictions" style="width: 95%; height: 100px;"><?php echo jc_ipr_f_cleanbothquotes($jc_ipr_v_IPRestrictions) ?></textarea>
<br />
Note: Keep in mind the difference in timezones between your remote server and your local workstation - 
Server: <?php echo date('H:i'); ?> / Local: 
<script type="text/javascript">
<!--
var currentTime = new Date()
var hours = currentTime.getHours()
var minutes = currentTime.getMinutes()
if (hours < 10)
hours = "0" + hours
if (minutes < 10)
minutes = "0" + minutes
document.write(hours + ":" + minutes + " ")
//-->
</script>
<h4>Restricted Message</h4>
<textarea wrap="off" name="jc_ipr_v_RestrictedMessage" style="width: 95%; height: 200px;"><?php echo jc_ipr_f_cleanbothquotes($jc_ipr_v_RestrictedMessage) ?></textarea>
<br />
<label><input type="checkbox" name="jc_ipr_v_RestoreMessage" value="restoredefaultmessage"> Restore Default Message</label>
<p class="submit"><input type="button" name="abutton" value="Preview Message" onclick="javascript:popUp('<?php echo get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/preview.php'; ?>', 800, 600)" /> <input type="submit" name="submit" value="<?php _e('Update options &raquo;'); ?>" /></p>
</form>
</div>
<div class="wrap">
<h2>About</h2>
<div style="float:left;text-align:left;margin-right:100px;">
<p>For information on using JC-IPRestrictions, please reference the online <a href="javascript:popUp('<?php echo get_option('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/readme.txt'; ?>', 800, 600)">documentation</a>.
<br />
Visit <a href="http://www.joshcook.net/">www.joshcook.net</a> for general information or to submit feature and support requests.</p>
<p>JC-IPRestrictions, Copyright &copy; 2007 Joshua R. Cook</p>
</div>
<div style="float:right;text-align:right;margin-left:-100px;width:100px;">
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
"></form>
</div>
<div style="clear:both;">
<p>JC-IPRestrictions is licensed under the <a href="http://www.gnu.org/licenses/gpl.html">GNU GPL</a>. JC-IPRestrictions comes with ABSOLUTELY NO WARRANTY. This is free software, and you are welcome to redistribute it under certain conditions. See the <a href="http://www.gnu.org/licenses/gpl.html">license</a> for details.</p>
</div>
</div>
<?php
}

add_action('activate_jc-iprestrictions/jc-iprestrictions.php', 'jc_ipr_f_PluginActivate');
function jc_ipr_f_PluginActivate()
{
	update_option('jc_ipr_v_RestrictMode', 0);
	
	if (get_option('jc_ipr_v_RestrictedMessage') == '')
	{
		update_option('jc_ipr_v_RestrictedMessage',	jc_ipr_f_DefaultRestrictedMessage());
	}
	
}

add_action('admin_menu', 'jc_ipr_f_AddManagePage');
function jc_ipr_f_AddManagePage()
{
	add_management_page('IP Restrictions', 'IP Restrictions', 10, basename(__FILE__), 'jc_ipr_f_DisplayManagePage');
}

add_action('init', 'jc_ipr_f_RestrictProcess');
?>
