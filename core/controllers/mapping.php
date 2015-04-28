<?php

add_action('init',  array('ORBTRMapping', 'init'));

if(!function_exists("rgget")):
	function rgget($name, $array=null){
		if(!isset($array))
			$array = $_GET;
	
		if(isset($array[$name]))
			return $array[$name];
	
		return "";
	}
endif;

if(!function_exists("rgpost")):
	function rgpost($name, $do_stripslashes=true){
		if(isset($_POST[$name]))
			return $do_stripslashes ? stripslashes_deep($_POST[$name]) : $_POST[$name];
	
		return "";
	}
endif;

if(!function_exists("rgar")):
	function rgar($array, $name){
		if(isset($array[$name]))
			return $array[$name];
	
		return '';
	}
endif;

if(!function_exists("rgars")):
	function rgars($array, $name){
		$names = explode("/", $name);
		$val = $array;
		foreach($names as $current_name){
			$val = rgar($val, $current_name);
		}
		return $val;
	}
endif;

if(!function_exists("rgempty")):
	function rgempty($name, $array = null){
		if(!$array)
			$array = $_POST;
	
		$val = rgget($name, $array);
		return empty($val);
	}
endif;


if(!function_exists("rgblank")):
	function rgblank($text){
		return empty($text) && strval($text) != "0";
	}
endif;

class ORBTRMappingData
{

    public static function update_table()
    {
        global $wpdb;
        $table_name = self::get_table_name();

        if ( ! empty($wpdb->charset) )
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if ( ! empty($wpdb->collate) )
            $charset_collate .= " COLLATE $wpdb->collate";

        require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

        $sql = "CREATE TABLE $table_name (
              id mediumint(8) unsigned not null auto_increment,
              form_id mediumint(8) unsigned not null,
              is_active tinyint(1) not null default 1,
              meta longtext,
              PRIMARY KEY  (id),
              KEY form_id (form_id)
            )$charset_collate;";

        dbDelta($sql);

	}

    public static function get_table_name()
    {
        global $wpdb;
        return $wpdb->prefix . "rg_orbtrmaps";
    }
    
    public static function get_feeds()
    {
        global $wpdb;
        $table_name = self::get_table_name();
        $form_table_name = RGFormsModel::get_form_table_name();
        
        $sql = "SELECT s.id, s.is_active, s.form_id, s.meta, f.title as form_title
                FROM $table_name s
                INNER JOIN $form_table_name f ON s.form_id = f.id";

        $results = $wpdb->get_results($sql, ARRAY_A);

        $count = sizeof($results);
        for($i=0; $i<$count; $i++)
        {
            $results[$i]["meta"] = maybe_unserialize($results[$i]["meta"]);
        }

        return $results;
    }

    public static function delete_feed($id)
    {
        global $wpdb;
        $table_name = self::get_table_name();
        $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id=%s", $id));
    }

    public static function get_feed_by_form($form_id, $only_active = false)
    {
        global $wpdb;
        $table_name = self::get_table_name();
        $active_clause = $only_active ? " AND is_active=1" : "";
        $sql = $wpdb->prepare("SELECT id, form_id, is_active, meta FROM $table_name WHERE form_id=%d $active_clause", $form_id);
        $results = $wpdb->get_results($sql, ARRAY_A);
        if(empty($results))
            return array();

        //Deserializing meta
        $count = sizeof($results);
        for($i=0; $i<$count; $i++){
            $results[$i]["meta"] = maybe_unserialize($results[$i]["meta"]);
        }
        return $results;
    }

    public static function get_feed($id)
    {
        global $wpdb;
        $table_name = self::get_table_name();
        $sql = $wpdb->prepare("SELECT id, form_id, is_active, meta FROM $table_name WHERE id=%d", $id);
        $results = $wpdb->get_results($sql, ARRAY_A);
        if(empty($results))
            return array();

        $result = $results[0];
        $result["meta"] = maybe_unserialize($result["meta"]);
        return $result;
    }

    public static function update_feed($id, $form_id, $is_active, $setting)
    {
        global $wpdb;
        $table_name = self::get_table_name();
        $setting = maybe_serialize($setting);
        if($id == 0)
        {
            //insert
            $wpdb->insert($table_name, array("form_id" => $form_id, "is_active"=> $is_active, "meta" => $setting), array("%d", "%d", "%s"));
            $id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
        }
        else
        {
            //update
            $wpdb->update($table_name, array("form_id" => $form_id, "is_active"=> $is_active, "meta" => $setting), array("id" => $id), array("%d", "%d", "%s"), array("%d"));
        }

        return $id;
    }

    public static function drop_tables()
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS " . self::get_table_name());
    }
    
    // get forms that are not assigned to feeds
    public static function get_available_forms($active_form = '')
    {
        $forms = RGFormsModel::get_forms();
        $available_forms = array();

        foreach($forms as $form) {
            $available_forms[] = $form;
        }

        return $available_forms;
    }

}

