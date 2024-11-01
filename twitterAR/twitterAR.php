<?php
	// OAUTH for Twitter
	require_once 'twitteroauth.php';

	/**
	 * Wrapper class for the Twitter Oath
	 *
	 * @package Shogun_Followers
	*/
	class TwitterAR {

		/**
		 * Connection object
		 * @var TwitterOAuth
		*/			
		private $_conn;
		
		/**
		 * Max users to get
		 * @var int
		*/
		private	$_max_users = 100;	
		
		/**
		 * Class constructor
		 * @return void
		*/
		public function __construct($consumer_key, $consumer_secret, $oath_token, $oath_secret)	{
			$this->_conn = new TwitterOAuth($consumer_key, $consumer_secret, $oath_token, $oath_secret);
		}
		
		/**
		 * Function to return whether the connection was authenticated or not.
		 * @return boolean
		*/
		public function authenticated() {
			$this->_credentials = $this->get_credentials();
			$connection = isset($this->_credentials->id) ? true : false;
			return $connection;
		}
		
		/**
		 * Function send a direct message
		 * @params int $id
		 * @params string $message
		 * @return boolean
		*/
		public function message($id, $message) {
			return $this->_conn->post('direct_messages/new', array('user_id' => $id, 'text'=> $message));
		}
		
		/**
		 * Function to follow a user
		 * @params int $id
		 * @return boolean
		*/
		public function follow($id) {
			return $this->_conn->post('friendships/create', array('user_id' => $id));			
		}
		
		
		/**
		 * Function to unfollow a user
		 * @params int $id
		 * @return boolean
		*/
		public function unfollow($id) {
			return $this->_conn->post('friendships/destroy', array('user_id' => $id));			
		}
		
		
		/**
		 * Function to verify the user connection.
		 * @return boolean
		*/
		public function get_credentials() {
			return $this->_conn->get('account/verify_credentials');
		}

		/**
		 * Function to get the rate limit for a certain resource.
		 * @params string $resource
		 * @return int
		*/
		private function get_rate_limit($resource){
			$result = $this->_conn->get('application/rate_limit_status', array('resources'=> $resource));
			return $result->resources->{$resource};
		}
		
		/**
		 * Function to get an array of followers.
		 * @return array
		*/
		public function get_followers_from_twitter() {
			$r_l = $this->get_rate_limit('followers');
			return $r_l->{'/followers/ids'}->remaining > 0 ? $this->_conn->get('followers/ids') : array();
		}
		
		/**
		 * Function to get the user details for an array of users.
		 * @params array $ids
		 * @return array
		*/
		public function get_users_details($ids) {
			$ids = array_slice($ids, 0, $this->_max_users-1);
			$r_l = $this->get_rate_limit('users');
			return $r_l->{'/users/lookup'}->remaining > 0 ? $this->_conn->get('users/lookup', array('user_id' => implode(',', $ids))) : array();
		}

		/**
		 * Function to get an array of followers.
		 * @return array
		*/
		public function get_friends() {
			$r_l = $this->get_rate_limit('friends');
			return $r_l->{'/friends/ids'}->remaining > 0 ? $this->_conn->get('friends/ids') : array();
		}
		
		/**
		 * Function to get the friends details for an array of users.
		 * @params array $ids
		 * @return array
		*/		
		public function get_friends_details($ids) {
			$ids = array_slice($ids, 0, $this->_max_users-1);
			$r_l = $this->get_rate_limit('friendships');
			return $r_l->{'/friendships/lookup'}->remaining > 0 ? $this->_conn->get('friendships/lookup', array('user_id' => implode(',', $ids))) : array();
		}
	}