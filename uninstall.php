<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();
require_once( plugin_dir_path( __FILE__ ) . 'class.SF_Options.php');

SF_Options::delete_all_options();

?>