class ORBTRMapping 
{
	private static $url = "http://orbtr.net";
	private static $slug = "orbtrfields";
	private static $version = "1.0.2";
	private static $min_gravityforms_version = "1.7";
											 
	public static function init()
	{
        if(!self::is_gravityforms_supported())
			return;
        
        if(is_admin())
		{
            self::setup();
            
            //integrating with Members plugin
			if(function_exists('members_get_capabilities'))
                add_filter('members_get_capabilities', array("ORBTRMapping", "members_get_capabilities"));
            
            //creates the subnav left menu
			add_filter('gform_addon_navigation', array('ORBTRMapping', 'create_menu'));
            
            if (self::is_orbtr_page())
			{
				//enqueueing sack for AJAX requests
				wp_enqueue_script(array("sack"));
				
				//loading Gravity Forms tooltips
				require_once(GFCommon::get_base_path() . "/tooltips.php");
				add_filter('gform_tooltips', array('ORBTRMapping', 'tooltips'));
			}
			elseif (in_array(RG_CURRENT_PAGE, array("admin-ajax.php")))
			{
			
				add_action('wp_ajax_gf_orbtr_update_feed_active', array('ORBTRMapping', 'update_feed_active'));
				add_action('wp_ajax_gf_select_orbtr_form', array('ORBTRMapping', 'select_orbtr_form'));
			
			}
        }
        else
        {
            add_action('gform_entry_post_save',array("ORBTRMapping", "trasmit_data"), 10, 2);
        }
    }
    
    public static function trasmit_data($entry, $form)
	{
        
        $config = self::get_config($form);
        
        if(!$config)
            return $entry;
				
		$form_data = self::get_form_data($form, $config);
        
		RGFormsModel::update_lead($entry);
        
        $uid = $_COOKIE['ping_uid'];
		   
        if (!$uid) return '';

        $fields = array(
            'oemail' => $form_data['oemail'],
            'fName' => $form_data['fName'],
            'lName' => $form_data['lName'],
            'ocompany' => $form_data['ocompany'],
            'uid' => $uid
        );
        
        try {
            self::create_tracking($fields);
        } catch (Exception $e) {}
        
		return $entry;
	}
    
    private static function get_form_data($form, $config){

        // get products
        $tmp_lead = RGFormsModel::create_lead($form);
        
        $form_data = array();

        $form_data["oemail"] = rgpost('input_'. str_replace(".", "_",$config["meta"]["orbtr_fields"]["oemail"]));
        $form_data["fName"] = rgpost('input_'. str_replace(".", "_",$config["meta"]["orbtr_fields"]["fName"]));
        $form_data["lName"] = rgpost('input_'. str_replace(".", "_",$config["meta"]["orbtr_fields"]["lName"]));
        $form_data["ocompany"] = rgpost('input_'. str_replace(".", "_",$config["meta"]["orbtr_fields"]["ocompany"]));

        // need an easy way to filter the the order info as it is not modifiable once it is added to the transaction object
        $form_data = apply_filters("gform_orbtr_form_data_{$form['id']}", apply_filters('gform_orbtr_form_data', $form_data, $form, $config), $form, $config);

        return $form_data;
    }
    
    public static function get_config($form)
	{

        //Getting authorizenet settings associated with this transaction
        $configs = ORBTRMappingData::get_feed_by_form($form["id"]);
        if(!$configs)
            return false;

        foreach($configs as $config){
        	return $config;
        }

        return false;
	}
    
    protected static function create_tracking($data)
	{
		if (empty($data['oemail'])) return;
		
		//set POST variables
		$url = ORBTR_TRACKING_URL;
		
		$settings = new Orbtr_SettingsModel('orbtrping_settings');
		
		$fields = array(
			'lName'		=> urlencode($data['lName']),
			'fName' 	=> urlencode($data['fName']),
			'oemail' 	=> urlencode($data['oemail']),
            'ocompany' => urlencode($data['ocompany']),
			'action' 	=> 'leaddata',
			'uid'		=> $data['uid'],
			'aid'		=> $settings->getSetting('orbtr_account_id')
		);
		
       $fields_string = '';
        
		foreach($fields as $key=>$value) 
		{ 
			$fields_string .= $key.'='.$value.'&'; 
		}
		
		rtrim($fields_string,'&');
        
        wp_remote_get($url.'?'.$fields_string, array( 'timeout' => 120, 'httpversion' => '1.1' ));
	}
    
