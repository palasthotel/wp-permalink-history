<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-04-08
 * Time: 18:04
 */

namespace Palasthotel\PermalinkHistory;

/**
 * @property Plugin plugin
 * @property string ajaxurl
 */
class Redirects {

	const ACTION = "permalink_history_map";

	/**
	 * Redirect constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin  = $plugin;
		$this->ajaxurl = "/wp-admin/admin-ajax.php?action=" . self::ACTION;

		add_action( 'template_redirect', array( $this, 'on_404' ) );
		add_action( 'wp_ajax_' . self::ACTION, array( $this, 'ajax_redirect_map' ) );
	}

	/**
	 * try to redirect if is 404
	 */
	function on_404() {
		if ( is_404() ) {
			global $wp;
			$post_id = $this->plugin->database->getPostId( $wp->request );
			if ( $post_id > 0 ) {
				$permalink = get_permalink( $post_id );
				if($permalink) wp_redirect( get_permalink( $post_id ), 301 );
			}
			$term_taxonomy_id = $this->plugin->database->getTermTaxonomyId($wp->request);
			if($term_taxonomy_id > 0){
				$term = $this->plugin->term_taxonomy->getTerm($term_taxonomy_id);
				if($term instanceof \WP_Term) wp_redirect( get_term_link($term), 301 );
			}
		}
	}

	/**
	 * ajax endpoint
	 */
	public function ajax_redirect_map() {
		$this->renderRedirectMap();
		exit;
	}

	/**
	 * render a url map
	 */
	public function renderRedirectMap() {
		$items = $this->plugin->database->getPostHistory();
		$first = true;
		foreach ( $items as $item ) {
			$permalink = $this->plugin->post->getEscapedPermalink( $item->content_id );
			if ( $item->permalink == $permalink ) {
				continue;
			}
			if ( ! $first ) {
				echo "\n";
			}
			echo $item->permalink . " " . $permalink;
			$first = false;
		}
	}
}