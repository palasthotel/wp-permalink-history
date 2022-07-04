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
		$this->ajaxurl = admin_url("/admin-ajax.php?action=".self::ACTION);

		add_action( 'template_redirect', array( $this, 'on_404' ), 99 );
		add_action( 'wp_ajax_' . self::ACTION, array( $this, 'ajax_redirect_map' ) );
	}

	/**
	 * try to redirect if is 404
	 */
	function on_404() {
		if ( !is_admin() && is_404() ) {
			global $wp;
			$requestPath = $wp->request;
			$url = $this->plugin->findRedirectsUseCase->find($requestPath);
			if(!empty($url)) {
				wp_redirect( $url, 301 );
				exit;
			}
			do_action(Plugin::ACTION_REDIRECT_404, $requestPath);
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
				echo "<br/>";
			}
			echo $item->permalink . " " . $permalink;
			$first = false;
		}
	}
}
