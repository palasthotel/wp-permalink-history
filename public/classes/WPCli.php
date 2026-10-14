<?php


namespace Palasthotel\PermalinkHistory;

defined( 'ABSPATH' ) || exit;

class WPCli {

	/**
	 * Initialize permalink history
	 *
	 * ## OPTIONS
	 *
	 * [--perPage=<number>]
	 * : Number of contents per query
	 *
	 * ## EXAMPLES
	 *
	 *     wp permalink-history init --perPage=10
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	function init($args, $assoc_args){
		$plugin = Plugin::instance();
		\WP_CLI::log( "" );

		$limit = ( isset( $assoc_args['perPage'] ) && intval( $assoc_args['perPage'] ) > 0)?
			intval( $assoc_args['perPage'] ) : 100;

		\WP_CLI::log( "Try to initialize history of posts without permalink history..." );

		$count = 0;
		$error = false;
		while ( $results = $plugin->database->getPostIdsWithNoHistory( $limit ) ) {

			if ( count( $results ) <= 0 ) {
				break;
			}

			foreach ( $results as $id ) {

				$worked = $plugin->database->addPostPermalink(
					$id,
					$plugin->post->getEscapedPermalink( $id )
				);

				$permalink = $plugin->post->getEscapedPermalink( $id );
				$title     = get_the_title( $id );
				\WP_CLI::log( ( ( $worked ) ? "✅  " : "🚨  " ) . "$id: $title $permalink" );
				if ( ! $worked ) {
					$error = true;
					\WP_CLI::log( $plugin->database->wpdb->last_error );
					break;
				}
				$count ++;

			}
			if ( $error ) {
				break;
			}
		}
		\WP_CLI::log( "Initialized $count posts history." );
		\WP_CLI::log( ( $error ) ? "Stopped by error..." : "Finished successfully." );

		\WP_CLI::log( "" );
		\WP_CLI::log( "Try to initialize history of term taxonomies without permalink history..." );

		$count = 0;
		$error = false;
		while ( $results = $plugin->database->getTermTaxonomyIdsWithNoHistory( $limit ) ) {
			if ( count( $results ) <= 0 ) {
				break;
			}
			foreach ( $results as $term_taxonomy_id ) {

				$permalink = $plugin->term_taxonomy->getEscapedPermalink( $term_taxonomy_id );

				if($permalink == false) {
					\WP_CLI::log( "🚨  Problem with $term_taxonomy_id" );
					$error = true;
					break;
				}

				$worked = $plugin->database->addTermTaxonomyPermalink(
					$term_taxonomy_id,
					$permalink
				);
				$term   = get_term_by( 'term_taxonomy_id', $term_taxonomy_id );
				\WP_CLI::log( ( ( $worked ) ? "✅  " : "🚨  " ) . "$term_taxonomy_id: $term->name $permalink" );
				if ( ! $worked ) {
					$error = true;
					\WP_CLI::log( $plugin->database->wpdb->last_error );
					break;
				}
				$count ++;
			}
			if ( $error ) {
				break;
			}
		}

		\WP_CLI::log( "Initialized $count term taxonomies history." );
		\WP_CLI::log( ( $error ) ? "Stopped by error..." : "finished successfully." );

		\WP_CLI::log( "" );

	}

	/**
	 * Check permalink history
	 *
	 * ## EXAMPLES
	 *
	 *     wp permalink-history check
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	function check($args, $assoc_args){
		\WP_CLI::log( "" );
		$plugin = Plugin::instance();
		$content_types = $plugin->database->getContentTypes();
		\WP_CLI::log( "There is history for the following content types:" );
		foreach ( $content_types as $type ) {
			$count = $plugin->database->getCount( $type );
			\WP_CLI::log( " - '$type' with $count items" );
		}
		\WP_CLI::log( "" );
	}
}