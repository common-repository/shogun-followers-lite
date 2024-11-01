<?php

/**
 * Class for handling the options
 *
 * @package ShogunFollowers
 */
 
class SF_Options {

	/**
	 * Options prefix
	 * @var string
	*/
	public static $prefix = 'shogun-followers-';
	
	/**
	 * Options array
	 * @var array
	*/
	public static $options = array(
		'settings',
		'account',
		'followers',
		'new_followers',
		'non_followers',
		'non_followers_exceptions',
		'new_followers_message',
		'new_followers_check'
	);
	/**
	 * Settings array
	 * @var array
	*/
	public static $settings;
	
	/**
	 * Account array
	 * @var array
	*/
	
	public static $account;
	
	/**
	 * Followers array
	 * @var array
	*/
	public static $followers;
	
	/**
	 * New Followers array
	 * @var array
	*/
	public static $new_followers;
	
	/**
	 * Non Followers array
	 * @var array
	*/
	public static $non_followers;
	
	/**
	 * Non Followers Exceptions array
	 * @var array
	*/
	public static $non_followers_exceptions;
	
	/**
	 * Settings array
	 * @var string
	*/
	public static $message;
	
	/**
	 * Whether there are new followers or not.
	 * @var boolean
	*/
	public static $new_followers_check;
	
	/**
	 * Class constructor
	 * @return void
	*/
	public function __construct() {
		$this->get_options();
	}
	
	/**
	 * Get all the options.
	 * @return void
	*/
	private function get_options() {
		foreach (self::$options as $option) {
			$this->{$option} = self::get( $option );
		}
	}
	
	/**
	 * Reset all the options.
	 * @return void
	*/	
	public static function get($option = null) {
		return get_option( self::$prefix . $option );
	}

	/**
	 * Reset all the options.
	 * @return void
	*/	
	public function reset() {
		foreach (self::$options as $option) {
			unset( $this->{$option} );
		}
		$this->get_options();
	}
	
	/**
	 * Update the options.
	 * @params array $fields
	 * @return void
	*/	
	public function commit_options($fields = array()) {
		foreach ($fields as $field) {
			self::update($field, $this->{$field});
		}
	}
	
	/**
	 * Update an option.
	 * @params data
	 * @return void
	*/	
	public function update($field, $data = null) {
		update_option(self::$prefix . $field, $data);
		self::$$field = self::get( $field );
	}
	
	/**
	 * Delete all the options
	 * @return void
	*/	
	public static function delete_all_options() {
		foreach (self::$options as $option) {
			delete_option(self::$prefix . $option);
		}
	}
	
}