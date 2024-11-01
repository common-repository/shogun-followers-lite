<?php

/**
 * Class for handing the followers
 *
 * @package ShogunFollowers_Lite
 *
*/
if ( ! class_exists( 'SF_Account' ) ) {

	class SF_Account {

		/**
		 * Account Details
		 * @var array
		*/
		private $_details = array();

		/**
		 * Account Settings
		 * @var array
		*/
		private $_settings = array();		

		/**
		 * Class constructor
		 * @return void
		*/
		public function __construct($options) {
			$this->_options = $options;
			$this->_details = self::get_details();
			$this->_settings = self::get_settings();
		}
		
		/**
		 * Get the account details
		 * @return array
		*/
		public static function get_details() {
			return SF_Options::get('account');
		}
		
		/**
		 * Get the account details
		 * @return array
		*/
		public static function get_settings() {
			return SF_Options::get('settings');
		}
		
		/**
		 * Update the account details
		 * @params $data stdClass
		 * @return array
		*/
		public static function update($data=null) {
			if(isset($data->profile_image_url) ) {
				$data->profile_image_url_big =  str_replace('normal', 'bigger', $data->profile_image_url);
			}
			SF_Options::update('account', $data);
		}
	}
}