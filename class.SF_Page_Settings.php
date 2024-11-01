<?php

/**
 * Class for the Page which for managing your settings
 *
 * @package ShogunFollowers
*/

class SF_Page_Settings {

	/**
	 * Page Title
	 * @var string
	*/	
	private $_title = 'Settings';
	
	/**
	 * Settings Fields
	 * @var array
	*/	
	private $_fields = array(
		'Consumer Key' => 'consumer_key',
		'Consumer Secret' => 'consumer_secret',
		'Access Token' => 'access_token',
		'Access Token Secret' => 'access_secret'
	);
	
	/**
	 * Options name
	 * @var string
	*/	
	private $_table ='shogun-followers-settings';
	
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
	* Add the actions
	* @return void
	*/	
	private function add_actions() {
		add_action('admin_menu', array( $this, 'add_menu') );
		add_action('admin_init', array( $this, 'page_init' ) );
	}
	
	/**
	 * Add the plugin menu
	 * @return void
	*/
	public function add_menu() {
		$hook = add_submenu_page( 'shogun-followers-top-menu', 'Settings', 'Settings', 'manage_options', 'shogun-followers-settings', array( $this, 'create_page') ); 
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
				$this->account_settings_section();
				?>
        </div>
        <?php
	}
	
	/**
	 * Create the new Account Settings Section
	 * @return void
	*/
	private function account_settings_section() {
		?>
		<h2><?php echo $this->_title; ?></h2>           
            <form method="post" action="options.php">
            <?php
                settings_fields( 'shogun_followers_option_group' );   
                do_settings_sections( 'shogun-followers-settings' );
                submit_button(); 
			?>
            </form>
<?php
	}
	
	/**
	 * Initialize page settings
	 * @return void
	*/
     public function page_init() {
		Shogun_Followers_Lite::register_plugin_scripts();
		$this->set_fields('shogun-followers-settings', 'setting_section_id');
		
    }
	
	/**
	 * Initialize the page settings
	 * @return void
	*/
	public function page_settings() {
		register_setting(
            'shogun_followers_option_group', // Option group
            $this->_table, // Option name
            array( $this, 'process_settings' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Twitter Account Info', // Title
            array( $this, 'print_section_info' ), // Callback
            'shogun-followers-settings' // Page
        ); 		
	}
	
	/**
	 * Set the fields for the settings
	 * @params string $page
	 * @params string $section
	 * @return void
	*/	
	private function set_fields($page, $section) {
		foreach ($this->_fields as $name => $field) {
			add_settings_field(
				$field, // ID
				$name, // Title 
				array( $this, 'field_callback' ), // Callback
				$page, // Page
				$section,// Section
				array('field'=> $field)
			);   
		}
	}
	
	/**
	 * Sanitize the input for the message.
	 * @params array $input
	 * @return array
	*/
	public function process_settings($input) {
		$results = $this->sanitize($input);
		$this->_followers->reset_followers();
		return $results;
	}
	
	/**
	 * Sanitize the input for the message.
	 * @var array $new_input
	 * @params array $input
	 * @return array
	*/
    private function sanitize( $input ) {
        $new_input = array();
		foreach ($this->_fields as $field) {
			if( isset( $input[$field] ) )
				$new_input[$field] = sanitize_text_field( $input[$field] );
		}
        return $new_input;
    }

	/**
	 * Print out the settings section info
	 * @return void
	*/
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }
	
	/**
	 * Field callback to print the input fields box.
	 * @params array $arg
	 * @return void
	*/	
	public function field_callback(array $arg) {
		$classes = isset($arg['classes']) ? 'class="' . $arg['classes'] . '"' : '';
		$settings = SF_Account::get_settings();
		printf(
            '<input type="text" '. $classes .' id="'. $arg['field'] . '" name="' . $this->_table . '[' . $arg['field'] . ']" value="%s" />',
            isset( $settings[$arg['field']] ) ? esc_attr( $settings[$arg['field']]) : ''
        );
	}
	
	
}