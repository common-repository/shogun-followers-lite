<?php

/**
 * Class for handing the tables
 *
 * @package ShogunFollowers
 *
*/

if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if(!class_exists('SF_Table')){
	class SF_Table extends WP_List_Table {

		/**
		 * List of actions
		 * @var array
		*/
		private $_bulk_actions;
		
		/**
		 * List of columns
		 * @var array
		*/
		private	$_columns;
		
		/**
		 * List sortable columns
		 * @var array
		*/
		private	$_sortable_columns;

		/**
		 * Class constructor
		 * @params array $columns
		 * @params array $actions
		 * @return void
		*/
		public function __construct($columns = array(), $actions = array() ) {
			add_filter( 'list_table_primary_column', array($this, 'set_primary_column'), 10, 2 );
			$this->set_table($columns, $actions);
			parent::__construct( array(
				'singular'=> 'follower', //Singular label
				'plural' => 'followers', //plural label, also this well be one of the table css class
				'ajax'   => false //We won't support Ajax for this table
			));
		}

		/**
		 * Set the tables columns, bulk actions and sortable columns.
		 * @params array $columns
		 * @params array $actions
		 * @return void
		*/
		private function set_table($columns = array(), $actions = array()) {
			$this->_bulk_actions = $actions;
			$this->_columns = $this->set_columns($columns);
			$this->_sortable_columns = $this->set_sortable_columns($columns);
		}
		
		/**
		 * Set the columns.
		 * @var array $results;
		 * @params array $columns
		 * @return array
		*/
		private function set_columns($columns = array()) {
			$results = array();
			foreach($columns as $key => $array) {
				$results[$key] = $array['title'];
			}
			return $results;
		}

		/**
		 * Get the columns.
		 * @var array $results;
		 * @return array
		*/
		public function get_columns() {
			$results = array();
			foreach ($this->_columns as $column => $value) {
				$results[$column] = $value;
			}
			return $results;
		}
		
		/**
		 * Add content before or after the table..
		 * @params string $which;
		 * @return void
		*/	
		public function extra_tablenav( $which ) {
			if ( $which == "top" ){
				//The code that goes before the table is here
				//echo 'Pre Table Content';
			}
			if ( $which == "bottom" ){
				//The code that goes after the table is there
				//echo "Post Table Content";
			}
		}
		
		/**
		 * Override WP_List_Table prepare_items function.
		 * @params array $items;
		 * @return void
		*/	
		public function prepare_items($items = null) {
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array($this->_columns, $hidden, $sortable);
			$this->items = $items;
		}

		/**
		 * Override the WP_List_Table column_default function.
		 * @var array $result
		 * @params object $item
		 * @params string $column_name
		 * @return array
		*/		
		public function column_default($item, $column_name) {
			$result = array();
			switch ( $column_name ) {
				case 'profile_image_url':
					$src = isset($item->{$column_name}) ? $item->{$column_name} : '';
					$result = '<img src="' . $src . '" height="48" width="48">';
				break;
				case 'screen_name': $result = '@' . $item->{$column_name};
				break;
				case 'following': 
					$result = $item->following ? _('Yes') : _('No');
				break;
				case 'twitter_name' :
					$result = $item->name;
				break;
				case 'activity' :
					$result = $this->get_activity_bar($item->activity);
				break;
				default:  
					$result = isset($item->{$column_name}) ? $item->{$column_name} : '';
				break;
			}
			return $result;
		}
		
		private function get_activity_bar($value) {
			$width = $value <= 100 && $value >= 0 ? floor(100 - $value) : 0; 
			
			$bar = <<<EOT
				<progress max="100" value="$width"></progress>
EOT;
			$result = $value . ' days ago' . $bar;
			return $result;
		}

		/**
		 * Function that sets the columns that are sortable
		 * @var array $result
		 * @params array $columns
		 * @return array
		*/	
		private function set_sortable_columns($columns) {
			$results = array();
			foreach ($columns as $key => $column) {
				if (isset($column['sortable']) ) {
					$results[$key] = $column['sortable'];
				}
			}
			return $results;
		}
		
		/**
		 * Get the columns that are sortable
		 * @var array $sortable_columns
		 * @return array
		*/	
		public function get_sortable_columns() {
			$sortable_columns = array();
			foreach($this->_sortable_columns as $column => $value) {
				$sortable_columns[$column] = array($column, $value);
			}
			return $sortable_columns;
		}

		/**
		 * Get the bulk actions
		 * @return array
		*/
		function get_bulk_actions() {
			return $this->_bulk_actions;
		}
		
		/**
		 * Create a checkbox column
		 * @params object $item
		 * @return string
		*/
		function column_cb($item) {
			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="{%2$s, %3$s, %4$s}" />',
				$this->_args['singular'], "&quot;id&quot;:&quot;{$item->id}&quot;", "&quot;screen_name&quot;:&quot;{$item->screen_name}&quot;", "&quot;name&quot;:&quot;{$item->name}&quot;"
			);    
		}
		
		/**
		 * Returns the actions.
		 * @params object $item
		 * @return array
		*/
		function column_actions($item){
			$actions = array(
					'follow' => sprintf('<a href="?page=%s&action=%s&id=%s">Follow</a>',$_REQUEST['page'],'follow',$item->id),
					'reply' => sprintf('<a href="?page=%s&action=%s&id=%s">Reply</a>',$_REQUEST['page'],'reply',$item->id),
					'ignore' => sprintf('<a href="?page=%s&action=%s&id=%s">Ignore</a>',$_REQUEST['page'],'ignore',$item->id)
				);
			return $this->row_actions($actions);
		}
		
		/**
		 * Function that prints out a message in the case there are no followers.
		 * @return void
		*/
		public function no_items() {
			_e( 'No followers yet.' );
		}
		
		/**
		 * Sets the primary column.
		 * @params string $column
		 * @params string $screen
		 * @return string
		*/
		public function set_primary_column( ) {
			return 'screen_name';
		}

	}
}