    public static function update_feed_active()
	{
		check_ajax_referer('gf_orbtr_update_feed_active','gf_orbtr_update_feed_active');
		$id = $_POST["feed_id"];
		$feed = ORBTRMappingData::get_feed($id);
		ORBTRMappingData::update_feed($id, $feed["form_id"], $_POST["is_active"], $feed["meta"]);
	}
    
    public static function select_orbtr_form()
	{

        check_ajax_referer("gf_select_orbtr_form", "gf_select_orbtr_form");

        $form_id =  intval($_POST["form_id"]);
        $setting_id =  intval($_POST["setting_id"]);

        //fields meta
        $form = RGFormsModel::get_form_meta($form_id);

        $orbtr_fields = self::get_orbtr_information($form);

        die("EndSelectForm(" . GFCommon::json_encode($form) . ", '" . str_replace("'", "\'", $orbtr_fields) . "');");
    }
    
    private static function get_orbtr_information($form, $config=null)
	{

        //getting list of all fields for the selected form
        $form_fields = self::get_form_fields($form);

        $str = "<table cellpadding='0' cellspacing='0'><tr><td class='orbtr_col_heading'>" . __("ORBTR Fields", "orbtrfields") . "</td><td class='corbtr_col_heading'>" . __("Form Fields", "orbtrfields") . "</td></tr>";
        $orbtrfields = self::get_orbtr_fields();
        foreach($orbtrfields as $field){
            $selected_field = $config ? $config["meta"]["orbtr_fields"][$field["name"]] : "";
            $str .= "<tr><td class='orbtr_field_cell'>" . $field["label"]  . "</td><td class='orbtr_field_cell'>" . self::get_mapped_field_list($field["name"], $selected_field, $form_fields) . "</td></tr>";
        }
        $str .= "</table>";

        return $str;
    }
    
    private static function get_orbtr_fields()
	{
        return
        array(
			array(
				"name" => "fName",
				"label" => __("First Name", "orbtrfields")
			),
            array(
				"name" => "lName",
				"label" => __("Last Name", "orbtrfields")
			),
            array(
				"name" => "oemail" , 
				"label" =>__("Email", "orbtrfields")
			),
			array(
				"name" => "ocompany" , 
				"label" =>__("Company", "orbtrfields")
			),
		);
    }
    
    public static function edit_page($id)
	{
		$data = array();
		self::load_view('edit', $data);
	}
    
    public static function list_page()
	{
		if(!self::is_gravityforms_supported()){
            die(__(sprintf("ORBTR Add-On requires Gravity Forms %s. Upgrade automatically on the %sPlugin page%s.", self::$min_gravityforms_version, "<a href='plugins.php'>", "</a>"), "orbtrfields"));
        }
		
		if(rgpost('action') == "delete"){
            check_admin_referer("list_action", "gf_orbtr_list");

            $id = absint($_POST["action_argument"]);
            ORBTRMappingData::delete_feed($id);
            ?>
            <div class="updated fade" style="padding:6px"><?php _e("Feed deleted.", "orbtrfields") ?></div>
            <?php
        }
        else if (!empty($_POST["bulk_action"])){
            check_admin_referer("list_action", "gf_orbtr_list");
            $selected_feeds = $_POST["feed"];
            if(is_array($selected_feeds)){
                foreach($selected_feeds as $feed_id)
                    ORBTRMappingData::delete_feed($feed_id);
            }
            ?>
            <div class="updated fade" style="padding:6px"><?php _e("Feeds deleted.", "orbtrfields") ?></div>
            <?php
        }
		
		$data = array();
		$data['settings'] = ORBTRMappingData::get_feeds();
		self::load_view('list', $data);
	}
    
    public static function load_view($file, $data=array())
	{
		$folder = ORBTR_CONNECT_PATH.'/core';
		
		extract($data);
		include("{$folder}/views/mapping/{$file}.php");
	}
    
