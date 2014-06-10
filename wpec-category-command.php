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

	/**
	 * Delete one or more product categories.
	 *
	 * ## OPTIONS
	 *
	 * <id>...
	 * : The term ID of the category to remove.
	 *
	 * ## EXAMPLES
	 *
	 *     # Delete term 7
	 *     wp wpec-category delete 7
	 */
	public function delete( $args, $assoc_args ) {

		// Validate all term IDs are numeric and valid before doing anything
		foreach ( $args as $term_id ) {
			if ( !is_numeric( $term_id ) ) {
				WP_CLI::error( "Invalid term ID provided: $term_id." );
			}
			$term = get_term_by( 'id', $term_id, 'wpsc_product_category' );
			if ( !$term ) {
				WP_CLI::error( "Invalid term ID provided: $term_id." );
			}
		}

		reset( $args );
		foreach ( $args as $term_id ) {
			$result = wp_delete_term( $term_id, 'wpsc_product_category' );
			if ( $result ) {
				WP_CLI::line( "Term ID $term_id successfully removed." );
			} else {
				WP_CLI::error( "Term ID $term_id could not be removed." );
			}
		}
		WP_CLI::success( "All terms deleted." );
	}

	/**
	 * Create a new category.
	 *
	 * ## OPTIONS
	 *
	 * <name>
	 * : The name of the category.
	 *
	 * [--description=<description>]
	 * : The description of the category.
	 *
	 * [--parent=<parent_id>]
	 * : The parent category ID to assign to this category.
	 *
 	 * [--slug=<slug>]
	 * : The slug to assign to this category.
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpec-category create "My category"
	 *
	 *     wp wpec-category create "Sub-category" --parent=4 --slug="sub-cat" --description="More specific awesome stuff"
	 */
	public function create( $args, $assoc_args ) {

		$name        = $args[0];
		$description = isset( $assoc_args['description'] ) ? $assoc_args['description'] : '';
		$parent      = isset( $assoc_args['parent'] ) ? $assoc_args['parent']  : 0;
		$slug        = isset( $assoc_args['slug'] ) ? $assoc_args['slug'] : '';

		$args = array(
			'description' => $description,
			'slug'        => $slug,
			'parent'      => $parent,
		);
		if ( wp_insert_term( $name, 'wpsc_product_category', $args ) ) {
			WP_CLI::success( "Category successfully created." );
		} else {
			WP_CLI::error( "Category could not be created." );
		}
	}
}

WP_CLI::add_command( 'wpec-category', 'Wpec_Category_Command' );

