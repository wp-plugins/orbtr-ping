<?php

class UserAgentParser
{
	protected $ua;
	
	public function __construct($ua)
	{
		$this->ua = $ua;	
	}
	
	/***
	 * Get Operating System
	 * Retrives the OS from the User-Agent string sent via the browser
	**/
	public function getOS() 
	{ 
	 	
		$user_agent = $this->ua;
		
		$os_platform    =   "Unknown OS Platform";
	 
		$os_array       =   array(
								'/windows nt 6.2/i'     =>  'Windows 8',
								'/windows nt 6.1/i'     =>  'Windows 7',
								'/windows nt 6.0/i'     =>  'Windows Vista',
								'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
								'/windows nt 5.1/i'     =>  'Windows XP',
								'/windows xp/i'         =>  'Windows XP',
								'/windows nt 5.0/i'     =>  'Windows 2000',
								'/windows me/i'         =>  'Windows ME',
								'/win98/i'              =>  'Windows 98',
								'/win95/i'              =>  'Windows 95',
								'/win16/i'              =>  'Windows 3.11',
								'/windows phone/i'		=>	'Windows Phone',
								'/macintosh|mac os x/i' =>  'Mac OS X',
								'/mac_powerpc/i'        =>  'Mac OS 9',
								'/linux/i'              =>  'Linux',
								'/ubuntu/i'             =>  'Ubuntu',
								'/iphone/i'             =>  'iPhone',
								'/ipod/i'               =>  'iPod',
								'/ipad/i'               =>  'iPad',
								'/android/i'            =>  'Android',
								'/blackberry/i'         =>  'BlackBerry',
								'/webos/i'              =>  'WebOS'
							);
	 
		foreach ($os_array as $regex => $value) { 
	 
			if (preg_match($regex, $user_agent)) {
				$os_platform    =   $value;
			}
	 
		}   
	 
		return $os_platform;
	 
	}
	
	/***
	 * Get Browser
	 * Retrieves the users browser from the User-Agent String sent via the browser.
	**/
	public function getBrowser() 
	{
	 	$user_agent = $this->ua;
		
		$browser        =   "Unknown Browser";
	 
		$browser_array  =   array(
								'/silk/i'				=>	'Silk',
								'/msie/i'       		=>  'MSIE',
								'/firefox/i'    		=>  'Firefox',
								'/(chrome)|(crios)/i'	=>  'Chrome',
								'/safari/i'				=>  'Safari',
								'/opera/i'      		=>  'Opera',
								'/netscape/i'   		=>  'Netscape',
								'/maxthon/i'    		=>  'Maxthon',
								'/konqueror/i'  		=>  'Konqueror',
								'/mobile/i'     		=>  'Handheld Browser'
							);
	 
		foreach ($browser_array as $regex => $value) { 
	 
			if (preg_match($regex, $user_agent)) {
				$browser    =   $value;
				break;
			}
	 
		}
		
		// finally get the correct version number
		$known = array('Version', $browser, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
		')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $user_agent, $matches)) {
			// we have no matching number just continue
		}
	   
		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($user_agent,"Version") < strripos($user_agent,$browser)){
				$version= $matches['version'][0];
			}
			else {
				$version= $matches['version'][1];
			}
		}
		else {
			$version= $matches['version'][0];
		}
	   
		// check if we have a number
		if ($version==null || $version=="") {$version="";}
	   
		return array(
			'name'      => $browser,
			'version'   => $version
		);
	 
		//return $browser;
	 
	} 
}