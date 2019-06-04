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
	 * save to history
	 */
	foreach ( $permalinks as $p ) {
		if(!Plugin::get_instance()->database->postPermalinkHistoryExists($post["ID"], $p))
			Plugin::get_instance()->database->addPostPermalink( $post["ID"], $p );
	}

}