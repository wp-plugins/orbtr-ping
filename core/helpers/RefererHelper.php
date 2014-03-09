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
		if (eregi("www\.google",$url)) {
			preg_match("'(\?|&)q=(.*?)(&|$)'si", " $url ", $keywords);
			$search_engine = 'Google';
		}
		// AllTheWeb
		if (eregi("www\.alltheweb",$url)) {
			preg_match("'(\?|&)q=(.*?)(&|$)'si", " $url ", $keywords);
			$search_engine = 'AllTheWeb';
		}
		// MSN
		if (eregi("search\.msn",$url)) {
			preg_match("'(\?|&)q=(.*?)(&|$)'si", " $url ", $keywords);
			$search_engine = 'MSN';
		}
		// Bing
		if (eregi("www\.bing",$url)) {
			preg_match("'(\?|&)q=(.*?)(&|$)'si", " $url ", $keywords);
			$search_engine = 'Bing';
		}
		// Yahoo
		if ((eregi("yahoo\.com",$url)) or (eregi("search\.yahoo",$url))) {
			preg_match("'(\?|&)p=(.*?)(&|$)'si", " $url ", $keywords);
			$search_engine = 'Yahoo';
		}
		// Looksmart
		if (eregi("looksmart\.com",$url)) {
			preg_match("'(\?|&)qt=(.*?)(&|$)'si", " $url ", $keywords);
			$search_engine = 'Looksmart';
		}
		
		if (($keywords[2] != '') and ($keywords[2] != ' ')) {
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
		$host = $refer['host'];  
		  
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