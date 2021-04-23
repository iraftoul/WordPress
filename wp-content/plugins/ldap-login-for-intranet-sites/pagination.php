<?php



if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class  Users_Report extends WP_List_Table {


	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'User', 'ldap' ), //singular name of the listed records
			'plural'   => __( 'Users', 'ldap' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}


	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_users( $per_page, $page_number) {

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}user_report";

		if ( ! empty( $_REQUEST['orderby'] ) ) { 
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		} else {
			$sql .= ' ORDER BY time DESC';			
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


		
		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		$Total_records = count($result);
        for($i=0;$i<$Total_records;$i++)
        {
        	$j= $i+1; 
        	$result[$i]["id"] = (string) $j;	
        }


   
		return $result;
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}user_report";

		return $wpdb->get_var( $sql );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'user_name':
			case 'time':
			case 'Ldap_status':
			case 'Ldap_error':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	


	

	/**
	 *  Associative array of columns
	 *
	 * @return array
	//  */
	function get_columns() {
		$columns = [
			'id' => __('Sr No.'),
			'user_name' => __( 'Username'),
			'time' => __( 'Time <br>(UTC + 0)'),
			'Ldap_status' => __( 'Status'),
			'Ldap_error' =>__('Additional Information')
		];

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'user_name' => array( 'user_name', true ),
			'time' => array( 'time', true )
		);

		return $sortable_columns; 
	}

	
	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
	

		$this->_column_headers = $this->get_column_info();

		$per_page     = $this->get_items_per_page( 'Users_per_page', 10 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

			

		
		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );



		$this->items = self::get_users( $per_page, $current_page);


		
	}

}


class LDAP_Plugin {

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $users_obj;

	//class constructor
	public function __construct() {
		// add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_init', [ $this, 'screen_option' ] );
	}


	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
		
		?>
		<div class="wrap">

			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->users_obj->prepare_items();
								$this->users_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option() {

		/*$option = 'per_page';
		$args   = [
			'label'   => 'Users',
			'default' => 5,
			'option'  => 'Users_per_page'
		];

		add_screen_option( $option, $args );*/

	$this->users_obj = new Users_Report();
	}


	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}


add_action( 'plugins_loaded', function () {
	LDAP_Plugin::get_instance();
} );