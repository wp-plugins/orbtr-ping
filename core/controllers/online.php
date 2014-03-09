<?php

class Orbtr_OnlineController
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
		$online_leads = '';
		$limit = 20;
		try 
		{
			if ((!isset($_GET['p'])) || ($_GET['p'] == "1")) $_GET['p'] = 1;
			$online_leads = $this->api->getOnline($limit, $_GET['p']);
			if (isset($online_leads->error)) unset($online_leads);
		}
		catch (Orbtr_Exception $e)
		{
			echo '<div id="message" class="error">'.__(ORBTR_ERROR_API, 'orbtr_ping').'</div>';
		}
		
		$pager = new Pager('admin.php?page='.$_GET['page']);
		$start = $pager->findStart($limit);
		$count = (int)isset($online_leas->total) ? $online_leads->total : 0;
		$pages = $pager->findPages($count, $limit);
		
		require(ORBTR_CONNECT_PATH.'/core/views/online.php');
	}
}