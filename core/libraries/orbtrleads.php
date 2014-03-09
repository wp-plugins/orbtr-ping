<?php
/*
Plugin Name: Gravity Forms ORBTR LeadGen Add-On
Plugin URI: http://www.orbtr.net
Description: Integrates Gravity Forms with ORBTR LeadGen allowing form submissions to be automatically sent to your ORBTR account to initialize lead tracking.
Version: 1.0
Author: Michael Shihinski
Author URI: http://www.graphicten.com

------------------------------------------------------------------------
Copyright 2012 graphicten
*/

add_action('init',  array('GFOrbtrLeads', 'init'));

class GFOrbtrLeads {
	private static $path = "orbtrleads/orbtrleads.php";
    private static $url = "http://www.orbtr.net";
    private static $slug = "orbtrleads";
    private static $version = "1.0";
    private static $min_gravityforms_version = "1.3.9";
	
	public static function init()
	{
		//add_action("admin_notices", array('GFOrbtrLeads', 'is_gravity_forms_installed'), 10);
		
		if(!self::is_gravityforms_supported())
		{
           return;
        }
		
		if(self::is_orbtr_page()){
		
			//enqueueing sack for AJAX requests
			wp_enqueue_script(array("sack"));
			wp_enqueue_style('gravityforms-admin', GFCommon::get_base_url().'/css/admin.css');
		} 
		elseif(in_array(RG_CURRENT_PAGE, array('admin.php'))) 
		{
			add_action('admin_head', array('GFOrbtrLeads', 'show_orbtr_status'));
		}
		else
		{
             //handling post submission.
            add_action("gform_pre_submission", array('GFOrbtrLeads', 'push'), 10, 2);
        }

        //creates the subnav left menu
        add_filter("gform_addon_navigation", array('GFOrbtrLeads', 'create_menu'), 20);
		
		add_action("gform_editor_js", array('GFOrbtrLeads', 'add_form_option_js'), 10);
		
		add_filter('gform_tooltips', array('GFOrbtrLeads', 'add_form_option_tooltip'));
		
		add_filter('gform_form_settings', array('GFOrbtrLeads','form_settings'), 10, 2);
		
		add_filter('gform_pre_form_settings_save', array('GFOrbtrLeads','save_settings'));
	}
	
