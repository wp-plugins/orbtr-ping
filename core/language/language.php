<?php

//Plugin Configuration
define("ORBTR_PLUGIN_NAME", "orbtrping");

//Admin Menus
define("ORBTR_MENU_MAIN", "ORBTR Ping");
define("ORBTR_MENU_DASHBOARD", "Dashboard");
define("ORBTR_MENU_ONLINE", "Who's Online");
define("ORBTR_MENU_RECORDS", "All Records");
define("ORBTR_MENU_CONFIG", "Configuration");

//Page Titles
define("ORBTR_PAGE_DASHBOARD", "ORBTR Ping Dashboard");
define("ORBTR_PAGE_PROFILE", "ORBTR Ping Visitor Profile");
define("ORBTR_PAGE_ONLINE", "ORBTR Ping Lead Tracker : Who's Online");
define("ORBTR_PAGE_RECORDS", "ORBTR Ping Lead Tracker : All Records");
define("ORBTR_PAGE_SETTINGS", "ORBTR Ping Settings");

//Settings
define("ORBTR_SETUP_TAB", "ORBTR Ping Setup");
define("ORBTR_SETUP_KEY", "ORBTR Ping API Key");
define("ORBTR_SETUP_ACCOUNT", "ORBTR Ping Account ID");
define("ORBTR_SETUP_NOTIFY_EMAIL", "Notification Email(s)");
define("ORBTR_SETUP_NOTIFY_EMAIL_HELP", "This is where system notifications will be sent when tracking leads. Separate multiple emails with commas.");
define("ORBTR_SETUP_NOTIFY", "Notifications");
define("ORBTR_SETUP_NOTIFY_LEADS", "Email me when visitors become known leads.");
define("ORBTR_SETUP_NOTIFY_RETURN", "Email me when known leads return to the site.");
define("ORBTR_SETUP_COMMENTS", "Track Comments");
define("ORBTR_SETUP_COMMENTS_ENABLE", "Enable ORBTR Ping for tracking comments.");
define("ORBTR_SETUP_TIMEZONE", "Timezone Settings");
define("ORBTR_SETUP_TIMEZONE_HELP", "ORBTR Ping uses WordPress's %stimezone settings%s. If dates and times do not display correctly, please %scheck%s you have your timezone set correctly.");

//Error Messages
define("ORBTR_ERROR_INVALID_KEY", "Please check your ORBTR Ping API key and ORBTR Ping account id are correct.");
define("ORBTR_ERROR_API", "An API Error Occured.");
define("ORBTR_ERROR_COMM", "Error communicating with the ORBTR Ping API.");