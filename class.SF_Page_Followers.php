<?php

/**
 * Class for the Page which is for managing and following new followers on Twitter
 *
 * @package ShogunFollowers
*/

class SF_Page_Followers {
    
	/**
	 * Page Title
	 * @var string
	*/	
	private $_title = 'Manage New Followers';
	
	/**
	 * Page slug
	 * @var string
	*/	
	private $_page_slug = 'shogun-followers-top-menu';
	
	/**
	 * Options
	 * @var SF_options
	*/
	private $_options;

	/**
	 * Message section option name
	 * @var string
	*/
	private $_section_message_option = 'shogun-followers-new_followers_message';
	
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
	 * Initiates the options and init actions.
	 * @return void
	*/
	private function init() {
		add_action('admin_menu', array( $this, 'add_menu') );
	}
	
	/**
	 * Add the plugin menu
	 * @return void
	*/
	public function add_menu(){
		$hook_main = add_menu_page( 'Shogun Followers', 'Shogun Followers', 'manage_options', $this->_page_slug, null, 'dashicons-admin-generic', 6 ); 
		$hook = add_submenu_page( $this->_page_slug, 'Shogun Followers', 'New Followers', 'manage_options', $this->_page_slug, array( $this, 'create_page') );
		add_action( "load-$hook",  array( $this, 'page_init' ) );
		add_action( 'admin_init', array( $this, 'page_settings' ) );
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
				$this->new_followers_section(); 
			?>
            
        </div>
        <?php
    }
	
	/**
	 * Create the new Follower Section
	 * @return void
	*/
	private function new_followers_section() {
		$this->_followers->set_new_followers_check(false);
		?>
		<form method="post" action="options.php">
            <h2><?php echo $this->_title; ?></h2>
			<?php
                // This prints out all hidden setting fields
				do_settings_sections( $this->_section_message_option );
                settings_fields( 'shogun_followers_option_message' );   
                submit_button(); 
			?>
         </form>
		<form method="post" action="">
			<div class="wrap">
				<div id="icon-users" class="icon32"></div>
				<h3>New Followers</h3>
		<?php
			$this->_followers_table->prepare_items($this->get_new_followers());
			$this->_followers_table->display();
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
		$this->_followers_table = new SF_Table($this->get_table_columns(), $this->get_table_actions());
		$this->process_action($this->_followers_table->current_action());
	}
	
	/**
	 * Initialize the page settings
	 * @return void
	*/
	public function page_settings() {
		$this->set_table_fields();
		
		register_setting(
            'shogun_followers_option_message', // Option group
           $this->_section_message_option, // Option name
            array( $this, 'process_message' ) // Sanitize
        );
		
		add_settings_section(
            'setting_section_message', // ID
            'Custom Twitter Message', // Title
            array( $this, 'print_section_message_info' ), // Callback
            $this->_section_message_option // Page
        );
		
		add_settings_field(
			'message', // ID
			'Message', // Title 
			array( $this, 'field_callback' ), // Callback
			$this->_section_message_option, // Page
			'setting_section_message',// Section
			array('field'=> 'message', 'classes'=> 'widefat')
		);
	}
	
	/**
	 * Print out the section message and info
	 * @return void
	*/
	public function print_section_message_info() {
		self::display_message_instructions();
    }
	
	/**
	* Check if the table has an action to process
	* @return boolean
	*/	
	private function has_action() {
		return $this->_followers_table->current_action() != null ? true : false;
	}

	/**
	* Process the table actions
	* @params string $action
	* @return void
	*/	
	private function process_action($action) {
		if ($this->has_action()) {
		
			switch ($action) {
				case 'reply':
					foreach ($this->get_reply($_POST['follower'], $this->_followers->get_new_followers_message() ) as $id=>$message) {
						$this->_followers->send_message($id, $message);
						$this->_followers->update_follower($id);
					}
				break;
				case 'follow':
					foreach ($this->get_reply($_POST['follower'] ) as $id=>$message)  {
						$this->_followers->follow($id);
						$this->_followers->update_follower($id);
					}
				break;
				case 'reply&follow':
					foreach ($this->get_reply($_POST['follower'], $this->_followers->get_new_followers_message() ) as $id=>$message) {
						$this->_followers->send_message($id, $message);
						$this->_followers->follow($id);
						$this->_followers->update_follower($id);
					}
				break;
				case 'ignore':
					foreach ($this->get_reply($_POST['follower'] ) as $id=>$message) {
						$this->_followers->update_follower($id);
					}
				break;
				
			}
		}
	}
	
