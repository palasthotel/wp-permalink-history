<?php

namespace Palasthotel\PermalinkHistory\UseCase;

defined( 'ABSPATH' ) || exit;

use Palasthotel\PermalinkHistory\Components\Component;
use Palasthotel\PermalinkHistory\Database;
use Palasthotel\PermalinkHistory\Plugin;
use Palasthotel\PermalinkHistory\Utils;

class FindRedirectUseCase extends Component {

	/**
	 * History of all publicly visible content of a type.
	 *
	 * Read by the public REST route and the public ajax endpoint, so entries of
	 * content a visitor cannot reach — drafts, pending, private, trashed, and
	 * non-public post types or taxonomies — are left out.
	 */
	public function of(string $contentType) {
		$results = $this->plugin->database->getHistoryOf($contentType);

		$this->primeCache($results, $contentType);

		$groups = [];
		foreach ($results as $result) {
			$contentId = (int) $result->content_id;

			if (!$this->isPubliclyVisible($contentId, $contentType)) {
				continue;
			}

			$path = Utils::getUrlPath(get_permalink($contentId));
			if (!isset($groups[$contentId])) {
				$groups[$contentId] = [
					"content_id" => $contentId,
					"permalink" => $path,
					"history" => [],
				];
			}
			if ($path == $result->permalink) {
				continue;
			}
			$groups[$contentId]['history'][] = [
				"id" => $result->id,
				"permalink" => $result->permalink
			];
		}
		return array_filter(array_values($groups),function($item){
			return count($item['history']);
		});
	}

	/**
	 * History of a single content, for public consumers.
	 *
	 * Returns an empty array for content that is not publicly visible. The
	 * block editor panel does not use this path: it reads through the REST field
	 * of the post itself, which is already covered by that post's permissions.
	 */
	public function historyFor(int $id, string $contentType = Database::CONTENT_TYPE_POST): array {
		if (!$this->isPubliclyVisible($id, $contentType)) {
			return [];
		}

		return array_map(function ($item) {
			$array = (array)$item;
			unset($array['content_type']);
			unset($array['content_id']);
			return $array;
		},$this->plugin->database->getHistoryFor($id, $contentType));
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

	/**
	 * Can a visitor without any capabilities reach this content?
	 */
	private function isPubliclyVisible(int $contentId, string $contentType): bool {
		if ($contentId < 1) {
			return false;
		}

		if (Database::CONTENT_TYPE_TERM_TAXONOMY === $contentType) {
			$term = $this->plugin->term_taxonomy->getTerm($contentId);
			if (!($term instanceof \WP_Term)) {
				return false;
			}
			$taxonomy = get_taxonomy($term->taxonomy);

			return $taxonomy instanceof \WP_Taxonomy && $taxonomy->public;
		}

		$post = get_post($contentId);

		return $post instanceof \WP_Post
			&& "publish" === $post->post_status
			&& is_post_type_viewable($post->post_type);
	}

	/**
	 * Load the posts of a history result set in one query.
	 *
	 * Without this, isPubliclyVisible() and get_permalink() would each hit the
	 * database per row, which matters because the callers are unauthenticated.
	 *
	 * @param \Palasthotel\PermalinkHistory\HistoryItem[] $items
	 */
	private function primeCache(array $items, string $contentType): void {
		if (Database::CONTENT_TYPE_POST !== $contentType || empty($items)) {
			return;
		}

		$ids = array_values(array_unique(array_map(function ($item) {
			return (int) $item->content_id;
		}, $items)));

		$ids = array_filter($ids);
		if (empty($ids)) {
			return;
		}

		get_posts([
			"post__in" => $ids,
			"post_type" => get_post_types(["public" => true]),
			"post_status" => "publish",
			"posts_per_page" => count($ids),
			"no_found_rows" => true,
			"ignore_sticky_posts" => true,
			"update_post_term_cache" => false,
		]);
	}

}
