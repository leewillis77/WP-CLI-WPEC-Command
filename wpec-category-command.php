<?php

/**
 * Commands for working with WP e-Commerce product categories.
 */
class Wpec_Category_Command extends \WP_CLI\CommandWithDBObject {

	protected $obj_type = 'stdClass';
	protected $obj_fields = array(
		'term_id',
		'name',
		'slug',
		'parent',
		'count',
		'sort_order',
	);

	/**
	 * Get a list of product categories.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Accepted values: table, csv, json, count. Default: table
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpec-category list
	 *
	 *     wp wpec-category list --format=csv
	 *
	 * @subcommand list
	 * @synopsis
	 */
	function list_( $args, $assoc_args ) {
		$formatter = $this->get_formatter( $assoc_args );
		$args = array(
			'number'     => 0,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'fields'     => 'all',
			'hide_empty' => false,
		);

		if ( 'ids' == $formatter->format ) {
			$args['fields'] = 'ids';
		}

		$terms = get_terms( 'wpsc_product_category', $args );

		if ( is_wp_error( $terms ) ) {
			WP_CLI::error( "Couldn't retrieve categories." );
		} elseif ( !count( $terms ) ) {
			WP_CLI::log( 'No categories found.' );
		}

		if ( 'ids' == $formatter->format ) {
			echo implode( ' ', $terms );
		} else {
			$formatter->display_items( $terms );
		}
	}

	/**
	 * Get a single category.
	 *
	 * ## OPTIONS
	 *
	 * <category>
	 * : Category ID or slug.
	 *
	 * [--format=<format>]
	 * : Accepted values: table, csv, json, count. Default: table
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpec-category get 12
	 *
	 *     wp wpec-category get example-category --format=json
	 */
	function get( $args, $assoc_args ) {
		$formatter = $this->get_formatter( $assoc_args );

		// Work out how we're searching for the term
		$fetch_by = 'id';
		if ( !is_numeric( $args[0] ) ) {
			$fetch_by = 'slug';
		}
		$fetch = $args[0];

		$term = get_term_by( $fetch_by, $fetch, 'wpsc_product_category' );

		if ( false === $term ) {
			WP_CLI::error( "Couldn't get category." );
		}

		if ( 'ids' == $formatter->format ) {
			echo $term->term_id;
		} else {
			$formatter->display_items( array( $term ) );
		}

	}
}

WP_CLI::add_command( 'wpec-category', 'Wpec_Category_Command' );

