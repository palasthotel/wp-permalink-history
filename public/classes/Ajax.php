<?php

namespace Palasthotel\PermalinkHistory;

defined( 'ABSPATH' ) || exit;

use Palasthotel\PermalinkHistory\Components\Component;

class Ajax extends Component {
	public function onCreate():void {
		add_action("wp_ajax_permalink_history", [$this, 'ajax']);
		add_action("wp_ajax_nopriv_permalink_history", [$this, 'ajax']);
	}

	/**
	 * Public endpoint, mirroring the public REST routes. Everything it returns
	 * comes from FindRedirectUseCase, which only reports publicly visible
	 * content.
	 */
	public function ajax(){

		$contentType = isset($_GET["contentType"]) ?
			sanitize_text_field( wp_unslash( $_GET["contentType"] ) ) :
			Database::CONTENT_TYPE_POST;

		$knownTypes = [
			Database::CONTENT_TYPE_POST,
			Database::CONTENT_TYPE_TERM_TAXONOMY,
		];

		if ( ! in_array( $contentType, $knownTypes, true ) ) {
			wp_send_json_error(
				[ "message" => "Unknown content type." ],
				400
			);
		}

		if(isset($_GET["id"])){
			$id = intval($_GET["id"]);
			wp_send_json($this->plugin->findRedirectsUseCase->historyFor($id, $contentType));
			return;
		} else if(isset($_GET["path"])){
			$path = sanitize_text_field( wp_unslash( $_GET["path"] ) );
			$url = $this->plugin->findRedirectsUseCase->find($path);
			$path = !empty($url) ? str_replace(home_url(), "", $url) : null;
			wp_send_json([
				"url" => $url,
				"path" => $path,
			]);
			return;
		}

		wp_send_json($this->plugin->findRedirectsUseCase->of($contentType));

	}
}
