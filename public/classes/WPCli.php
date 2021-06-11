<?php


namespace Palasthotel\PermalinkHistory;

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
		echo "\n";

		$limit = ( isset( $assoc_args['perPage'] ) && intval( $assoc_args['perPage'] ) > 0)?
			intval( $assoc_args['perPage'] ) : 100;

		echo "Try to initialize history of posts without permalink history...\n";

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
				echo ( ( $worked ) ? "✅  " : "🚨  " ) . "$id: $title $permalink\n";
				if ( ! $worked ) {
					$error = true;
					echo $plugin->database->wpdb->last_error . "\n";
					break;
				}
				$count ++;

			}
			if ( $error ) {
				break;
			}
		}
		echo "Initialized $count posts history.\n";
		echo ( $error ) ? "Stopped by error..." : "Finished successfully.\n";

		echo "\n";
		echo "Try to initialize history of term taxonomies without permalink history...\n";

		$count = 0;
		$error = false;
		while ( $results = $plugin->database->getTermTaxonomyIdsWithNoHistory( $limit ) ) {
			if ( count( $results ) <= 0 ) {
				break;
			}
			foreach ( $results as $term_taxonomy_id ) {

				$permalink = $plugin->term_taxonomy->getEscapedPermalink( $term_taxonomy_id );

				if($permalink == false) {
					echo "🚨  Problem with $term_taxonomy_id\n";
					$error = true;
					break;
				}

				$worked = $plugin->database->addTermTaxonomyPermalink(
					$term_taxonomy_id,
					$permalink
				);
				$term   = get_term_by( 'term_taxonomy_id', $term_taxonomy_id );
				echo ( ( $worked ) ? "✅  " : "🚨  " ) . "$term_taxonomy_id: $term->name $permalink\n";
				if ( ! $worked ) {
					$error = true;
					echo $plugin->database->wpdb->last_error . "\n";
					break;
				}
				$count ++;
			}
			if ( $error ) {
				break;
			}
		}

		echo "Initialized $count term taxonomies history.\n";
		echo ( $error ) ? "Stopped by error..." : "finished successfully.\n";

		echo "\n";

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
		echo "\n";
		$plugin = Plugin::instance();
		$content_types = $plugin->database->getContentTypes();
		echo "There is history for the following content types:\n";
		foreach ( $content_types as $type ) {
			$count = $plugin->database->getCount( $type );
			echo " - '$type' with $count items\n";
		}
		echo "\n";
	}
}