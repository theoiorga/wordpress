<?php
/**
 * Alter the query vars to include products which have the meta we are searching for.
 *
 * @param array $query_vars The current query vars.
 *
 * @return array
 */
function m_request_query( $query_vars ) {

	global $typenow;
	global $wpdb;
	global $pagenow;

	if ( 'product' === $typenow && isset( $_GET['s'] ) && 'edit.php' === $pagenow ) {
		$search_term  = esc_sql( sanitize_text_field( $_GET['s'] ) );
    // Split the search term by comma.
		$search_terms = explode( ',', $search_term );
    // If there are more terms make sure we also search for the whole thing, maybe it's not a list of terms.
		if ( count( $search_terms ) > 1 ) {
			$search_terms[] = $search_term;
		}
    // Cleanup the array manually to avoid issues with quote escaping.
		array_walk( $search_terms, 'trim' );
		array_walk( $search_terms, 'esc_sql' );
		$meta_key               = '_sku';
		$post_types             = array( 'product', 'product_variation' );
		$query                  = "SELECT DISTINCT posts.ID as product_id, posts.post_parent as parent_id FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id WHERE postmeta.meta_key = '{$meta_key}' AND postmeta.meta_value IN  ('" . implode( "','", $search_terms ) . "') AND posts.post_type IN ('" . implode( "','", $post_types ) . "') ORDER BY posts.post_parent ASC, posts.post_title ASC";
		$search_results         = $wpdb->get_results( $query );
		$product_ids            = wp_parse_id_list( array_merge( wp_list_pluck( $search_results, 'product_id' ), wp_list_pluck( $search_results, 'parent_id' ) ) );
		$query_vars['post__in'] = array_merge( $product_ids, $query_vars['post__in'] );
	}

	return $query_vars;
}

add_filter( 'request', 'm_request_query', 20 );
