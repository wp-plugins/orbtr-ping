<?php

class Orbtr_SettingsModel
{
	private $db;
	private $tables;
	
	public function __construct($table_name, $debug=false)
	{
		global $wpdb;
		
		$this->db = $wpdb;
		if ($debug) $this->db->show_errors();
		
		$this->tables = new stdClass();
		$this->tables->settings = $this->db->base_prefix.$table_name;
	}
	
	/**
		 * Get Setting
		 *
		 * get a plugin specific configuration setting
		 *
		 * @access public
		 *
		 * @since 1.0
		 *
		 * @param $name | name of setting to retrieve from database.
		 *
		 * @return string | setting value
		 */
		public function getSetting($name) 
		{
			$setting = $this->db->get_var('SELECT value FROM '. $this->tables->settings .' WHERE setting=\''.$name.'\' LIMIT 1');
			return stripslashes($setting);
		}
		
		/**
		 * Set Setting
		 *
		 * set a plugin specific configuration setting
		 *
		 * @access public
		 *
		 * @since 1.0
		 *
		 * @param $name | name of setting to save
		 * @param $value | value of setting to save
		 */
		public function setSetting($name, $value) 
		{
			$check = $this->db->get_var('SELECT count(*) FROM '. $this->tables->settings .' WHERE setting=\''.$name.'\'');
			if ($check > 0) 
			{
				$this->db->update($this->tables->settings, array('value' => $value), array('setting' => $name));
			} 
			else 
			{
				$this->db->insert($this->tables->settings, array('setting' => $name, 'value' => $value));
			}
		}
		
		/**
		 * Set Options
		 *
		 * Set multiple settings
		 *
		 * @access public
		 *
		 * @since 1.0
		 *
		 * @param array $options | multi-dimensional array of settings to save (name => value)
		 */
		public function setOptions($options) 
		{
			if (!empty($options)) 
			{
				foreach($options as $key=>$val) 
				{
					$this->setSetting($key, $val);
				}
			}
		}
		
		/**
		 * Get Options
		 *
		 * load all settings to array
		 *
		 * @access public
		 *
		 * @since 1.0
		 *
		 * @return multi-dimensional array of all settings
		 */
		public function getOptions() 
		{
			$options = $this->db->get_results('SELECT * FROM '. $this->tables->settings, ARRAY_A);
			$option_array = '';
			if (!empty($options)) 
			{
				foreach ($options as $option) 
				{
					$option_array[$option['setting']] = stripslashes($option['value']);
				}
			}
			return $option_array;
		}
}