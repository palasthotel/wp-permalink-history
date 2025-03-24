<?php

namespace Palasthotel\PermalinkHistory;

class Ajax {

    public Plugin $plugin;

	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;

		add_action("wp_ajax_permalink_history", [$this, 'ajax']);
		add_action("wp_ajax_nopriv_permalink_history", [$this, 'ajax']);
	}

	public function ajax(){

		if(isset($_GET["id"])){
			$id = intval($_GET["id"]);
			$contentType = isset($_GET["contentType"]) ?
				sanitize_text_field($_GET["contentType"]) :
				Database::CONTENT_TYPE_POST;
			wp_send_json($this->plugin->findRedirectsUseCase->getById($id, $contentType));
			return;
		}

		$path = sanitize_text_field($_GET["path"]);
		$url = $this->plugin->findRedirectsUseCase->find($path);
		$path = !empty($url) ? str_replace(home_url(), "", $url) : null;
		wp_send_json([
			"url" => $url,
			"path" => $path,
		]);
	}
}
