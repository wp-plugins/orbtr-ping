<?php

class Orbtr_LeadsController
{
	private $api;
	private $models;
	
	public function __construct()
	{
		$this->models = new stdClass();
		$this->models->settings = new Orbtr_SettingsModel('orbtrping_settings');
		$key = $this->models->settings->getSetting('orbtr_api_key');
		$account = $this->models->settings->getSetting('orbtr_account_id');
		$this->api = new OrbtrApi($key, $account);
		$this->form = new OrbtrHtmlForm();
		add_action('init', array($this, 'export_csv'));
	}
	
	public function export_csv()
	{
		if (isset($_GET['action']) && $_GET['action'] == 'leads_export_csv')
		{
			$priors = array(
				'1' => 'Cold',
				'2' => 'Warm',
				'3' => 'Hot'
			);
			
			try {
			$leads = $this->api->getAllLeads(-1, 1,'createDate','DESC');
			} catch (Orbtr_Exception $e){}
			
			header("Content-type: text/csv");  
			header("Cache-Control: no-store, no-cache");  
			header('Content-Disposition: attachment; filename="lead-export-'.date('Y-m-d').'.csv"');  
			  
			$outstream = fopen("php://output",'w');    
			
			if ($leads->leads->lead)
			{
				foreach( $leads->leads->lead as $row )  
				{ 
					$rowData = array($row->email, $row->fName, $row->lName, date('m/d/Y', strtotime($row->createDate))); 
					fputcsv($outstream, $rowData, ',', '"');  
				}  
			}
			fclose($outstream); 
			exit;
		}	
	}
	
	public function pageCallback()
	{
		if (isset($_GET['action']) && $_GET['action'] == 'delete')
		{
			try {
			$this->api->deleteLead($_REQUEST['uid']);
			} catch (Orbtr_Exception $e){}
			
			echo '<div id="notice" class="updated fade">The selected lead has been removed.</div>';	
		}
        
		if (isset($_GET['action']) && $_GET['action'] == 'views')
		{
			$this->showViews();
		}
		elseif (isset($_GET['action']) && $_GET['action'] == 'edit')
		{
			if (isset($_GET['action']) && $_POST['action2'] == 'updateLead')
			{
				try {
				$this->api->updateLead($_REQUEST['uid'], $_POST);
				} catch (Orbtr_Exception $e){}
			}
			$this->editLead();
		}
		else
		{
			$this->showLeads();	
		}
	}
    
    private function editLead()
	{
       if (!isset($_REQUEST['uid'])) wp_die('Invalid Request');
       
		$lead = $_REQUEST['uid'];
		
		try {
		$data = $this->api->getLead($lead);
		} catch (Orbtr_Exception $e){}
		
		require(ORBTR_CONNECT_PATH.'/core/views/editlead.php');
	}
	
	private function showViews()
	{
		$leads = '';
		$limit = 10;
		
		if (isset($_POST['updatelead']) && $_POST['updatelead'] == 1)
		{
			try {
			$this->api->updateLead($_REQUEST['uid'], $_POST);
			} catch (Orbtr_Exception $e){}
		}
		
		try 
		{
			if ((!isset($_GET['p'])) || ($_GET['p'] == "1")) $_GET['p'] = 1;
			
			$views = $this->api->getLeadProfile($_GET['uid'], $limit, $_GET['p']);
		}
		catch (Orbtr_Exception $e)
		{
			echo '<div id="message" class="error">'.__(ORBTR_ERROR_API, 'orbtr_connect').'</div>';
		}
		
		$pager = new Pager('admin.php?page='.$_GET['page'].'&amp;uid='.$_GET['uid'].'&amp;action=views');
		$start = $pager->findStart($limit);
		$count = (int)$views->total;
		$pages = $pager->findPages($count, $limit);
		
		require(ORBTR_CONNECT_PATH.'/core/views/views.php');
	}
	
	private function showLeads()
	{
		$leads = '';
		$limit = 20;
		$sort_order ='';
		$sort_column='';
		$search_text='';
		$filter='';
		
		try 
		{
			if ((!isset($_GET['p'])) || ($_GET['p'] == "1")) $_GET['p'] = 1;
			
			$sort_column = isset($_GET['sort_column']) ? $_GET['sort_column'] : '';
			$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : '';
			$search_text = isset($_GET['search_text']) ? $_GET['search_text'] : '';
			$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
			$orbit_filter = isset($_GET['orbit_filter']) ? $_GET['orbit_filter'] : '';
			
			$leads = $this->api->getAllLeads($limit, $_GET['p'], $sort_column, $sort_order, $search_text, $filter, $orbit_filter);
			//print_r($leads);
		}
		catch (Orbtr_Exception $e)
		{
           //error_log(print_r($e, true));
			echo '<div id="message" class="error">'.__(ORBTR_ERROR_API, 'orbtr_ping').'</div>';
		}
        
       $linkparams = array(
           'sort_column' => $sort_column,
           'sort_order' => $sort_order,
           'search_text' => $search_text,
           'filter' => $filter,
           'orbit_filter' => $orbit_filter
       );
       
       $linkparams = array_diff($linkparams, array(''));
		
		$sort_columns = array(
			'Sort By' => '',
			'Email' => 'email',
			'First Name' => 'fName',
			'Last Name' => 'lName',
			//'Priority' => 'priority',
			'Last Visit' => 'lastViewed'
		);
		
		$sort_orders = array(
			'ORDER' => '',
			'Ascending' => 'ASC',
			'Descending' => 'DESC'
		);
		
		$filters = array(
			'All' => '',
			'Visitors' => 'anon',
			'Leads' => 'leads'
		);
		
		$pager = new Pager(add_query_arg($linkparams, 'admin.php?page='.$_GET['page']));
		$start = $pager->findStart($limit);
		$count = isset($leads->total) ? $leads->total : 0;
		$pages = $pager->findPages($count, $limit);
		
		require(ORBTR_CONNECT_PATH.'/core/views/leads.php');
	}
}