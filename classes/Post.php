<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-04-04
 * Time: 12:01
 */

namespace Palasthotel\PermalinkHistory;


/**
 * @property Plugin plugin
 */
class Post {

	/**
	 * Post constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		add_action( 'save_post', array( $this, 'on_save' ), 1 );
		add_action( 'get_header', array( $this, 'save_history' ) );
	}

	/**
	 * save history on post save
	 * this will track post name permalink structure changes
	 *
	 * @param $post_id
	 */
	function on_save( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( "edit_post", $post_id ) ) {
			return;
		}

		$this->savePermalinkInHistory( $post_id );
	}

	/**
	 * save post permalink to history if not exists
	 * this will track general permalink structure changes
	 */
	function save_history() {
		if (
			is_main_query()
			&&
			is_singular()
		) {
			$this->savePermalinkInHistory( get_the_ID() );
		}
	}

	/**
	 * @param int $post_id
	 *
	 * @return false|int
	 */
	function savePermalinkInHistory( $post_id ) {

		$permalink = $this->getEscapedPermalink( $post_id );
		if ( $this->plugin->database->postPermalinkHistoryExists( $permalink ) ) {
			return false;
		}

		return $this->plugin->database->addPostPermalink(
			$post_id,
			$permalink
		);
	}

	/**
	 * @param int $post_id
	 *
	 * @return string
	 */
	function getEscapedPermalink( $post_id ) {
		return Utils::getUrlPath( get_permalink( $post_id ) );
	}
}