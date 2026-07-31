<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 2019-04-08
 * Time: 18:04
 */

namespace Palasthotel\PermalinkHistory;

defined( 'ABSPATH' ) || exit;

use Palasthotel\PermalinkHistory\Components\Component;

class Redirects extends Component {

	const ACTION = "permalink_history_map";

    public string $ajaxurl;

	public function onCreate():void {
		$this->ajaxurl = wp_nonce_url(
			admin_url( "admin-ajax.php?action=" . self::ACTION ),
			self::ACTION
		);

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
	 *
	 * The map lists every historical URL of the site, so it is limited to users
	 * who may see the permalink settings it is linked from.
	 */
	public function ajax_redirect_map() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'You are not allowed to generate the redirect map.', 'permalink-history' ),
				'',
				array( 'response' => 403 )
			);
		}
		check_admin_referer( self::ACTION );

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
			echo esc_html( $item->permalink ) . " " . esc_html( $permalink );
			$first = false;
		}
	}
}
