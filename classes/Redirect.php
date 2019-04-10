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
 */
class Redirect {

	/**
	 * Redirect constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		add_action( 'template_redirect', array($this, 'on_404'));
		add_action('wp_ajax_permalink_history_map', array($this, 'redirect_map'));
	}

	/**
	 * try to redirect if is 404
	 */
	function on_404(){
		if( is_404() ){
			global $wp;
			$post_id = $this->plugin->database->getPostId($wp->request);
			if($post_id > 0){
				wp_redirect(get_permalink($post_id),301);
			}
		}
	}

	/**
	 * ajax endpoint
	 */
	public function redirect_map(){
		$this->renderRedirectMap();
		exit;
	}

	/**
	 * render a url map
	 */
	public function renderRedirectMap(){
		$items = $this->plugin->database->getPostHistory();
		$first = true;
		foreach ($items as $item){
			$permalink = $this->plugin->post->getEscapedPermalink($item->post_id);
			if($item->permalink == $permalink) continue;
			if(!$first) echo "\n";
			echo $item->permalink." ".$permalink;
			$first = false;
		}
	}
}