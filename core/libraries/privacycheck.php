<?php

if (!class_exists('OrbtrPrivacyCheck')):

class OrbtrPrivacyCheck
{
	public static function check() 
	{
		global $wp_version;
		
		if (version_compare($wp_version, '3.5', '<')) 
		{
			$link = 'options-privacy.php';
		} 
		else
		{
			$link = 'options-reading.php';
		}
		
		if ( get_option('blog_public') == '0' ) 
		{
			echo "<div id='message' class='update-nag'><p>";
			echo "<strong>Privacy Warning: Your site is set to private. Search engines will not index this site.</strong>";
			echo " You must <a href='$link'>change your settings</a>";
			echo " for search engine visibility.";
			echo "</p></div>";
		}
	}
	
}

add_action('admin_notices', array('OrbtrPrivacyCheck', 'check'));

endif;