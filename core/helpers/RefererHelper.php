<?php

class RefererHelper
{
	protected $referer;
	
	public function __construct($referer)
	{
		$this->referer = $referer;	
	}
	
	public function getKeywords()  
	{   
		  
		$keywords = "";
		$search_engine='';
		
		$url = urldecode($this->referer);
		// Google
		if (stristr($url, "www\.google")) {
			preg_match("'(\?|&)q=(.*?)(&|$)'si", " $url ", $keywords);
			$search_engine = 'Google';
		}
		// AllTheWeb
		if (stristr($url, "www\.alltheweb")) {
			preg_match("'(\?|&)q=(.*?)(&|$)'si", " $url ", $keywords);
			$search_engine = 'AllTheWeb';
		}
		// MSN
		if (stristr($url, "search\.msn")) {
			preg_match("'(\?|&)q=(.*?)(&|$)'si", " $url ", $keywords);
			$search_engine = 'MSN';
		}
		// Bing
		if (stristr($url, "www\.bing")) {
			preg_match("'(\?|&)q=(.*?)(&|$)'si", " $url ", $keywords);
			$search_engine = 'Bing';
		}
		// Yahoo
		if ((stristr($url, "yahoo\.com")) or (stristr("search\.yahoo",$url))) {
			preg_match("'(\?|&)p=(.*?)(&|$)'si", " $url ", $keywords);
			$search_engine = 'Yahoo';
		}
		// Looksmart
		if (stristr($url, "looksmart\.com")) {
			preg_match("'(\?|&)qt=(.*?)(&|$)'si", " $url ", $keywords);
			$search_engine = 'Looksmart';
		}
		
       $keyword_array = array();
        
		if (isset($keywords[2]) && ($keywords[2] != '') && ($keywords[2] != ' ')) {
			$keywords = preg_replace('/"|\'/', '', $keywords[2]); // Remove quotes
			$keyword_array = preg_split("/[\s,\+\.]+/",$keywords); // Create keyword array
		}
		
		$j = (sizeof($keyword_array) > 10) ? 10 : sizeof($keyword_array);
		
		$keywords_list = '';
		
		if ($search_engine!='') {
			for ($i = 0; $i < $j; $i++) {
				$keywords_list .= $keyword_array[$i].' ';
			}	
			
			return array(
				'engine' => $search_engine,
				'keywords' => $keywords_list
			);
		}
		return '';
	}
	
	function getReferalHost()  
	{  
		if (empty($this->referer)) return 'Direct/Bookmark';
		
		$refer = parse_url($this->referer);  
		$host = strtolower($refer['host']);  
		  
		if(strstr($host,'google'))  
		{  
			return 'Google';  
		}  
		elseif(strstr($host,'yahoo'))  
		{  
			return 'Yahoo';  
		}  
		elseif(strstr($host,'msn'))  
		{  
			return 'MSN';  
		}
		elseif(strstr($host,'bing'))  
		{  
			return 'Bing';  
		} 
		elseif(strstr($host,'facebook'))  
		{  
			return 'Facebook';  
		} 
		elseif(strstr($host,'linkedin'))  
		{  
			return 'LinkedIn';  
		} 
		elseif(strstr($host,'twitter'))  
		{  
			return 'Twitter';  
		}
		elseif(!strstr($host, strtolower($_SERVER['HTTP_HOST'])))
		{
			return 'Backlink';
		}
		elseif(strstr($host, strtolower($_SERVER['HTTP_HOST'])))
		{
			return 'Internal Link';
		}
		else
		{
			return 'n/a';
		}
	} 
}