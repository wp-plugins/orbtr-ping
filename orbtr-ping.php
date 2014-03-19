<?php
/**
Plugin Name: ORBTR Ping
Plugin URI: http://orbtr.net
Description: ORBTR Dashboard for viewing your lead tracking from within WordPress.
Version: 1.0.4
Author: Michael Shihinski
Author URI: http://orbtr.net
Text Domain: orbtr_ping
*/

/**
 *
 * @package ORBTR
 */
 
define ('ORBTR_CONNECT_PATH', dirname(__FILE__));
define ('ORBTR_TRACKING_URL', 'http://ping.orbtr.net/v2/trk.v2.php');
define("ORBTR_PLUGIN_VERSION", "1.0.4");
define("ORBTR_DATABASE_VERSION", "1.0.0");

function orbtr_url() {
	return plugins_url().'/'.dirname(plugin_basename(__FILE__));
}

function orbtr_content_url() {
	$upload_dir = wp_upload_dir();
	return $upload_dir['baseurl'].'/orbtrping';
}

function orbtr_content_dir() {
	$upload_dir = wp_upload_dir();
	return $upload_dir['basedir'];	
}

function orbtr_convert_params($query) {
    $queryParts = explode('&', $query);
   
    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
   
    return $params;
} 

function orbtr_redirect_filter($location, $status='') {
    $parts = parse_url($location);
    if (isset($parts['query']) && !empty($parts['query']))
    {
        $params = orbtr_convert_params($parts['query']);
        error_log(print_r($params, true));
        if (isset($params['oemail']) && !empty($params['oemail']))
        {
            //error_log(str_replace('@', '%40', $params['oemail']));
            $location = str_replace($params['oemail'], str_replace('@', '%40', $params['oemail']), $location);
        }
    }
    //error_log($location);
    return $location;
}

add_filter('wp_redirect', 'orbtr_redirect_filter');

require_once('core/language/language.php');
require_once('core/PluginObject.php');
require_once('core/helpers/HtmlForm.php');
require_once('core/helpers/Pager.class.php');
require_once('core/helpers/ErrorMessages.php');
require_once('core/helpers/UserAgentParser.php');
require_once('core/helpers/RefererHelper.php');
require_once('core/models/settings.php');
require_once('core/controllers/dashboard_widget.php');
require_once('core/controllers/settings.php');
require_once('core/controllers/dashboard.php');
require_once('core/controllers/online.php');
require_once('core/controllers/leads.php');
require_once('core/libraries/ORBTRApi.php');
require_once('core/libraries/orbtrleads.php');
require_once('core/libraries/OrbtrJetpackTracking.php');
require_once('core/libraries/OrbtrCommentTracking.php');

//include secondary modules
require_once('core/libraries/privacycheck.php');

require_once('core/libraries/OrbtrConnect.php');

$orbtr_dashboard_widget = new ORBTRDashboardWidget();
$orbtr_errors = new OrbtrErrorMessages();

//initialize Orbtr System
$orbtrDashboard = new OrbtrConnect(__FILE__);