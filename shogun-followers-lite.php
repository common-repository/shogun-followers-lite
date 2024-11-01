<?php
/**
@package ShogunFollowers

Plugin Name: Shogun Followers
Plugin URI: http://www.samurai9design.com/shogun-followers
Description: A Wordpress plugin to manage your Twitter Followers.  You can send direct messages to new followers and unfollow followers who aren't following you.

A powerful tool to increase your Twitter network.

Version: 0.1.4
Author: Samurai 9 Design
Author URI: http://www.samurai9design.com/
License: GPLv2 or later

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'SHOGUN_FOLLOWERS_LITE',							true);
define( 'SHOGUN_FOLLOWERS_UPGRADE_URL', 	'http://www.samurai9design.com/shogun-followers');

/**
 * Main plugin class
 * @package ShogunFollowers
 */
 
if ( ! class_exists( 'Shogun_Followers_Lite' )) {

	final class Shogun_Followers_Lite {

		/**
		 * Plugin name
		 * @var string
		*/
		public static $name = 'Shogun Followers Lite';
		
		public static $account_details;
		/**
		 * Plugin version
		 * @var string
		*/
		public static $version = '0.1.4';
		
		/**
		 * Interval Check
		 * @var int
		*/
		public static $interval = 120;
				 
		 /**
		 * Get plugin version
		 * @return string
		*/
		public static function get_version() {
			return self::$version;
		}

		/**
		 * Get plugin basename
		 * @return string
		*/
		public static function get_basename() {
			return plugin_basename( __FILE__ );
		}
		
		/**
		 * Class constructor
		 * @return void
		*/
		public function __construct() {
			$this->include_files();
			$this->init();
			$settings = SF_Account::get_settings();
			$twitterAR = new TwitterAR($settings['consumer_key'], $settings['consumer_secret'],  $settings['access_token'], $settings['access_secret']);
			$followers = new SF_Followers($twitterAR);
			if (Shogun_Followers_Lite::update_ok($followers->get_new_followers_timestamp() )) {
				$credentials = $twitterAR->get_credentials();
				SF_Account::update($credentials);
				$followers->update_new_followers();
			}
			if (Shogun_Followers_Lite::update_ok($followers->get_non_followers_timestamp())) {
				$followers->update_non_followers();
			}

			if ( is_admin() ) {
				$this->admin_init($followers);
			}
		}
		
		/**
		 * Include all the necessary files.
		 * @return void
		*/
		private function include_files() {
			require_once( plugin_dir_path( __FILE__ ) . 'twitterAR/twitterAR.php');
			require_once( plugin_dir_path( __FILE__ ) . 'class.SF_Options.php');
			require_once( plugin_dir_path( __FILE__ ) . 'class.SF_Account.php');
			require_once( plugin_dir_path( __FILE__ ) . 'class.SF_Followers.php');
			require_once( plugin_dir_path( __FILE__ ) . 'class.SF_Page_Followers.php' );
			require_once( plugin_dir_path( __FILE__ ) . 'class.SF_Page_NonFollowers.php' );
			require_once( plugin_dir_path( __FILE__ ) . 'class.SF_Page_Settings.php');
			require_once( plugin_dir_path( __FILE__ ) . 'class.SF_Table.php');
		}
		
		/**
		 * Initialize the actions and other classes.
		 * @return void
		*/
		private function init() {
			
		}	
		
		/**
		 * Initialize the actions and other classes.
		 * @return void
		*/
		private function admin_init($followers) {
			new SF_Page_Followers($followers);
			new SF_Page_NonFollowers($followers);
			new SF_Page_Settings($followers);
		}	
		
		/**
		 * Display header with logo
		 * @var string
		 * @return string
		*/
		public static function display_header() {
			$name = self::$name;
			$account = SF_Account::get_details();
			$followers_count =  number_format($account->followers_count);
			$friends_count = number_format($account->friends_count);
			$html = <<<EOT
				<div id="sf-header">
					<a href="../wp-admin/admin.php?page=shogun-followers-top-menu" title="{$name}" class="current">
						<span>{$name}</span>
					</a>
					<div id="sf-user-profile">
						<div id="sf-user-name">
							<span id="" class="sf-account-name">{$account->name}</span>
							<span id="" class="sf-account-screen_name">@{$account->screen_name}</span>
						</div>
						<div id="sf-user-stats">
							<ul>
								<li>
									<span class="sf-twitter-label">Followers</span>
									<span class="sf-twitter-value">{$followers_count}</span>
								</li>
								<li>
									<span class="sf-twitter-label">Friends</span>
									<span class="sf-twitter-value">{$friends_count}</span>
								</li>
							</ul>
						</div>
						<img class="sf-account-image" src="{$account->profile_image_url_big}" alt="">
					</div>
				</div>
EOT;
			echo $html;
		}
		
		/**
		 * Check if it is ok to update the options.
		 *
		 * @params int $timestamp
		 * @return boolean
		*/
		public static function update_ok($timestamp) {
			return time() >=  $timestamp + self::$interval ? true : false;
		}
		
		/**
		 * Function that registers the plugin scripts
		 * @return void
		*/
		public static function register_plugin_scripts() {
			wp_register_script( 'shogun-followers_js', plugins_url( '/js/shogun_followers.js', __FILE__ ), array('jquery') );
			wp_register_style( 'shogun-followers_css', plugins_url( '/css/shogun_followers.css', __FILE__ ), array('buttons') );
			add_action( 'admin_enqueue_scripts', array( 'Shogun_Followers_Lite', 'enqueue_plugin_scripts' ) );
		}
		
		/**
		 * Function that enqueue the plugin scripts
		 * @return void
		*/
		public static function enqueue_plugin_scripts() {
			wp_enqueue_script( 'shogun-followers_js' );
			wp_enqueue_style( 'shogun-followers_css' );
		}
	}

	//Instantiate the class.
	new Shogun_Followers_Lite();
}

?>