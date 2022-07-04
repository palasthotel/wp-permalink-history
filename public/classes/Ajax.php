<?php

namespace Palasthotel\PermalinkHistory;

/**
 * @property Plugin $plugin
 */
class Ajax {


	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;

		add_action("wp_ajax_permalink_history", [$this, 'ajax']);
		add_action("wp_ajax_nopriv_permalink_history", [$this, 'ajax']);
	}

	public function ajax(){
		$path = sanitize_text_field($_GET["path"]);
		$url = $this->plugin->findRedirectsUseCase->find($path);
		$path = !empty($url) ? str_replace(home_url(), "", $url) : null;
		wp_send_json([
			"url" => $url,
			"path" => $path,
		]);
	}
}