	public static function show_orbtr_status()
	{
		global $pagenow; 
		
		if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'gf_edit_forms' && !isset($_REQUEST['id'])) 
		{
			$activeforms = array();
        	$forms = RGFormsModel::get_forms();
        	if(!is_array($forms)) { return; }
        	foreach($forms as $form) 
			{
        		$form = RGFormsModel::get_form_meta($form->id);
        		if(is_array($form) && ($form['enableOrbtr'] == 1)) 
				{
        			$activeforms[] = $form['id'];
        		}
        	}
        	
        	if(!empty($activeforms)) {
		
?>
<style type="text/css">
	td a.row-title span.orbtr_enabled {
		position: absolute;
		background: transparent url('<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>orbtr-icon.png') right top no-repeat;
		height: 16px;
		text-indent: -9999px;
		width: 16px;
		margin-left: 10px;
	}
</style>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var arr = <?php echo '[' . implode(',', $activeforms) . ']'; ?>;
		$('table tbody.user-list tr').each(function() {
			var that = $(this),
				check = parseInt(that.find('td.column-id').text())
			;
			if($.inArray(check, arr) >= 0) {
				that.find('td a.row-title').append('<span class="orbtr_enabled" title="ORBTR is Enabled for this Form"></span>');
			}
		});		
	});
</script>
<?php
			}
		}
	}
	
	public static function add_permissions()
	{
        global $wp_roles;
        $wp_roles->add_cap("administrator", "gravityforms_orbtr");
        $wp_roles->add_cap("administrator", "gravityforms_orbtr_uninstall");
    }
	
	//Target of Member plugin filter. Provides the plugin with Gravity Forms lists of capabilities
    public static function members_get_capabilities( $caps ) 
	{
        return array_merge($caps, array("gravityforms_orbtr", "gravityforms_orbtr_uninstall"));
    }
	
	private static function is_gravityforms_installed()
	{
        return class_exists("RGForms");
    }
	
	public static function is_gravity_forms_installed() 
	{
		global $pagenow, $page; $message = '';

		if($pagenow != 'plugins.php') { return;}

		if(!class_exists('RGForms')) {
			if(file_exists(WP_PLUGIN_DIR.'/'.self::$path)) 
			{
				$message .= '<p>Gravity Forms is installed but not active. <strong>Activate Gravity Forms</strong> to use the Gravity Forms Highrise plugin.</p>';
			} 
			else 
			{
				$message .= '<h2><a href="http://katz.si/gravityforms">Gravity Forms</a> is required.</h2><p>You do not have the Gravity Forms plugin enabled. <a href="http://katz.si/gravityforms">Get Gravity Forms</a>.</p>';
			}
		}
		
		if(!empty($message)) 
		{
			echo '<div id="message" class="error">'.$message.'</div>';
		}
	}
	
	private static function is_gravityforms_supported()
	{
        if(class_exists("GFCommon"))
		{
            $is_correct_version = version_compare(GFCommon::$version, self::$min_gravityforms_version, ">=");
            return $is_correct_version;
        }
        else
		{
            return false;
        }
    }
	
	protected static function has_access($required_permission)
	{
        $has_members_plugin = function_exists('members_get_capabilities');
        $has_access = $has_members_plugin ? current_user_can($required_permission) : current_user_can("level_7");
        if($has_access)
            return $has_members_plugin ? $required_permission : "level_7";
        else
            return false;
    }
	
	private static function is_orbtr_page()
	{
    	if(empty($_GET["page"])) { return false; }
        $current_page = trim(strtolower($_GET["page"]));
        $orbtr_pages = array("gf_orbtr");

        return in_array($current_page, $orbtr_pages);
    }
	
	public static function uninstall()
	{

        if(!GFHighrise::has_access("gravityforms_orbtr_uninstall"))
            (__("You don't have adequate permission to uninstall ORBTR Add-On.", "gravityformsorbtr"));

        //removing options
        delete_option("gf_orbtr_settings");

        //Deactivating plugin
        $plugin = self::$path;
        demanage_options($plugin);
        update_option('recently_activated', array($plugin => time()) + (array)get_option('recently_activated'));
    }
	
	public static function orbtr_page()
	{
        if(isset($_GET["view"]) && $_GET["view"] == "edit") 
		{
            self::edit_page($_GET["id"]);
        } 
		else 
		{
			self::settings_page();
		}
    }
	
	public static function edit_page()
	{
		
	}
	
	public static function settings_page()
	{
		?>
        <style type="text/css">
            .ul-square li { list-style: square!important; }
            .ol-decimal li { list-style: decimal!important; }
        </style>
		<div class="wrap">
		<div id="icon-orbtr" class="icon32"><br /></div>
        <h2>ORBTR Gravity Forms Addon</h2>
	<?php if(!$valid) { ?>
		<div class="clear hr-divider"></div>
		
		<h3>Usage Instructions</h3>
		
		<div class="delete-alert alert_gray">
			<h4>To integrate a form with ORBTR:</h4>
			<ol class="ol-decimal">
				<li>Edit the form you would like to integrate (choose from the <a href="<?php _e(admin_url('admin.php?page=gf_edit_forms')); ?>">Edit Forms page</a>).</li>
				<li>Click "Form Settings"</li>
				<li>Click the "Advanced" tab</li>
				<li><strong>Check the box "Enable ORBTR integration"</strong></li>
				<li>Save the form</li>
			</ol>
		</div>
		
		
        <h4>Form Fields</h4>
        <p>Fields will be automatically mapped by ORBTR using the default Gravity Forms labels. If you change the labels of your fields, make sure to use the following keywords in the label to match and send data to ORBTR.</p>
        <div class="alert_yellow" style="margin-bottom:16px;">
			<p style="font-weight:normal; padding:10px;">Note: <strong>Form entries must have an email address</strong> for data to be saved to ORBTR.</p>
		</div>
		
        <ul class="ul-square">
        	<li><code>name</code> (use to auto-split names into First Name and Last Name fields)</li>
            <li><code>first name</code></li>
            <li><code>last name</code></li>
            <li><code>email</code></li>
            <li>Anything not recognized by the list will be not be sent to ORBTR</li>
        </ul>
	
        
        <?php } // end if($api) ?>
        </div>
        <?php
	}
	
	public static function create_menu($menus)
	{
        // Adding submenu if user has access
		$permission = self::has_access("gravityforms_orbtr");
		if(!empty($permission)) 
		{
			$menus[] = array(
				"name" => "gf_orbtr", 
				"label" => __("ORBTR", "gravityformsorbtr"), 
				"callback" =>  array("GFOrbtrLeads", "orbtr_page"), 
				"permission" => $permission,
			);
		}
	    return $menus;
	}
	
	public static function add_form_option_tooltip($tooltips) 
	{
		$tooltips["form_orbtr"] = "<h6>" . __("Enable ORBTR Integration", "gravityformsorbtr") . "</h6>" . __("Check this box to integrate this form with ORBTR. When an user submits the form, the data will be added to ORBTR.", "gravityformsorbtr");
		return $tooltips;
	}
	
	public static function add_form_option_js() 
	{ 
		ob_start();
			gform_tooltip("form_orbtr");
			$tooltip = ob_get_contents();
		ob_end_clean();
		$tooltip = trim(rtrim($tooltip)).' ';
	?>
<style type="text/css">
	#gform_title .orbtr,
	#gform_enable_orbtr_label {
		float:right;
		background: url('<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>orbtr-icon.png') right top no-repeat;
		height: 16px;
		width: 16px;
		cursor: help;
	}
	#gform_enable_orbtr_label {
		float: none;
		width: auto;
		background-position: left top;
		padding-left: 18px;
		cursor:default;
	}
