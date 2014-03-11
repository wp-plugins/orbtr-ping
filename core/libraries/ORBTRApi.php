<?php

class Orbtr_Exception extends Exception
{
	const CONNECTION_FAIL = 0;
	const NOT_MODIFIED = 304; 
	const BAD_REQUEST = 400; 
	const UNAUTHORIZED = 401;
	const NOT_FOUND = 404; 
	const NOT_ALOWED = 405; 
	const CONFLICT = 409; 
	const PRECONDITION_FAILED = 412; 
	const INTERNAL_ERROR = 500; 
}

class OrbtrAPI
{
	/**
	 * Location of the ORBTR API Service
	 */
	protected $_serviceURL = 'ping.orbtr.net/v2';
	
	/**
     * Cache the user api_key so we only have to log in once per client instantiation
     */
	protected $_apiKey;
	
	/**
	 * Connect using a secure connection
	 */
	protected $_secure;
	
	/**
     * Cache the user account so we only have to log in once per client instantiation
     */
	protected $_account;
	
	/**
	 * Toggle debug messages for development.
	 */
	protected $_debug;
	
	/**
	 * Cache the connection protocol used to connect to the ORBTR API Service
	 */
	protected $_protocol;
	
	/**
	 * HTTP protocol
	 */
	const HTTP  = 'http';
	
	/**
	 * HTTPS (secure) protocol
	 */
	const HTTPS = 'https';
	
	/**
	 * HTTP post method
	 */
	const POST   = 'POST';
	
	/**
	 * HTTP get method
	 */
	const GET    = 'GET';
	
	/**
	 * HTTP delete method
	 */
	const DELETE = 'DELETE';
	
	/**
	 * HTTP OK successful response code
	 */
	const HTTP_OK = 200;
	
	/**
	 * HTTP Object Created successful response code
	 */
	const HTTP_CREATED = 201;
	
	/**
	 * HTTP Accepted successful response code
	 */
	const HTTP_ACEPTED = 202;
	
	/**
	 * HTTP UnAuthorized response code
	 */
	const UNAUTHORIZED = 401;
	
	/**
	 * Connect to the ORBTR API.
	 * 
	 * @param string $apiKey Your ORBTR apikey
	 * @param string $account Your ORBTR account ID
	 * @param bool $secure Whether or not this should use a secure connection
	 * @param bool $debug Whether or not this should display debug messages
	 */
	public function __construct( $apiKey, $account, $secure=false, $debug=false )
	{
		$this->_secure = $secure;
		$this->_apiKey = $apiKey;
		$this->_account = $account;
		$this->_debug = $debug;
		$this->_protocol = ($this->_secure ? self::HTTPS : self::HTTP);
		
	}
	
	/**
	 * Get Visitors/Leads Currently Online
	 *
	 * @return object All online leads
	 */
	public function getOnline()
	{
		return $this->doGet('leads/onlinenow');
	}
	
	/**
	 * Get Assigned Orbits for a Visitor/Lead
	 *
	 * @param int $id ID of the visitor/lead
	 * @param int $limit Number of results to return (-1 for all)
	 *
	 * @return object Orbits
	 */
	public function getAssignedOrbits($id, $limit=5)
	{
		return $this->doGet('orbits/assigned/'.$id, array('limit' => $limit));	
	}
	
	/**
	 * Get all leads filtered by optional search criteria
	 *
	 * @param int $num_per_page Number of results to return
	 * @param int $page Page of results that should be returned
	 * @param optional string $sort_column Column to sort results by
	 * @param optional string $sort_order Order to sort results by (ASC, DESC)
	 * @param optional string $search_text Text to search for in results
	 * @param optional string $filter Type of visitors to return (All, Vistors, Leads)
	 * @param optional string $orbit_filter Match type of Orbit visitors are assigned to
	 *
	 * @return object Visitor Results
	 */
	public function getAllLeads($num_per_page=10, $page=1, $sort_column='', $sort_order='', $search_text='', $filter='', $orbit_filter='')
	{
		$params = array(
			'page' => $page, 
			'limit' => $num_per_page,
			'sort_column' => $sort_column,
			'sort_order' => $sort_order,
			'search_text' => $search_text,
			'filter' => $filter,
			'orbit_filter' => $orbit_filter
		);
		
		return $this->doGet('leads', $params);
	}
	
	/**
	 * Depreciated: Marked for removal
	 */
	public function getLeadViews($lead=NULL, $num_per_page=10, $page=1)
	{
		if ($lead === NULL) return false;
		$params = array(
			'page' => $page, 
			'limit' => $num_per_page
		);
		return $this->doGet('leads/views/'.$lead, $params);
	}
	
