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
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action('the_post', array($this, 'save_history'));
	}

	/**
	 * save post permalink to history if not exists
	 */
	function save_history(){
		if(
			is_main_query()
			&&
			is_singular()
			&&
			$this->plugin->database->postPermalinkHistoryNotExists($this->getEscapedPermalink(get_the_ID()))
		){
			$this->savePermalinkInHistory(get_the_ID());
		}
	}

	/**
	 * @param int $post_id
	 *
	 * @return false|int
	 */
	function savePermalinkInHistory($post_id){
		return $this->plugin->database->addPostPermalink(
			$post_id,
			$this->getEscapedPermalink($post_id)
		);
	}

	/**
	 * @param int $post_id
	 *
	 * @return string
	 */
	function getEscapedPermalink($post_id){
		return $this->escapeUrl( get_permalink( $post_id ) );
	}

	/**
	 * @param string $url
	 *
	 * @return string
	 */
	function escapeUrl($url){
		return str_replace( home_url(), '', $url );
	}
}