</style>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#gform_settings_tab_2 .gforms_form_settings').append("<li><input type='checkbox' id='gform_enable_orbtr' /> <label for='gform_enable_orbtr' id='gform_enable_orbtr_label'><?php _e("Enable ORBTR integration", "gravityformsorbtr") ?> <?php echo $tooltip; ?></label></li>");
		
		if($().prop) {
			$("#gform_enable_orbtr").prop("checked", form.enableOrbtr ? true : false);
		} else {
			$("#gform_enable_orbtr").attr("checked", form.enableOrbtr ? true : false);
		}
		
		$("#gform_enable_orbtr").live('click change load', function() {
			
			var checked = $(this).is(":checked")
			
			form.enableOrbtr = checked;
			
			SortFields(); // Update the form object to include the new enableHighrise setting
			
			if(checked) {
				$("#gform_title").append('<span class="orbtr" title="<?php _e("ORBTR integration is enabled.", "gravityformsorbtr") ?>"></span>');
			} else {
				$("#gform_title .orbtr").remove();
			}
		}).trigger('load');
		
		$('.tooltip_form_orbtr').qtip({
	         content: $('.tooltip_form_orbtr').attr('tooltip'), // Use the tooltip attribute of the element for the content
	         show: { delay: 200, solo: true },
	         hide: { when: 'mouseout', fixed: true, delay: 200, effect: 'fade' },
	         style: 'gformsstyle', // custom tooltip style
	         position: {
	      		corner: {
	         		target: 'topRight'
	                ,tooltip: 'bottomLeft'
	      		}
	  		 }
	      });
	});