	/**
	 * Make Hash Parameters
	 *
	 * @return string URL query with hashed API credentials
	 */
	public function makeHashParam()
	{
		$ts = time();
		$hash = hash_hmac('sha1', $ts.$this->_apiKey, $this->_apiKey);
		
		$auth = array(
			'key' => $hash,
			'ts' => $ts,
			'aid' => $this->_account,
		);
		
		return '?'.http_build_query($auth);
	}
	
	/**
	 * Get Visitor/Lead Profile data
	 *
	 * @param int $lead ID of visitor to retrieve
	 * @param int $num_per_page Number of days to retrieve history for
	 * @param int $page Page of hisotry results to return
	 *
	 * @return object Lead Profile
	 */
	public function getLeadProfile($lead=NULL, $num_per_page=10, $page=1)
	{
		if ($lead === NULL) return false;
		$params = array(
			'page' => $page, 
			'limit' => $num_per_page
		);
		return $this->doGet('leads/profile/'.$lead, $params);
	}
	
	/**
	 * Get list of exiting Orbits
	 *
	 * @param int $num_per_page Number of results to return
	 * @param int $page Page of Orbit results to return
	 *
	 * @return object Orbits
	 */
	public function getOrbits($num_per_page=10, $page=1)
	{
		$params = array(
			'page' => $page,
			'limit' => $num_per_page
		);
		
		return $this->doGet('orbits', $params);
	}
	
	/**
	 * Retrive a single Orbit
	 *
	 * @param int $id ID of Orbit to retrieve
	 *
	 * @return object Orbit
	 */
	public function getOrbit($id)
	{
		if ($id==NULL || $id < 1) return false;
		return $this->doGet('orbits/edit/'.$id);
	}
	
	/**
	 * Depreciated: Marked for removal
	 */
	public function updatePriority($lead=NULL, $status=1)
	{
		if ($lead==NULL || $lead < 1) return false;
		if ($status != 0 && $status != 1) return false;
		$params = array(
			'id' => $lead,
			'status' => $status
		);
		return $this->doPost('lead/priority', $params);
	}
	
	/**
	 * Retrieve default settings for ORBTR account
	 *
	 * @return object Account Defaults
	 */
	public function getAccountDefaults()
	{
		return $this->doGet('account');
	}
	
	/**
	 * Update ORBTR Account Defaults
	 *
	 * @param string $email Email to send account notifications to
	 * @param int $status Default priority to assign leads to (Depreciated: Marked for deletion)
	 * @param int $email_new Whether to send email notifications for new leads (0 = false, 1 = true)
	 * @param int $email_returning Where to send email notifications for returning leads (0 = false, 1 = true)
	 */
	public function updateAccountDefaults($email, $status, $email_new, $email_returning, $digest)
	{
		return $this->doPost('account', array('email' => $email, 'priority' => $status, 'email_new' => $email_new, 'email_returning' => $email_returning, 'digest_email' => $digest));
	}
	
	/**
	 * Check for new plugin updates
	 *
	 * $param string $field Field to check (version)
	 *
	 * @return object Plugin update information
	 */
	public function updateCheck($field=NULL)
	{
		$params = array();
		if ($field)
		{
			$params = array(
				'field' => $field
			);
		}
       if (ORBTR_PLUGIN_NAME == 'orbtrping') 
		    return $this->doGet('update/check', $params);
       else
        return $this->doGet('update2/check', $params);
	}
	
	/**
	 * Retrieve update details for Plug-in
	 *
	 * @return object Plugin update details
	 */
	public function updateInfo()
	{
		if (ORBTR_PLUGIN_NAME == 'orbtrping') 
		    return $this->doGet('update/info');
       else
        return $this->doGet('update2/info');
	}
	
	/**
	 * Add an Orbit
	 * 
	 * @param array Orbit information and rule data
	 */
	public function addOrbit($data)
	{
		return $this->doPost('orbits', array('post' => serialize($data)) );	
	}
	
	/**
	 * Retrieve all Orbits
	 *
	 * @return object Orbits
	 */
	public function listOrbits()
	{
		return $this->doGet('orbits/all');	
	}
	
	/**
	 * Validate update license for ORBTR
	 */
	public function licenseValidate()
	{
        if (ORBTR_PLUGIN_NAME == 'orbtrping') 
		    return $this->doGet('update/validate');
       else
        return $this->doGet('update2/validate');
	}
	
