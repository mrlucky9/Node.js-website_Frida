<?php
/**
 * Give DB base class
 *
 * @package     Give
 * @subpackage  Classes/Give DB
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


abstract class Give_DB {

	/**
	 * The name of our database table
	 *
	 * @access  public
	 * @since   1.0
	 */
	public $table_name;

	/**
	 * The version of our database table
	 *
	 * @access  public
	 * @since   1.0
	 */
	public $version;

	/**
	 * The name of the primary column
	 *
	 * @access  public
	 * @since   1.0
	 */
	public $primary_key;

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function __construct() {
	}

	/**
	 * Whitelist of columns
	 *
	 * @access  public
	 * @since   1.0
	 * @return  array
	 */
	public function get_columns() {
		return array();
	}

	/**
	 * Default column values
	 *
	 * @access  public
	 * @since   1.0
	 * @return  array
	 */
	public function get_column_defaults() {
		return array();
	}

	/**
	 * Retrieve a row by the primary key
	 *
	 * @access  public
	 * @since   1.0
	 * @return  object
	 */
	public function get( $row_id ) {
		global $wpdb;

		return $wpdb->get_row( "SELECT * FROM $this->table_name WHERE $this->primary_key = $row_id LIMIT 1;" );
	}

	/**
	 * Retrieve a row by a specific column / value
	 *
	 * @access  public
	 * @since   1.0
	 * @return  object
	 */
	public function get_by( $column, $row_id ) {
		global $wpdb;

		return $wpdb->get_row( "SELECT * FROM $this->table_name WHERE $column = '$row_id' LIMIT 1;" );
	}

	/**
	 * Retrieve a specific column's value by the primary key
	 *
	 * @access  public
	 * @since   1.0
	 * @return  string
	 */
	public function get_column( $column, $row_id ) {
		global $wpdb;

		return $wpdb->get_var( "SELECT $column FROM $this->table_name WHERE $this->primary_key = $row_id LIMIT 1;" );
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value
	 *
	 * @access  public
	 * @since   1.0
	 * @return  string
	 */
	public function get_column_by( $column, $column_where, $column_value ) {
		global $wpdb;

		return $wpdb->get_var( "SELECT $column FROM $this->table_name WHERE $column_where = '$column_value' LIMIT 1;" );
	}

	/**
	 * Insert a new row
	 *
	 * @access  public
	 * @since   1.0
	 * @return  int
	 */
	public function insert( $data, $type = '' ) {
		global $wpdb;

		// Set default values
		$data = wp_parse_args( $data, $this->get_column_defaults() );

		do_action( 'give_pre_insert_' . $type, $data );

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$wpdb->insert( $this->table_name, $data, $column_formats );

		do_action( 'give_post_insert_' . $type, $wpdb->insert_id, $data );

		return $wpdb->insert_id;
	}

	/**
	 * Update a row
	 *
	 * @access  public
	 * @since   1.0
	 * @return  bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {

		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( empty( $where ) ) {
			$where = $this->primary_key;
		}

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		if ( false === $wpdb->update( $this->table_name, $data, array( $where => $row_id ), $column_formats ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Delete a row identified by the primary key
	 *
	 * @access  public
	 * @since   1.0
	 * @return  bool
	 */
	public function delete( $row_id = 0 ) {

		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $this->primary_key = %d", $row_id ) ) ) {
			return false;
		}

		return true;
	}
	
	/**
	 * Check if the given table exists
	 *
	 * @since  1.3.2
	 * @param  string $table The table name
	 * @return bool          If the table name exists
	 */
	public function table_exists( $table ) {
		global $wpdb;
		$table = sanitize_text_field( $table );
		
		return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table ) ) === $table;
	}

}
