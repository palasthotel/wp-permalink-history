<?php

namespace Palasthotel\PermalinkHistory;

class HistoryItem {

	private function __construct(public int $content_id, public string $content_type, public string $permalink, public ?int $id = null) {
	}

	public static function build(int $content_id, string $content_type, string $permalink, ?int $id = null){
		return new HistoryItem($content_id, $content_type, $permalink, $id);
	}

	/**
	 * @param object|null $resultObject
	 *
	 */
	public static function parse($resultObject){
		return (!is_object($resultObject))?
			null
			:
			self::build($resultObject->content_id, $resultObject->content_type, $resultObject->permalink, $resultObject->id);
	}
}