	/**
	 * Delete a Visitor/Lead and all its data
	 *
	 * @param int $id ID of Visitor/Lead to delete
	 */
	public function deleteLead($id)
	{
		return $this->doDelete('lead/'.$id);	
	}
	
	/**
	 * Delete an Orbit and all its data
	 *
	 * @param int $id ID of Orbit to delete
	 */
	public function deleteOrbit($id)
	{
		return $this->doDelete('orbits/'.$id);	
	}
	
	/**
	 * Retrieve a single Visitor/Lead
	 *
	 * @param int $id ID of Visitor/Lead to retrieve
	 *
	 * @return object Visitor/Lead
	 */
	public function getLead($id)
	{
		return $this->doGet('lead/'.$id);	
	}
	
	/**
	 * Update a Visitor's/Lead's Information
	 *
	 * @param int $id ID of Visitor/Lead to update
	 * @param array $data Array of Key/Values of fields to update
	 */
	public function updateLead($id, $data)
	{
		return $this->doPost('lead/'.$id, $data);
	}
	
	/**
	 * Retrieve Dashboard Stats
	 *
	 * @return object Dashboard Stats
	 */
	public function getStats()
	{
		return $this->doGet('leads/statistics', array());
	}
	
	/**
	 * Remote GET
	 *
	 * @param string $url API path of command
	 * @param array optional $params Query vars for command
	 */
	public function doGet( $url, $params=array() )
	{
		return $this->_exec(self::GET, $this->_url($url), $params);
	}
	
	/**
	 * Remote POST
	 *
	 * @param string $url API path of command
	 * @param array optional $params Query vars for command
	 */
	public function doPost( $url, $params=array() )
	{
		return $this->_exec(self::POST, $this->_url($url), $params);
	}
	
	/**
	 * Remote DELETE
	 *
	 * @param string $url API path of command
	 * @param array optional $params Query vars for command
	 */
	public function doDelete( $url, $params=array() )
	{
		return $this->_exec(self::DELETE, $this->_url($url), $params);
	}
	
	/**
	 * Internal URL Builder
	 *
	 * @param string $url API path of command
	 *
	 * @return string Full URL to the API command
	 */
	private function _url( $url )
	{
		return "{$this->_protocol}://{$this->_serviceURL}/{$url}";
	}
	
	/**
	 * Execute Remote Queries
	 *
	 * @param string $type Type of Request (GET, POST, DELETE)
	 * @param string $url Full URL to API Command
	 * @param array optional $params Query vars for command
	 */
	private function _exec( $type, $url, $params=array() )
	{
		//Setup the authorization query
		$ts = time();
		$hash = hash_hmac('sha1', $ts.$this->_apiKey, $this->_apiKey);
		
		$timezone = get_option('timezone_string');
		
		$timezone = empty($timezone) ? 'Etc/UTC' : $timezone;
		
		$auth = array(
			'key' => $hash,
			'ts' => $ts,
			'aid' => $this->_account,
			'timezone' => $timezone
		);
		
		$args = array();
		
		switch ($type) 
		{
			case self::DELETE:
				$url .= '?' . http_build_query(array_merge($params, $auth));
				$args = array(
					'method' => self::DELETE
				);
				break;
			case self::POST:
				$url .= '?' . http_build_query(array_merge($auth));
				$args = array(
					'method' => self::POST,
					'body' => $params
				);
				break;
			case self::GET:
			default:
				$url .= '?' . http_build_query(array_merge($params, $auth));
				$args = array(
					'method' => self::GET
				);
				break;
		}
		
		$args['timeout'] = 120;
		$args['httpversion'] = '1.1';
		
		//make request to the server
		$response = wp_remote_request($url, $args);
       
       $status = wp_remote_retrieve_response_code($response);
       $msg = wp_remote_retrieve_response_message($response);
       //error_log($status.': '.$msg);
		
		//check for errors
		if ( !is_wp_error( $response ) )
		{
			//Grab response code from the request
			switch ($status)
			{
				case self::HTTP_OK:
				case self::HTTP_ACEPTED:
				case self::HTTP_ACEPTED:
					//everything went ok, return the response for processing
					return json_decode($response['body']);
					break;
				case self::UNAUTHORIZED:
					return array('ERROR' => 'UNAUTHORIZED');
					break;
				default:
					//Something happened, throw an error to be handled
					throw new Orbtr_Exception( $msg, $status );	
			}
		}
		else
		{
			//Unknown error, possible API connection issue
			throw new Orbtr_Exception( "UNKNOWN API Error Occured: 0", 0 );	
		}
	}
}