<?php
define( 'WP_MEMORY_LIMIT', '2G' );
define( 'DISABLE_WP_CRON', 'true' );

$paths = explode( 'wp-content', __FILE__ );
require_once( $paths[0] . 'wp-load.php' );

if ( 1 == count( $argv ) ) {
	?>
	Usage: permalink-history [operation] [parameters]

	Operations:
	check - show permalink history stats.
	init - start initialization of all posts that have no history yet.
	show - rolls back a migration.

	check usage:
	permalink-history check

	init usage:
	permalink-history init [--perPage=100]

	--perPage - number of post ids queried per round

	show usage:
	permalink-history show [post_id]

	<?php
	return;
}

$plugin = \Palasthotel\PermalinkHistory\Plugin::get_instance();

switch ( $argv[1] ) {
	case "check":
		echo "\n";
		$content_types = $plugin->database->getContentTypes();
		echo "There is history for the following content types:\n";
		foreach ( $content_types as $type ) {
			$count = $plugin->database->getCount( $type );
			echo " - '$type' with $count items\n";
		}
		echo "\n";
		break;
	case "init":
		echo "\n";

		$limit = ( isset( $argv[2] ) && intval( str_replace( "--perPage=", "", $argv[2] ) ) > 0 ) ? intval( str_replace( "--perPage=", "", $argv[2] ) ) : 100;

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


		break;
	case "show":

		break;
	default:
		echo "unknown command";
}