</script><?php
	}
	
	public static function form_settings($settings, $form) 
	{
		$checked = rgar($form, 'enableOrbtr');
		$checked = ($checked == 1) ? ' checked' : '';
		
		ob_start();
		gform_tooltip("form_orbtr");
		$tooltip = ob_get_contents();
		ob_end_clean();
		$tooltip = trim(rtrim($tooltip)).' ';
		
		$settings['Form Options']['orbtr_setting'] = '
			<tr>
				<th>ORBTR '.$tooltip.'</th>
				<td><label for="enableOrbtr"><input type="checkbox" name="enableOrbtr" id="enableOrbtr" value="1"'.$checked.' /> Enable ORBTR integration</label></td>
			</tr>
		';
		
		return $settings;
	}
	
	public static function save_settings($form)
	{
		$form['enableOrbtr'] = rgpost('enableOrbtr');
		return $form;
	}
	
	public static function push($form_meta, $entry = array())
	{
		
		$data = array();
		
		$formid = $form_meta['id'];
		
		// Form Form Settings > Advanced > Enable Highrise
		if(!empty($form_meta['enableOrbtr'])) { $orbtr = true; }
		
	   //displaying all submitted fields
		foreach($form_meta["fields"] as $field){
		
		   if( is_array($field["inputs"]) ){
			   
			   //handling multi-input fields such as name and address
			   foreach($field["inputs"] as $input){
				   $value = isset($_POST["input_" . str_replace('.', '_', $input["id"])]) ? $_POST["input_" . str_replace('.', '_', $input["id"])] : '';
				   $label = self::getLabel($input["label"], $field, $input);
				   if(!$label) { $label = self::getLabel($field['label'], $field, $input); }
				   				   
				   if ($label == 'sBothNames') {
					    $names = explode(" ", $value);
				   		$data['sFirstName'] = $names[0];
				   		$data['sLastName'] = $names[1];
				   } else if ($label == 'sNotes') {
				   	   $message = 'true';
					   $data['sNotes'] .= "\n".$value."\n";
				   } else if ($label == 'sTags' ) {
			   	   		$is_tags = 'true';					   
				   		$tags[] = explode(",", $value) ;
			   	   } else if (trim(strtolower($label)) == 'orbtr' ) {
			   	   		$orbtr = $value ;
			   	   } else if($label == 'sStreet') {
			   			$data[$label] .= $value."\n";
			   	   } else {					   
					   $data[$label] = $value;
				   }
			   }
		   } else {
			   //handling single-input fields such as text and paragraph (textarea)
			   $value = stripslashes(@$_POST["input_" . $field["id"]]);		 
			   $label = self::getLabel($field["label"], $field);
			   
			   if ($label == 'sBothNames') {
					$names = explode(" ", $value);
					$data['sFirstName'] = $names[0];
					$data['sLastName'] = $names[1];
			   } else if ($label == 'sNotes') {
			   	   $message = 'true';					   
				   $data['sNotes'] .= "\n".$value."\n";
			   } else if ($label == 'sTags' ) {
			   	   		$is_tags = 'true';					   
				   		$tags[] = explode(",", $value) ;
			   } else if (trim(strtolower($label)) == 'orbtr' ) {
			   	   		$orbtr = $value ;
			   } else if($label == 'sStreet') {
			   		$data[$label] .= $value."\n";
			   } else if ($label == 'sEmail' || $label == 'sPhone' || $label == 'sFax' || $label == 'sMobile' || $label == 'sWebsite') {
					$data[$label] = $value; // allow more than one email or phone
			   } else {
					$data[$label] = $value ;
			   }
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
		
		//error_log(print_r($data, true), 1, 'michael@graphicten.com');
		/**/
	   if (isset($orbtr)) {
		   $uid = $_COOKIE['ping_uid'];
		   
		   if (!$uid) return '';
		   
		   $fields = array(
				'oemail' => $data['sEmail'],
				'fName' => $data['sFirstName'],
				'lName' => $data['sLastName'],
              'ocompany' => $data['sCompany'],
				'uid' => $uid
			);
		   
		   self::create_tracking($fields);
	   }
		
	   return '';
    }
	
	public static function create_tracking($data)
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
		
		error_log(print_r($result, true), 1, "michael@graphicten.com");
	}
	
	public static function getLabel($temp, $field = '', $input = false){
		$label = false;
				
		if($input && isset($input['id'])) {
			$id = $input['id'];
		} else {
			$id = $field['id'];
		}
		
		$type = $field['type'];
		
		switch($type) {
		
			case 'name':
				if($field['nameFormat'] != 'simple') {
					if(strpos($id, '.2')) {
						$label = 'salutation'; // 'Prefix'
					} else if(strpos($id, '.3')) {
						$label = 'sFirstName';
					} else if(strpos($id, '.6')) {
						$label = 'sLastName';
					} else if(strpos($id, '.8')) {
						$label = 'suffix'; // Suffix
					}
				}
				break;
			case 'address':
				if(strpos($id, '.1') || strpos($id, '.2')) {
					$label = 'sStreet'; // 'Prefix'
				} else if(strpos($id, '.3')) {
					$label = 'sCity';
				} else if(strpos($id, '.4')) {
					$label = 'sState'; // Suffix
				} else if(strpos($id, '.5')) {
					$label = 'sZip'; // Suffix
				} else if(strpos($id, '.6')) {
					$label = 'sCountry'; // Suffix
				}
				break;
			case 'email':
				$label = 'sEmail';
				break;
		}
		
		if($label) { 
			return $label; 
		}
				
		$the_label = strtolower($temp);
		
		if ((strpos($the_label, 'first') !== false || ( strpos($the_label,"name") !== false && strpos($the_label,"first") !== false))) {
			$label = 'sFirstName'; 
		} else if (( strpos( $the_label,"last") !== false || ( strpos( $the_label,"name") !== false && strpos($the_label,"last") !== false) )) {
			$label = 'sLastName';
		} else if ( strpos( $the_label,"name") !== false && $type == 'name') {
			$label = 'sBothNames';
		} else if ( strpos( $the_label,"company") !== false ) {
			$label = 'sCompany';
		} else if ( strpos( $the_label,"email") !== false || strpos( $the_label,"e-mail") !== false || $type == 'email') {
			$label = 'sEmail';
		} else if ( strpos( $the_label,"mobile") !== false || strpos( $the_label,"cell") !== false ) {
			$label = 'sMobile';
		} else if ( strpos( $the_label,"fax") !== false) {
			$label = 'sFax';
		} else if ( strpos( $the_label,"phone") !== false || $type == 'phone') {
			$label = 'sPhone';
		} else if ( strpos( $the_label,"city") !== false ) {
			$label = 'sCity';
		} else if ( strpos( $the_label,"country") !== false ) {
			$label = 'sCountry';
		} else if ( strpos( $the_label,"state") !== false ) {
			$label = 'sState';
		} else if ( strpos( $the_label,"zip") !== false ) {
			$label = 'sZip';
		} else if ( strpos( $the_label,"street") !== false || strpos( $the_label,"address") !== false ) {
			$label = 'sStreet';
		} else if ( strpos( $the_label,"website") !== false || strpos( $the_label,"web site") !== false || strpos( $the_label,"web") !== false ||  strpos( $the_label,"url") !== false) {
			$label = 'sWebsite';
		} else if ( strpos( $the_label,"highrise") !== false ) {	
			$label = 'highrise';
		} else if ( strpos( $the_label,"twitter") !== false ) {	
			$label = 'sTwitter';
		} else if ( strpos( $the_label,"title") !== false && strpos( $the_label,"untitled") === false) {
			$label = 'sTitle';
		} else if ( strpos( $the_label,"question") !== false || strpos( $the_label,"message") !== false || strpos( $the_label,"comments") !== false || strpos( $the_label,"description") !== false) {
			$label = 'sNotes';
		} else if ( strpos( $the_label,"staff_comment") !== false || strpos( $the_label,"background") !== false  ) {
			$label = 'sBackground';
		} else {
			$label = $temp;
		}

		return $label;
    }
	
	//Returns the url of the plugin's root folder
    protected function get_base_url()
	{
        return plugins_url(null, __FILE__);
    }

    //Returns the physical path of the plugin's root folder
    protected function get_base_path()
	{
        $folder = basename(dirname(__FILE__));
        return WP_PLUGIN_DIR . "/" . $folder;
    }
}