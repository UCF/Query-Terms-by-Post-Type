<?php
/*
Plugin Name: Term Query by Post Type
Description: Adds options for filtering taxonomy term retrieval by post types with assigned terms.
Version: 0.0.0
Author: UCF Web Communications
License: GPL3
GitHub Plugin URI: UCF/Term-Query-by-Post-Type
*/
namespace TermQueryByPostType;

if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Custom filter for filtering terms by post_type
 * @author Jim Barnes
 * @since 0.0.0
 * @param array $pieces The pieces of the query to be sent to MySQL ('select', 'join', 'where')
 * @param array $taxonomies The taxonomies being filtered
 * @param array $args The get_terms arguments
 * @return array $pieces The modified $pieces array
 */
function filter_terms_by_post_type( $pieces, $taxonomies, $args ) {
	if ( isset( $args['post_types'] ) ) {
		global $wpdb;

		$post_types = implode( "','", array_map( 'esc_sql', (array) $args['post_types'] ) );

		$pieces['fields'] .= ", COUNT(*) ";

		$pieces['join']   .= " INNER JOIN $wpdb->term_relationships as tr ON tr.term_taxonomy_id = tt.term_taxonomy_id";
		$pieces['join']   .= " INNER JOIN $wpdb->posts as p ON p.ID = tr.object_id";

		$pieces['where']  .= $wpdb->prepare( " AND p.post_type IN(%s) GROUP BY t.term_id", $post_types );
	}

	return $pieces;
}

add_filter( 'terms_clauses', __NAMESPACE__ . '\filter_terms_by_post_type', 99999, 3 );

