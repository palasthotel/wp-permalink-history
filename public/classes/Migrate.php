<?php

namespace Palasthotel\PermalinkHistory;

class Migrate {

	const PREFIX = "permalink_history:";

	const FIELD = "permalinks";

	/**
	 * Migrate constructor.
	 *
	 */
	public function __construct() {
		add_action( 'ph_migrate_register_field_handlers', array(
			$this,
			'handler_register',
		) );
	}

	function handler_register() {
		ph_migrate_register_field_handler( 'ph_post_destination', self::PREFIX, '\Palasthotel\PermalinkHistory\migrate_handler' );
	}

}

function migrate_handler( $post, $fields ) {
	$permalinks = $fields[ Migrate::PREFIX . Migrate::FIELD ];

	/**
	 * if its just a string, make it an array
	 */
	if ( ! is_array( $permalinks ) ) {
		$permalinks = array( $permalinks );
	}

	/**
	 * delete old history items
	 */
	Plugin::instance()->database->deletePostPermalinkHistory($post["ID"]);

	/**
	 * save to history
	 */
	foreach ( $permalinks as $p ) {
		Plugin::instance()->database->addPostPermalink( $post["ID"], $p );
	}

}