	/**
	 * Function to get the new followers
	 * @return array
	*/	
	private function get_new_followers() {
		return $this->_followers->get_new_followers();
	}	
	
	/**
	 * Create an array of reply messages
	 * @params array $ids
	 * @params string $message
	 * @return array
	*/	
	private function get_reply($ids, $message = '') {
		$results = array();
		if(count($ids) >0) {
			foreach ($ids as $id ) {
				$this->create_message($results, $id, $message);
			}
		}
		return $results;
	}
	
	/**
	 * Create a message specific for the recipient.
	 * @params array &$array
	 * @params int $id
	 * @parms string message
	 * @return void
	*/	
	private function create_message(&$array, $id, $message) {
		$label = base64_decode('LUBzYW11cmFpOWRlc2lnbg==');
		$json = json_decode(str_replace('\\', '', $id), true);
		$array[$json['id']] = str_replace("[screen_name]", '@' . $json['screen_name'], $message);
		$array[$json['id']] = str_replace("[name]", $json['name'], $array[$json['id']]);
		$array[$json['id']] = strlen($array[$json['id']]) > 500 - strlen( $label ) ? substr($array[$json['id']], 0, 500 - strlen( $label ) ) : $array[$json['id']] . $label;
	}

	/**
	 * Add the table fields
	 * @return void
	*/	
	private function set_table_fields() {
		add_settings_field(
			'timestamp', // ID
			'Timestamp', // Title 
			array( $this, 'field_callback' ), // Callback
			'shogun-followers-setting-admin', // Page
			'followers_table',// Section
			array('field'=> 'timestamp')
		);  
	}
	
	/**
	 * Sanitize the input for the message.
	 * @params string $input
	 * @return string
	*/
	public function process_message($input) {
		return sanitize_text_field($input['message']);
	}
	
	/**
	 * Field callback to produce the message textarea box.
	 * @params array $arg
	 * @return void
	*/	
	public function field_callback(array $arg) {
		$classes = isset($arg['classes']) ? 'class="' . $arg['classes'] . '"' : '';
		$message = $this->_followers->get_new_followers_message();
		printf(
            '<textarea '. $classes .' id="'. $arg['field'] . '" name="' .  $this->_section_message_option . '[' . $arg['field'] . ']" />%s</textarea />',
            isset( $message ) ? esc_attr( $message ) : ''
        );
	}
	
	/**
	 * Function that returns the table columns
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
			'screen_name'=>array(
				'title' => 'Screen Name',
				'sortable'=> true
			),
			'twitter_name'=> array(
				'title' => 'Name',
				'sortable'=> true
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
			'following'=>array(
				'title' => 'Following',
				'sortable'=> true
			),
			'activity'=>array(
				'title' => 'Last Tweet'
			)
		);
	}
	
	/**
	 * Function that returns the table actions
	 * @return array
	*/
	private function get_table_actions() {
		return array(
			'reply' => 'Reply',
			'follow' => 'Follow',
			'reply&follow' => 'Reply and Follow',
			'ignore' => 'Ignore'
		);
	}
	
	/**
	 * Display message instructions
	 * @var string
	 *
	 * @return string
	*/
	public static function display_message_instructions() {
		$html = <<<EOT
			<div id="message_instructions">
			Type [screen_name] to insert user's screen name<br><strong>Example</strong>  <em>"Hey [screen_name], thanks for the follow!"</em>
			<br>Type [name] to insert user's name<br><strong>Example</strong> <em>"Hey [name], thanks for the follow!"</em>
			</div>
EOT;
		echo $html;
	}
}