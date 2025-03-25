<?php

namespace Palasthotel\PermalinkHistory\UseCase;

use Palasthotel\PermalinkHistory\Database;
use Palasthotel\PermalinkHistory\Plugin;
use Palasthotel\PermalinkHistory\Utils;

class FindRedirectUseCase {

	public Plugin $plugin;

	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
	}

	public function of(string $contentType = Database::CONTENT_TYPE_POST) {
		$results = $this->plugin->database->getHistoryOf($contentType);

		$groups = [];
		foreach ($results as $result) {
			$contentId = $result->content_id;
			$path = Utils::getUrlPath(get_permalink($result->content_id));
			if (!isset($groups[$contentId])) {
				$groups[$contentId] = [
					"path" => $path,
					"history" => [],
				];
			}
			if ($path == $result->permalink) {
				continue;
			}
			$groups[$contentId]['history'][] = $result->permalink;
		}
		return array_filter($groups,function($item){
			return count($item['history']);
		});
	}

	public function historyFor(int $id, string $contentType = Database::CONTENT_TYPE_POST): array {
		return $this->plugin->database->getHistoryFor($id, $contentType);
	}

	public function find(string $requestPath): ?string {
		if (empty($requestPath)) return null;
		if (is_multisite()) {
			$requestPath = get_blog_details()->path . $requestPath;
		}

		$before = apply_filters(Plugin::FILTER_FIND_REDIRECT_BEFORE, null, $requestPath);
		if (is_string($before) && !empty($before)) return $before;

		$post_id = $this->plugin->database->getPostId($requestPath);
		if ($post_id > 0 && get_post_status($post_id) == "publish") {
			$permalink = get_permalink($post_id);
			if ($permalink) return $permalink;
		}

		$term_taxonomy_id = $this->plugin->database->getTermTaxonomyId($requestPath);
		if ($term_taxonomy_id > 0) {
			$term = $this->plugin->term_taxonomy->getTerm($term_taxonomy_id);
			if ($term instanceof \WP_Term) return get_term_link($term);
		}

		return apply_filters(Plugin::FILTER_FIND_REDIRECT_AFTER, null, $requestPath);
	}

}
