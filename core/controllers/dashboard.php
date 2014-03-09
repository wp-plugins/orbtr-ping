<?php

class Orbtr_DashboardController
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
	}
	
	public function pageCallback()
	{
		if (isset($_GET['action']) && $_GET['action'] == 'views')
		{
			$this->showViews();
		}
		else
		{
			$this->showLeads();	
		}
	}
	
    private function showViews()
	{
		$leads = '';
		$limit = 50;
		try 
		{
			if ((!isset($_GET['p'])) || ($_GET['p'] == "1")) $_GET['p'] = 1;
			
			$views = $this->api->getLeadViews($_GET['uid'], $limit, $_GET['p']);
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
    
	public function ajaxDashboard()
	{
       
		try 
		{
			$stats = $this->api->getStats();
			//print_r($leads);
		}
		catch (Orbtr_Exception $e)
		{
			echo '<div id="message" class="error">'.__(ORBTR_ERROR_API, 'orbtr_ping').'</div>';
		}
		
		require(ORBTR_CONNECT_PATH.'/core/views/dashboard-ajax.php');
		exit;	
	}
	
	private function showLeads()
	{
		try 
		{
			$stats = $this->api->getStats();
			//print_r($leads);
		}
		catch (Orbtr_Exception $e)
		{
			echo '<div id="message" class="error">'.__(ORBTR_ERROR_API, 'orbtr_ping').'</div>';
		}
			
		require(ORBTR_CONNECT_PATH.'/core/views/dashboard.php');
	}
}