<?php

namespace Palasthotel\PermalinkHistory;

use Palasthotel\PermalinkHistory\Components\Component;
use WP_Error;
use WP_REST_Request;

class REST extends Component {
	public function onCreate(): void {
		parent::onCreate();

		add_action('rest_api_init', [$this, 'rest_api_init']);
	}

	public function rest_api_init(): void {

		$postTypes = get_post_types( array( 'public' => true ) );

		foreach ( $postTypes as $post_type ) {
			register_rest_field(
				$post_type,
				'permalink_history',
				[
					'get_callback' => function ($object) {
						$id = intval($object['id']);
						$permalink = $this->plugin->post->getEscapedPermalink($id);
						return array_filter($this->plugin->database->getHistoryFor($id, Database::CONTENT_TYPE_POST), function($item) use ($permalink){
							return $item->permalink !== $permalink;
						});
					},
					'update_callback' => function ($value) {
						if(is_array($value)){
							foreach ($value as $item){
								if(isset($item["remove"]) && $item["remove"] == "true"){
									$this->plugin->database->deleteById($item["id"]);
								}
							}
						}
					},
				]
			);
		}

		register_rest_route(Plugin::REST_NAMESPACE_V1, '/posts', [
			'methods' => \WP_REST_Server::READABLE,
			'callback' => [$this, 'get_history'],
			'permission_callback' => '__return_true',
		]);
		register_rest_route(Plugin::REST_NAMESPACE_V1, '/posts/(?P<content_id>\d+)', [
			'methods' => \WP_REST_Server::READABLE,
			'callback' => [$this, 'get_history_by_id'],
			'permission_callback' => '__return_true',
			'args' => array(
				'content_id' => array(
					'validate_callback' => function ($param) {
						return is_numeric($param);
					}
				),
			),
		]);
	}

	public function get_history() {
		return $this->plugin->findRedirectsUseCase->of(Database::CONTENT_TYPE_POST);
	}

	public function get_history_by_id(WP_REST_Request $request) {
		$id = intval($request->get_param('content_id'));
		return $this->plugin->findRedirectsUseCase->historyFor($id);
	}
}
