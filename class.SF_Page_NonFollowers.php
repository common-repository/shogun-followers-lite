<?php

/**
 * Class for the Page for unfollowing those who aren't following the user on Twitter
 *
 * @package ShogunFollowers
*/
 
class SF_Page_NonFollowers {

	/**
	 * Page Title
	 * @var string
	*/	
	private $_title = 'Unfollow Non Followers';
	
	/**
	 * options
	 * @var SF_options
	*/
	private $_options;

   	/**
	 * Class constructor
	 * @params array $followers
	 * @return void
	*/
    public function __construct($followers) {
		$this->_followers = $followers;
		$this->init();
    }
	
	/**
	 * Initializes the options, followers and Wordpress action hooks.
	 * @var SF_Options
	 * @return void
	*/
	private function init() {		
		add_action('admin_menu', array( $this, 'add_menu') );
	}
	
	/**
	 * Add the plugin menu
	 * @return void
	*/
	public function add_menu() {
		$hook = add_submenu_page( 'shogun-followers-top-menu', 'Non Followers', 'Non Followers', 'manage_options', 'shogun-followers-non-followers', array( $this, 'create_page') );
		add_action( "load-$hook",  array( $this, 'page_init' ) );
	}
	
	/**
	 * Create the page
	 * @return void
	*/
    public function create_page() {
 ?>
        <div class="wrapper">
			<?php	
			Shogun_Followers_Lite::display_header();
			$this->nonfollowers_section();
			?>
        </div>
        <?php
    }
	
	/**
	 * Create the Non Followers Section
	 * @return void
	*/
	private function nonfollowers_section() {
		?>
		<form method="post" action="">
			<div class="wrap">
				<div id="icon-users" class="icon32"></div>
				<h2><?php echo $this->_title; ?></h2>
		<?php
			$this->_nonFollowers_table->prepare_items($this->get_non_followers());
			$this->_nonFollowers_table->display();
		?>
			</div>
		</form>
	<?php

    }
	
	/**
	 * Initialize the page
	 * @return void
	*/
	public function page_init() {
		Shogun_Followers_Lite::register_plugin_scripts();
		$this->_nonFollowers_table = new SF_Table($this->get_table_columns(), $this->get_table_actions());
		$this->process_action($this->_nonFollowers_table->current_action());		
	}
		
	/**
	 * Check if the table has an action to process
	 * @params string $action
	 * @return boolean
	*/
	private function has_action($action) {
		return $action != null ? true : false;
	}
	
	/**
	 * Process the action by the table and update the necessary options.
	 * @params string $action
	 * @return void
	*/
	private function process_action($action) {
		if ($this->has_action($action) && $action != 'update') {
			switch ($action) {
				case 'keep':
					foreach ($this->process_post($_POST['follower']) as $id) {
						$this->_followers->update_non_followers_exception($id);
					}
				break;
				case 'unfollow':
					foreach ($this->process_post($_POST['follower']) as $id) {
						$this->_followers->unfollow($id);
						$this->_followers->update_non_follower($id);
					}
				break;	
			}
		}
	}
	
	/**
	 * Process the post data.
	 * @var array $results
	 * @params array $ids
	 * @return array
	*/
	private function process_post($ids) {
		$results = array();
		if(count($ids) >0) {
			foreach ($ids as $id) {
				$json = json_decode(str_replace('\\', '', $id), true);
				$results[] = $json['id'];
			}
		}		
		return $results;
	}
	
	/**
	 * Get a list of the non followers
	 * @return array
	*/
	private function get_non_followers() {
		return $this->_followers->get_non_followers();
	}
	
	/**
	 * Return the table columns
	 * @return array
	*/
	private function get_table_columns() {
		return array(
			'cb' => array(
				'title' => '<input type="checkbox" />'
			),
			'profile_image_url'=>array(
				'title' => 'Profile Image'
			),
			'twitter_name'=> array(
				'title' => 'Name',
				'sortable'=> true
			),
			'screen_name'=>array(
				'title' => 'Screen Name'
			),
			'description'=>array(
				'title' => 'Description'
			),
			'followers_count'=>array(
				'title' => 'Followers'
			),
			'friends_count'=>array(
				'title' => 'Friends'
			),
			'activity'=>array(
				'title' => 'Last Tweet'
			)
		);
	}

	/**
	 * Return the table actions
	 * @return array
	*/
	private function get_table_actions() {
		return array(
			'unfollow' => 'Unfollow',
			'keep' => 'Keep'
		);
	}
}