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
		$search_term            = esc_sql( sanitize_text_field( $_GET['s'] ) );
		$meta_key               = '_your_meta_key';
		$post_types             = array( 'product', 'product_variation' );
		$search_results         = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT posts.ID as product_id, posts.post_parent as parent_id FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id WHERE postmeta.meta_key = '{$meta_key}' AND postmeta.meta_value LIKE %s AND posts.post_type IN ('" . implode( "','", $post_types ) . "') ORDER BY posts.post_parent ASC, posts.post_title ASC",
				'%' . $wpdb->esc_like( $search_term ) . '%'
			)
		);
		$product_ids            = wp_parse_id_list( array_merge( wp_list_pluck( $search_results, 'product_id' ), wp_list_pluck( $search_results, 'parent_id' ) ) );
		$query_vars['post__in'] = array_merge( $product_ids, $query_vars['post__in'] );
	}

	return $query_vars;
}

add_filter( 'request', 'm_request_query', 20 );
