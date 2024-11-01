<?php

/**
 * Class for handing the followers
 *
 * @package ShogunFollowers_Lite
 *
*/
if ( ! class_exists( 'SF_Followers' ) ) {

	class SF_Followers {

		/**
		 * List of Followers
		 * @var array
		*/
		private $_followers;
		
		/**
		 * List of New Followers
		 * @var array
		*/
		private $_new_followers;

		/**
		 * List of Friends
		 * @var array
		*/
		private $_friends;
		
		/**
		 * To hold all the options.
		 * @var SF_Options
		*/
		private $_options;
		
		/**
		 * Variable to hold the Twitter AutoResponder class
		 * @var TwitterAR
		*/
		private $_twitterAR;

		/**
		 * Variable to indicate whether there are new followers or not.
		 * @var boolean
		*/
		private $_new_followers_check = false;
		
		/**
		 * An array of users details.
		 * @var array
		*/	
		private $_users_details;


		/**
		 * Class constructor
		 * @return void
		*/
		public function __construct($twitterAR = null) {
			$this->_options = new SF_Options();
			$this->_twitterAR = $twitterAR;
			$this->_followers->ids = $this->get_followers_ids();
		}
		
		/**
		 * Get the list of all the followers ids
		 * @return array
		*/
		public function get_followers_ids() {
			return is_array($this->_options->followers['ids']) ? $this->_options->followers['ids'] : array();
		}
		
		/**
		 * Get the list of all the new followers
		 * @return array
		*/
		public function get_new_followers() {
			return is_array($this->_options->new_followers['followers']) ? $this->_options->new_followers['followers'] : array();
		}
		
		/**
		 * Get the list of all the friends
		 * @return array
		*/
		public function get_friends_from_twitter() {
			if (!isset($this->_friends)) {
				$response = isset($this->_twitterAR) ? $this->_twitterAR->get_friends() : new stdClass();
				if (isset($response->ids) ) {
					$this->_friends = count($response->ids) > 0 ? $response->ids : array();
				}
			}
			return isset($this->_friends) ? $this->_friends : array();
		}
		
		/**
		 * Get the list of all the followers from twitter
		 * @return array
		*/
		private function get_followers_ids_from_twitter() {
			$results = array();
			if (!isset($this->_twitter_response)) {
				$this->_twitter_response = isset($this->_twitterAR) ? $this->_twitterAR->get_followers_from_twitter() : new stdClass();
				if (isset($this->_twitter_response->ids) ) {
					$results = count($this->_twitter_response->ids) > 0 ? $this->_twitter_response->ids : $results;
				}
			}
			return $results;
		}
		
		/**
		 * Get the details of each user.
		 * @param array $ids
		 * @return void
		*/
		public function get_users_details($ids) {
			if (count($this->_users_details) != count($ids)) {
				$this->_users_details = isset($this->_twitterAR) ? $this->_twitterAR->get_users_details($ids) : array();
			}
			return $this->_users_details;
		}
		
		/**
		 * Get the list of new followers
		 * @var array $results
		 * @param array $followers
		 * @return array
		*/
		private function get_new_followers_from_twitter() {
			$results = array();
			$ids = $this->get_new_followers_ids_from_twitter();
			if (count($ids) > 0) {
				$followers = $this->get_users_details($ids);
				foreach ($followers as $follower) {
					$results[] = $this->set_follower_data($follower);
				}
			}
			return $results;
		}
		
		/**
		 * Set the list of new followers
		 * @var array $ids
		 * @param array $followers
		 * @return array
		*/
		private function get_new_followers_ids_from_twitter() {
			return array_diff($this->get_followers_ids_from_twitter(), $this->_followers->ids);
		}
		
		/**
		 * Set the follower data
		 * @param object $follower
		 * @return object
		*/
		private function set_follower_data($follower = null) {
			$result = new stdClass();
			$result->id = $follower->id;
			$result->name = $follower->name;
			$result->screen_name = $follower->screen_name;
			$result->profile_image_url = $follower->profile_image_url;
			$result->description = $follower->description;
			$result->following = $follower->following;
			$result->followers_count = $follower->followers_count;
			$result->friends_count = $follower->friends_count;
			if (!isset($follower->status) ) {
				//SF_Log::write($follower, 'STATUS');
			}
			$result->activity = isset($follower->status) ? $this->get_user_activity($follower->status) : '-';
			return $result;
		}

		/**
		 * Get the number of days since last tweet.
		 * @param object $status
		 * @return int
		*/		
		private function get_user_activity($status = null) {
			$status = !null ? $status : stdClass;
			$time_difference_seconds = time() - strtotime($status->created_at);
			$time_difference = floor($time_difference_seconds / (60*60*24) );
			return $time_difference;
		}
		
		/**
		 * Reset the list of followers
		 * @return void
		*/
		public function reset_followers() {
			$this->_options->followers = array('ids' => $this->get_followers_ids(), 'timestamp' => time());
			$this->_options->new_followers = array( 'followers' => array(), 'timestamp' => time() );
			$this->_options->commit_options(array('followers', 'new_followers'));
		}
		
		/**
		 * Update the list of new followers
		 * @param array $followers
		 * @return array
		*/
		public function update_new_followers() {			
			$newFollowers = $this->get_new_followers_from_twitter();
			$this->_options->new_followers_check = ($this->_options->new_followers['followers'] != $newFollowers );
			$this->_options->new_followers = array('followers' =>$newFollowers, 'timestamp'=>time());
			$this->_options->commit_options(array('followers', 'new_followers', 'new_followers_check'));
		}
		
		/**
		 * Update list of Followers and timestamp
		 * @params int $id
		 * @return void
		*/		
		public function update_follower($id){
			$this->_options->followers['ids'][]=$id;
			$this->_options->_followers['timestamp'] = time();
			foreach ($this->_options->new_followers['followers'] as $key => $follower) {
				if ($follower->id == $id) {
					unset( $this->_options->new_followers['followers'][$key]);
					$this->_options->new_followers['timestamp'] = time();
				}
			}
			$this->_options->commit_options( array('followers', 'new_followers') );
			$this->_options->reset();
		}

		/**
		 * Get the list of non followers
		 * @return array
		*/
		public function get_non_followers() {
			return $this->_options->non_followers['ids'];
		}
		
		/**
		 * Get the list of non followers ids
		 * @return array
		*/
		public function refresh_non_followers_ids() {
			$results = array();
			$exceptions = isset($this->_options->non_followers_exceptions['ids']) ? $this->_options->non_followers_exceptions['ids']: array();
			$results = array_diff($this->get_friends_from_twitter(), $exceptions, $this->get_followers_ids());
			return $results;
		}
		
		/**
		 * Get the list of non followers including the timestamp
		 * @return array
		*/
		public function get_non_followers_array() {
			$results = array();
			$ids = $this->refresh_non_followers_ids();
			if (count($ids) > 0) {
				$followers = $this->get_users_details($ids);
				foreach ($followers as $follower) {
					$results['nonFollowers'][] = $this->set_follower_data($follower);
				}
			}
			return $results;
		}
		
		/**
		 * Update the list of non followers
		 * $param array $followers
		 * @return void
		*/
		public function update_non_followers($followers = null) {
			$nonFollowers = $this->get_non_followers_array();
			$lists_to_update = array();
			if (array_key_exists('nonFollowers', $nonFollowers) ) {
				$this->_options->non_followers = array('ids' => $nonFollowers['nonFollowers'], 'timestamp'=>time());
				$lists_to_update[] = 'non_followers';
			}
			if (array_key_exists('ids', $nonFollowers) ) {
				$this->_options->non_followers_exceptions = array('ids' => $nonFollowers['followers'], 'timestamp'=>time());
				$lists_to_update[] = 'non_followers_exceptions';
			}
			$this->_options->commit_options($lists_to_update);
		}
		
		/**
		 * Update the list of Non Followers, exceptions and timestamp.
		 * @params int $id
		 * @return void
		*/
		public function update_non_follower($id){
			foreach ($this->_options->non_followers['ids'] as $key => $follower) {
				if ($follower->id == $id) {
					unset( $this->_options->non_followers['ids'][$key]);
					$this->_options->non_followers['timestamp'] = time();
				}
			}
			$this->_options->non_followers['timestamp']=time();
			$this->_options->commit_options( array('non_followers') );
		}
		
		/**
		 * Update the list of Non Followers exceptions and timestamp.
		 * @params int $id
		 * @return void
		*/	
		public function update_non_followers_exception($id){
			$this->_options->non_followers_exceptions['ids'][]=$id;
			$this->_options->non_followers_exceptions['timestamp'] = time();
			$this->_options->non_followers['timestamp'] = time();
			$this->_options->commit_options( array('non_followers_exceptions') );
			$this->update_non_follower($id);
		}
		
		/**
		 * Get the new followers check status
		 * @params boolean $status
		*/
		public function get_new_followers_check() {
			return $this->_options->new_followers_check;
		}
		
		/**
		 * Set the new followers check status
		 * @params boolean $status
		*/
		public function set_new_followers_check($status = false) {
			$this->_options->new_followers_check = $status;
			$this->_options->commit_options( array('new_followers_check') );
		}
		
		/**
		 * Get the new followers direct message
		 * @return string
		*/
		public function get_new_followers_message() {
			return $this->_options->new_followers_message;
		}
		
		/**
		 * Get the new followers timestamp
		 * @return int
		*/
		public function get_new_followers_timestamp() {
			return $this->_options->new_followers['timestamp'];
		}

		/**
		 * Get the non followers timestamp
		 * @return int
		*/
		public function get_non_followers_timestamp() {
			return $this->_options->non_followers['timestamp'];
		}				
		
		
		
		/**
		 * Send a direct message
		 * @param int $id
		 * @param string $message;
		 * @return void
		*/
		public function send_message($id, $message) {
			$this->_twitterAR->message($id, $message);
		}
		
		/**
		 * Follow a user
		 * @param int $id
		 * @return void
		*/	
		public function follow($id) {
			$this->_twitterAR->follow($id);
		}
		
		/**
		 * Unfollow a user.
		 * @param int $id
		 * @return void
		*/
		public function unfollow($id) {
			$this->_twitterAR->unfollow($id);
		}
	}
}