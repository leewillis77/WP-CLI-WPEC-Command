<?php

/**
 * Commands for working with WP e-Commerce product tags.
 */
class Wpec_Product_Tag_Command extends \WP_CLI\CommandWithDBObject {

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
	 * Get a list of product tags.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Accepted values: table, csv, json, count. Default: table
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpec-product-tag list
	 *
	 *     wp wpec-product-tag list --format=csv
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

		$terms = get_terms( 'product_tag', $args );

		if ( is_wp_error( $terms ) ) {
			WP_CLI::error( "Couldn't retrieve tags." );
		} elseif ( !count( $terms ) ) {
			WP_CLI::log( 'No tags found.' );
		}

		if ( 'ids' == $formatter->format ) {
			echo implode( ' ', $terms );
		} else {
			$formatter->display_items( $terms );
		}
	}

	/**
	 * Get a single tag.
	 *
	 * ## OPTIONS
	 *
	 * <product-tag>
	 * : Tag ID or slug.
	 *
	 * [--format=<format>]
	 * : Accepted values: table, csv, json, count. Default: table
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpec-product-tag get 12
	 *
	 *     wp wpec-product-tag get example-tag --format=json
	 */
	function get( $args, $assoc_args ) {
		$formatter = $this->get_formatter( $assoc_args );

		// Work out how we're searching for the term
		$fetch_by = 'id';
		if ( !is_numeric( $args[0] ) ) {
			$fetch_by = 'slug';
		}
		$fetch = $args[0];

		$term = get_term_by( $fetch_by, $fetch, 'product_tag' );

		if ( false === $term ) {
			WP_CLI::error( "Couldn't get tag." );
		}

		if ( 'ids' == $formatter->format ) {
			echo $term->term_id;
		} else {
			$formatter->display_items( array( $term ) );
		}

	}

	/**
	 * Delete one or more product tags.
	 *
	 * ## OPTIONS
	 *
	 * <id>...
	 * : The term ID of the tag to remove.
	 *
	 * ## EXAMPLES
	 *
	 *     # Delete term 7
	 *     wp wpec-product-tag delete 7
	 */
	public function delete( $args, $assoc_args ) {

		// Validate all term IDs are numeric and valid before doing anything
		foreach ( $args as $term_id ) {
			if ( !is_numeric( $term_id ) ) {
				WP_CLI::error( "Invalid tag ID provided: $term_id." );
			}
			$term = get_term_by( 'id', $term_id, 'product_tag' );
			if ( !$term ) {
				WP_CLI::error( "Invalid tag ID provided: $term_id." );
			}
		}

		reset( $args );
		foreach ( $args as $term_id ) {
			$result = wp_delete_term( $term_id, 'product_tag' );
			if ( $result ) {
				WP_CLI::line( "Tag ID $term_id successfully removed." );
			} else {
				WP_CLI::error( "Tag ID $term_id could not be removed." );
			}
		}
		WP_CLI::success( "All tags deleted." );
	}

	/**
	 * Create a new tag.
	 *
	 * ## OPTIONS
	 *
	 * <name>
	 * : The name of the tag.
	 *
	 * [--description=<description>]
	 * : The description of the tag.
	 *
 	 * [--slug=<slug>]
	 * : The slug to assign to this tag.
	 *
	 * ## EXAMPLES
	 *
	 *     wp wpec-product-tag create "My tag"
	 *
	 *     wp wpec-product-tag create "Other tag" --slug="other-tag" --description="More awesome stuff"
	 */
	public function create( $args, $assoc_args ) {

		$name        = $args[0];
		$description = isset( $assoc_args['description'] ) ? $assoc_args['description'] : '';
		$slug        = isset( $assoc_args['slug'] ) ? $assoc_args['slug'] : '';

		$args = array(
			'description' => $description,
			'slug'        => $slug,
		);
		if ( wp_insert_term( $name, 'product_tag', $args ) ) {
			WP_CLI::success( "Tag successfully created." );
		} else {
			WP_CLI::error( "Tag could not be created." );
		}
	}

	/**
	 * Generate product tags.
	 *
	 * ## OPTIONS
	 *
	 * [--count=<number>]
	 * : How many tags to generate. Default: 10
	 *
	 * ## EXAMPLES
	 *
	 *     # Generate 10 product tags
	 *     wp wpec-product-tag generate
	 *
	 *     # Generate 20 product tags
	 *     wp wpec-product-tag generate --count=20
	 *
	 */
	function generate( $args, $assoc_args ) {
		$count = isset( $assoc_args['count'] ) ? (int) $assoc_args['count'] : 10;

		$notify = \WP_CLI\Utils\make_progress_bar( 'Generating tags', $count );

		for ( $i = 1; $i <= $count; $i++ ) {
			$name = sprintf( 'Product tag %d', $i );
			if ( wp_insert_term( $name, 'product_tag', array() ) ) {
				$notify->tick();
			} else {
				WP_CLI::error( "Failed to create tag $name" );
			}
		}
		$notify->finish();
	}

}

WP_CLI::add_command( 'wpec-product-tag', 'Wpec_Product_Tag_Command' );

