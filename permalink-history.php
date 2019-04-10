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
		break;
	case "init":

		$limit = (isset($argv[2]) && intval($argv[2]) > 0)? intval($argv[2]): 100;

		while ( $results = $plugin->database->getPostIdsWithNoHistory( $limit ) ) {

			if ( count( $results ) <= 0 ) {
				break;
			}

			foreach ( $results as $id ) {
				$plugin->post->savePermalinkInHistory( $id );

				$permalink = $plugin->post->getEscapedPermalink( $id );
				$title = get_the_title($id);
				echo "$id: $title $permalink\n";

			}
			sleep( 3 );
		}

		break;
	case "show":

		break;
	default:
		echo "unknown command";
}
