<?php
/*
Plugin Name: Query Terms by Post Type
Description: Adds options for filtering taxonomy term retrieval by post types with assigned terms.
Version: 1.0.0
Author: UCF Web Communications
License: GPL3
GitHub Plugin URI: UCF/Query-Terms-by-Post-Type
*/
namespace QueryTermsByPostType;

if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Custom filter for filtering terms by post_type.
 * Intended for use with the `terms_clauses` hook.
 * @author Jim Barnes
 * @since 1.0.0
 * @param array $pieces The pieces of the query to be sent to MySQL ('select', 'join', 'where')
 * @param array $taxonomies The taxonomies being filtered
 * @param array $args The get_terms arguments
 * @return array $pieces The modified $pieces array
 */
function filter_terms_by_post_type( $pieces, $taxonomies, $args ) {
	if ( isset( $args['post_types'] ) ) {
		global $wpdb;

		$post_types_raw = is_string( $args['post_types'] ) ? explode( ',', $args['post_types'] ) : (array) $args['post_types'];
		$post_types = "'" . implode( "','", array_filter( $post_types_raw, 'post_type_exists' ) ) . "'";

		$pieces['fields'] .= ", COUNT(*) ";

		$pieces['join']   .= " INNER JOIN $wpdb->term_relationships as tr ON tr.term_taxonomy_id = tt.term_taxonomy_id";
		$pieces['join']   .= " INNER JOIN $wpdb->posts as p ON p.ID = tr.object_id";

		$pieces['where']  .= " AND p.post_type IN({$post_types}) GROUP BY t.term_id";
	}

	return $pieces;
}


/**
 * Adds the `post_types` argument to the $args array
 * if it is request in the GET parameters of the $request.
 * Intended for use with the `rest_{$this->taxonomy}_query` hook.
 * @author Jim Barnes
 * @since 1.0.0
 * @param array $args The argument array for the query
 * @param WP_Request $request The WP_Request argument
 * @return array $args The modified args array
 */
function rest_add_post_types_arg( $args, $request ) {
	if ( isset( $request['post_types'] ) && ! empty( $request['post_types'] ) ) {
		$args['post_types'] = $request['post_types'];
	}

	return $args;
}


/**
 * Custom callback for getting an accurate term count
 * when the `post_types` filter is used.
 * @author Jim Barnes
 * @since 1.0.0
 * @param array $term The term in array form
 * @param string $field_name The field name being modified
 * @param WP_Request $request The request object
 * @return mixed In this case, we're returning an integer
 */
function rest_get_term_count( $term, $field_name, $request ) {
	if ( isset( $request['post_types'] ) && ! empty( $request['post_types'] ) ) {
		$post_types_raw = is_string( $request['post_types'] ) ? explode( ',', $request['post_types'] ) : (array) $request['post_types'];
		$post_types = array_map( 'sanitize_title_for_query' , $post_types_raw );

		$args = array(
			'post_type' => $post_types,
			'tax_query' => array(
				array(
					'taxonomy' => $term['taxonomy'],
					'field'    => 'term_id',
					'terms'    => $term['id']
				)
			)
		);

		$query = new \WP_Query( $args );

		return intval( $query->found_posts );
	}

	return $term['count'];
}


/**
 * Adds all actions, filters, etc. required for the plugin to function.
 * @since 1.0.0
 * @author Jo Dickson
 * @return void
 */
function init() {
	$rest_enabled_taxonomies = get_taxonomies( array(
		'show_in_rest' => true
	) );

	foreach ( $rest_enabled_taxonomies as $tax ) {
		add_filter( "rest_{$tax}_query", __NAMESPACE__ . '\rest_add_post_types_arg', 10, 2 );

		register_rest_field(
			$tax,
			'count',
			array(
				'get_callback' => __NAMESPACE__ . '\rest_get_term_count'
			)
		);
	}

	add_filter( 'terms_clauses', __NAMESPACE__ . '\filter_terms_by_post_type', 99999, 3 );
}

add_action( 'init', __NAMESPACE__ . '\init', 99999 );

