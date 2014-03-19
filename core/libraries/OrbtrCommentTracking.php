<?php

class ORBTRCommentTracking
{
	public static function commentHandler($id, $comment)
	{
		if (!isset($comment) && empty($comment)) return;
		
		$all_values = array(
			'name' => $comment->comment_author,
			'email' => $comment->comment_author_email
		);
		
		$data = array();
		
		foreach ($all_values as $temp => $value)
		{
			$the_label = strtolower($temp);
			
			$label = '';
			
			if ((strpos($the_label, 'first') !== false || ( strpos($the_label,"name") !== false && strpos($the_label,"first") !== false))) {
				$label = 'sFirstName'; 
			} else if (( strpos( $the_label,"last") !== false || ( strpos( $the_label,"name") !== false && strpos($the_label,"last") !== false) )) {
				$label = 'sLastName';
			} else if ( strpos( $the_label,"name") !== false) {
				$label = 'sBothNames';
			} else if ( strpos( $the_label,"email") !== false || strpos( $the_label,"e-mail") !== false) {
				$label = 'sEmail';
			}
			
			if ($label == 'sBothNames') {
				$names = explode(" ", $value);
				$data['sFirstName'] = $names[0];
				$data['sLastName'] = $names[1];
			} else if ($label == 'sEmail') {
				$data[$label] = $value; // allow more than one email or phone
			} else {
				$data[$label] = $value ;
			}
		}
		
		foreach($data as $key => $value) {
			if(is_array($value)) {
				foreach($value as $k => $v) {
					if(!is_array($v)) {
						$value[$k] = trim(rtrim($v));
					}
				}
				$data[$key] = $value;
			} else {
				$data[$key] = trim(rtrim($value));
			}
		}
		
		$fields = array(
			'email' => $data['sEmail'],
			'fName' => $data['sFirstName'],
			'lName' => $data['sLastName']
		);
	   
	   //var_dump($fields);
	   self::create_tracking($fields);
	}
	
	public static function create_tracking($data)
	{
		if (empty($data['email'])) return;
		
		//set POST variables
		$url = ORBTR_TRACKING_URL;
		
		$settings = new Orbtr_SettingsModel('orbtrping_settings');
		
		$fields = array(
			'lName'=>urlencode($data['lName']),
			'fName'=>urlencode($data['fName']),
			'oemail'=>urlencode($data['email']),
			'action'=>'leaddata',
			'uid' => $_COOKIE['ping_uid'],
			'aid'=> $settings->getSetting('orbtr_account_id')
		);
		
		foreach($fields as $key=>$value) 
		{ 
			$fields_string .= $key.'='.$value.'&'; 
		}
		
		rtrim($fields_string,'&');
		
		//open connection
		$ch = curl_init();
		
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$url.'?'.$fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, '3');
		
		//execute post
		$result = curl_exec($ch);
		
		//close connection
		curl_close($ch);
		
	}
}