    private static function get_mapped_field_list($variable_name, $selected_field, $fields)
	{
        $field_name = "orbtr_field_" . $variable_name;
        $str = "<select name='$field_name' id='$field_name'><option value=''></option>";
        foreach($fields as $field){
            $field_id = $field[0];
            $field_label = esc_html(GFCommon::truncate_middle($field[1], 40));

            $selected = $field_id == $selected_field ? "selected='selected'" : "";
            $str .= "<option value='" . $field_id . "' ". $selected . ">" . $field_label . "</option>";
        }
        $str .= "</select>";
        return $str;
    }
    
    public static function tooltips($tooltips)
	{
        $chargify_tooltips = array(
             "orbtr_gravity_form" => "<h6>" . __("Gravity Form", "orbtrfields") . "</h6>" . __("Select which Gravity Forms you would like to integrate with ORBTR.", "orbtrfields"),
			 "orbtr_fields" => "<h6>" . __("ORBTR Fields", "orbtrfields") . "</h6>" . __("Map your Form Fields to the available ORBTR field data.", "orbtrfields")
        );
        return array_merge($tooltips, $chargify_tooltips);
    }
    
    public static function add_permissions()
	{
        global $wp_roles;
        $wp_roles->add_cap("administrator", "gravityforms_orbtrmaps");
    }
    
    //Creates Chargify left nav menu under Forms
    public static function create_menu($menus)
	{

        // Adding submenu if user has access
        $permission = self::has_access("gravityforms_orbtrmaps");
        if(!empty($permission)) {
            $menus[] = array("name" => "orbtrfields", "label" => __("ORBTR", "orbtrfields"), "callback" =>  array("ORBTRMapping", "mapping_page"), "permission" => $permission);
        }

        return $menus;
    }
    
    //Manage displaying and editing feeds
    public static function mapping_page()
	{
		$view = rgget("view");
		if($view == "edit")
			self::edit_page(rgget("id"));
		else
			self::list_page();
	}
    
    //check if user has access
    protected static function has_access($required_permission)
	{
		$has_members_plugin = function_exists('members_get_capabilities');
		$has_access = $has_members_plugin ? current_user_can($required_permission) : current_user_can("level_7");
		if($has_access)
			return $has_members_plugin ? $required_permission : "level_7";
		else
			return false;
	}
    
    //Checks if the Supported Gravity Forms is installed
    protected static function is_gravityforms_supported()
	{
        if(class_exists("GFCommon"))
		{
            return version_compare(GFCommon::$version, self::$min_gravityforms_version, ">=");
        }
        else
		{
            return false;
        }
    }
    
    //retrieve form fields
    private static function get_form_fields($form)
	{
        $fields = array();

        if(is_array($form["fields"])){
            foreach($form["fields"] as $field){
                if(is_array(rgar($field,"inputs"))){

                    foreach($field["inputs"] as $input)
                        $fields[] =  array($input["id"], GFCommon::get_label($field, $input["id"]));
                }
                else if(!rgar($field, 'displayOnly')){
                    $fields[] =  array($field["id"], GFCommon::get_label($field));
                }
            }
        }
        return $fields;
    }
    
    //Creates or updates database tables. Will only run when version changes
    protected static function setup()
	{
        if(get_option("gf_orbtrmaps_version") != self::$version)
        {
            ORBTRMappingData::update_table();
        }

        update_option("gf_orbtrmaps_version", self::$version);
    }
    
    //IS this a plugin page?
    protected static function is_orbtr_page()
	{
        $current_page = trim(strtolower(RGForms::get("page")));
        return in_array($current_page, array("orbtrfields"));
    }
    
    //Target of Member plugin filter. Provides the plugin with Gravity Forms lists of capabilities
    public static function members_get_capabilities( $caps ) 
	{
        return array_merge($caps, array("gravityforms_orbtrmaps"));
    }
    
    //Log an error message
    protected static function log_error($message)
	{
		if(class_exists("GFLogging"))
		{
			GFLogging::include_logger();
			GFLogging::log_message(self::$slug, $message, KLogger::ERROR);
		}
	}
	
    //Log a debugger message
	protected static function log_debug($message)
	{
		if(class_exists("GFLogging"))
		{
			GFLogging::include_logger();
			GFLogging::log_message(self::$slug, $message, KLogger::DEBUG);
		}
	}
	
    //Set gravity forms to see that ORBTR supports logging
	public static function set_logging_supported($plugins)
	{
        $plugins[self::$slug] = "ORBTR";
        return $plugins;
    }
    
} //end class